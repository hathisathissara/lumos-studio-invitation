<?php
require_once __DIR__ . '/includes/lang.php';
require_once __DIR__ . '/includes/music_library.php';
set_invite_lang($wedding['invite_language'] ?? 'en');

$hero_style = "";
if (!empty($wedding['hero_image'])) {
    $img_path = htmlspecialchars($wedding['hero_image']);
    $hero_style = "style=\"background: url('{$img_path}') center/cover no-repeat;\"";
}
$monogram = strtoupper(substr($wedding['bride_name'] ?? '', 0, 1)) . strtoupper(substr($wedding['groom_name'] ?? '', 0, 1));

// Background music (off unless the couple picked a preset in customize.php)
$music_key = $wedding['music_track'] ?? null;
$music_file = ($music_key && isset($MUSIC_LIBRARY[$music_key])) ? $MUSIC_LIBRARY[$music_key]['file'] : null;
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
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,300;9..144,500;9..144,600;9..144,700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --navy: #1c2340;
            --navy-2: #262e54;
            --plum: #7a1f2b;
            --plum-dark: #4d1219;
            --gold: #c6a15b;
            --gold-dark: #9c7c3a;
            --gold-light: #ddc48b;
            --parchment: #faf7f0;
            --parchment-2: #f1e9d8;
            --parchment-border: #d9c9a3;
            --ink: #1c2340;
            --ink-mid: #3d4566;
            --ink-light: #7a8099;
            --white: #ffffff;
        }

        html { scroll-behavior: smooth; }

        body {
            background: var(--parchment);
            font-family: 'Inter', sans-serif;
            color: var(--ink);
            min-height: 100vh;
            position: relative;
        }

        ::selection { background: var(--gold); color: var(--navy); }
        .reveal { opacity: 0; }
        .reveal-scale { opacity: 0; transform: scale(0.94) translateY(24px); }

        /* Persistent fixed background animation — visible behind every section */
        #page-canvas { position: fixed; inset: 0; width: 100%; height: 100%; z-index: 0; pointer-events: none; }
        body > * { position: relative; z-index: 1; }
        #page-canvas { z-index: 0; }

        /* Preview banner */
        .preview-bar {
            background: var(--navy);
            color: var(--parchment);
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

        /* ======= HERO : formal split layout ======= */
        .hero { display: grid; grid-template-columns: 1fr 1fr; min-height: 560px; box-shadow: 0 4px 30px rgba(77,18,25,0.2); perspective: 1400px; }
        .hero-media {
            position: relative;
            background: linear-gradient(160deg, var(--navy), var(--plum-dark));
            background-size: cover; background-position: center;
            display: flex; align-items: flex-end; justify-content: flex-start;
            min-height: 320px;
            overflow: hidden;
        }
        .hero-media::after {
            content: ''; position: absolute; inset: 0; pointer-events: none;
            background: radial-gradient(circle at 30% 20%, rgba(198,161,91,0.16), transparent 60%);
        }
        #hero-rings-canvas { position: absolute; inset: 0; width: 100%; height: 100%; z-index: 1; pointer-events: none; }
        .wax-seal {
            margin: 28px; width: 88px; height: 88px; border-radius: 50%;
            background: linear-gradient(135deg, var(--gold), var(--gold-dark));
            border: 3px solid rgba(28,35,64,0.3);
            color: var(--navy);
            display: flex; align-items: center; justify-content: center;
            font-family: 'Fraunces', serif; font-size: 1.7rem; font-weight: 700; letter-spacing: 1px;
            box-shadow: 0 10px 26px rgba(28,35,64,0.35);
            position: relative; z-index: 2;
        }
        .hero-panel {
            background: rgba(250,247,240,0.55);
            backdrop-filter: blur(14px) saturate(140%);
            -webkit-backdrop-filter: blur(14px) saturate(140%);
            border-left: 4px solid var(--gold);
            padding: 60px 44px;
            display: flex; flex-direction: column; justify-content: center;
            position: relative;
            overflow: hidden;
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.4);
            transform-style: preserve-3d;
            transition: transform 0.15s ease-out;
        }
        /* Decorative golden frame corners */
        .gold-frame { position: relative; }
        .gold-frame::before, .gold-frame::after,
        .gold-frame .gf-tr, .gold-frame .gf-bl {
            content: ''; position: absolute; width: 26px; height: 26px; z-index: 3; pointer-events: none;
            border-color: var(--gold); opacity: 0.85;
        }
        .gold-frame::before { top: 10px; left: 10px; border-top: 2px solid var(--gold); border-left: 2px solid var(--gold); }
        .gold-frame::after { bottom: 10px; right: 10px; border-bottom: 2px solid var(--gold); border-right: 2px solid var(--gold); }
        .gold-frame .gf-tr { top: 10px; right: 10px; border-top: 2px solid var(--gold); border-right: 2px solid var(--gold); }
        .gold-frame .gf-bl { bottom: 10px; left: 10px; border-bottom: 2px solid var(--gold); border-left: 2px solid var(--gold); }

        /* Animated golden light rays behind couple names */
        .light-rays-wrap { position: relative; }
        .light-rays-wrap::before {
            content: ''; position: absolute; left: 50%; top: 50%; width: 900px; height: 900px;
            transform: translate(-50%,-50%);
            background: conic-gradient(from 0deg,
                rgba(198,161,91,0) 0deg, rgba(198,161,91,0.16) 8deg, rgba(198,161,91,0) 16deg,
                rgba(198,161,91,0) 40deg, rgba(198,161,91,0.14) 48deg, rgba(198,161,91,0) 56deg,
                rgba(198,161,91,0) 90deg, rgba(198,161,91,0.16) 98deg, rgba(198,161,91,0) 106deg,
                rgba(198,161,91,0) 150deg, rgba(198,161,91,0.13) 158deg, rgba(198,161,91,0) 166deg,
                rgba(198,161,91,0) 200deg, rgba(198,161,91,0.15) 208deg, rgba(198,161,91,0) 216deg,
                rgba(198,161,91,0) 260deg, rgba(198,161,91,0.14) 268deg, rgba(198,161,91,0) 276deg,
                rgba(198,161,91,0) 320deg, rgba(198,161,91,0.16) 328deg, rgba(198,161,91,0) 336deg);
            animation: raySpin 26s linear infinite;
            z-index: 0; pointer-events: none; mix-blend-mode: multiply;
        }
        @keyframes raySpin { to { transform: translate(-50%,-50%) rotate(360deg); } }
        .light-rays-wrap > * { position: relative; z-index: 1; }
        .eyebrow { font-size: 0.72rem; font-weight: 600; letter-spacing: 2.5px; text-transform: uppercase; color: var(--gold-dark); margin-bottom: 14px; }
        .guest-line { font-family: 'Fraunces', serif; font-style: italic; font-weight: 400; font-size: clamp(1.2rem, 2.4vw, 1.6rem); color: var(--ink-mid); margin-bottom: 22px; }

        .reserved-note {
            margin: 0 0 26px;
            background: var(--parchment-2);
            border-left: 3px solid var(--navy);
            padding: 10px 16px;
            display: inline-flex; align-items: center; gap: 10px;
            color: var(--plum-dark); font-size: 0.86rem; font-weight: 600; max-width: 100%;
        }
        .reserved-note i { color: var(--navy); width: 16px; }

        .couple-title { font-family: 'Fraunces', serif; font-weight: 600; font-size: clamp(2.4rem, 5.5vw, 4rem); line-height: 1.1; color: var(--navy); margin-bottom: 24px; }
        .couple-title .amp { display: block; font-style: italic; font-weight: 400; font-size: 0.5em; color: var(--plum-dark); margin: 2px 0; }
        .date-chip {
            display: inline-flex; flex-direction: column; gap: 2px;
            border: 2px solid var(--navy);
            padding: 12px 18px;
            width: fit-content;
        }
        .date-chip .lbl { font-size: 0.65rem; letter-spacing: 2px; text-transform: uppercase; color: var(--ink-light); }
        .date-chip .val { font-family: 'Fraunces', serif; font-size: 1.2rem; font-weight: 700; color: var(--navy); }

        .hero-venue { margin-top: 16px; font-size: 0.92rem; color: var(--gold-dark); display: flex; align-items: center; gap: 8px; }

        @media (max-width: 760px) {
            .hero { grid-template-columns: 1fr; }
            .hero-media { min-height: 240px; }
            .hero-panel { padding: 40px 24px; }
        }

        /* ======= COUNTDOWN : 3D flip cards ======= */
        .countdown-section { position: relative; background: rgba(28,35,64,0.85); backdrop-filter: blur(3px); -webkit-backdrop-filter: blur(3px); padding: 26px 20px 40px; text-align: center; overflow: hidden; }
        .countdown-label { font-size: 0.68rem; letter-spacing: 4px; text-transform: uppercase; color: rgba(198,161,91,0.7); margin-bottom: 18px; }
        .countdown { display: flex; justify-content: center; align-items: center; gap: 16px; flex-wrap: wrap; perspective: 800px; }
        .time-unit { text-align: center; min-width: 66px; }
        .flip-card { width: 66px; height: 62px; position: relative; }
        .flip-card-inner {
            position: relative; width: 100%; height: 100%; transform-style: preserve-3d;
            transition: transform 0.6s cubic-bezier(0.45,0.05,0.55,0.95);
            background: linear-gradient(160deg, rgba(198,161,91,0.14), rgba(198,161,91,0.03));
            border: 1px solid rgba(198,161,91,0.4);
            box-shadow: 0 8px 20px rgba(0,0,0,0.25), inset 0 1px 0 rgba(255,255,255,0.08);
        }
        .flip-card.flipping .flip-card-inner { transform: rotateX(-180deg); }
        .flip-face {
            position: absolute; inset: 0; backface-visibility: hidden; -webkit-backface-visibility: hidden;
            display: flex; align-items: center; justify-content: center;
            font-family: 'Fraunces', serif; font-size: clamp(1.5rem, 4.5vw, 2rem); font-weight: 600; color: var(--gold);
        }
        .flip-face.back { transform: rotateX(180deg); }
        .time-label { font-size: 0.6rem; letter-spacing: 1.5px; text-transform: uppercase; color: rgba(198,161,91,0.5); margin-top: 8px; display: block; }
        .time-sep { font-size: 1.4rem; color: rgba(198,161,91,0.35); margin-bottom: 22px; }
        .just-married-msg { font-family: 'Fraunces', serif; font-size: 2rem; font-style: italic; color: var(--gold); }
        #countdown-cake { position: absolute; right: 18px; bottom: 8px; width: 70px; height: 70px; opacity: 0.9; pointer-events: none; }
        @media (max-width: 560px) { #countdown-cake { display: none; } }

        /* ======= BODY ======= */
        .invitation-body { max-width: 720px; margin: 0 auto; padding: 0 20px; }
        .section-head { text-align: center; margin: 56px 0 34px; }
        .section-head .tag { font-size: 0.7rem; letter-spacing: 2.5px; text-transform: uppercase; color: var(--gold-dark); display: block; margin-bottom: 8px; }
        .section-head h2 { font-family: 'Fraunces', serif; font-weight: 600; font-size: clamp(1.8rem, 5vw, 2.4rem); color: var(--ink); }
        .section-head h2 em { font-style: italic; color: var(--plum-dark); }

        /* Love story: formal ledger card */
        .letter-card {
            background: rgba(255,255,255,0.55);
            backdrop-filter: blur(10px) saturate(140%);
            -webkit-backdrop-filter: blur(10px) saturate(140%);
            border-top: 3px solid var(--navy);
            border-bottom: 3px solid var(--navy);
            padding: 36px 32px;
            position: relative;
            font-family: 'Fraunces', serif;
            font-style: italic;
            font-size: 1.1rem;
            line-height: 1.9;
            color: var(--ink-mid);
            box-shadow: 0 14px 34px rgba(28,35,64,0.06);
        }
        .letter-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 5px; background: var(--gold); }

        /* Programme: formal vertical timeline */
        .timeline { position: relative; padding-left: 68px; }
        .timeline::before { content: ''; position: absolute; left: 27px; top: 6px; bottom: 6px; width: 2px; background: repeating-linear-gradient(to bottom, var(--parchment-border) 0 6px, transparent 6px 12px); }
        .tl-item { position: relative; margin-bottom: 26px; }
        .tl-marker {
            position: absolute; left: -68px; top: 0; width: 56px; height: 56px;
            background: var(--navy); border: 2px solid var(--gold);
            color: var(--parchment);
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            font-family: 'Fraunces', serif; line-height: 1.1;
        }
        .tl-marker .d { font-size: 1.3rem; font-weight: 600; }
        .tl-marker .m { font-size: 0.6rem; letter-spacing: 1px; text-transform: uppercase; }
        .tl-card { background: rgba(255,255,255,0.6); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); border: 1px solid rgba(28,35,64,0.15); border-bottom: 3px solid var(--navy); padding: 22px 24px; transition: transform 0.3s ease, box-shadow 0.3s ease; }
        .tl-card:hover { transform: translateY(-3px); box-shadow: 0 16px 30px rgba(28,35,64,0.12); }
        .tl-name { font-family: 'Fraunces', serif; font-weight: 600; font-size: 1.35rem; color: var(--ink); margin-bottom: 10px; }
        .tl-meta { display: flex; flex-direction: column; gap: 5px; margin-bottom: 14px; }
        .tl-meta-item { display: flex; align-items: center; gap: 9px; font-size: 0.86rem; color: var(--ink-mid); }
        .tl-meta-item i { color: var(--plum); width: 15px; text-align: center; }
        .tl-actions { display: flex; gap: 9px; flex-wrap: wrap; margin-top: 14px; }
        .btn-map {
            display: inline-flex; align-items: center; gap: 6px;
            background: var(--navy); color: white; text-decoration: none;
            padding: 8px 16px; font-size: 0.78rem; font-weight: 600; transition: all 0.2s;
        }
        .btn-map:hover { background: var(--plum-dark); color: white; }
        .btn-cal {
            display: inline-flex; align-items: center; gap: 6px;
            background: transparent; border: 1px solid var(--parchment-border);
            color: var(--ink-mid); text-decoration: none;
            padding: 8px 16px; font-size: 0.78rem; font-weight: 500; cursor: pointer; transition: all 0.2s;
        }
        .btn-cal:hover { border-color: var(--gold); color: var(--gold-dark); }
        .cal-dropdown { position: relative; display: inline-block; }
        .cal-menu { display: none; position: absolute; bottom: calc(100% + 8px); left: 0; background: white; border: 1px solid var(--parchment-border); box-shadow: 0 10px 30px rgba(28,35,64,0.12); min-width: 180px; z-index: 10; overflow: hidden; }
        .cal-menu.open { display: block; }
        .cal-menu a { display: flex; align-items: center; gap: 10px; padding: 11px 15px; font-size: 0.82rem; color: var(--ink-mid); text-decoration: none; transition: background 0.15s; }
        .cal-menu a:hover { background: var(--parchment-2); color: var(--gold-dark); }
        .cal-menu a i { width: 16px; text-align: center; }

        /* Gallery: 3D coverflow slider */
        .coverflow-wrap { position: relative; padding: 20px 0 46px; }
        .coverflow-viewport { perspective: 1200px; overflow: hidden; padding: 30px 0; }
        .coverflow-track {
            display: flex; align-items: center; justify-content: center;
            min-height: 260px; position: relative; transform-style: preserve-3d;
            touch-action: pan-y;
        }
        .coverflow-slide {
            position: absolute; width: 210px; height: 210px; cursor: pointer;
            transition: transform 0.55s cubic-bezier(0.22,0.61,0.36,1), opacity 0.55s ease, filter 0.55s ease;
            transform-style: preserve-3d;
        }
        .coverflow-slide .cf-frame {
            position: relative; width: 100%; height: 100%; background: white;
            padding: 8px; border: 1px solid rgba(198,161,91,0.5);
            box-shadow: 0 20px 40px rgba(28,35,64,0.25);
        }
        .coverflow-slide img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .coverflow-nav {
            position: absolute; top: 50%; transform: translateY(-50%); z-index: 5;
            width: 42px; height: 42px; border-radius: 50%; border: 1px solid var(--gold);
            background: rgba(255,255,255,0.8); color: var(--gold-dark);
            display: flex; align-items: center; justify-content: center; cursor: pointer;
            transition: all 0.2s;
        }
        .coverflow-nav:hover { background: var(--gold); color: white; }
        .coverflow-nav.prev { left: 4px; } .coverflow-nav.next { right: 4px; }
        .coverflow-dots { display: flex; justify-content: center; gap: 7px; margin-top: 18px; }
        .coverflow-dots span { width: 7px; height: 7px; border-radius: 50%; background: rgba(198,161,91,0.3); cursor: pointer; transition: background 0.2s, transform 0.2s; }
        .coverflow-dots span.active { background: var(--gold); transform: scale(1.3); }
        @media (max-width: 560px) { .coverflow-slide { width: 160px; height: 160px; } }

        /* Lightbox */
        .lightbox { display: none; position: fixed; inset: 0; background: rgba(28,35,64,0.9); z-index: 1000; align-items: center; justify-content: center; }
        .lightbox.open { display: flex; }
        .lightbox img { max-width: 90vw; max-height: 90vh; border-radius: 6px; object-fit: contain; }
        .lightbox-close { position: absolute; top: 20px; right: 20px; color: white; font-size: 1.8rem; cursor: pointer; opacity: 0.75; transition: opacity 0.2s; }
        .lightbox-close:hover { opacity: 1; }

        /* Guest shared gallery */
        .section-divider { display: flex; align-items: center; gap: 16px; max-width: 720px; margin: 56px auto 0; padding: 0 20px; }
        .section-divider-line { flex: 1; height: 1px; background: linear-gradient(to right, transparent, var(--gold)); }
        .section-divider-line.right { background: linear-gradient(to left, transparent, var(--gold)); }
        .section-divider-icon { width: 40px; height: 40px; border: 1px solid var(--gold); display: flex; align-items: center; justify-content: center; color: var(--gold-dark); flex-shrink: 0; }
        .section-heading { text-align: center; font-family: 'Fraunces', serif; font-weight: 600; font-size: clamp(1.8rem, 5vw, 2.4rem); color: var(--ink); margin-top: 18px; }
        .section-heading em { font-style: italic; color: var(--plum-dark); }
        .section-sub { text-align: center; color: var(--ink-mid); font-size: 0.9rem; margin: 6px 0 26px; max-width: 720px; margin-left: auto; margin-right: auto; padding: 0 20px; }

        .gallery-grid {
            display: flex; flex-wrap: nowrap; overflow-x: auto; gap: 15px; padding: 10px 0;
            scroll-behavior: smooth; -webkit-overflow-scrolling: touch;
            scrollbar-width: none; -ms-overflow-style: none;
        }
        .gallery-grid::-webkit-scrollbar { display: none; }
        .gallery-item { flex: 0 0 auto; width: 240px; height: 240px; overflow: hidden; position: relative; cursor: pointer; border: 2px solid var(--parchment-border); box-shadow: 0 10px 24px rgba(28,35,64,0.12); transition: transform 0.3s; }
        .gallery-item:hover { transform: translateY(-4px); }
        .gallery-item img { width: 100%; height: 100%; object-fit: cover; display: block; }

        /* ======= RSVP : formal two column card ======= */
        .rsvp-section { background: rgba(241,233,216,0.6); backdrop-filter: blur(3px); -webkit-backdrop-filter: blur(3px); border-top: 1px solid var(--parchment-border); padding: 60px 20px; }
        .rsvp-card {
            max-width: 780px; margin: 0 auto; background: rgba(255,255,255,0.72);
            backdrop-filter: blur(12px) saturate(140%);
            -webkit-backdrop-filter: blur(12px) saturate(140%);
            border: 2px solid var(--navy);
            overflow: hidden; display: grid; grid-template-columns: 0.85fr 1.15fr;
            box-shadow: 0 16px 44px rgba(28,35,64,0.1);
            position: relative;
        }
        #rsvp-hearts-layer { position: absolute; inset: 0; pointer-events: none; z-index: 5; overflow: visible; }
        .flying-heart { position: absolute; bottom: 10%; font-size: 1.4rem; color: var(--plum); opacity: 0.95; animation: heartFloat 2.6s ease-in forwards; }
        @keyframes heartFloat {
            0% { transform: translate(0,0) scale(0.6) rotate(0deg); opacity: 0; }
            10% { opacity: 1; }
            100% { transform: translate(var(--hx,20px), -260px) scale(1.1) rotate(var(--hr,20deg)); opacity: 0; }
        }
        .rsvp-aside { background: var(--navy); color: var(--parchment); padding: 44px 34px; display: flex; flex-direction: column; justify-content: center; }
        .rsvp-aside .quote-mark { font-family: 'Fraunces', serif; font-size: 3rem; color: var(--gold); opacity: 0.7; line-height: 1; margin-bottom: 10px; }
        .rsvp-aside p { font-family: 'Fraunces', serif; font-style: italic; font-size: 1.15rem; line-height: 1.7; }
        .rsvp-form-side { padding: 44px 34px; }
        .rsvp-title { font-family: 'Fraunces', serif; font-weight: 600; font-size: 1.9rem; color: var(--ink); margin-bottom: 4px; }
        .rsvp-subtitle { font-size: 0.78rem; color: var(--ink-light); letter-spacing: 1px; text-transform: uppercase; margin-bottom: 24px; }
        .rsvp-options { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 18px; }
        .rsvp-option input[type="radio"] { display: none; }
        .rsvp-option label { display: flex; flex-direction: column; align-items: center; gap: 7px; padding: 16px 10px; border: 2px solid var(--parchment-border); cursor: pointer; font-size: 0.8rem; font-weight: 500; color: var(--ink-mid); transition: all 0.2s; }
        .rsvp-option label i { font-size: 1.3rem; }
        .rsvp-option:first-child input[type="radio"]:checked + label { border-color: var(--navy); background: rgba(28,35,64,0.06); color: var(--navy); }
        .rsvp-option:last-child input[type="radio"]:checked + label { border-color: var(--plum); background: rgba(122,31,43,0.08); color: var(--plum-dark); }
        .rsvp-note { width: 100%; background: var(--parchment); border: 1px solid var(--parchment-border); padding: 13px 15px; font-family: 'Inter', sans-serif; font-size: 0.86rem; color: var(--ink-mid); resize: none; outline: none; transition: border-color 0.2s; margin-bottom: 16px; }
        .rsvp-note:focus { border-color: var(--gold); }
        .btn-rsvp-submit { width: 100%; background: var(--navy); color: var(--parchment); border: none; padding: 15px; font-size: 0.86rem; font-weight: 700; letter-spacing: 4px; text-transform: uppercase; cursor: pointer; transition: all 0.25s; }
        .btn-rsvp-submit:hover { background: var(--plum-dark); transform: translateY(-2px); }
        @media (max-width: 640px) { .rsvp-card { grid-template-columns: 1fr; } .rsvp-aside { padding: 30px 26px; } .rsvp-form-side { padding: 30px 26px; } }

        /* Flying doves — triggered on scroll */
        #dove-layer { position: fixed; inset: 0; z-index: 2; pointer-events: none; overflow: hidden; }
        .dove {
            position: absolute; top: var(--dt, 20%); left: -80px; width: 46px; height: auto;
            opacity: 0; color: rgba(198,161,91,0.75);
            filter: drop-shadow(0 2px 6px rgba(28,35,64,0.15));
        }
        .dove.flying {
            animation: doveFly 5.5s ease-in-out forwards;
        }
        @keyframes doveFly {
            0% { opacity: 0; transform: translate(0, 0) scale(0.8); }
            8% { opacity: 0.9; }
            50% { transform: translate(55vw, -40px) scale(1); }
            92% { opacity: 0.85; }
            100% { opacity: 0; transform: translate(115vw, -90px) scale(0.9); }
        }
        .dove svg { width: 100%; height: 100%; animation: doveFlap 0.5s ease-in-out infinite alternate; }
        @keyframes doveFlap { from { transform: scaleY(1); } to { transform: scaleY(0.72); } }

        /* ======= FOOTER ======= */
        .inv-footer { text-align: center; padding: 40px 20px; font-size: 0.75rem; color: rgba(198,161,91,0.5); border-top: 1px solid var(--parchment-border); background: var(--navy); }
        .inv-footer .brand { font-family: 'Fraunces', serif; font-style: italic; font-size: 1.3rem; color: var(--gold); display: block; margin-bottom: 6px; }
    </style>
