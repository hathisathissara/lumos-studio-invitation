<?php
$hero_style = "";
if (!empty($wedding['hero_image'])) {
    $img_path = htmlspecialchars($wedding['hero_image']);
    $hero_style = "style=\"background: linear-gradient(180deg, rgba(255,255,255,0.55) 0%, rgba(255,255,255,0.9) 60%, transparent 100%), url('{$img_path}') center/cover no-repeat;\"";
}
// Detect a successful RSVP submission so we can trigger the celebration animation.
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
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,400&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js"></script>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --ink: #111111;
            --ink-mid: #4a4a4a;
            --ink-light: #8f8f8f;
            --line: #e6e6e3;
            --gold: #b8935a;
            --gold-dark: #8f6f42;
            --gold-light: #ded0b8;
            --cream: #faf9f6;
            --white: #ffffff;
        }

        html { scroll-behavior: smooth; }

        body {
            background: var(--cream);
            font-family: 'Inter', sans-serif;
            color: var(--ink);
            min-height: 100vh;
            overflow-x: hidden;
            position: relative;
        }

        ::selection { background: var(--gold-light); color: var(--ink); }
        .reveal { opacity: 0; }

        /* Persistent fixed background animation — visible behind every section */
        #page-canvas {
            position: fixed;
            inset: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            pointer-events: none;
        }
        body > * { position: relative; z-index: 1; }
        #page-canvas { z-index: 0; }

        /* Preview banner */
        .preview-bar {
            background: var(--ink);
            color: var(--gold-light);
            text-align: center;
            padding: 10px 20px;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
            position: sticky;
            top: 0;
            z-index: 200;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }
        .preview-bar a { color: var(--gold); text-decoration: underline; text-underline-offset: 3px; }

        /* ======= HERO ======= */
        .hero-header {
            min-height: 92vh;
            display: flex;
            align-items: center;
            padding: 90px 8vw 70px;
            background: rgba(255,255,255,0.35);
            backdrop-filter: blur(2px);
            -webkit-backdrop-filter: blur(2px);
        }
        .hero-content { max-width: 640px; }
        .guest-greeting-tag {
            display: inline-block;
            font-size: 0.68rem;
            font-weight: 600;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: var(--gold-dark);
            border: 1px solid var(--gold);
            padding: 7px 18px;
            border-radius: 40px;
            margin-bottom: 22px;
        }
        .guest-name-display {
            font-family: 'Cormorant Garamond', serif;
            font-style: italic;
            font-weight: 500;
            font-size: clamp(1.2rem, 2.4vw, 1.5rem);
            color: var(--ink-mid);
            margin-bottom: 20px;
        }
        .couple-names-hero {
            font-family: 'Cormorant Garamond', serif;
            font-weight: 300;
            font-size: clamp(3.4rem, 8vw, 5.6rem);
            line-height: 1.05;
            color: var(--ink);
        }
        .couple-names-hero .amp { color: var(--gold); font-style: italic; }
        .hero-date-area {
            margin-top: 34px;
            padding-top: 26px;
            border-top: 1px solid var(--line);
        }
        .hero-getting-married {
            font-size: 0.66rem;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: var(--ink-light);
            margin-bottom: 8px;
        }
        .hero-date {
            font-family: 'Cormorant Garamond', serif;
            font-size: clamp(1.3rem, 3vw, 1.7rem);
            font-weight: 500;
            color: var(--ink);
            letter-spacing: 1px;
        }
        .hero-venue {
            margin-top: 14px;
            font-size: 0.92rem;
            color: var(--gold-dark);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .reserved-note {
            margin: 0 0 24px;
            background: rgba(184,147,90,0.08);
            border: 1px dashed rgba(184,147,90,0.5);
            padding: 10px 20px;
            border-radius: 30px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            color: var(--gold-dark);
            font-size: 0.88rem;
            font-weight: 600;
            max-width: 100%;
        }
        .reserved-note i { color: var(--gold); width: 16px; }

        /* ======= COUNTDOWN ======= */
        .countdown-section {
            background: rgba(255,255,255,0.55);
            backdrop-filter: blur(2px);
            padding: 50px 20px;
            text-align: center;
            border-top: 1px solid var(--line);
            border-bottom: 1px solid var(--line);
        }
        .countdown-label { font-size: 0.68rem; letter-spacing: 3px; text-transform: uppercase; color: var(--ink-light); margin-bottom: 22px; }
        .countdown { display: flex; justify-content: center; align-items: center; gap: 18px; flex-wrap: wrap; }
        .time-unit { text-align: center; min-width: 90px; background: rgba(255,255,255,0.6); padding: 20px 14px 14px; border: 1px solid var(--line); }
        .time-value { display: block; font-family: 'Cormorant Garamond', serif; font-weight: 600; font-size: clamp(2.6rem, 7vw, 3.6rem); color: var(--ink); line-height: 1; }
        .time-label { font-size: 0.6rem; letter-spacing: 2px; text-transform: uppercase; color: var(--gold-dark); font-weight: 600; margin-top: 6px; }
        .time-sep { display: none; }
        .just-married-msg { font-family: 'Cormorant Garamond', serif; font-size: 2.5rem; color: var(--gold-dark); font-style: italic; }

        /* ======= BODY ======= */
        .invitation-body { max-width: 700px; margin: 0 auto; padding: 0 20px; }
        .section-divider { display: none; }
        .section-heading { text-align: left; font-family: 'Cormorant Garamond', serif; font-size: clamp(2rem, 5vw, 3rem); font-weight: 300; letter-spacing: 0.5px; color: var(--ink); margin: 70px 0 4px; }
        .section-heading em { font-style: normal; color: var(--gold-dark); }
        .section-sub { text-align: left; font-size: 0.78rem; letter-spacing: 2px; text-transform: uppercase; color: var(--ink-light); margin-bottom: 30px; }

        /* Love story */
        .love-story-text {
            background: rgba(255,255,255,0.5);
            backdrop-filter: blur(2px);
            border-left: 3px solid var(--gold);
            padding: 24px 30px;
            text-align: left;
            font-family: 'Cormorant Garamond', serif;
            font-style: italic;
            font-size: 1.25rem;
            line-height: 1.85;
            color: var(--ink-mid);
        }

        /* Events */
        .event-timeline { position: relative; }
        .event-card { border: none; border-bottom: 1px solid var(--line); padding: 26px 0; background: transparent; }
        .event-name { font-family: 'Cormorant Garamond', serif; font-weight: 400; font-size: 1.9rem; letter-spacing: 0.5px; color: var(--ink); margin-bottom: 14px; }
        .event-meta { display: flex; flex-direction: column; gap: 6px; margin-bottom: 18px; }
        .event-meta-item { display: flex; align-items: center; gap: 10px; font-size: 0.88rem; color: var(--ink-mid); }
        .event-meta-item i { color: var(--gold-dark); width: 16px; text-align: center; }
        .event-actions { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 16px; }
        .btn-map {
            display: inline-flex; align-items: center; gap: 7px;
            background: var(--ink); color: white; text-decoration: none;
            padding: 10px 20px; font-size: 0.78rem; font-weight: 600;
            letter-spacing: 0.5px; transition: all 0.2s;
        }
        .btn-map:hover { background: var(--gold-dark); color: white; transform: translateY(-1px); }
        .btn-cal {
            display: inline-flex; align-items: center; gap: 7px;
            background: transparent; border: 1px solid var(--line);
            color: var(--ink-mid); text-decoration: none;
            padding: 10px 20px; font-size: 0.78rem; font-weight: 500; cursor: pointer; transition: all 0.2s;
        }
        .btn-cal:hover { border-color: var(--gold); color: var(--gold-dark); }
        .cal-dropdown { position: relative; display: inline-block; }
        .cal-menu { display: none; position: absolute; bottom: calc(100% + 8px); left: 0; background: white; border: 1px solid var(--line); box-shadow: 0 14px 34px rgba(0,0,0,0.1); min-width: 180px; z-index: 10; overflow: hidden; }
        .cal-menu.open { display: block; }
        .cal-menu a { display: flex; align-items: center; gap: 10px; padding: 11px 15px; font-size: 0.82rem; color: var(--ink-mid); text-decoration: none; transition: background 0.15s; }
        .cal-menu a:hover { background: var(--cream); color: var(--gold-dark); }
        .cal-menu a i { width: 16px; text-align: center; }

        /* ======= SWEET MOMENTS SLIDESHOW ======= */
        .sweet-slideshow {
            position: relative;
            max-width: 640px;
            margin: 0 auto;
            overflow: hidden;
            box-shadow: 0 20px 50px rgba(0,0,0,0.1);
            background: #000;
        }
        .sweet-slideshow-track { display: flex; transition: transform 0.5s ease; }
        .sweet-slide { flex: 0 0 100%; aspect-ratio: 4/3; cursor: pointer; }
        .sweet-slide img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .sweet-slide-nav {
            position: absolute; top: 50%; transform: translateY(-50%);
            background: rgba(255,255,255,0.9); color: var(--gold-dark);
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

        /* Lightbox */
        .lightbox { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.92); z-index: 1000; align-items: center; justify-content: center; }
        .lightbox.open { display: flex; }
        .lightbox img { max-width: 90vw; max-height: 90vh; object-fit: contain; }
        .lightbox-close { position: absolute; top: 20px; right: 20px; color: white; font-size: 1.8rem; cursor: pointer; opacity: 0.75; transition: opacity 0.2s; }
        .lightbox-close:hover { opacity: 1; }

        /* ======= LOVE STORY — INTERACTIVE TIMELINE ======= */
        .story-timeline { position: relative; max-width: 760px; margin: 36px auto 60px; padding: 6px 0 0; }
        .story-timeline-line { position: absolute; top: 0; bottom: 0; left: 50%; width: 2px; background: var(--line); transform: translateX(-50%); }
        .story-timeline-fill { position: absolute; top: 0; left: 0; width: 100%; height: 0%; background: linear-gradient(180deg, var(--gold), var(--gold-dark)); }
        .story-milestone { position: relative; width: 50%; padding: 0 42px 56px; opacity: 0; }
        .story-milestone.left { left: 0; text-align: right; }
        .story-milestone.right { left: 50%; text-align: left; }
        .story-milestone-marker {
            position: absolute; top: 2px; width: 34px; height: 34px; border-radius: 50%;
            background: var(--cream); border: 2px solid var(--gold); color: var(--gold-dark);
            display: flex; align-items: center; justify-content: center; font-size: 0.85rem;
            box-shadow: 0 0 0 6px rgba(250,249,246,0.9);
        }
        .story-milestone.left .story-milestone-marker { right: -17px; }
        .story-milestone.right .story-milestone-marker { left: -17px; }
        .story-milestone-card {
            display: inline-block; text-align: left; max-width: 100%;
            background: rgba(255,255,255,0.65); backdrop-filter: blur(2px);
            border: 1px solid var(--line); border-top: 3px solid var(--gold);
            padding: 20px 24px; box-shadow: 0 10px 28px rgba(0,0,0,0.06);
        }
        .story-milestone-label {
            display: block; font-size: 0.68rem; letter-spacing: 2px; text-transform: uppercase;
            color: var(--gold-dark); font-weight: 600; margin-bottom: 8px;
        }
        .story-milestone-card p {
            font-family: 'Cormorant Garamond', serif; font-style: italic; font-size: 1.12rem;
            line-height: 1.75; color: var(--ink-mid); margin: 0;
        }
        @media (max-width: 700px) {
            .story-timeline-line { left: 18px; }
            .story-milestone, .story-milestone.left, .story-milestone.right {
                width: 100%; left: 0; text-align: left; padding: 0 0 40px 52px;
            }
            .story-milestone.left .story-milestone-marker, .story-milestone.right .story-milestone-marker { left: 1px; right: auto; }
        }
        body.night-mode .story-milestone-card { background: rgba(30,30,38,0.7); color: #e9e6de; border-color: rgba(184,147,90,0.25); }

        /* Guest Memory Wall — subtle hover lift + accent quote */
        .memory-card { transition: transform 0.25s ease, box-shadow 0.25s ease; position: relative; }
        .memory-card:hover { transform: translateY(-4px); box-shadow: 0 14px 34px rgba(0,0,0,0.1); }
        .memory-card::before {
            content: '\201C'; position: absolute; top: 6px; right: 14px;
            font-family: 'Cormorant Garamond', serif; font-size: 2.6rem; color: var(--gold-light); line-height: 1;
        }

        /* Guest shared gallery */
        .gallery-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 14px; }
        .gallery-item { overflow: hidden; aspect-ratio: 1; cursor: pointer; position: relative; border: 1px solid var(--line); transition: transform 0.3s; }
        .gallery-item:hover { transform: translateY(-3px); }
        .gallery-item img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s ease; display: block; }
        .gallery-item:hover img { transform: scale(1.06); }

        /* ======= RSVP ======= */
        .rsvp-section { background: rgba(255,255,255,0.6); backdrop-filter: blur(2px); border-top: 1px solid var(--line); padding: 70px 20px; text-align: center; }
        .rsvp-card { max-width: 500px; margin: 0 auto; background: rgba(255,255,255,0.85); border: 1px solid var(--line); padding: 44px 38px; text-align: left; }
        .rsvp-title { font-family: 'Cormorant Garamond', serif; font-size: 2.1rem; font-weight: 400; color: var(--ink); margin-bottom: 6px; }
        .rsvp-subtitle { font-size: 0.78rem; color: var(--ink-light); letter-spacing: 2px; text-transform: uppercase; margin-bottom: 28px; }
        .rsvp-options { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 20px; }
        .rsvp-option input[type="radio"] { display: none; }
        .rsvp-option label { display: flex; flex-direction: column; align-items: center; gap: 8px; padding: 18px 12px; border: 1px solid var(--line); cursor: pointer; font-size: 0.82rem; font-weight: 500; color: var(--ink-mid); transition: all 0.2s; }
        .rsvp-option label i { font-size: 1.4rem; }
        .rsvp-option:first-child input[type="radio"]:checked + label { border-color: #22c55e; background: rgba(34,197,94,0.05); color: #16a34a; }
        .rsvp-option:last-child input[type="radio"]:checked + label { border-color: #ef4444; background: rgba(239,68,68,0.05); color: #dc2626; }
        .rsvp-note { width: 100%; background: var(--cream); border: 1px solid var(--line); padding: 14px 16px; font-family: 'Inter', sans-serif; font-size: 0.88rem; color: var(--ink-mid); resize: none; outline: none; transition: border-color 0.2s; margin-bottom: 16px; }
        .rsvp-note:focus { border-color: var(--gold); }
        .btn-rsvp-submit { width: 100%; background: var(--ink); color: white; border: none; padding: 16px; font-size: 0.86rem; font-weight: 700; letter-spacing: 3px; text-transform: uppercase; cursor: pointer; transition: all 0.25s; }
        .btn-rsvp-submit:hover { background: var(--gold-dark); }

        /* ======= FOOTER ======= */
        .inv-footer { text-align: center; padding: 44px 20px; font-size: 0.75rem; color: var(--ink-light); border-top: 1px solid var(--line); background: rgba(255,255,255,0.6); }
        .inv-footer .brand { font-family: 'Inter', sans-serif; font-size: 1rem; font-weight: 700; letter-spacing: 3px; text-transform: uppercase; color: var(--gold-dark); display: block; margin-bottom: 6px; }

        @media (max-width: 640px) {
            .hero-header { padding: 80px 6vw 50px; }
            .rsvp-card { padding: 32px 24px; }
        }

        /* ======================================================================
           IMMERSIVE 3D EXPERIENCE LAYER
           ====================================================================== */

        /* Accessibility: skip link */
        .skip-link {
            position: absolute; left: -9999px; top: 0; z-index: 500;
            background: var(--ink); color: var(--gold-light);
            padding: 12px 20px; font-size: 0.85rem; text-decoration: none;
        }
        .skip-link:focus { left: 12px; top: 12px; }
        a:focus-visible, button:focus-visible, input:focus-visible, textarea:focus-visible {
            outline: 2px solid var(--gold-dark); outline-offset: 2px;
        }

        /* Hero-local 3D canvas: floral arch, rings, candles, sparkles */
        .hero-header { position: relative; overflow: hidden; perspective: 1100px; }
        #hero3d-canvas {
            position: absolute; inset: 0; width: 100%; height: 100%;
            z-index: 0; pointer-events: none;
            opacity: 0; transition: opacity 1.6s ease;
        }
        #hero3d-canvas.ready { opacity: 1; }
        .hero-content { position: relative; z-index: 1; transition: transform 0.15s ease-out; will-change: transform; }

        /* Falling petals + cursor sparkle, page-wide */
        #petal-canvas { position: fixed; inset: 0; width: 100vw; height: 100vh; z-index: 2; pointer-events: none; }

        /* Celebration burst canvas */
        #celebration-canvas {
            position: fixed; inset: 0; width: 100vw; height: 100vh; z-index: 999;
            pointer-events: none; display: none;
        }
        #celebration-canvas.active { display: block; }

        /* Floating control buttons: day/night + music */
        .fx-controls { position: fixed; right: 16px; bottom: 20px; z-index: 60; display: flex; flex-direction: column; gap: 10px; }
        .fx-btn {
            width: 46px; height: 46px; border-radius: 50%;
            border: 1px solid var(--line); background: rgba(255,255,255,0.9); color: var(--gold-dark);
            box-shadow: 0 6px 20px rgba(0,0,0,0.15);
            display: flex; align-items: center; justify-content: center; font-size: 1.05rem;
            cursor: pointer; backdrop-filter: blur(6px); transition: transform 0.25s, background 0.25s;
        }
        .fx-btn:hover { transform: translateY(-2px) scale(1.06); }
        .fx-btn.playing { color: #16a34a; }

        /* Day / Night ambience */
        body.night-mode { background: #14141a; color: #e9e6de; }
        body.night-mode .hero-header { background: rgba(10,10,16,0.5) !important; }
        body.night-mode .couple-names-hero,
        body.night-mode .guest-name-display,
        body.night-mode .hero-date { color: #f1eee6; }
        body.night-mode .countdown-section,
        body.night-mode .rsvp-section,
        body.night-mode .inv-footer { background: rgba(20,20,26,0.75); color: #e9e6de; }
        body.night-mode .time-unit,
        body.night-mode .rsvp-card,
        body.night-mode .love-story-text { background: rgba(30,30,38,0.7); color: #e9e6de; border-color: rgba(184,147,90,0.25); }
        body.night-mode .event-card { border-color: rgba(184,147,90,0.2); }
        body.night-mode .fx-btn { background: rgba(24,24,30,0.85); color: var(--gold-light); border-color: rgba(184,147,90,0.3); }
        body.night-mode::after {
            content: ''; position: fixed; inset: 0; z-index: 1; pointer-events: none; opacity: 0.85;
            background:
                radial-gradient(1.5px 1.5px at 12% 18%, rgba(255,255,255,0.6), transparent 60%),
                radial-gradient(1.5px 1.5px at 32% 60%, rgba(255,255,255,0.45), transparent 60%),
                radial-gradient(1.5px 1.5px at 68% 22%, rgba(255,255,255,0.5), transparent 60%),
                radial-gradient(1.5px 1.5px at 84% 48%, rgba(255,255,255,0.4), transparent 60%),
                radial-gradient(1.5px 1.5px at 52% 82%, rgba(255,255,255,0.35), transparent 60%);
        }

        /* Masonry gallery layout (columns-based) */
        .gallery-grid.masonry {
            display: block; column-count: 3; column-gap: 14px;
        }
        .gallery-grid.masonry .gallery-item {
            aspect-ratio: auto; break-inside: avoid; margin-bottom: 14px; width: 100%;
        }
        @media (max-width: 700px) { .gallery-grid.masonry { column-count: 2; } }
        @media (max-width: 420px) { .gallery-grid.masonry { column-count: 1; } }

        /* Cinematic fullscreen slideshow controls in lightbox */
        .lightbox img { transition: opacity 0.4s ease, transform 6s ease; opacity: 0; transform: scale(1); }
        .lightbox.open img { opacity: 1; }
        .lightbox.open img.ken-burns { transform: scale(1.08); }
        .lightbox-nav {
            position: absolute; top: 50%; transform: translateY(-50%);
            background: rgba(255,255,255,0.15); color: #fff; border: 1px solid rgba(255,255,255,0.35);
            width: 48px; height: 48px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; font-size: 1.1rem; transition: background 0.2s;
        }
        .lightbox-nav:hover { background: rgba(255,255,255,0.3); }
        .lightbox-nav.prev { left: 18px; }
        .lightbox-nav.next { right: 18px; }

        /* Guest Memory Wall */
        .memory-wall-form { background: rgba(255,255,255,0.6); border: 2px dashed var(--gold); padding: 26px 22px; margin-bottom: 26px; }
        .memory-wall-form textarea, .memory-wall-form input[type="text"] {
            width: 100%; background: var(--cream); border: 1px solid var(--line); padding: 12px 14px;
            font-family: 'Inter', sans-serif; font-size: 0.88rem; color: var(--ink-mid); outline: none;
            margin-bottom: 12px; resize: none; transition: border-color 0.2s;
        }
        .memory-wall-form textarea:focus, .memory-wall-form input[type="text"]:focus { border-color: var(--gold); }
        .memory-wall-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(230px, 1fr)); gap: 16px; }
        .memory-card {
            background: rgba(255,255,255,0.7); border: 1px solid var(--line); border-left: 3px solid var(--gold);
            padding: 18px 20px; font-family: 'Cormorant Garamond', serif;
        }
        .memory-card p { font-style: italic; font-size: 1.05rem; color: var(--ink-mid); margin-bottom: 10px; }
        .memory-card .memory-author { font-family: 'Inter', sans-serif; font-size: 0.72rem; letter-spacing: 1px; text-transform: uppercase; color: var(--gold-dark); font-weight: 600; }

        /* Interactive venue map preview (3D tilt card) */
        .venue-map-wrap { margin-top: 18px; perspective: 900px; }
        .venue-map-card {
            border: 1px solid var(--line); overflow: hidden; height: 220px;
            transform-style: preserve-3d; transition: transform 0.25s ease, box-shadow 0.25s ease;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        }
        .venue-map-card:hover { box-shadow: 0 18px 46px rgba(0,0,0,0.14); }
        .venue-map-card iframe { width: 100%; height: 100%; border: 0; display: block; }
    </style>
</head>
<body>

<a href="#invitation-main" class="skip-link">Skip to invitation content</a>

<canvas id="page-canvas"></canvas>
<canvas id="petal-canvas" aria-hidden="true"></canvas>
<canvas id="celebration-canvas" aria-hidden="true"></canvas>

<div class="fx-controls">
    <?php if (!empty($wedding['music_url'])): ?>
    <button type="button" class="fx-btn" id="music-toggle" title="Play background music" aria-label="Play background music">
        <i class="fas fa-music"></i>
    </button>
    <audio id="bg-music" loop preload="none" src="<?php echo htmlspecialchars($wedding['music_url']); ?>"></audio>
    <?php endif; ?>
</div>

<?php if ($guest_id == 0): ?>
<div class="preview-bar">
    <i class="fas fa-eye"></i>
    <strong>PREVIEW MODE</strong> — This is how your guests will see the invitation.
    <a href="dashboard/index.php">← Back to Dashboard</a>
</div>
<?php endif; ?>

<?php if ($msg): ?>
<div style="max-width:700px; margin:20px auto; padding:0 20px;">
    <?php echo $msg; ?>
</div>
<?php endif; ?>

<!-- HERO HEADER -->
<div class="hero-header" <?php echo $hero_style; ?>>
    <canvas id="hero3d-canvas" aria-hidden="true"></canvas>
    <div class="hero-content reveal" id="invitation-main">
        <span class="guest-greeting-tag">You're Warmly Invited</span>
        <div class="guest-name-display">Dear <?php echo htmlspecialchars($guest_name); ?>,</div>

        <div class="couple-names-hero">
            <?php echo htmlspecialchars($wedding['bride_name']); ?>
            <span class="amp">&amp;</span>
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
        <div class="time-unit"><span class="time-value" id="cd-hours">00</span><span class="time-label">Hours</span></div>
        <div class="time-unit"><span class="time-value" id="cd-mins">00</span><span class="time-label">Minutes</span></div>
        <div class="time-unit"><span class="time-value" id="cd-secs">00</span><span class="time-label">Seconds</span></div>
    </div>
</div>

<div class="invitation-body container py-4">

    <!-- LOVE STORY — INTERACTIVE TIMELINE -->
    <?php if (!empty($wedding['love_story'])):
        // The love story is stored as one free-text field. We split it into "chapters"
        // on blank lines so the couple's own paragraphs become animated timeline milestones —
        // no schema change needed. Falls back to a single chapter if there are no blank lines.
        $story_raw = str_replace("\r\n", "\n", $wedding['love_story']);
        $story_chapters = preg_split('/\n\s*\n/', trim($story_raw));
        $story_chapters = array_values(array_filter(array_map('trim', $story_chapters), function($p) { return $p !== ''; }));
        if (empty($story_chapters)) { $story_chapters = [trim($story_raw)]; }
        $story_icons  = ['fa-heart', 'fa-seedling', 'fa-ring', 'fa-champagne-glasses', 'fa-dove', 'fa-gem'];
        $story_labels = ['How It Began', 'A Growing Bond', 'A Promise Made', 'Building a Life Together', 'Forever Begins', 'Our Story Continues'];
    ?>
    <h2 class="section-heading reveal">Our <em>Love Story</em></h2>
    <p class="section-sub">How it all began</p>
    <div class="story-timeline" id="story-timeline">
        <div class="story-timeline-line"><div class="story-timeline-fill" id="story-timeline-fill"></div></div>
        <?php foreach ($story_chapters as $i => $chapter):
            $side  = $i % 2 === 0 ? 'left' : 'right';
            $icon  = $story_icons[$i % count($story_icons)];
            $label = $story_labels[$i % count($story_labels)];
        ?>
        <div class="story-milestone <?php echo $side; ?>" data-side="<?php echo $side; ?>">
            <div class="story-milestone-marker"><i class="fas <?php echo $icon; ?>"></i></div>
            <div class="story-milestone-card">
                <span class="story-milestone-label"><?php echo htmlspecialchars($label); ?></span>
                <p><?php echo nl2br(htmlspecialchars($chapter)); ?></p>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- EVENTS / PROGRAMME -->
    <h2 class="section-heading reveal"><em>Wedding</em> Programme</h2>
    <p class="section-sub">Join us for these celebrations</p>

    <?php if (count($wedding_events) > 0): ?>
        <div class="event-timeline row row-cols-1 g-0 justify-content-start">
            <?php foreach ($wedding_events as $ev):
                $ev_start = date('Ymd\THis', strtotime($ev['event_date_time']));
                $ev_end = date('Ymd\THis', strtotime($ev['event_date_time']) + 7200);
                $ev_title = urlencode($ev['event_name'] . ' — ' . $wedding['bride_name'] . ' & ' . $wedding['groom_name']);
                $ev_loc = urlencode($ev['location_name']);
                $ev_gcal = "https://calendar.google.com/calendar/render?action=TEMPLATE&text={$ev_title}&dates={$ev_start}/{$ev_end}&location={$ev_loc}";
                $ev_ics = "calendar.php?wedding_id={$wedding_id}&event_id={$ev['id']}";
                $ev_outlook = "https://outlook.live.com/calendar/0/deeplink/compose?path=/calendar/action/compose&rru=addevent&subject=" . $ev_title . "&startdt=" . urlencode(date('c', strtotime($ev['event_date_time']))) . "&enddt=" . urlencode(date('c', strtotime($ev['event_date_time']) + 7200)) . "&location=" . $ev_loc;
            ?>
            <div class="event-card col reveal">
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
                <?php if (!empty($ev['location_name'])): ?>
                <div class="venue-map-wrap">
                    <div class="venue-map-card tilt-card">
                        <iframe
                            src="https://www.google.com/maps?q=<?php echo urlencode($ev['location_name']); ?>&output=embed"
                            loading="lazy"
                            title="Map preview for <?php echo htmlspecialchars($ev['location_name']); ?>"
                            referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p style="text-align:center; color:var(--ink-light); font-style:italic; padding:30px 0;">Event details will be updated soon.</p>
    <?php endif; ?>

    <!-- GALLERY -->
    <?php if (count($gallery_images) > 0): ?>
    <h2 class="section-heading reveal"><em>Sweet</em> Moments</h2>
    <p class="section-sub">Our engagement memories</p>

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
    <?php endif; ?>

</div><!-- /invitation-body -->

<!-- LIGHTBOX -->
<div class="lightbox" id="lightbox" onclick="if(event.target===this) closeLightbox()" role="dialog" aria-modal="true" aria-label="Photo viewer">
    <span class="lightbox-close" onclick="closeLightbox()" role="button" tabindex="0" aria-label="Close photo viewer"><i class="fas fa-times"></i></span>
    <button type="button" class="lightbox-nav prev" onclick="lightboxMove(-1)" aria-label="Previous photo"><i class="fas fa-chevron-left"></i></button>
    <img src="" id="lightbox-img" alt="">
    <button type="button" class="lightbox-nav next" onclick="lightboxMove(1)" aria-label="Next photo"><i class="fas fa-chevron-right"></i></button>
</div>
<!-- =====================================================================
         📸 GUEST SHARED GALLERY SECTION (පැකේජය අනුව පමණක් පෙන්වයි)
         ===================================================================== -->
    <?php if (isset($has_guest_gallery) && $has_guest_gallery): ?>
    <div class="invitation-body" style="padding-top:20px;">
    <h2 class="section-heading reveal"><em>Guest</em> Shared Moments</h2>
    <p class="section-sub">Capture and share your beautiful memories with us!</p>

    <!-- Upload Box (Preview mode එකේදී අක්‍රීය වේ) -->
    <div style="background: rgba(255,255,255,0.6); border: 2px dashed var(--gold); padding: 30px 20px; text-align: center; margin-bottom: 30px;">
        <?php if ($guest_id == 0): ?>
            <p class="text-muted small"><i class="fas fa-lock"></i> Photo upload is disabled in Preview Mode.</p>
        <?php else: ?>
            <i class="fas fa-camera-retro" style="font-size: 2.2rem; color: var(--gold-dark); margin-bottom: 12px; display: block;"></i>
            <h5 class="fw-bold" style="font-family:'Inter', sans-serif; font-size: 0.95rem; color: var(--ink); margin-bottom: 6px;">Share a Photo from Your Phone</h5>
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
    <div class="gallery-grid masonry" id="guest-gallery-grid" style="margin-bottom: 30px;">
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
<!-- =====================================================================
         💌 GUEST MEMORY WALL (පැකේජය අනුව පමණක් පෙන්වයි)
         Assumes the controller passes $has_memory_wall (bool) and
         $guest_messages (array of ['guest_name' => ..., 'message' => ...]),
         mirroring the existing $has_guest_gallery / $guest_images pattern.
         The AJAX call below posts action=guest_leave_message to
         view_invitation.php — wire that endpoint up the same way the
         guest photo upload endpoint already works.
         ===================================================================== -->
    <?php if (isset($has_memory_wall) && $has_memory_wall): ?>
    <div class="invitation-body" style="padding-top:20px;">
    <h2 class="section-heading reveal"><em>Guest</em> Memory Wall</h2>
    <p class="section-sub">Leave a message for the happy couple</p>

    <div class="memory-wall-form reveal">
        <?php if ($guest_id == 0): ?>
            <p class="text-muted small"><i class="fas fa-lock"></i> Leaving a message is disabled in Preview Mode.</p>
        <?php else: ?>
            <input type="text" id="memory-name-input" placeholder="Your name" maxlength="80">
            <textarea id="memory-message-input" rows="3" placeholder="Share a wish, a memory, or a blessing for the couple..." maxlength="500"></textarea>
            <button type="button" class="btn-map" id="memory-submit-btn" style="border:none; cursor:pointer;">
                <i class="fas fa-feather"></i> Leave a Message
            </button>
            <div id="memory-wall-status" class="small mt-2" style="min-height:1.2em;" aria-live="polite"></div>
        <?php endif; ?>
    </div>

    <div class="memory-wall-grid" id="memory-wall-grid">
        <?php if (isset($guest_messages) && count($guest_messages) > 0): ?>
            <?php foreach ($guest_messages as $gm): ?>
            <div class="memory-card reveal">
                <p>&ldquo;<?php echo nl2br(htmlspecialchars($gm['message'])); ?>&rdquo;</p>
                <div class="memory-author">— <?php echo htmlspecialchars($gm['guest_name']); ?></div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="text-center text-muted small py-3" style="font-style: italic; grid-column: 1 / -1;" id="no-memory-msgs">
                No messages yet. Be the first to leave one! 💌
            </div>
        <?php endif; ?>
    </div>
    </div>
    <script>
    document.getElementById('memory-submit-btn')?.addEventListener('click', function() {
        const nameInput = document.getElementById('memory-name-input');
        const msgInput = document.getElementById('memory-message-input');
        const status = document.getElementById('memory-wall-status');
        const message = msgInput.value.trim();
        if (!message) { status.textContent = 'Please write a message first.'; status.style.color = '#dc2626'; return; }

        const btn = this;
        btn.disabled = true;
        status.textContent = 'Sending...';
        status.style.color = 'var(--ink-light)';

        const formData = new FormData();
        formData.append('action', 'guest_leave_message');
        formData.append('guest_name', nameInput.value.trim());
        formData.append('message', message);

        fetch('view_invitation.php', { method: 'POST', body: formData })
            .then(r => r.json())
            .then(data => {
                btn.disabled = false;
                if (data.success) {
                    status.textContent = 'Thank you for your message! 💛';
                    status.style.color = '#16a34a';
                    const grid = document.getElementById('memory-wall-grid');
                    const empty = document.getElementById('no-memory-msgs');
                    if (empty) empty.remove();
                    const card = document.createElement('div');
                    card.className = 'memory-card';
                    card.style.opacity = '0';
                    card.innerHTML = `<p>&ldquo;${message.replace(/</g,'&lt;').replace(/\n/g,'<br>')}&rdquo;</p><div class="memory-author">— ${(nameInput.value.trim() || 'A guest').replace(/</g,'&lt;')}</div>`;
                    grid.appendChild(card);
                    if (typeof anime !== 'undefined') {
                        anime({ targets: card, opacity: [0, 1], translateY: [16, 0], easing: 'easeOutCubic', duration: 600 });
                    } else {
                        card.style.opacity = '1';
                    }
                    msgInput.value = '';
                } else {
                    status.textContent = data.message || 'Could not post your message.';
                    status.style.color = '#dc2626';
                }
            })
            .catch(() => {
                btn.disabled = false;
                status.textContent = 'Something went wrong. Please try again.';
                status.style.color = '#dc2626';
            });
    });
    </script>
    <?php endif; ?>
<!-- RSVP -->
<div class="rsvp-section">
    <div class="rsvp-card reveal">
        <h2 class="rsvp-title">RSVP</h2>
        <p class="rsvp-subtitle">Will you be joining us?</p>
        <?php if (isset($current_guest['seats_reserved']) && $current_guest['seats_reserved'] > 0): ?>
        <div class="reserved-note" style="margin-bottom: 20px;">
            <i class="fas fa-chair"></i>
            <span>We have reserved <strong><?php echo intval($current_guest['seats_reserved']); ?></strong> seat(s) in your honor.</span>
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

            <button type="submit" name="submit_rsvp" class="btn-rsvp-submit">Send My RSVP</button>
        </form>
    </div>
</div>

<!-- FOOTER -->
<div class="inv-footer">
    <span class="brand">Lumus Studio</span>
    Digital Wedding Invitations · Designed by Hathisa Thissara
</div>

<!-- Three.js (persistent page-wide animation) + anime.js (scroll reveals) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/animejs/3.2.1/anime.min.js"></script>

<script>
// Global flags used by a few of the feature scripts below.
const RSVP_JUST_SUCCEEDED = <?php echo $rsvp_success ? 'true' : 'false'; ?>;
const REDUCE_MOTION = !!(window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches);

// Countdown
const weddingDate = new Date("<?php echo $wedding['wedding_date']; ?> 00:00:00").getTime();
function tick() {
    const now = Date.now();
    const dist = weddingDate - now;
    if (dist < 0) {
        document.getElementById('countdown').innerHTML = '<p class="just-married-msg">Just Married! ❧</p>';
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

// Background music — play/pause toggle (button only renders when the couple set a track)
(function initMusicToggle() {
    const btn = document.getElementById('music-toggle');
    const audio = document.getElementById('bg-music');
    if (!btn || !audio) return;
    const icon = btn.querySelector('i');
    btn.addEventListener('click', () => {
        if (audio.paused) {
            audio.play().catch(() => {});
            icon.className = 'fas fa-pause';
            btn.classList.add('playing');
            btn.setAttribute('aria-label', 'Pause background music');
        } else {
            audio.pause();
            icon.className = 'fas fa-music';
            btn.classList.remove('playing');
            btn.setAttribute('aria-label', 'Play background music');
        }
    });
})();

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

// Lightbox — cinematic fullscreen slideshow with Ken Burns zoom + prev/next
let lightboxSources = [];
let lightboxIndex = -1;
function refreshLightboxSources() {
    lightboxSources = Array.from(document.querySelectorAll('.sweet-slide img, .gallery-item img')).map(img => img.getAttribute('src'));
}
function openLightbox(src) {
    refreshLightboxSources();
    lightboxIndex = lightboxSources.indexOf(src);
    const img = document.getElementById('lightbox-img');
    img.classList.remove('ken-burns');
    img.src = src;
    document.getElementById('lightbox').classList.add('open');
    document.body.style.overflow = 'hidden';
    // restart the slow Ken Burns zoom on the newly shown photo
    requestAnimationFrame(() => requestAnimationFrame(() => img.classList.add('ken-burns')));
}
function lightboxMove(dir) {
    if (!lightboxSources.length) refreshLightboxSources();
    if (!lightboxSources.length) return;
    lightboxIndex = (lightboxIndex + dir + lightboxSources.length) % lightboxSources.length;
    const img = document.getElementById('lightbox-img');
    img.classList.remove('ken-burns');
    img.src = lightboxSources[lightboxIndex];
    requestAnimationFrame(() => requestAnimationFrame(() => img.classList.add('ken-burns')));
}
function closeLightbox() {
    document.getElementById('lightbox').classList.remove('open');
    document.getElementById('lightbox-img').classList.remove('ken-burns');
    document.body.style.overflow = '';
}
document.addEventListener('keydown', e => {
    if (!document.getElementById('lightbox').classList.contains('open')) return;
    if (e.key === 'Escape') closeLightbox();
    if (e.key === 'ArrowRight') lightboxMove(1);
    if (e.key === 'ArrowLeft') lightboxMove(-1);
});

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

// RSVP celebration — a short confetti/petal/heart burst that plays once, right after
// a successful RSVP submission reloads the page (see $rsvp_success on the server side).
(function initCelebration() {
    if (!RSVP_JUST_SUCCEEDED || REDUCE_MOTION) return;
    const canvas = document.getElementById('celebration-canvas');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    if (!ctx) return;

    function resize() { canvas.width = window.innerWidth; canvas.height = window.innerHeight; }
    resize();
    window.addEventListener('resize', resize);
    canvas.classList.add('active');

    const colors = ['#b8935a', '#c98f6b', '#ded0b8', '#e7c9a0', '#f3d9a0', '#8f6f42'];
    const count = window.innerWidth < 700 ? 55 : 100;
    const pieces = [];
    for (let i = 0; i < count; i++) {
        pieces.push({
            x: canvas.width / 2 + (Math.random() - 0.5) * 140,
            y: canvas.height * 0.72,
            vx: (Math.random() - 0.5) * 9,
            vy: -(6 + Math.random() * 7),
            size: 5 + Math.random() * 6,
            rot: Math.random() * Math.PI * 2,
            rotSpeed: (Math.random() - 0.5) * 0.25,
            color: colors[Math.floor(Math.random() * colors.length)],
            shape: Math.random() < 0.4 ? 'heart' : 'petal',
            life: 1
        });
    }

    function drawPiece(p) {
        ctx.save();
        ctx.translate(p.x, p.y);
        ctx.rotate(p.rot);
        ctx.globalAlpha = Math.max(p.life, 0);
        ctx.fillStyle = p.color;
        if (p.shape === 'heart') {
            const s = p.size * 0.5;
            ctx.beginPath();
            ctx.moveTo(0, s * 0.6);
            ctx.bezierCurveTo(-s, -s * 0.4, -s * 0.5, -s * 1.2, 0, -s * 0.4);
            ctx.bezierCurveTo(s * 0.5, -s * 1.2, s, -s * 0.4, 0, s * 0.6);
            ctx.fill();
        } else {
            ctx.beginPath();
            ctx.moveTo(0, -p.size);
            ctx.bezierCurveTo(p.size * 0.7, -p.size * 0.3, p.size * 0.5, p.size * 0.7, 0, p.size);
            ctx.bezierCurveTo(-p.size * 0.5, p.size * 0.7, -p.size * 0.7, -p.size * 0.3, 0, -p.size);
            ctx.fill();
        }
        ctx.restore();
    }

    const gravity = 0.22;
    let elapsed = 0;
    function loop() {
        elapsed += 16;
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        pieces.forEach(p => {
            p.vy += gravity;
            p.x += p.vx;
            p.y += p.vy;
            p.rot += p.rotSpeed;
            if (elapsed > 1800) p.life -= 0.012;
            drawPiece(p);
        });
        if (elapsed < 4200) {
            requestAnimationFrame(loop);
        } else {
            canvas.classList.remove('active');
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            window.removeEventListener('resize', resize);
        }
    }
    loop();

    // Bring the RSVP card into view so the burst is actually seen.
    const rsvpCard = document.querySelector('.rsvp-card');
    if (rsvpCard) rsvpCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
})();

// Interactive 3D tilt for the venue map preview card
(function initTiltCards() {
    if (REDUCE_MOTION) return;
    document.querySelectorAll('.tilt-card').forEach(card => {
        card.addEventListener('mousemove', (e) => {
            const r = card.getBoundingClientRect();
            const px = (e.clientX - r.left) / r.width - 0.5;
            const py = (e.clientY - r.top) / r.height - 0.5;
            card.style.transform = `rotateX(${(-py * 9).toFixed(2)}deg) rotateY(${(px * 9).toFixed(2)}deg) translateY(-2px)`;
        });
        card.addEventListener('mouseleave', () => {
            card.style.transform = 'rotateX(0deg) rotateY(0deg)';
        });
    });
})();

// Love Story timeline — GSAP ScrollTrigger fills the connecting line as you scroll,
// and each milestone slides in from its own side once it enters view.
(function initStoryTimeline() {
    const wrap = document.getElementById('story-timeline');
    if (!wrap) return;

    const milestones = wrap.querySelectorAll('.story-milestone');
    if (REDUCE_MOTION) {
        milestones.forEach(m => { m.style.opacity = '1'; });
    } else if (typeof anime !== 'undefined') {
        const io = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (!entry.isIntersecting) return;
                const side = entry.target.dataset.side === 'right' ? 1 : -1;
                anime({
                    targets: entry.target,
                    opacity: [0, 1],
                    translateX: [side * 34, 0],
                    easing: 'easeOutCubic',
                    duration: 750
                });
                io.unobserve(entry.target);
            });
        }, { threshold: 0.25 });
        milestones.forEach(m => io.observe(m));
    } else {
        milestones.forEach(m => { m.style.opacity = '1'; });
    }

    const fill = document.getElementById('story-timeline-fill');
    if (fill && typeof gsap !== 'undefined' && typeof ScrollTrigger !== 'undefined') {
        gsap.registerPlugin(ScrollTrigger);
        gsap.to(fill, {
            height: '100%',
            ease: 'none',
            scrollTrigger: {
                trigger: wrap,
                start: 'top 75%',
                end: 'bottom 60%',
                scrub: 0.6
            }
        });
    } else if (fill) {
        fill.style.height = '100%';
    }
})();

// ================= THREE.JS — HERO: 3D wedding rings + floral arch =================
// Lives only inside the hero header canvas (#hero3d-canvas), fades in once ready.
(function initHero3D() {
    const canvas = document.getElementById('hero3d-canvas');
    const heroEl = document.querySelector('.hero-header');
    if (!canvas || !heroEl || typeof THREE === 'undefined' || REDUCE_MOTION) return;

    const renderer = new THREE.WebGLRenderer({ canvas, alpha: true, antialias: true });
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));

    const scene = new THREE.Scene();
    const camera = new THREE.PerspectiveCamera(45, 1, 0.1, 100);
    camera.position.set(0, 0.25, 9);

    function resize() {
        const w = heroEl.clientWidth || window.innerWidth;
        const h = heroEl.clientHeight || window.innerHeight;
        renderer.setSize(w, h);
        camera.aspect = w / h;
        camera.updateProjectionMatrix();
    }
    resize();
    window.addEventListener('resize', resize);

    // Soft warm lighting so the metallic rings + flowers read with depth
    scene.add(new THREE.HemisphereLight(0xfff3e0, 0x2a1d10, 0.95));
    const key = new THREE.DirectionalLight(0xffe3b3, 1.1);
    key.position.set(4, 5, 6);
    scene.add(key);
    const rim = new THREE.DirectionalLight(0xb8935a, 0.55);
    rim.position.set(-5, -2, -4);
    scene.add(rim);

    // ---- Procedural 3D floral arch (half-torus "frame" + flower clusters) ----
    const archGroup = new THREE.Group();
    const archRadius = 3.5;
    const archMat = new THREE.MeshStandardMaterial({ color: 0x8f6f42, roughness: 0.8, metalness: 0.1 });
    const archTorus = new THREE.Mesh(new THREE.TorusGeometry(archRadius, 0.075, 10, 72, Math.PI), archMat);
    archGroup.add(archTorus);

    const flowerColors = [0xb8935a, 0xded0b8, 0xf5efe2, 0xc9a96b, 0x8f6f42];
    function makeFlower(color) {
        const g = new THREE.Group();
        const petalGeo = new THREE.SphereGeometry(0.14, 8, 8);
        const petalMat = new THREE.MeshStandardMaterial({ color, roughness: 0.65, metalness: 0.05 });
        for (let i = 0; i < 5; i++) {
            const p = new THREE.Mesh(petalGeo, petalMat);
            const ang = (i / 5) * Math.PI * 2;
            p.position.set(Math.cos(ang) * 0.16, Math.sin(ang) * 0.16, 0);
            p.scale.set(1, 0.55, 0.6);
            g.add(p);
        }
        g.add(new THREE.Mesh(new THREE.SphereGeometry(0.075, 8, 8), new THREE.MeshStandardMaterial({ color: 0xffe9b0, roughness: 0.4 })));
        return g;
    }
    const leafMat = new THREE.MeshStandardMaterial({ color: 0x6b7a4a, roughness: 0.7 });
    function makeLeaf() {
        const geo = new THREE.ConeGeometry(0.06, 0.28, 5);
        return new THREE.Mesh(geo, leafMat);
    }
    const flowerCount = 20;
    for (let i = 0; i <= flowerCount; i++) {
        const t = i / flowerCount;
        const ang = t * Math.PI; // sweeps the top half, matching the arch geometry
        const fx = Math.cos(ang) * archRadius;
        const fy = Math.sin(ang) * archRadius;
        const flower = makeFlower(flowerColors[i % flowerColors.length]);
        flower.position.set(fx, fy, (Math.random() - 0.5) * 0.3);
        flower.rotation.z = ang - Math.PI / 2;
        flower.scale.setScalar(0.6 + Math.random() * 0.5);
        archGroup.add(flower);
        if (i % 2 === 0) {
            const leaf = makeLeaf();
            leaf.position.set(fx * 1.02, fy * 1.02, (Math.random() - 0.5) * 0.3);
            leaf.rotation.z = ang - Math.PI / 2 + (Math.random() - 0.5) * 0.6;
            leaf.rotation.x = Math.PI / 2;
            archGroup.add(leaf);
        }
    }
    archGroup.position.set(0, -1.65, -2.6);
    scene.add(archGroup);

    // ---- Animated 3D wedding rings, slowly rotating near the hero text ----
    const ringGroup = new THREE.Group();
    const ringMatGold = new THREE.MeshStandardMaterial({ color: 0xd4af6a, metalness: 0.9, roughness: 0.25 });
    const ringMatRose = new THREE.MeshStandardMaterial({ color: 0xc98f6b, metalness: 0.9, roughness: 0.25 });
    const ring1 = new THREE.Mesh(new THREE.TorusGeometry(0.55, 0.07, 24, 64), ringMatGold);
    const ring2 = new THREE.Mesh(new THREE.TorusGeometry(0.55, 0.07, 24, 64), ringMatRose);
    ring1.position.set(-0.32, 0, 0);
    ring2.position.set(0.32, 0, 0.18);
    ring1.rotation.x = Math.PI / 2.3;
    ring2.rotation.x = Math.PI / 2.3;
    ringGroup.add(ring1, ring2);
    const gem = new THREE.Mesh(
        new THREE.OctahedronGeometry(0.09, 0),
        new THREE.MeshStandardMaterial({ color: 0xffffff, metalness: 0.15, roughness: 0.05, emissive: 0x2a3540, emissiveIntensity: 0.2 })
    );
    gem.position.set(0.32, 0.56, 0.18);
    ringGroup.add(gem);
    ringGroup.position.set(2.5, 0.55, 0.5);
    ringGroup.scale.setScalar(1.35);
    scene.add(ringGroup);

    // ---- A light scatter of golden sparkle points around the rings/arch ----
    const sparkleCount = 40;
    const sparklePos = new Float32Array(sparkleCount * 3);
    for (let i = 0; i < sparkleCount; i++) {
        sparklePos[i * 3] = (Math.random() - 0.5) * 8;
        sparklePos[i * 3 + 1] = Math.random() * 4 - 1.5;
        sparklePos[i * 3 + 2] = (Math.random() - 0.5) * 4 - 1;
    }
    const sparkleGeo = new THREE.BufferGeometry();
    sparkleGeo.setAttribute('position', new THREE.BufferAttribute(sparklePos, 3));
    const sparkles = new THREE.Points(sparkleGeo, new THREE.PointsMaterial({ color: 0xf3d9a0, size: 0.045, transparent: true, opacity: 0.75 }));
    scene.add(sparkles);

    let mx = 0, my = 0;
    heroEl.addEventListener('mousemove', (e) => {
        const r = heroEl.getBoundingClientRect();
        mx = ((e.clientX - r.left) / r.width - 0.5);
        my = ((e.clientY - r.top) / r.height - 0.5);
    });

    let t = 0;
    function animateHero() {
        requestAnimationFrame(animateHero);
        t += 0.01;
        ringGroup.rotation.y = t * 0.55;
        ring1.rotation.z = Math.sin(t * 0.4) * 0.05;
        ring2.rotation.z = Math.cos(t * 0.4) * 0.05;
        gem.rotation.y = t * 2;
        archGroup.rotation.z = Math.sin(t * 0.15) * 0.012;
        sparkles.rotation.y = t * 0.04;
        const sp = sparkleGeo.attributes.position.array;
        for (let i = 0; i < sparkleCount; i++) { sp[i * 3 + 1] += 0.0025; if (sp[i * 3 + 1] > 3) sp[i * 3 + 1] = -1.5; }
        sparkleGeo.attributes.position.needsUpdate = true;

        camera.position.x += (mx * 0.8 - camera.position.x) * 0.03;
        camera.position.y += (0.25 - my * 0.5 - camera.position.y) * 0.03;
        camera.lookAt(0, -0.2, 0);

        renderer.render(scene, camera);
    }
    animateHero();
    requestAnimationFrame(() => canvas.classList.add('ready'));
})();

