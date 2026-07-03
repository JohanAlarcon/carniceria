#!/bin/sh
set -e
cd /var/www/html

DB_HOST="${DB_HOST:-db}"
DB_PORT="${DB_PORT:-3306}"

# ---------------------------------------------------------------------------
# 1. Wait for the database to accept TCP connections
# ---------------------------------------------------------------------------
echo "[entrypoint] Waiting for database ${DB_HOST}:${DB_PORT}..."
tries=0
until php -r '$c=@fsockopen(getenv("DB_HOST")?:"db",(int)(getenv("DB_PORT")?:3306),$e,$s,2); exit($c?0:1);' 2>/dev/null; do
    tries=$((tries + 1))
    if [ "$tries" -ge 60 ]; then
        echo "[entrypoint] Database still unreachable after ~2min, continuing anyway."
        break
    fi
    sleep 2
done
echo "[entrypoint] Database reachable."

# ---------------------------------------------------------------------------
# 2. Make sure runtime dirs exist (the storage volume may start empty)
# ---------------------------------------------------------------------------
mkdir -p \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/app/public \
    storage/logs \
    bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# ---------------------------------------------------------------------------
# 3. First-boot database setup (only the "app" container sets RUN_MIGRATIONS)
# ---------------------------------------------------------------------------
if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
    echo "[entrypoint] Running migrations..."
    php artisan migrate --force || echo "[entrypoint] WARNING: migrate failed"

    if [ "${RUN_SEED:-false}" = "true" ]; then
        echo "[entrypoint] Seeding database..."
        php artisan db:seed --force || echo "[entrypoint] WARNING: seed failed"
    fi

    # public/storage -> storage/app/public (idempotent)
    php artisan storage:link 2>/dev/null || true
fi

# ---------------------------------------------------------------------------
# 4. Optimize caches (config + views + Filament; NOT routes — closures present)
# ---------------------------------------------------------------------------
php artisan config:clear >/dev/null 2>&1 || true
php artisan config:cache || true
php artisan view:cache || true
php artisan filament:cache-components 2>/dev/null || true

echo "[entrypoint] Boot complete, starting: $*"
exec "$@"
