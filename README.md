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

## Local Development & Docker Testing

The application runs locally in a Docker LAMP container exposing Apache and MySQL:

- **Web Access**: Navigate to `http://localhost` in your browser.
- **Testing the Daily Log Summary**:
  - In a browser or via `curl`:
    ```bash
    # View summary with dry-run output in plain text
    curl "http://localhost/scripts/daily_log_summary.php?dry-run=1"

    # Run and send issue alert email if issues occurred
    curl "http://localhost/scripts/daily_log_summary.php"

    # Test the cron API endpoint
    curl "http://localhost/api/cron/daily_log_summary.php?key=kcb-daily-summary-token"
    ```
  - Via Docker CLI:
    ```bash
    docker exec -it <container_name> php /app/scripts/daily_log_summary.php --dry-run
    ```

---

## Composer Setup

The project uses Composer for third-party libraries (e.g. `PHPMailer`, `Monolog`) and class autoloading.

To install dependencies locally:

```bash
composer install
```

The `vendor/` directory is managed by Composer and should not be committed to Git.

---

## Logging & Daily Issue Alerting

The application uses **Monolog** with PSR-3 compliance via `KcbLogger` (`includes/class/kcbLogger.class.php`) and a backward-compatible `Log` facade:
- **Daily Rotating Logs**: Stored as `logs/kcb-YYYY-MM-DD.log` using atomic thread-safe append locks (`LOCK_EX`).
- **Retention**: Older log files beyond 30 days are purged automatically.
- **Global Error Handling**: Uncaught exceptions, PHP warnings/notices, and fatal script terminations are captured automatically.
- **Daily Status & Issue Digest**: A GitHub Actions workflow (`.github/workflows/daily-log-summary.yml`) runs daily at 7:00 AM Eastern Time to trigger `api/cron/daily_log_summary.php`. A comprehensive HTML daily status digest is always emailed to `webmaster@keystoneconcertband.com` (or `ALERT_EMAIL_RECIPIENT`), reporting member logins for the day and full categorized breakdowns of any issues detected (or confirming all systems are healthy if 0 issues occurred).
- **Abuse Prevention & Security**:
  - **Single Email Per Day Lock**: Enforces a daily lock (`logs/.sent_YYYY-MM-DD.lock`) so subsequent executions on the same day skip email dispatch, preventing duplicate emails or quota exhaustion from repeated hits. Use `--force` (or `&force=1` with valid key) to explicitly override.
  - **Public Web Access Blocking**: `scripts/.htaccess` blocks all public internet access to the `scripts/` directory at the Apache layer (allowing only local/private subnets).
  - **Token & Role Verification**: `api/cron/daily_log_summary.php` requires a valid authentication key (`?key=...` or `Authorization: Bearer <key>`) matching `APPSETTING_CRON_KEY`. In `scripts/daily_log_summary.php`, non-CLI web execution requires an active admin session, a valid key, or local development environment.
  - **Rate Limiting**: Limits requests to 10 per hour per IP to guard against brute-force or denial-of-service attempts.
  - **Security Auditing**: Unauthorized access attempts are recorded to the daily log as `WARNING` events with client IP and request details.
- **CLI & Web Execution**: The summary script can be triggered via CLI inside the container or directly over HTTP:
  ```bash
  # Inside Docker container:
  php /app/scripts/daily_log_summary.php [--date=YYYY-MM-DD] [--force] [--dry-run]

  # Or via HTTP:
  curl "http://localhost/scripts/daily_log_summary.php?dry-run=1"
  ```

---

## Deployment

The website is continuously deployed to Azure Web Apps via GitHub Actions workflows:
- `dev` branch $\rightarrow$ `webserver-dev`
- `main` branch $\rightarrow$ `webserver-prod`
