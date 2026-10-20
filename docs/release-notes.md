# Next v1 platform update

This update brings Journal's runtime and libraries up to date while keeping
existing accounts, encrypted content, and uploaded images usable.

When the next version tag is published, the Docker `latest` image will move to
**PHP 8.5**. Merging to master does not publish an image. Pin a version tag if
you need to choose when to upgrade. Source installations also need PHP 8.5 and ZIP.

Back up both the database and uploads before upgrading. Keep the current database
engine version and storage mounts. No database reset or password reset is needed.
Doctrine shortens its migration-tracking version column to 191 characters;
existing v1 migration records and application tables are preserved.

See [the upgrade steps](upgrading.md) for upload persistence, checks, and rollback.
