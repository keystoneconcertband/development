# Keystone Concert Band
Development Repo for KCB Website

This repository contains the main website for the Keystone Concert Band. The project has been updated to use Composer for dependency management in the root project.

## Composer Setup

The application now installs dependencies from Composer and loads `PHPMailer` through `vendor/autoload.php`.

To install dependencies locally:

    composer install

The `vendor/` directory is ignored by Git and should not be committed.

## Notes

- PHPMailer is now managed via Composer.
- Bootstrap/Bootswatch assets have been migrated away from local bundled copies where possible.

## Application Layout

The application uses a feature-oriented structure while preserving the existing public URLs:

- `src/Shared/` contains reusable classes and helpers.
- `src/Public/Handlers/` contains public form and authentication handlers.
- `src/Public/Pages/` contains public page implementations.
- `src/Members/` contains member pages grouped by feature, plus shared handlers.
- `templates/partials/` contains reusable page fragments.
- `public/assets/` contains browser-served CSS, images, JavaScript, and media.
- `tools/` contains isolated third-party utilities such as Tiny File Manager.
- `public/` contains browser-facing entry points and static files. New implementation code should be added under `src/` rather than directly in `public/`.

## Azure App Service

The web root is `public/`. For the Linux PHP App Service container, set the Startup Command to:

    cp /home/site/wwwroot/default /etc/nginx/sites-available/default && service nginx reload

The committed `default` Nginx configuration points the site root at `/home/site/wwwroot/public`. This keeps application code, templates, Composer dependencies, and tools outside the web root.
