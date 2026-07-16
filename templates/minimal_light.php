<?php
require_once __DIR__ . '/includes/lang.php';
require_once __DIR__ . '/includes/music_library.php';
set_invite_lang($wedding['invite_language'] ?? 'en');

$hero_style = "";
if (!empty($wedding['hero_image'])) {
    $img_path = htmlspecialchars($wedding['hero_image']);
    $hero_style = "style=\"background: linear-gradient(180deg, rgba(255,255,255,0.55) 0%, rgba(255,255,255,0.9) 60%, transparent 100%), url('{$img_path}') center/cover no-repeat;\"";
}

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
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,400&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
            z-index: 10;
            pointer-events: none;
        }
        body > * { position: relative; z-index: 1; }
        #page-canvas { z-index: 10; }

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
    </style>
</head>
<body>

<canvas id="page-canvas"></canvas>

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
<div style="max-width:700px; margin:20px auto; padding:0 20px;">
    <?php echo $msg; ?>
</div>
<?php endif; ?>

<!-- HERO HEADER -->
<div class="hero-header" <?php echo $hero_style; ?>>
    <div class="hero-content reveal">
        <span class="guest-greeting-tag"><?php echo t('hero_eyebrow'); ?></span>
        <div class="guest-name-display"><?php echo t('hero_dear'); ?> <?php echo htmlspecialchars($guest_name); ?>,</div>

        <div class="couple-names-hero">
            <?php echo htmlspecialchars($wedding['bride_name']); ?>
            <span class="amp">&amp;</span>
            <?php echo htmlspecialchars($wedding['groom_name']); ?>
        </div>

        <div class="hero-date-area">
            <p class="hero-getting-married"><?php echo t('hero_getting_married'); ?></p>
            <p class="hero-date"><?php echo t_date($wedding['wedding_date']); ?></p>
            <?php if (!empty($wedding['venue'])): ?>
            <p class="hero-venue"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($wedding['venue']); ?></p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- COUNTDOWN TIMER -->
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
    <h2 class="section-heading reveal"><?php echo t('love_story_title'); ?></h2>
    <p class="section-sub"><?php echo t('love_story_tag'); ?></p>
    <div class="love-story-text reveal">
        <?php echo nl2br(htmlspecialchars($wedding['love_story'])); ?>
    </div>
    <?php endif; ?>

    <!-- EVENTS / PROGRAMME -->
    <h2 class="section-heading reveal"><?php echo t('programme_title'); ?></h2>
    <p class="section-sub"><?php echo t('programme_tag'); ?></p>

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
                    <div class="event-meta-item"><i class="far fa-calendar"></i><span><?php echo t_date($ev['event_date_time']); ?></span></div>
                    <div class="event-meta-item"><i class="far fa-clock"></i><span><?php echo t_time($ev['event_date_time']); ?></span></div>
                    <div class="event-meta-item"><i class="fas fa-map-marker-alt"></i><span><?php echo htmlspecialchars($ev['location_name']); ?></span></div>
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
        <p style="text-align:center; color:var(--ink-light); font-style:italic; padding:30px 0;"><?php echo t('event_details_soon'); ?></p>
    <?php endif; ?>

    <!-- GALLERY -->
    <?php if (count($gallery_images) > 0): ?>
    <h2 class="section-heading reveal"><?php echo t('gallery_title'); ?></h2>
    <p class="section-sub"><?php echo t('gallery_tag'); ?></p>

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
<div class="lightbox" id="lightbox" onclick="closeLightbox()">
    <span class="lightbox-close" onclick="closeLightbox()"><i class="fas fa-times"></i></span>
    <img src="" id="lightbox-img" alt="">
