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
            --plum: #6b4257;
            --plum-dark: #4a2c3b;
            --plum-light: #a97e93;
            --sage: #8a9a7e;
            --sage-dark: #5f6e54;
            --parchment: #f8f2e9;
            --parchment-2: #f0e6d6;
            --parchment-border: #e2d3ba;
            --ink: #2e2a28;
            --ink-mid: #5c5450;
            --ink-light: #8a7f77;
            --blush: #c98a8a;
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

        ::selection { background: var(--sage); color: white; }
        .reveal { opacity: 0; }

        /* Persistent fixed background animation — visible behind every section */
        #page-canvas { position: fixed; inset: 0; width: 100%; height: 100%; z-index: 0; pointer-events: none; }
        body > * { position: relative; z-index: 1; }
        #page-canvas { z-index: 0; }

        /* Preview banner */
        .preview-bar {
            background: var(--plum-dark);
            color: var(--parchment);
            text-align: center;
            padding: 10px 20px;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
            position: sticky;
            top: 3px;
            z-index: 200;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }
        .preview-bar a { color: var(--blush); text-decoration: underline; text-underline-offset: 3px; }

        /* ======= HERO : asymmetric split layout ======= */
        .hero {
            display: grid;
            grid-template-columns: 1fr 1fr;
            min-height: 560px;
            border-radius: 0 0 0 80px;
            overflow: hidden;
        }
        .hero-media {
            position: relative;
            overflow: hidden;
            background: linear-gradient(160deg, var(--plum-dark), #2e1a25);
            display: flex;
            align-items: flex-end;
            justify-content: flex-start;
            min-height: 320px;
        }
        /* Layer 1: the actual photo — animated for a slow cinematic zoom */
        .hero-media-bg {
            position: absolute;
            inset: 0;
            z-index: 0;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            animation: heroZoom 16s ease-in-out infinite alternate;
            will-change: transform;
        }
        @keyframes heroZoom {
            0%   { transform: scale(1); }
            100% { transform: scale(1.14); }
        }
        /* Layer 2: gentle scrim so text/UI on top stays legible */
        .hero-media-overlay {
            position: absolute;
            inset: 0;
            z-index: 1;
            background: linear-gradient(195deg, rgba(46,26,37,0.05) 0%, rgba(46,26,37,0.6) 100%);
            pointer-events: none;
        }
        /* Layer 3: 3D rings / petals / sparkles canvas — sits ON TOP of the photo */
        .hero-scene {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            z-index: 2;
            pointer-events: none;
            display: block;
        }
        .wax-seal {
            position: relative;
            z-index: 3;
            margin: 28px;
            width: 88px; height: 88px;
            border-radius: 50%;
            background: var(--sage);
            color: white;
            display: flex; align-items: center; justify-content: center;
            font-family: 'Fraunces', serif;
            font-size: 1.7rem;
            font-weight: 600;
            letter-spacing: 1px;
            box-shadow: 0 10px 26px rgba(74,44,59,0.4);
        }
        .hero-panel {
            background: rgba(248,242,233,0.55);
            backdrop-filter: blur(3px);
            -webkit-backdrop-filter: blur(3px);
            padding: 60px 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .eyebrow { font-size: 0.72rem; font-weight: 600; letter-spacing: 2.5px; text-transform: uppercase; color: var(--sage-dark); margin-bottom: 14px; }
        .guest-line { font-family: 'Fraunces', serif; font-style: italic; font-weight: 400; font-size: clamp(1.2rem, 2.4vw, 1.6rem); color: var(--ink-mid); margin-bottom: 22px; }

        .reserved-note {
            margin: 0 0 26px;
            background: var(--parchment-2);
            border-left: 3px solid var(--sage);
            padding: 10px 16px;
            border-radius: 4px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            color: var(--plum-dark);
            font-size: 0.86rem;
            font-weight: 600;
            max-width: 100%;
        }
        .reserved-note i { color: var(--sage-dark); width: 16px; }

        .couple-title { font-family: 'Fraunces', serif; font-weight: 600; font-size: clamp(2.4rem, 5.5vw, 4rem); line-height: 1.1; color: var(--ink); margin-bottom: 24px; }
        .couple-title .amp { display: block; font-style: italic; font-weight: 400; font-size: 0.5em; color: var(--sage-dark); margin: 2px 0; }
        .shimmer-name {
            display: inline-block;
            background: linear-gradient(90deg, #a3792f 0%, #f3e2a0 22%, #d4af37 45%, #fff6d8 60%, #a3792f 100%);
            background-size: 250% auto;
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            animation: shimmerGold 5s linear infinite;
        }
        @keyframes shimmerGold {
            0%   { background-position: 0% center; }
            100% { background-position: -250% center; }
        }
        .date-chip {
            display: inline-flex; flex-direction: column; gap: 2px;
            background: rgba(138,154,126,0.12);
            border-radius: 16px; padding: 16px 22px;
            width: fit-content;
        }
        .date-chip .lbl { font-size: 0.65rem; letter-spacing: 2px; text-transform: uppercase; color: var(--ink-light); }
        .date-chip .val { font-family: 'Fraunces', serif; font-size: 1.2rem; font-weight: 500; color: var(--plum-dark); }

        .hero-venue {
            margin-top: 16px;
            font-size: 0.92rem;
            color: var(--sage-dark);
            display: flex; align-items: center; gap: 8px;
        }

        @media (max-width: 760px) {
            .hero { grid-template-columns: 1fr; }
            .hero-media { min-height: 58vh; max-height: 80vh; background-color: #241019; }
            .hero-media-bg { background-size: contain; background-position: center; }
            .hero-panel { padding: 40px 26px; }
        }

        /* ======= COUNTDOWN : ticket strip ======= */
        .countdown-section {
            background: rgba(74,44,59,0.82);
            backdrop-filter: blur(3px);
            -webkit-backdrop-filter: blur(3px);
            padding: 26px 20px;
            text-align: center;
            position: relative;
        }
        .countdown-section::before,
        .countdown-section::after {
            content: ''; position: absolute; top: 50%;
            width: 22px; height: 22px;
            background: var(--sage);
            border-radius: 50%;
            transform: translateY(-50%);
        }
        .countdown-section::before { left: -11px; }
        .countdown-section::after { right: -11px; }
        .countdown-label { font-size: 0.68rem; letter-spacing: 2.5px; text-transform: uppercase; color: var(--plum-light); margin-bottom: 14px; }
        .countdown { display: flex; justify-content: center; align-items: center; gap: 14px; flex-wrap: wrap; }
        .time-unit { text-align: center; min-width: 58px; }
        .time-value { display: block; font-family: 'Fraunces', serif; font-size: clamp(1.8rem, 5vw, 2.4rem); font-weight: 600; color: #b7c3ac; line-height: 1; }
        .time-label { font-size: 0.6rem; letter-spacing: 1.5px; text-transform: uppercase; color: rgba(183,195,172,0.65); margin-top: 4px; }
        .time-sep { font-size: 1.4rem; color: var(--plum-light); margin-bottom: 14px; }
        .just-married-msg { font-family: 'Fraunces', serif; font-size: 2rem; font-style: italic; color: var(--parchment); }

        /* Wave separator between hero and countdown */
        .wave-separator { line-height: 0; margin-top: -3px; position: relative; z-index: 2; }
        .wave-separator svg { display: block; width: 100%; height: 54px; }

        /* Scroll progress bar */
        .scroll-progress {
            position: fixed;
            top: 0; left: 0;
            height: 3px;
            width: 0%;
            background: linear-gradient(90deg, #a3792f, #f3e2a0, #d4af37);
            z-index: 500;
            transition: width 0.12s linear;
            box-shadow: 0 0 8px rgba(212,175,55,0.6);
        }

        /* ======= BODY ======= */
        .invitation-body { max-width: 720px; margin: 0 auto; padding: 0 20px; }
        .section-head { text-align: center; margin: 56px 0 34px; }
        .section-head .tag { font-size: 0.7rem; letter-spacing: 2.5px; text-transform: uppercase; color: var(--sage-dark); display: block; margin-bottom: 8px; }
        .section-head h2 { font-family: 'Fraunces', serif; font-weight: 600; font-size: clamp(1.8rem, 5vw, 2.4rem); color: var(--ink); }
        .section-head h2 em { font-style: italic; color: var(--plum-dark); }

        /* Love story: folded letter card */
        .letter-card {
            background: var(--white);
            border-left: 5px solid var(--sage);
            border-radius: 0 16px 16px 0;
            padding: 36px 32px;
            position: relative;
            font-family: 'Fraunces', serif;
            font-style: italic;
            font-size: 1.1rem;
            line-height: 1.9;
            color: var(--ink-mid);
            box-shadow: 0 14px 34px rgba(46,42,40,0.06);
        }

        /* Programme: vertical timeline */
        .timeline { position: relative; padding-left: 68px; }
        .timeline::before { content: ''; position: absolute; left: 27px; top: 6px; bottom: 6px; width: 2px; background: repeating-linear-gradient(to bottom, var(--parchment-border) 0 6px, transparent 6px 12px); }
        .tl-item { position: relative; margin-bottom: 26px; }
        .tl-marker {
            position: absolute; left: -68px; top: 0; width: 56px; height: 56px;
            border-radius: 50%;
            background: var(--sage);
            color: white;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            font-family: 'Fraunces', serif; line-height: 1.1;
            transition: box-shadow 0.35s ease, transform 0.35s ease;
        }
        .tl-item:hover .tl-marker {
            box-shadow: 0 0 0 6px rgba(212,175,55,0.16), 0 0 22px 6px rgba(212,175,55,0.55);
            transform: scale(1.08);
        }
        .tl-marker .d { font-size: 1.3rem; font-weight: 600; }
        .tl-marker .m { font-size: 0.6rem; letter-spacing: 1px; text-transform: uppercase; }
        .tl-card {
            background: var(--white); border-left: 3px solid var(--sage); border-radius: 0 16px 16px 0; padding: 22px 24px;
            transition: box-shadow 0.35s ease, transform 0.35s ease, border-color 0.35s ease;
        }
        .tl-item:hover .tl-card {
            box-shadow: 0 14px 34px rgba(46,42,40,0.12), 0 0 26px rgba(212,175,55,0.28);
            transform: translateY(-4px);
            border-left-color: #d4af37;
        }
        .tl-name { font-family: 'Fraunces', serif; font-weight: 600; font-size: 1.35rem; color: var(--ink); margin-bottom: 10px; }
        .tl-meta { display: flex; flex-direction: column; gap: 5px; margin-bottom: 14px; }
        .tl-meta-item { display: flex; align-items: center; gap: 9px; font-size: 0.86rem; color: var(--ink-mid); }
        .tl-meta-item i { color: var(--plum); width: 15px; text-align: center; }
        .tl-actions { display: flex; gap: 9px; flex-wrap: wrap; margin-top: 14px; }
        .btn-map {
            display: inline-flex; align-items: center; gap: 6px;
            background: var(--plum-dark); color: white; text-decoration: none;
            padding: 8px 16px; border-radius: 10px;
            font-size: 0.78rem; font-weight: 600; transition: all 0.2s;
        }
        .btn-map:hover { background: var(--plum); color: white; }
        .btn-cal {
            display: inline-flex; align-items: center; gap: 6px;
            background: transparent; border: 1px solid var(--parchment-border);
            color: var(--ink-mid); text-decoration: none;
            padding: 8px 16px; border-radius: 8px;
            font-size: 0.78rem; font-weight: 500; cursor: pointer; transition: all 0.2s;
        }
        .btn-cal:hover { border-color: var(--sage); color: var(--sage-dark); }
        .cal-dropdown { position: relative; display: inline-block; }
        .cal-menu { display: none; position: absolute; bottom: calc(100% + 8px); left: 0; background: white; border: 1px solid var(--parchment-border); border-radius: 12px; box-shadow: 0 10px 30px rgba(46,42,40,0.12); min-width: 180px; z-index: 10; overflow: hidden; }
        .cal-menu.open { display: block; }
        .cal-menu a { display: flex; align-items: center; gap: 10px; padding: 11px 15px; font-size: 0.82rem; color: var(--ink-mid); text-decoration: none; transition: background 0.15s; }
        .cal-menu a:hover { background: var(--parchment-2); color: var(--plum); }
        .cal-menu a i { width: 16px; text-align: center; }

        /* Gallery: polaroid scatter, each photo a different shape */
        .polaroid-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 22px 16px; padding: 10px 6px 30px; }
        .polaroid {
            background: linear-gradient(135deg, #fff, var(--parchment));
            padding: 10px 10px 26px;
            border-radius: 16px;
            box-shadow: 0 10px 22px rgba(46,42,40,0.12);
            cursor: pointer;
            transition: transform 0.25s;
        }
        .polaroid:nth-child(3n+1) { transform: rotate(-2deg); }
        .polaroid:nth-child(3n+2) { transform: rotate(1.5deg); }
        .polaroid:nth-child(3n)   { transform: rotate(-1deg); }
        .polaroid:hover { transform: rotate(0deg) scale(1.05); z-index: 2; }
        .polaroid img { width: 100%; aspect-ratio: 1; object-fit: cover; display: block; }
        .polaroid:nth-child(4n+1) img { border-radius: 50%; }
        .polaroid:nth-child(4n+2) img { border-radius: 8px; }
        .polaroid:nth-child(4n+3) img { border-radius: 42% 58% 65% 35% / 45% 40% 60% 55%; }
        .polaroid:nth-child(4n) img { border-radius: 0; clip-path: polygon(50% 0%, 100% 38%, 82% 100%, 18% 100%, 0% 38%); }

        /* Lightbox */
        .lightbox { display: none; position: fixed; inset: 0; background: rgba(20,16,15,0.92); z-index: 1000; align-items: center; justify-content: center; }
        .lightbox.open { display: flex; }
        .lightbox img { max-width: 90vw; max-height: 90vh; border-radius: 6px; object-fit: contain; }
        .lightbox-close { position: absolute; top: 20px; right: 20px; color: white; font-size: 1.8rem; cursor: pointer; opacity: 0.75; transition: opacity 0.2s; }
        .lightbox-close:hover { opacity: 1; }

        /* Guest shared gallery */
        .section-divider { display: flex; align-items: center; gap: 16px; max-width: 720px; margin: 56px auto 0; padding: 0 20px; }
        .section-divider-line { flex: 1; height: 1px; background: linear-gradient(to right, transparent, var(--sage)); }
        .section-divider-line.right { background: linear-gradient(to left, transparent, var(--sage)); }
        .section-divider-icon { width: 40px; height: 40px; border-radius: 50%; border: 1px solid var(--sage); display: flex; align-items: center; justify-content: center; color: var(--sage-dark); flex-shrink: 0; }
        .section-heading { text-align: center; font-family: 'Fraunces', serif; font-weight: 600; font-size: clamp(1.8rem, 5vw, 2.4rem); color: var(--ink); margin-top: 18px; }
        .section-heading em { font-style: italic; color: var(--plum-dark); }
        .section-sub { text-align: center; color: var(--ink-mid); font-size: 0.9rem; margin: 6px 0 26px; max-width: 720px; margin-left: auto; margin-right: auto; padding: 0 20px; }

        .gallery-grid {
            display: flex;
            flex-wrap: nowrap;
            overflow-x: auto;
            gap: 15px;
            padding: 10px 0;
            scroll-behavior: smooth;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }
        .gallery-grid::-webkit-scrollbar { display: none; }
        .gallery-item { flex: 0 0 auto; width: 240px; height: 240px; border-radius: 14px; overflow: hidden; position: relative; cursor: pointer; box-shadow: 0 10px 24px rgba(46,42,40,0.12); transition: transform 0.3s; }
        .gallery-item:hover { transform: translateY(-4px); }
        .gallery-item img { width: 100%; height: 100%; object-fit: cover; display: block; }

        /* ======= RSVP : two column asymmetric card ======= */
        .rsvp-section { background: rgba(240,230,214,0.55); backdrop-filter: blur(3px); -webkit-backdrop-filter: blur(3px); border-top: 1px solid var(--parchment-border); padding: 60px 20px; }
        .rsvp-card {
            max-width: 780px; margin: 0 auto; background: white;
            border-radius: 0 30px 30px 0; border-left: 5px solid var(--sage);
            overflow: hidden; display: grid; grid-template-columns: 0.85fr 1.15fr;
            box-shadow: 0 16px 44px rgba(46,42,40,0.1);
        }
        .rsvp-aside { background: linear-gradient(160deg, var(--plum-dark), #2e1a25); color: var(--parchment); padding: 44px 34px; display: flex; flex-direction: column; justify-content: center; }
        .rsvp-aside .quote-mark { font-family: 'Fraunces', serif; font-size: 3rem; opacity: 0.5; line-height: 1; margin-bottom: 10px; }
        .rsvp-aside p { font-family: 'Fraunces', serif; font-style: italic; font-size: 1.15rem; line-height: 1.7; }
        .rsvp-form-side { padding: 44px 34px; }
        .rsvp-title { font-family: 'Fraunces', serif; font-weight: 600; font-size: 1.9rem; color: var(--ink); margin-bottom: 4px; }
        .rsvp-subtitle { font-size: 0.78rem; color: var(--ink-light); letter-spacing: 1px; text-transform: uppercase; margin-bottom: 24px; }
        .rsvp-options { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 18px; }
        .rsvp-option input[type="radio"] { display: none; }
        .rsvp-option label { display: flex; flex-direction: column; align-items: center; gap: 7px; padding: 16px 10px; border: 2px solid var(--parchment-border); border-radius: 10px; cursor: pointer; font-size: 0.8rem; font-weight: 500; color: var(--ink-mid); transition: all 0.2s; }
        .rsvp-option label i { font-size: 1.3rem; }
        .rsvp-option:first-child input[type="radio"]:checked + label { border-color: var(--sage-dark); background: rgba(138,154,126,0.1); color: var(--sage-dark); }
        .rsvp-option:last-child input[type="radio"]:checked + label { border-color: var(--blush); background: rgba(201,138,138,0.1); color: #a25c5c; }
        .rsvp-note { width: 100%; background: var(--parchment); border: 1px solid var(--parchment-border); border-radius: 12px; padding: 13px 15px; font-family: 'Inter', sans-serif; font-size: 0.86rem; color: var(--ink-mid); resize: none; outline: none; transition: border-color 0.2s; margin-bottom: 16px; }
        .rsvp-note:focus { border-color: var(--sage); }
        .btn-rsvp-submit { width: 100%; background: var(--plum-dark); color: var(--parchment); border: none; border-radius: 10px; padding: 15px; font-size: 0.86rem; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; cursor: pointer; transition: all 0.25s; }
        .btn-rsvp-submit:hover { background: var(--plum); transform: translateY(-2px); }
        @media (max-width: 640px) { .rsvp-card { grid-template-columns: 1fr; } .rsvp-aside { padding: 30px 26px; } .rsvp-form-side { padding: 30px 26px; } }

        /* ======= FOOTER ======= */
        .inv-footer { text-align: center; padding: 40px 20px; font-size: 0.75rem; color: var(--ink-light); border-top: 1px solid var(--parchment-border); background: rgba(248,242,233,0.6); }
        .inv-footer .brand { font-family: 'Fraunces', serif; font-style: italic; font-size: 1.3rem; color: var(--plum-dark); display: block; margin-bottom: 6px; }
    </style>
</head>
<body>

<canvas id="page-canvas"></canvas>
<div class="scroll-progress" id="scroll-progress"></div>

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
<div class="hero reveal">
    <div class="hero-media">
        <div class="hero-media-bg" <?php echo $hero_style; ?>></div>
        <div class="hero-media-overlay"></div>
        <canvas class="hero-scene" id="hero-scene"></canvas>
        <div class="wax-seal"><?php echo htmlspecialchars($monogram); ?></div>
    </div>
    <div class="hero-panel">
        <span class="eyebrow"><?php echo t('hero_eyebrow'); ?></span>
        <p class="guest-line"><?php echo t('hero_dear'); ?> <?php echo htmlspecialchars($guest_name); ?>,</p>

        <h1 class="couple-title">
            <span class="shimmer-name"><?php echo htmlspecialchars($wedding['bride_name']); ?></span>
            <span class="amp">&amp;</span>
            <span class="shimmer-name"><?php echo htmlspecialchars($wedding['groom_name']); ?></span>
        </h1>

        <div class="date-chip">
            <span class="lbl"><?php echo t('hero_getting_married'); ?></span>
            <span class="val"><?php echo t_date($wedding['wedding_date']); ?></span>
        </div>

        <?php if (!empty($wedding['venue'])): ?>
        <p class="hero-venue"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($wedding['venue']); ?></p>
        <?php endif; ?>
    </div>
</div>

<!-- WAVE SEPARATOR -->
<div class="wave-separator">
    <svg viewBox="0 0 1440 100" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M0,32 C240,90 480,0 720,28 C960,56 1200,96 1440,40 L1440,100 L0,100 Z" fill="rgba(74,44,59,0.82)"></path>
    </svg>
</div>

<!-- COUNTDOWN -->
<div class="countdown-section">
    <p class="countdown-label"><?php echo t('countdown_label'); ?></p>
    <div class="countdown" id="countdown">
        <div class="time-unit"><span class="time-value" id="cd-days">00</span><span class="time-label"><?php echo t('cd_days'); ?></span></div>
        <span class="time-sep">:</span>
        <div class="time-unit"><span class="time-value" id="cd-hours">00</span><span class="time-label"><?php echo t('cd_hours'); ?></span></div>
        <span class="time-sep">:</span>
        <div class="time-unit"><span class="time-value" id="cd-mins">00</span><span class="time-label"><?php echo t('cd_mins'); ?></span></div>
        <span class="time-sep">:</span>
        <div class="time-unit"><span class="time-value" id="cd-secs">00</span><span class="time-label"><?php echo t('cd_secs'); ?></span></div>
    </div>
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
    <div class="polaroid-grid" id="gallery-grid">
        <?php foreach ($gallery_images as $img): ?>
        <div class="polaroid reveal" onclick="openLightbox('<?php echo htmlspecialchars($img['image_path']); ?>')">
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
    <h2 class="section-heading"><?php echo t('guest_gallery_title'); ?></h2>
    <p class="section-sub">Capture and share your beautiful memories with us!</p>

    <!-- Upload Box (Preview mode එකේදී අක්‍රීය වේ) -->
    <div style="max-width:720px; margin:0 auto 30px; background: rgba(240,230,214,0.6); border: 2px dashed var(--sage); border-radius: 20px; padding: 30px 20px; text-align: center;">
        <?php if ($guest_id == 0): ?>
            <p class="text-muted small"><i class="fas fa-lock"></i> <?php echo t('upload_disabled_preview'); ?></p>
        <?php else: ?>
            <i class="fas fa-camera-retro" style="font-size: 2.2rem; color: var(--sage-dark); margin-bottom: 12px; display: block;"></i>
            <h5 class="fw-bold" style="font-family:'Inter', sans-serif; font-size: 0.95rem; color: var(--ink); margin-bottom: 6px;"><?php echo t('share_photo_heading'); ?></h5>
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
            <div class="gallery-item reveal" onclick="openLightbox('<?php echo htmlspecialchars($g_img['image_path']); ?>')" style="border: 2px solid #22c55e;">
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
                            item.style.border = '2px solid #22c55e';
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
    <div class="rsvp-card reveal">
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
            <?php endif; ?>

            <form method="POST" action="">
                <div class="rsvp-options">
                    <div class="rsvp-option">
                        <input type="radio" name="rsvp_status" id="rsvp-yes" value="accepted"
                            <?php if ($current_guest['rsvp_status'] == 'accepted') echo 'checked'; ?> required>
                        <label for="rsvp-yes">
                            <i class="fas fa-heart" style="color:#5f6e54;"></i>
                            <?php echo t('rsvp_accept'); ?>
                        </label>
                    </div>
                    <div class="rsvp-option">
                        <input type="radio" name="rsvp_status" id="rsvp-no" value="rejected"
                            <?php if ($current_guest['rsvp_status'] == 'rejected') echo 'checked'; ?>>
                        <label for="rsvp-no">
                            <i class="fas fa-heart-broken" style="color:#a25c5c;"></i>
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/animejs/3.2.1/anime.min.js"></script>

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

// ================= THREE.JS — persistent constellation background =================
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

    const count = 85;
    const positions = new Float32Array(count * 3);
    const velocities = [];
    for (let i = 0; i < count; i++) {
        positions[i * 3] = (Math.random() - 0.5) * 20;
        positions[i * 3 + 1] = (Math.random() - 0.5) * 20;
        positions[i * 3 + 2] = (Math.random() - 0.5) * 6;
        velocities.push({ x: (Math.random() - 0.5) * 0.004, y: (Math.random() - 0.5) * 0.004 });
    }
    const dotGeo = new THREE.BufferGeometry();
    dotGeo.setAttribute('position', new THREE.BufferAttribute(positions, 3));
    const dotMat = new THREE.PointsMaterial({ color: 0x8a9a7e, size: 0.055, transparent: true, opacity: 0.6 });
    const dots = new THREE.Points(dotGeo, dotMat);
    scene.add(dots);

    const maxLines = count * 3;
    const lineGeo = new THREE.BufferGeometry();
    const linePositions = new Float32Array(maxLines * 2 * 3);
    lineGeo.setAttribute('position', new THREE.BufferAttribute(linePositions, 3));
    const lineMat = new THREE.LineBasicMaterial({ color: 0xa97e93, transparent: true, opacity: 0.16 });
    const lines = new THREE.LineSegments(lineGeo, lineMat);
    scene.add(lines);

    // ---- Gold sparkle particles, floating across the whole page ----
    function makeSparkleTexture() {
        const c = document.createElement('canvas'); c.width = c.height = 64;
        const ctx = c.getContext('2d');
        const g = ctx.createRadialGradient(32, 32, 0, 32, 32, 32);
        g.addColorStop(0, 'rgba(255,244,214,1)');
        g.addColorStop(0.4, 'rgba(230,190,110,0.8)');
        g.addColorStop(1, 'rgba(230,190,110,0)');
        ctx.fillStyle = g; ctx.fillRect(0, 0, 64, 64);
        return new THREE.CanvasTexture(c);
    }
    const sparkleCount = 70;
    const sparklePos = new Float32Array(sparkleCount * 3);
    for (let i = 0; i < sparkleCount; i++) {
        sparklePos[i * 3] = (Math.random() - 0.5) * 24;
        sparklePos[i * 3 + 1] = (Math.random() - 0.5) * 24;
        sparklePos[i * 3 + 2] = (Math.random() - 0.5) * 6;
    }
    const sparkleGeo = new THREE.BufferGeometry();
    sparkleGeo.setAttribute('position', new THREE.BufferAttribute(sparklePos, 3));
    const sparkleMat = new THREE.PointsMaterial({ size: 0.16, map: makeSparkleTexture(), transparent: true, depthWrite: false, blending: THREE.AdditiveBlending, opacity: 0.85 });
    const sparkles = new THREE.Points(sparkleGeo, sparkleMat);
    scene.add(sparkles);

    // ---- Floating rose petals, drifting down the whole page as you scroll ----
    function makePetalTexture() {
        const c = document.createElement('canvas'); c.width = 64; c.height = 64;
        const ctx = c.getContext('2d');
        ctx.translate(32, 32);
        const g = ctx.createLinearGradient(0, -28, 0, 28);
        g.addColorStop(0, '#e8a3ac');
        g.addColorStop(1, '#c3596b');
        ctx.fillStyle = g;
        ctx.beginPath();
        ctx.moveTo(0, -28);
        ctx.bezierCurveTo(22, -18, 22, 18, 0, 28);
        ctx.bezierCurveTo(-22, 18, -22, -18, 0, -28);
        ctx.fill();
        return new THREE.CanvasTexture(c);
    }
    const petalMat = new THREE.MeshBasicMaterial({ map: makePetalTexture(), transparent: true, side: THREE.DoubleSide, depthWrite: false });
    const petalGeo = new THREE.PlaneGeometry(0.5, 0.5);
    const petals = [];
    for (let i = 0; i < 22; i++) {
        const m = new THREE.Mesh(petalGeo, petalMat);
        m.position.set((Math.random() - 0.5) * 22, Math.random() * 22 - 11, (Math.random() - 0.5) * 5);
        m.rotation.set(Math.random() * Math.PI, Math.random() * Math.PI, Math.random() * Math.PI);
        m.userData = {
            speed: 0.25 + Math.random() * 0.35,
            drift: 0.3 + Math.random() * 0.5,
            spin: (Math.random() - 0.5) * 0.9,
            phase: Math.random() * Math.PI * 2
        };
        scene.add(m);
        petals.push(m);
    }

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

    let mouseX = 0, mouseY = 0;
    window.addEventListener('mousemove', (e) => {
        mouseX = (e.clientX / window.innerWidth - 0.5);
        mouseY = (e.clientY / window.innerHeight - 0.5);
    });

    const clock = new THREE.Clock();
    function animate() {
        requestAnimationFrame(animate);
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

        const t = clock.getElapsedTime();
        const sp = sparkleGeo.attributes.position.array;
        for (let i = 0; i < sparkleCount; i++) {
            sp[i * 3 + 1] += 0.0025;
            if (sp[i * 3 + 1] > 12) sp[i * 3 + 1] = -12;
        }
        sparkleGeo.attributes.position.needsUpdate = true;
        sparkleMat.opacity = 0.55 + Math.sin(t * 2.2) * 0.3;

        petals.forEach(p => {
            const d = p.userData;
            p.position.y -= d.speed * 0.012;
            p.position.x += Math.sin(t * 0.6 + d.phase) * 0.004 * d.drift;
            p.rotation.z += d.spin * 0.01;
            p.rotation.x += 0.004;
            if (p.position.y < -11) {
                p.position.y = 11;
                p.position.x = (Math.random() - 0.5) * 22;
            }
        });

        scene.rotation.z = scrollFrac * 0.15;
        camera.position.x += (mouseX * 0.6 - camera.position.x) * 0.02;
        camera.position.y += (-mouseY * 0.6 - camera.position.y) * 0.02;
        camera.lookAt(0, 0, 0);

        renderer.render(scene, camera);
    }
    animate();
})();

// ================= Scroll progress bar =================
(function initScrollProgress() {
    const bar = document.getElementById('scroll-progress');
    if (!bar) return;
    function update() {
        const h = document.documentElement;
        const scrollable = h.scrollHeight - h.clientHeight;
        const pct = scrollable > 0 ? (h.scrollTop / scrollable) * 100 : 0;
        bar.style.width = pct + '%';
    }
    window.addEventListener('scroll', update, { passive: true });
    window.addEventListener('resize', update);
    update();
})();

// ================= THREE.JS — hero: rotating gold rings + rose petals + sparkles =================
// Renders ON TOP of the hero photo (canvas sits above .hero-media-bg / .hero-media-overlay
// via z-index), starts the moment the page loads, and loops forever.
(function initHeroScene() {
    const canvas = document.getElementById('hero-scene');
    const container = canvas ? canvas.closest('.hero-media') : null;
    if (!canvas || !container || typeof THREE === 'undefined') return;

    const renderer = new THREE.WebGLRenderer({ canvas, alpha: true, antialias: true });
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));

    const scene = new THREE.Scene();
    const camera = new THREE.PerspectiveCamera(45, 1, 0.1, 100);
    camera.position.set(0, 0, 9);

    function resize() {
        const w = container.clientWidth || container.offsetWidth || 1;
        const h = container.clientHeight || container.offsetHeight || 1;
        renderer.setSize(w, h, false);
        camera.aspect = w / h;
        camera.updateProjectionMatrix();
    }
    resize();
    // Layout can settle a beat after first paint (fonts, flex/grid reflow) — resize again to be safe.
    window.addEventListener('load', resize);
    setTimeout(resize, 300);
    window.addEventListener('resize', resize);
    window.addEventListener('orientationchange', resize);
    if (window.ResizeObserver) new ResizeObserver(resize).observe(container);

    // Lighting so the gold rings actually shine
    scene.add(new THREE.AmbientLight(0xfff2d0, 0.75));
    const keyLight = new THREE.PointLight(0xffe8b0, 1.5, 40);
    keyLight.position.set(4, 5, 8);
    scene.add(keyLight);
    const rimLight = new THREE.PointLight(0xd9a4b5, 0.7, 40);
    rimLight.position.set(-5, -3, 4);
    scene.add(rimLight);

    // ---- Interlocked 3D gold wedding rings ----
    const ringMat = new THREE.MeshStandardMaterial({ color: 0xd4af37, metalness: 0.95, roughness: 0.22, emissive: 0x2a1c05, emissiveIntensity: 0.15 });
    const ringGroup = new THREE.Group();
    const ringA = new THREE.Mesh(new THREE.TorusGeometry(0.85, 0.11, 24, 80), ringMat);
    const ringB = new THREE.Mesh(new THREE.TorusGeometry(0.85, 0.11, 24, 80), ringMat);
    ringA.rotation.x = Math.PI / 2.4;
    ringB.rotation.y = Math.PI / 2.4;
    ringB.position.set(0.55, -0.15, 0);
    ringGroup.add(ringA, ringB);
    ringGroup.position.set(1.6, -0.3, 0);
    ringGroup.rotation.z = 0.15;
    scene.add(ringGroup);

    // ---- Soft round sparkle texture ----
    function makeSparkleTexture() {
        const c = document.createElement('canvas'); c.width = c.height = 64;
        const ctx = c.getContext('2d');
        const g = ctx.createRadialGradient(32, 32, 0, 32, 32, 32);
        g.addColorStop(0, 'rgba(255,244,214,1)');
        g.addColorStop(0.4, 'rgba(230,190,110,0.8)');
        g.addColorStop(1, 'rgba(230,190,110,0)');
        ctx.fillStyle = g; ctx.fillRect(0, 0, 64, 64);
        return new THREE.CanvasTexture(c);
    }
    const sparkleCount = 46;
    const sparklePos = new Float32Array(sparkleCount * 3);
    for (let i = 0; i < sparkleCount; i++) {
        sparklePos[i * 3] = (Math.random() - 0.5) * 8;
        sparklePos[i * 3 + 1] = (Math.random() - 0.5) * 6;
        sparklePos[i * 3 + 2] = (Math.random() - 0.5) * 3;
    }
    const sparkleGeo = new THREE.BufferGeometry();
    sparkleGeo.setAttribute('position', new THREE.BufferAttribute(sparklePos, 3));
    const sparkleMat = new THREE.PointsMaterial({ size: 0.22, map: makeSparkleTexture(), transparent: true, depthWrite: false, blending: THREE.AdditiveBlending, opacity: 0.9 });
    const sparkles = new THREE.Points(sparkleGeo, sparkleMat);
    scene.add(sparkles);

    // ---- Floating rose petals ----
    function makePetalTexture() {
        const c = document.createElement('canvas'); c.width = 64; c.height = 64;
        const ctx = c.getContext('2d');
        ctx.translate(32, 32);
        const g = ctx.createLinearGradient(0, -28, 0, 28);
        g.addColorStop(0, '#e8a3ac');
        g.addColorStop(1, '#c3596b');
        ctx.fillStyle = g;
        ctx.beginPath();
        ctx.moveTo(0, -28);
        ctx.bezierCurveTo(22, -18, 22, 18, 0, 28);
        ctx.bezierCurveTo(-22, 18, -22, -18, 0, -28);
        ctx.fill();
        return new THREE.CanvasTexture(c);
    }
    const petalMat = new THREE.MeshBasicMaterial({ map: makePetalTexture(), transparent: true, side: THREE.DoubleSide, depthWrite: false });
    const petalGeo = new THREE.PlaneGeometry(0.42, 0.42);
    const petals = [];
    for (let i = 0; i < 16; i++) {
        const m = new THREE.Mesh(petalGeo, petalMat);
        m.position.set((Math.random() - 0.5) * 9, Math.random() * 8 - 4, (Math.random() - 0.5) * 3);
        m.rotation.set(Math.random() * Math.PI, Math.random() * Math.PI, Math.random() * Math.PI);
        m.userData = {
            speed: 0.25 + Math.random() * 0.35,
            drift: 0.3 + Math.random() * 0.5,
            spin: (Math.random() - 0.5) * 0.9,
            phase: Math.random() * Math.PI * 2
        };
        scene.add(m);
        petals.push(m);
    }

    const clock = new THREE.Clock();
    function animate() {
        requestAnimationFrame(animate);
        const t = clock.getElapsedTime();

        ringGroup.rotation.y = t * 0.5;
        ringGroup.rotation.x = Math.sin(t * 0.4) * 0.2;
        ringGroup.position.y = -0.3 + Math.sin(t * 0.8) * 0.08;

        const sp = sparkleGeo.attributes.position.array;
        for (let i = 0; i < sparkleCount; i++) {
            sp[i * 3 + 1] += 0.0025;
            if (sp[i * 3 + 1] > 3.2) sp[i * 3 + 1] = -3.2;
        }
        sparkleGeo.attributes.position.needsUpdate = true;
        sparkleMat.opacity = 0.55 + Math.sin(t * 2.2) * 0.35;

        petals.forEach(p => {
            const d = p.userData;
            p.position.y -= d.speed * 0.012;
            p.position.x += Math.sin(t * 0.6 + d.phase) * 0.004 * d.drift;
            p.rotation.z += d.spin * 0.01;
            p.rotation.x += 0.004;
            if (p.position.y < -4.2) {
                p.position.y = 4.2;
                p.position.x = (Math.random() - 0.5) * 9;
            }
        });

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
})();
</script>
</body>
</html>