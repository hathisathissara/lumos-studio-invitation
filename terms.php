<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms of Service - Lumos Studio</title>
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
        <h1 class="page-title">Terms of Service</h1>
        <p style="color: var(--text-muted);">Last updated: July 2026</p>
    </section>

    <section class="content-container">
        <h2>1. Acceptance of Terms</h2>
        <p>By accessing or using Lumus Studio ("the Service"), you agree to be bound by these Terms of Service ("Terms"). If you do not agree to these Terms, you may not access or use the Service. These Terms constitute a legally binding agreement between you and Lumus Studio, operated from Colombo, Sri Lanka.</p>

        <h2>2. Description of Service</h2>
        <p>Lumus Studio is a digital wedding invitation platform that enables users to create, customize, and share beautiful wedding invitations online. Our services include:</p>
        <ul>
            <li>Creating and customizing digital wedding invitations from professionally designed themes.</li>
            <li>Managing guest lists with contact information and grouping.</li>
            <li>Collecting and tracking RSVP responses from guests.</li>
            <li>Sharing invitations via unique links and QR codes.</li>
            <li>Providing analytics on invitation views and guest engagement.</li>
        </ul>

        <h2>3. User Accounts</h2>
        <p>To use certain features of the Service, you must register for an account. When creating an account, you agree to:</p>
        <ul>
            <li>Provide accurate, current, and complete information during registration.</li>
            <li>Maintain the security of your password and account credentials.</li>
            <li>Accept responsibility for all activities that occur under your account.</li>
            <li>Maintain only one account per person. Duplicate accounts may be terminated.</li>
            <li>Notify us immediately of any unauthorized use of your account.</li>
        </ul>
        <p>We reserve the right to suspend or terminate accounts that violate these Terms or that have been inactive for an extended period.</p>

        <h2>4. Plans and Pricing</h2>
<p>Lumus Studio offers the following plans and add-ons with fixed, one-time flat fees:</p>
<ul>
    <li><strong>Free Preview (Trial Mode):</strong> Build, configure, and preview your complete invitation layout inside your dashboard at no cost before publication.</li>
    <li><strong>Basic Plan - Rs. 2,500:</strong> Standard digital card supporting up to 150 guest seat reservations, dynamic RSVP dashboard, countdown timer, checklist, photo gallery, and Google Maps integration.</li>
    <li><strong>Standard Plan - Rs. 5,000:</strong> Our most popular plan, supporting up to 300 guest seat reservations, 2 dynamic templates, free support to edit, and all core planning tools.</li>
    <li><strong>Premium Plan - Rs. 10,000:</strong> Offers fully customized design built from scratch by our team, unlimited guest seats, priority support, and the interactive Guest Shared Gallery included at no extra cost.</li>
    <li><strong>Guest Gallery Add-on - Rs. 2,000:</strong> An optional add-on available for Basic and Standard plans, allowing invitees to upload their own candid wedding photos directly onto your live invitation.</li>
</ul>
<p>All prices are listed in Sri Lankan Rupees (LKR) and are strict, one-time payments—there are no recurring subscriptions or surprise maintenance fees. Active accounts can be upgraded to a higher tier plan at any time directly from the dashboard by paying only the balance difference between the plans.</p>
<p>We reserve the right to modify plans and pricing at any time. Any future pricing adjustments will not affect already paid, active invitations.</p>

       <h2>5. Payment Terms</h2>
<p>We accept payments exclusively through the following method:</p>
<ul>
    <li><strong>Bank Transfer:</strong> Direct bank deposit, internet banking, or mobile bank app transfer to our designated bank account. Account activation and package upgrades are subject to manual verification of your uploaded bank transfer slip or screenshot receipt.</li>
</ul>
<p><strong>Refund Policy:</strong> Payments are strictly non-refundable once your invitation link has been activated, published, or shared with guests. If you have not yet published or distributed your invitation link, you may officially submit a refund request directly through your dashboard payment panel or contact us at hatheesha6504@gmail.com within 7 days of your payment date.</p>
        <h2>6. User Content</h2>
        <p>You retain full ownership of any content you upload or create on the Service, including text, images, and wedding details ("User Content"). By using the Service, you grant Lumus Studio a non-exclusive, worldwide, royalty-free license to use, display, and distribute your User Content solely for the purpose of providing and operating the Service.</p>
        <p>You represent and warrant that you own or have the necessary rights and permissions to use and authorize us to display your User Content, and that your content does not infringe on the intellectual property rights of any third party.</p>

        <h2>7. Acceptable Use</h2>
        <p>You agree not to use the Service to:</p>
        <ul>
            <li>Upload, share, or distribute any illegal, harmful, threatening, abusive, or otherwise objectionable content.</li>
            <li>Send unsolicited messages, spam, or bulk communications to individuals who have not consented.</li>
            <li>Impersonate any person or entity or misrepresent your affiliation.</li>
            <li>Attempt to gain unauthorized access to the Service or its related systems.</li>
            <li>Introduce malware, viruses, or any other harmful code.</li>
            <li>Use the Service for any purpose other than creating legitimate wedding or event invitations.</li>
            <li>Scrape, crawl, or collect data from the Service through automated means without our consent.</li>
        </ul>

        <h2>8. Service Availability</h2>
        <p>We strive to keep Lumus Studio available at all times. However, we do not guarantee 100% uptime. The Service may be temporarily unavailable due to maintenance, updates, server issues, or circumstances beyond our control. We will make reasonable efforts to notify users of planned downtime in advance. We are not liable for any loss or inconvenience caused by service interruptions.</p>

        <h2>9. Intellectual Property</h2>
        <p>The Lumus Studio platform, including its design, themes, source code, logos, and all related intellectual property, is owned by Lumus Studio and is protected by applicable copyright and trademark laws. You may not copy, modify, distribute, sell, or lease any part of the Service or its underlying technology without our prior written consent. Themes provided by the Service are licensed for use within the platform only and may not be extracted or repurposed.</p>

        <h2>10. Limitation of Liability</h2>
        <p>To the fullest extent permitted by law, Lumus Studio and its operators, employees, and affiliates shall not be liable for any indirect, incidental, special, consequential, or punitive damages arising out of or relating to your use of the Service.</p>
        <p>Our total liability for any claim arising from or relating to the Service shall not exceed the amount you paid to us in the 12 months preceding the claim. The Service is provided on an "as is" and "as available" basis without warranties of any kind, either express or implied.</p>

        <h2>11. Termination</h2>
        <p>We may suspend or terminate your access to the Service at any time, with or without notice, for conduct that we believe violates these Terms or is harmful to other users, the Service, or third parties.</p>
        <p>You may terminate your account at any time by contacting us. Upon termination, your right to use the Service will cease immediately. Any data associated with your account may be deleted after termination, subject to our data retention policy.</p>

        <h2>12. Governing Law</h2>
        <p>These Terms shall be governed by and construed in accordance with the laws of Sri Lanka. Any disputes arising under or in connection with these Terms shall be subject to the exclusive jurisdiction of the courts of Sri Lanka.</p>

        <h2>13. Contact Information</h2>
        <p>If you have any questions about these Terms of Service, please contact us:</p>
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