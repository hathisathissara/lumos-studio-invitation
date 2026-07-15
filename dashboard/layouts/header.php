<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$role   = $_SESSION['role']   ?? 'couple';
$status = $_SESSION['status'] ?? 'pending';
$user_name = $_SESSION['user_name'] ?? 'User';

if ($role !== 'admin' && !empty($_SESSION['user_id']) && isset($pdo)) {
    $stmtStatus = $pdo->prepare('SELECT status FROM users WHERE id = ?');
    $stmtStatus->execute([$_SESSION['user_id']]);
    $dbStatus = $stmtStatus->fetchColumn();

    if ($dbStatus !== false) {
        $status = $dbStatus;
        $_SESSION['status'] = $dbStatus;
    }
}

// AJAX: සජීවීව (Live) Header/Sidebar Status Check කිරීම — admin deactivate/activate කලොත් couple pages ලට reflect වෙන්න
if (isset($_GET['header_status_check'])) {
    header('Content-Type: application/json');
    echo json_encode(['status' => $role !== 'admin' ? $status : null]);
    exit();
}

$invite_url_for_header = '';
$invite_share_message_header = '';
if ($role !== 'admin' && !empty($_SESSION['wedding_id']) && isset($pdo)) {
    $stmtSlugForHeader = $pdo->prepare('SELECT slug FROM weddings WHERE id = ?');
    $stmtSlugForHeader->execute([$_SESSION['wedding_id']]);
    $slugForHeader = $stmtSlugForHeader->fetchColumn();

    $invite_slug = !empty($slugForHeader) ? $slugForHeader : 'invite.php?w_id=' . $_SESSION['wedding_id'];
    $host = !empty($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
    $base_dir = dirname(dirname(dirname($_SERVER['PHP_SELF'])));
    // නිවැරදි කල නව කේතය (ඉබේම http/https හඳුනාගනී)
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $domain = $protocol . $_SERVER['HTTP_HOST'] . dirname(dirname(dirname($_SERVER['PHP_SELF'])));
    $ring = "\u{1F48D}";
    $flower = "\u{1F338}"; // 🌸
    $heart = "\u{2764}\u{FE0F}"; // ❤️
    $invite_url_for_header = rtrim($domain, '/') . '/' . $invite_slug;
    $invite_share_message_header =  $ring . " You're Invited! " . $ring . "\n\n"
    ."With so much love and happiness in our hearts, we're excited to invite you to celebrate the invitation of our journey together - " . $user_name . "\n\n"
    . "It would truly mean the world to us on this special day\n\n"
    . "Invitation: " . $invite_url_for_header . "\n\n"
    . "We can't wait to celebrate, laugh, and create beautiful memories with you! " . $heart;
}

// Get initials for avatar
$parts = explode(' ', $user_name);
$initials = '';
foreach (array_slice($parts, 0, 2) as $p) {
    $initials .= strtoupper(mb_substr($p, 0, 1));
}

// Determine current page for active state
$current = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lumus Studio Dashboard</title>
    <link rel="icon" type="image/x-icon" href="../../uploads/lumos.jpg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;1,400&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; }

        :root {
            --gold: #c9a96e;
            --gold-dark: #a07840;
            --sidebar-bg: #0f0f1a;
            --sidebar-bg2: #1a1a2e;
            --sidebar-border: rgba(201,169,110,0.1);
            --sidebar-text: #9e9aaa;
            --sidebar-text-active: #e8e4dc;
            --sidebar-active: rgba(201,169,110,0.1);
            --sidebar-width: 240px;
            --content-bg: #f4f6f9;
            --card-bg: #ffffff;
            --card-border: #e8ecf0;
            --text-dark: #1a1a2e;
            --text-mid: #4a5568;
            --text-light: #9ea3b0;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--content-bg);
            color: var(--text-dark);
            margin: 0;
        }

        /* ============ SIDEBAR ============ */
        .sidebar {
            position: fixed;
            top: 0; left: 0; bottom: 0;
            width: var(--sidebar-width);
            background: linear-gradient(180deg, var(--sidebar-bg) 0%, var(--sidebar-bg2) 100%);
            border-right: 1px solid var(--sidebar-border);
            display: flex;
            flex-direction: column;
            z-index: 200;
            overflow-y: auto;
            overflow-x: hidden;
            transition: transform 0.3s ease;
            box-shadow: 14px 0 30px rgba(15, 15, 26, 0.16);
        }

        .sidebar-brand {
            padding: 28px 24px 20px;
            border-bottom: 1px solid var(--sidebar-border);
        }
        .sidebar-logo {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.6rem;
            font-weight: 600;
            color: var(--gold);
            letter-spacing: 0.5px;
            text-decoration: none;
            display: block;
        }
        .sidebar-tagline {
            font-size: 0.68rem;
            color: rgba(201,169,110,0.45);
            letter-spacing: 1.5px;
            text-transform: uppercase;
            margin-top: 2px;
        }

        /* User profile strip */
        .sidebar-user {
            padding: 18px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 1px solid var(--sidebar-border);
        }
        .user-avatar {
            width: 36px; height: 36px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--gold), var(--gold-dark));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.78rem;
            font-weight: 700;
            color: #0f0f1a;
            flex-shrink: 0;
        }
        .user-info { overflow: hidden; }
        .user-name {
            font-size: 0.82rem;
            font-weight: 600;
            color: var(--sidebar-text-active);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .user-role {
            font-size: 0.68rem;
            color: rgba(201,169,110,0.5);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Status banner for pending */
        .sidebar-status-banner {
            margin: 12px 16px;
            background: rgba(255,193,7,0.08);
            border: 1px solid rgba(255,193,7,0.2);
            border-radius: 10px;
            padding: 10px 12px;
            font-size: 0.74rem;
            color: #f6c90e;
            text-align: center;
            line-height: 1.5;
        }
        .sidebar-status-banner a {
            color: #f6c90e;
            font-weight: 700;
            text-decoration: underline;
            text-underline-offset: 2px;
        }

        /* Nav sections */
        .sidebar-nav { padding: 14px 8px 12px; flex: 1; }
        .nav-section-label {
            padding: 14px 20px 6px;
            font-size: 0.62rem;
            font-weight: 600;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: rgba(201,169,110,0.3);
        }
        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 16px;
            color: var(--sidebar-text);
            text-decoration: none;
            font-size: 0.86rem;
            font-weight: 400;
            transition: all 0.2s;
            border-left: 3px solid transparent;
            position: relative;
            border-radius: 12px;
            margin: 2px 0;
        }
        .nav-item i {
            width: 18px;
            text-align: center;
            font-size: 0.9rem;
            opacity: 0.7;
            transition: opacity 0.2s;
        }
        .nav-item:hover {
            background: rgba(255,255,255,0.03);
            color: var(--sidebar-text-active);
        }
        .nav-item:hover i { opacity: 1; }
        .nav-item.active {
            background: linear-gradient(90deg, rgba(201,169,110,0.18), rgba(201,169,110,0.08));
            color: var(--gold);
            border-left-color: var(--gold);
            font-weight: 500;
        }
        .nav-item.active i { opacity: 1; color: var(--gold); }

        .nav-item-danger {
            color: rgba(239,68,68,0.6) !important;
            margin-top: 4px;
        }
        .nav-item-danger:hover {
            color: #ef4444 !important;
            background: rgba(239,68,68,0.05) !important;
        }
        .nav-item-warn {
            color: #f6c90e !important;
        }

        .sidebar-bottom {
            padding: 16px 0;
            border-top: 1px solid var(--sidebar-border);
        }

        /* ============ TOPBAR ============ */
        .topbar {
            position: fixed;
            top: 0;
            left: var(--sidebar-width);
            right: 0;
            height: 64px;
            background: rgba(255,255,255,0.9);
            backdrop-filter: blur(16px);
            border-bottom: 1px solid var(--card-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 32px;
            z-index: 100;
            box-shadow: 0 8px 24px rgba(15, 15, 26, 0.04);
        }
        .topbar-left {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .topbar-page-title {
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--text-dark);
        }
        .topbar-right {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .topbar-btn {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 8px 16px;
            border-radius: 10px;
            font-family: 'Inter', sans-serif;
            font-size: 0.8rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s;
            cursor: pointer;
            border: none;
        }
        .topbar-btn-outline {
            background: transparent;
            border: 1px solid var(--card-border);
            color: var(--text-mid);
        }
        .topbar-btn-outline:hover {
            border-color: var(--gold);
            color: var(--gold);
            background: rgba(201,169,110,0.04);
        }
        .topbar-btn-gold {
            background: linear-gradient(135deg, var(--gold), var(--gold-dark));
            color: #0f0f1a;
            font-weight: 600;
        }
        .topbar-btn-gold:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(201,169,110,0.3);
        }
        .topbar-btn-amber {
            background: rgba(245,158,11,0.1);
            border: 1px solid rgba(245,158,11,0.3);
            color: #d97706;
            font-weight: 600;
            text-decoration: none;
        }
        .topbar-btn-amber:hover {
            background: rgba(245,158,11,0.18);
            color: #d97706;
        }
        .topbar-lock-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            border-radius: 20px;
            background: rgba(239,68,68,0.07);
            border: 1px solid rgba(239,68,68,0.2);
            font-size: 0.74rem;
            font-weight: 600;
            color: #dc2626;
            letter-spacing: 0.3px;
        }
        .topbar-lock-badge i { font-size: 0.7rem; }

        /* ============ MAIN CONTENT ============ */
        .main-content {
            margin-left: var(--sidebar-width);
            margin-top: 64px;
            padding: 28px 32px 40px;
            min-height: calc(100vh - 64px);
        }

        /* ============ CARDS ============ */
        .card-custom {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 18px;
            box-shadow: 0 10px 30px rgba(15, 15, 26, 0.04);
        }

        /* ============ MOBILE TOGGLE ============ */
        .mobile-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 1.3rem;
            color: var(--text-dark);
            cursor: pointer;
            margin-right: 12px;
        }
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .topbar { left: 0; padding: 0 16px; }
            .topbar-page-title { font-size: 0.85rem; display: none; /* Hide on very small screens if needed, but let's just make it smaller or hide it so buttons fit */ }
            /* Better to hide page title on mobile to give space for buttons */
            .topbar-page-title { display: none; }
            .topbar-btn { padding: 6px 10px; font-size: 0.75rem; }
            .topbar-right { gap: 8px; }
            .main-content { margin-left: 0; padding: 20px 12px; }
            .mobile-toggle { display: block; }
        }

        /* Toast notification */
        .toast-notif {
            position: fixed;
            bottom: 24px; right: 24px;
            background: #1a1a2e;
            color: #c9a96e;
            padding: 12px 20px;
            border-radius: 12px;
            font-family: 'Inter', sans-serif;
            font-size: 0.85rem;
            font-weight: 500;
            box-shadow: 0 8px 30px rgba(0,0,0,0.3);
            border: 1px solid rgba(201,169,110,0.2);
            z-index: 9999;
            display: flex;
            align-items: center;
            gap: 10px;
            transform: translateY(100px);
            opacity: 0;
            transition: all 0.35s ease;
        }
        .toast-notif.show {
            transform: translateY(0);
            opacity: 1;
        }
    </style>
