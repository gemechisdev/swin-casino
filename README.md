# SWin Casino — Enterprise Casino Platform

**SWin Casino** is a clean, production-ready, self-hosted online casino platform built on **Laravel 11** (PHP 8.3+). It ships with a rich admin dashboard, 20+ automated payment gateways, a multi-language manager, KYC verification, 2FA, and a plug-and-play game engine. All vendor-specific licensing hooks have been removed; the codebase is ready to white-label for any brand.

> **Developed by [Scoware](https://scoware.com)** · support@scoware.com

---

## 🚀 Tech Stack

| Layer | Technology |
|---|---|
| Backend Framework | Laravel 11.x |
| Language | PHP 8.3+ |
| Database | MySQL 8.0+ / MariaDB 10.6+ |
| Frontend Build | Vite + Axios |
| Authentication | Laravel Sanctum (API) + Session (Web) |
| Image Processing | Intervention Image v3 |
| Payment SDKs | Stripe, Razorpay, Mollie, Authorize.Net, CoinGate, BTCPay, Coinbase Commerce, PayPal, Skrill, Payeer, PerfectMoney, 2Checkout, Coinpayments, Instamojo, Binance Pay, and more |
| Notification SDKs | Twilio, Vonage, SendGrid, Mailjet, MessageBird, PHPMailer |
| Social Login | Google, Facebook, LinkedIn (via Laravel Socialite) |

---

## ✨ Key Features

- **Robust Admin Dashboard** — Full oversight over users, transactions, game logs, and all system settings from a single UI.
- **Automated & Manual Payments** — 20+ globally integrated payment providers plus unlimited configurable manual gateways.
- **Dynamic SEO Manager** — Configure keywords, meta descriptions, and Open Graph content for every page without touching code.
- **Multi-Language Support** — Add, edit, and publish translations from the admin panel; zero code changes required.
- **Plug-and-Play Extensions** — One-click UI toggles for Google Analytics, reCAPTCHA v2, Tawk.to Live Chat, and Facebook Comments.
- **Enterprise Security** — KYC workflow, email & SMS 2FA, Google Authenticator (TOTP), automatic HTTPS enforcement, and secure-password policy.
- **Progressive Web App (PWA)** — Fully configured PWA manifest and service worker for installable mobile experience.
- **Referral System** — Configurable multi-tier affiliate/referral commission engine.
- **Multiple Frontend Templates** — Ships with two frontend themes (`basic` and `sunfyre`); switchable from the admin panel.
- **Fully Configurable Branding** — All brand identity (app name, developer, contact) is driven by `.env` variables with zero hardcoded strings.

---

## ⚙️ Server Requirements

| Requirement | Minimum |
|---|---|
| PHP | 8.3+ |
| MySQL | 8.0+ or MariaDB 10.6+ |
| PHP Extensions | BCMath, Ctype, cURL, DOM, Fileinfo, GD, JSON, Mbstring, OpenSSL, PCRE, PDO, pdo_mysql, Tokenizer, XML, Filter, Hash, Session, Zip |
| PHP Functions | `allow_url_fopen`, `file_get_contents` (must be enabled) |
| Web Server | Apache (with `mod_rewrite`) or Nginx |
| Composer | 2.x |
| Node.js | 18+ (for frontend asset compilation only) |

---

## 📥 Installation Guide

### Option 1: Web Installer (Recommended for Shared Hosting / cPanel)

1. Upload and extract the project files to the root of your web server (e.g., `public_html`).
2. Confirm both `index.php` and `.htaccess` are present at the root level.
3. Open your browser and navigate to `http://your-site-url/install/index.php`.
4. The installer will guide you through:
   - **Branding** — Set your casino name, developer brand, website URL, and support email. These are written directly to the `.env` file.
   - **Database** — Enter your MySQL credentials.
   - **Admin Account** — Set the admin username, password, and email.
5. After installation, **delete the `install/` folder** from your server to prevent re-installation.

### Option 2: CLI Setup (VPS / Local Development)

```bash
# 1. Navigate to the core Laravel directory
cd core

# 2. Install PHP dependencies
composer install --no-dev --optimize-autoloader

# 3. Install and compile frontend assets
npm install && npm run build

# 4. Copy environment file
cp .env.example .env

# 5. Edit .env with your settings (see Branding & Configuration below)
nano .env

# 6. Generate an application key
php artisan key:generate

# 7. Import the bundled database schema and seed data
mysql -u YOUR_USER -p YOUR_DATABASE < ../install/database.sql

# 8. Clear and warm caches
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 9. Set storage permissions
chmod -R 775 storage bootstrap/cache
```

---

## 🎨 Branding & Configuration

All brand identity is controlled via `.env` — no hardcoded strings anywhere in the codebase.

```dotenv
# ── Application Name ──────────────────────────────────────────────────────────
# Shown in page titles, emails, the admin panel, and the installer.
APP_NAME="SWin Casino"

# ── Developer / Brand Identity ────────────────────────────────────────────────
# Shown in admin panel footer, system info page, and notification emails.
APP_BRAND_NAME="Scoware"
APP_BRAND_URL="https://scoware.com"
APP_BRAND_EMAIL="support@scoware.com"

# ── Core Settings ─────────────────────────────────────────────────────────────
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com
APP_TIMEZONE=UTC

# ── Database ──────────────────────────────────────────────────────────────────
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=swin_casino
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password
```

### Available Config Helpers (PHP)

These global helper functions are available anywhere in the application:

```php
config('app.name')         // → "SWin Casino"
config('app.brand_name')   // → "Scoware"
config('app.brand_url')    // → "https://scoware.com"
config('app.brand_email')  // → "support@scoware.com"

brandName()   // shorthand for config('app.brand_name')
brandUrl()    // shorthand for config('app.brand_url')
brandEmail()  // shorthand for config('app.brand_email')

gs('site_name')  // site name from DB (set via admin General Settings panel)
```

> **Note:** `gs('site_name')` reads from the database `general_settings` table and is the value displayed in frontend page titles and footers. It is pre-seeded from `APP_NAME` during installation and is editable by admins at runtime.

---

## 📁 Directory Structure

```
swin-casino/
├── assets/          # Publicly accessible compiled frontend assets (CSS, JS, images)
├── core/            # Main Laravel 11 application (should NOT be web-accessible directly)
│   ├── app/         # Application logic (Controllers, Models, Middleware, Helpers)
│   ├── bootstrap/   # Framework bootstrap and cache
│   ├── config/      # Configuration files (app.php, database.php, mail.php, …)
│   ├── database/    # Migrations directory (schema is in install/database.sql)
│   ├── resources/   # Blade views, language files, uncompiled assets
│   │   ├── views/
│   │   │   ├── admin/     # Admin panel views
│   │   │   ├── templates/ # Frontend themes (basic, sunfyre)
│   │   │   └── partials/  # Shared blade components
│   │   └── lang/en.json   # English language strings
│   ├── routes/      # Route definitions (web.php, admin.php, api.php, user.php, ipn.php)
│   ├── storage/     # App-generated files (logs, sessions, cached views)
│   └── vendor/      # Composer-managed PHP dependencies
├── install/
│   ├── index.php    # Web installer (delete after installation)
│   └── database.sql # Full MySQL schema + seed data
├── .htaccess        # Apache routing rules (redirects public traffic to core/public)
└── index.php        # Public entry point
```

---

## 🔒 Security Best Practices for Production

| Action | Details |
|---|---|
| **Delete installer** | Remove the `install/` directory immediately after installation. |
| **Set permissions** | `chmod -R 775 core/storage core/bootstrap/cache` |
| **Protect `core/`** | Ensure `core/` is not directly web-accessible (the `.htaccess` handles this on Apache; configure a matching Nginx rule in production). |
| **Enable SSL** | Force HTTPS from **Admin → System Configuration → Force SSL**. |
| **Change default credentials** | Update the default admin password immediately after first login. |
| **Rotate keys** | Run `php artisan key:generate` and update `APP_KEY` in `.env` after any environment change. |
| **Set `APP_DEBUG=false`** | Never enable debug mode in production — it exposes stack traces. |

---

## 🗂️ Admin Panel Overview

Login at `https://yourdomain.com/admin`.

| Section | Description |
|---|---|
| **Dashboard** | Real-time stats: deposits, withdrawals, new users, active games |
| **Users** | List, search, ban, KYC review, balance adjustments, login history |
| **Games** | Manage all game types; toggle game availability |
| **Deposit** | Automated and manual gateway configuration; approve/reject pending deposits |
| **Withdrawal** | Configure withdrawal methods; approve/reject requests |
| **Payment Gateways** | Enable/disable and configure 20+ automated payment providers |
| **Extensions** | Toggle Google Analytics, reCAPTCHA, Tawk.to, Facebook Comments |
| **Frontend** | Edit homepage sections, policy pages, FAQ, testimonials, contact details |
| **Language** | Add languages; translate every UI string |
| **Settings → General** | Site title, currency, timezone, color scheme |
| **Settings → Logo/Icon** | Upload site logo and favicon |
| **Settings → System** | PHP/Laravel version info, build number |
| **Notifications** | Email and SMS template management |
| **Reports** | Transaction history, login logs, notification history, balance transfers |
| **Referral** | Configure multi-tier commission rules |
| **Subscribers** | Newsletter subscriber list; send bulk email |

---

## 🔧 Useful Artisan Commands

```bash
# Clear all caches (use during development)
php artisan optimize:clear

# Warm all production caches
php artisan optimize

# Cache config, routes, and views separately
php artisan config:cache
php artisan route:cache
php artisan view:cache

# List all registered routes
php artisan route:list

# Run queue worker (for background jobs/emails)
php artisan queue:work --sleep=3 --tries=3

# Run the cron scheduler (add to crontab: * * * * * php artisan schedule:run)
php artisan schedule:run
```

---

## 🌐 Web Server Configuration

### Apache (`.htaccess` is pre-configured)

Ensure `AllowOverride All` is set for your document root and `mod_rewrite` is enabled.

### Nginx

```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /var/www/swin-casino;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Block direct access to the Laravel core
    location ~ ^/core/ {
        deny all;
        return 404;
    }
}
```

---

## 💳 Payment Gateway Setup

Each gateway is configured via **Admin → Payment Gateways → Automatic**. The following providers are bundled:

| Gateway | Type |
|---|---|
| Stripe | Card / Redirect |
| Razorpay | Card / UPI |
| Mollie | Multi-method |
| Authorize.Net | Card |
| CoinGate | Crypto |
| BTCPay Server | Crypto (self-hosted) |
| Coinbase Commerce | Crypto |
| Coinpayments | Crypto |
| Binance Pay | Crypto |
| PayPal | Redirect |
| Skrill | Redirect |
| Payeer | Redirect |
| PerfectMoney | Redirect |
| 2Checkout | Card |
| Instamojo | India |

Manual gateways (bank transfer, mobile money, etc.) can be added without limit via **Admin → Payment Gateways → Manual**.

---

## 📧 Email & SMS Configuration

Configure SMTP or any Laravel-supported mail driver in `.env`:

```dotenv
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailgun.org
MAIL_PORT=587
MAIL_USERNAME=your@mailgun.com
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="${APP_BRAND_EMAIL}"
MAIL_FROM_NAME="${APP_NAME}"
```

SMS providers (Twilio, Vonage, MessageBird, etc.) are configured via **Admin → Settings → Notification**.

---

## 🤝 Developer & Support

- **Developed by:** [Scoware](https://scoware.com)
- **Support Email:** support@scoware.com
- **Platform Version:** 3.8 (Build 5.1.19)
- **Framework:** Laravel 11 / PHP 8.3+

---

## 📋 Changelog

### v3.8 (Current)
- All license enforcement, phone-home calls, and ViserLab branding removed
- Full branding made configurable via `.env` (`APP_NAME`, `APP_BRAND_NAME`, `APP_BRAND_URL`, `APP_BRAND_EMAIL`)
- Web installer updated with branding fields; writes all brand vars to generated `.env`
- `config/timezone.php` fixed to use `return` syntax — resolves `config:cache` failure
- Duplicate route names in `routes/admin.php` fixed — resolves `route:cache` failure
- Laravel infrastructure tables (cache, sessions, jobs, failed_jobs, etc.) added to `database.sql`
- `APP_TIMEZONE` env var now respected as fallback in `config/app.php`
- New global PHP helpers: `brandName()`, `brandUrl()`, `brandEmail()`
- Admin system info page label updated from "ViserAdmin Version" to "Build Version"
- All seeded branding (`site_name`, `email_from`, `email_from_name`, policy page text) updated to SWin Casino / Scoware
