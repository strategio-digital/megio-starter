#!/bin/sh

# migrate databases
su-exec www-data php bin/console migration:migrate --no-interaction
su-exec www-data php bin/console orm:generate-proxies
su-exec www-data php bin/console app:auth:resources:update

# rescuable queue worker
start_queue_worker() {
  worker_command="$1"

  while true; do
    echo "Starting queue worker '$worker_command'..."
    su-exec www-data nohup $worker_command >>"/var/www/html/log/queue-worker.log" 2>&1
    sleep 5
  done &
}

# start queue workers
echo "Enabled queue workers: $QUEUE_WORKERS_ENABLED"
if [ "$QUEUE_WORKERS_ENABLED" = "true" ]; then
  echo "Starting queue workers..."
  #start_queue_worker "php bin/console app:queue example.worker"
  #start_queue_worker "php bin/console app:queue example.worker-two"
fi

# start php-fpm and nginx
php-fpm -D && nginx -g 'daemon off;'
