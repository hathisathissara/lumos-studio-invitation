<?php
session_start();
require 'config/config.php';

$wedding_id = isset($_GET['w_id']) ? intval($_GET['w_id']) : 0;
$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';
$error = '';

// 1. Wedding & user status
if ($wedding_id > 0) {
    $stmtWed = $pdo->prepare("SELECT w.id as wedding_id, w.bride_name, w.groom_name, w.wedding_date, w.template_name, u.status, w.user_id 
                              FROM weddings w
                              JOIN users u ON w.user_id = u.id
                              WHERE w.id = ?");
    $stmtWed->execute([$wedding_id]);
} else if (!empty($slug)) {
    $stmtWed = $pdo->prepare("SELECT w.id as wedding_id, w.bride_name, w.groom_name, w.wedding_date, w.template_name, u.status, w.user_id 
                              FROM weddings w
                              JOIN users u ON w.user_id = u.id
                              WHERE w.slug = ? LIMIT 1");
    $stmtWed->execute([$slug]);
}

$wedding = (isset($stmtWed)) ? $stmtWed->fetch() : null;

if ($wedding) {
    $wedding_id = $wedding['wedding_id'];
} else {
    die("Invalid Invitation Link!");
}

// ---------------------------------------------------------------------
// Theme colors: derive the envelope/seal/paper palette from the same
// template_name the couple picked in Settings, so this page matches
// whatever theme view_invitation.php renders once the guest gets in.
// ---------------------------------------------------------------------
function tc_hex2rgb($hex) {
    $hex = ltrim($hex, '#');
    if (strlen($hex) === 3) {
        $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
    }
    return [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))];
}
function tc_rgb2hex($rgb) {
    return sprintf('#%02x%02x%02x', max(0, min(255, round($rgb[0]))), max(0, min(255, round($rgb[1]))), max(0, min(255, round($rgb[2]))));
}
function tc_mix($hex1, $hex2, $amount) {
    $a = tc_hex2rgb($hex1); $b = tc_hex2rgb($hex2);
    return tc_rgb2hex([
        $a[0] + ($b[0] - $a[0]) * $amount,
        $a[1] + ($b[1] - $a[1]) * $amount,
        $a[2] + ($b[2] - $a[2]) * $amount,
    ]);
}
function tc_darken($hex, $amount) { return tc_mix($hex, '#000000', $amount); }
function tc_lighten($hex, $amount) { return tc_mix($hex, '#ffffff', $amount); }
function tc_rgbstr($hex) { $r = tc_hex2rgb($hex); return round($r[0]) . ',' . round($r[1]) . ',' . round($r[2]); }

// One base "primary" (for the velvet envelope) and "accent" (for the wax
// seal / gold-ish highlights) hue per template — pulled from each
// template file's own :root palette so the two pages feel like one brand.
$theme_palettes = [
    'premium_gold'     => ['primary' => '#8a6520', 'accent' => '#b78a44', 'accent_light' => '#e8d5a3', 'paper' => '#fdfaf5', 'paper2' => '#f9f5ee', 'ink' => '#2d2115'],
    'minimal_light'    => ['primary' => '#5c755a', 'accent' => '#8ba888', 'accent_light' => '#d6e2d4', 'paper' => '#ffffff', 'paper2' => '#f8f9fa', 'ink' => '#333333'],
    'terracotta_bloom' => ['primary' => '#8f4526', 'accent' => '#c1633d', 'accent_light' => '#e3a880', 'paper' => '#faf5ec', 'paper2' => '#f4ece0', 'ink' => '#362b21'],
    'plum_parchment'   => ['primary' => '#4a2c3b', 'accent' => '#8a9a7e', 'accent_light' => '#b7c3ac', 'paper' => '#f8f2e9', 'paper2' => '#f0e6d6', 'ink' => '#2e2a28'],
    'floral_garden'    => ['primary' => '#a9607c', 'accent' => '#9caf88', 'accent_light' => '#c3d3b1', 'paper' => '#fffdf8', 'paper2' => '#fbf3ea', 'ink' => '#40352f'],
    'beach_tropical'   => ['primary' => '#2f7d9c', 'accent' => '#ef8264', 'accent_light' => '#f4a688', 'paper' => '#fffdf9', 'paper2' => '#fbf1e2', 'ink' => '#2b3a42'],
    'rustic_boho'      => ['primary' => '#7a4225', 'accent' => '#d99b6f', 'accent_light' => '#e6bd97', 'paper' => '#faf3e7', 'paper2' => '#f0e3ce', 'ink' => '#3b2a1e'],
    'royal_classic'    => ['primary' => '#4d1219', 'accent' => '#c6a15b', 'accent_light' => '#dcc189', 'paper' => '#faf7f0', 'paper2' => '#f1e9d8', 'ink' => '#1c2340'],
    'indian_royal'     => ['primary' => '#6e1626', 'accent' => '#e0a527', 'accent_light' => '#edc873', 'paper' => '#fff8ec', 'paper2' => '#fbecc9', 'ink' => '#3a1015'],
];

