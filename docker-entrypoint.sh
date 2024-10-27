#!/bin/sh
# remove temp files
#rm -rf /var/www/html/temp/*

# migrate databases
su-exec www-data php bin/console migration:migrate --no-interaction
su-exec www-data php bin/console orm:generate-proxies
su-exec www-data php bin/console app:auth:resources:update

# start php-fpm and nginx
php-fpm -D && nginx -g 'daemon off;'
