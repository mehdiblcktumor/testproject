#!/bin/sh
set -eu
php /usr/local/bin/generate-config.php
exec docker-php-entrypoint "$@"
