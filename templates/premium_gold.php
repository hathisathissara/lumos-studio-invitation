<?php
$hero_style = "";
if (!empty($wedding['hero_image'])) {
    $img_path = htmlspecialchars($wedding['hero_image']);
    $hero_style = "style=\"background: linear-gradient(180deg, rgba(10,10,20,0.35) 0%, rgba(18,18,36,0.55) 55%, var(--cream) 100%), url('{$img_path}') center/cover no-repeat;\"";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($wedding['bride_name'] . ' & ' . $wedding['groom_name']); ?> — Wedding Invitation</title>
    <meta name="description" content="You are warmly invited to the wedding of <?php echo htmlspecialchars($wedding['bride_name'] . ' and ' . $wedding['groom_name']); ?>.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,400;1,600&family=Great+Vibes&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --navy: #0d0d1a;
            --navy-2: #17172c;
            --gold: #c9a05a;
            --gold-light: #e8d5a3;
            --gold-dark: #8a6520;
            --couple-color: #e3c07f;
            --cream: #fdfaf5;
            --cream-2: #f9f5ee;
            --cream-border: #ede4d0;
            --text-dark: #241b10;
            --text-mid: #5a4a35;
            --text-light: #8a7560;
            --white: #ffffff;
        }

        html { scroll-behavior: smooth; }

        body {
            background: var(--cream);
            font-family: 'Cormorant Garamond', serif;
            color: var(--text-dark);
            min-height: 100vh;
            position: relative;
        }

        ::selection { background: var(--gold-light); color: var(--navy); }
        .reveal { opacity: 0; }

        /* Persistent fixed background animation — visible behind every section */
        #page-canvas { position: fixed; inset: 0; width: 100%; height: 100%; z-index: 0; pointer-events: none; }
        body > * { position: relative; z-index: 1; }
        #page-canvas { z-index: 0; }

        /* Preview banner */
        .preview-bar {
            background: linear-gradient(135deg, var(--navy), var(--navy-2));
            color: var(--gold-light);
            text-align: center;
            padding: 10px 20px;
            font-family: 'Inter', sans-serif;
            font-size: 0.8rem;
            letter-spacing: 1px;
            position: sticky;
            top: 0;
            z-index: 200;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }
        .preview-bar a { color: var(--gold-light); text-decoration: underline; text-underline-offset: 3px; }

        .reserved-note {
            margin: 20px auto;
            background: rgba(201,160,90,0.12);
            border: 1px dashed rgba(201,160,90,0.45);
            padding: 12px 22px;
            border-radius: 30px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            color: var(--gold-dark);
            font-family: 'Inter', sans-serif;
            font-size: 0.95rem;
            font-weight: 600;
            max-width: 100%;
            text-align: left;
        }
        .reserved-note i { color: var(--gold-dark); width: 18px; font-size: 1rem; }

        /* ======= HERO HEADER ======= */
        .hero-header {
            background: linear-gradient(180deg, var(--navy) 0%, var(--navy-2) 55%, var(--cream) 100%);
            padding: 70px 20px 80px;
            text-align: center;
            position: relative;
            overflow: hidden;
            border-bottom: 3px solid var(--gold);
        }
        .hero-content { position: relative; z-index: 1; text-shadow: 1px 2px 8px rgba(0,0,0,0.6), 0 1px 3px rgba(0,0,0,0.8); }
        .guest-greeting-tag {
            display: inline-block;
            font-family: 'Inter', sans-serif;
            font-size: 0.7rem; font-weight: 500; letter-spacing: 2.5px; text-transform: uppercase;
            color: rgba(227,192,127,0.9);
            margin-bottom: 12px;
        }
        .guest-name-display {
            font-family: 'Cormorant Garamond', serif;
            font-size: clamp(1.4rem, 4vw, 2rem);
            font-weight: 400; font-style: italic;
            color: rgba(245,245,240,0.95);
            margin-bottom: 32px;
        }
        .couple-names-hero {
            font-family: 'Great Vibes', cursive;
            font-size: clamp(3.5rem, 10vw, 6rem);
            color: var(--couple-color);
            line-height: 1.2;
            text-shadow: 2px 3px 10px rgba(0,0,0,0.5), 0 2px 4px rgba(0,0,0,0.7);
        }
        .couple-names-hero .amp { display: block; font-size: 0.45em; color: rgba(227,192,127,0.6); margin: -8px 0; text-shadow: none; }
        .hero-date-area { margin-top: 28px; padding-top: 28px; border-top: 1px solid rgba(201,160,90,0.2); }
        .hero-getting-married { font-family: 'Inter', sans-serif; font-size: 0.68rem; letter-spacing: 3px; text-transform: uppercase; color: rgba(245,245,240,0.75); margin-bottom: 8px; }
        .hero-date { font-family: 'Cormorant Garamond', serif; font-size: clamp(1.4rem, 4vw, 2.2rem); font-weight: 400; color: rgba(245,245,240,0.95); letter-spacing: 2px; }
        .hero-venue { font-family: 'Inter', sans-serif; font-size: 1.05rem; color: var(--gold-light); margin-top: 12px; }

        /* ======= COUNTDOWN ======= */
        .countdown-section {
            background: rgba(249,245,238,0.7);
            backdrop-filter: blur(3px);
            -webkit-backdrop-filter: blur(3px);
            border-top: 1px solid var(--cream-border);
            border-bottom: 1px solid var(--cream-border);
            padding: 40px 20px;
            text-align: center;
        }
        .countdown-label { font-family: 'Inter', sans-serif; font-size: 0.7rem; letter-spacing: 2.5px; text-transform: uppercase; color: var(--text-light); margin-bottom: 20px; }
        .countdown { display: flex; justify-content: center; align-items: center; gap: 16px; flex-wrap: wrap; }
        .time-unit { text-align: center; min-width: 70px; }
        .time-value {
            display: block; font-family: 'Cormorant Garamond', serif;
            font-size: clamp(2.5rem, 8vw, 3.5rem); font-weight: 300; line-height: 1;
            background: linear-gradient(135deg, var(--gold), var(--gold-dark));
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        }
        .time-label { font-family: 'Inter', sans-serif; font-size: 0.65rem; letter-spacing: 2px; text-transform: uppercase; color: var(--text-light); margin-top: 4px; }
        .time-sep { font-size: 2rem; color: rgba(183,138,68,0.3); font-weight: 300; margin-bottom: 16px; }
        .just-married-msg { font-family: 'Great Vibes', cursive; font-size: 3rem; color: var(--gold-dark); }

        /* ======= CONTENT SECTIONS ======= */
        .invitation-body { max-width: 680px; margin: 0 auto; padding: 0 20px; }
        .section-divider { display: flex; align-items: center; gap: 16px; margin: 50px 0 36px; }
        .section-divider-line { flex: 1; height: 1px; background: linear-gradient(to right, transparent, var(--cream-border)); }
        .section-divider-line.right { background: linear-gradient(to left, transparent, var(--cream-border)); }
        .section-divider-icon { color: var(--gold-dark); font-size: 0.9rem; }
        .section-heading { font-family: 'Cormorant Garamond', serif; font-size: clamp(1.8rem, 5vw, 2.6rem); font-weight: 400; color: var(--text-dark); text-align: center; margin-bottom: 8px; }
        .section-heading em { font-style: italic; background: linear-gradient(135deg, var(--gold), var(--gold-dark)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .section-sub { font-family: 'Inter', sans-serif; text-align: center; color: var(--text-light); font-size: 0.82rem; letter-spacing: 1px; margin-bottom: 30px; text-transform: uppercase; }

        /* ======= LOVE STORY ======= */
        .love-story-text {
            font-size: 1.15rem; line-height: 2; color: var(--text-mid); font-style: italic; text-align: center;
            padding: 30px 24px;
            background: rgba(249,245,238,0.75);
            backdrop-filter: blur(2px);
            border-radius: 20px;
            border: 1px solid var(--cream-border);
            position: relative;
        }
        .love-story-text::before { content: '\201C'; font-family: 'Cormorant Garamond', serif; font-size: 5rem; color: rgba(183,138,68,0.18); position: absolute; top: -10px; left: 20px; line-height: 1; }

        /* ======= EVENTS ======= */
        .event-timeline { position: relative; padding-left: 0; }
        .event-card {
            background: rgba(255,255,255,0.85);
            border-left: 4px solid var(--gold);
            border-radius: 0 20px 20px 0;
            padding: 28px 28px 24px;
            margin-bottom: 20px;
            position: relative;
            overflow: hidden;
            transition: box-shadow 0.3s, transform 0.3s;
        }
        .event-card:hover { box-shadow: 0 12px 40px rgba(183,138,68,0.15); transform: translateY(-2px); }
        .event-name { font-family: 'Cormorant Garamond', serif; font-size: 1.6rem; font-weight: 600; color: var(--text-dark); margin-bottom: 12px; }
        .event-meta { display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px; }
        .event-meta-item { display: flex; align-items: center; gap: 10px; font-family: 'Inter', sans-serif; font-size: 0.88rem; color: var(--text-mid); }
        .event-meta-item i { color: var(--gold-dark); width: 16px; text-align: center; }
        .event-actions { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 16px; }
        .btn-map {
            display: inline-flex; align-items: center; gap: 6px;
            background: linear-gradient(135deg, var(--gold), var(--gold-dark));
            color: white; text-decoration: none;
            padding: 9px 18px; border-radius: 50px;
            font-family: 'Inter', sans-serif; font-size: 0.8rem; font-weight: 600;
            transition: all 0.2s; box-shadow: 0 3px 12px rgba(183,138,68,0.25);
        }
        .btn-map:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(183,138,68,0.35); color: white; }
        .btn-cal {
            display: inline-flex; align-items: center; gap: 6px;
            background: transparent; border: 1px solid var(--cream-border);
            color: var(--text-mid); text-decoration: none;
            padding: 9px 18px; border-radius: 50px;
            font-family: 'Inter', sans-serif; font-size: 0.8rem; font-weight: 500;
            transition: all 0.2s; cursor: pointer;
        }
        .btn-cal:hover { border-color: var(--gold); color: var(--gold-dark); background: rgba(183,138,68,0.05); }
        .cal-dropdown { position: relative; display: inline-block; }
        .cal-menu { display: none; position: absolute; bottom: calc(100% + 8px); left: 0; background: white; border: 1px solid var(--cream-border); border-radius: 14px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); min-width: 180px; z-index: 10; overflow: hidden; }
        .cal-menu.open { display: block; }
        .cal-menu a { display: flex; align-items: center; gap: 10px; padding: 12px 16px; font-family: 'Inter', sans-serif; font-size: 0.84rem; color: var(--text-mid); text-decoration: none; transition: background 0.15s; }
        .cal-menu a:hover { background: var(--cream-2); color: var(--gold-dark); }
        .cal-menu a i { width: 16px; text-align: center; }

        /* ======= SWEET MOMENTS SLIDESHOW ======= */
        .sweet-slideshow { position: relative; max-width: 640px; margin: 0 auto; border-radius: 20px; overflow: hidden; box-shadow: 0 20px 50px rgba(0,0,0,0.16); background: #000; }
        .sweet-slideshow-track { display: flex; transition: transform 0.5s ease; }
        .sweet-slide { flex: 0 0 100%; aspect-ratio: 4/3; cursor: pointer; }
        .sweet-slide img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .sweet-slide-nav {
            position: absolute; top: 50%; transform: translateY(-50%);
            background: rgba(255,255,255,0.88); color: var(--gold-dark);
            border: none; width: 42px; height: 42px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; box-shadow: 0 4px 14px rgba(0,0,0,0.25);
            transition: all 0.2s; z-index: 2;
        }
        .sweet-slide-nav:hover { background: var(--gold); color: #fff; }
        .sweet-slide-nav.prev { left: 14px; }
        .sweet-slide-nav.next { right: 14px; }
        .sweet-slide-dots { position: absolute; bottom: 14px; left: 0; right: 0; display: flex; justify-content: center; gap: 8px; z-index: 2; }
        .sweet-slide-dot { width: 8px; height: 8px; border-radius: 50%; background: rgba(255,255,255,0.6); cursor: pointer; transition: all 0.25s; }
        .sweet-slide-dot.active { background: var(--gold); width: 22px; border-radius: 5px; }
        @media (max-width: 560px) { .sweet-slide-nav { width: 34px; height: 34px; font-size: 0.8rem; } }

        /* ======= GUEST GALLERY ======= */
        .gallery-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 12px; }
        .gallery-item { border-radius: 20px; overflow: hidden; aspect-ratio: 1; cursor: pointer; position: relative; border: 3px solid var(--cream-border); }
        .gallery-item img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s ease; }
        .gallery-item:hover img { transform: scale(1.06); }
        .gallery-item::after { content: ''; position: absolute; inset: 0; background: linear-gradient(to bottom, transparent 50%, rgba(0,0,0,0.2)); opacity: 0; transition: opacity 0.3s; }
        .gallery-item:hover::after { opacity: 1; }

        /* Lightbox */
        .lightbox { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.92); z-index: 1000; align-items: center; justify-content: center; }
        .lightbox.open { display: flex; }
        .lightbox img { max-width: 90vw; max-height: 90vh; border-radius: 16px; object-fit: contain; }
        .lightbox-close { position: absolute; top: 20px; right: 20px; color: white; font-size: 1.8rem; cursor: pointer; opacity: 0.7; transition: opacity 0.2s; }
        .lightbox-close:hover { opacity: 1; }

        /* ======= RSVP ======= */
        .rsvp-section { background: rgba(249,245,238,0.7); backdrop-filter: blur(3px); -webkit-backdrop-filter: blur(3px); border-top: 1px solid var(--cream-border); padding: 60px 20px; text-align: center; }
        .rsvp-card {
            max-width: 500px; margin: 0 auto; background: white;
            border-top: 4px solid var(--gold);
            border-radius: 0 0 24px 24px;
            padding: 40px 36px;
            box-shadow: 0 8px 40px rgba(0,0,0,0.08);
        }
        .rsvp-title { font-family: 'Cormorant Garamond', serif; font-size: 2rem; font-weight: 400; color: var(--text-dark); margin-bottom: 6px; }
        .rsvp-subtitle { font-family: 'Inter', sans-serif; font-size: 0.8rem; color: var(--text-light); letter-spacing: 1px; text-transform: uppercase; margin-bottom: 28px; }
        .rsvp-options { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 20px; }
        .rsvp-option input[type="radio"] { display: none; }
        .rsvp-option label { display: flex; flex-direction: column; align-items: center; gap: 8px; padding: 18px 12px; border: 2px solid var(--cream-border); border-radius: 16px; cursor: pointer; font-family: 'Inter', sans-serif; font-size: 0.82rem; font-weight: 500; color: var(--text-mid); transition: all 0.2s; }
        .rsvp-option label i { font-size: 1.4rem; }
        .rsvp-option:first-child input[type="radio"]:checked + label { border-color: #22c55e; background: rgba(34,197,94,0.05); color: #16a34a; }
        .rsvp-option:last-child input[type="radio"]:checked + label { border-color: #ef4444; background: rgba(239,68,68,0.05); color: #dc2626; }
        .rsvp-note { width: 100%; background: var(--cream-2); border: 1px solid var(--cream-border); border-radius: 14px; padding: 14px 16px; font-family: 'Inter', sans-serif; font-size: 0.88rem; color: var(--text-mid); resize: none; outline: none; transition: border-color 0.2s; margin-bottom: 16px; }
        .rsvp-note:focus { border-color: var(--gold); }
        .btn-rsvp-submit {
            width: 100%; background: linear-gradient(135deg, var(--gold-dark), var(--navy));
            color: var(--gold-light); border: none; border-radius: 50px; padding: 16px;
            font-family: 'Inter', sans-serif; font-size: 0.9rem; font-weight: 700; letter-spacing: 2px; text-transform: uppercase;
            cursor: pointer; transition: all 0.3s;
        }
        .btn-rsvp-submit:hover { transform: translateY(-2px); box-shadow: 0 10px 30px rgba(13,13,26,0.35); }

        /* ======= FOOTER ======= */
        .inv-footer { text-align: center; padding: 40px 20px; font-family: 'Inter', sans-serif; font-size: 0.75rem; color: rgba(245,245,240,0.6); border-top: 1px solid var(--cream-border); background: linear-gradient(135deg, var(--navy), var(--navy-2)); }
        .inv-footer .brand { font-family: 'Great Vibes', cursive; font-size: 1.4rem; color: var(--gold); display: block; margin-bottom: 6px; }

        @media (max-width: 500px) {
            .rsvp-card { padding: 30px 20px; }
            .rsvp-options { grid-template-columns: 1fr 1fr; }
        }

        /* ======================================================================
           LUXURY 3D EXPERIENCE LAYER
           ====================================================================== */

        /* --- Hero-local 3D canvas: rotating rings, starfield, crystal accents --- */
        .hero-header { perspective: 1200px; }
        #hero3d-canvas {
            position: absolute; inset: 0; width: 100%; height: 100%;
            z-index: 0; pointer-events: none;
            opacity: 0; transition: opacity 1.6s ease;
        }
        #hero3d-canvas.ready { opacity: 1; }
        .hero-content { transition: transform 0.15s ease-out; will-change: transform; }

        /* --- Ambient rose petal layer (full page, behind content, above bg canvas) --- */
        #petal-canvas { position: fixed; inset: 0; width: 100vw; height: 100vh; z-index: 2; pointer-events: none; }

        /* --- Golden cursor sparkle trail --- */
        #trail-canvas { position: fixed; inset: 0; width: 100vw; height: 100vh; z-index: 3; pointer-events: none; }

        /* --- Glassmorphism RSVP card --- */
        .rsvp-card {
            background: rgba(255,255,255,0.5);
            backdrop-filter: blur(20px) saturate(160%);
            -webkit-backdrop-filter: blur(20px) saturate(160%);
            border: 1px solid rgba(201,160,90,0.35);
            box-shadow: 0 8px 40px rgba(0,0,0,0.1), 0 0 0 1px rgba(255,255,255,0.4) inset, 0 0 40px rgba(201,160,90,0.12);
            transform-style: preserve-3d;
            transition: transform 0.25s ease, box-shadow 0.25s ease;
        }
        .rsvp-card:hover { box-shadow: 0 16px 50px rgba(0,0,0,0.14), 0 0 0 1px rgba(255,255,255,0.5) inset, 0 0 60px rgba(201,160,90,0.2); }
        .rsvp-option label {
            background: rgba(255,255,255,0.35);
            backdrop-filter: blur(6px);
        }

        /* --- Elegant magnetic tilt for cards --- */
        .tilt-card { transform-style: preserve-3d; transition: transform 0.25s ease, box-shadow 0.25s ease; }

        .event-card { transition: transform 0.25s ease, box-shadow 0.3s; }

        /* --- Buttons: shimmering gold hover --- */
        .btn-map, .btn-rsvp-submit { position: relative; overflow: hidden; }
        .btn-map::after, .btn-rsvp-submit::after {
            content: ''; position: absolute; top: 0; left: -60%; width: 40%; height: 100%;
            background: linear-gradient(120deg, transparent, rgba(255,255,255,0.55), transparent);
            transform: skewX(-20deg); transition: left 0.7s ease;
        }
        .btn-map:hover::after, .btn-rsvp-submit:hover::after { left: 130%; }

        /* --- 3D Countdown timer cards --- */
        .countdown-section { perspective: 900px; }
        .time-unit {
            background: rgba(255,255,255,0.4);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(201,160,90,0.3);
            border-radius: 16px;
            padding: 14px 16px 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08), 0 0 20px rgba(201,160,90,0.1);
            transform-style: preserve-3d;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            animation: floatCard 4s ease-in-out infinite;
        }
        .time-unit:nth-child(3) { animation-delay: 0.3s; }
        .time-unit:nth-child(5) { animation-delay: 0.6s; }
        .time-unit:nth-child(7) { animation-delay: 0.9s; }
        .time-unit:hover { box-shadow: 0 16px 40px rgba(0,0,0,0.12), 0 0 30px rgba(201,160,90,0.22); }
        @keyframes floatCard { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-6px); } }

        /* --- 3D Coverflow gallery carousel --- */
        .sweet-slideshow {
            background: transparent; box-shadow: none; overflow: visible;
            perspective: 1300px; height: auto; min-height: 260px;
        }
        .sweet-slideshow-track {
            position: relative; display: block; width: 100%;
            height: 0; padding-bottom: 56%;
            transform-style: preserve-3d;
        }
        .sweet-slide {
            position: absolute; top: 50%; left: 50%; flex: none;
            width: 62%; aspect-ratio: 4/3;
            margin: 0; border-radius: 18px; overflow: hidden;
            box-shadow: 0 20px 45px rgba(0,0,0,0.35);
            transition: transform 0.6s cubic-bezier(.2,.7,.2,1), opacity 0.6s ease;
            will-change: transform, opacity;
        }
        .sweet-slide img { width: 100%; height: 100%; object-fit: cover; }
        .sweet-slide.is-active { box-shadow: 0 26px 60px rgba(183,138,68,0.35); }

        /* --- Floating golden frame around the carousel --- */
        .golden-frame-wrap { position: relative; max-width: 640px; margin: 0 auto; }
        .golden-frame-svg {
            position: absolute; inset: -22px; width: calc(100% + 44px); height: calc(100% + 44px);
            pointer-events: none; z-index: 5;
            animation: frameFloat 6s ease-in-out infinite;
        }
        @keyframes frameFloat { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-8px); } }

        @media (max-width: 560px) {
            .sweet-slide { width: 78%; }
            .golden-frame-svg { inset: -12px; width: calc(100% + 24px); height: calc(100% + 24px); }
        }
    </style>
