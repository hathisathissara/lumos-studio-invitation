<?php
require_once __DIR__ . '/includes/lang.php';
require_once __DIR__ . '/includes/music_library.php';
set_invite_lang($wedding['invite_language'] ?? 'en');

$hero_style = "";
if (!empty($wedding['hero_image'])) {
    $img_path = htmlspecialchars($wedding['hero_image']);
    $hero_style = "style=\"background: linear-gradient(180deg, rgba(250,245,236,0.55) 0%, rgba(250,245,236,0.92) 55%, var(--cream) 100%), url('{$img_path}') center/cover no-repeat;\"";
}
// Background music (off unless the couple picked a preset in customize.php)
$music_key = $wedding['music_track'] ?? null;
$music_file = ($music_key && isset($MUSIC_LIBRARY[$music_key])) ? $MUSIC_LIBRARY[$music_key]['file'] : null;

// Detect a successful RSVP submission so we can trigger the confetti/petal celebration.
$rsvp_just_submitted = ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_rsvp']));
$rsvp_success = $rsvp_just_submitted && !empty($msg) && stripos($msg, 'danger') === false && stripos($msg, 'error') === false;
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
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,500;1,600&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js"></script>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --gold: #c1633d;
            --gold-light: #e3a880;
            --gold-dark: #8f4526;
            --couple-color: #a84f28;
            --olive: #6f7d55;
            --olive-dark: #4c5639;
            --cream: #faf5ec;
            --cream-2: #f4ece0;
            --cream-border: #e6d9c7;
            --text-dark: #362b21;
            --text-mid: #6b5c4c;
            --text-light: #93816d;
            --blush: #ecd3bd;
            --white: #ffffff;
        }

        html { scroll-behavior: smooth; }

        body {
            background: var(--cream);
            font-family: 'Cormorant Garamond', serif;
            color: var(--text-dark);
            min-height: 100vh;
        }

        /* Preview banner */
        .preview-bar {
            background: linear-gradient(135deg, var(--gold-dark), var(--couple-color));
            color: var(--cream);
            text-align: center;
            padding: 10px 20px;
            font-family: 'Jost', sans-serif;
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
            color: var(--blush);
            text-decoration: underline;
            text-underline-offset: 3px;
        }

        /* Reserved seats note */
        .reserved-note {
            margin: 0 auto 32px;
            background: rgba(193,99,61,0.08);
            border: 1px dashed rgba(193,99,61,0.4);
            padding: 10px 22px;
            border-radius: 30px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            color: var(--couple-color);
            font-family: 'Jost', sans-serif;
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

        /* ======= HERO HEADER ======= */
        .hero-header {
            background:
                radial-gradient(circle at 15% 20%, rgba(111,125,85,0.06) 0, transparent 40%),
                radial-gradient(circle at 85% 75%, rgba(193,99,61,0.06) 0, transparent 40%),
                var(--cream);
            padding: 70px 20px 140px;
            text-align: center;
            position: relative;
            overflow: hidden;
            border-bottom-left-radius: 50% 90px;
            border-bottom-right-radius: 50% 90px;
        }
        .hero-ornament-top {
            font-family: 'Cormorant Garamond', serif;
            font-size: 11rem;
            color: rgba(111,125,85,0.06);
            position: absolute;
            top: -30px;
            left: 50%;
            transform: translateX(-50%);
            pointer-events: none;
            white-space: nowrap;
        }
        .hero-content {
            position: relative;
            z-index: 1;
            text-shadow: 0 2px 14px rgba(250,245,236,0.85);
        }

        .guest-greeting-tag {
            display: inline-block;
            font-family: 'Jost', sans-serif;
            font-size: 0.7rem;
            font-weight: 500;
            letter-spacing: 2.5px;
            text-transform: uppercase;
            color: var(--olive);
            margin-bottom: 12px;
        }
        .guest-name-display {
            font-family: 'Cormorant Garamond', serif;
            font-size: clamp(1.4rem, 4vw, 2rem);
            font-weight: 500;
            font-style: italic;
            color: var(--text-dark);
            margin-bottom: 28px;
        }

        .couple-names-hero {
            font-family: 'Cormorant Garamond', serif;
            font-size: clamp(3.4rem, 10vw, 6rem);
            font-weight: 600;
            color: var(--couple-color);
            line-height: 1.15;
        }
        .couple-names-hero .amp {
            display: block;
            font-size: 0.4em;
            font-style: italic;
            font-weight: 400;
            color: var(--olive);
            margin: -6px 0;
        }

        .hero-vine-divider {
            display: flex;
            justify-content: center;
            margin: 22px 0 6px;
        }
        .hero-vine-divider svg { display: block; }

        .hero-date-area {
            margin-top: 18px;
            padding-top: 24px;
        }
        .hero-getting-married {
            font-family: 'Jost', sans-serif;
            font-size: 0.68rem;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: var(--text-light);
            margin-bottom: 8px;
        }
        .hero-date {
            font-family: 'Cormorant Garamond', serif;
            font-size: clamp(1.4rem, 4vw, 2.2rem);
            font-weight: 500;
            color: var(--text-dark);
            letter-spacing: 1px;
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
            font-family: 'Jost', sans-serif;
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
            font-family: 'Cormorant Garamond', serif;
            font-size: clamp(2.5rem, 8vw, 3.5rem);
            font-weight: 500;
            color: var(--gold);
            line-height: 1;
        }
        .time-label {
            font-family: 'Jost', sans-serif;
            font-size: 0.65rem;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: var(--text-light);
            margin-top: 4px;
        }
        .time-sep {
            font-size: 2rem;
            color: rgba(111,125,85,0.35);
            font-weight: 300;
            margin-bottom: 16px;
        }
        .just-married-msg {
            font-family: 'Cormorant Garamond', serif;
            font-size: 3rem;
            font-style: italic;
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
            color: var(--olive);
            font-size: 0.9rem;
        }
        .section-heading {
            font-family: 'Cormorant Garamond', serif;
            font-size: clamp(1.8rem, 5vw, 2.6rem);
            font-weight: 500;
            color: var(--text-dark);
            text-align: center;
            margin-bottom: 8px;
        }
        .section-heading em { font-style: italic; color: var(--gold); }
        .section-sub {
            font-family: 'Jost', sans-serif;
            text-align: center;
            color: var(--text-light);
            font-size: 0.8rem;
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
            font-family: 'Cormorant Garamond', serif;
            font-size: 5rem;
            color: rgba(193,99,61,0.14);
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
            box-shadow: 0 8px 30px rgba(193,99,61,0.12);
            transform: translateY(-2px);
        }
        .event-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0;
            width: 4px; height: 100%;
            background: linear-gradient(to bottom, var(--gold), var(--olive));
        }
        .event-name {
            font-family: 'Cormorant Garamond', serif;
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
            font-family: 'Jost', sans-serif;
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
            font-family: 'Jost', sans-serif;
            font-size: 0.8rem;
            font-weight: 600;
            transition: all 0.2s;
            box-shadow: 0 3px 12px rgba(193,99,61,0.25);
        }
        .btn-map:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(193,99,61,0.35);
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
            font-family: 'Jost', sans-serif;
            font-size: 0.8rem;
            font-weight: 500;
            transition: all 0.2s;
            cursor: pointer;
        }
        .btn-cal:hover {
            border-color: var(--gold);
            color: var(--gold);
            background: rgba(193,99,61,0.04);
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
            box-shadow: 0 10px 30px rgba(54,43,33,0.1);
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
            font-family: 'Jost', sans-serif;
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
            background: linear-gradient(to bottom, transparent 50%, rgba(54,43,33,0.22));
            opacity: 0;
            transition: opacity 0.3s;
        }
        .gallery-item:hover::after { opacity: 1; }

        /* Lightbox */
        .lightbox {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(28,22,17,0.92);
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
            box-shadow: 0 8px 40px rgba(54,43,33,0.06);
        }
        .rsvp-title {
            font-family: 'Cormorant Garamond', serif;
            font-size: 2.1rem;
            font-weight: 500;
            color: var(--text-dark);
            margin-bottom: 6px;
        }
        .rsvp-subtitle {
            font-family: 'Jost', sans-serif;
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
            font-family: 'Jost', sans-serif;
            font-size: 0.82rem;
            font-weight: 500;
            color: var(--text-mid);
            transition: all 0.2s;
        }
        .rsvp-option label i { font-size: 1.4rem; }
        .rsvp-option input[type="radio"]:checked + label {
            border-color: var(--gold);
            background: rgba(193,99,61,0.06);
            color: var(--gold);
        }
        .rsvp-option:first-child input[type="radio"]:checked + label {
            border-color: var(--olive);
            background: rgba(111,125,85,0.08);
            color: var(--olive-dark);
        }
        .rsvp-option:last-child input[type="radio"]:checked + label {
            border-color: #b5473a;
            background: rgba(181,71,58,0.06);
            color: #96382d;
        }

        .rsvp-note {
            width: 100%;
            background: var(--cream-2);
            border: 1px solid var(--cream-border);
            border-radius: 14px;
            padding: 14px 16px;
            font-family: 'Jost', sans-serif;
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
            background: linear-gradient(135deg, var(--gold-dark), var(--olive-dark));
            color: var(--cream);
            border: none;
            border-radius: 16px;
            padding: 16px;
            font-family: 'Jost', sans-serif;
            font-size: 0.9rem;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            cursor: pointer;
            transition: all 0.3s;
        }
        .btn-rsvp-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(143,69,38,0.3);
        }

        /* ======= FOOTER ======= */
        .inv-footer {
            text-align: center;
            padding: 40px 20px;
            font-family: 'Jost', sans-serif;
            font-size: 0.75rem;
            color: var(--text-light);
            border-top: 1px solid var(--cream-border);
        }
        .inv-footer .brand {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.4rem;
            font-style: italic;
            color: var(--gold);
            display: block;
            margin-bottom: 6px;
        }

        @media (max-width: 500px) {
            .rsvp-card { padding: 30px 20px; }
            .rsvp-options { grid-template-columns: 1fr 1fr; }
        }
        /* ======= SWEET MOMENTS SLIDESHOW (couple's gallery only) ======= */
        .sweet-slideshow {
            position: relative;
            max-width: 640px;
            margin: 0 auto;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 50px rgba(0,0,0,0.14);
            background: #000;
        }
        .sweet-slideshow-track {
            display: flex;
            transition: transform 0.5s ease;
        }
