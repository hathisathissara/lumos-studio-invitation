<?php
$page_title = "Lumos Wedding Invitation | Elegant Digital Wedding Invitations";
// Header Layout එක Load කිරීම
require 'layouts/header.php'; 
?>

<!-- index.php පිටුවට පමණක් අදාල විශේෂිත CSS මෝස්තරයන් -->
<style>
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

    /* Floating Animations */
    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(30px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .fade-up { opacity: 0; animation: fadeUp 0.7s ease forwards; }
    .delay-1 { animation-delay: 0.1s; }
    .delay-2 { animation-delay: 0.2s; }
    .delay-3 { animation-delay: 0.35s; }
    .delay-4 { animation-delay: 0.5s; }

    @keyframes float {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-10px); }
    }
    .phone-mockup { animation: float 5s ease-in-out infinite; }

    @media (max-width: 1024px) {
        section { padding: 80px 4%; }
        .hero { padding: 100px 20px 60px; }
        .features-grid { grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); }
        .theme-gallery-grid { grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); }
    }

    @media (max-width: 768px) {
        section { padding: 60px 3%; }
        .hero { min-height: auto; padding: 80px 20px 60px; }
        .hero-ornament { font-size: 12rem; }
        .hero-title { font-size: clamp(2rem, 5vw, 3rem); }
        .hero-subtitle { font-size: 0.95rem; max-width: 100%; }
        .hero-cta { flex-direction: column; gap: 12px; }
        .btn-primary-gold, .btn-ghost { width: 100%; justify-content: center; padding: 12px 20px; }
        .hero-proof { flex-direction: column; gap: 12px; margin-top: 40px; }
        .proof-item { flex-direction: column; align-items: center; text-align: center; }
        
        .features-grid { grid-template-columns: 1fr; gap: 16px; margin-top: 40px; }
        .feature-card { padding: 24px 20px; }
        
        .section-title { font-size: clamp(1.5rem, 4vw, 2.5rem); margin-bottom: 12px; }
        .section-subtitle { max-width: 100%; }
        
        .steps-grid { grid-template-columns: 1fr; gap: 20px; margin-top: 40px; }
        .steps-grid::before { display: none; }
        .step { padding: 15px 10px; }
        .step-number { width: 56px; height: 56px; font-size: 1.4rem; }
        
        .peace-grid { grid-template-columns: 1fr; gap: 16px; }
        .peace-card { padding: 24px 20px; gap: 16px; }
        
        .phone-frame { width: 220px; }
        .phone-mockup { margin-top: 40px; }
        
        .theme-gallery-grid { grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 16px; }
        .theme-phone-frame { max-width: 180px; }
        
        .cta-section { padding-top: 60px; padding-bottom: 60px; }
        .cta-section h2 { font-size: clamp(1.8rem, 5vw, 2.8rem); margin-bottom: 12px; }
        .cta-features { gap: 16px; flex-direction: column; align-items: center; }
        .cta-feature { justify-content: center; }
    }

    @media (max-width: 480px) {
        section { padding: 50px 3%; }
        .hero { padding: 70px 15px 50px; }
        .hero-badge { padding: 5px 12px; font-size: 0.7rem; margin-bottom: 16px; }
        .hero-title { font-size: clamp(1.5rem, 4.5vw, 2.5rem); margin-bottom: 16px; line-height: 1.2; }
        .hero-subtitle { font-size: 0.9rem; margin: 0 auto 30px; line-height: 1.6; }
        .btn-primary-gold, .btn-ghost { padding: 11px 18px; font-size: 0.85rem; gap: 6px; }
        .btn-primary-gold i, .btn-ghost i { font-size: 0.85rem; }
        .hero-proof { margin-top: 30px; gap: 10px; }
        .proof-item { font-size: 0.75rem; gap: 6px; }
        
        .feature-card { padding: 20px 16px; border-radius: 16px; }
        .feature-icon { width: 40px; height: 40px; margin-bottom: 16px; font-size: 1rem; }
        .feature-card h3 { font-size: 1.15rem; margin-bottom: 8px; }
        .feature-card p { font-size: 0.8rem; }
        
        .section-tag { font-size: 0.65rem; margin-bottom: 10px; }
        .section-title { font-size: clamp(1.3rem, 4vw, 2.2rem); margin-bottom: 10px; line-height: 1.1; }
        .section-subtitle { font-size: 0.9rem; }
        
        .step { padding: 12px 8px; }
        .step-number { width: 48px; height: 48px; font-size: 1.2rem; margin: 0 auto 16px; }
        .step h4 { font-size: 1rem; margin-bottom: 6px; }
        .step p { font-size: 0.75rem; }
        
        .peace-card { padding: 20px 16px; gap: 12px; }
        .peace-card-icon { width: 44px; height: 44px; font-size: 1.1rem; }
        .peace-card h4 { font-size: 1.1rem; margin-bottom: 6px; }
        .peace-card p { font-size: 0.8rem; }
        
        .phone-frame { width: 180px; padding: 14px 12px; border-radius: 30px; }
        .phone-notch { width: 60px; height: 4px; margin: 0 auto 12px; }
        .phone-screen { padding: 16px 12px; min-height: 360px; }
        .mock-names { font-size: 1.4rem; margin: 8px 0 4px; }
        .mock-date { font-size: 0.65rem; margin-bottom: 8px; }
        .mock-countdown { gap: 4px; margin-bottom: 8px; }
        .mock-box { min-width: 35px; padding: 4px 6px; }
        .mock-box span { font-size: 0.85rem; }
        .mock-box small { font-size: 0.45rem; }
        .mock-event { padding: 8px 10px; margin-bottom: 6px; }
        .mock-event-name { font-size: 0.65rem; margin-bottom: 1px; }
        .mock-event-time { font-size: 0.55rem; }
        .mock-rsvp { margin-top: 8px; padding: 6px 16px; font-size: 0.65rem; }
        
        .theme-gallery-grid { grid-template-columns: 1fr; gap: 12px; margin-top: 40px; }
        .theme-phone-outer { padding: 20px 10px 16px; }
        .theme-phone-frame { max-width: 160px; padding: 12px 10px; }
        .theme-phone-notch { width: 50px; height: 4px; margin: 0 auto 10px; }
        .theme-label { padding: 10px 12px; }
        .theme-swatch { width: 12px; height: 12px; }
        .theme-name { font-size: 0.95rem; }
        
        .cta-section { padding: 50px 3%; }
        .cta-section h2 { font-size: clamp(1.5rem, 4.5vw, 2.5rem); margin-bottom: 10px; }
        .cta-section p { font-size: 0.9rem; margin-bottom: 30px; }
        .cta-features { gap: 12px; }
        .cta-feature { font-size: 0.8rem; }
    }
