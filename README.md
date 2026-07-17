# Lumos Studio — Premium Digital Wedding Invitation Platform

A modern, highly interactive, and self-hosted digital wedding invitation and planning platform built with **Pure PHP**, **MySQL**, and **Three.js**. It features immersive 3D animated invitations, a comprehensive couple's dashboard for wedding management, real-time RSVP tracking, guest photo sharing, and a robust administrative billing workflow.

🌐 **Live Platform:** [https://invitation.iceiy.com/](https://invitation.iceiy.com/)

---

## 🌟 Key Features

### ✉️ Immersive Guest Experience (Digital Invitations)
- **Stunning 3D Animations**: WebGL-powered 3D elements using Three.js (e.g., interactive floral wreaths, rotating wedding rings, flying butterflies, falling petals, and floating doves).
- **Multiple Premium Themes**: A diverse collection of beautifully designed, responsive themes (e.g., Rustic Boho, Royal Classic, Minimal Light, Premium Gold, Indian Royal).
- **Interactive Virtual Envelope**: Beautiful custom-styled virtual envelope with initials monogram wax seal and realistic unsealing animation.
- **Guest Shared Gallery**: Guests can take and upload candid photos directly from their phones, automatically optimized and compressed client-side into WebP format.
- **Guest Memory Wall**: An interactive wall where guests can leave heartfelt messages and blessings for the couple.
- **Personalized Welcomes**: Displays the guest's name and specifies the exact number of reserved seats (e.g., "We have reserved 3 seat(s) in your honor").
- **Live Countdown & Timelines**: A real-time ticking countdown timer to the wedding day, alongside animated event timelines.
- **Google Maps Integration**: Direct navigation links and embedded 3D tilt-cards for all wedding events.
- **Add to Calendar**: Interactive dropdown allowing guests to quickly save events to Google Calendar, Apple iCal, or Outlook.
- **Instant RSVP Form**: Guests can submit whether they are attending or declining, along with optional dietary requirements or notes.
- **Automated Email Delivery**: Integrated SMTP via PHPMailer for reliable delivery of transactional emails like RSVPs and account notifications.

### 👰 Couple Dashboard (User Panel)
- **Interactive Overview**: Real-time analytics showing total guests, opened invitations, attending (RSVP) guests, and declined RSVPs.
- **Guest Management**: Search, filter, add, and delete guests. Define reserved seat counts and track who has opened their invitation.
- **Personalized Sharing**: Generate and copy customized invite text and unique, personalized sharing links for each guest with one click.
- **Wedding Programme**: Manage multiple events (e.g., Ceremony, Reception, Homecoming) with date, time, location, and Google Maps integration.
- **Sweet Moments Gallery**: Bulk-upload engagement photos for a cinematic Ken-Burns style slideshow on the invitation.
- **Love Story Editor**: Write and format a personalized love story to share with guests on the interactive timeline.
- **Wedding Checklist**: Dynamic progress bar showcasing the percentage of completed planning tasks.
- **Manual Payment Workflow**: Upload a bank transaction slip directly to the dashboard to request account activation from the administrator.

### 👑 Super Admin Panel
- **Verification Dashboard**: Monitor registered couples, view uploaded bank slips in a lightbox, and activate/deactivate accounts with a single click.
- **Role-Based Access**: Restricts administrative pages exclusively to users with the `admin` role.
- **Dynamic Sidebar**: Sidebar menu adapts automatically based on whether the logged-in user is an Admin or a Couple.

---

## 📂 Directory Structure

```text
invite/
├── config/
│   ├── config.php            # Database connections and configuration
│   ├── mailer.php            # SMTP email configuration
│   ├── PHPMailer/            # PHPMailer library files
│   └── installer.php         # Auto-setup script
├── dashboard/
│   ├── admin/                # Super Admin Panel
│   │   ├── index.php         # Billing & Verification Dashboard
│   │   ├── admin_refunds.php # Refund management
│   │   └── admin_delete_account.php # Account management
│   ├── user/                 # Couple's Dashboard
│   │   ├── index.php         # Analytics overview
│   │   ├── guests.php        # Guest & RSVP management
│   │   ├── events.php        # Events timeline & maps
│   │   ├── gallery.php       # Engagement photos upload
│   │   ├── guest_gallery.php # Manage photos uploaded by guests
│   │   ├── checklist.php     # Wedding tasks progress
│   │   ├── payment.php       # Subscription & Payments
│   │   └── settings.php      # Profile & security settings
│   ├── layouts/              # Shared dashboard layouts (sidebar, header, footer)
│   ├── login.php             # Dual-role secure login
│   ├── register.php          # Account registration
│   └── logout.php            # Session termination
├── layouts/                  # Landing page and platform layouts
├── templates/                # 3D Digital Invitation Themes
│   ├── premium_gold.php
│   ├── rustic_boho.php
│   ├── royal_classic.php
│   └── ...
├── uploads/                  # User & Guest uploaded assets (Gallery, Slips, etc.)
├── document/                 # System documentation and manuals
├── index.php                 # Platform Landing Page (SaaS Homepage)
├── features.php              # Features showcase page
├── pricing.php               # Subscription pricing page
├── themes.php                # Theme showcase and preview page
├── privacy.php               # Privacy Policy
├── refund.php                # Refund Policy
├── terms.php                 # Terms & Conditions
├── invite.php                # Virtual envelope unseal & core invitation logic
├── view_invitation.php       # Dynamic invitation viewer and RSVP processor
├── calendar.php              # ICS Calendar file generator
└── .env                      # Environment variables
```

---

## 🛠️ Technology Stack
- **Backend**: Pure PHP 8.x (using secure PDO Prepared Statements)
- **Database**: MySQL 8.x
- **Frontend**: HTML5, CSS3, ES6 JavaScript, Bootstrap 5
- **3D & Animations**: Three.js, GSAP, Anime.js
- **Libraries**: 
  - [Compressor.js](https://github.com/fengyuanchen/compressorjs) (Client-side image compression and WebP conversion for fast uploads)
  - [PHPMailer](https://github.com/PHPMailer/PHPMailer) (Secure SMTP email delivery)
  - FontAwesome 6 (Icons)
  - Google Fonts (Cormorant Garamond, Fraunces, Inter, etc.)

---

## 💻 Installation & Setup

### 1. Database Configuration
1. Open **phpMyAdmin** on your server.
2. Create a database named `invite` (or any custom name).
3. Import the schema SQL file to construct the empty database structure.

### 2. File Deployment
Upload all project directories to your web server:
- For **Localhost (WampServer)**: Place files in `C:\wamp64\www\invite\`
- For **Hosted Servers (e.g. unaux.com)**: Upload files directly to the root `public_html` or `htdocs` directory.

### 3. Connection & Email Setup
Open `config/config.php` and configure your database parameters:
```php
$host = 'localhost';
$dbname = 'invite';
$username = 'root'; // Your DB username
$password = '';     // Your DB password
```

Open `config/mailer.php` to configure your SMTP settings for outgoing emails:
```php
define('MAIL_SMTP_HOST', 'smtp.gmail.com');
define('MAIL_SMTP_PORT', 587);
define('MAIL_SMTP_USERNAME', 'your-email@gmail.com');
define('MAIL_SMTP_PASSWORD', 'your-app-password');
```

### 4. Enable Apache Rewrite Module (For Pretty Slug URLs)
Make sure `mod_rewrite` is enabled on your server to allow the `.htaccess` slug routing to work correctly.

- **WampServer**: Left-click Wamp Icon ➡️ **Apache** ➡️ **Apache modules** ➡️ check **`rewrite_module`**. Restart all services.
- **httpd.conf**: Make sure `AllowOverride All` is set for your web root directory (e.g., `www/`), and that `<Directory />` remains `AllowOverride none` for server security.

### 5. Adjust `.htaccess` based on Hosting Environment
- **WampServer (Localhost)**:
  Make sure your `.htaccess` uses `/invite/` as the rewrite base:
  ```apache
  RewriteBase /invite/
  ```
- **Hosted Server (Production Root)**:
  Make sure your `.htaccess` points directly to the web root:
  ```apache
  RewriteBase /
  ```

### 6. Cloudflare HTTPS Redirect Loop Fix (For Production)
If hosting on free servers with Cloudflare, ensure your **SSL encryption mode** in Cloudflare is changed from **Flexible** to **Full** (or **Full Strict**). This prevents infinite `ERR_TOO_MANY_REDIRECTS` loops.

---

## 🔒 Security Practices Built-In
- **PDO Prepared Statements**: Safe from SQL Injection.
- **Password Hashing**: Utilizes native PHP `password_hash` using the secure `PASSWORD_DEFAULT` algorithm.
- **Role Guards**: Prevents non-admin accounts from loading administrative templates.
- **Activation Block**: Unactivated accounts will display a "Coming Soon" virtual seal card to guests. No dynamic wedding data is shared with the public until the administrator approves the billing slip.
- **Isolated Sessions**: Writes secure session cookies locally within a private `/sessions/` directory, resolving common session-loss issues found on shared hosting platforms.

---

## 👨‍💻 Author
- **Lumos Studio** — Designed & Developed by Hathisa Thissara (Sri Lanka)