$theme_name = !empty($wedding['template_name']) ? $wedding['template_name'] : 'premium_gold';
$pal = $theme_palettes[$theme_name] ?? $theme_palettes['premium_gold'];

// Envelope velvet gradient, derived from the template's primary hue
$c_env_light = tc_lighten($pal['primary'], 0.28);
$c_env_mid   = tc_darken($pal['primary'], 0.05);
$c_env_dark  = tc_darken($pal['primary'], 0.55);

$c_accent           = $pal['accent'];
$c_accent_light     = $pal['accent_light'];
$c_accent_rgb       = tc_rgbstr($c_accent);
$c_accent_light_rgb = tc_rgbstr($c_accent_light);

$c_paper   = $pal['paper'];
$c_paper2  = $pal['paper2'];
$c_ink     = $pal['ink'];
$c_ink_rgb = tc_rgbstr($c_ink);

// Legible dark tones for text sitting on the cream letter / gold button
$c_paper_accent_strong = tc_darken($c_accent, 0.55); // couple names, owner banner
$c_paper_accent_mid    = tc_darken($c_accent, 0.35); // greeting, redirect note
$c_paper_accent_rgb    = tc_rgbstr(tc_darken($c_accent, 0.45)); // dividers, icons
$c_seal_ink            = tc_darken($c_accent, 0.75); // text inside the wax seal / button

$is_owner = (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $wedding['user_id']);
$is_admin = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin');

