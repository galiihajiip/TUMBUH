#!/usr/bin/env bash
# Prepares env, database and caches, then starts the app.
set -e
cd /var/www/html

[ -f .env ] || cp .env.example .env
if ! grep -q '^APP_KEY=base64' .env && [ -z "${APP_KEY}" ]; then
    php artisan key:generate --force --no-interaction
fi

mkdir -p "$(dirname "$DB_DATABASE")"
FIRST_RUN=false
if [ ! -f "$DB_DATABASE" ]; then
    touch "$DB_DATABASE"
    FIRST_RUN=true
fi

php artisan migrate --force --no-interaction
if [ "$FIRST_RUN" = true ] && [ "${TUMBUH_AUTO_SEED}" = "true" ]; then
    echo "Seeding demo scenarios (illustrative data)…"
    php artisan db:seed --force --no-interaction
fi

php artisan storage:link --force >/dev/null 2>&1 || true
php artisan config:cache >/dev/null
php artisan view:cache >/dev/null

echo "TUMBUH is running on http://localhost:${APP_PORT:-8000}"
exec "$@"