// ================= CANVAS 2D — falling flower petals, visible across the whole page =================
(function initPetals() {
    const canvas = document.getElementById('petal-canvas');
    if (!canvas || REDUCE_MOTION) return;
    const ctx = canvas.getContext('2d');
    if (!ctx) return;

    let W, H;
    function resize() {
        W = canvas.width = window.innerWidth;
        H = canvas.height = window.innerHeight;
    }
    resize();
    window.addEventListener('resize', resize);

    const petalColors = ['#c98f6b', '#b8935a', '#ded0b8', '#e7c9a0', '#9c6b4a'];
    const petalCount = window.innerWidth < 700 ? 12 : 24;
    const petals = [];

    function makePetal(seedAcrossScreen) {
        return {
            x: Math.random() * W,
            y: seedAcrossScreen ? Math.random() * H : -20 - Math.random() * 120,
            size: 6 + Math.random() * 7,
            speedY: 0.35 + Math.random() * 0.55,
            speedX: (Math.random() - 0.5) * 0.4,
            rot: Math.random() * Math.PI * 2,
            rotSpeed: (Math.random() - 0.5) * 0.02,
            sway: Math.random() * Math.PI * 2,
            swaySpeed: 0.008 + Math.random() * 0.012,
            color: petalColors[Math.floor(Math.random() * petalColors.length)],
            opacity: 0.45 + Math.random() * 0.4
        };
    }
    for (let i = 0; i < petalCount; i++) petals.push(makePetal(true));

    function drawPetal(p) {
        ctx.save();
        ctx.translate(p.x, p.y);
        ctx.rotate(p.rot);
        ctx.globalAlpha = p.opacity;
        ctx.fillStyle = p.color;
        ctx.beginPath();
        ctx.moveTo(0, -p.size);
        ctx.bezierCurveTo(p.size * 0.7, -p.size * 0.3, p.size * 0.5, p.size * 0.7, 0, p.size);
        ctx.bezierCurveTo(-p.size * 0.5, p.size * 0.7, -p.size * 0.7, -p.size * 0.3, 0, -p.size);
        ctx.fill();
        ctx.restore();
    }

    let paused = false;
    document.addEventListener('visibilitychange', () => { paused = document.hidden; });

    function loop() {
        requestAnimationFrame(loop);
        if (paused) return;
        ctx.clearRect(0, 0, W, H);
        petals.forEach(p => {
            p.y += p.speedY;
            p.sway += p.swaySpeed;
            p.x += p.speedX + Math.sin(p.sway) * 0.35;
            p.rot += p.rotSpeed;
            if (p.y > H + 20) Object.assign(p, makePetal(false));
            if (p.x > W + 20) p.x = -20;
            if (p.x < -20) p.x = W + 20;
            drawPetal(p);
        });
    }
    loop();
})();

