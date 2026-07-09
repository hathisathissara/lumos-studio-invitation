<?php
$hero_style = "";
if (!empty($wedding['hero_image'])) {
    $img_path = htmlspecialchars($wedding['hero_image']);
    $hero_style = "style=\"background: linear-gradient(180deg, rgba(250,245,236,0.55) 0%, rgba(250,245,236,0.92) 55%, var(--cream) 100%), url('{$img_path}') center/cover no-repeat;\"";
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
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,500;1,600&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
            padding: 70px 20px 76px;
            text-align: center;
            position: relative;
            overflow: hidden;
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
    $hero_style = "style=\"background: linear-gradient(180deg, rgba(250,245,236,0.55) 0%, rgba(250,245,236,0.92) 55%, var(--cream) 100%), url('{$img_path}') center/cover no-repeat;\"";
}
?>
<div class="hero-header" <?php echo $hero_style; ?>>
    <div class="hero-ornament-top">❧</div>
    <div class="hero-content">
        <span class="guest-greeting-tag">You're Warmly Invited</span>
        <div class="guest-name-display">
            Dear <?php echo htmlspecialchars($guest_name); ?>,
        </div>
<?php if (isset($current_guest['seats_reserved']) && $current_guest['seats_reserved'] > 0): ?>
    <div class="reserved-note">
        <i class="fas fa-chair"></i>
        <span>
            We have reserved <strong><?php echo intval($current_guest['seats_reserved']); ?></strong> seat(s) in your honor.
        </span>
    </div>
<?php endif; ?>
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
            <p class="hero-getting-married">We are getting married on</p>
            <p class="hero-date"><?php echo date("l, d F Y", strtotime($wedding['wedding_date'])); ?></p>
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

<div class="invitation-body">

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
        <div class="event-timeline">
            <?php foreach ($wedding_events as $ev):
                $ev_start = date('Ymd\THis', strtotime($ev['event_date_time']));
                $ev_end = date('Ymd\THis', strtotime($ev['event_date_time']) + 7200);
                $ev_title = urlencode($ev['event_name'] . ' — ' . $wedding['bride_name'] . ' & ' . $wedding['groom_name']);
                $ev_loc = urlencode($ev['location_name']);
                $ev_gcal = "https://calendar.google.com/calendar/render?action=TEMPLATE&text={$ev_title}&dates={$ev_start}/{$ev_end}&location={$ev_loc}";
                $ev_ics = "calendar.php?wedding_id={$wedding_id}&event_id={$ev['id']}";
                $ev_outlook = "https://outlook.live.com/calendar/0/deeplink/compose?path=/calendar/action/compose&rru=addevent&subject=" . $ev_title . "&startdt=" . urlencode(date('c', strtotime($ev['event_date_time']))) . "&enddt=" . urlencode(date('c', strtotime($ev['event_date_time']) + 7200)) . "&location=" . $ev_loc;
            ?>
            <div class="event-card">
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

    <div class="rsvp-card">
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
                        <i class="fas fa-heart" style="color:#6f7d55;"></i>
                        Joyfully Accept
                    </label>
                </div>
                <div class="rsvp-option">
                    <input type="radio" name="rsvp_status" id="rsvp-no" value="rejected"
                        <?php if ($current_guest['rsvp_status'] == 'rejected') echo 'checked'; ?>>
                    <label for="rsvp-no">
                        <i class="fas fa-heart-broken" style="color:#b5473a;"></i>
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