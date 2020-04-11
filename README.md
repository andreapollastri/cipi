# CIPI - (VERSION 2 IS COMING... 1th May, 2020) :)

## About
Cipi is a Laravel Based VPS manager.
It installs with a click: PHP 7.3, MySql 5.7, phpmyadmin, Let's Encrypt, fail2ban, npm and other with a click.
More info on [https://cipi.sh](https://cipi.sh).


## Documentation
Cipi Documentation is available at: [https://cipi.sh/docs/](https://cipi.sh/docs/).


## Installation

The best way to install Cipi is running this autoinstall script on a VPS with Ubuntu 18.04 LTS (fresh installation):
```
wget -O - https://cipi.sh/go.sh | bash
```

#### Installation Note
Before you can use Cipi, please make sure your server fulfils these requirements:

- Ubuntu 18.04 x86_64 LTS (Fresh installation)
- If the server is virtual (VPS), OpenVZ may not be supported (Kernel 2.6)

Hardware Requirement: More than 1GB of HDD / At least 1 core processor / 512MB minimum RAM / At least 1 public IP Address (NAT VPS is not supported) / External firewall / For VPS providers such as AWS, those providers already include an external firewall for your VPS. Please open port 22, 80 and 443 to install Cipi.

Installation may take up to about 5 minutes minimum which may also depend on your server's internet speed. After the installation is completed, you are ready to use Cipi to manage your servers.

#### Installation notes on AWS
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
