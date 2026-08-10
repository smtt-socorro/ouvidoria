#!/bin/sh
set -eu

CONFIG_DIR=/var/www/hesk-config
CONFIG_FILE="$CONFIG_DIR/hesk_settings.inc.php"

mkdir -p "$CONFIG_DIR"
mkdir -p /var/www/html/anexos /var/www/html/cache2

# Cria a configuração somente na primeira execução.
if [ ! -f "$CONFIG_FILE" ]; then
    cp /var/www/html/hesk_settings.example.inc.php "$CONFIG_FILE"
fi

# O HESK continua procurando o arquivo no local padrão,
# mas o conteúdo real fica no diretório persistente.
rm -f /var/www/html/hesk_settings.inc.php
ln -s "$CONFIG_FILE" /var/www/html/hesk_settings.inc.php

# Linux/VPS
chown www-data:www-data "$CONFIG_FILE" 2>/dev/null || true
chmod 640 "$CONFIG_FILE" 2>/dev/null || true

chown -R www-data:www-data /var/www/html/anexos /var/www/html/cache2 2>/dev/null || true

exec docker-php-entrypoint "$@"