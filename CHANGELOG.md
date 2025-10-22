# Changelog

All notable changes to Cipi will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

For upgrade instructions, see [UPGRADE.md](UPGRADE.md).

## [4.0.0] - 2025-10-22

### 🎉 Complete Rewrite

Cipi has been completely rewritten as a **CLI-only tool** using pure Bash scripts. No more web interface - everything is now managed via SSH!

### ✨ Added

- **CLI-based management** - Complete control via `cipi` command
- **App management** - Create, list, show, and delete virtual hosts
- **Domain management** - Assign domains and aliases to virtual hosts
- **Database management** - Create and manage MySQL databases with secure password handling
- **PHP version management** - Install and switch between PHP 5.6-8.5
- **Service management** - Restart nginx, PHP-FPM, MySQL, Supervisor, Redis
- **Auto-update system** - Automatic updates from GitHub releases
- **JSON-based storage** - Lightweight data storage in `/etc/cipi` (passwords never stored)
- **Interactive & non-interactive modes** - Flexible command usage
- **Deployment scripts** - Auto-generated `deploy.sh` for each virtual host
- **SSL management** - Let's Encrypt SSL with `ssl.sh` script per virtual host
- **S3 backups** - Auto-generated `backup.sh` for storage + database backups to S3
- **Webhook support** - Documentation for GitHub/GitLab webhooks
- **Log rotation** - Automatic log rotation with 30-day retention
- **User isolation** - Each virtual host runs under its own system user
- **SSH key pairs** - Each virtual host has its own SSH key for private Git repos
- **Password management** - Change passwords for apps and databases (never stored in plain text)
- **PHP version switching** - Change PHP version per virtual host (not globally)
- **ClamAV antivirus** - Daily malware scans at 3 AM for all apps
- **Nginx optimization** - Worker processes, gzip compression, rate limiting, version hiding
- **Security hardening** - Server tokens hidden, security headers enabled, firewall configured
- **Crontab management** - Edit crontab for each app with `cipi app crontab`
- **.env editor** - Edit `.env` files with `cipi app env`
- **AWS CLI pre-installed** - Ready for S3 backups and AWS integrations
- **Nano as default editor** - User-friendly editor configured system-wide
- **Fail2ban integration** - Automatic SSH brute-force protection
- **UFW firewall** - Only ports 22, 80, 443 open by default

### 🔧 Changed

- **Ubuntu 24.04 LTS** is now the minimum required version
- **PHP 8.4** is now the default PHP version
- **No web interface** - CLI-only for better security and performance
- **Removed dependencies** on Laravel framework

### 🗑️ Removed

- Web-based control panel
- API endpoints
- Database migrations
- Web authentication system

### 📦 Technical Stack

- **OS:** Ubuntu 24.04 LTS (or higher)
- **Web Server:** nginx
- **Database:** MySQL 8.0+
- **PHP:** 8.4 (default), 5.6-8.5 (installable)
- **Cache:** Redis
- **Process Manager:** Supervisor
- **Node.js:** 20.x
- **SSL:** Let's Encrypt (Certbot)

### 🔒 Security

- Fail2ban with SSH protection
- UFW firewall (ports 22, 80, 443 only)
- Isolated system users per virtual host
- Automatic SSL certificates
- Secure password generation
- No unnecessary services running

---

## [3.x] - Previous Versions

Previous versions (1.x, 2.x, 3.x) were Laravel-based web applications. See [legacy branch](https://github.com/andreapollastri/cipi/tree/3.x) for older versions.

---

## Migration Guide (3.x → 4.0)

**⚠️ Warning:** Cipi 4.0 is a complete rewrite and is **not compatible** with previous versions.

If you're running Cipi 3.x or earlier:

1. **Backup** all your data and configurations
2. **Export** all your virtual host configurations
3. **Fresh install** Cipi 4.0 on a new server
4. **Manually recreate** virtual hosts using the new CLI
5. **Migrate** your applications and databases

We recommend setting up Cipi 4.0 on a fresh server rather than trying to upgrade in place.

---

For detailed upgrade instructions, see [UPGRADE.md](UPGRADE.md)
