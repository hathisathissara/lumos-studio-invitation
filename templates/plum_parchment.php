<?php
$hero_style = "";
if (!empty($wedding['hero_image'])) {
    $img_path = htmlspecialchars($wedding['hero_image']);
    $hero_style = "style=\"background: url('{$img_path}') center/cover no-repeat;\"";
}
$monogram = strtoupper(substr($wedding['bride_name'] ?? '', 0, 1)) . strtoupper(substr($wedding['groom_name'] ?? '', 0, 1));
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
        }

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
            z-index: 100;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }
        .preview-bar a { color: var(--blush); text-decoration: underline; text-underline-offset: 3px; }

        /* ======= HERO : split layout ======= */
        .hero {
            display: grid;
            grid-template-columns: 1fr 1fr;
            min-height: 560px;
        }
        .hero-media {
            position: relative;
            background: linear-gradient(160deg, var(--plum-dark), var(--sage-dark));
            background-size: cover;
            background-position: center;
            display: flex;
            align-items: flex-end;
            justify-content: flex-start;
            min-height: 320px;
        }
        .wax-seal {
            margin: 28px;
            width: 88px;
            height: 88px;
            border-radius: 50%;
            background: var(--plum);
            border: 3px solid rgba(248,242,233,0.85);
            color: var(--parchment);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Fraunces', serif;
            font-size: 1.7rem;
            font-weight: 600;
            letter-spacing: 1px;
            box-shadow: 0 10px 26px rgba(46,42,40,0.35);
        }
        .hero-panel {
            background: var(--parchment);
            padding: 60px 44px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .eyebrow {
            font-size: 0.72rem;
            font-weight: 600;
            letter-spacing: 2.5px;
            text-transform: uppercase;
            color: var(--sage-dark);
            margin-bottom: 14px;
        }
        .guest-line {
            font-family: 'Fraunces', serif;
            font-style: italic;
            font-weight: 400;
            font-size: clamp(1.2rem, 2.4vw, 1.6rem);
            color: var(--ink-mid);
            margin-bottom: 22px;
        }

        .reserved-note {
            margin: 0 0 26px;
            background: var(--parchment-2);
            border-left: 3px solid var(--plum);
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
        .reserved-note i { color: var(--plum); width: 16px; }

        .couple-title {
            font-family: 'Fraunces', serif;
            font-weight: 600;
            font-size: clamp(2.4rem, 5.5vw, 4rem);
            line-height: 1.1;
            color: var(--ink);
            margin-bottom: 24px;
        }
        .couple-title .amp {
            display: block;
            font-style: italic;
            font-weight: 400;
            font-size: 0.5em;
            color: var(--plum);
            margin: 2px 0;
        }
        .date-chip {
            display: inline-flex;
            flex-direction: column;
            gap: 2px;
            border: 1px solid var(--parchment-border);
            border-radius: 12px;
            padding: 12px 18px;
            width: fit-content;
        }
        .date-chip .lbl {
            font-size: 0.65rem;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: var(--ink-light);
        }
        .date-chip .val {
            font-family: 'Fraunces', serif;
            font-size: 1.2rem;
            font-weight: 500;
            color: var(--plum-dark);
        }

        @media (max-width: 760px) {
            .hero { grid-template-columns: 1fr; }
            .hero-media { min-height: 240px; }
            .hero-panel { padding: 40px 24px; }
        }

        /* ======= COUNTDOWN : ticket strip ======= */
        .countdown-section {
            background: var(--plum-dark);
            padding: 26px 20px;
            text-align: center;
            position: relative;
        }
        .countdown-section::before,
        .countdown-section::after {
            content: '';
            position: absolute;
            top: 50%;
            width: 22px; height: 22px;
            background: var(--parchment);
            border-radius: 50%;
            transform: translateY(-50%);
        }
        .countdown-section::before { left: -11px; }
        .countdown-section::after { right: -11px; }
        .countdown-label {
            font-size: 0.68rem;
            letter-spacing: 2.5px;
            text-transform: uppercase;
            color: var(--plum-light);
            margin-bottom: 14px;
        }
        .countdown {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 14px;
            flex-wrap: wrap;
        }
        .time-unit { text-align: center; min-width: 58px; }
        .time-value {
            display: block;
            font-family: 'Fraunces', serif;
            font-size: clamp(1.8rem, 5vw, 2.4rem);
            font-weight: 600;
            color: var(--parchment);
            line-height: 1;
        }
        .time-label {
            font-size: 0.6rem;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: var(--plum-light);
            margin-top: 4px;
        }
        .time-sep { font-size: 1.4rem; color: var(--plum-light); margin-bottom: 14px; }
        .just-married-msg { font-family: 'Fraunces', serif; font-size: 2rem; font-style: italic; color: var(--parchment); }

        /* ======= BODY ======= */
        .invitation-body { max-width: 720px; margin: 0 auto; padding: 0 20px; }

        .section-head { text-align: center; margin: 56px 0 34px; }
        .section-head .tag {
            font-size: 0.7rem;
            letter-spacing: 2.5px;
            text-transform: uppercase;
            color: var(--sage-dark);
            display: block;
            margin-bottom: 8px;
        }
        .section-head h2 {
            font-family: 'Fraunces', serif;
            font-weight: 600;
            font-size: clamp(1.8rem, 5vw, 2.4rem);
            color: var(--ink);
        }
        .section-head h2 em { font-style: italic; color: var(--plum); }

        /* Love story: folded letter card */
        .letter-card {
            background: var(--white);
            border: 1px solid var(--parchment-border);
            border-radius: 4px;
            padding: 36px 32px;
            position: relative;
            font-family: 'Fraunces', serif;
            font-style: italic;
            font-size: 1.1rem;
            line-height: 1.9;
            color: var(--ink-mid);
            box-shadow: 0 14px 34px rgba(46,42,40,0.06);
        }
        .letter-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 5px;
            background: linear-gradient(to right, var(--plum), var(--sage));
        }

        /* Programme: vertical timeline */
        .timeline { position: relative; padding-left: 68px; }
        .timeline::before {
            content: '';
            position: absolute;
            left: 27px; top: 6px; bottom: 6px;
            width: 2px;
            background: repeating-linear-gradient(to bottom, var(--parchment-border) 0 6px, transparent 6px 12px);
        }
        .tl-item { position: relative; margin-bottom: 26px; }
        .tl-marker {
            position: absolute;
            left: -68px; top: 0;
            width: 56px; height: 56px;
            border-radius: 12px;
            background: var(--plum-dark);
            color: var(--parchment);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            font-family: 'Fraunces', serif;
            line-height: 1.1;
        }
        .tl-marker .d { font-size: 1.3rem; font-weight: 600; }
        .tl-marker .m { font-size: 0.6rem; letter-spacing: 1px; text-transform: uppercase; }
        .tl-card {
            background: var(--white);
            border: 1px solid var(--parchment-border);
            border-radius: 14px;
            padding: 22px 24px;
        }
        .tl-name { font-family: 'Fraunces', serif; font-weight: 600; font-size: 1.35rem; color: var(--ink); margin-bottom: 10px; }
        .tl-meta { display: flex; flex-direction: column; gap: 5px; margin-bottom: 14px; }
        .tl-meta-item { display: flex; align-items: center; gap: 9px; font-size: 0.86rem; color: var(--ink-mid); }
        .tl-meta-item i { color: var(--plum); width: 15px; text-align: center; }
        .tl-actions { display: flex; gap: 9px; flex-wrap: wrap; margin-top: 14px; }
        .btn-map {
            display: inline-flex; align-items: center; gap: 6px;
            background: var(--plum);
            color: white; text-decoration: none;
            padding: 8px 16px; border-radius: 8px;
            font-size: 0.78rem; font-weight: 600;
            transition: all 0.2s;
        }
        .btn-map:hover { background: var(--plum-dark); color: white; }
        .btn-cal {
            display: inline-flex; align-items: center; gap: 6px;
            background: transparent;
            border: 1px solid var(--parchment-border);
            color: var(--ink-mid);
            text-decoration: none;
            padding: 8px 16px; border-radius: 8px;
            font-size: 0.78rem; font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-cal:hover { border-color: var(--plum); color: var(--plum); }

        .cal-dropdown { position: relative; display: inline-block; }
        .cal-menu {
            display: none;
            position: absolute;
            bottom: calc(100% + 8px);
            left: 0;
            background: white;
            border: 1px solid var(--parchment-border);
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(46,42,40,0.12);
            min-width: 180px;
            z-index: 10;
            overflow: hidden;
        }
        .cal-menu.open { display: block; }
        .cal-menu a {
            display: flex; align-items: center; gap: 10px;
            padding: 11px 15px;
            font-size: 0.82rem;
            color: var(--ink-mid);
            text-decoration: none;
            transition: background 0.15s;
        }
        .cal-menu a:hover { background: var(--parchment-2); color: var(--plum); }
        .cal-menu a i { width: 16px; text-align: center; }

        /* Gallery: polaroid scatter */
        .polaroid-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 22px 16px;
            padding: 10px 6px 30px;
        }
        .polaroid {
            background: white;
            padding: 10px 10px 26px;
            box-shadow: 0 10px 22px rgba(46,42,40,0.12);
            cursor: pointer;
            transition: transform 0.25s;
            transform: rotate(-2deg);
        }
        .polaroid:nth-child(3n+1) { transform: rotate(-2deg); }
        .polaroid:nth-child(3n+2) { transform: rotate(1.5deg); }
        .polaroid:nth-child(3n)   { transform: rotate(-1deg); }
        .polaroid:hover { transform: rotate(0deg) scale(1.04); z-index: 2; }
        .polaroid img { width: 100%; aspect-ratio: 1; object-fit: cover; display: block; }

        /* Lightbox */
        .lightbox {
            display: none;
            position: fixed; inset: 0;
            background: rgba(20,16,15,0.92);
            z-index: 1000;
            align-items: center; justify-content: center;
        }
        .lightbox.open { display: flex; }
        .lightbox img { max-width: 90vw; max-height: 90vh; border-radius: 6px; object-fit: contain; }
        .lightbox-close {
            position: absolute; top: 20px; right: 20px;
            color: white; font-size: 1.8rem; cursor: pointer;
            opacity: 0.75; transition: opacity 0.2s;
        }
        .lightbox-close:hover { opacity: 1; }

        /* ======= RSVP : two column card ======= */
        .rsvp-section { background: var(--parchment-2); border-top: 1px solid var(--parchment-border); padding: 60px 20px; }
        .rsvp-card {
            max-width: 780px;
            margin: 0 auto;
            background: white;
            border-radius: 18px;
            overflow: hidden;
            display: grid;
            grid-template-columns: 0.85fr 1.15fr;
            box-shadow: 0 16px 44px rgba(46,42,40,0.08);
        }
        .rsvp-aside {
            background: linear-gradient(160deg, var(--plum-dark), var(--sage-dark));
            color: var(--parchment);
            padding: 44px 34px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .rsvp-aside .quote-mark { font-family: 'Fraunces', serif; font-size: 3rem; opacity: 0.5; line-height: 1; margin-bottom: 10px; }
        .rsvp-aside p {
            font-family: 'Fraunces', serif;
            font-style: italic;
            font-size: 1.15rem;
            line-height: 1.7;
        }
        .rsvp-form-side { padding: 44px 34px; }
        .rsvp-title { font-family: 'Fraunces', serif; font-weight: 600; font-size: 1.9rem; color: var(--ink); margin-bottom: 4px; }
        .rsvp-subtitle { font-size: 0.78rem; color: var(--ink-light); letter-spacing: 1px; text-transform: uppercase; margin-bottom: 24px; }

        .rsvp-options { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 18px; }
        .rsvp-option input[type="radio"] { display: none; }
        .rsvp-option label {
            display: flex; flex-direction: column; align-items: center; gap: 7px;
            padding: 16px 10px;
            border: 2px solid var(--parchment-border);
            border-radius: 12px;
            cursor: pointer;
            font-size: 0.8rem;
            font-weight: 500;
            color: var(--ink-mid);
            transition: all 0.2s;
        }
        .rsvp-option label i { font-size: 1.3rem; }
        .rsvp-option:first-child input[type="radio"]:checked + label {
            border-color: var(--sage-dark); background: rgba(138,154,126,0.1); color: var(--sage-dark);
        }
        .rsvp-option:last-child input[type="radio"]:checked + label {
            border-color: var(--blush); background: rgba(201,138,138,0.1); color: #a25c5c;
        }

        .rsvp-note {
            width: 100%;
            background: var(--parchment);
            border: 1px solid var(--parchment-border);
            border-radius: 12px;
            padding: 13px 15px;
            font-family: 'Inter', sans-serif;
            font-size: 0.86rem;
            color: var(--ink-mid);
            resize: none;
            outline: none;
            transition: border-color 0.2s;
            margin-bottom: 16px;
        }
        .rsvp-note:focus { border-color: var(--plum); }

        .btn-rsvp-submit {
            width: 100%;
            background: var(--plum);
            color: var(--parchment);
            border: none;
            border-radius: 12px;
            padding: 15px;
            font-size: 0.86rem;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            cursor: pointer;
            transition: all 0.25s;
        }
        .btn-rsvp-submit:hover { background: var(--plum-dark); transform: translateY(-2px); }

        @media (max-width: 640px) {
            .rsvp-card { grid-template-columns: 1fr; }
            .rsvp-aside { padding: 30px 26px; }
            .rsvp-form-side { padding: 30px 26px; }
        }

        /* ======= FOOTER ======= */
        .inv-footer { text-align: center; padding: 40px 20px; font-size: 0.75rem; color: var(--ink-light); border-top: 1px solid var(--parchment-border); }
        .inv-footer .brand { font-family: 'Fraunces', serif; font-style: italic; font-size: 1.3rem; color: var(--plum); display: block; margin-bottom: 6px; }
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
<div style="max-width:720px; margin:20px auto; padding:0 20px;">
    <?php echo $msg; ?>
</div>
<?php endif; ?>

<!-- HERO -->
<div class="hero">
    <div class="hero-media" <?php echo $hero_style; ?>>
        <div class="wax-seal"><?php echo htmlspecialchars($monogram); ?></div>
    </div>
    <div class="hero-panel">
        <span class="eyebrow">You're Warmly Invited</span>
        <p class="guest-line">Dear <?php echo htmlspecialchars($guest_name); ?>,</p>

<?php if (isset($current_guest['seats_reserved']) && $current_guest['seats_reserved'] > 0): ?>
    <div class="reserved-note">
        <i class="fas fa-chair"></i>
        <span>We have reserved <strong><?php echo intval($current_guest['seats_reserved']); ?></strong> seat(s) in your honor.</span>
    </div>
<?php endif; ?>

        <h1 class="couple-title">
            <?php echo htmlspecialchars($wedding['bride_name']); ?>
            <span class="amp">&amp;</span>
            <?php echo htmlspecialchars($wedding['groom_name']); ?>
        </h1>

        <div class="date-chip">
            <span class="lbl">We're getting married</span>
            <span class="val"><?php echo date("l, d F Y", strtotime($wedding['wedding_date'])); ?></span>
        </div>
    </div>
</div>

<!-- COUNTDOWN -->
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

<div class="invitation-body">

    <!-- LOVE STORY -->
    <?php if (!empty($wedding['love_story'])): ?>
    <div class="section-head">
        <span class="tag">How It All Began</span>
        <h2>Our <em>Love Story</em></h2>
    </div>
    <div class="letter-card">
        <?php echo nl2br(htmlspecialchars($wedding['love_story'])); ?>
    </div>
    <?php endif; ?>

    <!-- PROGRAMME -->
    <div class="section-head">
        <span class="tag">Join Us For These Celebrations</span>
        <h2><em>Wedding</em> Programme</h2>
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
            <div class="tl-item">
                <div class="tl-marker">
                    <span class="d"><?php echo date('d', strtotime($ev['event_date_time'])); ?></span>
                    <span class="m"><?php echo date('M', strtotime($ev['event_date_time'])); ?></span>
                </div>
                <div class="tl-card">
                    <div class="tl-name"><?php echo htmlspecialchars($ev['event_name']); ?></div>
                    <div class="tl-meta">
                        <div class="tl-meta-item"><i class="far fa-clock"></i><span><?php echo date("h:i A", strtotime($ev['event_date_time'])); ?></span></div>
                        <div class="tl-meta-item"><i class="fas fa-map-marker-alt"></i><span><?php echo htmlspecialchars($ev['location_name']); ?></span></div>
                    </div>
                    <div class="tl-actions">
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
            </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p style="text-align:center; color:var(--ink-light); font-style:italic; padding:30px 0;">Event details will be updated soon.</p>
    <?php endif; ?>

    <!-- GALLERY -->
    <?php if (count($gallery_images) > 0): ?>
    <div class="section-head">
        <span class="tag">Our Engagement Memories</span>
        <h2><em>Sweet</em> Moments</h2>
    </div>
    <div class="polaroid-grid" id="gallery-grid">
        <?php foreach ($gallery_images as $img): ?>
        <div class="polaroid" onclick="openLightbox('<?php echo htmlspecialchars($img['image_path']); ?>')">
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
    <div class="rsvp-card">
        <div class="rsvp-aside">
            <span class="quote-mark">&ldquo;</span>
            <p>Every love story is beautiful, but ours is our favorite. Come celebrate this new chapter with us.</p>
        </div>
        <div class="rsvp-form-side">
            <h2 class="rsvp-title">RSVP</h2>
            <p class="rsvp-subtitle">Will you be joining us?</p>

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
                            Joyfully Accept
                        </label>
                    </div>
                    <div class="rsvp-option">
                        <input type="radio" name="rsvp_status" id="rsvp-no" value="rejected"
                            <?php if ($current_guest['rsvp_status'] == 'rejected') echo 'checked'; ?>>
                        <label for="rsvp-no">
                            <i class="fas fa-heart-broken" style="color:#a25c5c;"></i>
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
</div>

<!-- FOOTER -->
<div class="inv-footer">
    <span class="brand">Lumus Studio</span>
    Digital Wedding Invitations · Designed by Hathisa Thissara
</div>

<script>
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