# Keystone Concert Band
Development repository for the KCB website.

This project uses Composer for dependency management at the site root. The main website code remains in the repository, while third-party PHP libraries are installed from Composer into the `vendor/` directory.

## Setup

Install dependencies locally with:

    composer install

The `vendor/` directory is ignored by Git and should not be committed.

## Composer-managed dependencies

The site currently uses Composer for:

- `phpmailer/phpmailer` for outbound email support
- `phpmyadmin/phpmyadmin` for database administration access

## phpMyAdmin integration

phpMyAdmin should be installed as a Composer dependency rather than copied into the website source tree. The preferred deployment approach is to serve it from a dedicated path or subdomain, such as `/phpmyadmin` or `pma.example.com`, and point that web location to the Composer-installed package under `vendor/phpmyadmin/phpmyadmin`.

This keeps phpMyAdmin easier to update, reduces maintenance overhead, and avoids keeping a second copy of the application in the repository.

## Notes

- PHPMailer is now managed via Composer.
- Bootstrap and related assets have been migrated away from local bundled copies where possible.
- The application still uses some remaining local assets such as `jquery.mb.miniAudioPlayer` until those are migrated.
