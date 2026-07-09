<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy - Lumos Studio</title>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,400;1,600&family=Inter:wght@300;400;500;600&family=Great+Vibes&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --gold: #c9a96e;
            --gold-light: #e8d5a3;
            --gold-dark: #a07840;
            --dark: #0f0f1a;
            --dark-2: #1a1a2e;
            --dark-3: #242440;
            --text-light: #e8e4dc;
            --text-muted: #9e9aaa;
            --pink: #d63384;
            --white: #ffffff;
        }
        body {
            font-family: 'Inter', sans-serif;
            background: var(--dark);
            color: var(--text-light);
            overflow-x: hidden;
            line-height: 1.6;
        }
        nav {
            position: fixed;
            top: 0; left: 0; right: 0;
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 5%;
            background: rgba(15,15,26,0.95);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(201,169,110,0.15);
        }
        .nav-logo {
            font-family: 'Great Vibes', cursive;
            font-size: 1.9rem;
            color: var(--gold);
            text-decoration: none;
        }
        .nav-links { display: flex; align-items: center; gap: 32px; }
        .nav-links a {
            color: var(--text-muted);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: color 0.2s;
        }
        .nav-links a:hover { color: var(--gold); }
        .btn-nav {
            background: linear-gradient(135deg, var(--gold), var(--gold-dark));
            color: var(--dark) !important;
            padding: 9px 22px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.85rem;
            transition: transform 0.2s, box-shadow 0.2s !important;
        }
        .btn-nav:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(201,169,110,0.35);
        }
        
        .page-header {
            padding: 140px 5% 60px;
            text-align: center;
            background: radial-gradient(ellipse 80% 60% at 50% 40%, rgba(201,169,110,0.12) 0%, transparent 70%);
        }
        .page-title {
            font-family: 'Cormorant Garamond', serif;
            font-size: 3rem;
            color: var(--white);
            margin-bottom: 16px;
        }
        .content-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 0 20px 80px;
        }
        .content-container h2 {
            font-family: 'Cormorant Garamond', serif;
            color: var(--gold);
            font-size: 1.8rem;
            margin-top: 40px;
            margin-bottom: 16px;
        }
        .content-container p { margin-bottom: 16px; color: var(--text-muted); }
        .content-container ul { margin-bottom: 16px; margin-left: 20px; color: var(--text-muted); }
        .content-container li { margin-bottom: 8px; }
        
        footer {
            border-top: 1px solid rgba(255,255,255,0.05);
            padding: 40px 5%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 16px;
        }
        footer .footer-logo {
            font-family: 'Great Vibes', cursive;
            font-size: 1.6rem;
            color: var(--gold);
        }
        footer p { color: var(--text-muted); font-size: 0.82rem; }
        
        /* Mobile nav */
        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            color: var(--text-light);
            font-size: 1.5rem;
            cursor: pointer;
        }

        @media (max-width: 768px) {
            .mobile-menu-btn { display: block; }
            .nav-links { 
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background: rgba(15,15,26,0.98);
                backdrop-filter: blur(20px);
                flex-direction: column;
                padding: 20px;
                border-bottom: 1px solid rgba(201,169,110,0.15);
                display: none;
                gap: 20px;
            }
            .nav-links.active { display: flex; }
            .steps-grid::before { display: none; }
        }
    </style>
</head>
<body>
    <nav id="navbar">
    <a href="index.php" class="nav-logo">Lumus Studio</a>
    <button class="mobile-menu-btn" onclick="document.querySelector('.nav-links').classList.toggle('active')">
        <i class="fas fa-bars"></i>
    </button>
    <div class="nav-links">
        <a href="features.php">Features</a>
        <a href="themes.php">Themes</a>
        <a href="index.php#how-it-works">How It Works</a>
        <a href="pricing.php">Pricing</a>
        <a href="dashboard/login.php">Sign In</a>
        <a href="dashboard/register.php" class="btn-nav">Get Started Free</a>
    </div>