</head>
<body>

<canvas id="page-canvas"></canvas>
<div id="dove-layer"></div>

<?php if ($music_file): ?>
<audio id="bg-music" src="<?php echo htmlspecialchars($music_file); ?>" loop preload="none"></audio>
<button type="button" id="music-toggle" aria-label="Toggle background music"
    style="position:fixed; bottom:22px; right:22px; z-index:1000; width:48px; height:48px; border-radius:50%;
    background:var(--plum-dark); color:var(--parchment); border:none; box-shadow:0 8px 20px rgba(161,88,115,0.35);
    display:flex; align-items:center; justify-content:center; font-size:1.1rem; cursor:pointer;">
    <i class="fas fa-music"></i>
</button>
<script>
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
</script>
<?php endif; ?>

<?php if ($guest_id == 0): ?>
<div class="preview-bar">
    <i class="fas fa-eye"></i>
    <strong><?php echo t('preview_mode_label'); ?></strong> — <?php echo t('preview_mode_note'); ?>
    <a href="dashboard/index.php"><?php echo t('back_to_dashboard'); ?></a>
</div>
<?php endif; ?>

<?php if ($msg): ?>
<div style="max-width:720px; margin:20px auto; padding:0 20px;">
    <?php echo $msg; ?>
</div>
<?php endif; ?>

