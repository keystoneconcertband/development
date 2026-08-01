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
- The application still uses some remaining local assets such as `jquery.mb.miniAudioPlayer` until those are migrated.