if ($wedding['status'] !== 'active' && !$is_owner && !$is_admin) {
    $bride = htmlspecialchars($wedding['bride_name']);
    $groom = htmlspecialchars($wedding['groom_name']);
    $initials = mb_strtoupper(mb_substr($bride, 0, 1) . '&' . mb_substr($groom, 0, 1));
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $bride . ' & ' . $groom; ?> — Coming Soon</title>
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;1,400&family=Inter:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
        body{
            font-family:'Inter',sans-serif;
            background-color:<?php echo $c_env_dark; ?>;
            background-image:
                radial-gradient(circle at 15% 20%, rgba(<?php echo $c_accent_rgb; ?>,0.10), transparent 45%),
                radial-gradient(circle at 85% 80%, rgba(<?php echo $c_accent_rgb; ?>,0.08), transparent 45%),
                url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='220' height='220' viewBox='0 0 220 220'%3E%3Cg fill='none' stroke='%23<?php echo ltrim($c_accent, '#'); ?>' stroke-width='1.1' opacity='0.16'%3E%3Cpath d='M20 200 C 10 160, 40 150, 35 110 C 30 70, 60 60, 55 20'/%3E%3Cpath d='M35 110 C 55 105, 65 90, 60 70'/%3E%3Cpath d='M35 150 C 55 148, 68 135, 62 118'/%3E%3Ccircle cx='55' cy='20' r='4'/%3E%3Cpath d='M190 20 C 200 60, 170 70, 175 110 C 180 150, 150 160, 160 200'/%3E%3Cpath d='M175 110 C 155 105, 145 90, 150 70'/%3E%3Cpath d='M175 150 C 155 148, 142 135, 148 118'/%3E%3Ccircle cx='150' cy='70' r='3'/%3E%3C/g%3E%3C/svg%3E");
            background-size:auto, auto, 220px 220px;
            min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px;overflow:hidden;
        }
        .card{
            background:linear-gradient(160deg, <?php echo tc_lighten($c_env_dark, 0.18); ?>, <?php echo $c_env_dark; ?>);
            border:1px solid rgba(<?php echo $c_accent_rgb; ?>,0.25);
            border-radius:6px;
            padding:56px 44px;max-width:440px;width:100%;text-align:center;position:relative;z-index:1;
            box-shadow:0 30px 90px rgba(0,0,0,0.55), inset 0 1px 0 rgba(<?php echo $c_accent_rgb; ?>,0.1);
            animation:up .7s ease;
        }
        .card::before{
            content:'';position:absolute;inset:10px;border:1px solid rgba(<?php echo $c_accent_rgb; ?>,0.15);border-radius:3px;pointer-events:none;
        }
        @keyframes up{from{opacity:0;transform:translateY(30px);}to{opacity:1;transform:translateY(0);}}
        .seal{
            width:74px;height:74px;border-radius:50%;margin:0 auto 30px;
            background:radial-gradient(circle at 35% 30%, <?php echo $c_accent_light; ?>, <?php echo $c_accent; ?> 60%, <?php echo tc_darken($c_accent, 0.4); ?> 100%);
            box-shadow:0 8px 18px rgba(0,0,0,0.45), inset 0 2px 3px rgba(255,255,255,0.35);
            display:flex;align-items:center;justify-content:center;
            font-family:'Cormorant Garamond',serif;font-style:italic;font-size:1.15rem;color:<?php echo $c_seal_ink; ?>;letter-spacing:1px;
        }
        .eyebrow{font-family:'Cormorant Garamond',serif;font-size:0.78rem;letter-spacing:4px;text-transform:uppercase;color:rgba(<?php echo $c_accent_light_rgb; ?>,0.75);margin-bottom:22px;}
        .couple{font-family:'Great Vibes',cursive;font-size:2.9rem;color:<?php echo $c_accent_light; ?>;line-height:1.15;}
        .amp{display:block;font-size:1.3rem;color:rgba(<?php echo $c_accent_light_rgb; ?>,0.4);margin:-2px 0;font-family:'Cormorant Garamond',serif;}
        .divider{display:flex;align-items:center;gap:12px;margin:26px 0;}
        .divider-line{flex:1;height:1px;background:linear-gradient(to right, transparent, rgba(<?php echo $c_accent_rgb; ?>,0.4), transparent);}
        .divider-icon{color:rgba(<?php echo $c_accent_light_rgb; ?>,0.6);font-size:0.7rem;}
        .title{font-family:'Cormorant Garamond',serif;font-size:1.25rem;color:rgba(245,239,225,0.9);margin-bottom:12px;font-style:italic;}
        .desc{font-size:0.85rem;color:rgba(230,225,210,0.55);line-height:1.8;font-weight:300;}
        .badge{display:inline-flex;align-items:center;gap:7px;background:rgba(<?php echo $c_accent_rgb; ?>,0.08);border:1px solid rgba(<?php echo $c_accent_rgb; ?>,0.25);border-radius:20px;padding:7px 18px;font-size:0.72rem;color:rgba(<?php echo $c_accent_light_rgb; ?>,0.8);letter-spacing:1.5px;text-transform:uppercase;margin-top:26px;}
        .pulse{width:6px;height:6px;border-radius:50%;background:<?php echo $c_accent_light; ?>;animation:pulse 2s infinite;}
        @keyframes pulse{0%,100%{opacity:1;transform:scale(1);}50%{opacity:.4;transform:scale(.8);}}
    </style>
</head>
<body>
<div class="card">
    <div class="seal"><?php echo $initials; ?></div>
    <p class="eyebrow">Save The Date</p>
    <div class="couple">
        <?php echo $bride; ?>
        <span class="amp">&amp;</span>
        <?php echo $groom; ?>
    </div>
    <div class="divider">
        <div class="divider-line"></div>
        <div class="divider-icon"><i class="fas fa-heart"></i></div>
        <div class="divider-line"></div>
    </div>
    <p class="title">Their invitation is being written</p>
    <p class="desc">
        This wedding invitation is being carefully prepared.<br>
        Please check back shortly — it will be ready soon.
    </p>
    <div class="badge">
        <div class="pulse"></div> Being prepared
    </div>
</div>
</body>
</html>
    <?php
    exit();
}