</head>
<body>

<canvas id="page-canvas"></canvas>
<canvas id="petal-canvas"></canvas>
<canvas id="trail-canvas"></canvas>

<?php if ($guest_id == 0): ?>
<div class="preview-bar">
    <i class="fas fa-eye"></i>
    <strong>PREVIEW MODE</strong> — This is how your guests will see the invitation.
    <a href="dashboard/index.php">← Back to Dashboard</a>
</div>
<?php endif; ?>

<?php if ($msg): ?>
<div style="max-width:680px; margin:20px auto; padding:0 20px;">
    <?php echo $msg; ?>
</div>
<?php endif; ?>

<!-- HERO HEADER -->
<div class="hero-header position-relative overflow-hidden text-center" <?php echo $hero_style; ?>>
    <canvas id="hero3d-canvas" aria-hidden="true"></canvas>
    <div class="hero-content reveal">
        <span class="guest-greeting-tag">You're Warmly Invited</span>
        <div class="guest-name-display">Dear <?php echo htmlspecialchars($guest_name); ?>,</div>

        <div class="couple-names-hero">
            <?php echo htmlspecialchars($wedding['bride_name']); ?>
            <span class="amp">&</span>
            <?php echo htmlspecialchars($wedding['groom_name']); ?>
        </div>

        <div class="hero-date-area">
            <p class="hero-getting-married">We are getting married on</p>
            <p class="hero-date"><?php echo date("l, d F Y", strtotime($wedding['wedding_date'])); ?></p>
            <?php if (!empty($wedding['venue'])): ?>
            <p class="hero-venue"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($wedding['venue']); ?></p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- COUNTDOWN TIMER -->
