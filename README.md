
# Cipi - An Open Source Control Panel for your Cloud!
![GitHub stars](https://img.shields.io/github/stars/andreapollastri/cipi?style=social)
![GitHub watchers](https://img.shields.io/github/watchers/andreapollastri/cipi?style=social)
![GitHub issues](https://img.shields.io/github/issues/andreapollastri/cipi)
![GitHub](https://img.shields.io/github/license/andreapollastri/cipi)
![GitHub tag (latest by date)](https://img.shields.io/github/v/tag/andreapollastri/cipi?label=version)
## About
Cipi is a Laravel based cloud server control panel that supports Digital Ocean, AWS, Vultr, Google Cloud, Linode, Azure and other VPS. It comes with nginx, Mysql, multi PHP-FPM versions, multi users, Supervisor, Composer, npm, free Let's Encrypt certificates, Git deployment, backups, ffmpeg, fail2ban, Redis, API and with a simple graphical interface useful to manage Laravel, Codeigniter, Symfony, WordPress or other PHP applications. With Cipi you don’t need to be a Sys Admin to deploy and manage websites and PHP applications powered by cloud VPS.

## Features
- Easy install: setup one or more servers with a click in less than ten minutes without be a Linux expert.

- Server Management: manage one or more servers in as easy as a few clicks without be a LEMP Guru.

- Perfect stack for PHP devs: Cipi comes with nginx, PHP, MySql, Composer, npm and Supervisor.

- Multi-PHP: Run simultaneous PHP versions at your ease & convenience.

- Secure: no unsed open ports, unprivileged PHP, isolated system users and filesystem, only SFTP (no insecure FTP), Free SSL certificates everywhere.

- Always update: Cipi takes care about your business and automatically keeps your server's software up to date so you always have the latest security patches.

- Integrate Cipi with your own software via Rest API and Swagger.

- Real-time servers stats: Keep an eye on everything through an awesome dashboard.

- Always up to date: Cipi installs last versions of LTS dists and supports Ubuntu 20.04 LTS :)

## Documentation
Cipi Documentation is available at: [https://cipi.sh/docs/](https://cipi.sh/docs/).

## Installation
```
wget -O - https://cipi.sh/go.sh | bash
```
#### Installation on AWS
AWS by default disables root login. To login as root inside AWS, login as default user and then use command sudo -s.

```
$ ssh ubuntu@<your server IP address>
$ ubuntu@aws:~$ sudo -s
$ root@aws:~# wget -O - https://cipi.sh/go.sh | bash
```
Remember to open ports: 22, 80 and 443.
#### Installation Note
Before you can use Cipi, please make sure your server fulfils these requirements:

- Ubuntu 20.04 x86_64 LTS (Fresh installation)
- If the server is virtual (VPS), OpenVZ may not be supported (Kernel 2.6)

Hardware Requirement: More than 1GB of HDD / At least 1 core processor / 512MB minimum RAM / At least 1 public IP Address (NAT VPS is not supported) / External firewall / For VPS providers such as AWS, those providers already include an external firewall for your VPS. Please open port 22, 80 and 443 to install Cipi.

Installation may take up to about 30 minutes which may also depend on your server's internet speed. After the installation is completed, you are ready to use Cipi to manage your servers.

To correctly manage remote servers Cipi has to be on a public IP address... do not use it in localhost!

## Cipi LEMP environment
- nginx: 1.18
- PHP-FPM: 8.0, 7.4, 7.3
- MySql: 8 
- node: 15
- npm: 7
- Composer: 2

## Contributing
Thank you for considering contributing to the Cipi Project (pr, issues, feedbacks, ideas, promo, money, beers) :)

## Problem with Cipi?
Please open an issue here: https://github.com/andreapollastri/cipi/issues.

## Security Vulnerabilities
If you discover a security vulnerability within Cipi, please open an issue.

All security vulnerabilities will be promptly addressed.

## Licence
Cipi is an open-source software licensed under the MIT license.

## Why use Cipi?
Cipi is easy, stable, powerful and free for any personal and commercial use and it's a perfect alternative to Runcloud, Ploi.io, Serverpilot, Forge, Moss.sh and similar software...

### Enjoy Cipi :)
https://cipi.sh
