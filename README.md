# CIPI - Cloud Control Panel
### Laravel Based VPS manager
Install PHP 7.3, MySql 5.7, phpmyadmin, Let's Encrypt, fail2ban, npm and other with a click.

More info on [https://cipi.sh](https://cipi.sh)

## Installation
Cloning the git
```
git clone https://github.com/andreapollastri/cipi.git <install-directory>
cd <install-directory>
composer install
npm install
```
## Database
Creata a new database
```
mysql -uroot -p
mysql> create database yourDatabaseName;
mysql> quit;
```

Then `cp .env.example .env` and update your database creds.
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=yourDatabaseName
DB_USERNAME=root
DB_PASSWORD=root
```

Change (if you want) the initial credential by editing the file `/database/seeds/UsersTableSeeder.php` or use these:

```
email: admin@admin.com
password: 12345678
```

run the migrations with seed
```
php artisan migrate:fresh --seed
```
You can now run the web server

```
php artisan serve
```