</head>
<body>

<!-- SIDEBAR -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <a href="index.php" class="sidebar-logo">Lumus Studio</a>
        <div class="sidebar-tagline">Wedding Invitations</div>
    </div>

    <div class="sidebar-user">
        <div class="user-avatar"><?php echo $initials; ?></div>
        <div class="user-info">
            <div class="user-name"><?php echo htmlspecialchars($user_name); ?></div>
            <div class="user-role"><?php echo $role === 'admin' ? 'Administrator' : 'Couple'; ?></div>
        </div>
    </div>

    <?php if ($role !== 'admin' && $status === 'pending'): ?>
    <div class="sidebar-status-banner">
        <i class="fas fa-exclamation-circle"></i> Account pending activation.<br>
        <a href="payment.php">Activate now →</a>
    </div>
    <?php endif; ?>

    <nav class="sidebar-nav">
        <?php if ($role === 'admin'): ?>
            <div class="nav-section-label">Administration</div>
            <a href="index.php" class="nav-item <?php echo $current === 'index.php' ? 'active' : ''; ?>">
                <i class="fas fa-shield-alt"></i> Admin Panel
            </a>
            <a href="admin_refunds.php" class="nav-item <?php echo $current === 'admin_refunds.php' ? 'active' : ''; ?>">
                <i class="fas fa-rotate-left"></i> Refund Requests
            </a>
            <a href="admin_upgrades.php" class="nav-item <?php echo $current === 'admin_upgrades.php' ? 'active' : ''; ?>">
                <i class="fas fa-arrow-up-right-dots"></i> Upgrade Requests
            </a>
        <?php else: ?>
            <div class="nav-section-label">Overview</div>
            <a href="index.php" class="nav-item <?php echo $current === 'index.php' ? 'active' : ''; ?>">
                <i class="fas fa-home"></i> Dashboard
            </a>
            <div class="nav-section-label">Invitation</div>
            <a href="guests.php" class="nav-item <?php echo $current === 'guests.php' ? 'active' : ''; ?>">
                <i class="fas fa-users"></i> Guest List
            </a>
            <a href="events.php" class="nav-item <?php echo $current === 'events.php' ? 'active' : ''; ?>">
                <i class="fas fa-calendar-alt"></i> Events
            </a>
            <a href="gallery.php" class="nav-item <?php echo $current === 'gallery.php' ? 'active' : ''; ?>">
                <i class="fas fa-images"></i> Gallery & Story
            </a>
            <a href="guest_gallery.php" class="nav-item <?php echo $current === 'guest_gallery.php' ? 'active' : ''; ?>">
                <i class="fas fa-camera-retro"></i> Guest Shared Pics
            </a>
            <div class="nav-section-label">Tools</div>
            <a href="checklist.php" class="nav-item <?php echo $current === 'checklist.php' ? 'active' : ''; ?>">
                <i class="fas fa-tasks"></i> Checklist
            </a>
            <a href="settings.php" class="nav-item <?php echo $current === 'settings.php' ? 'active' : ''; ?>">
                <i class="fas fa-cog"></i> Settings
            </a>


            <a href="payment.php" class="nav-item nav-item-warn <?php echo $current === 'payment.php' ? 'active' : ''; ?>">
                <i class="fas fa-credit-card"></i> Activate Account
            </a>
        <?php endif; ?>
    </nav>

    <div class="sidebar-bottom">
        <a href="../logout.php" class="nav-item nav-item-danger">
            <i class="fas fa-sign-out-alt"></i> Sign Out
        </a>
    </div>
