# CIPI
### LAMP AUTO-DEPLOY ON LINUX UBUNTU SERVER
Install PHP 7.2, MySql 5.7, phpmyadmin, Let's encrypt, fail2ban and other on an empty Linux Ubuntu VPS.

More info on [http://andreapollastri.net/docs/cipi](http://andreapollastri.net/docs/cipi)

#### Installation
Run it as root on an empty Linux Ubuntu 18.04 server:
> wget -O - https://raw.githubusercontent.com/andreapollastri/cipi/master/go.sh | bash

#### Create a Virtual host
To create a virtual host:
> sudo sh /cipi/host-add.sh -d DOMAIN.EXT

This script generates one SFTP/SSH user, one document root, one SSL certificate, one MySql DB and one MySql user for DOMAIN and WWW.DOMAIN.

#### Delete a Virtual host
To remove a virtual host (and its user)
> sudo sh /cipi/host-del.sh -u HOSTUSER

#### Create an Alias
To create an alias pointed to an user document root:
> sudo sh /cipi/alias-add.sh -d DOMAIN.EXT -u HOSTUSER

#### Delete an Alias
To create an alias pointed to an user document root:
> sudo sh /cipi/alias-del.sh -a ALIASCODE -u ALIASHOSTUSER

#### Regenerate a SSL certificate
To regenerate a SSL certificate:
> sudo sh /cipi/ssl.sh -d DOMAIN.EXT

#### Change user SFTP/SSH and DB passwords
To change an user SSH/SFTP and MySql passwords:
> sudo bash /cipi/passwd.sh -u HOSTUSER

## Enjoy :)

### Coming Soon...
- VPS automated websites backups and dbs dumps!
- HTML admin interface
- Multi-server support
- Git Support
