මෙන්න ඔයාගේ මුළු Wedding Invitation පද්ධතියටම ගැලපෙන, GitHub එකට හෝ ඔයාගේ Project Folder එකේ තියාගන්න පුළුවන් ඉතාමත්ම පැහැදිලි සහ වෘත්තීය මට්ටමේ **`README.md`** ගොනුවක්. 

මෙය ඉංග්‍රීසි භාෂාවෙන් සකස් කර ඇති අතර (GitHub/Documentation සඳහා සම්මතය එය බැවින්), සර්වර් එකක සහ Localhost එකක මේක Setup කරගන්නා ආකාරය පියවරෙන් පියවර මෙහි ඇතුලත් කර ඇත.

ඔයාගේ ප්‍රධාන Root folder එකේ (`invite/` එක ඇතුලේ) **`README.md`** නමින් අලුත් file එකක් හදලා මේ පහත කේතය ඇතුලත් කරන්න:

```markdown
# Lumus Studio — Digital Wedding Invitation Platform

A modern, lightweight, self-hosted digital wedding invitation and planning platform built in **Pure PHP** and **MySQL**. It features beautifully animated digital invitations, real-time RSVP tracking, guest-specific seat reservations, wedding checklists, photo gallery uploads with client-side WebP compression, and a robust administrative billing workflow.

---

## 🌟 Key Features

### 👰 Couple Dashboard (User Panel)
- **Interactive Overview**: Real-time counters showing total guests, opened invitations, attending (RSVP) guests, and declined RSVPs.
- **Personalized Sharing**: Copies customized invite text and personalized sharing links with one click.
- **Seat Reservations**: Specify how many seats are reserved per guest party when adding them.
- **Guest Management**: Search, filter, add, and delete guests. Track who has opened their invitation and when.
- **Wedding Programme**: Manage multiple events (e.g., Poruwa Ceremony, Reception, Homecoming) with date, time, location, and Google Maps integration.
- **Wedding Checklist**: Dynamic progress bar showcasing the percentage of completed planning tasks.
- **Sweet Moments Gallery**: Bulk-upload engagement photos.
- **Love Story Editor**: Write a personalized love story to share with guests on the invite.
- **Flexible Settings**: Update wedding details (names, dates) and change accounts passwords securely.
- **Manual Payment Workflow**: Upload a bank transaction slip directly to the dashboard to request activation from the administrator.

### 👑 Super Admin Panel
- **Role-Based Access**: Restricts administrative pages exclusively to users with the `admin` role.
- **Dynamic Sidebar**: Sidebar menu updates automatically based on whether the logged-in user is an Admin or a Couple.
- **Verification Dashboard**: Monitor registered couples, view uploaded bank slips in a lightbox, and activate/deactivate accounts with a single click.

### ✉️ Guest Experience (Digital Invitation)
- **Interactive Envelope**: Beautiful custom-styled virtual envelope with initials monogram wax seal and realistic unsealing animation.
- **Personalized Welcomes**: Displays the guest's name and specifies the exact number of reserved seats (e.g., "We have reserved 3 seat(s) in your honor").
- **Live Countdown**: A real-time ticking countdown timer to the wedding day.
- **Google Maps Integration**: Direct navigation links for all added wedding events.
- **Add to Calendar**: Interactive dropdown allowing guests to quickly save events to Google Calendar, Apple iCal, or Outlook.
- **Optimized Performance**: Native client-side photo gallery rendering utilizing highly compressed `.webp` formats.
- **Instant RSVP Form**: Guests can submit whether they are attending or declining, along with an optional message/dietary note.
- **Preview Bypass**: Allows couples to preview their invitations safely without writing RSVP data or entering their own phone numbers.

---

## 📂 Directory Structure

```text
invite/
├── config/
│   └── config.php            # Database connections and session paths
├── dashboard/
│   ├── layouts/
│   │   ├── header.php        # Dynamic sidebar and header templates
│   │   └── footer.php        # Common footer and JS libraries
│   ├── admin_dashboard.php   # Super admin billing verification panel
│   ├── index.php             # Couple's overview dashboard
│   ├── guests.php            # Guest management and seats reservations
│   ├── events.php            # Event timelines & mapping configurations
│   ├── gallery.php           # Our Love Story & Gallery photo uploads
│   ├── checklist.php         # Tasks checklist & progress bar
│   ├── settings.php          # Profile details & password changing
│   ├── login.php             # Secure dual-role login system
│   ├── register.php          # Step-by-step registration wizard
│   └── logout.php            # Session destruction & redirect
├── templates/
│   └── premium_gold.php      # Main responsive wedding invitation layout
├── uploads/
│   ├── gallery/              # Highly compressed WebP wedding moments
│   └── slips/                # Uploaded bank transaction receipts
├── sessions/                 # Self-hosted stable session handler directory
├── .htaccess                 # Pretty URL rewrite rules (slug configurations)
├── db.php                    # Public root database connection fallback
├── invite.php                # Virtual envelope unseal & login landing
└── view_invitation.php       # Dynamic invitation viewer and RSVP processor
```

---

## 🛠️ Technology Stack
- **Backend**: Pure PHP 8.x (using secure PDO Prepared Statements to prevent SQL injection)
- **Database**: MySQL 8.x
- **Frontend**: HTML5, CSS3, ES6 JavaScript, Bootstrap 5
- **Libraries**: 
  - [Compressor.js](https://github.com/fengyuanchen/compressorjs) (Client-side fast image compression and WebP conversion)
  - FontAwesome 6 (Icons)
  - Google Fonts (Cormorant Garamond, Great Vibes, Inter)

---

## 💻 Installation & Setup

### 1. Database Configuration
1. Open **phpMyAdmin** on your server.
2. Create a database named `invite` (or any custom name).
3. Import the clean schema SQL commands to construct the empty database structure.

### 2. File Deployment
Upload all project directories to your web server:
- For **Localhost (WampServer)**: Place files in `C:\wamp64\www\invite\`
- For **Hosted Servers (e.g. unaux.com)**: Upload files directly to the root `public_html` or `htdocs` directory.

### 3. Connection Setup
Open `config/config.php` (and `db.php` if applicable) and configure your database parameters:
```php
$host = 'localhost';
$dbname = 'invite';
$username = 'root'; // Your DB username
$password = '';     // Your DB password
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
If hosting on free servers (like `unaux.com` / iFastNet) with Cloudflare, ensure your **SSL encryption mode** in Cloudflare is changed from **Flexible** to **Full** (or **Full Strict**). This prevents infinite `ERR_TOO_MANY_REDIRECTS` loops.

---

## 🔒 Security Practices Built-In
- **PDO Prepared Statements**: Safe from SQL Injection.
- **Password Hashing**: Utilizes native PHP `password_hash` using the secure `PASSWORD_DEFAULT` algorithm.
- **Role Guards**: Prevents non-admin accounts from loading administrative templates.
- **Activation Block**: Unactivated accounts will display a "Coming Soon/Being Prepared" virtual seal card to guests. No dynamic wedding data is shared with the public until the administrator approves the billing slip.
- **Isolated Sessions**: Writes secure session cookies locally within a private `/sessions/` directory, resolving common session-loss issues found on free hosting platforms.

---

## 👨‍💻 Author
- **Lumus Studio** — Designed & Developed by Hathisa Thissara (Sri Lanka)
```

මෙම ලේඛනය (Document) මඟින් ඔයාගේ පද්ධතියේ ඇති සියලුම තාක්ෂණික සහ වෘත්තීය ගුණාංග ඉතාම පැහැදිලිව සටහන් කරලා තියෙනවා. වෙනත් කිසිම උදව්වක් අවශ්‍ය නම් කියන්න!
