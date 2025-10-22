# Cipi - Server Management CLI

<p align="center">
  <h1>Work in progress... this is not a stable version!</h1>
</p>

<p align="center">
  <strong>A powerful server management CLI for Laravel applications on Ubuntu</strong>
</p>

<p align="center">
  <a href="https://github.com/andreapollastri/cipi/releases"><img src="https://img.shields.io/github/v/release/andreapollastri/cipi" alt="Latest Release"></a>
  <a href="https://github.com/andreapollastri/cipi/blob/latest/LICENSE"><img src="https://img.shields.io/github/license/andreapollastri/cipi" alt="License"></a>
  <a href="https://github.com/andreapollastri/cipi/stargazers"><img src="https://img.shields.io/github/stars/andreapollastri/cipi?style=social" alt="Stars"></a>
</p>

---

## 🚀 What is Cipi?

Cipi is a **CLI-based server control panel** designed exclusively for **Laravel developers** who need a secure, production-ready hosting environment on Ubuntu VPS. With Cipi, you can:

- ✨ Create isolated virtual hosts with individual users and PHP versions
- 🔒 Automatic SSL certificates with Let's Encrypt
- 🗄️ Manage MySQL databases
- 🌐 Configure domains and aliases
- 🐘 Install and manage multiple PHP versions (5.6 - 8.5)
- 🔄 Deploy Laravel applications with Git
- 📊 Monitor server status
- 🛡️ Built-in fail2ban + ClamAV antivirus protection
- 🔐 Secure password management (never stored in plain text)
- 📦 S3 backup integration for storage + databases

**No web interface needed** - everything is managed via SSH with the `cipi` command!

---

## 🎯 Who is Cipi For?

Cipi is **specifically designed for Laravel applications** and developers who:

- ✅ Want a **secure, production-ready** server without complex DevOps knowledge
- ✅ Need **isolated environments** for multiple Laravel projects on one VPS
- ✅ Prefer **CLI management** over web-based control panels
- ✅ Value **security hardening** with automatic updates and malware scanning
- ✅ Need **per-project PHP versions** (Laravel 8 on PHP 8.0, Laravel 11 on PHP 8.3, etc.)
- ✅ Want **automated backups** to S3 for storage and databases
- ✅ Are deploying on **Ubuntu 24.04 LTS** servers

### 🔒 Security Level: Production-Grade & Hardened

Cipi implements **production-ready security** with multiple layers of protection:

**System Security:**

- 🛡️ **Fail2ban** - Automatic SSH brute-force protection
- 🔥 **UFW Firewall** - Only ports 22, 80, 443 exposed
- 🦠 **ClamAV Antivirus** - Daily malware scans of all applications
- 🔐 **User Isolation** - Each app runs as a separate system user with strict permissions (chmod 750/640)
- 🔑 **Root-Only CLI** - Cipi commands require sudo for administrative control

**Application Security:**

- 🔒 **SSL Everywhere** - Free Let's Encrypt certificates with auto-renewal
- 🚫 **Nginx Hardening** - Server tokens hidden, rate limiting, security headers
- 🗝️ **SSH Keys** - Auto-generated for each app to access private Git repos
- 🔐 **Secure Passwords** - Complex passwords never stored in plain text, shown only once
- 🔄 **Automatic Updates** - Weekly system security patches via cron

**Laravel-Specific:**

- 📂 **Storage Permissions** - Optimized for Laravel's storage and cache directories
- ⚙️ **Supervisor Workers** - Process management for queues
- 📅 **Cron Scheduler** - Pre-configured for `php artisan schedule:run`
- 💾 **Database Backups** - Automated MySQL/PostgreSQL/SQLite backups to S3

**Monitoring & Response:**

- 📊 **System Status** - Real-time monitoring of all services
- 📝 **Comprehensive Logs** - Nginx, PHP-FPM, application, and security logs
- 🚨 **Antivirus Logs** - Track malware scans and threats

**What Cipi is NOT:**

- ❌ Not for "enterprise" with compliance certifications (SOC2, PCI-DSS)
- ❌ Not a WAF (Web Application Firewall) like Cloudflare
- ❌ Not high-availability clustering or load balancing
- ❌ Not for non-PHP applications (only Laravel/PHP)