.sweet-slide {
    flex: 0 0 100%;
    aspect-ratio: 4/3;
    cursor: pointer;
}
.sweet-slide img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}
.sweet-slide-nav {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background: rgba(255,255,255,0.85);
    color: var(--gold-dark);
    border: none;
    width: 42px;
    height: 42px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: 0 4px 14px rgba(0,0,0,0.25);
    transition: all 0.2s;
    z-index: 2;
}
.sweet-slide-nav:hover { background: var(--gold); color: #fff; }
.sweet-slide-nav.prev { left: 14px; }
.sweet-slide-nav.next { right: 14px; }
.sweet-slide-dots {
    position: absolute;
    bottom: 14px;
    left: 0;
    right: 0;
    display: flex;
    justify-content: center;
    gap: 8px;
    z-index: 2;
}
.sweet-slide-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: rgba(255,255,255,0.6);
    cursor: pointer;
    transition: all 0.25s;
}
.sweet-slide-dot.active { background: var(--gold); width: 22px; border-radius: 5px; }
@media (max-width: 560px) {
    .sweet-slide-nav { width: 34px; height: 34px; font-size: 0.8rem; }
}

/* terracotta_bloom unique overrides – Warm Organic */
.hero-header { background: linear-gradient(180deg, #3d1e14 0%, #5a2e1e 40%, var(--cream) 100%); }
.hero-header::before { background: radial-gradient(ellipse 120% 80% at 50% 30%, rgba(193,99,61,0.15), transparent); }
.hero-ornament-top { font-size: 16rem !important; color: rgba(193,99,61,0.05) !important; }
.couple-names-hero { text-shadow: 2px 4px 20px rgba(60,20,10,0.6) !important; }
.countdown-section { background: linear-gradient(135deg, #5a2e1e, #3d1e14) !important; border: none !important; }
.countdown-label { color: rgba(227,168,128,0.8) !important; }
.time-value { color: #e3a880 !important; font-weight: 600 !important; }
.time-label { color: rgba(227,168,128,0.6) !important; }
.time-sep { color: rgba(227,168,128,0.3) !important; }
.section-divider-icon { width: 50px; height: 50px; background: rgba(193,99,61,0.1); border-radius: 50%; display: flex !important; align-items: center; justify-content: center; }
.event-card { background: linear-gradient(135deg, #fff, var(--cream-2)) !important; border-radius: 24px !important; border: none !important; box-shadow: 0 4px 20px rgba(143,69,38,0.08) !important; }
.event-card::before { background: linear-gradient(to bottom, var(--gold), var(--gold-dark), transparent) !important; width: 3px !important; border-radius: 3px; }
.btn-map { background: linear-gradient(135deg, #8f4526, #5a2e1e) !important; border-radius: 12px !important; }
.gallery-item { border-radius: 50% !important; border: 4px solid var(--cream-border); }
.gallery-item img { border-radius: 50% !important; }
.rsvp-card { background: linear-gradient(160deg, #fff, var(--cream-2)) !important; border-radius: 32px !important; border: 2px solid var(--cream-border) !important; }
.rsvp-option label { border-radius: 50px !important; }
.btn-rsvp-submit { background: linear-gradient(135deg, #8f4526, #5a2e1e) !important; color: #e3a880 !important; border-radius: 50px !important; }
.inv-footer { background: linear-gradient(135deg, #3d1e14, #5a2e1e); color: rgba(227,168,128,0.5); }
.inv-footer .brand { color: #e3a880; }

/* ======================================================================
   PREMIUM 3D EXPERIENCE LAYER
   ====================================================================== */

/* --- Hero 3D canvas (arch, rings, warm particles) --- */
.hero-header { isolation: isolate; }
#hero3d-canvas {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    z-index: 0;
    pointer-events: none;
    opacity: 0;
    transition: opacity 1.4s ease;
}
#hero3d-canvas.ready { opacity: 1; }
.hero-content { z-index: 2; }

/* --- Global ambient floating layer (petals / leaves / butterflies / dust) --- */
#ambient-fx-canvas {
    position: fixed;
    inset: 0;
    width: 100vw;
    height: 100vh;
    z-index: 5;
    pointer-events: none;
}

/* --- RSVP celebration burst --- */
#celebration-canvas {
    position: fixed;
    inset: 0;
    width: 100vw;
    height: 100vh;
    z-index: 999;
    pointer-events: none;
    display: none;
}
#celebration-canvas.active { display: block; }

/* --- Floating control buttons (day/night + music) --- */
.fx-controls {
    position: fixed;
    right: 16px;
    bottom: 20px;
    z-index: 60;
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.fx-btn {
    width: 46px;
    height: 46px;
    border-radius: 50%;
    border: 1px solid var(--cream-border);
    background: rgba(255,255,255,0.9);
    color: var(--gold-dark);
    box-shadow: 0 6px 20px rgba(54,43,33,0.18);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.05rem;
    cursor: pointer;
    backdrop-filter: blur(6px);
    transition: transform 0.25s, background 0.25s, color 0.25s;
}
.fx-btn:hover { transform: translateY(-2px) scale(1.06); }
.fx-btn.playing { color: var(--olive-dark); }

/* --- Day / Night ambient body states --- */
body.night-mode {
    background: #1c130d;
    color: #ecdcc9;
}
body.night-mode .hero-header {
    background: linear-gradient(180deg, #140b06 0%, #2c1710 45%, #1c130d 100%) !important;
}
body.night-mode .countdown-section { background: linear-gradient(135deg, #2c1710, #140b06) !important; }
body.night-mode .invitation-body,
body.night-mode .rsvp-section,
body.night-mode .countdown-section,
body.night-mode .inv-footer { color: #ecdcc9; }
body.night-mode .event-card,
body.night-mode .love-story-text,
body.night-mode .rsvp-card {
    background: linear-gradient(160deg, #2a1c13, #1c130d) !important;
    color: #ecdcc9;
    border-color: rgba(227,168,128,0.15) !important;
}
body.night-mode .section-heading,
body.night-mode .event-name,
body.night-mode .rsvp-title { color: #f3e2cc; }
body.night-mode .fx-btn { background: rgba(30,20,14,0.85); color: #e3a880; border-color: rgba(227,168,128,0.25); }
body.night-mode::after {
    content: '';
    position: fixed;
    inset: 0;
    background:
        radial-gradient(2px 2px at 10% 20%, rgba(255,255,255,0.6), transparent 60%),
        radial-gradient(1.5px 1.5px at 30% 55%, rgba(255,255,255,0.45), transparent 60%),
        radial-gradient(1.5px 1.5px at 70% 15%, rgba(255,255,255,0.5), transparent 60%),
        radial-gradient(2px 2px at 85% 40%, rgba(255,255,255,0.4), transparent 60%),
        radial-gradient(1.5px 1.5px at 55% 80%, rgba(255,255,255,0.35), transparent 60%),
        radial-gradient(1.5px 1.5px at 15% 90%, rgba(255,255,255,0.35), transparent 60%);
    pointer-events: none;
    z-index: 1;
    opacity: 0.9;
}

/* --- Scroll reveal for sections --- */
.reveal-fx {
    opacity: 0;
    transform: translateY(28px);
    transition: opacity 0.9s cubic-bezier(.2,.7,.3,1), transform 0.9s cubic-bezier(.2,.7,.3,1);
}
.reveal-fx.in-view { opacity: 1; transform: translateY(0); }
.event-timeline .event-card.reveal-fx:nth-child(even) { transform: translateY(28px) translateX(0); }

/* --- Cinematic lightbox transition --- */
.lightbox img { transform: scale(0.92); opacity: 0; transition: transform 0.4s ease, opacity 0.4s ease; }
.lightbox.open img { transform: scale(1); opacity: 1; }

@media (max-width: 500px) {
    .fx-controls { right: 10px; bottom: 16px; }
    .fx-btn { width: 40px; height: 40px; font-size: 0.95rem; }
}
</style>
</head>
<body>

<canvas id="ambient-fx-canvas" aria-hidden="true"></canvas>
<canvas id="celebration-canvas" aria-hidden="true"></canvas>

<div class="fx-controls">
    <button type="button" class="fx-btn" id="daynight-toggle" title="Toggle day / night ambience" aria-label="Toggle day and night ambience">
        <i class="fas fa-moon"></i>
    </button>
    <?php if ($music_file): ?>
    <button type="button" class="fx-btn" id="music-toggle" title="Play background music" aria-label="Play background music">
        <i class="fas fa-music"></i>
    </button>
    <audio id="bg-music" loop preload="none" src="<?php echo htmlspecialchars($music_file); ?>"></audio>
    <?php endif; ?>
</div>

<?php if ($guest_id == 0): ?>
<div class="preview-bar">
    <i class="fas fa-eye"></i>
    <strong><?php echo t('preview_mode_label'); ?></strong> — <?php echo t('preview_mode_note'); ?>
    <a href="dashboard/index.php"><?php echo t('back_to_dashboard'); ?></a>
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
    $hero_style = "style=\"background: linear-gradient(180deg, rgba(250,245,236,0.55) 0%, rgba(250,245,236,0.92) 55%, var(--cream) 100%), url('{$img_path}') center/cover no-repeat;\"";
}
?>
<div class="hero-header position-relative overflow-hidden text-center shadow-lg border-bottom border-danger border-3" <?php echo $hero_style; ?>>
    <canvas id="hero3d-canvas" aria-hidden="true"></canvas>
    <div class="hero-ornament-top">❧</div>
    <div class="hero-content">
        <span class="guest-greeting-tag"><?php echo t('hero_eyebrow'); ?></span>
        <div class="guest-name-display">
            <?php echo t('hero_dear'); ?> <?php echo htmlspecialchars($guest_name); ?>,
        </div>

        <div class="couple-names-hero">
            <?php echo htmlspecialchars($wedding['bride_name']); ?>
            <span class="amp">&</span>
            <?php echo htmlspecialchars($wedding['groom_name']); ?>
        </div>

        <div class="hero-vine-divider" aria-hidden="true">
            <svg width="140" height="22" viewBox="0 0 140 22" xmlns="http://www.w3.org/2000/svg">
                <path d="M2 11 Q35 1 70 11 T138 11" stroke="var(--olive)" stroke-width="1.2" fill="none"/>
                <circle cx="70" cy="11" r="3" fill="var(--gold)"/>
                <path d="M56 8 Q61 1 69 5" stroke="var(--olive)" stroke-width="1" fill="none"/>
                <path d="M84 8 Q79 1 71 5" stroke="var(--olive)" stroke-width="1" fill="none"/>
            </svg>
        </div>

        <div class="hero-date-area">
            <p class="hero-getting-married"><?php echo t('hero_getting_married'); ?></p>
            <p class="hero-date"><?php echo t_date($wedding['wedding_date']); ?></p>
            <?php if(!empty($wedding['venue'])): ?>
            <p class="hero-venue mt-3" style="font-family:'Inter',sans-serif; font-size:1.1rem; color:var(--gold);"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($wedding['venue']); ?></p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- COUNTDOWN TIMER -->
<div class="countdown-section">
    <p class="countdown-label"><?php echo t('countdown_label'); ?></p>
    <div class="countdown" id="countdown">
        <div class="time-unit">
            <span class="time-value" id="cd-days">00</span>
            <span class="time-label"><?php echo t('cd_days'); ?></span>
        </div>
        <span class="time-sep">:</span>
        <div class="time-unit">
            <span class="time-value" id="cd-hours">00</span>
            <span class="time-label"><?php echo t('cd_hours'); ?></span>
        </div>
        <span class="time-sep">:</span>
        <div class="time-unit">
            <span class="time-value" id="cd-mins">00</span>
            <span class="time-label"><?php echo t('cd_mins'); ?></span>
        </div>
        <span class="time-sep">:</span>
        <div class="time-unit">
            <span class="time-value" id="cd-secs">00</span>
            <span class="time-label"><?php echo t('cd_secs'); ?></span>
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
    <h2 class="section-heading"><?php echo t('love_story_title'); ?></h2>
    <p class="section-sub"><?php echo t('love_story_tag'); ?></p>
    <div class="love-story-text reveal-fx">
        <?php echo nl2br(htmlspecialchars($wedding['love_story'])); ?>
    </div>
    <?php endif; ?>

    <!-- EVENTS / PROGRAMME -->
    <div class="section-divider">
        <div class="section-divider-line"></div>
        <div class="section-divider-icon"><i class="fas fa-calendar-alt"></i></div>
        <div class="section-divider-line right"></div>
    </div>
    <h2 class="section-heading"><?php echo t('programme_title'); ?></h2>
    <p class="section-sub"><?php echo t('programme_tag'); ?></p>

    <?php if (count($wedding_events) > 0): ?>
        <div class="event-timeline row row-cols-1 row-cols-md-2 g-5 align-items-center">
            <?php foreach ($wedding_events as $ev):
                $ev_start = date('Ymd\THis', strtotime($ev['event_date_time']));
                $ev_end = date('Ymd\THis', strtotime($ev['event_date_time']) + 7200);
                $ev_title = urlencode($ev['event_name'] . ' — ' . $wedding['bride_name'] . ' & ' . $wedding['groom_name']);
                $ev_loc = urlencode($ev['location_name']);
                $ev_gcal = "https://calendar.google.com/calendar/render?action=TEMPLATE&text={$ev_title}&dates={$ev_start}/{$ev_end}&location={$ev_loc}";
                $ev_ics = "calendar.php?wedding_id={$wedding_id}&event_id={$ev['id']}";
                $ev_outlook = "https://outlook.live.com/calendar/0/deeplink/compose?path=/calendar/action/compose&rru=addevent&subject=" . $ev_title . "&startdt=" . urlencode(date('c', strtotime($ev['event_date_time']))) . "&enddt=" . urlencode(date('c', strtotime($ev['event_date_time']) + 7200)) . "&location=" . $ev_loc;
            ?>
            <div class="event-card col text-end shadow-sm rounded-4 reveal-fx">
                <div class="event-name"><?php echo htmlspecialchars($ev['event_name']); ?></div>
                <div class="event-meta">
                    <div class="event-meta-item">
                        <i class="far fa-calendar"></i>
                        <span><?php echo t_date($ev['event_date_time']); ?></span>
                    </div>
                    <div class="event-meta-item">
                        <i class="far fa-clock"></i>
                        <span><?php echo t_time($ev['event_date_time']); ?></span>
                    </div>
                    <div class="event-meta-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <span><?php echo htmlspecialchars($ev['location_name']); ?></span>
                    </div>
                </div>
                <div class="event-actions">
                    <?php if (!empty($ev['google_map_link'])): ?>
                    <a href="<?php echo htmlspecialchars($ev['google_map_link']); ?>" target="_blank" class="btn-map" rel="noopener">
                        <i class="fas fa-directions"></i> <?php echo t('get_directions'); ?>
                    </a>
                    <?php endif; ?>

                    <div class="cal-dropdown">
                        <button class="btn-cal" onclick="toggleCal(this)">
                            <i class="fas fa-calendar-plus"></i> <?php echo t('add_to_calendar'); ?>
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
        <p style="text-align:center; color:var(--text-light); font-style:italic; padding:30px 0;"><?php echo t('event_details_soon'); ?></p>
    <?php endif; ?>

    <!-- GALLERY -->
    <?php if (count($gallery_images) > 0): ?>
    <div class="section-divider">
        <div class="section-divider-line"></div>
        <div class="section-divider-icon"><i class="fas fa-camera"></i></div>
        <div class="section-divider-line right"></div>
    </div>
    <h2 class="section-heading"><?php echo t('gallery_title'); ?></h2>
    <p class="section-sub"><?php echo t('gallery_tag'); ?></p>

    <div class="sweet-slideshow reveal-fx" id="sweet-slideshow">
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
    <h2 class="section-heading"><?php echo t('guest_gallery_title'); ?></h2>
    <p class="section-sub">Capture and share your beautiful memories with us!</p>

    <!-- Upload Box (Preview mode එකේදී අක්‍රීය වේ) -->
    <div style="background: var(--cream-2); border: 2px dashed var(--gold); border-radius: 20px; padding: 30px 20px; text-align: center; margin-bottom: 30px;">
        <?php if ($guest_id == 0): ?>
            <p class="text-muted small"><i class="fas fa-lock"></i> <?php echo t('upload_disabled_preview'); ?></p>
        <?php else: ?>
            <i class="fas fa-camera-retro" style="font-size: 2.2rem; color: var(--gold); margin-bottom: 12px; display: block;"></i>
            <h5 class="fw-bold" style="font-family:'Inter', sans-serif; font-size: 0.95rem; color: #1a1a2e; margin-bottom: 6px;"><?php echo t('share_photo_heading'); ?></h5>
            <p class="text-muted" style="font-size: 0.8rem; margin-bottom: 15px;"><?php echo t('share_photo_desc'); ?></p>
            
            <input type="file" id="guest-image-input" accept="image/*" style="display: none;">
            <button type="button" class="btn-map" onclick="document.getElementById('guest-image-input').click()" style="border: none; cursor: pointer;">
                <i class="fas fa-cloud-upload-alt"></i> <?php echo t('upload_wedding_photo_btn'); ?>
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

    <div class="rsvp-card text-center shadow rounded-5 p-4 reveal-fx">
        <h2 class="rsvp-title"><?php echo t('rsvp_title'); ?></h2>
        <p class="rsvp-subtitle"><?php echo t('rsvp_subtitle'); ?></p>
            <?php if (isset($current_guest['seats_reserved']) && $current_guest['seats_reserved'] > 0): ?>
    <div class="reserved-note" style="margin-bottom: 20px;">
        <i class="fas fa-chair"></i>
        <span><?php echo t('seats_reserved'); ?>: <strong><?php echo intval($current_guest['seats_reserved']); ?></strong></span>
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
                        <i class="fas fa-heart" style="color:#6f7d55;"></i>
                        <?php echo t('rsvp_accept'); ?>
                    </label>
                </div>
                <div class="rsvp-option">
                    <input type="radio" name="rsvp_status" id="rsvp-no" value="rejected"
                        <?php if ($current_guest['rsvp_status'] == 'rejected') echo 'checked'; ?>>
                    <label for="rsvp-no">
                        <i class="fas fa-heart-broken" style="color:#b5473a;"></i>
                        <?php echo t('rsvp_decline'); ?>
                    </label>
                </div>
            </div>

            <textarea
                name="guest_note"
                class="rsvp-note"
                rows="3"
                placeholder="<?php echo htmlspecialchars(t('rsvp_note_placeholder')); ?>"
            ><?php echo !empty($current_guest['guest_note']) ? htmlspecialchars($current_guest['guest_note']) : ''; ?></textarea>

            <button type="submit" name="submit_rsvp" class="btn-rsvp-submit"><?php echo t('rsvp_submit'); ?></button>
        </form>
    </div>
</div>

<!-- FOOTER -->
<div class="inv-footer">
    <span class="brand">Lumos Studio</span><br>
    Digital Wedding Invitations · Designed by Hathisa Thissara
</div>

<script>
// Countdown
const weddingDate = new Date("<?php echo $wedding['wedding_date']; ?> 00:00:00").getTime();
function tick() {
    const now = Date.now();
    const dist = weddingDate - now;
    if (dist < 0) {
        document.getElementById('countdown').innerHTML = '<p class="just-married-msg"><?php echo t('just_married'); ?></p>';
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

// Sweet Moments Slideshow
(function() {
    const track = document.getElementById('sweet-slideshow-track');
    if (!track) return;
    const slides = track.children;
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
    function updateSlide() {
        track.style.transform = `translateX(-${idx * 100}%)`;
        if (dotsWrap) {
            Array.from(dotsWrap.children).forEach((d, i) => d.classList.toggle('active', i === idx));
        }
    }
    function goToSlide(i) { idx = (i + total) % total; updateSlide(); }
    window.sweetSlideMove = function(dir) { goToSlide(idx + dir); };

    let autoTimer;
    function startAuto() { if (total > 1) autoTimer = setInterval(() => goToSlide(idx + 1), 4000); }
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

    startAuto();
})();

// Cinematic lightbox open/close using GSAP if available
(function() {
    const lb = document.getElementById('lightbox');
    const lbImg = document.getElementById('lightbox-img');
    if (!lb || !window.gsap) return;
    const origOpen = window.openLightbox;
    window.openLightbox = function(src) {
        origOpen(src);
        gsap.fromTo(lbImg, { scale: 0.9, opacity: 0 }, { scale: 1, opacity: 1, duration: 0.45, ease: 'power2.out' });
    };
})();
</script>

<script>
/* ======================================================================
   PREMIUM 3D EXPERIENCE — hero scene, ambient particles, reveal,
   day/night ambience, music toggle, RSVP celebration.
   Everything below is procedurally generated (no external model files).
   ====================================================================== */
document.addEventListener('DOMContentLoaded', function () {

    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const isSmallScreen = window.innerWidth < 700;

    /* ---------------------------------------------------------------
       1) HERO 3D SCENE — procedural floral arch + wedding rings +
          warm golden dust, built entirely from primitive geometry.
       --------------------------------------------------------------- */
    function initHero3D() {
        const canvas = document.getElementById('hero3d-canvas');
        const heroEl = document.querySelector('.hero-header');
        if (!canvas || !heroEl || typeof THREE === 'undefined' || prefersReducedMotion) return;

        let width = heroEl.clientWidth, height = heroEl.clientHeight;

        const renderer = new THREE.WebGLRenderer({ canvas, alpha: true, antialias: true });
        renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 2));
        renderer.setSize(width, height);

        const scene = new THREE.Scene();
        const camera = new THREE.PerspectiveCamera(45, width / height, 0.1, 100);
        camera.position.set(0, 0.4, 9);

        // Warm lighting rig
        const ambient = new THREE.AmbientLight(0xffdecb, 0.65);
        scene.add(ambient);
        const warmLight = new THREE.PointLight(0xffb27a, 1.6, 30);
        warmLight.position.set(-3, 3, 4);
        scene.add(warmLight);
        const coolFill = new THREE.PointLight(0xffe9d6, 0.5, 30);
        coolFill.position.set(4, -2, 3);
        scene.add(coolFill);

        // Soft round glow texture used for dust / petal sprites
        function makeGlowTexture(hex) {
            const c = document.createElement('canvas');
            c.width = c.height = 64;
            const ctx = c.getContext('2d');
            const g = ctx.createRadialGradient(32, 32, 0, 32, 32, 32);
            g.addColorStop(0, hex + 'ff');
            g.addColorStop(0.4, hex + 'aa');
            g.addColorStop(1, hex + '00');
            ctx.fillStyle = g;
            ctx.fillRect(0, 0, 64, 64);
            return new THREE.CanvasTexture(c);
        }
        const goldGlow = makeGlowTexture('#e3a880');
        const oliveGlow = makeGlowTexture('#a9b98a');

        // --- Procedural terracotta floral arch: a torus "arch" frame
        //     dressed with small flower/leaf clusters around it.
        const archGroup = new THREE.Group();
        const archGeo = new THREE.TorusGeometry(3.4, 0.09, 12, 60, Math.PI);
        const archMat = new THREE.MeshStandardMaterial({ color: 0x8f4526, roughness: 0.55, metalness: 0.15 });
        const arch = new THREE.Mesh(archGeo, archMat);
        arch.rotation.z = Math.PI;
        arch.position.y = -1.6;
        archGroup.add(arch);

        // Flower clusters (small spheres) + leaves (flattened spheres) along the arch
        const flowerColors = [0xc1633d, 0xe3a880, 0xecd3bd, 0x6f7d55];
        for (let i = 0; i <= 22; i++) {
            const t = i / 22;
            const ang = Math.PI * (1 - t);
            const r = 3.4 + (Math.random() - 0.5) * 0.5;
            const x = Math.cos(ang) * r;
            const y = Math.sin(ang) * r - 1.6;
            if (Math.random() > 0.45) {
                const fGeo = new THREE.SphereGeometry(0.13 + Math.random() * 0.09, 8, 8);
                const fMat = new THREE.MeshStandardMaterial({
                    color: flowerColors[Math.floor(Math.random() * flowerColors.length)],
                    roughness: 0.6
                });
                const flower = new THREE.Mesh(fGeo, fMat);
                flower.position.set(x + (Math.random() - 0.5) * 0.3, y + (Math.random() - 0.5) * 0.3, (Math.random() - 0.5) * 0.4);
                archGroup.add(flower);
            }
            if (Math.random() > 0.55) {
                const lGeo = new THREE.SphereGeometry(0.16, 6, 6);
                lGeo.scale(1, 0.35, 0.6);
                const lMat = new THREE.MeshStandardMaterial({ color: 0x6f7d55, roughness: 0.7 });
                const leaf = new THREE.Mesh(lGeo, lMat);
                leaf.position.set(x + (Math.random() - 0.5) * 0.4, y + (Math.random() - 0.5) * 0.4, (Math.random() - 0.5) * 0.4);
                leaf.rotation.z = Math.random() * Math.PI;
                archGroup.add(leaf);
            }
        }
        archGroup.position.set(0, 1.1, -1.5);
        archGroup.scale.setScalar(isSmallScreen ? 0.72 : 0.92);
        scene.add(archGroup);

        // --- Animated wedding rings: two interlocking tori with a soft metal material
        const ringGroup = new THREE.Group();
        const ringMat = new THREE.MeshStandardMaterial({ color: 0xe3c199, metalness: 0.85, roughness: 0.25 });
        const ringGeo = new THREE.TorusGeometry(0.55, 0.07, 24, 64);
        const ringA = new THREE.Mesh(ringGeo, ringMat);
        const ringB = new THREE.Mesh(ringGeo, ringMat);
        ringA.position.set(-0.32, 0, 0);
        ringB.position.set(0.32, 0, 0);
        ringA.rotation.y = 0.3;
        ringB.rotation.y = -0.3;
        ringGroup.add(ringA, ringB);
        ringGroup.position.set(0, -1.55, 2.2);
        ringGroup.scale.setScalar(isSmallScreen ? 0.8 : 1);
        scene.add(ringGroup);

        // --- Golden dust particles
        const dustCount = isSmallScreen ? 45 : 90;
        const dustGeo = new THREE.BufferGeometry();
        const dustPos = new Float32Array(dustCount * 3);
        for (let i = 0; i < dustCount; i++) {
            dustPos[i * 3] = (Math.random() - 0.5) * 12;
            dustPos[i * 3 + 1] = (Math.random() - 0.5) * 8;
            dustPos[i * 3 + 2] = (Math.random() - 0.5) * 8;
        }
        dustGeo.setAttribute('position', new THREE.BufferAttribute(dustPos, 3));
        const dustMat = new THREE.PointsMaterial({
            size: 0.16, map: goldGlow, transparent: true, opacity: 0.85,
            depthWrite: false, blending: THREE.AdditiveBlending
        });
        const dust = new THREE.Points(dustGeo, dustMat);
        scene.add(dust);

        canvas.classList.add('ready');

        let frame = 0;
        function animate() {
            frame += 1;
            requestAnimationFrame(animate);
            archGroup.rotation.y = Math.sin(frame * 0.002) * 0.12;
            ringGroup.rotation.y += 0.006;
            ringGroup.position.y = -1.55 + Math.sin(frame * 0.02) * 0.08;
            const positions = dust.geometry.attributes.position.array;
            for (let i = 0; i < dustCount; i++) {
                positions[i * 3 + 1] += 0.004;
                if (positions[i * 3 + 1] > 4) positions[i * 3 + 1] = -4;
            }
            dust.geometry.attributes.position.needsUpdate = true;
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

        // Expose so the day/night toggle can retint the warm light
        window.__heroWarmLight = warmLight;
    }

    /* ---------------------------------------------------------------
       2) GLOBAL AMBIENT LAYER — floating dried petals, leaves and
          gently flapping butterflies, drawn on a lightweight 2D canvas
          so it stays smooth on every device.
       --------------------------------------------------------------- */
    function initAmbientFX() {
        const canvas = document.getElementById('ambient-fx-canvas');
        if (!canvas || prefersReducedMotion) return;
        const ctx = canvas.getContext('2d');
        let w, h;
        function resize() {
            w = canvas.width = window.innerWidth;
            h = canvas.height = window.innerHeight;
        }
        resize();
        window.addEventListener('resize', resize);

        const petalColors = ['#c1633d', '#e3a880', '#ecd3bd'];
        const leafColor = '#6f7d55';

        function makeParticle(kind) {
            return {
                kind,
                x: Math.random() * w,
                y: -20 - Math.random() * h,
                size: kind === 'butterfly' ? 10 + Math.random() * 6 : 6 + Math.random() * 6,
                speedY: kind === 'butterfly' ? 0.6 + Math.random() * 0.4 : 0.4 + Math.random() * 0.6,
                driftAmp: 20 + Math.random() * 30,
                driftSpeed: 0.4 + Math.random() * 0.6,
                phase: Math.random() * Math.PI * 2,
                rotation: Math.random() * Math.PI * 2,
                rotSpeed: (Math.random() - 0.5) * 0.02,
                color: kind === 'leaf' ? leafColor : petalColors[Math.floor(Math.random() * petalColors.length)],
                flap: 0
            };
        }

        const petalCount = isSmallScreen ? 10 : 20;
        const leafCount = isSmallScreen ? 5 : 10;
        const butterflyCount = isSmallScreen ? 2 : 5;
        const particles = [];
        for (let i = 0; i < petalCount; i++) particles.push(makeParticle('petal'));
        for (let i = 0; i < leafCount; i++) particles.push(makeParticle('leaf'));
        for (let i = 0; i < butterflyCount; i++) particles.push(makeParticle('butterfly'));
        particles.forEach(p => { p.y = Math.random() * h; });

        function drawPetal(p) {
            ctx.save();
            ctx.translate(p.x, p.y);
            ctx.rotate(p.rotation);
            ctx.fillStyle = p.color;
            ctx.globalAlpha = 0.85;
            ctx.beginPath();
            ctx.ellipse(0, 0, p.size, p.size * 0.55, 0, 0, Math.PI * 2);
            ctx.fill();
            ctx.restore();
        }
        function drawLeaf(p) {
            ctx.save();
            ctx.translate(p.x, p.y);
            ctx.rotate(p.rotation);
            ctx.fillStyle = p.color;
            ctx.globalAlpha = 0.8;
            ctx.beginPath();
            ctx.ellipse(0, 0, p.size * 0.5, p.size, 0, 0, Math.PI * 2);
            ctx.fill();
            ctx.restore();
        }
        function drawButterfly(p) {
            ctx.save();
            ctx.translate(p.x, p.y);
            ctx.rotate(Math.sin(p.phase) * 0.15);
            const flap = Math.sin(p.flap) * 0.9;
            ctx.fillStyle = p.color;
            ctx.globalAlpha = 0.9;
            ctx.save();
            ctx.scale(flap, 1);
            ctx.beginPath();
            ctx.ellipse(-p.size * 0.5, 0, p.size * 0.6, p.size * 0.4, 0, 0, Math.PI * 2);
            ctx.fill();
            ctx.restore();
            ctx.save();
            ctx.scale(-flap, 1);
            ctx.beginPath();
            ctx.ellipse(-p.size * 0.5, 0, p.size * 0.6, p.size * 0.4, 0, 0, Math.PI * 2);
            ctx.fill();
            ctx.restore();
            ctx.fillStyle = '#4c5639';
            ctx.fillRect(-1, -p.size * 0.4, 2, p.size * 0.8);
            ctx.restore();
        }

        let running = true;
        document.addEventListener('visibilitychange', function () { running = !document.hidden; });

        function loop() {
            requestAnimationFrame(loop);
            if (!running) return;
            ctx.clearRect(0, 0, w, h);
            particles.forEach(p => {
                p.phase += p.driftSpeed * 0.02;
                p.flap += 0.15;
                p.rotation += p.rotSpeed;
                p.x += Math.sin(p.phase) * (p.driftAmp * 0.01);
                p.y += p.speedY;
                if (p.y > h + 20) {
                    p.y = -20;
                    p.x = Math.random() * w;
                }
                if (p.kind === 'petal') drawPetal(p);
                else if (p.kind === 'leaf') drawLeaf(p);
                else drawButterfly(p);
            });
        }
        loop();
    }

    /* ---------------------------------------------------------------
       3) ORGANIC SCROLL REVEAL for love story / events / gallery / RSVP
       --------------------------------------------------------------- */
    function initReveal() {
        const items = document.querySelectorAll('.reveal-fx');
        if (!items.length) return;
        if (!('IntersectionObserver' in window)) {
            items.forEach(el => el.classList.add('in-view'));
            return;
        }
        const obs = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('in-view');
                    obs.unobserve(entry.target);
                }
            });
        }, { threshold: 0.15, rootMargin: '0px 0px -60px 0px' });
        items.forEach(el => obs.observe(el));

        // Subtle organic parallax on scroll for the hero ornament, if GSAP+ScrollTrigger loaded
        if (window.gsap && window.ScrollTrigger && !prefersReducedMotion) {
            gsap.registerPlugin(ScrollTrigger);
            const ornament = document.querySelector('.hero-ornament-top');
            if (ornament) {
                gsap.to(ornament, {
                    y: 80,
                    ease: 'none',
                    scrollTrigger: { trigger: '.hero-header', start: 'top top', end: 'bottom top', scrub: true }
                });
            }
        }
    }

    /* ---------------------------------------------------------------
       4) DAY / NIGHT AMBIENT TOGGLE
       --------------------------------------------------------------- */
    function initDayNight() {
        const btn = document.getElementById('daynight-toggle');
        if (!btn) return;
        btn.addEventListener('click', function () {
            const night = document.body.classList.toggle('night-mode');
            btn.innerHTML = night ? '<i class="fas fa-sun"></i>' : '<i class="fas fa-moon"></i>';
            if (window.__heroWarmLight) {
                window.__heroWarmLight.intensity = night ? 0.9 : 1.6;
                window.__heroWarmLight.color.set(night ? 0x8a6a52 : 0xffb27a);
            }
        });
    }

    /* ---------------------------------------------------------------
       5) OPTIONAL BACKGROUND MUSIC TOGGLE
       --------------------------------------------------------------- */
    (function () {
    const audio = document.getElementById('bg-music');
    const btn = document.getElementById('music-toggle');
    let playing = false;

    function setPlayingUI(isPlaying) {
        playing = isPlaying;
        btn.innerHTML = isPlaying ? '<i class="fas fa-volume-up"></i>' : '<i class="fas fa-music"></i>';
    }

    function startPlayback() {
        if (playing) return;
        audio.play().then(() => setPlayingUI(true)).catch(() => {});
    }

    // 1) Try to autoplay as soon as the page loads.
    startPlayback();

    // 2) If the browser blocked it, start on the very first user interaction anywhere on the page.
    ['click', 'touchstart', 'scroll', 'keydown'].forEach(evt => {
        document.addEventListener(evt, function onceHandler() {
            if (!playing) startPlayback();
        }, { once: true, passive: true });
    });

    // 3) The button still works as a manual toggle.
    btn.addEventListener('click', function () {
        if (playing) {
            audio.pause();
            setPlayingUI(false);
        } else {
            audio.play().catch(() => {});
            setPlayingUI(true);
        }
    });
})();

    /* ---------------------------------------------------------------
       6) RSVP CELEBRATION — confetti + petal burst after submission
       --------------------------------------------------------------- */
    function celebrate() {
        const canvas = document.getElementById('celebration-canvas');
        if (!canvas) return;
        const ctx = canvas.getContext('2d');
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;
        canvas.classList.add('active');

        const colors = ['#c1633d', '#e3a880', '#ecd3bd', '#6f7d55', '#f4ece0'];
        const pieces = [];
        const count = isSmallScreen ? 60 : 110;
        for (let i = 0; i < count; i++) {
            pieces.push({
                x: canvas.width / 2 + (Math.random() - 0.5) * 200,
                y: canvas.height * 0.35 + (Math.random() - 0.5) * 60,
                vx: (Math.random() - 0.5) * 9,
                vy: -6 - Math.random() * 6,
                size: 5 + Math.random() * 6,
                color: colors[Math.floor(Math.random() * colors.length)],
                rotation: Math.random() * Math.PI * 2,
                rotSpeed: (Math.random() - 0.5) * 0.3,
                shape: Math.random() > 0.5 ? 'petal' : 'square'
            });
        }
        let t = 0;
        const gravity = 0.22;
        function frame() {
            t += 1;
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            let alive = false;
            pieces.forEach(p => {
                p.vy += gravity;
                p.x += p.vx;
                p.y += p.vy;
                p.rotation += p.rotSpeed;
                if (p.y < canvas.height + 40) alive = true;
                ctx.save();
                ctx.translate(p.x, p.y);
                ctx.rotate(p.rotation);
                ctx.fillStyle = p.color;
                ctx.globalAlpha = Math.max(0, 1 - t / 220);
                if (p.shape === 'petal') {
                    ctx.beginPath();
                    ctx.ellipse(0, 0, p.size, p.size * 0.6, 0, 0, Math.PI * 2);
                    ctx.fill();
                } else {
                    ctx.fillRect(-p.size / 2, -p.size / 2, p.size, p.size);
                }
                ctx.restore();
            });
            if (alive && t < 260) {
                requestAnimationFrame(frame);
            } else {
                canvas.classList.remove('active');
                ctx.clearRect(0, 0, canvas.width, canvas.height);
            }
        }
        frame();
    }

    // Init everything
    initHero3D();
    initAmbientFX();
    initReveal();
    initDayNight();
    initMusic();

    <?php if ($rsvp_success): ?>
    setTimeout(celebrate, 300);
    <?php endif; ?>
});
</script>
</body>
</html>