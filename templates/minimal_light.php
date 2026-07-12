<?php
$hero_style = "";
if (!empty($wedding['hero_image'])) {
    $img_path = htmlspecialchars($wedding['hero_image']);
    $hero_style = "style=\"background: linear-gradient(180deg, rgba(255,255,255,0.7) 0%, rgba(255,255,255,0.95) 50%, var(--cream) 100%), url('{$img_path}') center/cover no-repeat;\"";
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
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;1,400&family=Montserrat:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
                :root {
            --gold: #8ba888;
            --gold-light: #d6e2d4;
            --gold-dark: #5c755a;
            --couple-color: #dcb365;
            --cream: #ffffff;
            --cream-2: #f8f9fa;
            --cream-border: #e9ecef;
            --text-dark: #333333;
            --text-mid: #555555;
            --text-light: #777777;
            --pink: #ff8fa3;
            --white: #ffffff;
        }

        html { scroll-behavior: smooth; }

        body {
            background: var(--cream);
            font-family: 'Playfair Display', serif;
            color: var(--text-dark);
            min-height: 100vh;
        }

        /* Preview banner */
        .preview-bar {
            background: linear-gradient(135deg, #1a1a2e, #242440);
            color: #c9a96e;
            text-align: center;
            padding: 10px 20px;
            font-family: 'Montserrat', sans-serif;
            font-size: 0.8rem;
            letter-spacing: 1px;
            position: sticky;
            top: 0;
            z-index: 100;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }
        .preview-bar a {
            color: #e8d5a3;
            text-decoration: underline;
            text-underline-offset: 3px;
        }

        /* Flash messages */
            .reserved-note {
            margin: 20px auto;
            background: rgba(183,138,68,0.12);
            border: 1px dashed rgba(183,138,68,0.45);
            padding: 12px 22px;
            border-radius: 30px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            color: var(--couple-color);
            font-family: 'Inter', sans-serif;
            font-size: 0.95rem;
            font-weight: 600;
            max-width: 100%;
            text-align: left;
        }
        .reserved-note i {
            color: var(--couple-color);
            width: 18px;
            font-size: 1rem;
        }
        /* ======= HERO HEADER ======= */
        .hero-header { background: #ffffff;
            padding: 70px 20px 80px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .hero-header::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(ellipse 80% 60% at 50% 40%, rgba(201,169,110,0.1), transparent);
        }
        .hero-ornament-top {
            font-family: 'Playfair Display', serif;
            font-size: 12rem;
            color: rgba(201,169,110,0.04);
            position: absolute;
            top: -20px;
            left: 50%;
            transform: translateX(-50%);
            pointer-events: none;
            white-space: nowrap;
        }
        .hero-content {
            position: relative;
            z-index: 1;
            text-shadow: 1px 2px 8px rgba(0,0,0,0.6), 0 1px 3px rgba(0,0,0,0.8);
        }

        .guest-greeting-tag {
            display: inline-block;
            font-family: 'Montserrat', sans-serif;
            font-size: 0.7rem;
            font-weight: 500;
            letter-spacing: 2.5px;
            text-transform: uppercase;
            color: rgba(220,185,125,0.9);
            margin-bottom: 12px;
        }
        .guest-name-display {
            font-family: 'Playfair Display', serif;
            font-size: clamp(1.4rem, 4vw, 2rem);
            font-weight: 400;
            font-style: italic;
            color: rgba(245,245,240,0.95);
            margin-bottom: 32px;
        }

        .reserved-note {
            margin: 0 auto 32px;
            background: rgba(220,185,125,0.1);
            border: 1px dashed rgba(220,185,125,0.45);
            padding: 10px 22px;
            border-radius: 30px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            color: var(--couple-color);
            font-family: 'Montserrat', sans-serif;
            font-size: 0.9rem;
            font-weight: 600;
            max-width: 100%;
            text-align: left;
        }
        .reserved-note i {
            color: var(--couple-color);
            width: 18px;
            font-size: 1rem;
        }

        .couple-names-hero {
            font-family: 'Playfair Display', serif;
            font-size: clamp(3.5rem, 10vw, 6rem);
            color: var(--couple-color);
            line-height: 1.2;
            text-shadow: 2px 3px 10px rgba(0,0,0,0.5), 0 2px 4px rgba(0,0,0,0.7);
        }
        .couple-names-hero .amp {
            display: block;
            font-size: 0.45em;
            color: rgba(220,185,125,0.6);
            margin: -8px 0;
            text-shadow: none;
        }

        .hero-date-area {
            margin-top: 28px;
            padding-top: 28px;
            border-top: 1px solid rgba(201,169,110,0.15);
        }
        .hero-getting-married {
            font-family: 'Montserrat', sans-serif;
            font-size: 0.68rem;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: rgba(245,245,240,0.75);
            margin-bottom: 8px;
        }
        .hero-date {
            font-family: 'Playfair Display', serif;
            font-size: clamp(1.4rem, 4vw, 2.2rem);
            font-weight: 400;
            color: rgba(245,245,240,0.95);
            letter-spacing: 2px;
        }

        /* ======= COUNTDOWN ======= */
        .countdown-section {
            background: var(--cream-2);
            border-top: 1px solid var(--cream-border);
            border-bottom: 1px solid var(--cream-border);
            padding: 40px 20px;
            text-align: center;
        }
        .countdown-label {
            font-family: 'Montserrat', sans-serif;
            font-size: 0.7rem;
            letter-spacing: 2.5px;
            text-transform: uppercase;
            color: var(--text-light);
            margin-bottom: 20px;
        }
        .countdown {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
        }
        .time-unit {
            text-align: center;
            min-width: 70px;
        }
        .time-value {
            display: block;
            font-family: 'Playfair Display', serif;
            font-size: clamp(2.5rem, 8vw, 3.5rem);
            font-weight: 300;
            color: var(--gold);
            line-height: 1;
        }
        .time-label {
            font-family: 'Montserrat', sans-serif;
            font-size: 0.65rem;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: var(--text-light);
            margin-top: 4px;
        }
        .time-sep {
            font-size: 2rem;
            color: rgba(183,138,68,0.3);
            font-weight: 300;
            margin-bottom: 16px;
        }
        .just-married-msg {
            font-family: 'Playfair Display', serif;
            font-size: 3rem;
            color: var(--gold);
        }

        /* ======= CONTENT SECTIONS ======= */
        .invitation-body {
            max-width: 680px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .section-divider {
            display: flex;
            align-items: center;
            gap: 16px;
            margin: 50px 0 36px;
        }
        .section-divider-line {
            flex: 1;
            height: 1px;
            background: linear-gradient(to right, transparent, var(--cream-border));
        }
        .section-divider-line.right {
            background: linear-gradient(to left, transparent, var(--cream-border));
        }
        .section-divider-icon {
            color: var(--gold);
            font-size: 0.9rem;
        }
        .section-heading {
            font-family: 'Playfair Display', serif;
            font-size: clamp(1.8rem, 5vw, 2.6rem);
            font-weight: 400;
            color: var(--text-dark);
            text-align: center;
            margin-bottom: 8px;
        }
        .section-heading em { font-style: italic; color: var(--gold); }
        .section-sub {
            font-family: 'Montserrat', sans-serif;
            text-align: center;
            color: var(--text-light);
            font-size: 0.82rem;
            letter-spacing: 1px;
            margin-bottom: 30px;
            text-transform: uppercase;
        }

        /* ======= LOVE STORY ======= */
        .love-story-text {
            font-size: 1.15rem;
            line-height: 2;
            color: var(--text-mid);
            font-style: italic;
            text-align: center;
            padding: 30px 24px;
            background: var(--cream-2);
            border-radius: 20px;
            border: 1px solid var(--cream-border);
            position: relative;
        }
        .love-story-text::before {
            content: '\201C';
            font-family: 'Playfair Display', serif;
            font-size: 5rem;
            color: rgba(183,138,68,0.15);
            position: absolute;
            top: -10px;
            left: 20px;
            line-height: 1;
        }

        /* ======= EVENTS ======= */
        .event-timeline {
            position: relative;
            padding-left: 0;
        }
        .event-card {
            background: var(--white);
            border: 1px solid var(--cream-border);
            border-radius: 20px;
            padding: 28px 28px 24px;
            margin-bottom: 20px;
            position: relative;
            overflow: hidden;
            transition: box-shadow 0.3s, transform 0.3s;
        }
        .event-card:hover {
            box-shadow: 0 8px 30px rgba(183,138,68,0.1);
            transform: translateY(-2px);
        }
        .event-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0;
            width: 4px; height: 100%;
            background: linear-gradient(to bottom, var(--gold), rgba(183,138,68,0.2));
        }
        .event-name {
            font-family: 'Playfair Display', serif;
            font-size: 1.6rem;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 12px;
        }
        .event-meta {
            display: flex;
            flex-direction: column;
            gap: 6px;
            margin-bottom: 16px;
        }
        .event-meta-item {
            display: flex;
            align-items: center;
            gap: 10px;
            font-family: 'Montserrat', sans-serif;
            font-size: 0.88rem;
            color: var(--text-mid);
        }
        .event-meta-item i {
            color: var(--gold);
            width: 16px;
            text-align: center;
        }

        .event-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 16px;
        }
        .btn-map {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: linear-gradient(135deg, var(--gold), var(--gold-dark));
            color: white;
            text-decoration: none;
            padding: 9px 18px;
            border-radius: 50px;
            font-family: 'Montserrat', sans-serif;
            font-size: 0.8rem;
            font-weight: 600;
            transition: all 0.2s;
            box-shadow: 0 3px 12px rgba(183,138,68,0.25);
        }
        .btn-map:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(183,138,68,0.35);
            color: white;
        }
        .btn-cal {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: transparent;
            border: 1px solid var(--cream-border);
            color: var(--text-mid);
            text-decoration: none;
            padding: 9px 18px;
            border-radius: 50px;
            font-family: 'Montserrat', sans-serif;
            font-size: 0.8rem;
            font-weight: 500;
            transition: all 0.2s;
            cursor: pointer;
        }
        .btn-cal:hover {
            border-color: var(--gold);
            color: var(--gold);
            background: rgba(183,138,68,0.04);
        }

        /* Calendar dropdown */
        .cal-dropdown {
            position: relative;
            display: inline-block;
        }
        .cal-menu {
            display: none;
            position: absolute;
            bottom: calc(100% + 8px);
            left: 0;
            background: white;
            border: 1px solid var(--cream-border);
            border-radius: 14px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            min-width: 180px;
            z-index: 10;
            overflow: hidden;
        }
        .cal-menu.open { display: block; }
        .cal-menu a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            font-family: 'Montserrat', sans-serif;
            font-size: 0.84rem;
            color: var(--text-mid);
            text-decoration: none;
            transition: background 0.15s;
        }
        .cal-menu a:hover { background: var(--cream-2); color: var(--gold); }
        .cal-menu a i { width: 16px; text-align: center; }

        /* ======= GALLERY ======= */
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            gap: 12px;
        }
        .gallery-item {
            border-radius: 14px;
            overflow: hidden;
            aspect-ratio: 1;
            cursor: pointer;
            position: relative;
        }
        .gallery-item img {
            width: 100%; height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        .gallery-item:hover img { transform: scale(1.06); }
        .gallery-item::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(to bottom, transparent 50%, rgba(0,0,0,0.2));
            opacity: 0;
            transition: opacity 0.3s;
        }
        .gallery-item:hover::after { opacity: 1; }

        /* Lightbox */
        .lightbox {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.92);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        .lightbox.open { display: flex; }
        .lightbox img {
            max-width: 90vw;
            max-height: 90vh;
            border-radius: 16px;
            object-fit: contain;
        }
        .lightbox-close {
            position: absolute;
            top: 20px; right: 20px;
            color: white;
            font-size: 1.8rem;
            cursor: pointer;
            opacity: 0.7;
            transition: opacity 0.2s;
        }
        .lightbox-close:hover { opacity: 1; }

        /* ======= RSVP ======= */
        .rsvp-section {
            background: var(--cream-2);
            border-top: 1px solid var(--cream-border);
            padding: 60px 20px;
            text-align: center;
        }
        .rsvp-card {
            max-width: 500px;
            margin: 0 auto;
            background: white;
            border: 1px solid var(--cream-border);
            border-radius: 24px;
            padding: 40px 36px;
            box-shadow: 0 8px 40px rgba(0,0,0,0.05);
        }
        .rsvp-title {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            font-weight: 400;
            color: var(--text-dark);
            margin-bottom: 6px;
        }
        .rsvp-subtitle {
            font-family: 'Montserrat', sans-serif;
            font-size: 0.8rem;
            color: var(--text-light);
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 28px;
        }

        .rsvp-options {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 20px;
        }
        .rsvp-option input[type="radio"] { display: none; }
        .rsvp-option label {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            padding: 18px 12px;
            border: 2px solid var(--cream-border);
            border-radius: 16px;
            cursor: pointer;
            font-family: 'Montserrat', sans-serif;
            font-size: 0.82rem;
            font-weight: 500;
            color: var(--text-mid);
            transition: all 0.2s;
        }
        .rsvp-option label i { font-size: 1.4rem; }
        .rsvp-option input[type="radio"]:checked + label {
            border-color: var(--gold);
            background: rgba(183,138,68,0.05);
            color: var(--gold);
        }
        .rsvp-option:first-child input[type="radio"]:checked + label {
            border-color: #22c55e;
            background: rgba(34,197,94,0.05);
            color: #16a34a;
        }
        .rsvp-option:last-child input[type="radio"]:checked + label {
            border-color: #ef4444;
            background: rgba(239,68,68,0.05);
            color: #dc2626;
        }

        .rsvp-note {
            width: 100%;
            background: var(--cream-2);
            border: 1px solid var(--cream-border);
            border-radius: 14px;
            padding: 14px 16px;
            font-family: 'Montserrat', sans-serif;
            font-size: 0.88rem;
            color: var(--text-mid);
            resize: none;
            outline: none;
            transition: border-color 0.2s;
            margin-bottom: 16px;
        }
        .rsvp-note:focus { border-color: var(--gold); }

        .btn-rsvp-submit {
            width: 100%;
            background: linear-gradient(135deg, #1a1a2e, #2d2d50);
            color: #c9a96e;
            border: none;
            border-radius: 16px;
            padding: 16px;
            font-family: 'Montserrat', sans-serif;
            font-size: 0.9rem;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            cursor: pointer;
            transition: all 0.3s;
        }
        .btn-rsvp-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(26,26,46,0.35);
        }

        /* ======= FOOTER ======= */
        .inv-footer {
            text-align: center;
            padding: 40px 20px;
            font-family: 'Montserrat', sans-serif;
            font-size: 0.75rem;
            color: var(--text-light);
            border-top: 1px solid var(--cream-border);
        }
        .inv-footer .brand {
            font-family: 'Playfair Display', serif;
            font-size: 1.4rem;
            color: var(--gold);
            display: block;
            margin-bottom: 6px;
        }

        @media (max-width: 500px) {
            .rsvp-card { padding: 30px 20px; }
            .rsvp-options { grid-template-columns: 1fr 1fr; }
        }
    </style>