</div>
<!-- =====================================================================
         📸 GUEST SHARED GALLERY SECTION (පැකේජය අනුව පමණක් පෙන්වයි)
         ===================================================================== -->
    <?php if (isset($has_guest_gallery) && $has_guest_gallery): ?>
    <div class="invitation-body" style="padding-top:20px;">
    <h2 class="section-heading reveal"><?php echo t('guest_gallery_title'); ?></h2>
    <p class="section-sub">Capture and share your beautiful memories with us!</p>

    <!-- Upload Box (Preview mode එකේදී අක්‍රීය වේ) -->
    <div style="background: rgba(255,255,255,0.6); border: 2px dashed var(--gold); padding: 30px 20px; text-align: center; margin-bottom: 30px;">
        <?php if ($guest_id == 0): ?>
            <p class="text-muted small"><i class="fas fa-lock"></i> <?php echo t('upload_disabled_preview'); ?></p>
        <?php else: ?>
            <i class="fas fa-camera-retro" style="font-size: 2.2rem; color: var(--gold-dark); margin-bottom: 12px; display: block;"></i>
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
    <div class="rsvp-card reveal">
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
                        <i class="fas fa-heart" style="color:#22c55e;"></i>
                        <?php echo t('rsvp_accept'); ?>
                    </label>
                </div>
                <div class="rsvp-option">
                    <input type="radio" name="rsvp_status" id="rsvp-no" value="rejected"
                        <?php if ($current_guest['rsvp_status'] == 'rejected') echo 'checked'; ?>>
                    <label for="rsvp-no">
                        <i class="fas fa-heart-broken" style="color:#ef4444;"></i>
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

    // Lighting for the 3D butterfly models (shape-shaded, not flat/unlit)
    const pageAmbient = new THREE.AmbientLight(0xfff3e0, 0.8);
    scene.add(pageAmbient);
    const pageKey = new THREE.PointLight(0xb8935a, 1.6, 30);
    pageKey.position.set(4, 5, 6);
    scene.add(pageKey);
    const pageRim = new THREE.PointLight(0xffffff, 0.8, 30);
    pageRim.position.set(-5, -3, 4);
    scene.add(pageRim);

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
    // Built as an actual 3D model (extruded wings + a rounded body + antennae), not a flat shape
    function makeWingGeometry() {
        const shape = new THREE.Shape();
        shape.moveTo(0, 0);
        shape.bezierCurveTo(0.42, 0.32, 0.52, 0.62, 0.16, 0.78);
        shape.bezierCurveTo(-0.12, 0.6, -0.08, 0.28, 0, 0);
        return new THREE.ExtrudeGeometry(shape, { depth: 0.035, bevelEnabled: true, bevelThickness: 0.01, bevelSize: 0.01, bevelSegments: 1 });
    }
    const wingGeo = makeWingGeometry();
    const bodyGeo = new THREE.SphereGeometry(0.055, 10, 10);
    const antennaGeo = new THREE.CylinderGeometry(0.004, 0.004, 0.22, 6);
    const butterflyColors = [0xb8935a, 0x8f6f42, 0xded0b8, 0xc9a96b];

    function createButterfly(color) {
        const bfly = new THREE.Group();

        const wingMat = new THREE.MeshStandardMaterial({
            color, side: THREE.DoubleSide, transparent: true, opacity: 0.88,
            roughness: 0.4, metalness: 0.15, emissive: color, emissiveIntensity: 0.12
        });
        const wingL = new THREE.Mesh(wingGeo, wingMat);
        wingL.rotation.z = Math.PI / 2;
        wingL.position.x = -0.02;
        const wingR = new THREE.Mesh(wingGeo, wingMat.clone());
        wingR.rotation.z = Math.PI / 2;
        wingR.scale.x = -1;
        wingR.position.x = 0.02;

        // Rear (smaller) wing pair for a fuller silhouette
        const wingLBack = new THREE.Mesh(wingGeo, wingMat.clone());
        wingLBack.rotation.z = Math.PI / 2;
        wingLBack.position.set(-0.02, -0.15, -0.01);
        wingLBack.scale.setScalar(0.6);
        const wingRBack = new THREE.Mesh(wingGeo, wingMat.clone());
        wingRBack.rotation.z = Math.PI / 2;
        wingRBack.scale.x = -0.6;
        wingRBack.scale.y = 0.6;
        wingRBack.position.set(0.02, -0.15, -0.01);

        // Body: a small elongated sphere
        const bodyMat = new THREE.MeshStandardMaterial({ color: 0x3a2f22, roughness: 0.5, metalness: 0.1 });
        const body = new THREE.Mesh(bodyGeo, bodyMat);
        body.scale.set(0.7, 1.6, 0.7);

        // Antennae
        const antennaMat = new THREE.MeshStandardMaterial({ color: 0x3a2f22, roughness: 0.5 });
        const antL = new THREE.Mesh(antennaGeo, antennaMat);
        antL.position.set(-0.03, 0.16, 0);
        antL.rotation.z = 0.35;
        const antR = new THREE.Mesh(antennaGeo, antennaMat.clone());
        antR.position.set(0.03, 0.16, 0);
        antR.rotation.z = -0.35;

        bfly.add(wingL, wingR, wingLBack, wingRBack, body, antL, antR);
        bfly.userData.wingL = wingL;
        bfly.userData.wingR = wingR;
        bfly.userData.wingLBack = wingLBack;
        bfly.userData.wingRBack = wingRBack;
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
            b.position.x = b.userData.baseX + Math.sin(t * b.userData.swaySpeed) * b.userData.swayAmp;
            const flap = Math.sin(t * b.userData.flapSpeed) * 0.9;
            b.userData.wingL.rotation.y = flap;
            b.userData.wingR.rotation.y = -flap;
            b.userData.wingLBack.rotation.y = flap * 0.8;
            b.userData.wingRBack.rotation.y = -flap * 0.8;
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