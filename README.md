# Keystone Concert Band

Development Repository for the Keystone Concert Band (KCB) Website.

---

## Architecture & Directory Layout

The application is structured into clearly separated modules for public views, member services, AJAX APIs, and static assets:

```
dev/
├── api/                     # Public AJAX endpoints (booking, join, auth)
├── assets/                  # Consolidated static web assets
│   ├── css/                 # Stylesheets (site.css, member.css)
│   ├── docs/                # Band policies, bylaws, and uniform guidelines
│   ├── images/              # Logos, slide backgrounds, and conductor photos
│   ├── js/                  # Client-side JavaScript modules
│   └── media/               # Audio sample tracks
├── includes/                # Server-side PHP backend components
│   ├── class/               # Domain classes & DB abstraction layers
│   ├── asset.php            # Cache-busting & SRI asset helper
│   ├── nav.php & footer.php # Reusable layout partials
│   └── common_*.php         # Shared metadata, CSS, and JS includes
├── logs/                    # Application runtime logs (HTTP access denied)
├── members/                 # Authenticated member portal
│   ├── api/                 # Member AJAX endpoints (members, schedule, music, etc.)
│   ├── index.php            # Portal dashboard
│   ├── documents.php        # Member document repository & file manager
│   ├── music.php            # Sheet music catalogue
│   └── schedule.php         # Rehearsals and call times
├── roster.php               # Public musician roster page
├── members.php              # Legacy redirect (HTTP 301 to roster.php)
├── index.php                # Public homepage
├── concerts.php             # Public concert schedule
├── conductors.php           # Public conductor bios
├── donate.php               # Public donation details
├── join.php                 # Public membership application form
├── music.php                # Public recording player & track list
├── privacy.php              # Public privacy policy
├── composer.json            # Composer dependency manifest
└── vendor/                  # Managed dependencies (git-ignored)
```

---

## Asset Management & Cache Busting

All local assets are referenced through the `asset()` helper function in `includes/asset.php`:

```php
<link href="<?= asset('/assets/css/site.css') ?>" rel="stylesheet">
<script src="<?= asset('/assets/js/book.js', $integrity) ?>" integrity="<?= $integrity ?>" crossorigin="anonymous"></script>
```

- Appends file modification timestamps (`?v=<mtime>`) for automatic cache-busting.
- Dynamically computes SHA-384 Subresource Integrity (SRI) hashes for scripts and styles.
- Provides fallback resolution for legacy asset paths.

---

## Composer Setup

The project uses Composer for third-party libraries (e.g. `PHPMailer`) and class autoloading.

To install dependencies locally:

```bash
composer install
```

The `vendor/` directory is managed by Composer and should not be committed to Git.

---

## Deployment

The website is continuously deployed to Azure Web Apps via GitHub Actions workflows:
- `dev` branch $\rightarrow$ `webserver-dev`
- `main` branch $\rightarrow$ `webserver-prod`
