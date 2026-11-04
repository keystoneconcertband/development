# AGENTS.md — Keystone Concert Band (KCB) Codebase Guide

This file provides a token-efficient, high-density architecture map and operational guide for AI agents working in this repository.

---

## 1. Quick Reference & Technology Stack

| Layer | Technology | Details |
| :--- | :--- | :--- |
| **Runtime** | PHP 8.2+ (tested up to 8.5) | Procedural + OOP, PDO MySQL, strict UTF-8 |
| **Dependencies** | Composer | `monolog/monolog` (^3.7), `phpmailer/phpmailer` (^6.10) in `vendor/` |
| **Frontend** | Bootstrap 5.3 + FontAwesome 6 | Bootswatch *Yeti* theme, responsive layout |
| **Database** | MySQL / MariaDB | Configured via `includes/class/settings.ini.php` |
| **Production Host** | Azure Web Apps (Linux) | **Nginx + PHP-FPM** (`dev` -> `webserver-dev`, `main` -> `webserver-prod`) |
| **Local Dev** | Docker LAMP container | Apache + MySQL on `http://localhost:8080` |
| **Timezone** | `America/New_York` | Enforced globally via `KcbBase` |

---

## 2. Critical Architectural Rules & Gotchas

1. **Azure Web Apps uses Nginx (Not Apache)**:
   - Azure App Service runs **Nginx**; `.htaccess` files and `mod_rewrite` rules are **ignored** in production.
   - Always reference explicit `.php` file paths (`/health.php`, `/concerts.php`) in links, forms, and monitoring probes. Extensionless URLs (e.g. `/health`) return 404 in Azure.
2. **Asset Cache-Busting & SRI**:
   - Never hardcode `/assets/...` URLs in PHP views.
   - Always use `asset('/path/to/asset')` from `includes/asset.php` (appends modification timestamp `?v=<mtime>` and generates SRI hashes).
3. **Database Connections & Error Handling**:
   - Database layer lives in `includes/class/db.class.php` (used by `*.db.class.php`).
   - **Caution**: `new Db()` calls `die()` with an HTML dump on connection failure. For health checks, CLI utilities, or graceful failover, connect via raw `PDO` in a `try/catch` block.
   - PHP 8.4/8.5 compatibility: Use `defined('Pdo\Mysql::ATTR_INIT_COMMAND') ? \Pdo\Mysql::ATTR_INIT_COMMAND : PDO::MYSQL_ATTR_INIT_COMMAND`.
4. **Caching vs. Sessions**:
   - **Public Pages**: Do not call `session_start()` for anonymous visitors. Use `require_once __DIR__ . '/includes/public_cache.php'` to emit public edge cache headers (`max-age=3600`).
   - **Authenticated Member Pages (`members/`)**: Use `new Member(true)` which verifies authentication and starts the session.
   - **Health & API Endpoints**: Must emit strict no-cache headers (`Cache-Control: no-cache, no-store, must-revalidate`).
5. **Security & Input Handling**:
   - Always use PDO prepared statements with parameter binding for SQL queries.
   - Always sanitize output with `htmlspecialchars()` before rendering dynamic values into HTML.
   - Protect webhooks / cron endpoints with `hash_equals()` against `APPSETTING_CRON_KEY` or `CRON_SECRET` and IP rate-limiting.

---

## 3. Directory Layout & Mental Map

```
dev/
├── api/                     # Public AJAX / Webhook endpoints
│   ├── auth.php             # Member login & verification handler
│   ├── book.php             # Public booking request submission
│   ├── join.php             # Public member application submission
│   ├── health.php           # Dedicated JSON health check probe
│   └── cron/                # Scheduled triggers (daily_log_summary.php)
├── assets/                  # Static assets (css, docs, images, js, media)
├── includes/                # Backend server-side includes
│   ├── asset.php            # Cache-busting & SRI helper function asset()
│   ├── common_*.php         # Shared meta, css, js, datatables tags
│   ├── nav.php & footer.php # Layout partials
│   ├── public_cache.php     # Cache-Control headers for anonymous views
│   └── class/               # Domain classes & DB abstraction
│       ├── kcbBase.class.php           # Base application class, error handlers, mailer
│       ├── kcbLogger.class.php         # PSR-3 Monolog wrapper, rotating daily logs
│       ├── dailySummaryService.class.php # Incident scanner and daily digest generator
│       ├── member.class.php & *.db.php # Member authentication & database models
│       └── settings.ini.php            # DB credentials (git-ignored)
├── logs/                    # Daily rotating logs (kcb-YYYY-MM-DD.log)
├── members/                 # Authenticated member area (overview, music, schedule, docs)
│   └── api/                 # Member-only AJAX endpoints
├── scripts/                 # CLI & administrative scripts (daily_log_summary.php)
├── health.php               # System health dashboard (HTML) & JSON probe (?format=json)
├── index.php                # Public homepage
├── concerts.php             # Public concert schedule
├── conductors.php           # Public conductor biographies
├── donate.php               # Public donation page
├── join.php                 # Membership application form
├── music.php                # Public recording player
├── roster.php               # Public musician roster
└── privacy.php              # Privacy policy
```

---

## 4. Key Classes & Patterns

- **`KcbBase` (`includes/class/kcbBase.class.php`)**:
  - Sets Eastern timezone, error reporting, global uncaught exception handlers.
  - Call `new KcbBase(false)` to initialize settings without starting a PHP session.
  - Provides `sendEmail($to, $message, $subject, $html = true)` using PHPMailer via Brevo SMTP (`APPSETTING_BREVO_USERNAME` / `APPSETTING_BREVO_PASSWORD`). Simulates email on localhost.
- **`KcbLogger` (`includes/class/kcbLogger.class.php`)**:
  - Singleton: `KcbLogger::getInstance()`.
  - Daily rotating logs in `logs/kcb-YYYY-MM-DD.log` with atomic append locks (`LOCK_EX`) and 30-day retention.
- **`DailySummaryService` (`includes/class/dailySummaryService.class.php`)**:
  - Scans previous day's log for warnings/errors/criticals and emails a daily HTML summary to `ALERT_EMAIL_RECIPIENT` (or `webmaster@keystoneconcertband.com`).
  - Single email per day lock enforced via `logs/.sent_YYYY-MM-DD.lock`.
- **`Member` (`includes/class/member.class.php`)**:
  - `accountType`: `0` = Musician member, `1` / `2` = Officer / Administrator.

---

## 5. Development & Testing Commands

```bash
# Health check diagnostics
curl "http://localhost/health.php?format=json"
curl "http://localhost/api/health.php"

# Test daily log summary (dry-run without sending email)
curl "http://localhost/scripts/daily_log_summary.php?dry-run=1"
docker exec -it <container> php /app/scripts/daily_log_summary.php --dry-run

# Test cron webhook endpoint
curl "http://localhost/api/cron/daily_log_summary.php?key=kcb-daily-summary-token"

# Composer dependencies (run inside container or environment with PHP installed)
composer install --prefer-dist --no-progress
composer validate --no-check-publish
```