// Preview mode for owner and admin
if (isset($_GET['preview']) && ($is_owner || $is_admin)) {
    $_SESSION['guest_id'] = 0;
    $_SESSION['guest_name'] = "Preview (Admin/Owner)";
    $_SESSION['invite_wedding_id'] = $wedding_id;
    header("Location: view_invitation.php");
    exit();
}

// Handle form submit OR personalized auto-open link (?wa=0771234567)
$just_verified = false;
$verified_via_form = false;
$whatsapp_from_link = isset($_GET['wa']) ? trim($_GET['wa']) : '';

if ($_SERVER["REQUEST_METHOD"] == "POST" || $whatsapp_from_link !== '') {
    $whatsapp = ($_SERVER["REQUEST_METHOD"] == "POST") ? trim($_POST['whatsapp_number']) : $whatsapp_from_link;

    $stmt = $pdo->prepare("SELECT id, name, is_opened FROM guests WHERE wedding_id = ? AND whatsapp_number = ?");
    $stmt->execute([$wedding_id, $whatsapp]);
    $guest = $stmt->fetch();

    if ($guest) {
        if ($guest['is_opened'] == 0) {
            $updateStmt = $pdo->prepare("UPDATE guests SET is_opened = 1, opened_at = NOW() WHERE id = ?");
            $updateStmt->execute([$guest['id']]);
        }

        $_SESSION['guest_id'] = $guest['id'];
        $_SESSION['guest_name'] = $guest['name'];
        $_SESSION['invite_wedding_id'] = $wedding_id;

        // Don't redirect right away — flag success so the page below
        // can play the envelope-opening animation before moving on.
        $just_verified = true;
        // Typed-number submissions (default link) auto-continue straight
        // into the opening animation; personalized ?wa= links still wait
        // for a manual tap on the seal.
        $verified_via_form = ($_SERVER["REQUEST_METHOD"] == "POST");
    } elseif ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Only show the "not on guest list" error for manual typing —
        // a stale/invalid personalized link falls back to the normal form silently.
        $error = "Sorry, this number is not on the guest list.";
    }
}


