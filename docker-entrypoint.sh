#!/usr/bin/env sh
set -eu

envsubst '${LARAVEL_ECHO_SERVER_HOST}' < /etc/nginx/nginx.conf.template > /etc/nginx/conf.d/nginx.conf

# Start PHP-FPM in the background
php-fpm -D

# Start Nginx in the foreground
exec nginx -g 'daemon off;'