</aside>

<!-- TOPBAR -->
<header class="topbar">
    <div class="topbar-left">
        <button class="mobile-toggle" onclick="document.getElementById('sidebar').classList.toggle('open')">
            <i class="fas fa-bars"></i>
        </button>
        <span class="topbar-page-title" id="page-title">Dashboard</span>
    </div>
    <?php if ($role !== 'admin'): ?>
    <div class="topbar-right">
        <?php if (isset($_SESSION['wedding_id'])): ?>

        <?php if ($status === 'pending'): ?>
        <!-- Guest link is locked — pending activation -->
        <div class="topbar-lock-badge">
            <i class="fas fa-lock"></i> Guest Link Locked
        </div>
        <a href="../../view_invitation.php?w_id=<?php echo $_SESSION['wedding_id']; ?>&preview=1" target="_blank" class="topbar-btn topbar-btn-outline">
            <i class="fas fa-eye"></i> Preview Only
        </a>
        <a href="payment.php" class="topbar-btn topbar-btn-amber">
            <i class="fas fa-unlock-alt"></i> Activate Now
        </a>

        <?php else: ?>
        <!-- Active — full access -->
         <a href="<?php echo htmlspecialchars($invite_url_for_header); ?>" target="_blank" class="topbar-btn topbar-btn-outline">
            <i class="fas fa-eye"></i> Preview
        </a>
        <button class="topbar-btn topbar-btn-gold" onclick="copyInviteLink()">
            <i class="fas fa-link"></i> Copy Link
        </button>
        <?php endif; ?>

        <?php endif; ?>
    </div>
    <?php endif; ?>
