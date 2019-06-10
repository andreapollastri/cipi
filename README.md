# CIPI
### LARAVEL BASED LAMP VPS MANAGER
Install PHP 7.3, MySql 5.7, phpmyadmin, Let's encrypt, fail2ban, npm and other on an empty Linux Ubuntu VPS.

More info on [http://andreapollastri.dev/cipi](http://andreapollastri.dev/cipi)

#### Installation
- Copy this laravel project into a lamp web-server
- Run: composer install
- Run: cp .env.example .env
- Compile .env file with your data
- Run: php artisan key:generate
- Run: php artisan cache:clear
- Run: php artisan config:cache
- Run: php artisan migrate --seed
- Run: php artisan storage:link
- Run: composer update
- Run: composer dump-autoload