**Verdict:** Cipi provides **production-grade security** suitable for professional Laravel applications, side projects, and small-to-medium businesses. For highly regulated industries or applications requiring compliance certifications, additional security layers may be needed.

---

## 📋 Requirements

- **Ubuntu 24.04 LTS** (or higher)
- Fresh server installation recommended
- Minimum 512MB RAM, 1 CPU core
- Root access (sudo)
- Public IPv4 address

### VPS Providers Tested

- ✅ DigitalOcean
- ✅ AWS EC2
- ✅ Vultr
- ✅ Linode
- ✅ Hetzner
- ✅ Google Cloud

---

## ⚡ Quick Installation

```bash
wget -O - https://raw.githubusercontent.com/andreapollastri/cipi/refs/heads/latest/install.sh | bash
```

Installation takes approximately **10-15 minutes** depending on your server's internet speed.

### AWS Installation

AWS disables root login by default. Use this method:

```bash
ssh ubuntu@<your-server-ip>
sudo -s
wget -O - https://raw.githubusercontent.com/andreapollastri/cipi/refs/heads/latest/install.sh | bash
```

**Important:**

- Open ports 22, 80, and 443 in your VPS firewall!
- **Save the MySQL root password** shown during installation!
- App and database passwords are **never stored** in config files for security. Save them when displayed!

---

## 📚 Usage

### Basic Commands

```bash
# Show server status
cipi status

# Show all available commands
cipi help

# Show Cipi version
cipi version
```

### App Management

```bash
# Create a new virtual host (interactive)
cipi app create

# Create virtual host (non-interactive)
cipi app create \
  --user=myapp \
  --repository=https://github.com/laravel/laravel.git \
  --branch=main \
  --php=8.4

# List all virtual hosts
cipi app list

# Show virtual host details
cipi app show myapp

# Change PHP version for a virtual host
cipi app edit myapp --php=8.3

# Edit .env file
cipi app env myapp

# Edit crontab (for Laravel scheduler, backups, etc.)
cipi app crontab myapp

# Change virtual host password
cipi app password myapp

# Change virtual host password (custom)
cipi app password myapp --password=MySecurePass123!

# Delete virtual host
cipi app delete myapp
```

### Domain Management

```bash
# Assign a domain (interactive)
cipi domain create

# Assign a domain (non-interactive)
cipi domain create \
  --domain=example.com \
  --aliases=www.example.com \
  --app=myapp

# List all domains
cipi domain list

# Delete a domain
cipi domain delete example.com

# Add alias to domain
cipi alias add example.com www.example.com

# Remove alias from domain
cipi alias remove example.com www.example.com
```

### Database Management

```bash
# Create a new database (interactive)
cipi database create

# Create database (non-interactive)
cipi database create --name=mydb

# List all databases
cipi database list

# Change database password
cipi database password mydb

# Change database password (custom)
cipi database password mydb --password=MyDbPass123!

# Delete a database
cipi database delete mydb
```

### PHP Management

```bash
# List installed PHP versions
cipi php list

# Install a PHP version
cipi php install 8.3

# Switch CLI PHP version
cipi php switch 8.4
```

### Service Management

```bash
# Restart nginx
cipi service restart nginx

# Restart all PHP-FPM services
cipi service restart php

# Restart MySQL
cipi service restart mysql

# Restart Supervisor
cipi service restart supervisor

# Restart Redis
cipi service restart redis
```

### Auto-Update

```bash
# Update Cipi to the latest version
cipi update
```

---

## 🏗️ App Structure

When you create a virtual host, Cipi creates the following structure:

```
/home/<username>/
├── wwwroot/          # Your Laravel project (Git repository)
├── logs/             # Nginx access & error logs
├── backups/          # Local backup storage
├── .ssh/             # SSH keys for Git (private repositories)
├── deploy.sh         # Deployment script (editable)
├── ssl.sh            # SSL certificate management script
├── backup.sh         # S3 backup script (storage + database)
├── gitkey.pub        # SSH public key for GitHub/GitLab
└── WEBHOOK_SETUP.md  # Webhook setup documentation
```

### Deployment Script

Each virtual host comes with a pre-configured `deploy.sh` script optimized for Laravel:

```bash
cd /home/<username>
./deploy.sh
```

