# Xaxino - Ultimate Casino Platform

Xaxino is a premium, enterprise-grade Casino and Prediction platform built with Laravel 11. It provides a comprehensive solution for managing an online casino, offering automated payment gateways, a secure architecture, and an extensive admin dashboard. There are an estimated 2.8 billion predictors across the globe, and this platform provides everything you need to start operating immediately.

---

## 🚀 Tech Stack

- **Backend Framework:** Laravel 11.x
- **Language:** PHP 8.3+
- **Frontend Build Tools:** Vite, Axios
- **Database:** MySQL 8.0+ or MariaDB 10.6+
- **Integrations included:** 20+ Automated Payment Gateways (Stripe, Razorpay, Mollie, Coingate, Authorize.net, etc.)
- **Notification Services:** Twilio, SendGrid, Mailjet, Vonage, MessageBird.

---

## 🛠️ Key Features

- **Robust Admin Dashboard:** Full oversight over users, transactions, game histories, and system configuration directly from the UI.
- **Automated & Manual Payments:** Integrated support for over 20 global payment providers and the ability to configure unlimited manual deposit/withdrawal methods.
- **Dynamic SEO Manager:** Built-in SEO management allows you to configure keywords, meta descriptions, and Open Graph content for the entire platform without code modifications.
- **Multi-Language Support:** Fully customizable localization manager. Add, edit, and translate any string on the platform directly from the admin panel.
- **Plug-and-Play Extensions:** Easy UI toggle integration for Google Analytics, reCAPTCHA v2, Tawk.to Live Chat, and Facebook Comments.
- **Enterprise Security:** Built-in KYC (Know Your Customer) verification workflow, Email & SMS verifications, Google Authenticator (2FA), and automatic HTTPS (SSL) enforcement options.

---

## ⚙️ Server Requirements

Ensure your hosting environment meets the following criteria before deployment:
- **PHP** >= 8.3
- **MySQL** >= 8.0 or MariaDB >= 10.6
- **Required PHP Extensions:** `BCMath`, `Ctype`, `cURL`, `DOM`, `Fileinfo`, `GD`, `JSON`, `Mbstring`, `OpenSSL`, `PCRE`, `PDO`, `pdo_mysql`, `Tokenizer`, `XML`, `Filter`, `Hash`, `Session`, `Zip`
- **Required PHP Functions:** `allow_url_fopen()` and `file_get_contents()` enabled.

---

## 📥 Installation Guide

### Option 1: Easy Web Installer (Recommended for Shared Hosting / cPanel)
1. Upload and extract the project files to the root of your web server (e.g., `public_html`).
2. Ensure you have moved both `index.php` and `.htaccess` exactly as they are.
3. Open your browser and navigate to `http://your-site-url/install/index.php`.
4. Follow the intuitive installation GUI. You will be prompted to enter your database credentials and set up the main administrative account.

### Option 2: CLI Development Setup (For VPS/Local Development)
If you prefer managing the environment manually:
1. Navigate to the `core/` directory: `cd core`
2. Install PHP dependencies: `composer install`
3. Install and compile frontend assets: `npm install && npm run build`
4. Copy the environment variables: `cp .env.example .env`
5. Configure `.env` with your database and environment settings.
6. Generate an application key: `php artisan key:generate`
7. Run the database migrations: `php artisan migrate`

---

## 📁 Directory Structure Breakdown

Because this application relies on a secure architecture, the structure separates public-facing assets from core logic:

```text
xaxino-v3.8/
├── assets/          # Publicly accessible compiled frontend assets (CSS, JS, Images)
├── core/            # Main Laravel 11 application (Protected, Should not be Web Accessible directly)
│   ├── app/         # Application logic (Controllers, Models, Middleware)
│   ├── bootstrap/   # Laravel bootstrap and cache directory
│   ├── config/      # Application configuration files
│   ├── database/    # Database migrations, seeders, and factories
│   ├── resources/   # Uncompiled assets, Blade UI views, and language files
│   ├── routes/      # Application routes definition (web.php, api.php)
│   ├── storage/     # App-generated files (logs, sessions, cached views)
│   └── vendor/      # Composer-managed PHP dependencies
├── install/         # Web installer script (Should be deleted after successful installation!)
├── .htaccess        # Required Apache routing rules (Redirects traffic to core)
└── index.php        # Core entry point
```

---

## 🔒 Security Best Practices for Production

- **Protect the core:** Make sure the `core/` folder is inaccessible from browser URL routing (the included `.htaccess` helps, but virtual hosts are better). 
- **Permissions:** Set appropriate writable server permissions (`chmod 775`) for `core/storage` and `core/bootstrap/cache`.
- **Delete Installer:** Always remove the `install/` directory located in the root as soon as you have finished setting up the project to avoid unauthorized re-installation attempts.
- **Enable SSL:** Force SSL internally from the *System Configuration* page in the Admin Dashboard.
- **Change Default Details:** Change default admin passwords and API keys routinely.

---

## 🤝 Developer & Support

For licensing, deeper code troubleshooting, or additional feature requests, please consult the product owner:
- **Created By:** Viserlab
- **Documentation:** Review the `Documentation/` folder if bundled with your download.
- **Email Support:** support@viserlab.com

> **Disclaimer:** The codebase includes integrated protection systems and requires a valid Purchase/License Code during setup to activate premium features.