<div class="countdown-section">
    <p class="countdown-label">Counting down to the big day</p>
    <div class="countdown" id="countdown">
        <div class="time-unit"><span class="time-value" id="cd-days">00</span><span class="time-label">Days</span></div>
        <span class="time-sep">:</span>
        <div class="time-unit"><span class="time-value" id="cd-hours">00</span><span class="time-label">Hours</span></div>
        <span class="time-sep">:</span>
        <div class="time-unit"><span class="time-value" id="cd-mins">00</span><span class="time-label">Minutes</span></div>
        <span class="time-sep">:</span>
        <div class="time-unit"><span class="time-value" id="cd-secs">00</span><span class="time-label">Seconds</span></div>
    </div>
</div>

<div class="invitation-body container py-4">

    <!-- LOVE STORY -->
    <?php if (!empty($wedding['love_story'])): ?>
    <div class="section-divider reveal">
        <div class="section-divider-line"></div>
        <div class="section-divider-icon"><i class="fas fa-heart"></i></div>
        <div class="section-divider-line right"></div>
    </div>
    <h2 class="section-heading reveal">Our <em>Love Story</em></h2>
    <p class="section-sub">How it all began</p>
    <div class="love-story-text reveal">
        <?php echo nl2br(htmlspecialchars($wedding['love_story'])); ?>
    </div>
    <?php endif; ?>

    <!-- EVENTS / PROGRAMME -->
    <div class="section-divider reveal">
        <div class="section-divider-line"></div>
        <div class="section-divider-icon"><i class="fas fa-calendar-alt"></i></div>
        <div class="section-divider-line right"></div>
    </div>
    <h2 class="section-heading reveal"><em>Wedding</em> Programme</h2>
    <p class="section-sub">Join us for these celebrations</p>

    <?php if (count($wedding_events) > 0): ?>
        <div class="event-timeline row row-cols-1 row-cols-md-2 g-4 justify-content-center">
            <?php foreach ($wedding_events as $ev):
                $ev_start = date('Ymd\THis', strtotime($ev['event_date_time']));
                $ev_end = date('Ymd\THis', strtotime($ev['event_date_time']) + 7200);
                $ev_title = urlencode($ev['event_name'] . ' — ' . $wedding['bride_name'] . ' & ' . $wedding['groom_name']);
                $ev_loc = urlencode($ev['location_name']);
                $ev_gcal = "https://calendar.google.com/calendar/render?action=TEMPLATE&text={$ev_title}&dates={$ev_start}/{$ev_end}&location={$ev_loc}";
                $ev_ics = "calendar.php?wedding_id={$wedding_id}&event_id={$ev['id']}";
                $ev_outlook = "https://outlook.live.com/calendar/0/deeplink/compose?path=/calendar/action/compose&rru=addevent&subject=" . $ev_title . "&startdt=" . urlencode(date('c', strtotime($ev['event_date_time']))) . "&enddt=" . urlencode(date('c', strtotime($ev['event_date_time']) + 7200)) . "&location=" . $ev_loc;
            ?>
            <div class="event-card col text-center reveal">
                <div class="event-name"><?php echo htmlspecialchars($ev['event_name']); ?></div>
                <div class="event-meta">
                    <div class="event-meta-item"><i class="far fa-calendar"></i><span><?php echo date("l, d F Y", strtotime($ev['event_date_time'])); ?></span></div>
                    <div class="event-meta-item"><i class="far fa-clock"></i><span><?php echo date("h:i A", strtotime($ev['event_date_time'])); ?></span></div>
                    <div class="event-meta-item"><i class="fas fa-map-marker-alt"></i><span><?php echo htmlspecialchars($ev['location_name']); ?></span></div>
                </div>
                <div class="event-actions">
                    <?php if (!empty($ev['google_map_link'])): ?>
                    <a href="<?php echo htmlspecialchars($ev['google_map_link']); ?>" target="_blank" class="btn-map" rel="noopener">
                        <i class="fas fa-directions"></i> Get Directions
                    </a>
                    <?php endif; ?>
                    <div class="cal-dropdown">
                        <button class="btn-cal" onclick="toggleCal(this)">
                            <i class="fas fa-calendar-plus"></i> Add to Calendar
                        </button>
                        <div class="cal-menu">
                            <a href="<?php echo $ev_gcal; ?>" target="_blank" rel="noopener"><i class="fab fa-google" style="color:#4285f4;"></i> Google Calendar</a>
                            <a href="<?php echo $ev_ics; ?>" download><i class="fab fa-apple" style="color:#555;"></i> Apple Calendar</a>
                            <a href="<?php echo $ev_outlook; ?>" target="_blank" rel="noopener"><i class="fas fa-envelope" style="color:#0072c6;"></i> Outlook</a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p style="text-align:center; color:var(--text-light); font-style:italic; padding:30px 0;">Event details will be updated soon.</p>
    <?php endif; ?>

    <!-- GALLERY -->
    <?php if (count($gallery_images) > 0): ?>
    <div class="section-divider reveal">
        <div class="section-divider-line"></div>
        <div class="section-divider-icon"><i class="fas fa-camera"></i></div>
        <div class="section-divider-line right"></div>
    </div>
    <h2 class="section-heading reveal"><em>Sweet</em> Moments</h2>
    <p class="section-sub">Our engagement memories</p>

    <div class="golden-frame-wrap">
        <svg class="golden-frame-svg" viewBox="0 0 100 100" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <linearGradient id="frameGoldGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#f4e2b8"/>
                    <stop offset="50%" stop-color="#c9a05a"/>
                    <stop offset="100%" stop-color="#8a6520"/>
                </linearGradient>
            </defs>
            <rect x="1.5" y="1.5" width="97" height="97" rx="6" fill="none" stroke="url(#frameGoldGrad)" stroke-width="0.6" vector-effect="non-scaling-stroke"/>
            <path d="M1.5 10 V1.5 H10" fill="none" stroke="url(#frameGoldGrad)" stroke-width="1" vector-effect="non-scaling-stroke"/>
            <path d="M90 1.5 H98.5 V10" fill="none" stroke="url(#frameGoldGrad)" stroke-width="1" vector-effect="non-scaling-stroke"/>
            <path d="M98.5 90 V98.5 H90" fill="none" stroke="url(#frameGoldGrad)" stroke-width="1" vector-effect="non-scaling-stroke"/>
            <path d="M10 98.5 H1.5 V90" fill="none" stroke="url(#frameGoldGrad)" stroke-width="1" vector-effect="non-scaling-stroke"/>
        </svg>
        <div class="sweet-slideshow reveal" id="sweet-slideshow">
            <div class="sweet-slideshow-track" id="sweet-slideshow-track">
                <?php foreach ($gallery_images as $img): ?>
                <div class="sweet-slide" onclick="openLightbox('<?php echo htmlspecialchars($img['image_path']); ?>')">
                    <img src="<?php echo htmlspecialchars($img['image_path']); ?>" alt="Our moment" loading="lazy">
                </div>
                <?php endforeach; ?>
            </div>
            <?php if (count($gallery_images) > 1): ?>
            <button type="button" class="sweet-slide-nav prev" onclick="sweetSlideMove(-1)" aria-label="Previous photo"><i class="fas fa-chevron-left"></i></button>
            <button type="button" class="sweet-slide-nav next" onclick="sweetSlideMove(1)" aria-label="Next photo"><i class="fas fa-chevron-right"></i></button>
            <div class="sweet-slide-dots" id="sweet-slide-dots"></div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