<!-- HERO -->
<div class="hero reveal" id="hero-root">
    <div class="hero-media" <?php echo $hero_style; ?>>
        <canvas id="hero-rings-canvas"></canvas>
        <div class="wax-seal"><?php echo htmlspecialchars($monogram); ?></div>
    </div>
    <div class="hero-panel gold-frame" id="hero-panel">
        <span class="gf-tr"></span><span class="gf-bl"></span>
        <span class="eyebrow"><?php echo t('hero_eyebrow'); ?></span>
        <p class="guest-line"><?php echo t('hero_dear'); ?> <?php echo htmlspecialchars($guest_name); ?>,</p>

        <div class="light-rays-wrap">
            <h1 class="couple-title">
                <?php echo htmlspecialchars($wedding['bride_name']); ?>
                <span class="amp">&amp;</span>
                <?php echo htmlspecialchars($wedding['groom_name']); ?>
            </h1>
        </div>

        <div class="date-chip">
            <span class="lbl"><?php echo t('hero_getting_married'); ?></span>
            <span class="val"><?php echo t_date($wedding['wedding_date']); ?></span>
        </div>

        <?php if (!empty($wedding['venue'])): ?>
        <p class="hero-venue"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($wedding['venue']); ?></p>
        <?php endif; ?>
    </div>
