# Upgrading an existing v1 installation

This platform update keeps the v1 database and encryption format. It does not
require a new database, new accounts, password resets, or re-encryption. Upgrade
the runtime to PHP 8.5.12 or newer with the ZIP extension and install the committed Composer lockfile. PHP 8.5.12 includes the critical October 2026 security fixes. Existing PHP
7/8.0–8.4 runtimes cannot execute this version.

The automated database checks cover MySQL 5.7/8.0 and MariaDB 10.4/10.11. Rehearse separately
if you run another database version. The older engines are compatibility targets,
not recommendations for new installations; use a maintained database release.
Do not combine an application upgrade with
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

Existing passwords continue to work. New accounts and password changes require
at least 12 characters (maximum 1024 bytes). Password or privilege changes and
account deletion revoke existing sessions; users sign in again. Old session
caches also require a fresh login after the update.

## Apply the update

- Briefly stop application writes and exports.
- Deploy the new code with its locked dependencies and PHP 8.5.12 or newer; retain the existing
  database connection, database volume, upload mount, URL prefix, and HTTPS settings.
- Run `vendor/bin/doctrine-migrations migrate --no-interaction`. The existing six
  v1 migration versions are retained. Two new migrations add the nullable
  `templates.defaultEntryTitlePattern` column and the `user_two_factor` /
  `user_two_factor_recovery` tables. Existing template defaults stay empty and
  existing accounts stay password-only. No existing content or keys are rewritten.
  Never run schema-update/reset commands to upgrade.
- Doctrine also updates its `migrations.version` tracking column from 1024 to
  191 characters. All six v1 version names fit, and their records are preserved.
  The rehearsal checks this separately on each database engine, along with every
  original column, schema definition, stored value and original migration record.
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

New export ZIPs are stored under the account ID. Account deletion takes the
worker's exclusive lock and asks you to wait if an export is still running.
It removes both old and new archive copies before deleting the account, including
when a database can later reuse that account's ID. Existing flat export archives
remain readable and downloadable.
Preserve the entire private export directory if you keep exports during an upgrade.

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

Stop writes and take a fresh database/upload backup. Before reverting below this
version, explicitly disable enrolled authenticators using the new application:
older versions do not enforce two-factor login. This is an authentication downgrade.
While the new code and PHP runtime are still available, run as the application user:

```sh
vendor/bin/doctrine-migrations migrate 'App\Database\Migration\Version20230209195153' --no-interaction
```

This removes the two new security tables and template title patterns. Those new
settings and recovery codes are lost; original journal data and keys are retained.
Then restore the previous application/image and its compatible PHP runtime, and
clear disposable application caches. The rehearsal runs the down migrations and
the original v1.5.1 startup/readback, including entries written by the new app.

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
TEST_DATABASE_IMAGE=mysql:5.7 php ci/verify-install-upgrade.php --skip-build
TEST_DATABASE_IMAGE=mariadb:10.4 php ci/verify-install-upgrade.php --skip-build
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
CI runs the full rehearsal on all four database versions above. The two-factor
checks also race separate database connections to verify one-use counters and
recovery codes, and test private pending login and enrolled-user password changes.

The old runtime is test-only and must not be deployed or exposed publicly.
No production credentials, data, or services are used.

## Browser and security changes

TinyMCE, Materialize and category sorting are updated. JavaScript libraries are
still served locally; no Node build or package install is needed. Image paste,
upload previews and saved media remain supported. The pinned versions and
upstream archive integrity values are in `ci/frontend-dependencies.json`.

Rich entry and template HTML is cleaned when displayed, loaded into the editor,
or exported. Stored encrypted content is not changed by reading it. Scripts,
event handlers, editor control attributes, active SVG and unsafe styles are
removed. Normal formatting, image captions/alignment, tables and local images
are retained; embedded frames are sandboxed. Saving an edited entry stores the
content visible in the editor. Keep a backup if old entries intentionally contain
active HTML that needs to be recovered separately.

Delete and logout endpoints now require POST with the form's CSRF token in its
body. Bookmarks or scripts that used GET deletion URLs must use the application
forms instead. Timezone changes, category sorting and uploads also require CSRF
checks. Private pages, images, template JSON and export downloads use `no-store`.
Optional Sentry reporting sends error types and safe stack locations, without
request bodies, cookies, account details or journal text.

Docker configures PHP upload limits at startup with `IMAGE_UPLOAD_SIZE_LIMIT`
(default 1 MB, maximum 100 MB). Other web servers must configure
`upload_max_filesize` and a larger `post_max_size` in PHP's server configuration;
application-time `ini_set` cannot change how an incoming body was already parsed.
Image types are checked from their contents, not their names.

## New entry and account options

Entries can be dated in the account's timezone, including when editing an entry.
Leaving an existing date unchanged keeps it. Date filters use that same timezone.
Templates can suggest titles with `{date}` (YYYY-MM-DD), `{time}` (HH:MM) and
`{template}`. Selecting a template suggests its category; both title and category
can still be changed before saving. Existing templates require no changes.

The account page links to optional authenticator setup and browser installation.
The PWA requires HTTPS (or localhost), a supporting browser and a server connection.
Only its public offline notice is cached; journal pages, media, keys and account
details are never stored by the service worker for offline use.

Two-factor setup requires the current password and a confirmed authenticator
code. Store the ten one-use recovery codes safely; they are shown once and only
their hashes are kept. There is no email bypass or administrator recovery path.
Disabling or replacing recovery codes requires the password plus a fresh app or
recovery code, and revokes other sessions. Accepted app codes cannot be reused;
wait for the next code after using one to confirm setup or change settings.
Password changes keep the same journal key and authenticator working.

This adds a login check, not a new encryption factor. A database copy plus the
password can still unlock the existing v1 content key and encrypted authenticator
secret. Keep HTTPS enabled and protect server access and backups.

Login limits and export locks require a private local filesystem with working
file locks. The bundled image uses its container filesystem. Do not share cache
directories over a filesystem without reliable locks or run independent web hosts
without a common atomic rate-limit backend. Storage failures reject authentication.

## Docker release changes

The next version tag updates `:latest` to this PHP 8.5 image. Pulling `:latest`
after that release will apply this runtime and its migrations on startup.
Pin a version tag if updates must be deliberate. Merging to master runs checks;
only a new `v*` tag publishes versioned images and updates `latest`.
