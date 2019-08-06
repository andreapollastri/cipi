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

## Installation Note
Before you can use Cipi, please make sure your server fulfils these requirements:

- Ubuntu 18.04 x86_64 LTS (Fresh installation)
- If the server is virtual (VPS), OpenVZ may not be supported (Kernel 2.6)

Hardware Requirement: More than 1GB of HDD / At least 1 core processor / 512MB minimum RAM / At least 1 public IP Address (NAT VPS is not supported) / External firewall / For VPS providers such as AWS and AZURE, those providers already include an external firewall for your VPS. Please open port 22, 80 and 443 to install Cipi.

Installation may take up to about 5 minutes minimum which may also depend on your server's internet speed. After the installation is completed, you are ready to use Cipi to manage your servers.

## Installation notes on AWS
AWS by default disables root login. To login as root inside AWS, login as default user and then use command sudo -s.

```
$ ssh ubuntu@<your server IP address>
$ ubuntu@aws:~$ sudo -s
$ root@aws:~# <paste installation script>
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
