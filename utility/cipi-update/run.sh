cd /var/www/html && git stash
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
sudo chown -R www-data:www-data /var/www/html
curl -s 'https://service.cipi.sh/setupcount' > /dev/null