The script automatically:

- Pulls latest changes from Git
- Installs Composer dependencies
- Runs database migrations
- Clears and optimizes cache
- Builds assets with npm (if needed)
- Restarts queue workers

### SSL Certificates

Enable SSL for your domain:

```bash
# As root
sudo -u <username> /home/<username>/ssl.sh

# Or wait for the automatic hourly cron job
```

Certificates are automatically renewed via cron job.

### Private Git Repositories

Each virtual host has its own SSH key pair for accessing private Git repositories:

```bash
# View the public key
cat /home/<username>/gitkey.pub

# Or show it with the app details
cipi app show <username>
```

Copy the public key and add it to your Git provider:

- **GitHub:** Settings → SSH and GPG keys → New SSH key
- **GitLab:** Settings → SSH Keys → Add new key
- **Bitbucket:** Personal settings → SSH keys → Add key

### Backups to S3

Each virtual host includes an S3 backup script for Laravel storage and database:

```bash
# Edit backup configuration
nano /home/<username>/backup.sh

# Configure:
# - S3_BUCKET (your S3 bucket name)
# - S3_REGION (e.g., us-east-1)
# - AWS_ACCESS_KEY_ID
# - AWS_SECRET_ACCESS_KEY

# Test the backup
sudo -u <username> /home/<username>/backup.sh

# Setup automatic nightly backups
sudo -u <username> crontab -e
# Add this line for 2 AM backups:
# 0 2 * * * /home/<username>/backup.sh >> /home/<username>/logs/backup.log 2>&1
```

The backup script automatically:

- Backs up Laravel `storage/` directory
- Backs up database (MySQL, SQLite, or PostgreSQL)
- Creates compressed archive
- Uploads to S3 with retention policy
- Cleans up backups older than 30 days (both local and S3)

**Note:** AWS CLI is pre-installed with Cipi

---

## 🔗 Git Webhooks

Cipi supports automatic deployments using Git webhooks. Each virtual host includes a `WEBHOOK_SETUP.md` guide with detailed instructions for:

- GitHub webhook configuration
- GitLab webhook configuration
- Webhook endpoint setup
- Security best practices

---

## 🔒 Security Features

- 🛡️ **Fail2ban** - Automatic IP banning after failed SSH attempts
- 🔥 **UFW Firewall** - Only ports 22, 80, 443 are open
- 👤 **Isolated Users** - Each virtual host runs under its own system user
- 🔐 **Secure Permissions** - Proper file and directory permissions
- 🚫 **No FTP** - Only secure SFTP access
- 🔑 **SSL Everywhere** - Free Let's Encrypt certificates

---

## 📦 What's Installed

### Core Software

| Software   | Version       | Purpose                |
| ---------- | ------------- | ---------------------- |
| nginx      | Latest        | Web server             |
| PHP        | 8.4 (default) | PHP interpreter        |
| MySQL      | 8.0+          | Database server        |
| Redis      | Latest        | Caching & sessions     |
| Supervisor | Latest        | Process manager        |
| Composer   | 2.x           | PHP dependency manager |
| Node.js    | 20.x          | JavaScript runtime     |
| npm        | Latest        | Node package manager   |
| Certbot    | Latest        | SSL certificates       |

### Additional PHP Versions

You can install any PHP version from **5.6 to 8.5** (beta):

```bash
cipi php install 8.3
cipi php install 8.2
cipi php install 7.4
```

---

## 🔄 Auto-Updates

Cipi automatically checks for updates via cron job (daily at 5:00 AM). You can also manually update:

```bash
cipi update
```

Updates are pulled from GitHub releases and applied automatically.

---

## 📊 Monitoring

### Server Status

```bash
cipi status
```

Output:

```
 ██████ ██ ██████  ██
██      ██ ██   ██ ██
██      ██ ██████  ██
██      ██ ██      ██
 ██████ ██ ██      ██

SERVER STATUS
─────────────────────────────────────
IP:       123.456.789.0
HOSTNAME: my-server
CPU:      25%
RAM:      45%
HDD:      30%

SERVICES
─────────────────────────────────────
nginx:      ● running
mysql:      ● running
php8.4-fpm: ● running
supervisor: ● running
redis:      ● running
```

---

