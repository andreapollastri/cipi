# Cipi - An Open Source Control Panel for your Cloud!
![](http://cipi.sh/home/assets/images/home.gif)


![GitHub stars](https://img.shields.io/github/stars/andreapollastri/cipi?style=social)
![GitHub watchers](https://img.shields.io/github/watchers/andreapollastri/cipi?style=social)
![GitHub issues](https://img.shields.io/github/issues/andreapollastri/cipi)
![GitHub](https://img.shields.io/github/license/andreapollastri/cipi)
![GitHub tag (latest by date)](https://img.shields.io/github/v/tag/andreapollastri/cipi?label=version)

## 2020.07.05 > Cipi Version 3 Development as been started!
Visit https://github.com/andreapollastri/cipi/projects for Roadmap!

## About
Cipi is a Laravel based cloud server control panel that supports Digital Ocean, AWS, Vultr, Google Cloud, Linode, Azure and other VPS. It comes with nginx, Mysql, multi PHP-FPM versions, multi users, Supervisor, Composer, npm, free Let's Encrypt certificates, Git deployment, backups, postfix, phpmyadmin, fail2ban, Redis, API, data migration and with a simple graphical interface useful to manage Laravel, Codeigniter, Symphony, WordPress or other PHP applications. With Cipi you don’t need to be a Sys Admin to deploy and manage websites and PHP applications powered by cloud VPS.

## Features
- Easy install: setup one or more servers with a click in less than ten minutes without be a Linux expert.

- Server Management: manage one or more servers in as easy as a few clicks without be a LEMP Guru.

- Perfect stack for PHP devs: Cipi comes with nginx, PHP, MySql, Composer, npm and Supervisor.

- Multi-PHP: Run simultaneous PHP versions at your ease & convenience.

- Secure: no unsed open ports, unprivileged PHP, isolated system users and filesystem, only SFTP (no insecure FTP), Free SSL certificates everywhere.

- Always update: Cipi takes care about your business and automatically keeps your server's software up to date so you always have the latest security patches.

- Real-time servers stats: Keep an eye on everything through an awesome dashboard.

- Always up to date: Cipi installs last versions of LTS dists and supports Ubuntu 20.04 LTS :)

## Documentation
Cipi Documentation is available at: [https://cipi.sh/docs/](https://cipi.sh/docs/).

## Installation
There are two suggested ways to install Cipi:
- Via Autoinstall Script as a standalone Control Panel on a VPS
- Via Composer as a simple Laravel project into any shared/not shared hosting

### Via Autoinstall Script (Standalone)
The best way to install Cipi is running this autoinstall script on a VPS with Ubuntu 18.04 LTS or 20.04 LTS (fresh installation):
```
wget -O - https://cipi.sh/go.sh | bash
```
At the end of installation process, Cipi will show some password that you have to conserve.


#### Installation Note
Before you can use Cipi, please make sure your server fulfils these requirements:

- Ubuntu 18.04 or 20.04 x86_64 LTS (Fresh installation)
- If the server is virtual (VPS), OpenVZ may not be supported (Kernel 2.6)

Hardware Requirement: More than 1GB of HDD / At least 1 core processor / 512MB minimum RAM / At least 1 public IP Address (NAT VPS is not supported) / External firewall / For VPS providers such as AWS, those providers already include an external firewall for your VPS. Please open port 22, 80 and 443 to install Cipi.

Installation may take up to about ten minutes which may also depend on your server's internet speed. After the installation is completed, you are ready to use Cipi to manage your servers.

To correctly manage remote servers Cipi has to be on a public IP address... do not use it in localhost!

#### Installation notes on AWS
AWS by default disables root login. To login as root inside AWS, login as default user and then use command sudo -s.

```
$ ssh ubuntu@<your server IP address>
$ ubuntu@aws:~$ sudo -s
$ root@aws:~# wget -O - https://cipi.sh/go.sh | bash
```

### Via Composer (Laravel Project)
Cipi is a Laravel based project so you can install it in any virtualhost (shared or not shared) using Composer:
```
composer create-project andreapollastri/cipi <your-folder>
Copy .env.example file in .env and compile your DB and URL data
php artisan key:generate
php artisan config:cache
php artisan view:cache
php artisan migrate --seed
```
Cipi has to be online to work remotely with its clients servers.


## Cipi tech
Cipi was developed with:
- Laravel 7 (https://laravel.com/)
- SB Admin 2 (https://startbootstrap.com/themes/sb-admin-2/)
- Datatable JS (https://datatables.net/)

## Cipi LEMP environment
- nginx: 1.14 (on Ubuntu 18.04) / 1.17 (on Ubuntu 20.04)
- PHP-FPM: 7.4, 7.3, 7.2
- MySql: 5.7 (on Ubuntu 18.04) / 8 (on Ubuntu 20.04)
- node: 12 (on Ubuntu 18.04) / 14 (on Ubuntu 20.04)
- npm: 6
- Composer: 1.10

## Roadmap
You can follow Cipi Project RoadMap here: https://github.com/andreapollastri/cipi/projects/

## Contributing
Thank you for considering contributing to the Cipi Project (pr, issues, feedbacks, ideas, code, promo, money, beers) :)

## Problem with Cipi?
Please open an issue or write and e-mail to andrea@pollastri.dev.

## Security Vulnerabilities
If you discover a security vulnerability within Cipi, please open an issue or send an e-mail to andrea@pollastri.dev.

All security vulnerabilities will be promptly addressed.

## Licence
Cipi is an open-source software licensed under the MIT license.

## Why use Cipi?
Cipi is easy, stable, powerful and free for any personal and commercial use and it's a perfect alternative to Runcloud, Ploi.io, Serverpilot, Forge, Moss.sh and similar software...

### Enjoy Cipi :)
https://cipi.sh
