#!/bin/sh
set -e

# Guarantee a single Apache MPM at runtime (prefork, required by mod_php).
# Done here (not just at build) so it always applies regardless of layer cache.
rm -f /etc/apache2/mods-enabled/mpm_event.load /etc/apache2/mods-enabled/mpm_event.conf \
      /etc/apache2/mods-enabled/mpm_worker.load /etc/apache2/mods-enabled/mpm_worker.conf
a2enmod mpm_prefork >/dev/null 2>&1 || true

# Railway (and most hosts) inject the listening port via $PORT.
PORT="${PORT:-8080}"
sed -ri "s/^Listen .*/Listen ${PORT}/" /etc/apache2/ports.conf
sed -ri "s/<VirtualHost \*:[0-9]+>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

# Apply DB schema (Shield + Settings + app) on boot. Idempotent.
php spark migrate --all -n || echo "migrate failed (will still start)"

exec apache2-foreground
