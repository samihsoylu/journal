#!/bin/sh
set -e

# Default values
DEBUG_MODE=${DEBUG_MODE:-false}
USE_SSL=${USE_SSL:-false}
BASE_URL=${BASE_URL:-/}
DB_HOST=${DB_HOST:-localhost}
DB_SCHEMA=${DB_SCHEMA:-testdb}
DB_USERNAME=${DB_USERNAME:-testuser}
DB_PASSWORD=${DB_PASSWORD:-testpass}
SITE_TITLE=${SITE_TITLE:-Journal}
USERNAME=${USERNAME:-demouser}
PASSWORD=${PASSWORD:-journal-demo-password}
CONFIRM_PASSWORD=$PASSWORD
EMAIL_ADDRESS=${EMAIL_ADDRESS:-"mail@example.com"}
ROOT_FOLDER=/var/www/html
IMAGE_UPLOAD_SIZE_LIMIT=${IMAGE_UPLOAD_SIZE_LIMIT:-1}
case "$IMAGE_UPLOAD_SIZE_LIMIT" in
    ''|*[!0-9]*) echo 'IMAGE_UPLOAD_SIZE_LIMIT must be an integer from 1 to 100 MB.' >&2; exit 1 ;;
esac
if [ "$IMAGE_UPLOAD_SIZE_LIMIT" -lt 1 ] || [ "$IMAGE_UPLOAD_SIZE_LIMIT" -gt 100 ]; then
    echo 'IMAGE_UPLOAD_SIZE_LIMIT must be an integer from 1 to 100 MB.' >&2
    exit 1
fi
# PHP parses request bodies before application code runs. Set these at startup,
# allowing room for the multipart boundary and CSRF field.
printf 'upload_max_filesize = %sM\npost_max_size = %sM\n' "$IMAGE_UPLOAD_SIZE_LIMIT" "$((IMAGE_UPLOAD_SIZE_LIMIT + 1))" > "$PHP_INI_DIR/conf.d/uploads.ini"

# Create a .env file if doesn't exist
if [ ! -f $ROOT_FOLDER/.env ]; then
    echo "DEBUG_MODE = \"$DEBUG_MODE\"
USE_SSL = \"$USE_SSL\"
BASE_URL = \"$BASE_URL\"
DB_HOST = \"$DB_HOST\"
DB_SCHEMA = \"$DB_SCHEMA\"
DB_USERNAME = \"$DB_USERNAME\"
DB_PASSWORD = \"$DB_PASSWORD\"
SITE_TITLE = \"$SITE_TITLE\"
ADMIN_EMAIL_ADDRESS = \"$EMAIL_ADDRESS\"
IMAGE_UPLOAD_SIZE_LIMIT = \"$IMAGE_UPLOAD_SIZE_LIMIT\"
" >> $ROOT_FOLDER/.env
fi

# wait for db to be up
wait-for-it.sh $DB_HOST:3306 -t 30 -- echo "db is up or timeout reached, continuing"

# CLI initialization and Apache must share ownership of writable cache files.
# Resolving services even for user:list creates the session cache namespace.
mkdir -p "$ROOT_FOLDER/private/cache/export/log"
chown -R www-data:www-data "$ROOT_FOLDER/private/cache"

# run database migrations
runuser -u www-data -- "$ROOT_FOLDER/vendor/bin/doctrine-migrations" migrate --no-interaction

# create an admin user if there wasn't any user
COUNT=$(runuser -u www-data -- "$ROOT_FOLDER/bin/console" user:list | wc -l)
if [ "$COUNT" = "3" ]; then 
    printf '%s\n' "$USERNAME" "$PASSWORD" "$CONFIRM_PASSWORD" "$EMAIL_ADDRESS" '' | runuser -u www-data -- "$ROOT_FOLDER/bin/console" user:create
fi

if [ "${1#-}" != "$1" ]; then
	set -- apache2-foreground "$@"
fi

exec "$@"
