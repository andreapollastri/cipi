# CIPI - Cloud Control Panel
### Laravel Based VPS manager
Install PHP 7.3, MySql 5.7, phpmyadmin, Let's Encrypt, fail2ban, npm and other with a click.

More info on [https://cipi.sh](https://cipi.sh)


### There are two ways to install Cipi.

#### Autoinstall Script
The first way is run an autoinstall script on a Ubuntu 18.04 LTS based VPS:
```
wget -O - https://cipi.sh/go.sh | bash
```
After installation, you can change your personal data and password in the profile section.
You can configure a SMTP provider into /cipi/.env file.

#### Laravel Installation
The second way is install Cipi Laravel Project into an hosting:

- Cloning the git
```
git clone https://github.com/andreapollastri/cipi.git <install-directory>
cd <install-directory>
composer install
```
Using composer
```
composer create-project andreapollastri/cipi <install-directory>
cd <install-directory>
npm install
```

## Database
Create a new database
```
mysql -uroot -p
mysql> create database yourDatabaseName;
mysql> quit;
```

- Then run `cp .env.example .env` and update your database creds.
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=yourDatabaseName
DB_USERNAME=root
DB_PASSWORD=root
```
(Into .env file you can also config an SMTP provider and customize initial username and password)


- At the end run this commands:
```
php artisan migrate:fresh --seed
php artisan key:generate
php artisan storage:link
```


## Cipi tech
Cipi was developed with:
- Laravel 5.8 (https://laravel.com/)
- SB Admin 2 (https://startbootstrap.com/themes/sb-admin-2/)
- Datatable JS (https://datatables.net/)

 

Enjoy Cipi :)
