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
PASSWORD=${PASSWORD:-demopass}
CONFIRM_PASSWORD=${CONFIRM_PASSWORD:-demopass}
EMAIL_ADDRESS=${EMAIL_ADDRESS:-"mail@example.com"}
ROOT_FOLDER=/var/www/html

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
" >> $ROOT_FOLDER/.env
fi

# wait for db to be up
wait-for-it.sh $DB_HOST:3306 -t 30 -- echo "db is up or timeout reached, continuing"

# run database migrations
$ROOT_FOLDER/vendor/bin/doctrine-migrations migrate --no-interaction

# create an admin user if there wasn't any user
COUNT=$($ROOT_FOLDER/bin/journalctl user:list | wc -l)
if [ "$COUNT" = "3" ]; then 
    printf "${USERNAME}\n${PASSWORD}\n${CONFIRM_PASSWORD}\n${EMAIL_ADDRESS}\n\n" | $ROOT_FOLDER/bin/journalctl user:create
fi

if [ "${1#-}" != "$1" ]; then
	set -- apache2-foreground "$@"
fi

exec "$@"
