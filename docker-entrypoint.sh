#!/bin/sh
# remove temp files
#rm -rf /var/www/html/temp/*

# migrate databases
php bin/console migration:migrate --no-interaction
php bin/console orm:generate-proxies
php bin/console app:auth:resources:update

# override permissions
chown -R www-data:www-data /var/www/html/temp
chown -R www-data:www-data /var/www/html/log
chown -R www-data:www-data /var/www/html/docker/temp

# start php-fpm and nginx
php-fpm -D && nginx -g 'daemon off;'