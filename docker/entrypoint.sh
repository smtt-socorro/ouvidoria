#!/bin/sh
set -eu

cp /var/www/html/hesk_settings.example.inc.php /var/www/html/hesk_settings.inc.php
chown www-data:www-data /var/www/html/hesk_settings.inc.php
chmod 640 /var/www/html/hesk_settings.inc.php
mkdir -p /var/www/html/anexos /var/www/html/cache2
chown -R www-data:www-data /var/www/html/anexos /var/www/html/cache2

exec docker-php-entrypoint "$@"
