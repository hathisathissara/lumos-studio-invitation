<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Features | Lumus Studio - Everything Your Wedding Invitation Can Do</title>
    <link rel="icon" type="image/x-icon" href="uploads/lumos.jpg">
    <meta name="description" content="Explore every feature of Lumus Studio's digital wedding invitations - personalized guest names, WhatsApp sharing, live RSVP dashboard, countdown timer, photo gallery, and more.">
    <meta name="keywords" content="Lumus Studio features, wedding invitation features, RSVP tracking, WhatsApp wedding invite, digital invitation features Sri Lanka">
    <link rel="canonical" href="https://lumosinvitation.unaux.com/features.php">
    <meta property="og:title" content="Features | Lumus Studio - Digital Wedding Invitations">
    <meta property="og:description" content="Everything your wedding invitation can do - personalized names, WhatsApp sharing, live RSVP, countdown, gallery and more.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://lumosinvitation.unaux.com/features.php">
    <meta property="og:image" content="https://lumosinvitation.unaux.com/lumos.jpg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
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

        html { scroll-behavior: smooth; }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--dark);
            color: var(--text-light);
            overflow-x: hidden;
        }

        /* =========== NAVBAR =========== */
        nav {
            position: fixed;
            top: 0; left: 0; right: 0;
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 18px 5%;
            transition: all 0.3s ease;
        }
        nav.scrolled {
            background: rgba(15,15,26,0.95);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(201,169,110,0.15);
            padding: 12px 5%;
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
            letter-spacing: 0.5px;
            transition: color 0.2s;
        }
        .nav-links a:hover { color: var(--gold); }
        .btn-nav {
            background: linear-gradient(135deg, var(--gold), var(--gold-dark));
            color: var(--dark) !important;
            padding: 9px 22px;
            border-radius: 50px;
            font-weight: 600 !important;
            font-size: 0.85rem !important;
            transition: transform 0.2s, box-shadow 0.2s !important;
        }
        .btn-nav:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(201,169,110,0.35);
            color: var(--dark) !important;
        }

        /* =========== HERO =========== */
        .hero {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            position: relative;
            padding: 120px 20px 80px;
            overflow: hidden;
        }
        .hero-bg {
            position: absolute;
            inset: 0;
            background: radial-gradient(ellipse 80% 60% at 50% 40%, rgba(201,169,110,0.12) 0%, transparent 70%),
                        radial-gradient(ellipse 50% 40% at 80% 80%, rgba(214,51,132,0.08) 0%, transparent 60%);
        }
        .hero-ornament {
            position: absolute;
            font-size: 28rem;
            color: rgba(201,169,110,0.03);
            font-family: 'Great Vibes', cursive;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            pointer-events: none;
            user-select: none;
            white-space: nowrap;
        }
        .hero-content { position: relative; z-index: 1; max-width: 800px; }
        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(201,169,110,0.12);
            border: 1px solid rgba(201,169,110,0.25);
            color: var(--gold);
            padding: 6px 16px;
            border-radius: 50px;
            font-size: 0.78rem;
            font-weight: 500;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            margin-bottom: 28px;
        }
        .hero-title {
            font-family: 'Cormorant Garamond', serif;
            font-size: clamp(3rem, 5.5vw, 4.6rem);
            font-weight: 300;
            line-height: 1.1;
            margin-bottom: 24px;
            color: var(--white);
        }
        .hero-title .accent {
            font-style: italic;
            color: var(--gold);
        }
        .hero-subtitle {
            font-size: 1.1rem;
            color: var(--text-muted);
            line-height: 1.7;
            max-width: 560px;
            margin: 0 auto 40px;
        }
        .hero-cta {
            display: flex;
            gap: 16px;
            justify-content: center;
            flex-wrap: wrap;
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
        .hero-proof {
            margin-top: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 24px;
            flex-wrap: wrap;
        }
        .proof-item {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--text-muted);
            font-size: 0.85rem;
        }
        .proof-item i { color: var(--gold); font-size: 0.9rem; }

        /* =========== SECTION COMMON =========== */
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

        /* =========== FEATURES — INVITATION =========== */
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 24px;
            margin-top: 60px;
        }
        .feature-card {
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(201,169,110,0.12);
            border-radius: 20px;
            padding: 32px 28px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        .feature-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 1px;
            background: linear-gradient(to right, transparent, rgba(201,169,110,0.4), transparent);
            transform: scaleX(0);
            transition: transform 0.4s ease;
        }
        .feature-card:hover::before { transform: scaleX(1); }
        .feature-card:hover {
            background: rgba(201,169,110,0.06);
            border-color: rgba(201,169,110,0.25);
            transform: translateY(-4px);
        }
        .feature-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: rgba(201,169,110,0.12);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            font-size: 1.2rem;
            color: var(--gold);
        }
        .feature-card h3 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--white);
            margin-bottom: 10px;
        }
        .feature-card p {
            color: var(--text-muted);
            font-size: 0.88rem;
            line-height: 1.65;
        }

        /* =========== HOW IT WORKS =========== */
        .how-it-works { background: rgba(255,255,255,0.015); }
        .steps-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 0;
            margin-top: 60px;
            position: relative;
        }
        .steps-grid::before {
            content: '';
            position: absolute;
            top: 32px;
            left: 10%;
            right: 10%;
            height: 1px;
            background: linear-gradient(to right, transparent, rgba(201,169,110,0.3), transparent);
        }
        .step {
            text-align: center;
            padding: 20px;
            position: relative;
        }
        .step-number {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            border: 1px solid rgba(201,169,110,0.35);
            background: var(--dark-2);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.6rem;
            color: var(--gold);
            position: relative;
            z-index: 1;
        }
        .step h4 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--white);
            margin-bottom: 8px;
        }
        .step p { color: var(--text-muted); font-size: 0.85rem; line-height: 1.6; }

        /* =========== PEACE OF MIND =========== */
        .peace-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            margin-top: 60px;
        }
        @media (max-width: 768px) { .peace-grid { grid-template-columns: 1fr; } }
        .peace-card {
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(201,169,110,0.1);
            border-radius: 20px;
            padding: 36px 32px;
            display: flex;
            gap: 20px;
            align-items: flex-start;
            transition: all 0.3s;
        }
        .peace-card:hover {
            border-color: rgba(201,169,110,0.25);
            background: rgba(201,169,110,0.04);
        }
        .peace-card-icon {
            flex-shrink: 0;
            width: 52px;
            height: 52px;
            border-radius: 14px;
            background: rgba(201,169,110,0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--gold);
            font-size: 1.3rem;
        }
        .peace-card h4 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--white);
            margin-bottom: 8px;
        }
        .peace-card p { color: var(--text-muted); font-size: 0.88rem; line-height: 1.65; }

        /* =========== DEMO PREVIEW =========== */
        .demo-section {
            text-align: center;
            position: relative;
        }
        .phone-mockup {
            display: inline-block;
            margin-top: 60px;
            position: relative;
        }
        .phone-frame {
            width: 260px;
            background: var(--dark-2);
            border-radius: 40px;
            border: 2px solid rgba(201,169,110,0.2);
            padding: 20px 16px;
            box-shadow: 0 40px 80px rgba(0,0,0,0.6), 0 0 0 1px rgba(201,169,110,0.05);
            position: relative;
        }
        .phone-notch {
            width: 80px;
            height: 6px;
            background: rgba(255,255,255,0.1);
            border-radius: 10px;
            margin: 0 auto 16px;
        }
        .phone-screen {
            background: #f9f6f0;
            border-radius: 24px;
            padding: 20px 16px;
            text-align: center;
            min-height: 420px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .mock-names {
            font-family: 'Great Vibes', cursive;
            font-size: 1.8rem;
            color: #b78a44;
            line-height: 1.3;
            margin: 10px 0 6px;
        }
        .mock-date { font-size: 0.65rem; color: #888; letter-spacing: 1.5px; text-transform: uppercase; margin-bottom: 12px; font-family: sans-serif; }
        .mock-countdown {
            display: flex;
            gap: 6px;
            margin-bottom: 12px;
        }
        .mock-box {
            background: white;
            border: 1px solid #f0e6d2;
            border-radius: 8px;
            padding: 6px 8px;
            text-align: center;
            min-width: 40px;
        }
        .mock-box span { display: block; font-size: 1rem; font-weight: bold; color: #b78a44; font-family: sans-serif; }
        .mock-box small { font-size: 0.5rem; text-transform: uppercase; color: #aaa; font-family: sans-serif; }
        .mock-event {
            background: #fdfaf5;
            border: 1px solid #f0e6d2;
            border-radius: 10px;
            padding: 10px 12px;
            width: 100%;
            text-align: left;
            margin-bottom: 8px;
        }
        .mock-event-name { font-family: sans-serif; font-size: 0.7rem; font-weight: bold; color: #d63384; margin-bottom: 2px; }
        .mock-event-time { font-family: sans-serif; font-size: 0.6rem; color: #888; }
        .mock-rsvp {
            margin-top: 10px;
            background: #1a1a2e;
            color: white;
            border-radius: 20px;
            padding: 8px 24px;
            font-family: sans-serif;
            font-size: 0.7rem;
            font-weight: bold;
            letter-spacing: 1px;
        }
        .phone-glow {
            position: absolute;
            width: 300px;
            height: 300px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(201,169,110,0.15) 0%, transparent 70%);
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            pointer-events: none;
        }

        /* =========== THEME GALLERY =========== */
        .theme-gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 28px;
            margin-top: 60px;
        }
        .theme-card {
            border-radius: 20px;
            overflow: hidden;
            border: 1px solid rgba(201,169,110,0.15);
            background: rgba(255,255,255,0.02);
            transition: transform 0.35s ease, box-shadow 0.35s ease, border-color 0.35s ease;
        }
        .theme-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 24px 45px rgba(0,0,0,0.4);
            border-color: rgba(201,169,110,0.3);
        }
        .theme-phone-outer {
            padding: 30px 20px 22px;
            display: flex;
            justify-content: center;
        }
        .theme-phone-frame {
            width: 100%;
            max-width: 230px;
            background: var(--dark-2);
            border-radius: 34px;
            border: 2px solid rgba(201,169,110,0.2);
            padding: 16px 12px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.5), 0 0 0 1px rgba(201,169,110,0.05);
        }
        .theme-phone-notch {
            width: 60px;
            height: 5px;
            background: rgba(255,255,255,0.12);
            border-radius: 10px;
            margin: 0 auto 14px;
        }
        .theme-phone-screen {
            border-radius: 20px;
            padding: 20px 14px;
            text-align: center;
            min-height: 400px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .theme-label {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 14px 18px;
            background: rgba(255,255,255,0.03);
            border-top: 1px solid rgba(201,169,110,0.1);
        }
        .theme-swatch {
            width: 14px;
            height: 14px;
            border-radius: 50%;
            flex-shrink: 0;
            box-shadow: 0 0 0 3px rgba(255,255,255,0.05);
        }
        .theme-label .theme-name {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.05rem;
            font-weight: 600;
            color: var(--white);
        }

        /* =========== PRICING / CTA =========== */
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

        /* =========== FOOTER =========== */
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

        /* =========== ANIMATIONS =========== */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .fade-up {
            opacity: 0;
            animation: fadeUp 0.7s ease forwards;
        }
        .delay-1 { animation-delay: 0.1s; }
        .delay-2 { animation-delay: 0.2s; }
        .delay-3 { animation-delay: 0.35s; }
        .delay-4 { animation-delay: 0.5s; }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        .phone-mockup { animation: float 5s ease-in-out infinite; }

        /* Scroll reveal */
        .reveal { opacity: 0; transform: translateY(40px); transition: opacity 0.7s ease, transform 0.7s ease; }
        .reveal.visible { opacity: 1; transform: translateY(0); }

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

        /* Horizontal scroll for small screens features */
        @media (max-width: 480px) {
            section { padding: 70px 5%; }
        }
    </style>
</head>
<body>

<!-- NAVBAR -->
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
<!-- INVITATION FEATURES -->
<section id="features" style="padding-top:150px;">
    <div style="text-align:center; margin-bottom: 20px;">
        <span class="section-tag reveal">Inside the Invitation</span>
        <h2 class="section-title reveal">Everything your guests<br><em>will love</em></h2>
        <div class="divider"></div>
        <p class="section-subtitle reveal" style="margin: 0 auto;">Every detail, beautifully crafted — from personalized greetings to one-tap directions.</p>
    </div>

    <div class="features-grid">
        <div class="feature-card reveal">
            <div class="feature-icon"><i class="fas fa-user-tag"></i></div>
            <h3>Personalized Guest Names</h3>
            <p>Each guest opens an invitation with their own name on it — a personal touch that makes them feel truly invited.</p>
        </div>
        <div class="feature-card reveal">
            <div class="feature-icon"><i class="fab fa-whatsapp"></i></div>
            <h3>One Link, Easy Sharing</h3>
            <p>Share one invitation link via WhatsApp to all your guests. Each guest enters their number to open their personal invitation.</p>
        </div>
        <div class="feature-card reveal">
            <div class="feature-icon"><i class="fas fa-eye"></i></div>
            <h3>Live Open Tracking</h3>
            <p>See which guests have opened their invitation and exactly when — in real time from your dashboard.</p>
        </div>
        <div class="feature-card reveal">
            <div class="feature-icon"><i class="fas fa-reply"></i></div>
            <h3>Live RSVP Dashboard</h3>
            <p>See who's attending, who can't come, and any dietary notes — all in one live, filterable dashboard.</p>
        </div>
        <div class="feature-card reveal">
            <div class="feature-icon"><i class="fas fa-calendar-alt"></i></div>
            <h3>All Events in One Place</h3>
            <p>Poruwa, Church, Reception, Homecoming — linked in one invitation with timelines and venue details.</p>
        </div>
        <div class="feature-card reveal">
            <div class="feature-icon"><i class="fas fa-clock"></i></div>
            <h3>Live Countdown Timer</h3>
            <p>A real-time countdown to your big day — visible on every guest's invitation, building the excitement.</p>
        </div>
        <div class="feature-card reveal">
            <div class="feature-icon"><i class="fas fa-map-marker-alt"></i></div>
            <h3>Google Maps Directions</h3>
            <p>One tap from the invitation opens turn-by-turn directions for each venue. No address confusion.</p>
        </div>
        <div class="feature-card reveal">
            <div class="feature-icon"><i class="fas fa-calendar-plus"></i></div>
            <h3>Add to Calendar</h3>
            <p>Guests save the date to Google, Apple, or Outlook with a single tap — no one forgets the day.</p>
        </div>
        <div class="feature-card reveal">
            <div class="feature-icon"><i class="fas fa-qrcode"></i></div>
            <h3>QR Code for Printed Cards</h3>
            <p>Print a scan-to-open code on physical cards so paper and digital work together seamlessly.</p>
        </div>
        <div class="feature-card reveal">
            <div class="feature-icon"><i class="fas fa-images"></i></div>
            <h3>Photo Gallery & Love Story</h3>
            <p>Share engagement photos and how you met — give guests a feel for your beautiful story before the day.</p>
        </div>
        <div class="feature-card reveal">
            <div class="feature-icon"><i class="fas fa-mobile-alt"></i></div>
            <h3>Beautiful on Any Phone</h3>
            <p>From your aunty's older phone to the latest iPhone — every guest opens it instantly, on slow or fast data.</p>
        </div>
        <div class="feature-card reveal">
            <div class="feature-icon"><i class="fas fa-users-cog"></i></div>
            <h3>Per-Event Guest Assignment</h3>
            <p>Invite guests to only the events that apply — Poruwa, Reception, or Homecoming separately.</p>
        </div>
    </div>
</section>
<!-- PLANNING TOOLS -->
<section>
    <div style="text-align:center; margin-bottom:20px;">
        <span class="section-tag reveal">Plan the Day</span>
        <h2 class="section-title reveal">Wedding planning tools<br><em>alongside your invitation</em></h2>
        <div class="divider"></div>
    </div>
    <div class="features-grid" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));">
        <div class="feature-card reveal">
            <div class="feature-icon"><i class="fas fa-tasks"></i></div>
            <h3>Task Checklist</h3>
            <p>Track what's done and what's coming — from saree fitting to thank-you notes.</p>
        </div>
        <div class="feature-card reveal">
            <div class="feature-icon"><i class="fas fa-tags"></i></div>
            <h3>Guest Categories</h3>
            <p>Tag guests by group (Family, Friends, Office, VIP) and side (Bride, Groom, Both) for easy filtering.</p>
        </div>
        <a href="#themes" class="feature-card reveal" style="text-decoration:none; display:block; color:inherit;">
            <div class="feature-icon"><i class="fas fa-palette"></i></div>
            <h3>Choose From 9 Themes</h3>
            <p>Pick from 9 beautiful invitation themes and preview each one live before you publish your final design. <span style="color:var(--gold); font-weight:600;">See all themes ↓</span></p>
        </a>
    </div>
</section>
<!-- CTA -->
<section class="cta-section" id="cta">
    <span class="section-tag reveal">Ready to Begin?</span>
    <h2 class="reveal">Start for <em>free</em> today</h2>
    <p class="reveal">Build your full invitation and preview it completely — no payment until you're ready.</p>
    <div class="reveal">
        <a href="dashboard/register.php" class="btn-primary-gold" style="font-size:1rem; padding:16px 40px;">
            <i class="fas fa-heart"></i> Create My Wedding Invitation
        </a>
    </div>
    <div class="cta-features reveal">
        <div class="cta-feature"><i class="fas fa-check"></i> Free to build & preview</div>
        <div class="cta-feature"><i class="fas fa-check"></i> Pay only when ready</div>
        <div class="cta-feature"><i class="fas fa-check"></i> Edit forever after</div>
        <div class="cta-feature"><i class="fas fa-check"></i> Beautiful on any phone</div>
    </div>
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

<script>
// Navbar scroll effect
const navbar = document.getElementById('navbar');
window.addEventListener('scroll', () => {
    navbar.classList.toggle('scrolled', window.scrollY > 50);
});

// Scroll reveal
const reveals = document.querySelectorAll('.reveal');
const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry, i) => {
        if (entry.isIntersecting) {
            setTimeout(() => entry.target.classList.add('visible'), i * 60);
        }
    });
}, { threshold: 0.1 });
reveals.forEach(el => observer.observe(el));

// Live countdown for all theme preview mockups
function updateThemeCountdowns() {
    const target = new Date("2026-02-14 00:00:00").getTime();
    const now = new Date().getTime();
    const dist = target - now;
    if (dist < 0) return;
    const d = String(Math.floor(dist / 86400000)).padStart(2, '0');
    const h = String(Math.floor((dist % 86400000) / 3600000)).padStart(2, '0');
    const m = String(Math.floor((dist % 3600000) / 60000)).padStart(2, '0');
    const s = String(Math.floor((dist % 60000) / 1000)).padStart(2, '0');
    document.querySelectorAll('.t-cd-days').forEach(el => el.textContent = d);
    document.querySelectorAll('.t-cd-hrs').forEach(el => el.textContent = h);
    document.querySelectorAll('.t-cd-min').forEach(el => el.textContent = m);
    document.querySelectorAll('.t-cd-sec').forEach(el => el.textContent = s);
}
updateThemeCountdowns();
setInterval(updateThemeCountdowns, 1000);

</script>
</body>
</html>