</div>

<!-- COUNTDOWN -->
<div class="countdown-section">
    <p class="countdown-label"><?php echo t('countdown_label'); ?></p>
    <div class="countdown" id="countdown">
        <div class="time-unit">
            <div class="flip-card" id="fc-days"><div class="flip-card-inner">
                <div class="flip-face front" id="cd-days">00</div>
                <div class="flip-face back" id="cd-days-back">00</div>
            </div></div>
            <span class="time-label"><?php echo t('cd_days'); ?></span>
        </div>
        <span class="time-sep">:</span>
        <div class="time-unit">
            <div class="flip-card" id="fc-hours"><div class="flip-card-inner">
                <div class="flip-face front" id="cd-hours">00</div>
                <div class="flip-face back" id="cd-hours-back">00</div>
            </div></div>
            <span class="time-label"><?php echo t('cd_hours'); ?></span>
        </div>
        <span class="time-sep">:</span>
        <div class="time-unit">
            <div class="flip-card" id="fc-mins"><div class="flip-card-inner">
                <div class="flip-face front" id="cd-mins">00</div>
                <div class="flip-face back" id="cd-mins-back">00</div>
            </div></div>
            <span class="time-label"><?php echo t('cd_mins'); ?></span>
        </div>
        <span class="time-sep">:</span>
        <div class="time-unit">
            <div class="flip-card" id="fc-secs"><div class="flip-card-inner">
                <div class="flip-face front" id="cd-secs">00</div>
                <div class="flip-face back" id="cd-secs-back">00</div>
            </div></div>
            <span class="time-label"><?php echo t('cd_secs'); ?></span>
        </div>
    </div>
    <svg id="countdown-cake" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
        <ellipse cx="32" cy="54" rx="22" ry="5" fill="#c6a15b" opacity="0.25"/>
        <rect x="12" y="40" width="40" height="12" rx="2" fill="#f1e9d8"/>
        <rect x="16" y="28" width="32" height="12" rx="2" fill="#faf7f0"/>
        <rect x="20" y="17" width="24" height="11" rx="2" fill="#f1e9d8"/>
        <rect x="12" y="40" width="40" height="3" fill="#c6a15b" opacity="0.6"/>
        <rect x="16" y="28" width="32" height="3" fill="#c6a15b" opacity="0.6"/>
        <rect x="20" y="17" width="24" height="3" fill="#c6a15b" opacity="0.6"/>
        <rect x="30" y="8" width="4" height="9" rx="1.5" fill="#9c7c3a"/>
        <path id="cake-flame" d="M32 2c2 3 3 5 0 8-3-3-2-5 0-8z" fill="#e8b84b"/>
    </svg>
