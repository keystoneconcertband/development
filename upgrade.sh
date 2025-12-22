#!/usr/bin/env bash
set -euo pipefail

# Wrapper that runs an embedded Python 3 processor to safely perform repo-wide edits.
# Usage: bash ./scripts/upgrade-bootstrap-3-to-5.sh
BRANCH="upgrade/bootstrap-3-to-5"
COMMIT_MSG="chore: upgrade Bootstrap 3 → 5, add Font Awesome, remove jQuery (automated changes)"
BOOTSTRAP_VER="5.3.2"
FONTAWESOME_VER="6.5.0"

if ! command -v python3 >/dev/null 2>&1; then
  echo "python3 is required. Install Python 3 and re-run."
  exit 1
fi

echo "Ensure your working tree is clean..."
if [ -n "$(git status --porcelain)" ]; then
  echo "Working tree not clean. Commit or stash changes first."
  git status --porcelain
  exit 1
fi

python3 - <<'PY'
import re, sys, subprocess, os

BOOTSTRAP_CSS = f'https://cdn.jsdelivr.net/npm/bootstrap@' + os.environ.get('BOOTSTRAP_VER', '5.3.2') + '/dist/css/bootstrap.min.css'
BOOTSTRAP_JS = f'https://cdn.jsdelivr.net/npm/bootstrap@' + os.environ.get('BOOTSTRAP_VER', '5.3.2') + '/dist/js/bootstrap.bundle.min.js'
FONTAWESOME_CSS = f'https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@' + os.environ.get('FONTAWESOME_VER', '6.5.0') + '/css/all.min.css'

# Target file extensions
exts = ('.html', '.erb', '.haml', '.slim', '.eex', '.tpl', '.js', '.coffee', '.jsx', '.tsx', '.css', '.scss', '.less', '.rb', '.yml', '.yaml', '.xml', '.ts')

# Collect tracked files from git
proc = subprocess.run(['git', 'ls-files'], stdout=subprocess.PIPE, check=True, text=True)
all_files = [p for p in proc.stdout.splitlines() if p.endswith(exts)]
print(f"Found {len(all_files)} candidate files to inspect.")

# Prepare class replacements (pattern -> replacement)
class_replacements = [
    (r'\bpanel\b', 'card'),
    (r'\bpanel-heading\b', 'card-header'),
    (r'\bpanel-body\b', 'card-body'),
    (r'\bpanel-footer\b', 'card-footer'),
    (r'\bpanel-title\b', 'card-title'),
    (r'\bwell\b', 'card'),
    (r'\bbtn-default\b', 'btn-secondary'),
    (r'\bbtn-xs\b', 'btn-sm'),
    (r'\bimg-responsive\b', 'img-fluid'),
    (r'\bpull-left\b', 'float-start'),
    (r'\bpull-right\b', 'float-end'),
    (r'\bnavbar-toggle\b', 'navbar-toggler'),
    (r'\binput-group-addon\b', 'input-group-text'),
    (r'\bcenter-block\b', 'mx-auto'),
    (r'\bcol-xs-([0-9]{1,2})\b', r'col-\1'),
]

# Glyphicon -> FA mapping (best-effort)
glyph_map = {
    'glyphicon-search':'fa fa-search',
    'glyphicon-chevron-right':'fa fa-chevron-right',
    'glyphicon-chevron-left':'fa fa-chevron-left',
    'glyphicon-pencil':'fa fa-pen',
    'glyphicon-trash':'fa fa-trash',
    'glyphicon-edit':'fa fa-edit',
    'glyphicon-plus':'fa fa-plus',
    'glyphicon-minus':'fa fa-minus',
    'glyphicon-user':'fa fa-user',
    'glyphicon-lock':'fa fa-lock',
    'glyphicon-envelope':'fa fa-envelope',
    'glyphicon-ok':'fa fa-check',
    'glyphicon-remove':'fa fa-times',
    'glyphicon-arrow-left':'fa fa-arrow-left',
    'glyphicon-arrow-right':'fa fa-arrow-right',
}

# Regex helpers
re_bootstrap_css = re.compile(r'<link\b[^>]*href=(["\'])(?:(?:(?:(?:https?:)?\/\/)?[^"\']*bootstrap[^"\']*(?:/3/|/3\.)[^"\']*))\1[^>]*>', re.IGNORECASE|re.DOTALL)
re_bootstrap_js = re.compile(r'<script\b[^>]*src=(["\'])(?:(?:(?:https?:)?\/\/)?[^"\']*bootstrap[^"\']*(?:/3/|/3\.)[^"\']*)\1[^>]*>.*?</script\s*>', re.IGNORECASE|re.DOTALL)
re_jquery_script = re.compile(r'<script\b[^>]*src=(["\'])(?:(?:(?:https?:)?\/\/)?[^"\']*jquery[^"\']*)\1[^>]*>.*?</script\s*>', re.IGNORECASE|re.DOTALL)
re_data_toggle = re.compile(r'(data-toggle|data-target)\s*=', re.IGNORECASE)

# Detect head tag for inserting Font Awesome
re_head_close = re.compile(r'</head\s*>', re.IGNORECASE)

# jQuery ready pattern (common simple form)
re_doc_ready = re.compile(r'\$\(\s*document\s*\)\.ready\s*\(\s*function\s*\(\s*\)\s*\{', re.IGNORECASE)

changed_files = []

