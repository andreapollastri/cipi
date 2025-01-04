cd /var/www/html && git stash
cd /var/www/html && git checkout 4.x
cd /var/www/html && git fetch
cd /var/www/html && git pull
sudo chmod -R o+w /var/www/html/storage
sudo chmod -R 775 /var/www/html/storage
sudo chmod -R o+w /var/www/html/bootstrap/cache
sudo chmod -R 775 /var/www/html/bootstrap/cache
cd /var/www/html && composer update --no-interaction
cd /var/www/html && php artisan optimize
cd /var/www/html && php artisan migrate --force
sudo chown -R www-data:www-data /var/www/html
sudo chmod -R 755 /var/www/html
