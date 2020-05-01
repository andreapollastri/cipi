# Cipi (version 2 - BETA)

## About
Cipi is a Laravel based cloud server control panel that support Digital Ocean, AWS, Vultr, Google Cloud, Linode, Azure and other VPS. It comes with nginx, Mysql, multi PHP-FPM versions, multi users, Supervisor, Composer, npm, free Let's Encrypt certificates, Git deployment fail2ban and with a simple graphical interface to manage Laravel, Codeigniter, Symphony, WordPress or other PHP applications. With Cipi you don’t need to be a sys admin to build websites and PHP applications powered by cloud VPS.

## Features
- Easy install: Configure a server with a click in less than ten minutes without be an expert.

- Perfect stack for PHP devs: Cipi comes with nginx, PHP 7.4 (and older versions for legacy) MySql 5.7, Composer, npm and Supervisor.

- Server Management: Manage one or more servers in as easy as a few clicks.

- Multi-PHP: Run simultaneous PHP-FPM versions at your ease & convenience.

- Secure: no unsed open ports, unprivileged PHP, isolated system users and filesystem, only SFTP (no insecure FTP), Free SSL certificates everywhere.

- Always update: Cipi takes care about your business and automatically keeps your server's software up to date so you always have the latest security patches.

- Real-time servers stats: Keep an eye on everything through an awesome dashboard.

## Documentation
Cipi Documentation is available at: [https://cipi.sh/docs/](https://cipi.sh/docs/).

## Installation
- The best way to install Cipi is running this autoinstall script on a VPS with Ubuntu 18.04 LTS (fresh installation):
```
wget -O - https://cipi.sh/go.sh | bash
```
NOTE: it doesn't work with IPv6... use only IPv4 and no localhost, VPS has to be online to work remotely with its clients servers.
- But you can also install it via Composer:
```
composer create-project andreapollastri/cipi:2.0.0beta /your-folder
```
- Or you can also Dockerize it (the best way is using https://github.com/andreapollastri/easydock)

#### Installation Note
Before you can use Cipi, please make sure your server fulfils these requirements:

- Ubuntu 18.04 x86_64 LTS (Fresh installation)
- If the server is virtual (VPS), OpenVZ may not be supported (Kernel 2.6)

Hardware Requirement: More than 1GB of HDD / At least 1 core processor / 512MB minimum RAM / At least 1 public IP Address (NAT VPS is not supported) / External firewall / For VPS providers such as AWS, those providers already include an external firewall for your VPS. Please open port 22, 80 and 443 to install Cipi.

Installation may take up to about ten minutes which may also depend on your server's internet speed. After the installation is completed, you are ready to use Cipi to manage your servers.

To correctly manage remote servers Cipi has to be on a public IP address... do not use it in localhost!

#### Installation notes on AWS
AWS by default disables root login. To login as root inside AWS, login as default user and then use command sudo -s.

```
$ ssh ubuntu@<your server IP address>
$ ubuntu@aws:~$ sudo -s
$ root@aws:~# <paste installation script>
```

## Cipi tech
Cipi was developed with:
- Laravel 7 (https://laravel.com/)
- SB Admin 2 (https://startbootstrap.com/themes/sb-admin-2/)
- Datatable JS (https://datatables.net/)

## Roadmap
You can follow Cipi Project RoadMap here: https://github.com/andreapollastri/cipi/projects/

## Contributing
Thank you for considering contributing to the Cipi Project (feedback, code, beers) :)

## Security Vulnerabilities
If you discover a security vulnerability within Cipi, please send an e-mail to andrea@pollastri.dev. All security vulnerabilities will be promptly addressed.

## Licence
Cipi is open-source software licensed under the MIT license.

### Enjoy Cipi :)