</div><!-- /invitation-body -->

<!-- LIGHTBOX -->
<div class="lightbox" id="lightbox" onclick="closeLightbox()">
    <span class="lightbox-close" onclick="closeLightbox()"><i class="fas fa-times"></i></span>
    <img src="" id="lightbox-img" alt="">
</div>
<!-- =====================================================================
         📸 GUEST SHARED GALLERY SECTION (පැකේජය අනුව පමණක් පෙන්වයි)
         ===================================================================== -->
    <?php if (isset($has_guest_gallery) && $has_guest_gallery): ?>
    <div class="invitation-body">
    <div class="section-divider reveal">
        <div class="section-divider-line"></div>
        <div class="section-divider-icon"><i class="fas fa-images"></i></div>
        <div class="section-divider-line right"></div>
    </div>
    <h2 class="section-heading reveal"><em>Guest</em> Shared Moments</h2>
    <p class="section-sub">Capture and share your beautiful memories with us!</p>

    <!-- Upload Box (Preview mode එකේදී අක්‍රීය වේ) -->
    <div style="background: rgba(249,245,238,0.75); border: 2px dashed var(--gold); border-radius: 20px; padding: 30px 20px; text-align: center; margin-bottom: 30px;">
        <?php if ($guest_id == 0): ?>
            <p class="text-muted small"><i class="fas fa-lock"></i> Photo upload is disabled in Preview Mode.</p>
        <?php else: ?>
            <i class="fas fa-camera-retro" style="font-size: 2.2rem; color: var(--gold-dark); margin-bottom: 12px; display: block;"></i>
            <h5 class="fw-bold" style="font-family:'Inter', sans-serif; font-size: 0.95rem; color: var(--navy); margin-bottom: 6px;">Share a Photo from Your Phone</h5>
            <p class="text-muted" style="font-size: 0.8rem; margin-bottom: 15px;">Did you take some candid photos of the couple? Upload them here to share with everyone!</p>
            
            <input type="file" id="guest-image-input" accept="image/*" style="display: none;">
            <button type="button" class="btn-map" onclick="document.getElementById('guest-image-input').click()" style="border: none; cursor: pointer;">
                <i class="fas fa-cloud-upload-alt"></i> Upload Wedding Photo
            </button>
            
            <!-- Uploading Loader -->
            <div id="guest-upload-loader" style="display: none; margin-top: 15px;" class="fw-bold text-success small">
                <i class="fas fa-spinner fa-spin me-1"></i> Optimizing & Uploading... Please wait!
            </div>
        <?php endif; ?>
    </div>

    <!-- Guest Shared Grid Display -->
    <div class="gallery-grid" id="guest-gallery-grid" style="margin-bottom: 30px;">
        <?php if (isset($guest_images) && count($guest_images) > 0): ?>
            <?php foreach ($guest_images as $g_img): ?>
            <div class="gallery-item reveal" onclick="openLightbox('<?php echo htmlspecialchars($g_img['image_path']); ?>')" style="border-color: #22c55e;">
                <img src="<?php echo htmlspecialchars($g_img['image_path']); ?>" alt="Guest moment" loading="lazy">
                <!-- පින්තූරය එවූ අමුත්තාගේ නම යටින් පෙන්වයි -->
                <div style="position: absolute; bottom: 0; left: 0; right: 0; background: rgba(0,0,0,0.6); color: white; font-size: 0.65rem; padding: 4px; font-family: 'Inter', sans-serif; text-align: center; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; z-index: 2;">
                    By <?php echo htmlspecialchars($g_img['guest_name']); ?>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center text-muted small py-4" style="font-style: italic;" id="no-guest-pics">
                No guest photos shared yet. Be the first to share a moment! 🌸
            </div>
        <?php endif; ?>
    </div>
    </div>
    
    <!-- Compressor.js Load කිරීම (නොමැති නම් පමණක්) -->
    <script src="https://cdn.jsdelivr.net/npm/compressorjs@1.2.1/dist/compressor.min.js"></script>
    <script>
    document.getElementById('guest-image-input')?.addEventListener('change', function(event) {
        const file = event.target.files[0];
        if (!file) return;

        const loader = document.getElementById('guest-upload-loader');
        loader.style.display = 'block';

        // Compressor.js භාවිතයෙන් අමුත්තාගේ පින්තූරය 0.6 quality එකට WebP කර Compress කිරීම
        new Compressor(file, {
            quality: 0.6,
            mimeType: 'image/webp',
            maxWidth: 1200,
            success(result) {
                const formData = new FormData();
                const cleanName = file.name.substring(0, file.name.lastIndexOf('.')) + '.webp';
                
                formData.append('guest_image', result, cleanName);
                formData.append('action', 'guest_upload_image');

                // AJAX POST Request (view_invitation.php වෙත යැවීම)
                fetch('view_invitation.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    loader.style.display = 'none';
                    if (data.success) {
                        alert("Thank you! Your wedding photo has been shared successfully. 🎉");
                        // Reload වෙනුවට අලුත් පින්තූරය gallery එකට එකතු කිරීම —
                        // මෙයින් කලින් තිබූ අමුත්තන්ගේ පින්තූර අස් නොවී රැඳී පවතී.
                        const grid = document.getElementById('guest-gallery-grid');
                        const emptyMsg = document.getElementById('no-guest-pics');
                        if (emptyMsg) emptyMsg.remove();
                        if (grid && data.image_path) {
                            const item = document.createElement('div');
                            item.className = 'gallery-item';
                            item.style.borderColor = '#22c55e';
                            item.style.opacity = '0';
                            item.onclick = function() { openLightbox(data.image_path); };
                            item.innerHTML = `
                                <img src="${data.image_path}" alt="Guest moment" loading="lazy">
                                <div style="position: absolute; bottom: 0; left: 0; right: 0; background: rgba(0,0,0,0.6); color: white; font-size: 0.65rem; padding: 4px; font-family: 'Inter', sans-serif; text-align: center; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; z-index: 2;">
                                    By ${data.guest_name ? data.guest_name : 'You'}
                                </div>`;
                            grid.appendChild(item);
                            if (typeof anime !== 'undefined') {
                                anime({ targets: item, opacity: [0, 1], scale: [0.85, 1], easing: 'easeOutBack', duration: 700 });
                            } else {
                                item.style.opacity = '1';
                            }
                        } else {
                            location.reload();
                        }
                    } else {
                        alert("Upload failed: " + data.message);
                    }
                })
                .catch(error => {
                    loader.style.display = 'none';
                    console.error('Error:', error);
                    alert("An error occurred during upload.");
                });
            },
            error(err) {
                loader.style.display = 'none';
                console.error(err.message);
                alert("Image optimization failed.");
            }
        });
    });
    </script>
    <?php endif; ?>