</div>

<div class="invitation-body container py-4">

    <!-- LOVE STORY -->
    <?php if (!empty($wedding['love_story'])): ?>
    <div class="section-head reveal">
        <span class="tag"><?php echo t('love_story_tag'); ?></span>
        <h2><?php echo t('love_story_title'); ?></h2>
    </div>
    <div class="letter-card reveal">
        <?php echo nl2br(htmlspecialchars($wedding['love_story'])); ?>
    </div>
    <?php endif; ?>

    <!-- PROGRAMME -->
    <div class="section-head reveal">
        <span class="tag"><?php echo t('programme_tag'); ?></span>
        <h2><?php echo t('programme_title'); ?></h2>
    </div>

    <?php if (count($wedding_events) > 0): ?>
        <div class="timeline">
            <?php foreach ($wedding_events as $ev):
                $ev_start = date('Ymd\THis', strtotime($ev['event_date_time']));
                $ev_end = date('Ymd\THis', strtotime($ev['event_date_time']) + 7200);
                $ev_title = urlencode($ev['event_name'] . ' — ' . $wedding['bride_name'] . ' & ' . $wedding['groom_name']);
                $ev_loc = urlencode($ev['location_name']);
                $ev_gcal = "https://calendar.google.com/calendar/render?action=TEMPLATE&text={$ev_title}&dates={$ev_start}/{$ev_end}&location={$ev_loc}";
                $ev_ics = "calendar.php?wedding_id={$wedding_id}&event_id={$ev['id']}";
                $ev_outlook = "https://outlook.live.com/calendar/0/deeplink/compose?path=/calendar/action/compose&rru=addevent&subject=" . $ev_title . "&startdt=" . urlencode(date('c', strtotime($ev['event_date_time']))) . "&enddt=" . urlencode(date('c', strtotime($ev['event_date_time']) + 7200)) . "&location=" . $ev_loc;
            ?>
            <div class="tl-item reveal">
                <div class="tl-marker">
                    <span class="d"><?php echo date('d', strtotime($ev['event_date_time'])); ?></span>
                    <span class="m"><?php echo t_month($ev['event_date_time']); ?></span>
                </div>
                <div class="tl-card">
                    <div class="tl-name"><?php echo htmlspecialchars($ev['event_name']); ?></div>
                    <div class="tl-meta">
                        <div class="tl-meta-item"><i class="far fa-clock"></i><span><?php echo t_time($ev['event_date_time']); ?></span></div>
                        <div class="tl-meta-item"><i class="fas fa-map-marker-alt"></i><span><?php echo htmlspecialchars($ev['location_name']); ?></span></div>
                    </div>
                    <div class="tl-actions">
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
                                <a href="<?php echo $ev_gcal; ?>" target="_blank" rel="noopener"><i class="fab fa-google" style="color:#4285f4;"></i> Google Calendar</a>
                                <a href="<?php echo $ev_ics; ?>" download><i class="fab fa-apple" style="color:#555;"></i> Apple Calendar</a>
                                <a href="<?php echo $ev_outlook; ?>" target="_blank" rel="noopener"><i class="fas fa-envelope" style="color:#0072c6;"></i> Outlook</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p style="text-align:center; color:var(--ink-light); font-style:italic; padding:30px 0;"><?php echo t('event_details_soon'); ?></p>
    <?php endif; ?>

    <!-- GALLERY -->
    <?php if (count($gallery_images) > 0): ?>
    <div class="section-head reveal">
        <span class="tag"><?php echo t('gallery_tag'); ?></span>
        <h2><?php echo t('gallery_title'); ?></h2>
    </div>
    <div class="coverflow-wrap reveal-scale" id="gallery-grid">
        <div class="coverflow-viewport">
            <div class="coverflow-nav prev" onclick="coverflowMove(-1)"><i class="fas fa-chevron-left"></i></div>
            <div class="coverflow-track" id="coverflow-track">
                <?php $cf_i = 0; foreach ($gallery_images as $img): ?>
                <div class="coverflow-slide" data-index="<?php echo $cf_i; ?>" onclick="coverflowClick(<?php echo $cf_i; ?>, '<?php echo htmlspecialchars($img['image_path']); ?>')">
                    <div class="cf-frame">
                        <span class="gf-tr" style="border-top:1px solid var(--gold);border-right:1px solid var(--gold);width:14px;height:14px;top:4px;right:4px;"></span>
                        <span class="gf-bl" style="border-bottom:1px solid var(--gold);border-left:1px solid var(--gold);width:14px;height:14px;bottom:4px;left:4px;"></span>
                        <img src="<?php echo htmlspecialchars($img['image_path']); ?>" alt="Our moment" loading="lazy">
                    </div>
                </div>
                <?php $cf_i++; endforeach; ?>
            </div>
            <div class="coverflow-nav next" onclick="coverflowMove(1)"><i class="fas fa-chevron-right"></i></div>
        </div>
        <div class="coverflow-dots" id="coverflow-dots"></div>
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
    <div style="max-width:720px; margin:0 auto 30px; background: rgba(241,233,216,0.7); border: 2px dashed var(--gold); border-radius: 20px; padding: 30px 20px; text-align: center;">
        <?php if ($guest_id == 0): ?>
            <p class="text-muted small"><i class="fas fa-lock"></i> <?php echo t('upload_disabled_preview'); ?></p>
        <?php else: ?>
            <i class="fas fa-camera-retro" style="font-size: 2.2rem; color: var(--gold-dark); margin-bottom: 12px; display: block;"></i>
            <h5 class="fw-bold" style="font-family:'Inter', sans-serif; font-size: 0.95rem; color: var(--navy); margin-bottom: 6px;"><?php echo t('share_photo_heading'); ?></h5>
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
    <div class="gallery-grid" id="guest-gallery-grid" style="max-width:720px; margin:0 auto 30px; padding-left:20px; padding-right:20px;">
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
                            grid.scrollTo({ left: grid.scrollWidth, behavior: 'smooth' });
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
    <div class="rsvp-card reveal-scale">
        <div id="rsvp-hearts-layer"></div>
        <div class="rsvp-aside">
            <span class="quote-mark">&ldquo;</span>
            <p><?php echo t('rsvp_quote'); ?></p>
        </div>
        <div class="rsvp-form-side">
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
                <?php if ($current_guest['rsvp_status'] == 'accepted'): ?>
                <script>window.__justAccepted = true;</script>
                <?php endif; ?>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="rsvp-options">
                    <div class="rsvp-option">
                        <input type="radio" name="rsvp_status" id="rsvp-yes" value="accepted" onchange="triggerHeartBurst()"
                            <?php if ($current_guest['rsvp_status'] == 'accepted') echo 'checked'; ?> required>
                        <label for="rsvp-yes">
                            <i class="fas fa-heart" style="color:#1c2340;"></i>
                            <?php echo t('rsvp_accept'); ?>
                        </label>
                    </div>
                    <div class="rsvp-option">
                        <input type="radio" name="rsvp_status" id="rsvp-no" value="rejected"
                            <?php if ($current_guest['rsvp_status'] == 'rejected') echo 'checked'; ?>>
                        <label for="rsvp-no">
                            <i class="fas fa-heart-broken" style="color:#7a1f2b;"></i>
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
</div>