## 🗑️ Uninstall

To uninstall Cipi from your server while **keeping all virtual hosts, databases, and websites running**:

```bash
# Stop and remove Cipi system cron jobs
sudo crontab -l | grep -v "cipi\|certbot\|freshclam\|apt-get" | sudo crontab -

# Remove Cipi executable and scripts
sudo rm -f /usr/local/bin/cipi
sudo rm -rf /opt/cipi

# Optional: Remove Cipi configuration data
# WARNING: This removes all domain/app metadata but keeps actual files
sudo rm -rf /etc/cipi

# Optional: Remove Cipi log directory
sudo rm -rf /var/log/cipi
```

### What Gets Removed

✅ Cipi CLI tool (`/usr/local/bin/cipi`)  
✅ Cipi library scripts (`/opt/cipi/`)  
✅ Cipi configuration data (`/etc/cipi/`)  
✅ Cipi cron jobs (SSL renewal, updates, scans)  
✅ Cipi logs (`/var/log/cipi/`)

### What Stays Intact

✅ All virtual host users (e.g., `/home/myapp/`)  
✅ All websites and Laravel applications  
✅ All databases and MySQL users  
✅ Nginx configurations (`/etc/nginx/sites-available/`)  
✅ PHP-FPM pools (`/etc/php/*/fpm/pool.d/`)  
✅ SSL certificates (`/etc/letsencrypt/`)  
✅ All system packages (Nginx, MySQL, PHP, Redis, etc.)  
✅ Supervisor workers  
✅ Fail2ban and UFW configurations

### After Uninstall

Your websites will **continue to work normally**. You'll need to manage:

- **SSL Renewal**: Setup your own certbot cron job

  ```bash
  sudo crontab -e
  # Add: 10 4 * * 7 certbot renew --nginx --non-interactive
  ```

- **System Updates**: Setup your own update schedule

  ```bash
  sudo crontab -e
  # Add: 20 4 * * 7 apt-get -y update
  # Add: 40 4 * * 7 DEBIAN_FRONTEND=noninteractive apt-get -q -y dist-upgrade
  ```

- **Antivirus Scans**: Setup your own ClamAV scan schedule

  ```bash
  sudo crontab -e
  # Add: 0 3 * * * /usr/local/bin/cipi-scan
  ```

- **Manual Management**: Use standard Linux tools
  ```bash
  sudo nginx -t                          # Test Nginx
  sudo systemctl reload nginx            # Reload Nginx
  sudo systemctl restart php8.4-fpm      # Restart PHP-FPM
  mysql -u root -p                       # Manage databases
  ```

### Reinstallation

You can reinstall Cipi at any time without affecting existing sites:

```bash
wget -O - https://raw.githubusercontent.com/andreapollastri/cipi/refs/heads/latest/install.sh | bash
```

Cipi will detect existing virtual hosts and continue managing them.

---

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

---

## 📝 License

Cipi is open-source software licensed under the [MIT license](LICENSE).

---

## 📚 Documentation

- [Installation Guide](INSTALL.md) - Complete installation instructions
- [Quick Start Guide](QUICKSTART.md) - Get up and running in 10 minutes
- [Features](FEATURES.md) - Complete feature list
- [Security Guidelines](SECURITY.md) - Password management and security best practices
- [Upgrade Guide](UPGRADE.md) - How to update Cipi
- [Contributing](CONTRIBUTING.md) - How to contribute
- [Changelog](CHANGELOG.md) - Version history

---

## 💬 Support

- 🐛 **Bug Reports:** [GitHub Issues](https://github.com/andreapollastri/cipi/issues)
- 💡 **Feature Requests:** [GitHub Issues](https://github.com/andreapollastri/cipi/issues)
- 📧 **Email:** hello@cipi.sh
- 🌐 **Website:** [cipi.sh](https://cipi.sh)

---

## ⭐ Star History

If you find Cipi useful, please consider giving it a star on GitHub!

---

## 🙏 Acknowledgments

- Built with ❤️ by [Andrea Pollastri](https://github.com/andreapollastri)
- Inspired by server management tools like Forge, RunCloud, and Ploi
- Thanks to all contributors!

---

<p align="center">
  <strong>Made with ❤️ for the Laravel community</strong>
</p>