<!-- RSVP -->
<div class="rsvp-section">
    <div class="section-divider reveal" style="max-width:680px; margin:0 auto 20px;">
        <div class="section-divider-line"></div>
        <div class="section-divider-icon"><i class="fas fa-reply"></i></div>
        <div class="section-divider-line right"></div>
    </div>

    <div class="rsvp-card text-center reveal">
        <h2 class="rsvp-title">RSVP</h2>
        <p class="rsvp-subtitle">Will you be joining us?</p>
            <?php if (isset($current_guest['seats_reserved']) && $current_guest['seats_reserved'] > 0): ?>
    <div class="reserved-note" style="margin-bottom: 20px;">
        <i class="fas fa-chair"></i>
        <span>
            We have reserved <strong><?php echo intval($current_guest['seats_reserved']); ?></strong> seat(s) in your honor.
        </span>
    </div>
<?php endif; ?>
        <?php if ($msg): ?>
            <?php echo $msg; ?>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="rsvp-options">
                <div class="rsvp-option">
                    <input type="radio" name="rsvp_status" id="rsvp-yes" value="accepted"
                        <?php if ($current_guest['rsvp_status'] == 'accepted') echo 'checked'; ?> required>

                    <label for="rsvp-yes">
                        <i class="fas fa-heart" style="color:#22c55e;"></i>
                        Joyfully Accept
                    </label>
                </div>
                <div class="rsvp-option">
                    <input type="radio" name="rsvp_status" id="rsvp-no" value="rejected"
                        <?php if ($current_guest['rsvp_status'] == 'rejected') echo 'checked'; ?>>
                    <label for="rsvp-no">
                        <i class="fas fa-heart-broken" style="color:#ef4444;"></i>
                        Regretfully Decline
                    </label>
                </div>
            </div>

            <textarea
                name="guest_note"
                class="rsvp-note"
                rows="3"
                placeholder="Any notes for the couple? (dietary needs, etc.) — optional"
            ><?php echo !empty($current_guest['guest_note']) ? htmlspecialchars($current_guest['guest_note']) : ''; ?></textarea>

            <button type="submit" name="submit_rsvp" class="btn-rsvp-submit">
                Send My RSVP
            </button>
        </form>
    </div>