<style>
/* Slideshow-like animation for gallery items */
@keyframes slideShowAnim {
    0% { opacity: 0; transform: scale(0.95) translateY(20px); }
    10% { opacity: 1; transform: scale(1) translateY(0); }
    90% { opacity: 1; transform: scale(1) translateY(0); }
    100% { opacity: 0; transform: scale(1.05) translateY(-20px); }
}
.gallery-grid {
    display: flex;
    flex-wrap: nowrap;
    overflow-x: auto;
    gap: 15px;
    padding: 10px 0;
    scroll-behavior: smooth;
    -webkit-overflow-scrolling: touch;
    animation: scrollGallery 20s linear infinite;
}
.gallery-grid:hover {
    animation-play-state: paused;
}
@keyframes scrollGallery {
    0% { transform: translateX(0); }
    100% { transform: translateX(-50%); }
}
.gallery-item {
    flex: 0 0 auto;
    width: 280px;
    height: 280px;
}
</style>

<style>
/* minimal_light unique overrides – Swiss Minimalism */
.hero-header { background: linear-gradient(180deg, #f8f9fa 0%, #ffffff 100%) !important; border-bottom: none; }
.hero-header::before { display: none; }
.hero-ornament-top { display: none; }
.hero-content { text-shadow: none !important; }
.guest-greeting-tag { color: var(--gold-dark) !important; border: 1px solid var(--gold); padding: 6px 18px; border-radius: 50px; }
.guest-name-display { color: var(--text-dark) !important; }
.couple-names-hero { font-family: 'Cormorant Garamond', serif !important; font-weight: 300; color: var(--text-dark) !important; text-shadow: none !important; font-size: clamp(3rem, 8vw, 5rem) !important; }
.couple-names-hero .amp { color: var(--gold) !important; }
.hero-getting-married { color: var(--text-light) !important; }
.hero-date { color: var(--text-dark) !important; }
.countdown-section { background: transparent; border: none; padding: 50px 20px; }
.time-value { font-weight: 600; font-size: clamp(3rem, 9vw, 4.5rem) !important; color: var(--text-dark) !important; }
.time-label { color: var(--gold) !important; font-weight: 600; }
.time-sep { display: none; }
.time-unit { background: #f8f9fa; padding: 20px 16px 14px; border-radius: 16px; min-width: 90px; }
.section-divider { display: none; }
.section-heading { text-align: left; font-size: clamp(2rem, 5vw, 3rem) !important; font-weight: 300 !important; letter-spacing: 1px; }
.section-heading em { font-style: normal !important; }
.section-sub { text-align: left; }
.love-story-text { background: transparent !important; border: none !important; border-left: 3px solid var(--gold) !important; border-radius: 0 !important; padding: 20px 30px !important; text-align: left !important; }
.love-story-text::before { display: none; }
.event-card { border-radius: 0 !important; border: none !important; border-bottom: 1px solid #e8ecf0 !important; padding: 24px 0 !important; box-shadow: none !important; }
.event-card::before { display: none; }
.event-card:hover { transform: none !important; box-shadow: none !important; }
.event-name { font-weight: 300 !important; font-size: 1.8rem !important; letter-spacing: 1px; }
.btn-map { border-radius: 4px !important; background: var(--text-dark) !important; box-shadow: none !important; }
.btn-cal { border-radius: 4px !important; }
.gallery-item { border-radius: 0 !important; }
.rsvp-section { background: #f8f9fa !important; }
.rsvp-card { border-radius: 0 !important; box-shadow: none !important; border: 1px solid #e8ecf0 !important; }
.rsvp-option label { border-radius: 4px !important; }
.btn-rsvp-submit { border-radius: 4px !important; letter-spacing: 4px; background: var(--text-dark) !important; color: white !important; }
.inv-footer .brand { font-family: 'Inter', sans-serif !important; font-size: 1rem !important; font-weight: 700; letter-spacing: 3px; text-transform: uppercase; }
</style>
</head>
<body>

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
<?php
$hero_style = "";
if (!empty($wedding['hero_image'])) {
    $img_path = htmlspecialchars($wedding['hero_image']);
    $hero_style = "style=\"background: linear-gradient(180deg, rgba(26,26,46,0.2) 0%, rgba(36,36,64,0.4) 50%, var(--cream) 100%), url('{$img_path}') center/cover no-repeat;\"";
}
?>
<div class="hero-header position-relative overflow-hidden text-start ps-5 shadow-sm rounded-0 border-bottom border-secondary border-2" <?php echo $hero_style; ?>>
    <div class="hero-ornament-top">♡</div>
    <div class="hero-content">
        <span class="guest-greeting-tag">You're Warmly Invited</span>
        <div class="guest-name-display">
            Dear <?php echo htmlspecialchars($guest_name); ?>,
        </div>

        <div class="couple-names-hero">
            <?php echo htmlspecialchars($wedding['bride_name']); ?>
            <span class="amp">&</span>
            <?php echo htmlspecialchars($wedding['groom_name']); ?>
        </div>

        <div class="hero-date-area">
            <p class="hero-getting-married">We are getting married on</p>
            <p class="hero-date"><?php echo date("l, d F Y", strtotime($wedding['wedding_date'])); ?></p>
            <?php if(!empty($wedding['venue'])): ?>
            <p class="hero-venue mt-3" style="font-family:'Inter',sans-serif; font-size:1.1rem; color:var(--gold);"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($wedding['venue']); ?></p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- COUNTDOWN TIMER -->
<div class="countdown-section">
    <p class="countdown-label">Counting down to the big day</p>
    <div class="countdown" id="countdown">
        <div class="time-unit">
            <span class="time-value" id="cd-days">00</span>
            <span class="time-label">Days</span>
        </div>
        <span class="time-sep">:</span>
        <div class="time-unit">
            <span class="time-value" id="cd-hours">00</span>
            <span class="time-label">Hours</span>
        </div>
        <span class="time-sep">:</span>
        <div class="time-unit">
            <span class="time-value" id="cd-mins">00</span>
            <span class="time-label">Minutes</span>
        </div>
        <span class="time-sep">:</span>
        <div class="time-unit">
            <span class="time-value" id="cd-secs">00</span>
            <span class="time-label">Seconds</span>
        </div>
    </div>
</div>

<div class="invitation-body container py-4">

    <!-- LOVE STORY -->
    <?php if (!empty($wedding['love_story'])): ?>
    <div class="section-divider">
        <div class="section-divider-line"></div>
        <div class="section-divider-icon"><i class="fas fa-heart"></i></div>
        <div class="section-divider-line right"></div>
    </div>
    <h2 class="section-heading">Our <em>Love Story</em></h2>
    <p class="section-sub">How it all began</p>
    <div class="love-story-text">
        <?php echo nl2br(htmlspecialchars($wedding['love_story'])); ?>
    </div>
    <?php endif; ?>

    <!-- EVENTS / PROGRAMME -->
    <div class="section-divider">
        <div class="section-divider-line"></div>
        <div class="section-divider-icon"><i class="fas fa-calendar-alt"></i></div>
        <div class="section-divider-line right"></div>
    </div>
    <h2 class="section-heading"><em>Wedding</em> Programme</h2>
    <p class="section-sub">Join us for these celebrations</p>

    <?php if (count($wedding_events) > 0): ?>
        <div class="event-timeline row row-cols-1 g-3 justify-content-start">
            <?php foreach ($wedding_events as $ev):
                $ev_start = date('Ymd\THis', strtotime($ev['event_date_time']));
                $ev_end = date('Ymd\THis', strtotime($ev['event_date_time']) + 7200);
                $ev_title = urlencode($ev['event_name'] . ' — ' . $wedding['bride_name'] . ' & ' . $wedding['groom_name']);
                $ev_loc = urlencode($ev['location_name']);
                $ev_gcal = "https://calendar.google.com/calendar/render?action=TEMPLATE&text={$ev_title}&dates={$ev_start}/{$ev_end}&location={$ev_loc}";
                $ev_ics = "calendar.php?wedding_id={$wedding_id}&event_id={$ev['id']}";
                $ev_outlook = "https://outlook.live.com/calendar/0/deeplink/compose?path=/calendar/action/compose&rru=addevent&subject=" . $ev_title . "&startdt=" . urlencode(date('c', strtotime($ev['event_date_time']))) . "&enddt=" . urlencode(date('c', strtotime($ev['event_date_time']) + 7200)) . "&location=" . $ev_loc;
            ?>
            <div class="event-card col text-start shadow-none border-bottom border-2 rounded-0">
                <div class="event-name"><?php echo htmlspecialchars($ev['event_name']); ?></div>
                <div class="event-meta">
                    <div class="event-meta-item">
                        <i class="far fa-calendar"></i>
                        <span><?php echo date("l, d F Y", strtotime($ev['event_date_time'])); ?></span>
                    </div>
                    <div class="event-meta-item">
                        <i class="far fa-clock"></i>
                        <span><?php echo date("h:i A", strtotime($ev['event_date_time'])); ?></span>
                    </div>
                    <div class="event-meta-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <span><?php echo htmlspecialchars($ev['location_name']); ?></span>
                    </div>
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
                            <a href="<?php echo $ev_gcal; ?>" target="_blank" rel="noopener">
                                <i class="fab fa-google" style="color:#4285f4;"></i> Google Calendar
                            </a>
                            <a href="<?php echo $ev_ics; ?>" download>
                                <i class="fab fa-apple" style="color:#555;"></i> Apple Calendar
                            </a>
                            <a href="<?php echo $ev_outlook; ?>" target="_blank" rel="noopener">
                                <i class="fas fa-envelope" style="color:#0072c6;"></i> Outlook
                            </a>
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
    <div class="section-divider">
        <div class="section-divider-line"></div>
        <div class="section-divider-icon"><i class="fas fa-camera"></i></div>
        <div class="section-divider-line right"></div>
    </div>
    <h2 class="section-heading"><em>Sweet</em> Moments</h2>
    <p class="section-sub">Our engagement memories</p>

    <div class="gallery-grid" id="gallery-grid">
        <?php foreach ($gallery_images as $img): ?>
        <div class="gallery-item" onclick="openLightbox('<?php echo htmlspecialchars($img['image_path']); ?>')">
            <img src="<?php echo htmlspecialchars($img['image_path']); ?>" alt="Our moment" loading="lazy">
        </div>
        <?php endforeach; ?>
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
    <div class="section-divider">
        <div class="section-divider-line"></div>
        <div class="section-divider-icon"><i class="fas fa-images"></i></div>
        <div class="section-divider-line right"></div>
    </div>
    <h2 class="section-heading"><em>Guest</em> Shared Moments</h2>
    <p class="section-sub">Capture and share your beautiful memories with us!</p>

    <!-- Upload Box (Preview mode එකේදී අක්‍රීය වේ) -->
    <div style="background: var(--cream-2); border: 2px dashed var(--gold); border-radius: 20px; padding: 30px 20px; text-align: center; margin-bottom: 30px;">
        <?php if ($guest_id == 0): ?>
            <p class="text-muted small"><i class="fas fa-lock"></i> Photo upload is disabled in Preview Mode.</p>
        <?php else: ?>
            <i class="fas fa-camera-retro" style="font-size: 2.2rem; color: var(--gold); margin-bottom: 12px; display: block;"></i>
            <h5 class="fw-bold" style="font-family:'Inter', sans-serif; font-size: 0.95rem; color: #1a1a2e; margin-bottom: 6px;">Share a Photo from Your Phone</h5>
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
            <div class="gallery-item" onclick="openLightbox('<?php echo htmlspecialchars($g_img['image_path']); ?>')" style="border-color: #22c55e;">
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
                        location.reload(); // Reload කර පින්තූරය පෙන්වීම
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
    <div class="section-divider" style="max-width:680px; margin:0 auto 20px;">
        <div class="section-divider-line"></div>
        <div class="section-divider-icon"><i class="fas fa-reply"></i></div>
        <div class="section-divider-line right"></div>
    </div>

    <div class="rsvp-card text-start border-start border-5 rounded-0 p-4">
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
</script>
</body>
</html>