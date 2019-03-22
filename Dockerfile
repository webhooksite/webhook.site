# Stage 1: Install node dependencies and run gulp
FROM node:8 as npm
WORKDIR /app
COPY package.json /app
RUN npm install

COPY gulpfile.js /app
COPY resources /app/resources
RUN npm run gulp

# Stage 2: Composer, nginx and fpm
FROM bkuhl/fpm-nginx:fpm-7_nginx-1
WORKDIR /var/www/html

COPY /nginx.conf /etc/nginx/conf.d

USER www-data

RUN touch /var/www/html/database/database.sqlite

ADD --chown=www-data:www-data /composer.json /var/www/html
ADD --chown=www-data:www-data /composer.lock /var/www/html

RUN composer install  --no-interaction --optimize-autoloader --no-dev --prefer-dist \
    && rm -rf /home/www-data/.composer/cache

ADD --chown=www-data:www-data /app /var/www/html/app
ADD --chown=www-data:www-data /bootstrap /var/www/html/bootstrap
ADD --chown=www-data:www-data /config /var/www/html/config
ADD --chown=www-data:www-data /database /var/www/html/database
ADD --chown=www-data:www-data /public /var/www/html/public
ADD --chown=www-data:www-data /resources /var/www/html/resources
ADD --chown=www-data:www-data /storage /var/www/html/storage
ADD --chown=www-data:www-data /artisan /var/www/html

RUN php artisan optimize
RUN php artisan migrate

COPY --chown=www-data:www-data --from=npm /app/public/css /var/www/html/public/css
COPY --chown=www-data:www-data --from=npm /app/public/js /var/www/html/public/js