</div>

<!-- FOOTER -->
<div class="inv-footer">
    <span class="brand">Lumus Studio</span><br>
    Digital Wedding Invitations · Designed by Hathisa Thissara
</div>

<!-- Three.js (persistent page-wide gold-dust animation) + anime.js (scroll reveals) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/animejs/3.2.1/anime.min.js"></script>

<script>
// Countdown
const weddingDate = new Date("<?php echo $wedding['wedding_date']; ?> 00:00:00").getTime();
function tick() {
    const now = Date.now();
    const dist = weddingDate - now;
    if (dist < 0) {
        document.getElementById('countdown').innerHTML = '<p class="just-married-msg">Just Married! 🎉</p>';
        return;
    }
    const d = Math.floor(dist / 86400000);
    const h = Math.floor((dist % 86400000) / 3600000);
    const m = Math.floor((dist % 3600000) / 60000);
    const s = Math.floor((dist % 60000) / 1000);
    document.getElementById('cd-days').textContent = String(d).padStart(2,'0');
    document.getElementById('cd-hours').textContent = String(h).padStart(2,'0');
    document.getElementById('cd-mins').textContent = String(m).padStart(2,'0');
    document.getElementById('cd-secs').textContent = String(s).padStart(2,'0');
}
tick();
setInterval(tick, 1000);

// Calendar dropdown
function toggleCal(btn) {
    const menu = btn.nextElementSibling;
    document.querySelectorAll('.cal-menu.open').forEach(m => { if (m !== menu) m.classList.remove('open'); });
    menu.classList.toggle('open');
}
document.addEventListener('click', (e) => {
    if (!e.target.closest('.cal-dropdown')) {
        document.querySelectorAll('.cal-menu.open').forEach(m => m.classList.remove('open'));
    }
});

// Lightbox
function openLightbox(src) {
    document.getElementById('lightbox-img').src = src;
    document.getElementById('lightbox').classList.add('open');
    document.body.style.overflow = 'hidden';
}
function closeLightbox() {
    document.getElementById('lightbox').classList.remove('open');
    document.body.style.overflow = '';
}
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeLightbox(); });

// Sweet Moments 3D Coverflow Carousel
(function() {
    const track = document.getElementById('sweet-slideshow-track');
    if (!track) return;
    const slides = Array.from(track.children);
    const total = slides.length;
    let idx = 0;
    const dotsWrap = document.getElementById('sweet-slide-dots');
    if (dotsWrap && total > 1) {
        for (let i = 0; i < total; i++) {
            const dot = document.createElement('span');
            dot.className = 'sweet-slide-dot' + (i === 0 ? ' active' : '');
            dot.onclick = () => goToSlide(i);
            dotsWrap.appendChild(dot);
        }
    }

    // Preserve each slide's original click handler (opens the lightbox),
    // but only fire it when that slide is the centered one. Clicking a
    // side slide brings it to the front instead — like a real coverflow.
    const originalHandlers = slides.map(s => { const h = s.onclick; s.onclick = null; return h; });

    function updateSlide() {
        slides.forEach((slide, i) => {
            const delta = i - idx;
            const abs = Math.abs(delta);
            slide.classList.toggle('is-active', delta === 0);
            if (abs > 3) {
                slide.style.opacity = '0';
                slide.style.pointerEvents = 'none';
                return;
            }
            const tx = delta * 58; // % of container width
            const rot = delta * -32;
            const tz = -abs * 140;
            const scale = 1 - abs * 0.14;
            slide.style.transform = `translate(-50%,-50%) translateX(${tx}%) translateZ(${tz}px) rotateY(${rot}deg) scale(${Math.max(scale, 0.55)})`;
            slide.style.opacity = String(Math.max(1 - abs * 0.3, 0));
            slide.style.zIndex = String(10 - abs);
            slide.style.pointerEvents = 'auto';
        });
        if (dotsWrap) {
            Array.from(dotsWrap.children).forEach((d, i) => d.classList.toggle('active', i === idx));
        }
    }
    function goToSlide(i) {
        idx = (i + total) % total;
        updateSlide();
    }
    window.sweetSlideMove = function(dir) { goToSlide(idx + dir); };

    slides.forEach((slide, i) => {
        slide.addEventListener('click', function(e) {
            if (i === idx) {
                if (typeof originalHandlers[i] === 'function') originalHandlers[i].call(slide, e);
            } else {
                goToSlide(i);
            }
        });
    });

    let autoTimer;
    function startAuto() { if (total > 1) autoTimer = setInterval(() => goToSlide(idx + 1), 4200); }
    function stopAuto() { clearInterval(autoTimer); }

    const wrap = document.getElementById('sweet-slideshow');
    wrap.addEventListener('mouseenter', stopAuto);
    wrap.addEventListener('mouseleave', startAuto);

    let touchStartX = 0;
    track.addEventListener('touchstart', e => { touchStartX = e.touches[0].clientX; stopAuto(); }, { passive: true });
    track.addEventListener('touchend', e => {
        const diff = e.changedTouches[0].clientX - touchStartX;
        if (diff > 50) goToSlide(idx - 1);
        else if (diff < -50) goToSlide(idx + 1);
        startAuto();
    }, { passive: true });

    updateSlide();
    startAuto();
})();