</header>

<!-- MAIN CONTENT WRAPPER -->
<main class="main-content">

<!-- Toast notification -->
<div class="toast-notif" id="toast">
    <i class="fas fa-check-circle"></i>
    <span id="toast-msg">Copied!</span>
</div>

<script>
// Set page title dynamically
(function() {
    const titles = {
        'index.php': 'Dashboard',
        'guests.php': 'Guest List',
        'events.php': 'Wedding Events',
        'gallery.php': 'Gallery & Love Story',
        'checklist.php': 'Wedding Checklist',
        'settings.php': 'Account Settings',
        'payment.php': 'Activate Account',
        'admin_dashboard.php': 'Admin Panel'
    };
    const page = window.location.pathname.split('/').pop();
    document.getElementById('page-title').textContent = titles[page] || 'Dashboard';
})();

// Copy invite link
function copyInviteLink() {
    const shareText = <?php echo json_encode($invite_share_message_header ?: $invite_url_for_header); ?>;

    copyTextToClipboard(shareText).then(() => {
        showToast('✓ Copied invitation message');
    }).catch(() => {
        prompt('Copy this invitation message:', shareText);
    });
}

function copyTextToClipboard(text) {
    if (navigator.clipboard && navigator.clipboard.writeText) {
        return navigator.clipboard.writeText(text);
    }

    return new Promise((resolve, reject) => {
        const tempInput = document.createElement('textarea');
        tempInput.value = text;
        tempInput.setAttribute('readonly', '');
        tempInput.style.position = 'fixed';
        tempInput.style.left = '-9999px';
        document.body.appendChild(tempInput);
        tempInput.select();
        try {
            const copied = document.execCommand('copy');
            document.body.removeChild(tempInput);
            if (copied) {
                resolve();
            } else {
                reject(new Error('Copy failed'));
            }
        } catch (err) {
            document.body.removeChild(tempInput);
            reject(err);
        }
    });
}

