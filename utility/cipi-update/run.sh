cd /var/www/html && git stash
cd /var/www/html && git checkout 3.x
cd /var/www/html && git fetch
cd /var/www/html && git pull
sudo chmod -R o+w /var/www/html/storage
sudo chmod -R 775 /var/www/html/storage
sudo chmod -R o+w /var/www/html/bootstrap/cache
sudo chmod -R 775 /var/www/html/bootstrap/cache
cd /var/www/html && composer update --no-interaction
cd /var/www/html && php artisan cache:clear
cd /var/www/html && php artisan view:cache
cd /var/www/html && php artisan config:cache
cd /var/www/html && php artisan migrate --force
sudo chown -R www-data:cipi /var/www/html
sudo chmod -R 750 /var/www/html