// ================= THREE.JS — persistent gold-dust background =================
// This canvas is position:fixed and covers the FULL PAGE (not just the hero),
// so the same subtle sparkle animation stays visible behind every section as you scroll.
(function initPageScene() {
    const canvas = document.getElementById('page-canvas');
    if (!canvas || typeof THREE === 'undefined') return;

    const renderer = new THREE.WebGLRenderer({ canvas, alpha: true, antialias: true });
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));

    const scene = new THREE.Scene();
    const camera = new THREE.PerspectiveCamera(50, window.innerWidth / window.innerHeight, 0.1, 100);
    camera.position.set(0, 0, 11);

    function resize() {
        renderer.setSize(window.innerWidth, window.innerHeight);
        camera.aspect = window.innerWidth / window.innerHeight;
        camera.updateProjectionMatrix();
    }
    resize();
    window.addEventListener('resize', resize);

    // Drifting gold dust particles, slow upward float like champagne bubbles
    const count = 110;
    const positions = new Float32Array(count * 3);
    const speeds = [];
    for (let i = 0; i < count; i++) {
        positions[i * 3] = (Math.random() - 0.5) * 20;
        positions[i * 3 + 1] = (Math.random() - 0.5) * 20;
        positions[i * 3 + 2] = (Math.random() - 0.5) * 8;
        speeds.push(0.006 + Math.random() * 0.012);
    }
    const dotGeo = new THREE.BufferGeometry();
    dotGeo.setAttribute('position', new THREE.BufferAttribute(positions, 3));
    const dotMat = new THREE.PointsMaterial({ color: 0xc9a05a, size: 0.045, transparent: true, opacity: 0.6 });
    const dots = new THREE.Points(dotGeo, dotMat);
    scene.add(dots);

    let scrollFrac = 0;
    window.addEventListener('scroll', () => {
        const max = document.body.scrollHeight - window.innerHeight;
        scrollFrac = max > 0 ? window.scrollY / max : 0;
    });

    let mouseX = 0, mouseY = 0;
    window.addEventListener('mousemove', (e) => {
        mouseX = (e.clientX / window.innerWidth - 0.5);
        mouseY = (e.clientY / window.innerHeight - 0.5);
    });

    const clock = new THREE.Clock();
    function animate() {
        requestAnimationFrame(animate);
        const t = clock.getElapsedTime();
        const pos = dotGeo.attributes.position.array;
        for (let i = 0; i < count; i++) {
            pos[i*3+1] += speeds[i];
            pos[i*3] += Math.sin(t * 0.5 + i) * 0.0015;
            if (pos[i*3+1] > 10) pos[i*3+1] = -10;
        }
        dotGeo.attributes.position.needsUpdate = true;

        scene.rotation.z = scrollFrac * 0.1;
        camera.position.x += (mouseX * 0.6 - camera.position.x) * 0.02;
        camera.position.y += (-mouseY * 0.6 - camera.position.y) * 0.02;
        camera.lookAt(0, 0, 0);

        renderer.render(scene, camera);
    }
    animate();
})();

// ================= ANIME.JS — scroll reveal =================
(function initReveals() {
    if (typeof anime === 'undefined') return;
    const els = document.querySelectorAll('.reveal');
    const io = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                anime({
                    targets: entry.target,
                    opacity: [0, 1],
                    translateY: [28, 0],
                    scale: [0.97, 1],
                    easing: 'easeOutCubic',
                    duration: 900,
                    delay: 60
                });
                io.unobserve(entry.target);
            }
        });
    }, { threshold: 0.15 });
    els.forEach(el => io.observe(el));
})();

// ================= THREE.JS — hero-local scene: rotating gold rings,
// starfield and small crystal/rose accents (procedural geometry only) =================
(function initHero3D() {
    const canvas = document.getElementById('hero3d-canvas');
    const heroEl = document.querySelector('.hero-header');
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (!canvas || !heroEl || typeof THREE === 'undefined' || prefersReducedMotion) return;

    const isSmall = window.innerWidth < 700;
    let width = heroEl.clientWidth, height = heroEl.clientHeight;

    const renderer = new THREE.WebGLRenderer({ canvas, alpha: true, antialias: true });
    renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 2));
    renderer.setSize(width, height);

    const scene = new THREE.Scene();
    const camera = new THREE.PerspectiveCamera(45, width / height, 0.1, 100);
    camera.position.set(0, 0, 8.5);

    const ambient = new THREE.AmbientLight(0xfff1d6, 0.55);
    scene.add(ambient);
    const key = new THREE.PointLight(0xffe3ad, 1.7, 30);
    key.position.set(-3, 2.5, 4);
    scene.add(key);
    const rim = new THREE.PointLight(0xc9a05a, 0.8, 30);
    rim.position.set(3, -1.5, 3);
    scene.add(rim);

    // --- Starfield ---
    const starCount = isSmall ? 90 : 160;
    const starPos = new Float32Array(starCount * 3);
    for (let i = 0; i < starCount; i++) {
        starPos[i * 3] = (Math.random() - 0.5) * 14;
        starPos[i * 3 + 1] = (Math.random() - 0.5) * 9 + 1;
        starPos[i * 3 + 2] = -3 - Math.random() * 5;
    }
    const starGeo = new THREE.BufferGeometry();
    starGeo.setAttribute('position', new THREE.BufferAttribute(starPos, 3));
    const starMat = new THREE.PointsMaterial({ color: 0xfff3d9, size: 0.045, transparent: true, opacity: 0.85 });
    const stars = new THREE.Points(starGeo, starMat);
    scene.add(stars);

    // --- Interlocking gold wedding rings ---
    const ringGroup = new THREE.Group();
    const ringMat = new THREE.MeshStandardMaterial({ color: 0xd9b878, metalness: 0.9, roughness: 0.22, emissive: 0x3a2c0f, emissiveIntensity: 0.25 });
    const ringGeo = new THREE.TorusGeometry(0.62, 0.075, 28, 72);
    const ringA = new THREE.Mesh(ringGeo, ringMat);
    const ringB = new THREE.Mesh(ringGeo, ringMat);
    ringA.position.set(-0.36, 0, 0.15);
    ringB.position.set(0.36, 0, -0.15);
    ringA.rotation.y = 0.35;
    ringB.rotation.y = -0.35;
    ringGroup.add(ringA, ringB);
    ringGroup.position.set(0, -0.4, 1.2);
    ringGroup.scale.setScalar(isSmall ? 0.85 : 1.05);
    scene.add(ringGroup);

    // --- Small crystal-heart-ish gems + rose bouquet accent, procedural only ---
    const accentGroup = new THREE.Group();
    const gemMat = new THREE.MeshStandardMaterial({ color: 0xffd9e8, metalness: 0.3, roughness: 0.15, transparent: true, opacity: 0.75, emissive: 0xff9ec2, emissiveIntensity: 0.15 });
    for (let i = 0; i < 5; i++) {
        const gem = new THREE.Mesh(new THREE.OctahedronGeometry(0.11 + Math.random() * 0.05, 0), gemMat);
        const ang = (i / 5) * Math.PI * 2;
        gem.position.set(Math.cos(ang) * 2.1, Math.sin(ang) * 1.1 - 0.4, -0.5 + Math.random() * 0.6);
        accentGroup.add(gem);
    }
    const roseColors = [0xb5473a, 0xe3a880, 0xf4d6c4];
    for (let i = 0; i < 6; i++) {
        const rose = new THREE.Mesh(
            new THREE.SphereGeometry(0.09 + Math.random() * 0.05, 8, 8),
            new THREE.MeshStandardMaterial({ color: roseColors[i % roseColors.length], roughness: 0.6 })
        );
        rose.position.set(-2.3 + Math.random() * 0.6, -0.9 + Math.random() * 0.8, -0.3 + Math.random() * 0.5);
        accentGroup.add(rose);
    }
    scene.add(accentGroup);

    canvas.classList.add('ready');

    // Mouse-based parallax / depth
    let mouseX = 0, mouseY = 0, targetX = 0, targetY = 0;
    heroEl.addEventListener('mousemove', (e) => {
        const rect = heroEl.getBoundingClientRect();
        mouseX = ((e.clientX - rect.left) / rect.width - 0.5);
        mouseY = ((e.clientY - rect.top) / rect.height - 0.5);
    });
    heroEl.addEventListener('mouseleave', () => { mouseX = 0; mouseY = 0; });

    const heroContent = document.querySelector('.hero-content');
    let frame = 0;
    function animate() {
        frame += 1;
        requestAnimationFrame(animate);
        targetX += (mouseX - targetX) * 0.04;
        targetY += (mouseY - targetY) * 0.04;

        ringGroup.rotation.y += 0.008;
        ringGroup.rotation.x = targetY * 0.4;
        ringGroup.position.y = -0.4 + Math.sin(frame * 0.02) * 0.06;

        accentGroup.rotation.y = frame * 0.0015;
        accentGroup.children.forEach((c, i) => { c.position.y += Math.sin(frame * 0.02 + i) * 0.0009; });

        stars.material.opacity = 0.6 + Math.sin(frame * 0.02) * 0.2;

        camera.position.x += (targetX * 1.2 - camera.position.x) * 0.05;
        camera.position.y += (-targetY * 0.8 - camera.position.y) * 0.05;
        camera.lookAt(0, 0, 0);

        if (heroContent) {
            heroContent.style.transform = `translate(${targetX * -10}px, ${targetY * -8}px)`;
        }

        renderer.render(scene, camera);
    }
    animate();

    window.addEventListener('resize', function () {
        width = heroEl.clientWidth;
        height = heroEl.clientHeight;
        renderer.setSize(width, height);
        camera.aspect = width / height;
        camera.updateProjectionMatrix();
    });
})();

