web:  vendor/bin/heroku-php-nginx -C nginx.conf  -F fpm_custom.conf public/
release: ./c importmap:install && ./c asset-map:compile && ./c secrets:decrypt-to-local --force && ./c doctrine:migrations:migrate -n --allow-no-migration
