#!/usr/bin/env bash
set -euo pipefail

BRANCH="upgrade/bootstrap-3-to-5"
COMMIT_MSG="chore: upgrade Bootstrap 3 → 5, add Font Awesome, remove jQuery (automated changes)"
BOOTSTRAP_CSS="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
BOOTSTRAP_JS="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
FONTAWESOME_CSS="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.0/css/all.min.css"

echo "Ensure your working tree is clean..."
if [ -n "$(git status --porcelain)" ]; then
  echo "Working tree not clean. Commit or stash changes first."
  git status --porcelain
  exit 1
fi

# Find candidate files to modify
TARGET_FILES=$(git ls-files | grep -E "\.(html|erb|haml|slim|eex|tpl|js|coffee|jsx|tsx|css|scss|less|rb|yml|yaml|xml|ts)$" || true)

echo "Updating layout CDN links for Bootstrap 3 -> Bootstrap 5 and adding Font Awesome..."
update_layout_links() {
  local file="$1"
  if grep -qE "bootstrap.*(bootstrap|maxcdn|netdna).*(3\\.|/3/)" "$file"; then
    echo "Updating Bootstrap CDN references in $file"
    perl -0777 -pe "s{<link\b[^>]*href=[\"'][^\"']*bootstrap[^\"']*(3\\.[^\"']*|/3/)[^\"']*[\"'][^>]*>}{<link href=\"$BOOTSTRAP_CSS\" rel=\"stylesheet\" integrity=\"\" crossorigin=\"anonymous\">}gim" -i "$file" || true
    perl -0777 -pe "s{<script\b[^>]*src=[\"'][^\"']*bootstrap[^\"']*(3\\.[^\"']*|/3/)[^\"']*[\"'][^>]*>\\s*</script>}{<script src=\"$BOOTSTRAP_JS\" integrity=\"\" crossorigin=\"anonymous\"></script>}gim" -i "$file" || true

    if ! grep -qF "$FONTAWESOME_CSS" "$file"; then
      if grep -qF "$BOOTSTRAP_CSS" "$file"; then
        perl -0777 -pe "s{(<link[^>]*href=[\"']$BOOTSTRAP_CSS[\"'][^>]*>)}{\$1\n<link rel=\"stylesheet\" href=\"$FONTAWESOME_CSS\">}s" -i "$file" || true
      else
        perl -0777 -pe "s{</head>}{<link rel=\"stylesheet\" href=\"$FONTAWESOME_CSS\">\\n</head>}s" -i "$file" || true
      fi
    fi

    if grep -qi "jquery" "$file"; then
      echo "Removing jQuery script tags from $file and inserting TODO comment"
      perl -0777 -pe "s{<script\b[^>]*src=[\"'][^\"']*jquery[^\"']*[\"'][^>]*>\\s*</script>\\s*}{}gi" -i "$file" || true
      perl -0777 -pe "s{(</head>)}{<!-- TODO: jQuery removed from layout. If your app uses jQuery plugins, manually port them. -->\\n\\1}s" -i "$file" || true
    fi
  fi
}

# Update common layout names
find . -type f \( -iname "application.html.erb" -o -iname "index.html" -o -iname "*.html" -o -iname "*.erb" -o -iname "*.haml" -o -iname "*.slim" -o -iname "*.eex" -o -iname "*.tpl" -o -iname "default.html" \) | while read -r f; do
  update_layout_links "$f"
done

# Bulk class replacements (conservative)
echo "Running class replacements (panels -> card, btn-default -> btn-secondary, etc.)"
if [ -n "$TARGET_FILES" ]; then
  echo "$TARGET_FILES" | xargs -r perl -pi -e 's/\bpanel\b/card/g'
  echo "$TARGET_FILES" | xargs -r perl -pi -e 's/\bpanel-heading\b/card-header/g'
  echo "$TARGET_FILES" | xargs -r perl -pi -e 's/\bpanel-body\b/card-body/g'
  echo "$TARGET_FILES" | xargs -r perl -pi -e 's/\bpanel-footer\b/card-footer/g'
  echo "$TARGET_FILES" | xargs -r perl -pi -e 's/\bpanel-title\b/card-title/g'
  echo "$TARGET_FILES" | xargs -r perl -pi -e 's/\bwell\b/card/g'
  echo "$TARGET_FILES" | xargs -r perl -pi -e 's/\bbtn-default\b/btn-secondary/g'
  echo "$TARGET_FILES" | xargs -r perl -pi -e 's/\bbtn-xs\b/btn-sm/g'
  echo "$TARGET_FILES" | xargs -r perl -pi -e 's/\bimg-responsive\b/img-fluid/g'
  echo "$TARGET_FILES" | xargs -r perl -pi -e 's/\bpull-left\b/float-start/g'
  echo "$TARGET_FILES" | xargs -r perl -pi -e 's/\bpull-right\b/float-end/g'
  echo "$TARGET_FILES" | xargs -r perl -pi -e 's/\bnavbar-toggle\b/navbar-toggler/g'
  echo "$TARGET_FILES" | xargs -r perl -pi -e 's/\binput-group-addon\b/input-group-text/g'
  echo "$TARGET_FILES" | xargs -r perl -pi -e 's/\bcenter-block\b/mx-auto/g'
  echo "$TARGET_FILES" | xargs -r perl -pi -e 's/\bcol-xs-([0-9]{1,2})\b/col-$1/g'
