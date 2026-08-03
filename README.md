<div align="center">

<img src="https://cipi.sh/favicon-light-192.png" width="64" height="64" alt="Cipi logo" />

# Cipi

**Easy Laravel Deployments**  
One command installs a complete production stack. One command deploys your app.<br>
No panel, no bloat — so you can focus on what you love: building your application.

[Website](https://cipi.sh) · [Docs](https://cipi.sh/docs) · [Report a bug](https://github.com/cipi-sh/cipi/issues)

</div>

---

## What is Cipi?

Cipi turns any Ubuntu VPS into a **multi-app PHP hosting platform** — Laravel by default (PHP-FPM or optional **Octane/FrankenPHP**), with full isolation, zero-downtime deploys, SSL, queue workers, and S3 backups — all managed from a single CLI. Use **`--custom`** for simple sites: classic deploy (no releases/shared), configurable docroot and Nginx (try_files, entry point), no DB or cron.

No web panel. No bloat. No sleepless nights fighting Nginx configs or PHP-FPM pools.  
Just SSH and the `cipi` command.

```bash
$ wget -O - https://cipi.sh/setup.sh | bash
```

> Works on DigitalOcean, AWS EC2, Hetzner, Vultr, Linode, OVH, Google Cloud, Scaleway, and more.

---

## From zero to production in 3 steps

**1. Install Cipi** on a fresh Ubuntu 24.04 or 26.04 VPS (~10 minutes):

```bash
wget -O - https://cipi.sh/setup.sh | bash
```

**2. Create your app** (Laravel by default, or `cipi app create --custom` for a simple deploy):

```bash
cipi app create
# username, domain, git repo, branch, PHP version
# → Laravel: user, DB, Nginx, workers, cron, webhook
# → Laravel Octane: cipi app create --octane
# → Custom: user, Nginx, PHP-FPM; Git optional (empty = SFTP-only to ~/htdocs)
```

**3. Deploy and go live:**

```bash
cipi deploy myapp
cipi ssl install myapp
```

That's it. Your Laravel app is live.

---

## Stack

Every app gets a fully isolated environment. **Laravel** (default): zero-downtime deploy, DB, workers, cron, webhook — optionally **Octane (FrankenPHP)** instead of PHP-FPM. **`--custom`**: classic deploy into `htdocs`, configurable docroot only; Nginx uses `index index.html index.php`, `try_files $uri $uri/ /index.php?$args`, `error_page 404 /404.html`. No DB, no .env, no cron, no workers.

| Component          | Details                                                                                                      |
| ------------------ | ------------------------------------------------------------------------------------------------------------ |
| **Web server**     | Nginx reverse proxy with per-app virtual hosts — PHP-FPM or Octane (`proxy_pass`), optimized for Laravel     |
| **PHP & Composer** | Selectable per app — PHP 7.4 to 8.5, hot-swappable                                                           |
| **Runtime**        | PHP-FPM pools by default; optional **Laravel Octane (FrankenPHP)** per app (`--octane`)                      |
| **Database**       | MariaDB (default) + optional PostgreSQL; dedicated DB and user per Laravel app                               |
| **Queue workers**  | Supervisor with per-app pools — `queue:work` or **Horizon**; optional **Reverb** for WebSockets              |
| **Deployments**    | Deployer — Laravel: atomic symlink, 5 releases, rollback, optional Node build; Custom: clone into htdocs     |
| **SSL**            | Let's Encrypt via Certbot — HTTP-01 by default; optional **DNS-01 (Cloudflare)** + wildcards                 |
| **Security**       | Fail2ban + UFW, per-app Linux user + PHP-FPM/Octane + SSH key                                                |
| **Healthchecks**   | HTTP probes every 5 minutes with failure alerts                                                              |
| **Backups**        | Automated DB and storage dumps to S3 or any compatible provider; optional pre-deploy DB snapshots            |

---

## Features

### 🔒 Security & Isolation by Design

Each app runs under its own Linux user with an isolated filesystem, PHP-FPM pool (or Octane process), and database. A compromise in one app cannot touch the others. Configs are encrypted at rest with AES-256 (Vault). GDPR-compliant log rotation included. Per-app **resource limits** (`cipi app limits`) cap FPM children, memory, Octane workers, and queue processes.

### ⚡ Zero-Downtime Deploys

Deployer clones your repo, runs `composer install`, links storage, runs migrations, and swaps the symlink atomically. Optional **Node build** on deploy (`cipi app edit --node-build=…`). Roll back to any of the last 5 releases instantly. Opt-in **pre-deploy DB snapshot** (`cipi deploy --snapshot`).

### 🚀 Laravel Octane (FrankenPHP)

Serve Laravel via Octane instead of PHP-FPM — same server, side by side with classic apps:

```bash
cipi app create --octane
cipi app convert myapp --to=octane   # or --to=fpm
```

Nginx proxies to a localhost Octane port; Supervisor runs `${app}-octane`. Requires `laravel/octane` in the app repo (starts after the first successful deploy).

### 📡 Reverb, Horizon & Scheduler

First-class Laravel extras, all CLI-managed:

```bash
cipi app reverb enable myapp
cipi worker horizon enable myapp
cipi schedule on myapp
```

Reverb gets a localhost port, Supervisor program, and Nginx `/app` WebSocket proxy. Horizon is mutually exclusive with `queue:work` workers. Scheduler toggles the crontab `schedule:run` entry.

### 🔗 Webhook Auto-Deploy

Native GitHub and GitLab integration — deploy keys and webhooks configured automatically. HMAC signature verification. Or plug in any custom Git provider.

### 📦 App Types

**Laravel** (default) — zero-downtime deploy with releases, shared storage, workers, scheduler, webhook; add **`--octane`** for FrankenPHP. **`--custom`** — for simple sites (e.g. WordPress, static+PHP): classic deploy into `htdocs` (no current/shared), choose docroot only (e.g. `/`, `www`, `dist`). Nginx: `index index.html index.php`, `try_files $uri $uri/ /index.php?$args`, `error_page 404 /404.html`. No DB, no .env, no cron, no workers, no webhook — just Nginx, PHP-FPM, and deploy key.

Clone an app for staging with **`cipi app clone <src> --domain=…`**.

### 🌐 Aliases, www & SSL

Add multiple domains or subdomains to any app. Manage www/apex aliases and canonical redirects with **`cipi www`**. A single SAN certificate covers all of them — HTTP-01 by default, or **DNS-01 via Cloudflare** for wildcards (`cipi ssl install --dns=cloudflare --wildcard`). Auto-renew handles the rest.

### ❤️ HTTP Healthchecks

Point Cipi at an HTTP endpoint; it probes every 5 minutes and alerts after consecutive failures:

```bash
cipi health set myapp --url=https://example.com/up --expect=200
```

### 🤖 AI Agent Ready (MCP)

Cipi ships with a built-in MCP server. Laravel first: install the `cipi-agent` Laravel package, point your AI client at the endpoint, and deploy, rollback, query logs, and run Artisan commands via natural language — no SSH required.

Works with Claude, Cursor, VS Code, OpenAI, Gemini, and more.

```bash
composer require cipi/agent
```

MCP tools exposed: `health`, `app_info`, `deploy`, `logs`, `db_query`, `artisan`.

### 🔌 REST API (optional)

When you need to manage apps programmatically or integrate with external pipelines, enable the optional API layer with a single command. Bearer tokens, granular permissions, OpenAPI spec available, interactive Swagger docs.

### 🖥️ Web GUI (optional)

Multi-server control panel for operators who prefer a browser over SSH. Register N Cipi servers with API tokens, switch between them from any page, and manage apps, databases, deploys, SSL, aliases, and logs with Livewire UI and async job overlays. Install with **`cipi gui <domain>`** — requires **`cipi api`** on each managed server. Session login with optional Google Authenticator 2FA.

[GitHub](https://github.com/cipi-sh/gui)

### 🔁 Sync Between Servers

Move entire stacks or single apps between Cipi servers — for migration, failover, or disaster recovery. Archives are encrypted in transit.

### 🖥️ CLI Client

A standalone Go binary that talks to the Cipi REST API from your local machine — no SSH required. Manage apps, databases, SSL, aliases, and deployments from any terminal. Pre-built binaries for Linux and macOS (amd64/arm64).

[Docs](https://cipi.sh/docs/cli-client) · [GitHub](https://github.com/cipi-sh/cli)

### 🛒 WHMCS Module

An official provisioning module that bridges the WHMCS lifecycle to the Cipi REST API — automate app creation, deletion, SSL certificates, deployments, and package changes for your hosting customers. Self-contained drop-in, no Composer dependencies.

[Docs](https://cipi.sh/docs/advanced#whmcs) · [GitHub](https://github.com/cipi-sh/whmcs)

---

## Who uses Cipi?

- **Solo developers** — ship Laravel first, without the DevOps overhead
- **Agencies** — one VPS, many isolated client projects (Laravel first), onboard a new client in minutes
- **Startups & SaaS** — atomic deploys, instant rollbacks, grow without changing your workflow
- **Datacenters & automation pipelines** — every Cipi command is a plain shell call, wire it into Ansible or any provisioning script

---

## Requirements

- Ubuntu **24.04 LTS** or **26.04 LTS** (no other releases)
- Root access
- Ports **22**, **80**, **443** open

---

## Documentation

Full docs at: **[cipi.sh/docs](https://cipi.sh/docs)**

---

## Contributing

Cipi is open source and MIT licensed. Issues, PRs, and feedback are welcome on [GitHub](https://github.com/cipi-sh/cipi).

---

<div align="center">

Made with ❤️ by [Andrea Pollastri](https://web.ap.it)

</div>