// Toast
function showToast(msg) {
    const toast = document.getElementById('toast');
    document.getElementById('toast-msg').textContent = msg;
    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 3000);
}

// Mobile: close sidebar when clicking outside
document.addEventListener('click', function(e) {
    const sidebar = document.getElementById('sidebar');
    if (sidebar.classList.contains('open') && !sidebar.contains(e.target) && !e.target.closest('.mobile-toggle')) {
        sidebar.classList.remove('open');
    }
});

<?php if ($role !== 'admin'): ?>
// =====================================================================
// 🔥 සජීවීව Header/Sidebar Status Check කිරීම
// =====================================================================
const headerInitialStatus = <?php echo json_encode($status); ?>;
let headerPollingInterval = 8000;
let headerErrors = 0;
let headerTimer = null;

function checkHeaderStatusLive() {
    if (document.hidden) return;

    fetch('index.php?header_status_check=1')
        .then(r => r.json())
        .then(data => {
            headerErrors = 0;
            if (data.status && data.status !== headerInitialStatus) {
                showToast(data.status === 'active' ? '🎉 Your invitation is now active!' : '⚠️ Your account status has changed');
                setTimeout(() => location.reload(), 1800);
            }

            if (headerPollingInterval > 8000) {
                headerPollingInterval = 8000;
                resetHeaderTimer();
            }
        })
        .catch(err => {
            console.error('Error checking header status:', err);
            headerErrors++;
            if (headerErrors > 2) {
                headerPollingInterval = Math.min(60000, headerPollingInterval * 2);
                resetHeaderTimer();
            }
        });
}

function resetHeaderTimer() {
    if (headerTimer) clearInterval(headerTimer);
    headerTimer = setInterval(checkHeaderStatusLive, headerPollingInterval);
}

document.addEventListener("visibilitychange", () => {
    if (!document.hidden) {
        checkHeaderStatusLive();
    }
});
resetHeaderTimer();
<?php endif; ?>
</script>