<!-- FOOTER -->
<div class="inv-footer">
    <span class="brand">Lumos Studio</span>
    Digital Wedding Invitations · Designed by Hathisa Thissara
</div>

<!-- Three.js (persistent page-wide animation) + anime.js (scroll reveals) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/loaders/GLTFLoader.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/animejs/3.2.1/anime.min.js"></script>

<script>
// Countdown
const weddingDate = new Date("<?php echo $wedding['wedding_date']; ?> 00:00:00").getTime();
const __cdPrev = { days: null, hours: null, mins: null, secs: null };
function flipUnit(unit, value) {
    const val = String(value).padStart(2, '0');
    if (__cdPrev[unit] === val) return;
    __cdPrev[unit] = val;
    const card = document.getElementById('fc-' + unit);
    const front = document.getElementById('cd-' + unit);
    const back = document.getElementById('cd-' + unit + '-back');
    if (!card || !front || !back) return;
    back.textContent = val;
    card.classList.add('flipping');
    setTimeout(() => {
        front.textContent = val;
        card.classList.remove('flipping');
    }, 600);
}
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
    flipUnit('days', d);
    flipUnit('hours', h);
    flipUnit('mins', m);
    flipUnit('secs', s);
}
tick();
setInterval(tick, 1000);

// ================= Floating hearts on RSVP accept =================
function triggerHeartBurst() {
    const layer = document.getElementById('rsvp-hearts-layer');
    if (!layer) return;
    const count = 14;
    for (let i = 0; i < count; i++) {
        setTimeout(() => {
            const h = document.createElement('i');
            h.className = 'fas fa-heart flying-heart';
            const startX = 20 + Math.random() * 60;
            const drift = (Math.random() - 0.5) * 160;
            const rot = (Math.random() - 0.5) * 60;
            h.style.left = startX + '%';
            h.style.setProperty('--hx', drift + 'px');
            h.style.setProperty('--hr', rot + 'deg');
            h.style.color = Math.random() > 0.5 ? 'var(--plum)' : 'var(--gold-dark)';
            h.style.fontSize = (1 + Math.random() * 0.9) + 'rem';
            layer.appendChild(h);
            setTimeout(() => h.remove(), 2700);
        }, i * 70);
    }
}
window.addEventListener('load', () => {
    if (window.__justAccepted) {
        setTimeout(triggerHeartBurst, 900);
    }
});

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

// ================= Guest gallery auto-scroll (replaces scrollbar) =================
(function autoplayGuestGallery() {
    const track = document.getElementById('guest-gallery-grid');
    if (!track) return;
    let timer = null;
    function step() {
        const atEnd = track.scrollLeft + track.clientWidth >= track.scrollWidth - 5;
        if (atEnd) {
            track.scrollTo({ left: 0, behavior: 'smooth' });
        } else {
            track.scrollBy({ left: 260, behavior: 'smooth' });
        }
    }
    function start() { if (track.scrollWidth > track.clientWidth + 10) timer = setInterval(step, 2800); }
    function stop() { clearInterval(timer); }
    track.addEventListener('mouseenter', stop);
    track.addEventListener('mouseleave', start);
    track.addEventListener('touchstart', stop, { passive: true });
    track.addEventListener('touchend', start);
    start();
})();

