# Security Policy

Thank you for helping keep Cipi and its users safe. We take security reports seriously and appreciate responsible disclosure.

## Supported Versions

Security fixes are provided for the **latest stable release** of Cipi. Older versions may receive backports at our discretion when the fix is practical and the issue is severe.

| Version                                                     | Supported |
| ----------------------------------------------------------- | --------- |
| Latest release ([`cipi self-update`](https://cipi.sh/docs)) | ✅        |
| Older releases                                              | ❌        |

Check your installed version with:

```bash
cipi --version
```

## Reporting a Vulnerability

**Please do not open a public GitHub issue for security vulnerabilities.**

Instead, use one of the following channels:

### Preferred: GitHub Private Vulnerability Reporting

1. Open the [Security tab](https://github.com/cipi-sh/cipi/security) of this repository.
2. Click **Report a vulnerability**.
3. Submit a private report with the details below.

This keeps the issue confidential until a fix is available.

### Alternative: Email

We aim to acknowledge reports within an initial assessment within **60 days**.

## What to Include

A helpful report usually contains:

- A clear description of the vulnerability and its impact
- Steps to reproduce, including Cipi version (`cipi --version`) and Ubuntu version
- Proof of concept or exploit details (if available)
- Affected components (CLI, REST API, GUI, deploy webhooks, MCP agent, etc.)
- Your contact information for follow-up

Please limit testing to systems you own or have explicit permission to test.

## Scope

The following are **in scope** for this repository:

- The Cipi installer and core CLI (`setup.sh`, `/opt/cipi`, shell libraries)
- Generated server configuration (Nginx, PHP-FPM, MariaDB, Supervisor, cron, sudoers)
- Built-in REST API (`cipi api`) and authentication/authorization
- Deploy webhooks, Git integration, and backup/sync tooling shipped with Cipi
- Security-sensitive defaults and isolation between apps on a Cipi-managed server

Related projects maintained under [cipi-sh](https://github.com/cipi-sh) may have their own `SECURITY.md` files:

- [cipi/gui](https://github.com/cipi-sh/gui) — optional web control panel
- [cipi/cli](https://github.com/cipi-sh/cli) — standalone API client
- [cipi/whmcs](https://github.com/cipi-sh/whmcs) — WHMCS provisioning module

If you are unsure where a report belongs, submit it here and we will route it to the right maintainers.

## Out of Scope

The following are generally **out of scope** unless they demonstrate a flaw in Cipi itself:

- Vulnerabilities in third-party software installed on the server (Nginx, PHP, MariaDB, Ubuntu, etc.) — report those upstream
- Misconfigurations on a specific VPS (weak passwords, exposed SSH keys, disabled firewall)
- Social engineering or physical access attacks
- Denial-of-service attacks without a demonstrated root cause in Cipi
- Issues in user-deployed application code (Laravel apps, WordPress, etc.) hosted on Cipi

## Disclosure Policy

- We will work with you to understand and validate the report.
- We will notify you when a fix is released or if we need more time.
- We ask that you **do not publicly disclose** the issue until we have published a fix or agreed on a disclosure timeline.
- We credit reporters in the release notes when a fix ships, unless you prefer to remain anonymous.

## Security Best Practices for Operators

If you run Cipi in production:

- Keep Cipi updated: `cipi self-update`
- Configure SMTP notifications: `cipi smtp configure`
- Restrict API access with scoped tokens: `cipi api token create`
- Optionally lock the API to known IPs: `cipi api ip-whitelist` (GUI server + operator IPs; default `*` = allow all)
- Review the [documentation](https://cipi.sh/docs) for hardening and isolation details

Thank you for contributing to a safer Cipi ecosystem.