$wedding_date_formatted = date("d F Y", strtotime($wedding['wedding_date']));
$bride_name = htmlspecialchars($wedding['bride_name']);
$groom_name = htmlspecialchars($wedding['groom_name']);
$monogram = mb_strtoupper(mb_substr($wedding['bride_name'], 0, 1)) . ' · ' . mb_strtoupper(mb_substr($wedding['groom_name'], 0, 1));
$guest_greeting = (isset($_SESSION['guest_name']) && !empty($_SESSION['guest_name']) && !$just_verified) ? null : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $bride_name . ' &amp; ' . $groom_name; ?> — Wedding Invitation</title>
    <meta name="description" content="You're invited! Open your personal wedding invitation.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;1,400&family=Great+Vibes&family=Inter:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root{
            --emerald-950:<?php echo $c_env_dark; ?>;
            --emerald-900:<?php echo tc_darken($c_env_dark, 0.15); ?>;
            --emerald-800:<?php echo $c_env_mid; ?>;
            --emerald-700:<?php echo $c_env_light; ?>;
            --gold:<?php echo $c_accent; ?>;
            --gold-light:<?php echo $c_accent_light; ?>;
            --gold-soft:rgba(<?php echo $c_accent_rgb; ?>,0.35);
            --gold-rgb:<?php echo $c_accent_rgb; ?>;
            --gold-light-rgb:<?php echo $c_accent_light_rgb; ?>;
            --cream:<?php echo tc_darken($c_paper, 0.03); ?>;
            --paper-a:<?php echo $c_paper; ?>;
            --paper-b:<?php echo $c_paper2; ?>;
            --ink:<?php echo $c_ink; ?>;
            --ink-rgb:<?php echo $c_ink_rgb; ?>;
            --paper-accent-strong:<?php echo $c_paper_accent_strong; ?>;
            --paper-accent-mid:<?php echo $c_paper_accent_mid; ?>;
            --paper-accent-rgb:<?php echo $c_paper_accent_rgb; ?>;
            --seal-ink:<?php echo $c_seal_ink; ?>;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--emerald-900);
            background-image:
                radial-gradient(circle at 12% 15%, rgba(var(--gold-rgb),0.10), transparent 40%),
                radial-gradient(circle at 88% 85%, rgba(var(--gold-rgb),0.08), transparent 40%),
                url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='240' height='240' viewBox='0 0 240 240'%3E%3Cg fill='none' stroke='%23<?php echo ltrim($c_accent, '#'); ?>' stroke-width='1.1' opacity='0.14'%3E%3Cpath d='M20 220 C 8 175, 44 165, 38 120 C 32 75, 66 65, 60 20'/%3E%3Cpath d='M38 120 C 60 114, 72 96, 66 74'/%3E%3Cpath d='M38 165 C 60 162, 74 148, 68 129'/%3E%3Ccircle cx='60' cy='20' r='4.5'/%3E%3Ccircle cx='66' cy='74' r='3'/%3E%3Cpath d='M210 20 C 222 65, 188 75, 194 120 C 200 165, 166 175, 172 220'/%3E%3Cpath d='M194 120 C 172 114, 160 96, 166 74'/%3E%3Cpath d='M194 165 C 172 162, 158 148, 164 129'/%3E%3Ccircle cx='172' cy='220' r='4.5'/%3E%3Ccircle cx='166' cy='74' r='3'/%3E%3C/g%3E%3C/svg%3E");
            background-size: auto, auto, 240px 240px;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }

        .bg-glow {
            position: fixed;
            border-radius: 50%;
            filter: blur(90px);
            pointer-events: none;
        }
        .glow-1 { width: 480px; height: 480px; background: radial-gradient(circle, rgba(var(--gold-rgb),0.10), transparent); top: -120px; right: -120px; }
        .glow-2 { width: 420px; height: 420px; background: radial-gradient(circle, rgba(var(--gold-rgb),0.06), transparent); bottom: -100px; left: -100px; }

        .particles { position: fixed; inset: 0; pointer-events: none; overflow: hidden; }
        .particle { position: absolute; width: 2px; height: 2px; border-radius: 50%; background: rgba(var(--gold-light-rgb),0.5); animation: drift linear infinite; }
        @keyframes drift {
            from { transform: translateY(100vh) rotate(0deg); opacity: 0; }
            10% { opacity: 1; }
            90% { opacity: 1; }
            to { transform: translateY(-100px) rotate(360deg); opacity: 0; }
        }

        /* ============ STAGE / EYEBROW ============ */
        .stage {
            position: relative;
            width: min(440px, 90vw);
            z-index: 1;
            text-align: center;
        }
        .stage-label{
            font-family:'Cormorant Garamond',serif;
            font-size:0.82rem;
            letter-spacing:5px;
            text-transform:uppercase;
            color:rgba(var(--gold-light-rgb),0.75);
            margin-bottom:22px;
            transition:opacity .4s ease;
        }

        /* ============ ENVELOPE ============ */
        .envelope-wrap {
            position: relative;
            perspective: 1600px;
            /* One fluid variable drives the flap height, seal position and
               size, and the closed envelope's body height — everything
               scales together with viewport width instead of snapping at
               breakpoints. */
            --flap-h: clamp(108px, 36vw, 168px);
        }

        .envelope {
            position: relative;
            background: linear-gradient(155deg, var(--emerald-700), var(--emerald-800) 55%, var(--emerald-950));
            border: 1px solid var(--gold-soft);
            border-radius: 10px;
            box-shadow: 0 30px 90px rgba(0,0,0,0.55), inset 0 1px 0 rgba(var(--gold-rgb),0.12);
            padding-top: var(--flap-h);
            /* Closed envelope keeps a proper body below the flap, sized
               relative to the flap so the whole shape matches a real
               envelope's proportions rather than collapsing to just the
               triangle. */
            min-height: calc(var(--flap-h) * 1.6);
            animation: envIn 0.7s ease;
            overflow: hidden;
            transition: min-height 0.6s ease, padding-top 0.6s ease;
        }
        @keyframes envIn { from { opacity: 0; transform: translateY(40px) scale(0.97); } to { opacity: 1; transform: translateY(0) scale(1); } }

        .envelope-face-text{
            position:absolute;
            top: calc(var(--flap-h) * 0.22);
            left:0; right:0;
            text-align:center;
            font-family:'Cormorant Garamond',serif;
            font-style:italic;
            font-size: clamp(0.95rem, 3.4vw, 1.15rem);
            letter-spacing:1px;
            color:rgba(247,241,227,0.55);
            z-index:1;
            transition:opacity .35s ease;
        }

        .envelope-flap {
            position: absolute;
            top: 0; left: 0; right: 0;
            height: var(--flap-h);
            overflow: visible;
            transform-origin: top center;
            transform-style: preserve-3d;
            transition: transform 0.9s cubic-bezier(.6,-0.1,.35,1.4);
            z-index: 3;
        }
        .envelope-flap svg { width: 100%; height: 100%; display: block; }

        .wax-seal {
            position: absolute;
            top: calc(var(--flap-h) * 0.7);
            left: 50%;
            transform: translate(-50%, -50%);
            width: clamp(50px, 15vw, 62px);
            height: clamp(50px, 15vw, 62px);
            border-radius: 50%;
            background: radial-gradient(circle at 35% 30%, var(--gold-light), var(--gold) 60%, var(--seal-ink) 100%);
            box-shadow: 0 8px 18px rgba(0,0,0,0.5), inset 0 2px 3px rgba(255,255,255,0.35);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--seal-ink);
            font-family: 'Cormorant Garamond', serif;
            font-style: italic;
            font-size: clamp(0.82rem, 2.6vw, 1rem);
            letter-spacing: 0.5px;
            cursor: pointer;
            z-index: 4;
            transition: transform 0.25s ease, box-shadow 0.25s ease;
        }
        .wax-seal:hover { transform: translate(-50%, -50%) scale(1.07); box-shadow: 0 10px 22px rgba(0,0,0,0.55), inset 0 2px 3px rgba(255,255,255,0.4); }
        .wax-seal.cracked { animation: crack 0.4s ease forwards; }
        @keyframes crack {
            0% { transform: translate(-50%,-50%) scale(1); }
            40% { transform: translate(-50%,-50%) scale(1.2); filter: brightness(1.25); }
            100% { transform: translate(-50%,-50%) scale(0); opacity: 0; }
        }

        .tap-hint{
            margin-top:22px;
            font-family:'Cormorant Garamond',serif;
            font-size:0.78rem;
            letter-spacing:4px;
            text-transform:uppercase;
            color:rgba(var(--gold-light-rgb),0.55);
            transition:opacity .4s ease;
        }

        /* Letter / card content, tucked inside the envelope.
           Collapsed to zero height until the seal is tapped, so the
           envelope itself stays flap-sized instead of reserving space
           for the whole letter underneath. */
        .card {
            position: relative;
            background: linear-gradient(180deg, var(--paper-a), var(--paper-b));
            border-radius: 0 0 9px 9px;
            padding: 0 clamp(24px, 8vw, 40px);
            text-align: center;
            z-index: 1;
            opacity: 0;
            max-height: 0;
            overflow: hidden;
            transform: translateY(-16px);
            transition: opacity 0.5s ease 0.3s, transform 0.5s ease 0.3s, max-height 0.7s ease, padding 0.7s ease;
        }

        .card-top-line { position: absolute; top: 0; left: 10%; right: 10%; height: 1px; background: linear-gradient(to right, transparent, var(--gold-soft), transparent); }

        .greeting { font-family:'Cormorant Garamond',serif; font-size: 0.85rem; color: var(--paper-accent-mid); letter-spacing: 3.5px; text-transform: uppercase; margin-bottom: 18px; }

        .couple-names { font-family: 'Great Vibes', cursive; font-size: clamp(2.3rem, 9vw, 3.1rem); color: var(--paper-accent-strong); line-height: 1.2; margin-bottom: 4px; }
        .ampersand { display: block; font-size: 1.4rem; color: rgba(var(--paper-accent-rgb),0.55); margin: -6px 0; font-family:'Cormorant Garamond',serif; }

        .wedding-date { font-family: 'Cormorant Garamond', serif; font-size: 0.92rem; color: rgba(var(--ink-rgb),0.55); letter-spacing: 2px; margin-bottom: 30px; text-transform: uppercase; }

        .divider { display: flex; align-items: center; gap: 12px; margin-bottom: 26px; }
        .divider-line { flex: 1; height: 1px; background: rgba(var(--paper-accent-rgb),0.3); }
        .divider-icon { color: rgba(var(--paper-accent-rgb),0.55); font-size: 0.75rem; }

        .instruction { font-size: 0.86rem; color: rgba(var(--ink-rgb),0.65); margin-bottom: 18px; line-height: 1.6; }

        .input-group { position: relative; margin-bottom: 14px; }
        .input-icon { position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: rgba(var(--paper-accent-rgb),0.6); font-size: 0.95rem; z-index: 1; }
        input[type="text"] {
            width: 100%;
            background: rgba(255,255,255,0.6);
            border: 1px solid rgba(var(--paper-accent-rgb),0.3);
            border-radius: 12px;
            padding: 15px 18px 15px 46px;
            color: var(--ink);
            font-size: 1.05rem;
            font-family: 'Inter', sans-serif;
            font-weight: 400;
            letter-spacing: 1.2px;
            text-align: center;
            transition: all 0.3s;
            outline: none;
        }
        input[type="text"]::placeholder { color: rgba(var(--ink-rgb),0.3); letter-spacing: 0; font-size: 0.88rem; }
        input[type="text"]:focus { border-color: var(--gold); background: #fff; box-shadow: 0 0 0 3px rgba(var(--gold-rgb),0.15); }

        .error-msg {
            background: rgba(139,26,26,0.08);
            border: 1px solid rgba(139,26,26,0.25);
            border-radius: 10px;
            padding: 11px 15px;
            color: #7a1c1c;
            font-size: 0.83rem;
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-open {
            width: 100%;
            background: linear-gradient(135deg, var(--gold-light), var(--gold) 60%, #a3821e);
            color: var(--seal-ink);
            border: none;
            border-radius: 12px;
            padding: 15px;
            font-size: 0.9rem;
            font-weight: 700;
            font-family: 'Inter', sans-serif;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            cursor: pointer;
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
        }
        .btn-open::before {
            content: '';
            position: absolute;
            top: 0; left: -100%;
            width: 100%; height: 100%;
            background: linear-gradient(to right, transparent, rgba(255,255,255,0.35), transparent);
            transition: left 0.4s;
        }
        .btn-open:hover::before { left: 100%; }
        .btn-open:hover { transform: translateY(-2px); box-shadow: 0 10px 26px rgba(var(--gold-rgb),0.4); }
        .btn-open:active { transform: translateY(0); }
        .btn-open:disabled { opacity: 0.6; cursor: default; transform: none; box-shadow: none; }

        .hint { margin-top: 18px; font-size: 0.76rem; color: rgba(var(--ink-rgb),0.45); line-height: 1.6; }

        .owner-banner {
            background: rgba(var(--gold-rgb),0.1);
            border: 1px solid rgba(var(--gold-rgb),0.3);
            border-radius: 12px;
            padding: 11px 15px;
            margin-bottom: 20px;
            font-size: 0.8rem;
            color: var(--paper-accent-strong);
        }
        .owner-banner a { color: var(--paper-accent-strong); font-weight: 600; text-decoration: underline; text-underline-offset: 3px; }

        .redirect-note { margin-top: 16px; font-size: 0.78rem; color: var(--paper-accent-mid); letter-spacing: 0.5px; }

        /* Opening state, toggled on <body> once the seal is tapped */
        body.opening .envelope-flap { transform: rotateX(180deg); z-index: 0; }
        body.opening .wax-seal { animation: crack 0.4s ease forwards; }
        body.opening .envelope { min-height: 0; padding-top: 0; }
        body.opening .card {
            opacity: 1;
            transform: translateY(0);
            max-height: 2000px;
            padding: clamp(26px, 7vw, 34px) clamp(24px, 8vw, 40px) clamp(36px, 9vw, 46px);
        }
        body.opening .envelope-face-text,
        body.opening .stage-label,
        body.opening .tap-hint { opacity: 0; }
    </style>
</head>
<body>

<div class="bg-glow glow-1"></div>
<div class="bg-glow glow-2"></div>
<div class="particles" id="particles"></div>

<div class="stage">
    <p class="stage-label" id="stageLabel">You Are Invited</p>

    <div class="envelope-wrap">
        <div class="envelope" id="envelope">

            <p class="envelope-face-text">Dear Guest</p>

            <div class="envelope-flap" id="envelopeFlap">
                <svg viewBox="0 0 440 160" preserveAspectRatio="none">
                    <polygon points="0,0 440,0 220,150" style="fill:var(--emerald-800); stroke:rgba(var(--gold-rgb),0.3); stroke-width:1.5;"/>
                </svg>
            </div>

            <div class="wax-seal" id="waxSeal" title="Tap to unseal"><?php echo htmlspecialchars($monogram); ?></div>

            <div class="card">
                <div class="card-top-line"></div>

                <?php if ($is_owner): ?>
                <div class="owner-banner">
                    <i class="fas fa-user-shield" style="margin-right:6px;"></i>You're viewing as the couple.
                    <br><a href="invite.php?w_id=<?php echo $wedding_id; ?>&amp;preview=1">
                        <i class="fas fa-eye"></i> Click to Preview Invitation →
                    </a>
                </div>
                <?php endif; ?>

                <p class="greeting">The Wedding Of</p>

                <div class="couple-names">
                    <?php echo $bride_name; ?>
                    <span class="ampersand">&amp;</span>
                    <?php echo $groom_name; ?>
                </div>

                <p class="wedding-date"><?php echo $wedding_date_formatted; ?></p>

                <div class="divider">
                    <div class="divider-line"></div>
                    <div class="divider-icon"><i class="fas fa-heart"></i></div>
                    <div class="divider-line"></div>
                </div>

                <?php if (!$just_verified): ?>
                <p class="instruction">
                    Enter your WhatsApp number to open<br>your personal invitation
                </p>

                <?php if ($error): ?>
                <div class="error-msg">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
                <?php endif; ?>

                <form method="POST" action="" id="inviteForm">
                    <div class="input-group">
                        <i class="input-icon fab fa-whatsapp"></i>
                        <input
                            type="text"
                            name="whatsapp_number"
                            id="whatsapp_input"
                            placeholder="e.g. 0771234567"
                            required
                            autocomplete="tel"
                            inputmode="numeric"
                        >
                    </div>
                    <button type="submit" class="btn-open" id="openBtn">
                        <i class="fas fa-envelope-open-text" style="margin-right:8px;"></i> Open My Invitation
                    </button>
                </form>

                <p class="hint">
                    <i class="fas fa-lock" style="margin-right:4px;"></i>
                    Your number is only used to show you your invitation. It's not shared with anyone.
                </p>
                <?php else: ?>
                <!-- Guest already verified (personal link or form submit) —
                     number entry is no longer needed, so it's hidden and we
                     go straight into the seal-opening sequence below. -->
                <p class="redirect-note"><i class="fas fa-spinner fa-spin"></i> Opening your invitation…</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <p class="tap-hint" id="tapHint">Tap the seal to open</p>
</div>

<script>
// Generate floating particles
const container = document.getElementById('particles');
for (let i = 0; i < 18; i++) {
    const p = document.createElement('div');
    p.className = 'particle';
    p.style.left = Math.random() * 100 + 'vw';
    p.style.width = p.style.height = (Math.random() * 3 + 1) + 'px';
    p.style.animationDuration = (Math.random() * 15 + 10) + 's';
    p.style.animationDelay = (Math.random() * 10) + 's';
    container.appendChild(p);
}

const waxSeal = document.getElementById('waxSeal');
const whatsappInput = document.getElementById('whatsapp_input');
const openBtn = document.getElementById('openBtn');

// Cracks the seal open, tilts the flap back and reveals the letter inside.
let sealCracked = false;
function openInvitationFlow() {
    if (sealCracked) return;
    sealCracked = true;
    waxSeal.classList.add('cracked');
    document.body.classList.add('opening');

    <?php if ($just_verified): ?>
    // Guest already verified (personalized link or the number form) —
    // tapping the seal now moves straight to the invitation after a
    // short 3s envelope-opening animation.
    if (openBtn) openBtn.disabled = true;
    setTimeout(() => {
        window.location.href = 'view_invitation.php';
    }, 3000);
    <?php else: ?>
    setTimeout(() => whatsappInput.focus(), 350);
    <?php endif; ?>
}

// Seal needs a manual tap for the personalized ?wa= link. But when the
// guest just typed their number and submitted the form on the default
// link, continue straight into the opening animation — no second tap.
waxSeal.addEventListener('click', openInvitationFlow);

<?php if ($just_verified && $verified_via_form): ?>
openInvitationFlow();
<?php endif; ?>
</script>
</body>
</html>