// ================= THREE.JS — persistent formal grid background =================
// This canvas is position:fixed and covers the FULL PAGE (not just the hero),
// so the same subtle animation stays visible behind every section as you scroll.
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

    // Formal drifting diamond markers (small squares rotated 45deg) — sharper, more architectural than organic dots
    const goldMat = new THREE.MeshBasicMaterial({ color: 0xc6a15b, transparent: true, opacity: 0.5, side: THREE.DoubleSide });
    const diamondGeo = new THREE.PlaneGeometry(0.16, 0.16);
    const count = 70;
    const group = new THREE.Group();
    const items = [];
    for (let i = 0; i < count; i++) {
        const mesh = new THREE.Mesh(diamondGeo, goldMat.clone());
        mesh.position.set((Math.random() - 0.5) * 20, (Math.random() - 0.5) * 20, (Math.random() - 0.5) * 6);
        mesh.rotation.z = Math.PI / 4;
        mesh.userData = { speed: 0.003 + Math.random() * 0.006, sway: Math.random() * Math.PI * 2 };
        items.push(mesh);
        group.add(mesh);
    }
    scene.add(group);

    // Thin straight connecting lines between nearby markers (architectural network)
    const maxLines = count * 2;
    const lineGeo = new THREE.BufferGeometry();
    const linePositions = new Float32Array(maxLines * 2 * 3);
    lineGeo.setAttribute('position', new THREE.BufferAttribute(linePositions, 3));
    const lineMat = new THREE.LineBasicMaterial({ color: 0x7a1f2b, transparent: true, opacity: 0.12 });
    const lines = new THREE.LineSegments(lineGeo, lineMat);
    scene.add(lines);

    function updateLines() {
        let lineIdx = 0;
        const threshold = 3.4;
        for (let i = 0; i < count && lineIdx < maxLines; i++) {
            for (let j = i + 1; j < count && lineIdx < maxLines; j++) {
                const a = items[i].position, b = items[j].position;
                const dist = a.distanceTo(b);
                if (dist < threshold) {
                    linePositions[lineIdx*6] = a.x; linePositions[lineIdx*6+1] = a.y; linePositions[lineIdx*6+2] = a.z;
                    linePositions[lineIdx*6+3] = b.x; linePositions[lineIdx*6+4] = b.y; linePositions[lineIdx*6+5] = b.z;
                    lineIdx++;
                }
            }
        }
        for (let k = lineIdx; k < maxLines; k++) {
            linePositions[k*6]=0; linePositions[k*6+1]=0; linePositions[k*6+2]=0;
            linePositions[k*6+3]=0; linePositions[k*6+4]=0; linePositions[k*6+5]=0;
        }
        lineGeo.attributes.position.needsUpdate = true;
    }

    // ---- Floating rose petals ----
    function makePetalTexture() {
        const c = document.createElement('canvas'); c.width = 64; c.height = 64;
        const ctx = c.getContext('2d');
        const grad = ctx.createRadialGradient(32, 24, 2, 32, 32, 32);
        grad.addColorStop(0, 'rgba(230,150,160,0.95)');
        grad.addColorStop(0.6, 'rgba(160,40,60,0.75)');
        grad.addColorStop(1, 'rgba(122,31,43,0)');
        ctx.fillStyle = grad;
        ctx.beginPath();
        ctx.ellipse(32, 32, 22, 27, 0, 0, Math.PI * 2);
        ctx.fill();
        return new THREE.CanvasTexture(c);
    }
    const petalTex = makePetalTexture();
    const petalCount = 22;
    const petals = [];
    const petalGroup = new THREE.Group();
    for (let i = 0; i < petalCount; i++) {
        const mat = new THREE.SpriteMaterial({ map: petalTex, transparent: true, opacity: 0.55 + Math.random() * 0.3, depthWrite: false });
        const sprite = new THREE.Sprite(mat);
        const scale = 0.35 + Math.random() * 0.5;
        sprite.scale.set(scale, scale * 1.1, 1);
        sprite.position.set((Math.random() - 0.5) * 22, (Math.random() - 0.5) * 22 + 6, (Math.random() - 0.5) * 8 - 2);
        sprite.material.rotation = Math.random() * Math.PI * 2;
        sprite.userData = {
            fall: 0.006 + Math.random() * 0.01,
            sway: Math.random() * Math.PI * 2,
            spin: (Math.random() - 0.5) * 0.02
        };
        petals.push(sprite);
        petalGroup.add(sprite);
    }
    scene.add(petalGroup);

    // ---- Sparkling gold particles ----
    function makeSparkleTexture() {
        const c = document.createElement('canvas'); c.width = 32; c.height = 32;
        const ctx = c.getContext('2d');
        const grad = ctx.createRadialGradient(16, 16, 0, 16, 16, 16);
        grad.addColorStop(0, 'rgba(255,244,214,1)');
        grad.addColorStop(0.4, 'rgba(198,161,91,0.9)');
        grad.addColorStop(1, 'rgba(198,161,91,0)');
        ctx.fillStyle = grad;
        ctx.beginPath(); ctx.arc(16, 16, 16, 0, Math.PI * 2); ctx.fill();
        return new THREE.CanvasTexture(c);
    }
    const sparkleTex = makeSparkleTexture();
    const sparkleCount = 110;
    const sparkleGeo = new THREE.BufferGeometry();
    const sparklePos = new Float32Array(sparkleCount * 3);
    const sparklePhase = new Float32Array(sparkleCount);
    for (let i = 0; i < sparkleCount; i++) {
        sparklePos[i*3] = (Math.random() - 0.5) * 24;
        sparklePos[i*3+1] = (Math.random() - 0.5) * 24;
        sparklePos[i*3+2] = (Math.random() - 0.5) * 10 - 1;
        sparklePhase[i] = Math.random() * Math.PI * 2;
    }
    sparkleGeo.setAttribute('position', new THREE.BufferAttribute(sparklePos, 3));
    const sparkleMat = new THREE.PointsMaterial({
        size: 0.14, map: sparkleTex, transparent: true, opacity: 0.85,
        depthWrite: false, blending: THREE.AdditiveBlending, sizeAttenuation: true
    });
    const sparkles = new THREE.Points(sparkleGeo, sparkleMat);
    scene.add(sparkles);

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
        items.forEach(mesh => {
            mesh.position.y += mesh.userData.speed;
            mesh.position.x += Math.sin(t * 0.4 + mesh.userData.sway) * 0.001;
            mesh.rotation.z = Math.PI / 4 + Math.sin(t * 0.3 + mesh.userData.sway) * 0.15;
            if (mesh.position.y > 10) mesh.position.y = -10;
        });
        updateLines();

        petals.forEach(sp => {
            sp.position.y -= sp.userData.fall;
            sp.position.x += Math.sin(t * 0.5 + sp.userData.sway) * 0.004;
            sp.material.rotation += sp.userData.spin;
            if (sp.position.y < -12) { sp.position.y = 12; sp.position.x = (Math.random() - 0.5) * 22; }
        });

        sparkleMat.opacity = 0.55 + Math.sin(t * 1.4) * 0.3;
        sparkles.rotation.y = t * 0.02;

        scene.rotation.z = scrollFrac * 0.08;
        camera.position.x += (mouseX * 0.6 - camera.position.x) * 0.02;
        camera.position.y += (-mouseY * 0.6 - camera.position.y) * 0.02;
        camera.lookAt(0, 0, 0);

        renderer.render(scene, camera);
    }
    animate();
})();

// ================= THREE.JS — hero 3D rotating golden wedding rings =================
(function initHeroRings() {
    const canvas = document.getElementById('hero-rings-canvas');
    const heroMedia = canvas ? canvas.parentElement : null;
    if (!canvas || !heroMedia || typeof THREE === 'undefined') return;

    const renderer = new THREE.WebGLRenderer({ canvas, alpha: true, antialias: true });
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));

    const scene = new THREE.Scene();
    const camera = new THREE.PerspectiveCamera(45, 1, 0.1, 50);
    camera.position.set(0, 0, 6.2);

    function resize() {
        const w = heroMedia.clientWidth, h = heroMedia.clientHeight;
        renderer.setSize(w, h);
        camera.aspect = w / h;
        camera.updateProjectionMatrix();
    }
    resize();
    window.addEventListener('resize', resize);

    scene.add(new THREE.AmbientLight(0xffffff, 0.7));
    const key = new THREE.PointLight(0xffe9bf, 1.3, 20);
    key.position.set(3, 3, 5);
    scene.add(key);
    const rim = new THREE.PointLight(0xc6a15b, 0.9, 20);
    rim.position.set(-3, -2, 3);
    scene.add(rim);

    const goldMaterial = new THREE.MeshStandardMaterial({
        color: 0xd8b877, metalness: 0.9, roughness: 0.22, emissive: 0x3a2a10, emissiveIntensity: 0.15
    });

    const ringGroup = new THREE.Group();
    const ring1 = new THREE.Mesh(new THREE.TorusGeometry(1.05, 0.11, 32, 100), goldMaterial);
    ring1.position.set(-0.55, 0, 0);
    ring1.rotation.x = Math.PI / 2.3;
    const ring2 = new THREE.Mesh(new THREE.TorusGeometry(1.05, 0.11, 32, 100), goldMaterial.clone());
    ring2.position.set(0.55, -0.12, 0.3);
    ring2.rotation.x = Math.PI / 2.6;
    ring2.rotation.z = 0.3;
    ringGroup.add(ring1, ring2);
    scene.add(ringGroup);

    // Small accent gems on each ring
    const gemMat = new THREE.MeshStandardMaterial({ color: 0xfff2d6, metalness: 0.2, roughness: 0.05, emissive: 0xffe9bf, emissiveIntensity: 0.4 });
    [ring1, ring2].forEach(r => {
        const gem = new THREE.Mesh(new THREE.OctahedronGeometry(0.16, 0), gemMat);
        gem.position.set(0, 1.05, 0);
        r.add(gem);
    });

    let mx = 0, my = 0;
    heroMedia.addEventListener('mousemove', (e) => {
        const rect = heroMedia.getBoundingClientRect();
        mx = ((e.clientX - rect.left) / rect.width - 0.5);
        my = ((e.clientY - rect.top) / rect.height - 0.5);
    });
    heroMedia.addEventListener('mouseleave', () => { mx = 0; my = 0; });

    // Optional: load real .glb wedding ring model if one is provided at this path.
    // Falls back silently to the procedural torus rings above if not found.
    try {
        if (typeof THREE.GLTFLoader !== 'undefined') {
            const loader = new THREE.GLTFLoader();
            loader.load('/assets/models/wedding-rings.glb', (gltf) => {
                ringGroup.visible = false;
                gltf.scene.scale.set(1.4, 1.4, 1.4);
                scene.add(gltf.scene);
            }, undefined, () => { /* no model provided — keep procedural rings */ });
        }
    } catch (e) { /* GLTFLoader unavailable — procedural rings remain */ }

    const clock = new THREE.Clock();
    function animate() {
        requestAnimationFrame(animate);
        const t = clock.getElapsedTime();
        ringGroup.rotation.y = t * 0.5;
        ring1.rotation.z = Math.sin(t * 0.3) * 0.1;
        ring2.rotation.z = 0.3 + Math.cos(t * 0.3) * 0.1;
        ringGroup.position.x += (mx * 0.6 - ringGroup.position.x) * 0.04;
        ringGroup.position.y += (-my * 0.4 - ringGroup.position.y) * 0.04;
        renderer.render(scene, camera);
    }
    animate();
})();

