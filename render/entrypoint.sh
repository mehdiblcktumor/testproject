#!/bin/sh
set -eu
PORT_VALUE="${PORT:-10000}"
# Apache must listen on the port Render assigns to this service.
sed -i -E "s/^Listen [0-9]+/Listen ${PORT_VALUE}/" /etc/apache2/ports.conf
sed -i -E "s/<VirtualHost \*:[0-9]+>/<VirtualHost *:${PORT_VALUE}>/" /etc/apache2/sites-available/000-default.conf
php /usr/local/bin/generate-config.php
exec docker-php-entrypoint "$@"
