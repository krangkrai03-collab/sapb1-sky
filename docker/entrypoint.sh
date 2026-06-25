#!/bin/sh
set -e

# Railway (and most hosts) inject the listening port via $PORT.
PORT="${PORT:-8080}"
sed -ri "s/^Listen .*/Listen ${PORT}/" /etc/apache2/ports.conf
sed -ri "s/<VirtualHost \*:[0-9]+>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

# Apply DB schema (Shield + Settings + app) on boot. Idempotent.
php spark migrate --all -n || echo "migrate failed (will still start)"

exec apache2-foreground
