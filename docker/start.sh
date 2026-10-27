#!/bin/sh
set -e

export APP_URL="${APP_URL:-$RENDER_EXTERNAL_URL}"
if [ -z "$APP_KEY" ]; then
    export APP_KEY="base64:$(head -c 32 /dev/urandom | base64)"
fi

touch database/database.sqlite
php artisan migrate --force
php artisan db:seed --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

exec php artisan serve --host 0.0.0.0 --port "${PORT:-10000}"