// ================= THREE.JS — persistent constellation background =================
// This canvas is position:fixed and covers the FULL PAGE (not just the hero),
// so the same subtle animation stays visible behind every section as you scroll.
(function initPageScene() {
    const canvas = document.getElementById('page-canvas');
    if (!canvas || typeof THREE === 'undefined' || REDUCE_MOTION) return;

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

    // Drifting dots
    const count = 90;
    const positions = new Float32Array(count * 3);
    const velocities = [];
    for (let i = 0; i < count; i++) {
        positions[i * 3] = (Math.random() - 0.5) * 20;
        positions[i * 3 + 1] = (Math.random() - 0.5) * 20;
        positions[i * 3 + 2] = (Math.random() - 0.5) * 6;
        velocities.push({
            x: (Math.random() - 0.5) * 0.004,
            y: (Math.random() - 0.5) * 0.004
        });
    }
    const dotGeo = new THREE.BufferGeometry();
    dotGeo.setAttribute('position', new THREE.BufferAttribute(positions, 3));
    const dotMat = new THREE.PointsMaterial({ color: 0xb8935a, size: 0.05, transparent: true, opacity: 0.55 });
    const dots = new THREE.Points(dotGeo, dotMat);
    scene.add(dots);

    // Lines between nearby dots (constellation effect)
    const maxLines = count * 3;
    const lineGeo = new THREE.BufferGeometry();
    const linePositions = new Float32Array(maxLines * 2 * 3);
    lineGeo.setAttribute('position', new THREE.BufferAttribute(linePositions, 3));
    const lineMat = new THREE.LineBasicMaterial({ color: 0xcbb891, transparent: true, opacity: 0.14 });
    const lines = new THREE.LineSegments(lineGeo, lineMat);
    scene.add(lines);

    function updateLines() {
        const pos = dotGeo.attributes.position.array;
        let lineIdx = 0;
        const threshold = 3.2;
        for (let i = 0; i < count && lineIdx < maxLines; i++) {
            for (let j = i + 1; j < count && lineIdx < maxLines; j++) {
                const dx = pos[i*3] - pos[j*3];
                const dy = pos[i*3+1] - pos[j*3+1];
                const dz = pos[i*3+2] - pos[j*3+2];
                const dist = Math.sqrt(dx*dx + dy*dy + dz*dz);
                if (dist < threshold) {
                    linePositions[lineIdx*6] = pos[i*3];
                    linePositions[lineIdx*6+1] = pos[i*3+1];
                    linePositions[lineIdx*6+2] = pos[i*3+2];
                    linePositions[lineIdx*6+3] = pos[j*3];
                    linePositions[lineIdx*6+4] = pos[j*3+1];
                    linePositions[lineIdx*6+5] = pos[j*3+2];
                    lineIdx++;
                }
            }
        }
        // clear remaining segments
        for (let k = lineIdx; k < maxLines; k++) {
            linePositions[k*6] = 0; linePositions[k*6+1] = 0; linePositions[k*6+2] = 0;
            linePositions[k*6+3] = 0; linePositions[k*6+4] = 0; linePositions[k*6+5] = 0;
        }
        lineGeo.attributes.position.needsUpdate = true;
    }

    let scrollFrac = 0;
    window.addEventListener('scroll', () => {
        const max = document.body.scrollHeight - window.innerHeight;
        scrollFrac = max > 0 ? window.scrollY / max : 0;
    });

    // Butterflies flying upward, from bottom of the page to the top — visible behind every section
    function makeWingGeometry() {
        const shape = new THREE.Shape();
        shape.moveTo(0, 0);
        shape.bezierCurveTo(0.42, 0.32, 0.52, 0.62, 0.16, 0.78);
        shape.bezierCurveTo(-0.12, 0.6, -0.08, 0.28, 0, 0);
        return new THREE.ShapeGeometry(shape);
    }
    const wingGeo = makeWingGeometry();
    const butterflyColors = [0xb8935a, 0x8f6f42, 0xded0b8, 0xc9a96b];

    function createButterfly(color) {
        const bfly = new THREE.Group();
        const mat = new THREE.MeshBasicMaterial({ color, side: THREE.DoubleSide, transparent: true, opacity: 0.8 });
        const wingL = new THREE.Mesh(wingGeo, mat);
        wingL.rotation.z = Math.PI / 2;
        wingL.position.x = -0.02;
        const wingR = new THREE.Mesh(wingGeo, mat.clone());
        wingR.rotation.z = Math.PI / 2;
        wingR.scale.x = -1;
        wingR.position.x = 0.02;
        const body = new THREE.Mesh(
            new THREE.CylinderGeometry(0.012, 0.02, 0.24, 6),
            new THREE.MeshBasicMaterial({ color: 0x3a2a1a })
        );
        body.rotation.z = Math.PI / 2;
        bfly.add(wingL, wingR, body);
        bfly.userData.wingL = wingL;
        bfly.userData.wingR = wingR;
        return bfly;
    }

    const butterflies = [];
    const butterflyGroup = new THREE.Group();
    const butterflyCount = 14;
    for (let i = 0; i < butterflyCount; i++) {
        const bfly = createButterfly(butterflyColors[i % butterflyColors.length]);
        bfly.position.set((Math.random() - 0.5) * 14, Math.random() * 12 - 8, (Math.random() - 0.5) * 6 - 2);
        bfly.rotation.y = Math.random() * Math.PI * 2;
        bfly.scale.setScalar(0.7 + Math.random() * 0.6);
        bfly.userData.riseSpeed = 0.25 + Math.random() * 0.35;
        bfly.userData.swaySpeed = 0.5 + Math.random() * 0.8;
        bfly.userData.swayAmp = 0.6 + Math.random() * 0.8;
        bfly.userData.flapSpeed = 6 + Math.random() * 4;
        bfly.userData.baseX = bfly.position.x;
        butterflies.push(bfly);
        butterflyGroup.add(bfly);
    }
    scene.add(butterflyGroup);

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
            pos[i*3] += velocities[i].x;
            pos[i*3+1] += velocities[i].y;
            if (pos[i*3] > 10) pos[i*3] = -10;
            if (pos[i*3] < -10) pos[i*3] = 10;
            if (pos[i*3+1] > 10) pos[i*3+1] = -10;
            if (pos[i*3+1] < -10) pos[i*3+1] = 10;
        }
        dotGeo.attributes.position.needsUpdate = true;
        updateLines();

        butterflies.forEach(b => {
            b.position.y += b.userData.riseSpeed * 0.016;
            const swayX = Math.sin(t * b.userData.swaySpeed) * b.userData.swayAmp;
            b.position.x = b.userData.baseX + swayX;
            // sharper, easing-shaped flap reads more like a real wingbeat than a plain sine
            const flapRaw = Math.sin(t * b.userData.flapSpeed);
            const flap = Math.sign(flapRaw) * Math.pow(Math.abs(flapRaw), 0.6) * 1.05;
            b.userData.wingL.rotation.y = flap;
            b.userData.wingR.rotation.y = -flap;
            // bank into turns and let it wander gently instead of facing one fixed direction
            b.rotation.z = Math.cos(t * b.userData.swaySpeed) * 0.25;
            b.rotation.y += 0.0025;
            if (b.position.y > 10) { b.position.y = -10; }
        });

        // subtle parallax tied to scroll + mouse, keeps the field gently shifting through every section
        scene.rotation.z = scrollFrac * 0.15;
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
                    translateY: [24, 0],
                    easing: 'easeOutCubic',
                    duration: 800,
                    delay: 60
                });
                io.unobserve(entry.target);
            }
        });
    }, { threshold: 0.15 });
    els.forEach(el => io.observe(el));
})();
</script>
</body>
</html>