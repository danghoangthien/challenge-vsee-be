web: vendor/bin/heroku-php-nginx -C nginx.conf public/
worker: php artisan queue:work redis --tries=3 --queue=default 