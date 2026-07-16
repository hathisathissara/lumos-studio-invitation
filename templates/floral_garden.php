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
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,500&family=Fraunces:opsz,wght@9..144,300;9..144,500;9..144,600;9..144,700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --plum: #c47a94;
            --plum-dark: #a15873;
            --plum-light: #e7b9cb;
            --sage: #8fac7a;
            --sage-dark: #63805a;
            --parchment: #fffaf7;
            --parchment-2: #fbeef1;
            --parchment-border: #f0dde3;
            --ink: #3c2f34;
            --ink-mid: #6d5b60;
            --ink-light: #a6949a;
            --blush: #eec2d1;
            --gold: #c9a961;
            --cream-2: #fbeef1;
            --white: #ffffff;
        }

        html { scroll-behavior: smooth; }

        body {
            background: var(--parchment);
            font-family: 'Inter', sans-serif;
            color: var(--ink);
            min-height: 100vh;
            overflow-x: hidden;
        }

        ::selection { background: var(--plum-light); color: var(--ink); }
        .reveal { opacity: 0; }

        /* Preview banner */
        .preview-bar {
            background: var(--plum-dark);
            color: var(--parchment);
            text-align: center;
            padding: 10px 20px;
            font-family: 'Inter', sans-serif;
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
        .preview-bar a { color: var(--blush); text-decoration: underline; text-underline-offset: 3px; }

        /* ======= HERO : glass botanical scene ======= */
        .hero {
            position: relative;
            min-height: 100vh;
            background: linear-gradient(180deg, #fdf2f5, #fbeef1 55%, var(--parchment));
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            padding: 90px 20px 70px;
        }
        #hero-canvas {
            position: fixed;
            inset: 0;
            width: 100vw;
            height: 100vh;
            display: block;
            z-index: 1;
            pointer-events: none;
        }
        .hero-card {
            position: relative;
            z-index: 2;
            max-width: 600px;
            width: 100%;
            text-align: center;
            padding: 54px 42px;
            background: rgba(255,255,255,0.28);
            border: 1px solid rgba(196,122,148,0.35);
            border-radius: 32px;
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
            box-shadow: 0 30px 70px rgba(161,88,115,0.16);
        }
        .monogram-ring {
            width: 90px; height: 90px;
            margin: 0 auto 20px;
            border-radius: 50%;
            border: 1.5px dashed var(--plum);
            display: flex; align-items: center; justify-content: center;
            font-family: 'Fraunces', serif;
            font-size: 1.6rem;
            font-weight: 600;
            letter-spacing: 1px;
            color: var(--plum-dark);
            background: rgba(196,122,148,0.08);
        }
        .eyebrow {
            font-size: 0.7rem; font-weight: 600; letter-spacing: 3px;
            text-transform: uppercase; color: var(--sage-dark); margin-bottom: 12px;
        }
        .guest-line {
            font-family: 'Cormorant Garamond', serif;
            font-style: italic; font-weight: 500;
            font-size: clamp(1.1rem, 2.2vw, 1.4rem);
            color: var(--ink-mid);
            margin-bottom: 18px;
        }
        .reserved-note {
            margin: 0 auto 22px;
            background: rgba(196,122,148,0.1);
            border: 1px solid rgba(196,122,148,0.35);
            padding: 9px 18px;
            border-radius: 40px;
            display: inline-flex; align-items: center; gap: 10px;
            color: var(--plum-dark); font-size: 0.82rem; font-weight: 600;
        }
        .reserved-note i { color: var(--plum); width: 16px; }
        .couple-title {
            font-family: 'Fraunces', serif;
            font-weight: 600;
            font-size: clamp(2.3rem, 6vw, 3.6rem);
            line-height: 1.1;
            color: var(--ink);
            margin-bottom: 20px;
        }
        .couple-title .amp {
            display: block;
            font-family: 'Cormorant Garamond', serif;
            font-style: italic; font-weight: 500;
            font-size: 0.48em;
            color: var(--plum);
            margin: 4px 0;
        }
        .date-chip {
            display: inline-flex; flex-direction: column; gap: 3px;
            border: 1px solid rgba(196,122,148,0.3);
            border-radius: 16px; padding: 12px 24px;
            width: fit-content; margin: 0 auto;
            background: rgba(255,255,255,0.5);
        }
        .date-chip .lbl { font-size: 0.63rem; letter-spacing: 2.5px; text-transform: uppercase; color: var(--ink-light); }
        .date-chip .val { font-family: 'Fraunces', serif; font-size: 1.15rem; font-weight: 500; color: var(--plum-dark); }

        .hero-venue {
            margin-top: 18px;
            font-family: 'Inter', sans-serif;
            font-size: 0.92rem;
            font-weight: 500;
            color: var(--sage-dark);
            display: flex; align-items: center; justify-content: center; gap: 8px;
        }
        .hero-venue i { color: var(--plum); }

        .scroll-cue {
            position: absolute; bottom: 26px; left: 50%; transform: translateX(-50%);
            z-index: 2; color: var(--plum-dark); font-size: 0.65rem;
            letter-spacing: 2px; text-transform: uppercase;
            display: flex; flex-direction: column; align-items: center; gap: 8px; opacity: 0.75;
        }
        .scroll-cue span.line { width: 1px; height: 26px; background: linear-gradient(var(--plum), transparent); animation: scrollLine 1.8s ease-in-out infinite; }
        @keyframes scrollLine { 0%,100% { opacity: 0.3; } 50% { opacity: 1; } }

        @media (max-width: 760px) { .hero-card { padding: 38px 24px; } }

        /* ======= COUNTDOWN ======= */
        .countdown-section {
            background: rgba(196,122,148,0.08);
            border-radius: 30px;
            max-width: 640px;
            margin: 34px auto;
            padding: 30px 20px;
            text-align: center;
        }
        .countdown-label { font-size: 0.68rem; letter-spacing: 2.5px; text-transform: uppercase; color: var(--plum-dark); margin-bottom: 16px; }
        .countdown { display: flex; justify-content: center; align-items: center; gap: 14px; flex-wrap: wrap; }
        .time-unit {
            text-align: center; min-width: 74px; padding: 14px 6px;
            border-radius: 16px; background: rgba(255,255,255,0.6);
            border: 1px solid rgba(196,122,148,0.2);
        }
        .time-value { display: block; font-family: 'Fraunces', serif; font-size: clamp(1.7rem, 5vw, 2.3rem); font-weight: 600; color: var(--plum-dark); line-height: 1; }
        .time-label { font-size: 0.58rem; letter-spacing: 1.5px; text-transform: uppercase; color: var(--sage-dark); margin-top: 5px; }
        .time-sep { display: none; }
        .just-married-msg { font-family: 'Fraunces', serif; font-size: 2rem; font-style: italic; color: var(--plum-dark); }

        /* ======= BODY ======= */
        .invitation-body { max-width: 760px; margin: 0 auto; padding: 0 20px; }
        .section-head { text-align: center; margin: 70px 0 36px; }
        .section-head .tag { font-size: 0.7rem; letter-spacing: 2.5px; text-transform: uppercase; color: var(--sage-dark); display: block; margin-bottom: 10px; }
        .section-head h2 { font-family: 'Fraunces', serif; font-weight: 600; font-size: clamp(1.8rem, 5vw, 2.5rem); color: var(--ink); }
        .section-head h2 em { font-style: italic; color: var(--plum); }

        /* Love story */
        .letter-card {
            background: var(--white);
            border: 2px dashed rgba(196,122,148,0.25);
            border-radius: 26px;
            padding: 42px 38px;
            position: relative;
            font-family: 'Cormorant Garamond', serif;
            font-style: italic;
            font-size: 1.25rem;
            line-height: 1.85;
            color: var(--ink-mid);
            box-shadow: 0 20px 50px rgba(161,88,115,0.08);
        }
        .letter-card::before {
            content: '\1F33F';
            position: absolute;
            top: -18px; left: 34px;
            font-size: 2rem;
            background: var(--parchment);
            padding: 0 8px;
        }

        /* Programme timeline */
        .timeline { position: relative; padding-left: 74px; }
        .timeline::before { content: ''; position: absolute; left: 29px; top: 6px; bottom: 6px; width: 2px; background: repeating-linear-gradient(to bottom, var(--plum-light) 0 6px, transparent 6px 12px); }
        .tl-item { position: relative; margin-bottom: 28px; }
        .tl-marker {
            position: absolute; left: -74px; top: 0; width: 60px; height: 60px;
            border-radius: 40% 60% 55% 45% / 55% 45% 60% 40%;
            background: linear-gradient(150deg, var(--plum), var(--plum-dark));
            color: var(--parchment);
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            font-family: 'Fraunces', serif; line-height: 1.1;
            box-shadow: 0 8px 22px rgba(161,88,115,0.25);
        }
        .tl-marker .d { font-size: 1.25rem; font-weight: 600; }
        .tl-marker .m { font-size: 0.58rem; letter-spacing: 1px; text-transform: uppercase; }
        .tl-card { background: var(--white); border: 1px solid var(--parchment-border); border-radius: 20px; padding: 24px 26px; transition: box-shadow 0.25s, transform 0.25s; }
        .tl-card:hover { box-shadow: 0 16px 36px rgba(161,88,115,0.1); transform: translateY(-2px); }
        .tl-name { font-family: 'Fraunces', serif; font-weight: 600; font-size: 1.4rem; color: var(--ink); margin-bottom: 10px; }
        .tl-meta { display: flex; flex-direction: column; gap: 5px; margin-bottom: 14px; }
        .tl-meta-item { display: flex; align-items: center; gap: 9px; font-size: 0.86rem; color: var(--ink-mid); }
        .tl-meta-item i { color: var(--plum); width: 15px; text-align: center; }
        .tl-actions { display: flex; gap: 9px; flex-wrap: wrap; margin-top: 14px; }
        .btn-map {
            display: inline-flex; align-items: center; gap: 6px;
            background: linear-gradient(135deg, var(--plum), var(--plum-dark));
            color: white; text-decoration: none;
            padding: 9px 18px; border-radius: 40px;
            font-size: 0.78rem; font-weight: 700;
            transition: all 0.2s;
        }
        .btn-map:hover { filter: brightness(1.08); color: white; transform: translateY(-1px); }
        .btn-cal {
            display: inline-flex; align-items: center; gap: 6px;
            background: transparent; border: 1px solid var(--parchment-border);
            color: var(--ink-mid); text-decoration: none;
            padding: 9px 18px; border-radius: 40px;
            font-size: 0.78rem; font-weight: 500; cursor: pointer; transition: all 0.2s;
        }
        .btn-cal:hover { border-color: var(--plum); color: var(--plum); }
        .cal-dropdown { position: relative; display: inline-block; }
        .cal-menu { display: none; position: absolute; bottom: calc(100% + 8px); left: 0; background: white; border: 1px solid var(--parchment-border); border-radius: 14px; box-shadow: 0 14px 34px rgba(60,47,52,0.14); min-width: 180px; z-index: 10; overflow: hidden; }
        .cal-menu.open { display: block; }
        .cal-menu a { display: flex; align-items: center; gap: 10px; padding: 11px 15px; font-size: 0.82rem; color: var(--ink-mid); text-decoration: none; transition: background 0.15s; }
        .cal-menu a:hover { background: var(--parchment-2); color: var(--plum); }
        .cal-menu a i { width: 16px; text-align: center; }

        /* ======= GALLERY : rounded photo carousel ======= */
        .carousel-wrap { position: relative; padding: 10px 0 30px; }
        .carousel-track-outer { overflow: hidden; }
        .carousel-track {
            display: flex;
            gap: 26px;
            overflow-x: auto;
            scroll-snap-type: x mandatory;
            padding: 20px 50px 26px;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
        }
        .carousel-track::-webkit-scrollbar { display: none; }
        .polaroid {
            flex: 0 0 auto;
            scroll-snap-align: center;
            width: 168px; height: 168px;
            border-radius: 50%;
            overflow: hidden;
            cursor: pointer;
            border: 4px solid var(--white);
            box-shadow: 0 14px 30px rgba(161,88,115,0.22);
            transition: transform 0.3s, box-shadow 0.3s;
            position: relative;
        }
        .polaroid::after {
            content: '';
            position: absolute; inset: 0;
            border-radius: 50%;
            border: 2px dashed rgba(196,122,148,0.5);
            transform: scale(1.08);
            pointer-events: none;
        }
        .polaroid:hover { transform: scale(1.07); box-shadow: 0 20px 40px rgba(161,88,115,0.32); }
        .polaroid img { width: 100%; height: 100%; object-fit: cover; display: block; }

        .car-btn {
            position: absolute; top: 50%; transform: translateY(-50%);
            width: 44px; height: 44px; border-radius: 50%;
            background: var(--white); border: 1px solid var(--parchment-border);
            color: var(--plum-dark); display: flex; align-items: center; justify-content: center;
            cursor: pointer; box-shadow: 0 8px 20px rgba(161,88,115,0.15);
            z-index: 3; transition: all 0.2s;
        }
        .car-btn:hover { background: var(--plum); color: white; }
        .car-prev { left: -6px; }
        .car-next { right: -6px; }
        @media (max-width: 640px) { .car-btn { width: 36px; height: 36px; font-size: 0.8rem; } }

        /* Lightbox */
        .lightbox { display: none; position: fixed; inset: 0; background: rgba(60,47,52,0.9); z-index: 1000; align-items: center; justify-content: center; }
        .lightbox.open { display: flex; }
        .lightbox img { max-width: 90vw; max-height: 90vh; border-radius: 12px; object-fit: contain; box-shadow: 0 30px 80px rgba(0,0,0,0.4); }
        .lightbox-close { position: absolute; top: 20px; right: 24px; color: var(--parchment); font-size: 1.8rem; cursor: pointer; opacity: 0.85; transition: opacity 0.2s; }
        .lightbox-close:hover { opacity: 1; }

        /* Guest shared gallery */
        .section-divider { display: flex; align-items: center; gap: 16px; max-width: 760px; margin: 70px auto 0; padding: 0 20px; }
        .section-divider-line { flex: 1; height: 1px; background: linear-gradient(to right, transparent, var(--plum)); }
        .section-divider-line.right { background: linear-gradient(to left, transparent, var(--plum)); }
        .section-divider-icon { width: 44px; height: 44px; border-radius: 50%; border: 1px solid var(--plum); display: flex; align-items: center; justify-content: center; color: var(--plum-dark); background: rgba(196,122,148,0.08); flex-shrink: 0; }
        .section-heading { text-align: center; font-family: 'Fraunces', serif; font-weight: 600; font-size: clamp(1.8rem, 5vw, 2.5rem); color: var(--ink); margin-top: 20px; }
        .section-heading em { font-style: italic; color: var(--plum); }
        .section-sub { text-align: center; color: var(--ink-mid); font-size: 0.92rem; margin: 8px 0 30px; max-width: 760px; margin-left: auto; margin-right: auto; padding: 0 20px; }

        .gallery-grid { display: flex; flex-wrap: nowrap; overflow-x: auto; gap: 15px; padding: 10px 0; scroll-behavior: smooth; -webkit-overflow-scrolling: touch; scrollbar-width: none; -ms-overflow-style: none; }
        .gallery-grid::-webkit-scrollbar { display: none; }
        .gallery-item { flex: 0 0 auto; width: 200px; height: 200px; border-radius: 50%; overflow: hidden; position: relative; cursor: pointer; border: 3px solid var(--white); box-shadow: 0 10px 24px rgba(161,88,115,0.18); transition: transform 0.3s; }
        .gallery-item img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .gallery-item:hover { transform: translateY(-4px) scale(1.03); }

        /* ======= RSVP ======= */
        .rsvp-section { background: linear-gradient(180deg, var(--parchment), var(--parchment-2)); padding: 70px 20px; margin-top: 20px; }
        .rsvp-card { max-width: 800px; margin: 0 auto; background: white; border-radius: 34px; overflow: hidden; display: grid; grid-template-columns: 0.85fr 1.15fr; box-shadow: 0 26px 60px rgba(161,88,115,0.14); }
        .rsvp-aside { background: linear-gradient(160deg, var(--plum), var(--plum-dark), var(--sage-dark)); color: var(--parchment); padding: 46px 36px; display: flex; flex-direction: column; justify-content: center; }
        .rsvp-aside .quote-mark { font-family: 'Fraunces', serif; font-size: 3.2rem; opacity: 0.55; line-height: 1; margin-bottom: 12px; }
        .rsvp-aside p { font-family: 'Cormorant Garamond', serif; font-style: italic; font-size: 1.25rem; line-height: 1.75; }
        .rsvp-form-side { padding: 46px 36px; }
        .rsvp-title { font-family: 'Fraunces', serif; font-weight: 600; font-size: 2rem; color: var(--ink); margin-bottom: 4px; }
        .rsvp-subtitle { font-size: 0.78rem; color: var(--ink-light); letter-spacing: 1px; text-transform: uppercase; margin-bottom: 24px; }
        .rsvp-options { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 18px; }
        .rsvp-option input[type="radio"] { display: none; }
        .rsvp-option label { display: flex; flex-direction: column; align-items: center; gap: 7px; padding: 18px 10px; border: 2px solid var(--parchment-border); border-radius: 20px; cursor: pointer; font-size: 0.8rem; font-weight: 500; color: var(--ink-mid); transition: all 0.2s; }
        .rsvp-option label i { font-size: 1.3rem; }
        .rsvp-option:first-child input[type="radio"]:checked + label { border-color: var(--sage-dark); background: rgba(143,172,122,0.12); color: var(--sage-dark); }
        .rsvp-option:last-child input[type="radio"]:checked + label { border-color: #b5687f; background: rgba(181,104,127,0.1); color: #a15873; }
        .rsvp-note { width: 100%; background: var(--parchment); border: 1px solid var(--parchment-border); border-radius: 16px; padding: 13px 15px; font-family: 'Inter', sans-serif; font-size: 0.86rem; color: var(--ink-mid); resize: none; outline: none; transition: border-color 0.2s; margin-bottom: 16px; }
        .rsvp-note:focus { border-color: var(--plum); }
        .btn-rsvp-submit { width: 100%; background: linear-gradient(135deg, var(--plum), var(--plum-dark)); color: var(--parchment); border: none; border-radius: 40px; padding: 16px; font-size: 0.86rem; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; cursor: pointer; transition: all 0.25s; }
        .btn-rsvp-submit:hover { filter: brightness(1.06); transform: translateY(-2px); }
        @media (max-width: 640px) { .rsvp-card { grid-template-columns: 1fr; } .rsvp-aside { padding: 32px 26px; } .rsvp-form-side { padding: 32px 26px; } }

        /* ======= FOOTER ======= */
        .inv-footer { text-align: center; padding: 46px 20px; font-size: 0.75rem; color: var(--ink-light); border-top: 1px solid var(--parchment-border); background: var(--parchment); }
        .inv-footer .brand { font-family: 'Fraunces', serif; font-style: italic; font-size: 1.35rem; color: var(--plum-dark); display: block; margin-bottom: 6px; }
    </style>
</head>
<body>

<canvas id="hero-canvas"></canvas>

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
<div class="hero" <?php echo $hero_style; ?>>
    <div class="hero-card reveal">
        <div class="monogram-ring"><?php echo htmlspecialchars($monogram); ?></div>
        <span class="eyebrow"><?php echo t('hero_eyebrow'); ?></span>
        <p class="guest-line"><?php echo t('hero_dear'); ?> <?php echo htmlspecialchars($guest_name); ?>,</p>

        <h1 class="couple-title">
            <?php echo htmlspecialchars($wedding['bride_name']); ?>
            <span class="amp">&amp;</span>
            <?php echo htmlspecialchars($wedding['groom_name']); ?>
        </h1>

        <div class="date-chip">
            <span class="lbl"><?php echo t('hero_getting_married'); ?></span>
            <span class="val"><?php echo t_date($wedding['wedding_date']); ?></span>
        </div>

        <?php if (!empty($wedding['venue'])): ?>
        <p class="hero-venue"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($wedding['venue']); ?></p>
        <?php endif; ?>
    </div>
    <div class="scroll-cue"><span class="line"></span><?php echo t('hero_scroll_cue'); ?></div>
</div>

<!-- COUNTDOWN -->
<div class="countdown-section">
    <p class="countdown-label"><?php echo t('countdown_label'); ?></p>
    <div class="countdown" id="countdown">
        <div class="time-unit"><span class="time-value" id="cd-days">00</span><span class="time-label"><?php echo t('cd_days'); ?></span></div>
        <div class="time-unit"><span class="time-value" id="cd-hours">00</span><span class="time-label"><?php echo t('cd_hours'); ?></span></div>
        <div class="time-unit"><span class="time-value" id="cd-mins">00</span><span class="time-label"><?php echo t('cd_mins'); ?></span></div>
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

    <!-- GALLERY : rounded photo carousel -->
    <?php if (count($gallery_images) > 0): ?>
    <div class="section-head reveal">
        <span class="tag"><?php echo t('gallery_tag'); ?></span>
        <h2><?php echo t('gallery_title'); ?></h2>
    </div>
    <div class="carousel-wrap reveal">
        <button type="button" class="car-btn car-prev" onclick="moveCarousel(-1)" aria-label="Previous"><i class="fas fa-chevron-left"></i></button>
        <div class="carousel-track-outer">
            <div class="carousel-track" id="gallery-grid">
                <?php foreach ($gallery_images as $img): ?>
                <div class="polaroid" onclick="openLightbox('<?php echo htmlspecialchars($img['image_path']); ?>')">
                    <img src="<?php echo htmlspecialchars($img['image_path']); ?>" alt="Our moment" loading="lazy">
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <button type="button" class="car-btn car-next" onclick="moveCarousel(1)" aria-label="Next"><i class="fas fa-chevron-right"></i></button>
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
    <div style="max-width:760px; margin:0 auto 30px; background: var(--cream-2); border: 2px dashed var(--plum); border-radius: 20px; padding: 30px 20px; text-align: center;">
        <?php if ($guest_id == 0): ?>
            <p class="text-muted small"><i class="fas fa-lock"></i> <?php echo t('upload_disabled_preview'); ?></p>
        <?php else: ?>
            <i class="fas fa-camera-retro" style="font-size: 2.2rem; color: var(--plum); margin-bottom: 12px; display: block;"></i>
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
    <div class="gallery-grid" id="guest-gallery-grid" style="max-width:760px; margin:0 auto 30px; padding-left:20px; padding-right:20px;">
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
                                anime({ targets: item, opacity: [0, 1], scale: [0.8, 1], easing: 'easeOutBack', duration: 700 });
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
    <div class="rsvp-card text-center shadow rounded-5 p-4">
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
                            <i class="fas fa-heart" style="color:#63805a;"></i>
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

<!-- Three.js (hero petal scene) + anime.js (scroll reveals) -->
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

// ================= Sweet Moments carousel =================
function moveCarousel(dir) {
    const track = document.getElementById('gallery-grid');
    if (!track) return;
    const item = track.querySelector('.polaroid');
    const step = item ? (item.offsetWidth + 26) : 194;
    track.scrollBy({ left: dir * step * 2, behavior: 'smooth' });
}
(function autoplayCarousel() {
    const track = document.getElementById('gallery-grid');
    if (!track) return;
    let timer = null;
    function start() {
        timer = setInterval(() => {
            const atEnd = track.scrollLeft + track.clientWidth >= track.scrollWidth - 5;
            if (atEnd) {
                track.scrollTo({ left: 0, behavior: 'smooth' });
            } else {
                moveCarousel(1);
            }
        }, 3200);
    }
    function stop() { clearInterval(timer); }
    track.addEventListener('mouseenter', stop);
    track.addEventListener('mouseleave', start);
    track.addEventListener('touchstart', stop, { passive: true });
    track.addEventListener('touchend', start);
    start();
})();

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
            track.scrollBy({ left: 220, behavior: 'smooth' });
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

// ================= THREE.JS — falling petals hero scene =================
(function initHeroScene() {
    const canvas = document.getElementById('hero-canvas');
    if (!canvas || typeof THREE === 'undefined') return;

    const renderer = new THREE.WebGLRenderer({ canvas, alpha: true, antialias: true });
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));

    const scene = new THREE.Scene();
    const camera = new THREE.PerspectiveCamera(45, window.innerWidth / window.innerHeight, 0.1, 100);
    camera.position.set(0, 0, 9);

    function resize() {
        const w = window.innerWidth, h = window.innerHeight;
        renderer.setSize(w, h);
        camera.aspect = w / h;
        camera.updateProjectionMatrix();
    }
    resize();
    window.addEventListener('resize', resize);

    const ambient = new THREE.AmbientLight(0xffe4ec, 0.9);
    scene.add(ambient);
    const key = new THREE.PointLight(0xe7b9cb, 1.6, 30);
    key.position.set(4, 5, 6);
    scene.add(key);

    // Petal shape (simple 5-lobe flower silhouette)
    function makePetalGeometry() {
        const shape = new THREE.Shape();
        shape.moveTo(0, 0);
        shape.bezierCurveTo(0.35, 0.15, 0.4, 0.55, 0, 0.85);
        shape.bezierCurveTo(-0.4, 0.55, -0.35, 0.15, 0, 0);
        return new THREE.ShapeGeometry(shape);
    }
    const petalGeo = makePetalGeometry();
    const petalColors = [0xe7b9cb, 0xc47a94, 0xfbeef1, 0xd9a5b8];

    const petals = [];
    const petalCount = 46;
    for (let i = 0; i < petalCount; i++) {
        const mat = new THREE.MeshStandardMaterial({
            color: petalColors[i % petalColors.length],
            side: THREE.DoubleSide,
            roughness: 0.6,
            metalness: 0.05,
            transparent: true,
            opacity: 0.9
        });
        const mesh = new THREE.Mesh(petalGeo, mat);
        mesh.position.set((Math.random() - 0.5) * 14, Math.random() * 12 - 4, (Math.random() - 0.5) * 6 - 2);
        mesh.rotation.z = Math.random() * Math.PI * 2;
        mesh.scale.setScalar(0.5 + Math.random() * 0.7);
        mesh.userData = {
            fallSpeed: 0.35 + Math.random() * 0.5,
            swaySpeed: 0.5 + Math.random() * 0.8,
            swayAmp: 0.6 + Math.random() * 0.8,
            rotSpeed: (Math.random() - 0.5) * 0.6,
            baseX: mesh.position.x
        };
        petals.push(mesh);
        scene.add(mesh);
    }

    let mouseX = 0;
    window.addEventListener('mousemove', (e) => { mouseX = (e.clientX / window.innerWidth - 0.5); });

    const clock = new THREE.Clock();
    function animate() {
        requestAnimationFrame(animate);
        const t = clock.getElapsedTime();
        petals.forEach(p => {
            p.position.y -= p.userData.fallSpeed * 0.016;
            p.position.x = p.userData.baseX + Math.sin(t * p.userData.swaySpeed) * p.userData.swayAmp;
            p.rotation.z += p.userData.rotSpeed * 0.016;
            if (p.position.y < -6) { p.position.y = 6; }
        });
        camera.position.x += (mouseX * 1 - camera.position.x) * 0.02;
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
                    easing: 'easeOutCubic',
                    duration: 800,
                    delay: 60
                });
                io.unobserve(entry.target);
            }
        });
    }, { threshold: 0.15 });
    els.forEach(el => io.observe(el));

    anime({ targets: '.hero-card', opacity: [0, 1], translateY: [24, 0], easing: 'easeOutCubic', duration: 1000, delay: 200 });
    anime({ targets: '.monogram-ring', scale: [0.7, 1], opacity: [0, 1], easing: 'easeOutBack', duration: 900, delay: 400 });
})();
</script>
</body>
</html>