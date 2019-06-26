# CIPI - Cloud Control Panel

## About
Cipi is a Laravel Based VPS manager.
It installs with a click: PHP 7.3, MySql 5.7, phpmyadmin, Let's Encrypt, fail2ban, npm and other with a click.
More info on [https://cipi.sh](https://cipi.sh).

## Installation
### There are two ways to install Cipi.

#### Autoinstall Script
The first way is run an autoinstall script on a Ubuntu 18.04 LTS based VPS:
```
wget -O - https://cipi.sh/go.sh | bash
```
After installation, you can change your personal data and password in the profile section.
You can configure a SMTP provider into /cipi/.env file.

#### Composer Installation
- You can install Cipi Laravel Project into an hosting within Composer:

```
composer create-project andreapollastri/cipi <install-directory>
```

- Then run `cp .env.example .env` and update your database creds. (you can also config an SMTP provider and customize initial username and password)


- At the end run this commands:
```
php artisan migrate --seed
php artisan key:generate
php artisan storage:link
```

## Cipi tech
Cipi was developed with:
- Laravel 5.8 (https://laravel.com/)
- SB Admin 2 (https://startbootstrap.com/themes/sb-admin-2/)
- Datatable JS (https://datatables.net/)


## Roadmap
You can follow Cipi Project RoadMap here: https://github.com/andreapollastri/cipi/projects/


## Contributing
Thank you for considering contributing to the Cipi Project!


## Security Vulnerabilities
If you discover a security vulnerability within Cipi, please send an e-mail to Andrea Pollastri via mail@andreapollastri.net. All security vulnerabilities will be promptly addressed.


## Licence
Cipi is open-source software licensed under the MIT license.

### Enjoy Cipi :)
