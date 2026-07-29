# Composer Migration Plan

## Goal
Replace the site’s manually bundled third-party files with Composer-managed dependencies while keeping the application behavior stable.

## Scope
This plan focuses on the main website code under the repository root, especially the PHP mail integration and any CSS/JavaScript assets currently served from the local 3rd-party directory.

## Current observations
- The website currently includes PHPMailer files from the local 3rd-party folder.
- Bootstrap and related assets are also served from local bundled folders.
- The deployment workflow already expects Composer support to be available in CI.

## Implementation plan

### Phase 1: Inventory and decision-making
1. Review the current dependencies loaded from the local 3rd-party directory.
2. Identify which libraries should be migrated first.
3. Decide whether to migrate:
   - PHPMailer first
   - Bootstrap/Bootswatch assets next
   - any remaining local JS/CSS assets later

### Phase 2: Add Composer support for the site root
1. Create a root composer.json for the main website project.
2. Add the initial dependency set, starting with PHPMailer.
3. Configure Composer autoloading for the PHP classes used by the site.
4. Keep the project’s existing structure intact while pointing PHP imports to Composer-managed packages.

### Phase 3: Replace hard-coded PHP includes
1. Update the PHP code that currently requires PHPMailer files from the local 3rd-party folder.
2. Replace those direct file includes with Composer autoloading.
3. Verify that the mail-sending flow still resolves classes correctly.

### Phase 4: Replace hard-coded asset paths
1. Update the site’s CSS and JavaScript includes so they no longer depend on local bundled copies where Composer-managed assets are preferred.
2. Adjust any helper logic that builds asset URLs if the install path changes.
3. Preserve the existing cache-busting behavior where possible.

### Phase 5: Update deployment and environment setup
1. Ensure the deployment workflow installs Composer dependencies as part of the build.
2. Confirm the deployment artifact includes the Composer vendor directory.
3. Document the local setup steps for future developers.

### Phase 6: Validation and cleanup
1. Verify that the mail feature still works after switching to Composer-managed PHPMailer.
2. Smoke-test the pages that rely on Bootstrap or related CSS/JS assets.
3. Remove the old bundled third-party files only after the Composer-based setup is confirmed stable.

## Suggested task checklist
- [ ] Create root composer.json
- [ ] Add PHPMailer dependency
- [ ] Replace manual PHPMailer require statements
- [ ] Add Composer autoload configuration
- [ ] Update CSS/JS asset loading paths
- [ ] Update deployment workflow if needed
- [ ] Add documentation for developers
- [ ] Validate mail sending and page rendering
- [ ] Remove old bundled dependency files after rollout

## Relevant files to review later
- includes/class/kcbBase.class.php
- includes/common_css.php
- includes/common_js.php
- includes/asset.php
- .github/workflows/deploy-azure-dev.yml

## Notes
This file is intentionally a planning artifact only; no implementation changes are included here.
