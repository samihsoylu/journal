# Upgrading an existing v1 installation

This platform update keeps the v1 database and encryption format. It does not
require a new database, new accounts, password resets, or re-encryption. Upgrade
the runtime to PHP 8.5 with the ZIP extension and install the committed Composer lockfile. Existing PHP
7/8.0–8.4 runtimes cannot execute this version.

The automated database checks cover MySQL 8.0 and MariaDB 10.11. Rehearse separately
if you run another database version. Do not combine an application upgrade with
a database-engine upgrade or downgrade.
The example Compose file pins MariaDB to the tested 10.11 series. Existing
installations must keep their current engine version; copying the example must
not downgrade a database that already uses a newer version.

## Before replacing the application

1. Record the current application/image version, database version, Compose project
   name, named volumes, bind mounts, and runtime configuration.
2. Back up the database **and uploaded files**. Preserve `.env` and deployment
   configuration privately. A database-only backup does not include images.
3. Rehearse on an isolated copy, with outgoing email and error reporting disabled.
   Confirm that existing credentials, entries, templates, images, and exports work.
4. Keep the previous application/image and PHP runtime available for rollback.

The example Compose file now persists `/var/www/html/uploads` in a named volume.
Existing deployments that already mount this directory should retain that mount.
If uploads currently live only inside the old container, copy them out **before**
recreating it, then populate the new persistent mount with those exact files.
Do not expect a new container or empty volume to recover files from an old
container. Verify file counts/checksums and ownership before removing the old copy.
For the bundled image the application user is `www-data`.

## Apply the update

- Briefly stop application writes and exports.
- Deploy the new code with its locked dependencies and PHP 8.5; retain the existing
  database connection, database volume, upload mount, URL prefix, and HTTPS settings.
- Run `vendor/bin/doctrine-migrations migrate --no-interaction`. The existing six
  v1 migration versions are retained; a fully migrated v1.5.1 database has no new
  application migrations to run. Never run schema-update/reset commands to upgrade.
- Doctrine updates only its `migrations.version` tracking column from 1024 to
  191 characters. All six v1 version names fit, and their records are preserved.
  The rehearsal checks this change separately on both database engines; application
  tables and stored ciphertext remain unchanged.
- Run migrations and administration commands as the web application's operating
  system user. Root-owned cache namespaces can otherwise prevent browser login.
  The bundled image handles this during startup.
- Clear only disposable compiled templates and ORM/session caches. Do not remove
  uploads or database storage. Users may need to log in again after cache cleanup.
- Start the application and verify login, an existing entry/template/image,
  password changes, and export. Check HTTPS redirects and secure cookies when
  running behind a reverse proxy.

Both `bin/console` and the v1-compatible `bin/journalctl` support administration
commands. The former development-only interactive `bin/shell` is not included.

Exports retain ZIP downloads and offline HTML/media. Entry and category IDs now
distinguish archive paths so matching/sanitized titles cannot overwrite content.
Archive construction uses PHP ZIP and publishes only completed files; failures
remove partial archives. Existing database content and encryption are unchanged.
For usernames containing spaces or other special characters, old cached ZIPs
may no longer appear in the export list because the new filename preserves the
exact username without unsafe path characters. Those files are not deleted;
keep a copy or create a new export. Old sanitized names can match more than one
username, so the app does not guess who owns them.

Keep the existing Compose project and explicit data-volume names. Do not run
`docker compose down -v`, change database storage paths, or point the new app at
an empty database as part of the upgrade.

## Rollback

Stop writes, restore the previous application/image and its compatible PHP
runtime, and clear disposable application caches. This upgrade does not alter
the v1 application tables or stored ciphertext, so the original v1.5.1 code can
read the same data. The automated rehearsal verifies this with synthetic data.

If a separate migration or other change modified the database, restore the
matching database and uploaded-file backup together; account for any writes
made after that backup. The synthetic rehearsal is not a substitute for
verifying your own deployment and backups.

## Reproduce the compatibility checks

Requires PHP 8.5 with curl/ZIP, Docker Compose, Git, tar, Chrome, and a matching
ChromeDriver. Set `CHROME_PATH` and `CHROMEDRIVER_PATH` if they are not discoverable.

```sh
php ci/verify-install-upgrade.php
TEST_DATABASE_IMAGE=mariadb:10.11 php ci/verify-install-upgrade.php --skip-build
```

This builds the candidate without publishing, builds the pinned v1.5.1 source
with its original dependencies, and uses disposable Docker databases/volumes
containing synthetic users, encrypted HTML/Markdown entries, templates, and media.
It compares application schema/data dumps before and after upgrade, checks the
migration tracking table separately, and tests login, content access, export,
ownership boundaries, restart/recreation, and
readback with the old runtime, including entries edited/created by the new app.
It also verifies startup, demo login, and recreation with the example Compose
configuration using the local candidate image. Only resources it creates are removed.
The Chrome test pastes an image into TinyMCE, checks its local blob preview,
uploads it through the real handler, saves the entry, and reloads the image.
CI runs the full rehearsal on both MySQL 8.0 and MariaDB 10.11.

The old runtime is test-only and must not be deployed or exposed publicly.
No production credentials, data, or services are used.
