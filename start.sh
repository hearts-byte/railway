#!/bin/bash
set -e

rm -f /etc/apache2/mods-enabled/mpm_event.load /etc/apache2/mods-enabled/mpm_event.conf
rm -f /etc/apache2/mods-enabled/mpm_worker.load /etc/apache2/mods-enabled/mpm_worker.conf
a2enmod mpm_prefork 2>/dev/null || true

PORT=${PORT:-80}
echo ">>> Starting Apache on port: ${PORT}"

sed -i "s/Listen 80/Listen ${PORT}/g" /etc/apache2/ports.conf
sed -i "s/:80>/:${PORT}>/g" /etc/apache2/sites-enabled/000-default.conf

echo ">>> ports.conf content:"
cat /etc/apache2/ports.conf
echo ">>> vhost content:"
cat /etc/apache2/sites-enabled/000-default.conf

exec apache2-foreground