for path in all_files:
    try:
        with open(path, 'r', encoding='utf-8') as fh:
            text = fh.read()
    except Exception:
        # skip files we cannot read as utf-8
        continue

    orig = text
    modified = text

    # Replace bootstrap 3 CDN links/scripts with bootstrap 5 links
    if re_bootstrap_css.search(modified) or re_bootstrap_js.search(modified):
        modified = re_bootstrap_css.sub(f'<link href="{BOOTSTRAP_CSS}" rel="stylesheet" integrity="" crossorigin="anonymous">', modified)
        modified = re_bootstrap_js.sub(f'<script src="{BOOTSTRAP_JS}" integrity="" crossorigin="anonymous"></script>', modified)

    # Add Font Awesome if head present and FA not already present
    if re.search(r'fontawesome|font-awesome|fortawesome', modified, re.IGNORECASE) is None:
        if re_head_close.search(modified):
            modified = re_head_close.sub(f'<link href="{FONTAWESOME_CSS}" rel="stylesheet" integrity="" crossorigin="anonymous">\\n</head>', modified, count=1)

    # Remove jQuery CDN script tags
    if re_jquery_script.search(modified):
        modified = re_jquery_script.sub('', modified)
        # add TODO near head end if head exists
        if re_head_close.search(modified):
            modified = re_head_close.sub('<!-- TODO: jQuery CDN removed. If your app uses jQuery plugins, manually port them. -->\\n</head>', modified, count=1)

    # Class replacements
    for patt, repl in class_replacements:
        try:
            modified = re.sub(patt, repl, modified, flags=re.IGNORECASE)
        except re.error:
            # skip bad patterns
            pass

    # Glyphicon mapping: replace 'glyphicon glyphicon-xxx' first
    # replace combined class token forms
    modified = re.sub(r'\bglyphicon\s+glyphicon-([a-z0-9\-_]+)\b', lambda m: glyph_map.get('glyphicon-'+m.group(1), f'fa fa-{m.group(1)}') , modified, flags=re.IGNORECASE)

    # Replace standalone glyphicon-name occurrences (best-effort)
    for gk, gv in glyph_map.items():
        modified = re.sub(r'\b' + re.escape(gk) + r'\b', gv, modified, flags=re.IGNORECASE)

    # Any remaining 'glyphicon' tokens -> placeholder with TODO
    if re.search(r'\bglyphicon\b', modified, re.IGNORECASE):
        modified = re.sub(r'\bglyphicon\b\s*(?:glyphicon-([a-z0-9\-_]+))?', '<i class="fa fa-question" aria-hidden="true"></i><!-- TODO: Replace this glyphicon with an appropriate Font Awesome icon -->', modified, flags=re.IGNORECASE)

    # Convert common $(document).ready(function() { -> document.addEventListener("DOMContentLoaded", function() {
    if re_doc_ready.search(modified):
        modified = re_doc_ready.sub('document.addEventListener("DOMContentLoaded", function() {', modified)

    # If data-toggle/data-target attributes are present, insert TODO comment immediately before element
    if re_data_toggle.search(modified):
        modified = re.sub(r'(<[^>]*\b(?:data-toggle|data-target)\s*=[^>]*>)', r'<!-- TODO: Verify data-toggle/data-target usage for Bootstrap 5 (API changed). -->\n\1', modified, flags=re.IGNORECASE)

    # For remaining jQuery $(... occurrences, append a TODO at EOF (do not aggressively transform)
    if re.search(r'\$\s*\(', modified):
        # avoid duplicating TODOs
        if '<!-- TODO: Remaining jQuery usages' not in modified:
            modified = modified + '\n\n<!-- TODO: Remaining jQuery usages detected in this file. Manually port to vanilla JS or keep jQuery temporarily. -->\n'

    # If modified, write back
    if modified != orig:
        try:
            with open(path, 'w', encoding='utf-8') as fh:
                fh.write(modified)
            changed_files.append(path)
            print(f"Updated: {path}")
        except Exception as e:
            print(f"Failed to write {path}: {e}", file=sys.stderr)

# Stage and commit if any changes
if changed_files:
    subprocess.run(['git', 'add', '-A'], check=True)
    # If nothing to commit, skip
    commit_proc = subprocess.run(['git', 'diff', '--cached', '--quiet'])
    if commit_proc.returncode == 1:
        subprocess.run(['git', 'commit', '-m', os.environ.get('COMMIT_MSG', "automated changes (bootstrap upgrade)")], check=True)
        print(f"Committed {len(changed_files)} files.")
    else:
        print("No staged changes to commit.")
else:
    print("No files changed by automated pass.")
PY

# Note: the embedded Python used environment variables for versions if you want to override.
# Provide the environment variables to override if desired:
# BOOTSTRAP_VER and FONTAWESOME_VER (but defaults are set inside the Python block above).

echo "Automated pass complete. Review changes:"
git --no-pager diff --name-only origin/main..HEAD || true
echo
echo "If changes look good, push the branch:"
echo "  git push -u origin $(git rev-parse --abbrev-ref HEAD)"
echo
echo "Manual follow-ups (high priority):"
echo " - Verify navbar collapse behavior and toggler markup"
echo " - Verify modals, tooltips, popovers (Bootstrap 5 changed JS API)"
echo " - Verify forms and input-group layout (input-group-text)"
echo " - Replace any fa-question placeholders with proper Font Awesome icons"
echo " - Manually port any remaining jQuery usage (TODOs left in files)"