// ================= Mouse parallax on hero glass panel =================
(function initHeroParallax() {
    const root = document.getElementById('hero-root');
    const panel = document.getElementById('hero-panel');
    if (!root || !panel) return;
    root.addEventListener('mousemove', (e) => {
        const rect = root.getBoundingClientRect();
        const px = (e.clientX - rect.left) / rect.width - 0.5;
        const py = (e.clientY - rect.top) / rect.height - 0.5;
        panel.style.transform = `rotateY(${px * 3}deg) rotateX(${-py * 3}deg) translateZ(0)`;
    });
    root.addEventListener('mouseleave', () => { panel.style.transform = 'rotateY(0) rotateX(0)'; });
})();

// ================= White doves fly across on scroll =================
(function initDoves() {
    const layer = document.getElementById('dove-layer');
    if (!layer) return;
    const doveSVG = `<svg viewBox="0 0 100 60" xmlns="http://www.w3.org/2000/svg"><path fill="currentColor" d="M50 30c-6-10-18-14-30-10 8 2 14 7 17 13-10-2-19 1-25 8 9-1 17 1 22 6-8 3-13 9-15 16 7-5 15-7 22-6-2 7 0 14 5 19 1-8 5-15 11-19 6 4 10 11 11 19 5-5 7-12 5-19 7-1 15 1 22 6-2-7-7-13-15-16 5-5 13-7 22-6-6-5-15-8-25-6 5-6 11-11 19-13-12-4-24 0-30 10-1-2-2-2-3 0z"/></svg>`;
    let lastSpawn = 0;
    const sections = document.querySelectorAll('.timeline, .coverflow-wrap, .rsvp-section');
    const io = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting && Date.now() - lastSpawn > 3500) {
                lastSpawn = Date.now();
                spawnDove();
                setTimeout(spawnDove, 500);
            }
        });
    }, { threshold: 0.3 });
    sections.forEach(s => io.observe(s));

    function spawnDove() {
        const d = document.createElement('div');
        d.className = 'dove';
        d.style.setProperty('--dt', (10 + Math.random() * 50) + '%');
        d.innerHTML = doveSVG;
        layer.appendChild(d);
        requestAnimationFrame(() => d.classList.add('flying'));
        setTimeout(() => d.remove(), 6000);
    }
})();

// ================= 3D Coverflow gallery slider =================
(function initCoverflow() {
    const track = document.getElementById('coverflow-track');
    if (!track) return;
    const slides = Array.from(track.querySelectorAll('.coverflow-slide'));
    if (slides.length === 0) return;
    const dotsWrap = document.getElementById('coverflow-dots');
    let current = 0;

    slides.forEach((_, i) => {
        const dot = document.createElement('span');
        dot.addEventListener('click', () => { current = i; render(); });
        dotsWrap.appendChild(dot);
    });
    const dots = Array.from(dotsWrap.children);

    function render() {
        const n = slides.length;
        slides.forEach((slide, i) => {
            let offset = i - current;
            if (offset > n / 2) offset -= n;
            if (offset < -n / 2) offset -= 0, offset += n;
            const abs = Math.abs(offset);
            const x = offset * 130;
            const z = -abs * 140;
            const rotY = offset * -35;
            slide.style.zIndex = String(100 - abs);
            slide.style.opacity = abs > 3 ? '0' : String(1 - abs * 0.22);
            slide.style.pointerEvents = abs > 3 ? 'none' : 'auto';
            slide.style.transform = `translateX(${x}px) translateZ(${z}px) rotateY(${rotY}deg) scale(${1 - abs * 0.12})`;
        });
        dots.forEach((d, i) => d.classList.toggle('active', i === current));
    }
    window.coverflowMove = function (dir) {
        current = (current + dir + slides.length) % slides.length;
        render();
    };
    window.coverflowClick = function (index, src) {
        if (index === current) { openLightbox(src); }
        else { current = index; render(); }
    };

    // Touch / drag swipe support
    let startX = 0, dragging = false;
    track.addEventListener('touchstart', (e) => { startX = e.touches[0].clientX; dragging = true; }, { passive: true });
    track.addEventListener('touchend', (e) => {
        if (!dragging) return;
        const dx = e.changedTouches[0].clientX - startX;
        if (Math.abs(dx) > 40) coverflowMove(dx > 0 ? -1 : 1);
        dragging = false;
    });

    window.addEventListener('resize', render);
    render();

    // Gentle auto-advance
    setInterval(() => { if (!document.hidden) coverflowMove(1); }, 5000);
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
                    translateY: [26, 0],
                    easing: 'easeOutCubic',
                    duration: 800,
                    delay: 60
                });
                io.unobserve(entry.target);
            }
        });
    }, { threshold: 0.15 });
    els.forEach(el => io.observe(el));

    const scaleEls = document.querySelectorAll('.reveal-scale');
    const ioScale = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                anime({
                    targets: entry.target,
                    opacity: [0, 1],
                    scale: [0.94, 1],
                    translateY: [24, 0],
                    easing: 'easeOutExpo',
                    duration: 1000,
                    delay: 80
                });
                ioScale.unobserve(entry.target);
            }
        });
    }, { threshold: 0.15 });
    scaleEls.forEach(el => ioScale.observe(el));
})();

</script>
</body>
</html>