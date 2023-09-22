FROM php:8.2-fpm-alpine as build-stage-php
WORKDIR /build

RUN apk add curl

COPY ./composer.json ./composer.json
COPY ./composer.lock ./composer.lock

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer install --no-cache --prefer-dist --no-scripts

FROM node:18-alpine as build-stage-node
WORKDIR /build

COPY ./assets ./assets
COPY ./package.json ./package.json
COPY ./yarn.lock ./yarn.lock
COPY ./vite.config.ts ./vite.config.ts
COPY ./tsconfig.node.json ./tsconfig.node.json
COPY ./tsconfig.json ./tsconfig.json
COPY --from=build-stage-php /build/vendor /build/vendor

RUN yarn cache clean --mirror
RUN yarn && yarn build

FROM php:8.2-fpm-alpine
WORKDIR /var/www/html

# Set timezone
ENV TZ="Europe/Prague"

# Nginx & PHP configs
COPY ./docker/nginx/nginx.conf /etc/nginx/nginx.conf
COPY ./docker/nginx/http.d/default.conf /etc/nginx/http.d/default.conf
COPY ./docker/php/php.ini /usr/local/etc/php/conf.d/php.ini
#COPY ./docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

# Install core linux dependencies
RUN apk add openssl curl ca-certificates
RUN apk add bash nano
RUN apk add nginx

# Supported dependencies
# https://github.com/mlocati/docker-php-extension-installer#supported-php-extensions

# Install opcache
RUN docker-php-ext-install opcache

# Install intl
RUN apk add --no-cache icu-dev
RUN docker-php-ext-configure intl
RUN docker-php-ext-install intl

# Install postgres
#RUN apk add --no-cache libpq-dev
#RUN docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql
#RUN docker-php-ext-install pdo pdo_pgsql

# Intall GD
#RUN apk add --no-cache freetype libpng libjpeg-turbo freetype-dev libpng-dev libjpeg-turbo-dev
#RUN docker-php-ext-configure gd --with-freetype --with-jpeg
#RUN NPROC=$(grep -c ^processor /proc/cpuinfo 2>/dev/null || 1)
#RUN docker-php-ext-install -j$(nproc) gd
#RUN apk del --no-cache freetype-dev libpng-dev libjpeg-turbo-dev

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copy source code & set permissions
COPY . ./
COPY --from=build-stage-php /build/vendor ./vendor
COPY --from=build-stage-node /build/www/temp ./www/temp

# Resolve permissions
RUN chmod -R ugo+w ./temp
RUN chmod -R ugo+w ./log
RUN chmod -R ugo+r ./www/temp
RUN chown -R www-data:www-data /var/www/html

# Add entrypoint
ADD ./docker-entrypoint.sh /docker-entrypoint.sh
RUN chmod +x /docker-entrypoint.sh

ENTRYPOINT ["/docker-entrypoint.sh"]