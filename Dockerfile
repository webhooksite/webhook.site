##############################################
# Stage 1: Install node dependencies and run gulp
##############################################

FROM node:11 AS npm
WORKDIR /app

COPY package.json /app
COPY package-lock.json /app
RUN npm install

COPY resources /app/resources
COPY gulpfile.js /app
RUN npm run gulp

##############################################
# Stage 2: Composer, nginx and fpm
##############################################

FROM bkuhl/fpm-nginx:7.4

# The base image installs the amd64 s6 overlay on both platforms. Replace it
# with the matching ARM64 release, keeping the base's s6 version and services.
RUN if [ "$(apk --print-arch)" = "aarch64" ]; then \
        curl -fsSL https://github.com/just-containers/s6-overlay/releases/download/v2.1.0.2/s6-overlay-aarch64.tar.gz -o /tmp/s6-overlay-aarch64.tar.gz \
        && echo "6783da208a23141e09cf9e02e923c4f01555b83d5cb33bf32d226b4b1edbaf7c  /tmp/s6-overlay-aarch64.tar.gz" | sha256sum -c - \
        && tar -xzf /tmp/s6-overlay-aarch64.tar.gz -C / \
        && rm /tmp/s6-overlay-aarch64.tar.gz; \
    fi

WORKDIR /var/www/html

# Contains laravel echo server proxy configuration
COPY /nginx.conf /etc/nginx/conf.d

USER www-data

ADD --chown=www-data:www-data /composer.json /var/www/html
ADD --chown=www-data:www-data /composer.lock /var/www/html

RUN composer install --no-interaction --no-autoloader --no-dev --prefer-dist --no-scripts \
    && rm -rf /home/www-data/.composer/cache

ADD --chown=www-data:www-data /storage /var/www/html/storage
ADD --chown=www-data:www-data /bootstrap /var/www/html/bootstrap
ADD --chown=www-data:www-data /public /var/www/html/public
ADD --chown=www-data:www-data /artisan /var/www/html
ADD --chown=www-data:www-data /database /var/www/html/database
ADD --chown=www-data:www-data /config /var/www/html/config
ADD --chown=www-data:www-data /app /var/www/html/app

RUN composer dump-autoload --optimize --no-dev \
    && touch /var/www/html/database/database.sqlite \
    && php artisan optimize \
    && php artisan migrate

ADD --chown=www-data:www-data /resources /var/www/html/resources
COPY --chown=www-data:www-data --from=npm /app/public/css /var/www/html/public/css
COPY --chown=www-data:www-data --from=npm /app/public/js /var/www/html/public/js

USER root