fi

# TODO annotations for visibility utilities
echo "$TARGET_FILES" | xargs -r grep -IHnE "visible-(xs|sm|md|lg)|hidden-(xs|sm|md|lg)" | cut -d: -f1 | sort -u | while read -r vf; do
  echo "<!-- TODO: Replace Bootstrap 3 visibility utility classes in $vf with Bootstrap 5 utilities (e.g., d-none, d-sm-block, etc.) -->" >> "$vf"
done

# Glyphicon -> Font Awesome mapping (best-effort) without associative arrays
echo "Replacing common glyphicons with Font Awesome (best-effort). Ambiguous icons get a TODO placeholder."
GLYPH_MAP="
glyphicon-search::fa fa-search
glyphicon-chevron-right::fa fa-chevron-right
glyphicon-chevron-left::fa fa-chevron-left
glyphicon-pencil::fa fa-pen
glyphicon-trash::fa fa-trash
glyphicon-edit::fa fa-edit
glyphicon-plus::fa fa-plus
glyphicon-minus::fa fa-minus
glyphicon-user::fa fa-user
glyphicon-lock::fa fa-lock
glyphicon-envelope::fa fa-envelope
glyphicon-ok::fa fa-check
glyphicon-remove::fa fa-times
glyphicon-arrow-left::fa fa-arrow-left
glyphicon-arrow-right::fa fa-arrow-right
"

# Replace known mappings
for pair in $GLYPH_MAP; do
  key=$(echo "$pair" | cut -d: -f1)
  val=$(echo "$pair" | cut -d: -f3-)
  echo "$TARGET_FILES" | xargs -r perl -pi -e "s/\\b${key}\\b/${val}/g"
done

# Any remaining 'glyphicon' occurrences -> fa-question placeholder + TODO
echo "$TARGET_FILES" | xargs -r perl -pi -e 's/\bglyphicon\s+glyphicon-([a-z0-9\-\_]+)\b/<i class="fa fa-question" aria-hidden="true"><\/i><!-- TODO: Replace this glyphicon-$1 with an appropriate Font Awesome icon -->/gi'

# Simple jQuery -> vanilla JS conversions (conservative and safe)
echo "Converting some simple jQuery patterns to vanilla JS (best-effort)."
JSFILES=$(git ls-files | grep -E "\.(js|coffee|jsx|tsx|erb|html)$" || true)
if [ -n "$JSFILES" ]; then
  # Safer literal replacement for the most common form: $(document).ready(function() {
  for f in $JSFILES; do
    # Replace literal "$(document).ready(function() {" with "document.addEventListener('DOMContentLoaded', function() {"
    if grep -q "\$\(document\)\.ready\s*(\s*function\s*(\s*)\s*{" "$f" 2>/dev/null || true; then
      perl -pi -e 's/\$\(\s*document\s*\)\.ready\s*\(\s*function\s*\(\s*\)\s*\{/document.addEventListener('"'"'DOMContentLoaded'"'"', function() {/g' "$f" || true
    fi
    # For other $(document).ready(...) forms we don't attempt complex transformation; insert a TODO
    if grep -q "\$\(document\)\.ready" "$f" 2>/dev/null || true; then
      echo "// TODO: Review and port jQuery $(document).ready(...) usage to vanilla JS (DOMContentLoaded) in $f" >> "$f"
    fi
    # If file contains any remaining occurrences of "$(" add a general TODO at the end
    if grep -q "\$\(" "$f" 2>/dev/null || true; then
      echo "// TODO: File contains remaining jQuery usage; manually port or review." >> "$f"
    fi
  done
fi

# Add TODOs for data-toggle/data-target attributes (Bootstrap 5 changed some APIs)
echo "$TARGET_FILES" | xargs -r perl -0777 -pi -e 's|(<[^>]+(?:data-toggle|data-target)=[^>]+>)|<!-- TODO: Verify data-toggle/data-target usage for Bootstrap 5 (API changed). -->\n\1|gim'

# Ensure package.json not accidentally modified
if git status --porcelain | grep -q "package.json"; then
  echo "Reverting accidental changes to package.json..."
  git checkout -- package.json
fi

echo "Staging changes..."
git add -A

if git diff --cached --quiet; then
  echo "No changes detected after automated pass."
else
  git commit -m "$COMMIT_MSG"
  echo "Committed automated changes to $BRANCH."
fi

echo "Done. Review changes: git diff origin/main..$BRANCH"
echo "When you're ready: git push -u origin $BRANCH"
echo "High-priority manual checks:"
echo " - Navbar structure and toggler behavior"
echo " - Modals, tooltips, popovers (new bootstrap JS API)"
echo " - Input groups and forms (input-group-text, sizing)"
echo " - Glyphicons replaced with Font Awesome placeholders (fa-question) — update as needed"
echo " - Remaining jQuery usages marked with TODO comments"