// ================= Ambient rose petals — lightweight full-page 2D canvas =================
(function initPetals() {
    const canvas = document.getElementById('petal-canvas');
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (!canvas || prefersReducedMotion) return;
    const ctx = canvas.getContext('2d');
    let w, h;
    function resize() { w = canvas.width = window.innerWidth; h = canvas.height = window.innerHeight; }
    resize();
    window.addEventListener('resize', resize);

    const isSmall = window.innerWidth < 700;
    const colors = ['#b5473a', '#c9a05a', '#e3a880', '#f4d6c4'];
    const count = isSmall ? 10 : 18;
    const petals = [];
    for (let i = 0; i < count; i++) {
        petals.push({
            x: Math.random() * w,
            y: Math.random() * h,
            size: 6 + Math.random() * 6,
            speedY: 0.35 + Math.random() * 0.5,
            driftAmp: 18 + Math.random() * 24,
            phase: Math.random() * Math.PI * 2,
            driftSpeed: 0.4 + Math.random() * 0.5,
            rotation: Math.random() * Math.PI * 2,
            rotSpeed: (Math.random() - 0.5) * 0.02,
            color: colors[Math.floor(Math.random() * colors.length)]
        });
    }

    let running = true;
    document.addEventListener('visibilitychange', () => { running = !document.hidden; });

    function draw(p) {
        ctx.save();
        ctx.translate(p.x, p.y);
        ctx.rotate(p.rotation);
        ctx.fillStyle = p.color;
        ctx.globalAlpha = 0.8;
        ctx.beginPath();
        ctx.moveTo(0, -p.size);
        ctx.quadraticCurveTo(p.size, 0, 0, p.size);
        ctx.quadraticCurveTo(-p.size, 0, 0, -p.size);
        ctx.fill();
        ctx.restore();
    }

    function loop() {
        requestAnimationFrame(loop);
        if (!running) return;
        ctx.clearRect(0, 0, w, h);
        petals.forEach(p => {
            p.phase += p.driftSpeed * 0.02;
            p.rotation += p.rotSpeed;
            p.x += Math.sin(p.phase) * (p.driftAmp * 0.01);
            p.y += p.speedY;
            if (p.y > h + 20) { p.y = -20; p.x = Math.random() * w; }
            draw(p);
        });
    }
    loop();
})();

// ================= Golden cursor sparkle trail =================
(function initTrail() {
    const canvas = document.getElementById('trail-canvas');
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (!canvas || prefersReducedMotion) return;
    const ctx = canvas.getContext('2d');
    let w, h;
    function resize() { w = canvas.width = window.innerWidth; h = canvas.height = window.innerHeight; }
    resize();
    window.addEventListener('resize', resize);

    const isTouch = 'ontouchstart' in window;
    let sparkles = [];
    let lastEmit = 0;

    function emit(x, y) {
        sparkles.push({ x, y, r: 1 + Math.random() * 2, life: 1, vy: -0.3 - Math.random() * 0.3, vx: (Math.random() - 0.5) * 0.6 });
        if (sparkles.length > 80) sparkles.shift();
    }

    if (!isTouch) {
        window.addEventListener('mousemove', (e) => {
            const now = performance.now();
            if (now - lastEmit > 30) { emit(e.clientX, e.clientY); lastEmit = now; }
        });
    }

    let running = true;
    document.addEventListener('visibilitychange', () => { running = !document.hidden; });

    function loop() {
        requestAnimationFrame(loop);
        if (!running) return;
        ctx.clearRect(0, 0, w, h);
        sparkles.forEach(s => {
            s.life -= 0.025;
            s.x += s.vx;
            s.y += s.vy;
        });
        sparkles = sparkles.filter(s => s.life > 0);
        sparkles.forEach(s => {
            ctx.save();
            ctx.globalAlpha = Math.max(s.life, 0);
            ctx.fillStyle = '#e8d5a3';
            ctx.beginPath();
            ctx.arc(s.x, s.y, s.r, 0, Math.PI * 2);
            ctx.fill();
            ctx.restore();
        });
    }
    loop();
})();

// ================= Elegant magnetic tilt for cards =================
(function initTilt() {
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (prefersReducedMotion || 'ontouchstart' in window) return;
    const cards = document.querySelectorAll('.event-card, .rsvp-card');
    cards.forEach(card => {
        card.addEventListener('mousemove', (e) => {
            const rect = card.getBoundingClientRect();
            const px = (e.clientX - rect.left) / rect.width - 0.5;
            const py = (e.clientY - rect.top) / rect.height - 0.5;
            card.style.transform = `perspective(900px) rotateX(${(-py * 5).toFixed(2)}deg) rotateY(${(px * 6).toFixed(2)}deg) translateY(-2px)`;
        });
        card.addEventListener('mouseleave', () => {
            card.style.transform = '';
        });
    });
})();

// ================= Cinematic lightbox open transition =================
(function() {
    if (typeof window.openLightbox !== 'function' || typeof anime === 'undefined') return;
    const origOpen = window.openLightbox;
    window.openLightbox = function(src) {
        origOpen(src);
        const img = document.getElementById('lightbox-img');
        anime({ targets: img, opacity: [0, 1], scale: [0.9, 1], easing: 'easeOutCubic', duration: 450 });
    };
})();
</script>
</body>
</html>