</style>

<!-- HERO -->
<section class="hero">
    <div class="hero-bg"></div>
    <div class="hero-ornament">♡</div>
    <div class="hero-content">
        <div class="hero-badge fade-up"><i class="fas fa-star"></i> Lumos Studio — Elegant Digital Wedding Invitations</div>
        <h1 class="hero-title fade-up delay-1">
            Beautiful wedding invites,<br>
            <em>crafted for your love story</em>
        </h1>
        <p class="hero-subtitle fade-up delay-2">
            Lumos Studio helps couples create stunning digital wedding invitations with elegant responsive themes, live previews, RSVP tracking, guest management, and one-click sharing for WhatsApp.
        </p>
        <div class="hero-cta fade-up delay-3">
            <a href="dashboard/register.php" class="btn-primary-gold">
                <i class="fas fa-heart"></i> Create My Invitation — Free
            </a>
            <a href="dashboard/login.php" class="btn-ghost">
                <i class="fas fa-sign-in-alt"></i> Sign In
            </a>
        </div>
        <div class="hero-proof fade-up delay-4">
            <div class="proof-item"><i class="fas fa-check-circle"></i> Free preview before payment</div>
            <div class="proof-item"><i class="fas fa-check-circle"></i> Edit anytime after publishing</div>
            <div class="proof-item"><i class="fas fa-check-circle"></i> Beautiful on every phone</div>
        </div>
    </div>
</section>

<!-- HOW IT WORKS -->
<section class="how-it-works" id="how-it-works">
    <div style="text-align:center; margin-bottom: 20px;">
        <span class="section-tag reveal">Simple & Fast</span>
        <h2 class="section-title reveal">Up and running in<br><em>under 10 minutes</em></h2>
        <div class="divider"></div>
    </div>
    <div class="steps-grid">
        <div class="step reveal">
            <div class="step-number">1</div>
            <h4>Register Free</h4>
            <p>Enter your names, wedding date, and create your account. No credit card needed.</p>
        </div>
        <div class="step reveal">
            <div class="step-number">2</div>
            <h4>Build Your Invitation</h4>
            <p>Add events, upload photos, write your love story. Preview exactly what your guests will see.</p>
        </div>
        <div class="step reveal">
            <div class="step-number">3</div>
            <h4>Add Your Guests</h4>
            <p>Add guests with their names and WhatsApp numbers. Tag by category and side.</p>
        </div>
        <div class="step reveal">
            <div class="step-number">4</div>
            <h4>Pay & Publish</h4>
            <p>Upload a bank transfer slip. We activate your invitation within hours.</p>
        </div>
        <div class="step reveal">
            <div class="step-number">5</div>
            <h4>Share the Link</h4>
            <p>Share one link via WhatsApp. Guests enter their number and open their personal invitation.</p>
        </div>
    </div>
</section>

<!-- PEACE OF MIND -->
<section id="peace">
    <div style="text-align:center; margin-bottom:20px;">
        <span class="section-tag reveal">Built for Peace of Mind</span>
        <h2 class="section-title reveal">Buy with confidence,<br><em>edit with confidence</em></h2>
        <div class="divider"></div>
    </div>
    <div class="peace-grid">
        <div class="peace-card reveal">
            <div class="peace-card-icon"><i class="fas fa-eye"></i></div>
            <div>
                <h4>Free Preview Before You Pay</h4>
                <p>Build the full invitation, see exactly how it looks on a real phone, then decide. No card on file, no pressure.</p>
            </div>
        </div>
        <div class="peace-card reveal">
            <div class="peace-card-icon"><i class="fas fa-edit"></i></div>
            <div>
                <h4>Edit Anytime — Even After Publishing</h4>
                <p>Venue changed? Guest list grew? Update once and every guest sees the latest version automatically.</p>
            </div>
        </div>
        <div class="peace-card reveal">
            <div class="peace-card-icon"><i class="fas fa-university"></i></div>
            <div>
                <h4>Hassle-Free Payment</h4>
                <p>Upload a bank transfer slip via WhatsApp. We review and unlock your invitation — no online card payments needed.</p>
            </div>
        </div>
        <div class="peace-card reveal">
            <div class="peace-card-icon"><i class="fas fa-headset"></i></div>
            <div>
                <h4>Personal Support</h4>
                <p>We're a local team. Have a question or need help? Reach us directly on WhatsApp and we'll sort it out.</p>
            </div>
        </div>
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

<?php 
// Footer Layout එක Load කිරීම
require 'layouts/footer.php'; 
?>