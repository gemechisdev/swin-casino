# AddisWin Casino — Enterprise Online Casino Platform

> **Ethiopia's premier self-hosted online casino platform** — production-ready, white-label, and built for scale.

[![Laravel](https://img.shields.io/badge/Laravel-11.x-red?logo=laravel)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2%2B-blue?logo=php)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0%2B-orange?logo=mysql)](https://mysql.com)
[![License](https://img.shields.io/badge/License-Commercial-green)](#)
[![Version](https://img.shields.io/badge/Version-3.8-brightgreen)](#changelog)

---

## Overview

**AddisWin Casino** is a full-featured, self-hosted online casino platform engineered for operators who demand reliability, flexibility, and a premium player experience. Built on **Laravel 11**, it ships with an intuitive admin dashboard, 26+ automated payment gateways, a multi-language manager, KYC verification, two-factor authentication, a comprehensive game engine, and a Progressive Web App (PWA) layer — everything you need to launch and scale a professional online casino.

The platform is architected as a white-label solution: every brand identity element — casino name, developer attribution, contact details, color scheme — is driven entirely by environment variables. No hardcoded strings. No vendor lock-in. Full ownership from day one.

> **Developed by [Scoware](https://scoware.com)** · support@scoware.com

---

## Tech Stack

| Layer                  | Technology                                                                                                                                                                    |
| ---------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Backend Framework      | Laravel 11.x                                                                                                                                                                  |
| Language               | PHP 8.2+                                                                                                                                                                      |
| Database               | MySQL 8.0+ / MariaDB 10.6+                                                                                                                                                    |
| Frontend Build         | Vite + Axios                                                                                                                                                                  |
| Authentication         | Laravel Sanctum (API) + Session (Web)                                                                                                                                         |
| Image Processing       | Intervention Image v3                                                                                                                                                         |
| Payment SDKs           | Stripe, Razorpay, Mollie, Authorize.Net, CoinGate, BTCPay, Coinbase Commerce, PayPal, Skrill, Payeer, PerfectMoney, 2Checkout, Coinpayments, Instamojo, Binance Pay, and more |
| Notification Providers | Twilio, Vonage, SendGrid, Mailjet, MessageBird, PHPMailer                                                                                                                     |
| Social Authentication  | Google, Facebook, LinkedIn (via Laravel Socialite)                                                                                                                            |

---

## Key Features

### Player Experience

- **19 Casino Games** — Head & Tail, Spin Wheel, Rock Paper Scissors, Number Guessing, Dice Rolling, Card Finding, Number Slot, Pool Number, Roulette, Casino Dice, Keno, Blackjack, Mines, Poker, Color Prediction, Crazy Times, Dream Catcher, Andar Bahar, and Pai Gow Poker.
- **Demo Mode** — Every game supports a free-play demo mode so players can learn mechanics before wagering real money.
- **Progressive Web App (PWA)** — Fully configured service worker and manifest for an installable, native-quality mobile experience.
- **Referral System** — Configurable multi-tier affiliate commission engine. Players earn real money by referring friends.
- **Gameplay Bonus** — Automatic bonus rewards distributed after a configurable number of gameplay rounds.
- **Balance Transfer** — Players can transfer balances between accounts with configurable fixed and percentage charges.

### Operator & Admin Tools

- **Unified Admin Dashboard** — Real-time insights across deposits, withdrawals, active users, and game logs — all from a single screen.
- **26+ Payment Gateways** — Automated card, e-wallet, and crypto gateways preconfigured and ready to activate. Unlimited manual gateways (bank transfer, mobile money) supported without code changes.
- **KYC Workflow** — Built-in Know Your Customer verification pipeline with admin review, approval, and rejection flows.
- **Two-Factor Authentication** — Email OTP, SMS OTP, and Google Authenticator (TOTP) — configurable per-deployment.
- **Dual Frontend Themes** — Ships with two complete frontend templates (`basic` and `sunfyre`), switchable from the admin panel in a single click.
- **Dynamic SEO Manager** — Configure meta titles, keywords, descriptions, and Open Graph tags per page without touching code.
- **Multi-Language Engine** — Add, edit, and publish translations from the admin panel. New languages require zero code changes.
- **Notification Template Manager** — Fully customizable email and SMS notification templates for every system event.
- **One-Click Extensions** — Integrated UI toggles for Google Analytics, Google reCAPTCHA v2, Tawk.to Live Chat, and Facebook Comments.
- **Cron-Based Automation** — Scheduled background tasks for incomplete game resolution and system maintenance.

### Security

- Bank-grade SSL/TLS encryption across all data in transit.
- Secure-password policy enforcement (configurable).
- Automatic HTTPS enforcement via admin toggle.
- IP-based login logging for every user session.
- CSRF protection, input sanitization, and SQL injection prevention — all native to the Laravel framework.
- Separate admin and user authentication guards.

---

## Server Requirements

| Requirement    | Minimum                                                                                                                           |
| -------------- | --------------------------------------------------------------------------------------------------------------------------------- |
| PHP            | 8.2+                                                                                                                              |
| MySQL          | 8.0+ or MariaDB 10.6+                                                                                                             |
| PHP Extensions | BCMath, Ctype, cURL, DOM, Fileinfo, GD, JSON, Mbstring, OpenSSL, PCRE, PDO, pdo_mysql, Tokenizer, XML, Filter, Hash, Session, Zip |
| PHP Functions  | `allow_url_fopen`, `file_get_contents` (must be enabled)                                                                          |
| Web Server     | Apache (with `mod_rewrite` enabled) or Nginx                                                                                      |
| Composer       | 2.x                                                                                                                               |
| Node.js        | 18+ (required only for frontend asset compilation)                                                                                |

---

## Installation Guide

### Option A: Web Installer (Recommended for Shared Hosting / cPanel)

This is the fastest path to a running installation and requires no command-line access.

1. **Upload files** — Extract the project archive and upload all contents to your web server's document root (e.g., `public_html`).
2. **Verify entry point** — Confirm that both `index.php` and `.htaccess` are present at the root level.
3. **Launch the installer** — Open `http://your-domain.com/install/index.php` in your browser.
4. **Complete the setup wizard:**
   - **Branding** — Set your casino name, developer brand, website URL, and support email. These values are written directly to the generated `.env` file.
   - **Database** — Provide your MySQL host, port, database name, and credentials.
   - **Admin Account** — Set the administrator username, email address, and password.
5. **Remove the installer** — Once setup is complete, **delete the `install/` directory** from your server immediately to prevent unauthorized re-installation.

### Option B: CLI Setup (VPS / Local Development)

For developers and DevOps engineers deploying to a VPS or a local environment.

```bash
# 1. Navigate to the core Laravel application directory
cd core

# 2. Install PHP dependencies
composer install --no-dev --optimize-autoloader

# 3. Install and compile frontend assets
npm install && npm run build

# 4. Set up your environment file
cp .env.example .env

# 5. Edit the .env file with your settings (see Configuration below)
nano .env

# 6. Generate a unique application encryption key
php artisan key:generate

# 7. Import the database schema and seed data
mysql -u YOUR_DB_USER -p YOUR_DATABASE < ../install/database.sql

# 8. Cache configuration, routes, and views for production
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 9. Set correct filesystem permissions
chmod -R 775 storage bootstrap/cache
```

---

## Branding & Configuration

All brand identity is defined in `.env` — the entire platform adapts to your brand automatically with no code changes required.

```dotenv
# ── Application Identity ──────────────────────────────────────────────────────
# Used in page titles, admin panel headers, emails, and the web installer.
APP_NAME="AddisWin Casino"

# ── Developer / White-Label Identity ─────────────────────────────────────────
# Displayed in the admin panel footer, system information page, and outbound emails.
APP_BRAND_NAME="Scoware"
APP_BRAND_URL="https://scoware.com"
APP_BRAND_EMAIL="support@scoware.com"

# ── Core Environment ──────────────────────────────────────────────────────────
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com
APP_TIMEZONE=UTC

# ── Database ──────────────────────────────────────────────────────────────────
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=addiswin_casino
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password
```

### Global PHP Helper Functions

These helpers are available anywhere in the application codebase:

```php
config('app.name')         // → "AddisWin Casino"
config('app.brand_name')   // → "Scoware"
config('app.brand_url')    // → "https://scoware.com"
config('app.brand_email')  // → "support@scoware.com"

brandName()   // Shorthand for config('app.brand_name')
brandUrl()    // Shorthand for config('app.brand_url')
brandEmail()  // Shorthand for config('app.brand_email')

gs('site_name')  // Reads the active site name from the database general_settings table
```

> **Note on `gs('site_name')`:** This reads from the `general_settings` database table and is the value displayed in frontend page titles, notification templates, and footers. It is seeded from `APP_NAME` at installation time and can be updated by administrators at runtime via the General Settings panel without a deployment.

---

## Directory Structure

```
addiswin-casino/
├── assets/              # Publicly accessible compiled frontend assets (CSS, JS, images, fonts)
├── core/                # Main Laravel 11 application (must NOT be directly web-accessible)
│   ├── app/             # Application logic: Controllers, Models, Middleware, Helpers, Services
│   ├── bootstrap/       # Framework bootstrapping and compiled caches
│   ├── config/          # Configuration files: app.php, database.php, mail.php, timezone.php, etc.
│   ├── database/        # Migration definitions (canonical schema is in install/database.sql)
│   ├── resources/       # Blade views, language files, and uncompiled frontend assets
│   │   ├── views/
│   │   │   ├── admin/           # Admin panel Blade templates
│   │   │   ├── templates/
│   │   │   │   ├── basic/       # "Basic" frontend theme
│   │   │   │   └── sunfyre/     # "Sunfyre" frontend theme
│   │   │   └── partials/        # Shared Blade components and layout includes
│   │   └── lang/en.json         # English UI string translations
│   ├── routes/          # Route definitions: web.php, admin.php, api.php, user.php, ipn.php
│   ├── storage/         # Application-generated files: logs, sessions, cached views, uploads
│   └── vendor/          # Composer-managed PHP dependencies (do not modify)
├── install/
│   ├── index.php        # Web-based setup wizard — DELETE this directory after installation
│   └── database.sql     # Complete MySQL schema with all seed and configuration data
├── .htaccess            # Apache rewrite rules (routes all public traffic into core/public)
└── index.php            # Public entry point
```

---

## Games Catalogue

AddisWin Casino ships with **19 fully playable games**, each configurable with its own min/max bet limits, win percentages, and game-specific rules managed from the admin panel.

| #   | Game                | Alias                 | Type         |
| --- | ------------------- | --------------------- | ------------ |
| 1   | Head & Tail         | `head_tail`           | Prediction   |
| 2   | Rock Paper Scissors | `rock_paper_scissors` | Prediction   |
| 3   | Spin Wheel          | `spin_wheel`          | Wheel        |
| 4   | Number Guessing     | `number_guess`        | Prediction   |
| 5   | Dice Rolling        | `dice_rolling`        | Dice         |
| 6   | Card Finding        | `card_finding`        | Card         |
| 7   | Number Slot         | `number_slot`         | Slot         |
| 8   | Pool Number         | `number_pool`         | Pool         |
| 9   | Roulette            | `roulette`            | Table        |
| 10  | Casino Dice (Craps) | `casino_dice`         | Dice / Table |
| 11  | Keno                | `keno`                | Lottery      |
| 12  | Blackjack           | `blackjack`           | Card / Table |
| 13  | Mines               | `mines`               | Grid         |
| 14  | Poker (Five-Card)   | `poker`               | Card / Table |
| 15  | Color Prediction    | `color_prediction`    | Prediction   |
| 16  | Crazy Times         | `crazy_times`         | Wheel / Live |
| 17  | Dream Catcher       | `dream_catcher`       | Money Wheel  |
| 18  | Andar Bahar         | `andar_bahar`         | Card / Live  |
| 19  | Pai Gow Poker       | `pai_gow_poker`       | Card / Table |

All games support **demo mode** (configurable demo balance) and **real-money mode** simultaneously.

---

## Admin Panel Reference

Access the admin panel at `https://yourdomain.com/admin`.

| Section                    | Description                                                                                                         |
| -------------------------- | ------------------------------------------------------------------------------------------------------------------- |
| **Dashboard**              | Real-time metrics: total deposits, withdrawals, new registrations, active games                                     |
| **Users**                  | Browse, search, filter, ban/unban, adjust balances, review KYC submissions, and inspect full login history per user |
| **Games**                  | Configure all 19 games — enable/disable, adjust bet limits, set win probabilities, and edit game instructions       |
| **Deposits**               | Configure automated and manual payment gateways; approve or reject pending manual deposits                          |
| **Withdrawals**            | Configure withdrawal methods and limits; process, approve, or reject pending requests                               |
| **Payment Gateways**       | Activate, deactivate, and configure credentials for all 26+ bundled payment providers                               |
| **Extensions**             | One-click enable/disable for Google Analytics, reCAPTCHA v2, Tawk.to, and Facebook Comments                         |
| **Frontend Manager**       | Edit all page sections, banners, policy pages, FAQs, testimonials, and contact information                          |
| **Language Manager**       | Add new languages and translate every UI string in the system                                                       |
| **Notification Templates** | Customize every email and SMS template for system events (deposits, withdrawals, KYC, etc.)                         |
| **General Settings**       | Configure site title, currency, timezone, color scheme, registration toggle, and bonus rules                        |
| **Branding**               | Upload site logo and favicon                                                                                        |
| **System Information**     | View PHP version, Laravel version, build number, and active template                                                |
| **Reports**                | Full transaction history, login logs, notification history, balance transfer records                                |
| **Referral Configuration** | Set up and adjust multi-tier commission percentages for the affiliate program                                       |
| **Subscribers**            | Manage newsletter subscribers and send bulk email campaigns                                                         |
| **Support Tickets**        | Manage and respond to player support requests                                                                       |
| **Cron Jobs**              | Monitor and manage scheduled background automation tasks                                                            |

---

## Payment Gateways

All gateways are configured via **Admin → Payment Gateways → Automatic**. Credentials are entered through the UI — no code changes required.

### Fiat / Card Gateways

| Gateway           | Notes                               |
| ----------------- | ----------------------------------- |
| Stripe Hosted     | Standard hosted checkout            |
| Stripe Storefront | On-page card form (Stripe.js)       |
| Stripe Checkout   | Webhook-confirmed checkout session  |
| PayPal Standard   | Classic PayPal redirect             |
| PayPal Express    | PayPal SDK integration              |
| Razorpay          | India — card, UPI, netbanking       |
| Skrill            | International e-wallet              |
| Payeer            | International e-wallet              |
| PerfectMoney      | USD / EUR digital currency          |
| Paystack          | Africa — NGN, GHS, KES, ZAR         |
| Flutterwave       | Africa — multi-currency             |
| Instamojo         | India — UPI and local methods       |
| Authorize.Net     | Card processing (US-centric)        |
| Mollie            | Europe — multi-method               |
| PayTM             | India — UPI and wallet              |
| 2Checkout         | International card processing       |
| NMI               | White-label payment gateway         |
| Checkout.com      | International card processing       |
| Mercado Pago      | Latin America                       |
| Cashmaal          | PKR / USD                           |
| SslCommerz        | Bangladesh — BDT and multi-currency |
| Aamarpay          | Bangladesh — BDT                    |
| bKash             | Bangladesh — BDT mobile money       |

### Cryptocurrency Gateways

| Gateway           | Supported Currencies                     |
| ----------------- | ---------------------------------------- |
| Coinbase Commerce | BTC, ETH, and 100+ cryptocurrencies      |
| Coinpayments      | BTC, LTC, ETH, DOGE, USDT, XRP, and 100+ |
| CoinGate          | BTC, ETH, and major cryptocurrencies     |
| Binance Pay       | BTC, BNB, USD stablecoins                |
| BTCPay Server     | BTC, LTC (self-hosted node)              |
| Now Payments      | BTC, ETH, ADA, XRP, and 50+ others       |
| Blockchain.info   | BTC only                                 |

### Manual Gateways

An unlimited number of manual gateways (bank transfer, mobile money, hawala, etc.) can be created from **Admin → Payment Gateways → Manual** without writing code.

---

## Email & SMS Configuration

Configure your mail driver in `.env`:

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

SMS providers (Twilio, Vonage/Nexmo, MessageBird, Infobip, TextMagic, SMSBroadcast, and custom webhooks) are configured from **Admin → Settings → Notification**.

---

## Web Server Configuration

### Apache

The bundled `.htaccess` file handles all routing. Ensure `AllowOverride All` is set in your virtual host configuration and that `mod_rewrite` is enabled:

```bash
a2enmod rewrite
systemctl restart apache2
```

### Nginx

```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /var/www/addiswin-casino;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Protect the Laravel core from direct web access
    location ~ ^/core/ {
        deny all;
        return 404;
    }

    # Deny access to hidden files (.env, .git, etc.)
    location ~ /\. {
        deny all;
    }
}
```

---

## Useful Artisan Commands

```bash
# ── Cache Management ──────────────────────────────────────────────────────────

# Clear all caches (use during development or after config changes)
php artisan optimize:clear

# Warm all production caches in one command
php artisan optimize

# Cache individual layers
php artisan config:cache
php artisan route:cache
php artisan view:cache

# ── Diagnostics ───────────────────────────────────────────────────────────────

# List all registered routes
php artisan route:list

# Display all registered event listeners
php artisan event:list

# ── Background Processing ─────────────────────────────────────────────────────

# Start the queue worker for background jobs (emails, notifications)
php artisan queue:work --sleep=3 --tries=3

# Run the scheduler (add this to your server crontab)
# * * * * * cd /path/to/core && php artisan schedule:run >> /dev/null 2>&1
php artisan schedule:run
```

---

## Security Best Practices for Production

| Action                         | Details                                                                                                                                       |
| ------------------------------ | --------------------------------------------------------------------------------------------------------------------------------------------- |
| **Delete the installer**       | Remove the `install/` directory immediately after completing installation.                                                                    |
| **Set file permissions**       | `chmod -R 775 core/storage core/bootstrap/cache`                                                                                              |
| **Protect `core/`**            | Ensure the Laravel application directory is not directly web-accessible. The bundled `.htaccess` and the Nginx config above both handle this. |
| **Enable SSL**                 | Force HTTPS from **Admin → System Configuration → Force SSL**. Obtain a certificate via Let's Encrypt if needed.                              |
| **Change default credentials** | Update the default admin password immediately after first login. Use a strong, unique password.                                               |
| **Set `APP_DEBUG=false`**      | Never enable debug mode on a production server — it exposes full stack traces and environment details.                                        |
| **Rotate the app key**         | After cloning or moving environments, run `php artisan key:generate` to regenerate `APP_KEY`.                                                 |
| **Environment file**           | Ensure `.env` is never publicly accessible. Verify it is excluded from your web root or blocked via server config.                            |
| **Database access**            | Create a dedicated MySQL user for the application with only the necessary privileges. Avoid using `root`.                                     |

---

## Troubleshooting

**Blank page or 500 error after installation:**
Run `php artisan optimize:clear` and check `core/storage/logs/laravel.log` for detailed error messages. Ensure all required PHP extensions are installed.

**`php artisan config:cache` fails:**
Check `core/config/timezone.php` — it must use the `return` syntax, not `<?php ...`. If it defines variables without returning, the caching step will fail.

**`php artisan route:cache` fails with duplicate route name error:**
Check `core/routes/admin.php` for duplicate named routes. Every named route must be unique across all route files.

**Assets not loading (CSS/JS returning 404):**
Ensure the `assets/` directory is present in your web root and accessible. Run `npm run build` in `core/` if you have made template changes.

**Emails not sending:**
Verify `MAIL_*` values in `.env`. Run `php artisan queue:work` if the mail driver is set to `queue`. Check spam folders and verify the sending domain's SPF/DKIM records.

**Cron jobs not running:**
Verify that the crontab entry is correctly set: `* * * * * cd /path/to/core && php artisan schedule:run >> /dev/null 2>&1`. The scheduled task runner requires the web server user (e.g., `www-data`) to have read/execute permission on the application.

---

## Changelog

### v3.8 (Current Release)

- All license enforcement, phone-home telemetry, and third-party vendor branding fully removed.
- Complete white-label branding system: `APP_NAME`, `APP_BRAND_NAME`, `APP_BRAND_URL`, and `APP_BRAND_EMAIL` now drive all identity elements platform-wide.
- Web installer updated with dedicated branding step; writes all brand variables to the generated `.env` file.
- `config/timezone.php` corrected to use proper `return` syntax — resolves `php artisan config:cache` failure on fresh installs.
- Duplicate route names in `routes/admin.php` resolved — eliminates `php artisan route:cache` failure.
- Laravel infrastructure tables (`cache`, `cache_locks`, `sessions`, `jobs`, `job_batches`, `failed_jobs`) added to `install/database.sql` for clean CLI installs.
- `APP_TIMEZONE` environment variable now respected as a fallback in `config/app.php`.
- New global PHP helpers registered: `brandName()`, `brandUrl()`, `brandEmail()`.
- Admin system information page updated: label changed from "ViserAdmin Version" to "Build Version".
- All seeded frontend content (site name, email sender, policy page text) updated to AddisWin Casino / Scoware branding.
- Added Dream Catcher (id 17), Andar Bahar (id 18), and Pai Gow Poker (id 19) to the games catalogue.
- bKash gateway (id 59) added for Bangladesh BDT mobile money support.

### v3.7

- Keno game engine introduced with configurable level-based payout structure.
- Poker (Five-Card Draw) game added with full hand ranking logic.
- Color Prediction and Crazy Times games added.
- Gameplay bonus system: automatic rewards after a configurable number of rounds.
- Balance transfer feature between registered player accounts.
- Social login support: Google, Facebook, LinkedIn via Laravel Socialite.

---

## Developer Notes

### Adding a New Game

1. Add a row to the `games` table with the appropriate `alias`, game rules, `min_limit`, `max_limit`, and `probable_win`.
2. Create a controller in `app/Http/Controllers/Games/` following the existing game controller pattern.
3. Register the game routes in `routes/user.php`.
4. Create the Blade view in `resources/views/templates/{theme}/games/`.
5. Add the game to the admin game management interface in `resources/views/admin/game/`.

### Adding a New Payment Gateway

1. Create a new gateway class in `app/Http/Controllers/Gateway/` implementing the standard gateway interface used by existing providers.
2. Register the gateway in `config/constants.php` (or equivalent gateway registry).
3. Insert a corresponding row in the `gateways` table via a database migration.
4. Create the gateway's IPN handler method and register it in `routes/ipn.php`.

### Adding a New Language

No code changes needed. From **Admin → Language Manager**, create a new language and use the built-in translation editor to populate all string keys.

---

## Support & Contact

| Channel        | Details                            |
| -------------- | ---------------------------------- |
| Email Support  | support@scoware.com                |
| Developer Site | [scoware.com](https://scoware.com) |
| Platform       | AddisWin Casino — v3.8             |
| Framework      | Laravel 11 / PHP 8.2+              |
| Build          | 5.1.19                             |