</nav>

    <section class="page-header">
        <h1 class="page-title">Privacy Policy</h1>
        <p style="color: var(--text-muted);">Last updated: April 2026</p>
    </section>

    <section class="content-container">
        <p>Welcome to Lumus Studio ("we", "us", or "our"). We are a digital wedding invitation platform based in Colombo, Sri Lanka. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you visit our website and use our services. Please read this policy carefully. By using Lumus Studio, you consent to the practices described in this policy.</p>

        <h2>1. Information We Collect</h2>
        <p>We collect information that you provide directly to us when you create an account, build an invitation, or interact with our services:</p>
        <ul>
            <li><strong>Account Information:</strong> Your name, email address, phone number, and password when you register.</li>
            <li><strong>Wedding Details:</strong> Bride and groom names, wedding date, venue details, ceremony information, and any other content you include in your invitation.</li>
            <li><strong>Guest Data:</strong> Names, email addresses, phone numbers, and RSVP responses of guests you add to your guest list.</li>
            <li><strong>Payment Information:</strong> Billing details processed through our payment provider. We do not store credit card numbers on our servers.</li>
            <li><strong>Usage Data:</strong> Information about how you interact with our platform, including page views, features used, and device information.</li>
        </ul>

        <h2>2. How We Use Your Information</h2>
        <p>We use the information we collect for the following purposes:</p>
        <ul>
            <li>To provide, maintain, and improve our digital invitation services.</li>
            <li>To create and manage your account and process your invitations.</li>
            <li>To send RSVP notifications, reminders, and service-related communications.</li>
            <li>To process payments and send transaction confirmations.</li>
            <li>To respond to your inquiries, support requests, and feedback.</li>
            <li>To monitor and analyze usage trends to improve user experience.</li>
            <li>To detect, prevent, and address fraud or technical issues.</li>
        </ul>

        <h2>3. Data Storage and Security</h2>
        <p>Your data is stored securely in a database hosted on reliable cloud infrastructure. We implement industry-standard security measures to protect your information, including:</p>
        <ul>
            <li>Encrypted data transmission using SSL/TLS protocols.</li>
            <li>Encrypted storage for sensitive data at rest.</li>
            <li>Regular security audits and updates to our infrastructure.</li>
            <li>Access controls limiting data access to authorized personnel only.</li>
        </ul>
        <p>While we strive to use commercially acceptable means to protect your personal information, no method of transmission over the Internet or electronic storage is 100% secure. We cannot guarantee absolute security.</p>

        <h2>4. Third-Party Services</h2>
        <p>We use trusted third-party services to operate our platform:</p>
        <ul>
            <li><strong>Resend:</strong> For delivering transactional emails such as RSVP notifications and account confirmations.</li>
            <li><strong>Cloud Hosting:</strong> For hosting and database infrastructure.</li>
        </ul>
        <p>These third parties have access to your information only to perform specific tasks on our behalf and are obligated to protect it.</p>

        <h2>5. Cookies and Tracking</h2>
        <p>We use cookies and similar technologies to enhance your experience:</p>
        <ul>
            <li><strong>Essential Cookies:</strong> Required for authentication and session management.</li>
            <li><strong>Analytics:</strong> We collect anonymous page view data to understand how visitors use our platform and to improve our services.</li>
        </ul>
        <p>We do not use third-party advertising trackers or sell your browsing data to advertisers.</p>

        <h2>6. Your Rights</h2>
        <p>You have the following rights regarding your personal data:</p>
        <ul>
            <li><strong>Access:</strong> Request a copy of the personal data we hold about you.</li>
            <li><strong>Correction:</strong> Request correction of any inaccurate or incomplete data.</li>
            <li><strong>Deletion:</strong> Request deletion of your account and all associated data.</li>
            <li><strong>Export:</strong> Request an export of your data in a portable format.</li>
            <li><strong>Withdraw Consent:</strong> Withdraw your consent for data processing at any time by contacting us.</li>
        </ul>
        <p>To exercise any of these rights, please contact us at hello@lumusstudio.lk. We will respond to your request within 30 days.</p>

        <h2>7. Data Retention</h2>
        <p>We retain your personal data and invitation content for the duration of your active account. After your wedding date, we retain your data for an additional 6 months to allow you to access your guest list, RSVP data, and invitation analytics. After this retention period, your data will be permanently deleted unless you request an extension or export your data beforehand. You may request early deletion of your data at any time.</p>

        <h2>8. Children's Privacy</h2>
        <p>Lumus Studio is intended for users who are at least 18 years of age. We do not knowingly collect personal information from children under 18. If we become aware that we have collected data from a child under 18, we will take steps to delete that information promptly. If you believe a child has provided us with personal data, please contact us at hello@lumusstudio.lk.</p>

        <h2>9. Changes to This Policy</h2>
        <p>We may update this Privacy Policy from time to time. We will notify you of any material changes by posting the new policy on this page and updating the "Last updated" date. We encourage you to review this Privacy Policy periodically for any changes. Continued use of our services after changes are posted constitutes acceptance of the revised policy.</p>

        <h2>10. Contact Us</h2>
        <p>If you have any questions or concerns about this Privacy Policy, please contact us:</p>
        <ul>
            <li><strong>Email:</strong> hatheesha6504@gmail.com</li>
            <li><strong>Location:</strong> Bandarawela, Sri Lanka</li>
        </ul>
    </section>

    <!-- FOOTER -->
<footer>
    <div class="footer-logo">Lumus Studio</div>
    <p>&copy; <?php echo date('Y'); ?> Lumus Studio &middot; Designed by Hathisa Thissara &middot; Sri Lanka</p>
    <div style="display:flex; gap:16px; margin-top:12px; flex-wrap:wrap; justify-content:center;">
        <a href="privacy.php" style="color: var(--text-muted); text-decoration:none; font-size:0.85rem;">Privacy Policy</a>
        <a href="terms.php" style="color: var(--text-muted); text-decoration:none; font-size:0.85rem;">Terms of Service</a>
        <a href="refund.php" style="color: var(--text-muted); text-decoration:none; font-size:0.85rem;">Refund Policy</a>
    </div>
    <div style="display:flex; gap:16px; margin-top:8px;">
        <a href="dashboard/register.php" style="color: var(--gold); text-decoration:none; font-size:0.85rem;">Get Started</a>
        <a href="dashboard/login.php" style="color: var(--text-muted); text-decoration:none; font-size:0.85rem;">Sign In</a>
    </div>
</footer>
</body>
</html>