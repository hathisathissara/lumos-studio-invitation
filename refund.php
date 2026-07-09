<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Return & Refund Policy - Lumos Studio</title>
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
        <h1 class="page-title">Return & Refund Policy</h1>
        <p style="color: var(--text-muted);">Last updated: April 2026</p>
    </section>

    <section class="content-container">
        <h2>1. Overview</h2>
        <p>Lumus Studio is a digital service. Every paid plan unlocks instant access to themes, editing tools, and your live invitation link. Because the product is delivered digitally and cannot be physically returned, refunds are handled on a case-by-case basis under the conditions below.</p>

        <h2>2. When you can request a refund</h2>
        <p>You are eligible for a full refund if all of the following are true:</p>
        <ul>
            <li>Your payment was completed within the last 7 days.</li>
            <li>Your invitation has not been published or shared with guests (the invitation link has not been distributed via WhatsApp, email, or any other channel).</li>
            <li>You have not sent RSVP links or bulk messages from your guest list.</li>
        </ul>
        <p>If your invitation has already been published or shared, we are unable to offer a refund - the service has already been delivered.</p>

        <h2>3. What is not refundable</h2>
        <ul>
            <li>Invitations that have been published or shared with guests.</li>
            <li>Payments older than 7 days from the date of purchase.</li>
            <li>Change of mind after the wedding date has passed.</li>
            <li>Charges made for add-ons that have already been consumed (e.g. bulk WhatsApp sends, SMS credits).</li>
        </ul>

        <h2>4. Failed or duplicate payments</h2>
        <p>If you were charged twice for the same plan, or a payment was debited but your plan was not upgraded, contact us within 30 days and we will investigate and refund the duplicate or failed charge in full. Please include your payment reference or bank receipt so we can locate the transaction quickly.</p>

        <h2>5. Bank transfers pending review</h2>
        <p>Bank transfers are manually verified before your plan is activated. If your transfer is still pending review and you no longer wish to proceed, email us and we will cancel the order before activation at no cost. Once a bank transfer has been approved and your plan activated, the standard refund conditions in Section 2 apply.</p>

        <h2>6. How to request a refund</h2>
        <p>Email hatheesha6504@gmail.com from the email address on your Lumus Studio account and include:</p>
        <ul>
            <li>Your full name and the plan you purchased.</li>
            <li>Date of payment and payment method (bank transfer).</li>
            <li>Payment reference and bank receipt</li>
            <li>A brief reason for the refund request.</li>
        </ul>
        <p>We will acknowledge your request within 2 business days and let you know the outcome.</p>

        <h2>7. Processing time</h2>
        <ul>
            <li><strong>Bank transfer refunds:</strong> 3–7 business days to the same account the payment was received from.</li>
        </ul>
        <p>Refunds are issued in Sri Lankan Rupees (LKR) for the exact amount originally paid. We do not refund currency conversion fees charged by your card issuer or bank.</p>

        <h2>8. Cancellations and account closure</h2>
        <p>All Lumus Studio plans are one-time payments - there are no recurring charges to cancel. If you would like your account and associated data deleted, you may request account closure at any time by contacting us. Account closure does not, by itself, entitle you to a refund outside the conditions in Section 2.</p>

        <h2>9. Related policies</h2>
        <p>This policy should be read alongside our Terms of Service and Privacy Policy. Where this policy conflicts with the Terms of Service, the terms of this Return & Refund Policy apply to refund matters only.</p>

        <h2>10. Contact us</h2>
        <p>Questions about this policy or an existing refund request?</p>
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