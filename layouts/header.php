<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// පරිශීලකයා දැනටමත් ලොග් වී ඇත්දැයි බැලීම (Sign In වෙනුවට Dashboard පෙන්වීමට)
$is_logged_in = isset($_SESSION['user_id']);
$role = $_SESSION['role'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' | Lumus Studio' : 'Lumos Studio | Elegant Digital Wedding Invitations'; ?></title>
    <link rel="icon" type="image/x-icon" href="./uploads/lumos.jpg">
    <meta name="description" content="Lumus Studio presents Lumos Wedding Invitation - create beautiful digital wedding invitations with RSVP tracking, WhatsApp delivery, live countdown, gallery, and 9 custom themes for your special day.">
    <meta name="keywords" content="Lumos Studio, Lumos Studio invitation, Lumos Wedding Invitation, digital wedding invitation, wedding invitation website, wedding RSVP, online wedding invitation, elegant wedding invite">
    <link rel="canonical" href="https://lumosinvitation.unaux.com/">
    
    <!-- Open Graph Metadata -->
    <meta property="og:title" content="Lumos Wedding Invitation | Elegant Digital Wedding Invitations">
    <meta property="og:description" content="Create beautiful digital wedding invitations with Lumos Wedding Invitation. Add RSVP tracking, WhatsApp delivery, live countdown, gallery, and custom themes.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://lumosinvitation.unaux.com/">
    <meta property="og:image" content="https://lumosinvitation.unaux.com/lumos.jpg">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Lumos Wedding Invitation | Elegant Digital Wedding Invitations">
    <meta name="twitter:description" content="Create beautiful digital wedding invitations with Lumos Wedding Invitation. Add RSVP tracking, WhatsApp delivery, live countdown, gallery, and custom themes.">
    <meta name="twitter:image" content="https://lumosinvitation.unaux.com/lumos.jpg">
    <meta name="google-site-verification" content="KrJVNOQBGtAEWfU1vPROURf0R31dI2ExYzITXmZN8X0" />
    
    <!-- Google SEO Schema JSON-LD -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "Organization",
      "name": "Lumus Studio",
      "alternateName": "Lumos Wedding Invitation",
      "url": "https://lumosinvitation.unaux.com/",
      "logo": "https://lumosinvitation.unaux.com/lumos.jpg",
      "description": "Lumos Studio creates elegant digital wedding invitations with RSVP tracking, WhatsApp delivery, live countdowns, photo galleries, and 9 custom themes."
    }
    </script>
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "WebSite",
      "name": "Lumos Studio - Lumos Wedding Invitation",
      "url": "https://lumosinvitation.unaux.com/"
    }
    </script>
    
    <!-- Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,400;1,600&family=Inter:wght@300;400;500;600&family=Great+Vibes&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    
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

        html { scroll-behavior: smooth; }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--dark);
            color: var(--text-light);
            overflow-x: hidden;
        }

        /* =========== NAVBAR (Bootstrap navbar, themed) =========== */
        .navbar {
            position: fixed;
            top: 0; left: 0; right: 0;
            z-index: 1030;
            padding: 18px 0;
            transition: background 0.3s ease, padding 0.3s ease, border-color 0.3s ease;
        }
        .navbar.scrolled {
            background: rgba(15,15,26,0.97);
            border-bottom: 1px solid rgba(201,169,110,0.15);
            padding: 12px 0;
        }
        .navbar-brand.nav-logo {
            font-family: 'Great Vibes', cursive;
            font-size: 1.9rem;
            color: var(--gold) !important;
        }
        .navbar-nav .nav-link {
            color: var(--text-muted);
            font-size: 0.9rem;
            font-weight: 500;
            letter-spacing: 0.5px;
            transition: color 0.2s;
        }
        .navbar-nav .nav-link:hover,
        .navbar-nav .nav-link:focus { color: var(--gold); }
        .btn-nav {
            background: linear-gradient(135deg, var(--gold), var(--gold-dark));
            color: var(--dark) !important;
            padding: 9px 22px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.85rem;
            transition: transform 0.2s, box-shadow 0.2s;
            text-decoration: none;
            display: inline-block;
        }
        .btn-nav:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(201,169,110,0.35);
            color: var(--dark) !important;
        }

        /* Custom animated burger -> X, driven purely by Bootstrap's aria-expanded */
        .navbar-toggler {
            border: none;
            background: transparent !important;
            padding: 4px;
            box-shadow: none !important;
        }
        .toggler-icon-wrap { width: 24px; height: 18px; position: relative; display: inline-block; }
        .toggler-icon-wrap span {
            position: absolute; left: 0; width: 100%; height: 2px;
            background: var(--text-light); border-radius: 2px;
            transition: transform 0.3s ease, opacity 0.3s ease, top 0.3s ease;
        }
        .toggler-icon-wrap span:nth-child(1) { top: 0; }
        .toggler-icon-wrap span:nth-child(2) { top: 8px; }
        .toggler-icon-wrap span:nth-child(3) { top: 16px; }
        .navbar-toggler[aria-expanded="true"] .toggler-icon-wrap span:nth-child(1) { top: 8px; transform: rotate(45deg); }
        .navbar-toggler[aria-expanded="true"] .toggler-icon-wrap span:nth-child(2) { opacity: 0; }
        .navbar-toggler[aria-expanded="true"] .toggler-icon-wrap span:nth-child(3) { top: 8px; transform: rotate(-45deg); }

        @media (max-width: 991.98px) {
            .navbar-collapse {
                background: rgba(15,15,26,0.98);
                margin-top: 14px;
                border-radius: 16px;
                padding: 16px;
                border: 1px solid rgba(201,169,110,0.15);
                max-height: calc(100vh - 100px);
                overflow-y: auto;
            }
            .navbar-nav { gap: 4px; }
            .navbar-nav .nav-link { padding: 12px 16px; border-radius: 8px; text-align: center; }
            .navbar-nav .nav-link:hover { background: rgba(201,169,110,0.08); }
            .navbar-nav .btn-nav { width: 100%; text-align: center; margin-top: 8px; }
        }

        /* =========== SHARED SITE-WIDE STYLES (used across all pages) =========== */
        section { padding: 100px 5%; }
        .section-tag {
            display: inline-block;
            font-size: 0.72rem;
            font-weight: 600;
            letter-spacing: 2.5px;
            text-transform: uppercase;
            color: var(--gold);
            margin-bottom: 14px;
        }
        .section-title {
            font-family: 'Cormorant Garamond', serif;
            font-size: clamp(2rem, 5vw, 3.2rem);
            font-weight: 400;
            line-height: 1.2;
            color: var(--white);
            margin-bottom: 16px;
        }
        .section-title em { font-style: italic; color: var(--gold); }
        .section-subtitle {
            color: var(--text-muted);
            font-size: 1rem;
            line-height: 1.7;
            max-width: 560px;
        }
        .divider {
            width: 60px;
            height: 1px;
            background: linear-gradient(to right, transparent, var(--gold), transparent);
            margin: 20px auto;
        }
        .btn-primary-gold {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(135deg, var(--gold), var(--gold-dark));
            color: var(--dark);
            padding: 14px 32px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 20px rgba(201,169,110,0.3);
        }
        .btn-primary-gold:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 35px rgba(201,169,110,0.45);
            color: var(--dark);
        }
        .btn-ghost {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: 1px solid rgba(201,169,110,0.35);
            color: var(--gold);
            padding: 14px 32px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }
        .btn-ghost:hover {
            background: rgba(201,169,110,0.08);
            border-color: var(--gold);
            color: var(--gold);
            transform: translateY(-3px);
        }

        /* CTA section */
        .cta-section {
            text-align: center;
            background: radial-gradient(ellipse 80% 60% at 50% 50%, rgba(201,169,110,0.1) 0%, transparent 70%);
            border-top: 1px solid rgba(201,169,110,0.1);
        }
        .cta-section h2 {
            font-family: 'Cormorant Garamond', serif;
            font-size: clamp(2.5rem, 6vw, 4rem);
            font-weight: 300;
            color: var(--white);
            margin-bottom: 16px;
        }
        .cta-section h2 em { font-style: italic; color: var(--gold); }
        .cta-section p {
            color: var(--text-muted);
            font-size: 1rem;
            margin-bottom: 40px;
        }
        .cta-features {
            display: flex;
            gap: 24px;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 40px;
        }
        .cta-feature {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--text-muted);
            font-size: 0.85rem;
        }
        .cta-feature i { color: var(--gold); }

        /* Scroll reveal animation (used site-wide via class="... reveal") */
        .reveal { opacity: 0; transform: translateY(24px); transition: opacity 0.6s ease, transform 0.6s ease; }
        .reveal.visible { opacity: 1; transform: translateY(0); }

        /* =========== FOOTER =========== */
        footer {
            text-align: center;
            padding: 56px 5% 40px;
            border-top: 1px solid rgba(201,169,110,0.12);
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .footer-logo {
            font-family: 'Great Vibes', cursive;
            font-size: 1.8rem;
            color: var(--gold);
            margin-bottom: 14px;
        }
        footer p { color: var(--text-muted); font-size: 0.85rem; line-height: 1.6; }

        @media (max-width: 1024px) {
            section { padding: 80px 4%; }
        }

        @media (max-width: 768px) {
            section { padding: 60px 3%; }
            .section-title { font-size: clamp(1.5rem, 4vw, 2.5rem); margin-bottom: 12px; }
            .section-subtitle { max-width: 100%; }
            .cta-section { padding-top: 60px; padding-bottom: 60px; }
            .cta-section h2 { font-size: clamp(1.8rem, 5vw, 2.8rem); margin-bottom: 12px; }
            .cta-features { gap: 16px; flex-direction: column; align-items: center; }
            .cta-feature { justify-content: center; }
            footer { padding: 44px 5% 32px; }
        }

        @media (max-width: 480px) {
            section { padding: 50px 3%; }
            .section-tag { font-size: 0.65rem; margin-bottom: 10px; }
            .section-title { font-size: clamp(1.3rem, 4vw, 2.2rem); margin-bottom: 10px; line-height: 1.1; }
            .section-subtitle { font-size: 0.9rem; }
            .cta-section { padding: 50px 3%; }
            .cta-section h2 { font-size: clamp(1.5rem, 4.5vw, 2.5rem); margin-bottom: 10px; }
            .cta-section p { font-size: 0.9rem; margin-bottom: 30px; }
            .cta-features { gap: 12px; }
            .cta-feature { font-size: 0.8rem; }
        }
    </style>
</head>
<body>

<!-- NAVBAR (Bootstrap navbar component, dynamic login state integrated) -->
<nav class="navbar navbar-expand-lg fixed-top" id="navbar">
    <div class="container-fluid px-4 px-lg-5">
        <a href="index.php" class="navbar-brand nav-logo">Lumus Studio</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain" aria-controls="navMain" aria-expanded="false" aria-label="Toggle navigation">
            <span class="toggler-icon-wrap"><span></span><span></span><span></span></span>
        </button>
        <div class="collapse navbar-collapse" id="navMain">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-4 mt-3 mt-lg-0">
                <li class="nav-item"><a class="nav-link" href="features.php">Features</a></li>
                <li class="nav-item"><a class="nav-link" href="themes.php">Themes</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php#how-it-works">How It Works</a></li>
                <li class="nav-item"><a class="nav-link" href="pricing.php">Pricing</a></li>
                <?php if ($is_logged_in): ?>
                    <!-- ලොග් වී ඇත්නම් කෙලින්ම Dashboard මෙනු පෙන්වයි -->
                    <?php if ($role === 'admin'): ?>
                    <li class="nav-item"><a class="nav-link" href="dashboard/admin/index.php">Dashboard</a></li>
                    <li class="nav-item"><a class="btn-nav" href="dashboard/admin/index.php">My Account</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="dashboard/user/index.php">Dashboard</a></li>
                    <li class="nav-item"><a class="btn-nav" href="dashboard/user/index.php">My Account</a></li>
                <?php endif; ?>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="dashboard/login.php">Sign In</a></li>
                    <li class="nav-item"><a class="btn-nav" href="dashboard/register.php">Get Started Free</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>