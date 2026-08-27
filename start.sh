#!/bin/bash
set -e

rm -f /etc/apache2/mods-enabled/mpm_event.load /etc/apache2/mods-enabled/mpm_event.conf
rm -f /etc/apache2/mods-enabled/mpm_worker.load /etc/apache2/mods-enabled/mpm_worker.conf
a2enmod mpm_prefork 2>/dev/null || true

echo ">>> Fixing permissions on mounted volume"
mkdir -p /var/www/html/upload/avatar_data /var/www/html/upload/cover_data /var/www/html/upload/room_icon_data
mkdir -p /var/www/html/upload/chat /var/www/html/upload/news /var/www/html/upload/private /var/www/html/upload/upload /var/www/html/upload/wall

# نقل أي ملفات موجودة حالياً في avatar/cover/room_icon (القادمة من جيثب) لمجلد الفولوم الدائم أول مرة فقط، ثم تحويلها لـ symlink
if [ -d /var/www/html/avatar ] && [ ! -L /var/www/html/avatar ]; then
    cp -rn /var/www/html/avatar/. /var/www/html/upload/avatar_data/ 2>/dev/null || true
fi
if [ -d /var/www/html/cover ] && [ ! -L /var/www/html/cover ]; then
    cp -rn /var/www/html/cover/. /var/www/html/upload/cover_data/ 2>/dev/null || true
fi
if [ -d /var/www/html/room_icon ] && [ ! -L /var/www/html/room_icon ]; then
    cp -rn /var/www/html/room_icon/. /var/www/html/upload/room_icon_data/ 2>/dev/null || true
fi

rm -rf /var/www/html/avatar /var/www/html/cover /var/www/html/room_icon
ln -sfn /var/www/html/upload/avatar_data /var/www/html/avatar
ln -sfn /var/www/html/upload/cover_data /var/www/html/cover
ln -sfn /var/www/html/upload/room_icon_data /var/www/html/room_icon

chown -R www-data:www-data /var/www/html/upload
chown -h www-data:www-data /var/www/html/avatar /var/www/html/cover /var/www/html/room_icon 2>/dev/null || true
chmod -R 777 /var/www/html/upload

PORT=${PORT:-80}
echo ">>> Starting Apache on port: ${PORT}"

sed -i "s/Listen 80/Listen ${PORT}/g" /etc/apache2/ports.conf
sed -i "s/:80>/:${PORT}>/g" /etc/apache2/sites-enabled/000-default.conf

echo ">>> ports.conf content:"
cat /etc/apache2/ports.conf
echo ">>> vhost content:"
cat /etc/apache2/sites-enabled/000-default.conf

exec apache2-foreground
