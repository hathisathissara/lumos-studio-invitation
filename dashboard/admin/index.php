<?php
session_start();
require '../../config/config.php';
require '../../config/mailer.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$msg = "";

if (isset($_GET['deleted'])) {
    $msg = "<div class='flash flash-success'><i class='fas fa-check-circle'></i> Account and all associated data permanently deleted.</div>";
}

// Check CSRF for state-changing actions
if (isset($_GET['action']) && in_array($_GET['action'], ['activate', 'deactivate', 'notify_delete'])) {
    if (!isset($_GET['csrf_token']) || $_GET['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF token validation failed.");
    }
}

// 1. Activate / Deactivate Standard Activation Flow
if (isset($_GET['action']) && isset($_GET['uid']) && in_array($_GET['action'], ['activate', 'deactivate'])) {
    $action_status = ($_GET['action'] === 'activate') ? 'active' : 'pending';
    $u_id = intval($_GET['uid']);
    
    $stmtUpdate = $pdo->prepare("UPDATE users SET status = ? WHERE id = ?");

    if ($stmtUpdate->execute([$action_status, $u_id])) {
        $msg = "<div class='flash flash-success'><i class='fas fa-check-circle'></i> Account status updated to <strong>{$action_status}</strong>.</div>";

        if ($action_status === 'active') {
            $stmtCoupleMail = $pdo->prepare("
                SELECT u.name, u.email, w.slug, w.id as wedding_id
                FROM users u LEFT JOIN weddings w ON u.id = w.user_id
                WHERE u.id = ?
            ");
            $stmtCoupleMail->execute([$u_id]);
            $coupleMailInfo = $stmtCoupleMail->fetch();

            if ($coupleMailInfo) {
                $domain_for_mail = rtrim('http://' . $_SERVER['HTTP_HOST'] . dirname(dirname(dirname($_SERVER['PHP_SELF']))), '/');
                $invite_slug_for_mail = !empty($coupleMailInfo['slug']) ? $coupleMailInfo['slug'] : ('invite.php?w_id=' . $coupleMailInfo['wedding_id']);
                $invite_url_for_mail = $domain_for_mail . '/' . $invite_slug_for_mail;

                send_activation_mail($coupleMailInfo['email'], $coupleMailInfo['name'], $invite_url_for_mail);
            }
        }
    }
}

// 4. Send the 7-day deletion notice
if (isset($_GET['action']) && $_GET['action'] === 'notify_delete' && isset($_GET['uid'])) {
    $notify_uid = intval($_GET['uid']);

    $stmtNotify = $pdo->prepare("SELECT name, email FROM users WHERE id = ?");
    $stmtNotify->execute([$notify_uid]);
    $notifyUser = $stmtNotify->fetch();

    if ($notifyUser) {
        $mail_sent = send_deletion_notice_mail($notifyUser['email'], $notifyUser['name']);

        if ($mail_sent) {
            $pdo->prepare("UPDATE users SET deletion_notice_sent_at = NOW() WHERE id = ?")->execute([$notify_uid]);
            $msg = "<div class='flash flash-success'><i class='fas fa-check-circle'></i> Deletion notice emailed to <strong>" . htmlspecialchars($notifyUser['name']) . "</strong>. Account can be deleted in 7 days.</div>";
        } else {
            $msg = "<div class='flash flash-error'><i class='fas fa-times-circle'></i> Failed to send the notice email. Please check the mail settings in config/mailer.php.</div>";
        }
    }
}

// 5. AJAX: Live Admin Stat Cards + Registered Couples Table Update
if (isset($_GET['action']) && $_GET['action'] === 'live_stats') {
    header('Content-Type: application/json');

    $stmtLiveList = $pdo->prepare("
        SELECT users.id, users.name, users.email, users.status, users.payment_slip, users.upgrade_slip, users.pending_upgrade_plan, users.created_at, users.deletion_notice_sent_at, users.refund_requested_at, users.package, users.has_guest_gallery,
               weddings.wedding_date, weddings.bride_name, weddings.groom_name, weddings.id as wedding_id,
               weddings.slug
        FROM users
        LEFT JOIN weddings ON users.id = weddings.user_id
        WHERE users.role = 'couple'
        ORDER BY users.id DESC
    ");
    $stmtLiveList->execute();
    $liveUsersList = $stmtLiveList->fetchAll();

    $liveDomain = rtrim('http://' . $_SERVER['HTTP_HOST'] . dirname(dirname(dirname($_SERVER['PHP_SELF']))), '/');

    $liveTotal   = count($liveUsersList);
    $liveActive  = count(array_filter($liveUsersList, fn($u) => $u['status'] === 'active'));
    $livePending = $liveTotal - $liveActive;
    $liveRefunds = count(array_filter($liveUsersList, fn($u) => !empty($u['refund_requested_at'])));
    $liveUpgrades = count(array_filter($liveUsersList, fn($u) => !empty($u['pending_upgrade_plan']) && !empty($u['upgrade_slip'])));

    $liveRowsFormatted = [];
    foreach ($liveUsersList as $user) {
        $wedding_past = !empty($user['wedding_date']) && strtotime($user['wedding_date']) < strtotime('today');
        $invite_slug  = !empty($user['slug']) ? $user['slug'] : ('invite.php?w_id=' . $user['wedding_id']);
        $invite_url   = $liveDomain . '/' . $invite_slug;

        $notice_sent = !empty($user['deletion_notice_sent_at']);
        $days_left = 0;
        $can_delete_now = false;
        if ($notice_sent) {
            $delete_eligible_at = strtotime($user['deletion_notice_sent_at'] . ' +7 days');
            $seconds_left = $delete_eligible_at - time();
            $days_left = $seconds_left > 0 ? (int) ceil($seconds_left / 86400) : 0;
            $can_delete_now = $seconds_left <= 0;
        }

        $slip_ext = null;
        if (!empty($user['payment_slip'])) {
            $slip_ext = strtolower(pathinfo($user['payment_slip'], PATHINFO_EXTENSION));
        }

        $liveRowsFormatted[] = [
            'id' => (int) $user['id'],
            'name' => htmlspecialchars($user['name']),
            'email' => htmlspecialchars($user['email']),
            'status' => $user['status'],
            'package' => ucfirst($user['package'] ?? 'Basic'),
            'has_guest_gallery' => !empty($user['has_guest_gallery']),
            'wedding_date' => $user['wedding_date'] ? date('d M Y', strtotime($user['wedding_date'])) : null,
            'wedding_past' => $wedding_past,
            'payment_slip' => !empty($user['payment_slip']) ? htmlspecialchars('../../' . $user['payment_slip']) : null,
            'slip_is_pdf' => ($slip_ext === 'pdf'),
            'refund_requested' => !empty($user['refund_requested_at']),
            'upgrade_pending' => !empty($user['pending_upgrade_plan']),
            'wedding_id' => $user['wedding_id'] ? (int) $user['wedding_id'] : null,
            'has_slug' => !empty($user['slug']),
            'invite_url' => htmlspecialchars($invite_url),
            'notice_sent' => $notice_sent,
            'notice_sent_at' => $notice_sent ? date('d M Y', strtotime($user['deletion_notice_sent_at'])) : null,
            'days_left' => $days_left,
            'can_delete_now' => $can_delete_now,
        ];
    }

    echo json_encode([
        'total' => $liveTotal,
        'active' => $liveActive,
        'pending' => $livePending,
        'refund_requests_count' => $liveRefunds,
        'upgrade_requests_count' => $liveUpgrades,
        'users' => $liveRowsFormatted,
    ]);
    exit();
}

// Main Users List Retrieval
$stmtUsers = $pdo->prepare("
    SELECT users.id, users.name, users.email, users.status, users.payment_slip, users.upgrade_slip, users.pending_upgrade_plan, users.created_at, users.deletion_notice_sent_at, users.refund_requested_at, users.package, users.has_guest_gallery,
           weddings.wedding_date, weddings.bride_name, weddings.groom_name, weddings.id as wedding_id,
           weddings.slug
    FROM users 
    LEFT JOIN weddings ON users.id = weddings.user_id 
    WHERE users.role = 'couple' 
    ORDER BY users.id DESC
");
$stmtUsers->execute();
$allUsersList = $stmtUsers->fetchAll();

$domain = rtrim('http://' . $_SERVER['HTTP_HOST'] . dirname(dirname(dirname($_SERVER['PHP_SELF']))), '/');

$total   = count($allUsersList);
$active  = count(array_filter($allUsersList, fn($u) => $u['status'] === 'active'));
$pending = $total - $active;
$refund_requests_count = count(array_filter($allUsersList, fn($u) => !empty($u['refund_requested_at'])));
$upgrade_requests_count = count(array_filter($allUsersList, fn($u) => !empty($u['pending_upgrade_plan']) && !empty($u['upgrade_slip'])));

require '../layouts/header.php';
?>

<style>
/* ============================================================
   ADMIN PANEL — PREMIUM REDESIGN
   ============================================================ */

:root {
    --ap-gold:        #c9a96e;
    --ap-gold-light:  rgba(201,169,110,0.12);
    --ap-gold-glow:   rgba(201,169,110,0.25);
    --ap-emerald:     #10b981;
    --ap-emerald-bg:  rgba(16,185,129,0.10);
    --ap-amber:       #f59e0b;
    --ap-amber-bg:    rgba(245,158,11,0.10);
    --ap-red:         #ef4444;
    --ap-red-bg:      rgba(239,68,68,0.10);
    --ap-blue:        #6366f1;
    --ap-blue-bg:     rgba(99,102,241,0.10);
    --ap-border-soft: #e8ecf0;
    --ap-surface:     #ffffff;
    --ap-text:        #1e293b;
    --ap-muted:       #64748b;
    --ap-faint:       #94a3b8;
    --ap-radius:      16px;
    --ap-radius-sm:   10px;
    --ap-shadow:      0 4px 24px rgba(15,15,26,0.06);
    --ap-shadow-lg:   0 12px 40px rgba(15,15,26,0.10);
}

.flash { display:flex; align-items:center; gap:12px; padding:14px 20px; border-radius:var(--ap-radius-sm); font-size:.875rem; font-weight:500; margin-bottom:28px; animation:slideDown .4s ease; }
@keyframes slideDown { from{opacity:0;transform:translateY(-10px)} to{opacity:1;transform:translateY(0)} }
.flash i { font-size:1rem; flex-shrink:0; }
.flash-success { background:var(--ap-emerald-bg); border:1px solid rgba(16,185,129,.25); color:#059669; }
.flash-error   { background:var(--ap-red-bg);     border:1px solid rgba(239,68,68,.25);  color:#dc2626; }

.admin-page-header { display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-bottom:28px; }
.admin-page-header-left h1 { font-size:1.5rem; font-weight:800; color:var(--ap-text); margin:0; letter-spacing:-.3px; }
.admin-page-header-left p  { font-size:.82rem; color:var(--ap-muted); margin:4px 0 0; }
.live-dot { display:inline-flex; align-items:center; gap:7px; background:var(--ap-emerald-bg); border:1px solid rgba(16,185,129,.2); border-radius:20px; padding:6px 14px; font-size:.75rem; font-weight:600; color:var(--ap-emerald); }
.live-dot::before { content:''; width:7px; height:7px; border-radius:50%; background:var(--ap-emerald); animation:pulse-dot 1.6s infinite; }
@keyframes pulse-dot { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:.4;transform:scale(1.3)} }

.stat-grid { display:grid; grid-template-columns:repeat(5,1fr); gap:18px; margin-bottom:28px; }
@media(max-width:1200px){.stat-grid{grid-template-columns:repeat(3,1fr)}}
@media(max-width:991px){.stat-grid{grid-template-columns:repeat(2,1fr)}}
@media(max-width:480px){.stat-grid{grid-template-columns:1fr;gap:12px}}

.stat-card { background:var(--ap-surface); border:1px solid var(--ap-border-soft); border-radius:var(--ap-radius); padding:22px 20px; display:flex; align-items:center; gap:16px; box-shadow:var(--ap-shadow); transition:transform .22s ease,box-shadow .22s ease; position:relative; overflow:hidden; text-decoration:none; cursor:default; }
a.stat-card { cursor:pointer; }
.stat-card::after { content:''; position:absolute; top:0; left:0; right:0; height:3px; border-radius:var(--ap-radius) var(--ap-radius) 0 0; opacity:0; transition:opacity .22s; }
.stat-card:hover { transform:translateY(-4px); box-shadow:var(--ap-shadow-lg); }
.stat-card:hover::after { opacity:1; }
.stat-card--gold::after  { background:linear-gradient(90deg,#c9a96e,#e2c28a); }
.stat-card--green::after { background:linear-gradient(90deg,#10b981,#34d399); }
.stat-card--amber::after { background:linear-gradient(90deg,#f59e0b,#fbbf24); }
.stat-card--red::after   { background:linear-gradient(90deg,#ef4444,#f87171); }
.stat-card--blue::after  { background:linear-gradient(90deg,#6366f1,#818cf8); }

.stat-icon { width:48px; height:48px; border-radius:13px; display:flex; align-items:center; justify-content:center; font-size:1.1rem; flex-shrink:0; transition:transform .2s; }
.stat-card:hover .stat-icon { transform:scale(1.08) rotate(-5deg); }
.stat-icon--gold  { background:var(--ap-gold-light);  color:var(--ap-gold); }
.stat-icon--green { background:var(--ap-emerald-bg);  color:var(--ap-emerald); }
.stat-icon--amber { background:var(--ap-amber-bg);    color:var(--ap-amber); }
.stat-icon--red   { background:var(--ap-red-bg);      color:var(--ap-red); }
.stat-icon--blue  { background:var(--ap-blue-bg);     color:var(--ap-blue); }

.stat-body { flex:1; min-width:0; }
.stat-num   { font-size:2rem; font-weight:900; color:var(--ap-text); line-height:1; letter-spacing:-1px; font-variant-numeric:tabular-nums; transition:color .2s; }
.stat-label { font-size:.72rem; font-weight:600; color:var(--ap-faint); text-transform:uppercase; letter-spacing:.7px; margin-top:4px; }

.panel-card { background:var(--ap-surface); border:1px solid var(--ap-border-soft); border-radius:var(--ap-radius); box-shadow:var(--ap-shadow); margin-bottom:24px; overflow:hidden; }
.panel-card-header { display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; padding:18px 24px; border-bottom:1px solid #f1f5f9; background:#fafbfc; }
.panel-card-title { display:flex; align-items:center; gap:10px; font-size:.95rem; font-weight:700; color:var(--ap-text); margin:0; }
.title-icon { width:32px; height:32px; border-radius:9px; display:flex; align-items:center; justify-content:center; font-size:.85rem; }
.title-icon--gold { background:var(--ap-gold-light); color:var(--ap-gold); }

.search-wrap { position:relative; }
.search-wrap .search-ico { position:absolute; left:12px; top:50%; transform:translateY(-50%); color:var(--ap-faint); font-size:.78rem; pointer-events:none; }
.search-input { border:1px solid var(--ap-border-soft); border-radius:var(--ap-radius-sm); padding:9px 12px 9px 34px; font-family:'Inter',sans-serif; font-size:.82rem; outline:none; width:230px; background:var(--ap-surface); color:var(--ap-text); transition:border-color .2s,box-shadow .2s; }
.search-input:focus { border-color:var(--ap-gold); box-shadow:0 0 0 3px var(--ap-gold-light); }
.search-input::placeholder { color:var(--ap-faint); }

.ap-table-wrap { overflow-x:auto; }
.ap-table { width:100%; border-collapse:collapse; min-width:700px; }
.ap-table thead tr { background:#f8fafc; }
.ap-table th { padding:11px 18px; font-size:.68rem; font-weight:700; text-transform:uppercase; letter-spacing:.9px; color:var(--ap-faint); border-bottom:1px solid #e8ecf0; text-align:left; white-space:nowrap; }
.ap-table td { padding:14px 18px; border-bottom:1px solid #f1f5f9; font-size:.855rem; color:var(--ap-muted); vertical-align:middle; }
.ap-table tbody tr { transition:background .15s; }
.ap-table tbody tr:hover td { background:#f8fafb; }
.ap-table tbody tr:last-child td { border-bottom:none; }

.couple-name   { font-weight:700; color:var(--ap-text); font-size:.88rem; }
.couple-email  { font-size:.76rem; color:var(--ap-faint); margin-top:2px; }
.couple-badges { display:flex; flex-wrap:wrap; gap:4px; margin-top:6px; }

.badge-pill    { display:inline-flex; align-items:center; gap:4px; border-radius:20px; padding:3px 9px; font-size:.68rem; font-weight:700; letter-spacing:.2px; }
.badge-active  { background:rgba(16,185,129,.10); color:#059669; border:1px solid rgba(16,185,129,.2); }
.badge-pending { background:rgba(245,158,11,.10); color:#b45309; border:1px solid rgba(245,158,11,.2); }
.badge-refund  { background:rgba(239,68,68,.10);  color:#dc2626; border:1px solid rgba(239,68,68,.2); }
.badge-upgrade { background:rgba(99,102,241,.10); color:#4f46e5; border:1px solid rgba(99,102,241,.2); }
.badge-pkg     { background:#f1f5f9; color:#475569; border:1px solid #e2e8f0; }
.badge-gallery { background:rgba(16,185,129,.08); color:#059669; border:1px solid rgba(16,185,129,.15); font-size:.65rem; }
.badge-passed  { display:inline-flex; align-items:center; gap:3px; background:rgba(239,68,68,.08); color:#dc2626; border-radius:6px; padding:2px 7px; font-size:.67rem; font-weight:700; margin-left:6px; }

.status-dot    { width:7px; height:7px; border-radius:50%; display:inline-block; margin-right:4px; }
.dot-active    { background:#10b981; box-shadow:0 0 0 3px rgba(16,185,129,.2); }
.dot-pending   { background:#f59e0b; box-shadow:0 0 0 3px rgba(245,158,11,.2); }

.slip-thumb    { width:42px; height:42px; border-radius:9px; object-fit:cover; border:2px solid #e8ecf0; cursor:pointer; transition:transform .2s,border-color .2s,box-shadow .2s; }
.slip-thumb:hover { transform:scale(1.1); border-color:var(--ap-gold); box-shadow:0 4px 12px rgba(201,169,110,.3); }
.slip-pdf-link { display:inline-flex; align-items:center; gap:6px; font-size:.78rem; color:var(--ap-gold); text-decoration:none; font-weight:600; padding:5px 10px; background:var(--ap-gold-light); border-radius:8px; border:1px solid var(--ap-gold-glow); transition:background .2s; }
.slip-pdf-link:hover { background:rgba(201,169,110,.2); color:var(--ap-gold); }
.slip-none { color:#d1d5db; font-size:.78rem; font-style:italic; }

.invite-link-cell { display:flex; align-items:center; gap:7px; }
.invite-link-text { font-size:.76rem; color:var(--ap-muted); max-width:155px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.btn-copy { background:none; border:1px solid #e2e8f0; border-radius:7px; padding:4px 9px; cursor:pointer; font-size:.72rem; color:var(--ap-faint); flex-shrink:0; transition:all .18s; line-height:1; }
.btn-copy:hover { border-color:var(--ap-gold); color:var(--ap-gold); }

.actions-cell { display:flex; align-items:center; gap:6px; flex-wrap:nowrap; }
.btn-ap { display:inline-flex; align-items:center; gap:5px; border-radius:var(--ap-radius-sm); padding:7px 13px; font-size:.74rem; font-weight:700; text-decoration:none; transition:all .2s cubic-bezier(.4,0,.2,1); cursor:pointer; border:1px solid transparent; white-space:nowrap; letter-spacing:.1px; }
.btn-ap:hover { transform:translateY(-1px); }
.btn-preview    { background:#f8fafc; color:var(--ap-muted); border-color:#e2e8f0; }
.btn-preview:hover { border-color:var(--ap-gold); color:var(--ap-gold); background:var(--ap-gold-light); box-shadow:0 3px 10px var(--ap-gold-glow); }
.btn-activate   { background:var(--ap-emerald-bg); color:#059669; border-color:rgba(16,185,129,.2); }
.btn-activate:hover { background:var(--ap-emerald); color:#fff; box-shadow:0 4px 14px rgba(16,185,129,.3); }
.btn-deactivate { background:var(--ap-amber-bg); color:#b45309; border-color:rgba(245,158,11,.2); }
.btn-deactivate:hover { background:var(--ap-amber); color:#fff; box-shadow:0 4px 14px rgba(245,158,11,.3); }
.btn-notify     { background:rgba(245,158,11,.08); color:#b45309; border-color:rgba(245,158,11,.2); }
.btn-notify:hover { background:var(--ap-amber); color:#fff; box-shadow:0 4px 14px rgba(245,158,11,.3); }
.btn-delete-icon { display:inline-flex; align-items:center; justify-content:center; width:32px; height:32px; border-radius:9px; background:var(--ap-red-bg); color:var(--ap-red); border:1px solid rgba(239,68,68,.2); text-decoration:none; font-size:.78rem; transition:all .2s; flex-shrink:0; }
.btn-delete-icon:hover { background:var(--ap-red); color:#fff; box-shadow:0 4px 12px rgba(239,68,68,.3); transform:translateY(-1px); }

.badge-countdown { display:inline-flex; align-items:center; gap:5px; background:#f1f5f9; color:var(--ap-muted); border:1px solid #e2e8f0; border-radius:9px; padding:7px 12px; font-size:.72rem; font-weight:700; white-space:nowrap; }

.empty-state { text-align:center; padding:52px 24px; color:var(--ap-faint); }
.empty-state i { font-size:2.5rem; margin-bottom:12px; display:block; opacity:.35; }
.empty-state p { font-size:.88rem; margin:0; }

.lightbox { display:none; position:fixed; inset:0; background:rgba(10,10,20,.94); backdrop-filter:blur(12px); -webkit-backdrop-filter:blur(12px); z-index:9999; align-items:center; justify-content:center; animation:lbFadeIn .2s ease; }
@keyframes lbFadeIn { from{opacity:0} to{opacity:1} }
.lightbox.open { display:flex; }
.lightbox-inner { position:relative; max-width:90vw; max-height:88vh; animation:lbScaleIn .25s cubic-bezier(.34,1.56,.64,1); }
@keyframes lbScaleIn { from{opacity:0;transform:scale(.85)} to{opacity:1;transform:scale(1)} }
.lightbox-inner img { max-width:90vw; max-height:85vh; border-radius:18px; object-fit:contain; box-shadow:0 30px 80px rgba(0,0,0,.6); border:1px solid rgba(255,255,255,.06); display:block; }
.lightbox-close { position:absolute; top:-14px; right:-14px; width:36px; height:36px; border-radius:50%; background:rgba(255,255,255,.1); border:1px solid rgba(255,255,255,.15); color:#fff; font-size:1rem; display:flex; align-items:center; justify-content:center; cursor:pointer; transition:background .2s,transform .2s; }
.lightbox-close:hover { background:rgba(239,68,68,.7); transform:rotate(90deg); }

@media(max-width:767px){
    .ap-table,
    .ap-table thead,.ap-table tbody,.ap-table th,.ap-table td,.ap-table tr{display:block}
    .ap-table thead tr{position:absolute;top:-9999px;left:-9999px}
    .ap-table tbody tr{border:1px solid var(--ap-border-soft);border-radius:12px;margin:12px;padding:4px 0;box-shadow:0 2px 8px rgba(15,24,40,.04)}
    .ap-table td{border-bottom:1px dashed #f1f5f9;padding:10px 14px;display:flex;align-items:flex-start;gap:10px}
    .ap-table td:last-child{border-bottom:none}
    .ap-table td::before{content:attr(data-label);font-size:.63rem;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:var(--ap-faint);flex:0 0 90px;padding-top:2px}
    .actions-cell{flex-wrap:wrap}
    .panel-card-header{flex-direction:column;align-items:flex-start}
    .search-input{width:100%}
}
</style>

<div class="admin-page-header">
    <div class="admin-page-header-left">
        <h1><i class="fas fa-shield-halved" style="color:var(--ap-gold);margin-right:10px;font-size:1.3rem;"></i>Admin Control Panel</h1>
        <p>Manage registered couples, packages & account lifecycle</p>
    </div>
    <div class="live-dot">Live Sync Active</div>
</div>

<?php if ($msg) echo $msg; ?>

<div class="stat-grid">
    <div class="stat-card stat-card--gold">
        <div class="stat-icon stat-icon--gold"><i class="fas fa-users"></i></div>
        <div class="stat-body">
            <div class="stat-num" id="live-admin-total"><?php echo $total; ?></div>
            <div class="stat-label">Total Couples</div>
        </div>
    </div>
    <div class="stat-card stat-card--green">
        <div class="stat-icon stat-icon--green"><i class="fas fa-circle-check"></i></div>
        <div class="stat-body">
            <div class="stat-num" id="live-admin-active"><?php echo $active; ?></div>
            <div class="stat-label">Active</div>
        </div>
    </div>
    <div class="stat-card stat-card--amber">
        <div class="stat-icon stat-icon--amber"><i class="fas fa-hourglass-half"></i></div>
        <div class="stat-body">
            <div class="stat-num" id="live-admin-pending"><?php echo $pending; ?></div>
            <div class="stat-label">Pending Review</div>
        </div>
    </div>
    <a href="admin_refunds.php" class="stat-card stat-card--red" style="cursor:pointer;">
        <div class="stat-icon stat-icon--red"><i class="fas fa-rotate-left"></i></div>
        <div class="stat-body">
            <div class="stat-num" id="live-admin-refunds" style="color:var(--ap-red);"><?php echo $refund_requests_count; ?></div>
            <div class="stat-label">Refund Requests</div>
        </div>
    </a>
    <a href="admin_upgrades.php" class="stat-card stat-card--blue" style="cursor:pointer;">
        <div class="stat-icon stat-icon--blue"><i class="fas fa-arrow-up-right-dots"></i></div>
        <div class="stat-body">
            <div class="stat-num" id="live-admin-upgrades" style="color:var(--ap-blue);"><?php echo $upgrade_requests_count; ?></div>
            <div class="stat-label">Upgrade Requests</div>
        </div>
    </a>
</div>

<div class="panel-card">
    <div class="panel-card-header">
        <h5 class="panel-card-title">
            <span class="title-icon title-icon--gold"><i class="fas fa-heart"></i></span>
            Registered Couples
            <span class="badge-pill badge-pkg" style="font-size:0.7rem;margin-left:6px;" id="couples-count-badge"><?php echo $total; ?></span>
        </h5>
        <div class="search-wrap">
            <i class="fas fa-search search-ico"></i>
            <input type="text" class="search-input" id="admin-search" placeholder="Search by name or email…" autocomplete="off">
        </div>
    </div>

    <div class="ap-table-wrap">
        <table class="ap-table" id="admin-table">
            <thead>
                <tr>
                    <th>Couple</th>
                    <th>Plan / Add-on</th>
                    <th>Wedding Date</th>
                    <th>Bank Slip</th>
                    <th>Status</th>
                    <th>Invite Link</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="admin-table-tbody">
                <?php if (count($allUsersList) > 0): ?>
                    <?php foreach ($allUsersList as $user):
                        $wedding_past = !empty($user['wedding_date']) && strtotime($user['wedding_date']) < strtotime('today');
                        $invite_slug  = !empty($user['slug']) ? $user['slug'] : ('invite.php?w_id=' . $user['wedding_id']);
                        $invite_url   = $domain . '/' . $invite_slug;

                        $notice_sent    = !empty($user['deletion_notice_sent_at']);
                        $days_left      = 0;
                        $can_delete_now = false;
                        if ($notice_sent) {
                            $delete_eligible_at = strtotime($user['deletion_notice_sent_at'] . ' +7 days');
                            $seconds_left = $delete_eligible_at - time();
                            $days_left = $seconds_left > 0 ? (int) ceil($seconds_left / 86400) : 0;
                            $can_delete_now = $seconds_left <= 0;
                        }
                    ?>
                    <tr data-search="<?php echo strtolower($user['name'] . ' ' . $user['email']); ?>">
                        <td data-label="Couple">
                            <div class="couple-name"><?php echo htmlspecialchars($user['name']); ?></div>
                            <div class="couple-email"><?php echo htmlspecialchars($user['email']); ?></div>
                            <div class="couple-badges">
                                <?php if (!empty($user['refund_requested_at'])): ?>
                                    <span class="badge-pill badge-refund"><i class="fas fa-triangle-exclamation"></i> Refund Req.</span>
                                <?php endif; ?>
                                <?php if (!empty($user['pending_upgrade_plan'])): ?>
                                    <span class="badge-pill badge-upgrade"><i class="fas fa-arrow-up"></i> Upgrade Pending</span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td data-label="Plan">
                            <span class="badge-pill badge-pkg"><?php echo ucfirst($user['package'] ?? 'Basic'); ?></span>
                            <?php if ($user['has_guest_gallery'] == 1): ?>
                                <br><span class="badge-pill badge-gallery" style="margin-top:4px;"><i class="fas fa-images"></i> +Gallery</span>
                            <?php endif; ?>
                        </td>
                        <td data-label="Wedding Date">
                            <?php if ($user['wedding_date']): ?>
                                <?php echo date("d M Y", strtotime($user['wedding_date'])); ?>
                                <?php if ($wedding_past): ?>
                                    <span class="badge-passed"><i class="fas fa-clock" style="font-size:0.6rem;"></i> Passed</span>
                                <?php endif; ?>
                            <?php else: ?>
                                <span style="color:#d1d5db;">—</span>
                            <?php endif; ?>
                        </td>
                        <td data-label="Bank Slip">
                            <?php if (!empty($user['payment_slip'])): ?>
                                <?php $ext_slip = strtolower(pathinfo($user['payment_slip'], PATHINFO_EXTENSION)); ?>
                                <?php if ($ext_slip === 'pdf'): ?>
                                    <a href="../../<?php echo htmlspecialchars($user['payment_slip']); ?>" target="_blank" class="slip-pdf-link">
                                        <i class="fas fa-file-pdf"></i> View PDF
                                    </a>
                                <?php else: ?>
                                    <img src="../../<?php echo htmlspecialchars($user['payment_slip']); ?>"
                                         class="slip-thumb"
                                         onclick="openLightbox(this.src)"
                                         alt="Payment slip">
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="slip-none">No slip yet</span>
                            <?php endif; ?>
                        </td>
                        <td data-label="Status">
                            <?php if ($user['status'] === 'active'): ?>
                                <span class="badge-pill badge-active">
                                    <span class="status-dot dot-active"></span> Active
                                </span>
                            <?php else: ?>
                                <span class="badge-pill badge-pending">
                                    <span class="status-dot dot-pending"></span> Pending
                                </span>
                            <?php endif; ?>
                        </td>
                        <td data-label="Invite Link">
                            <?php if ($user['wedding_id'] && !empty($user['slug'])): ?>
                                <div class="invite-link-cell">
                                    <span class="invite-link-text" title="<?php echo htmlspecialchars($invite_url); ?>"><?php echo htmlspecialchars($invite_url); ?></span>
                                    <button class="btn-copy" onclick="adminCopyLink('<?php echo addslashes($invite_url); ?>', this)" title="Copy link"><i class="fas fa-copy"></i></button>
                                </div>
                            <?php elseif ($user['wedding_id']): ?>
                                <span style="font-size:0.75rem;color:#d1d5db;">No slug yet</span>
                            <?php else: ?>
                                <span style="color:#d1d5db;">—</span>
                            <?php endif; ?>
                        </td>
                        <td data-label="Actions">
                            <div class="actions-cell">
                                <?php if ($user['wedding_id']): ?>
                                <a href="../../view_invitation.php?w_id=<?php echo $user['wedding_id']; ?>&preview=1"
                                   target="_blank" class="btn-ap btn-preview">
                                    <i class="fas fa-eye"></i> Preview
                                </a>
                                <?php endif; ?>

                                <?php if ($user['status'] === 'pending'): ?>
                                <a href="index.php?action=activate&uid=<?php echo $user['id']; ?>&csrf_token=<?php echo $csrf_token; ?>"
                                   class="btn-ap btn-activate"
                                   onclick="return confirm('Activate account for <?php echo addslashes($user['name']); ?>?');">
                                    <i class="fas fa-check"></i> Activate
                                </a>
                                <?php else: ?>
                                <a href="index.php?action=deactivate&uid=<?php echo $user['id']; ?>&csrf_token=<?php echo $csrf_token; ?>"
                                   class="btn-ap btn-deactivate"
                                   onclick="return confirm('Deactivate account for <?php echo addslashes($user['name']); ?>?');">
                                    <i class="fas fa-xmark"></i> Deactivate
                                </a>
                                <?php endif; ?>

                                <?php if ($wedding_past): ?>
                                    <?php if (!$notice_sent): ?>
                                    <a href="index.php?action=notify_delete&uid=<?php echo $user['id']; ?>&csrf_token=<?php echo $csrf_token; ?>"
                                       class="btn-ap btn-notify"
                                       onclick="return confirm('Email <?php echo addslashes($user['name']); ?> that their invitation will be deleted in 7 days?')"
                                       title="Send 7-day deletion notice">
                                        <i class="fas fa-bell"></i> Notify
                                    </a>
                                    <?php elseif (!$can_delete_now): ?>
                                    <span class="badge-countdown" title="Notice sent on <?php echo date('d M Y', strtotime($user['deletion_notice_sent_at'])); ?>">
                                        <i class="fas fa-hourglass-half"></i> <?php echo $days_left; ?>d left
                                    </span>
                                    <?php else: ?>
                                    <a href="admin_delete_account.php?uid=<?php echo $user['id']; ?>&csrf_token=<?php echo $csrf_token; ?>"
                                       class="btn-delete-icon"
                                       onclick="return confirm('Permanently delete this account? The 7-day notice period has ended.')"
                                       title="Notice period ended — delete this account">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">
                                <i class="fas fa-heart-crack"></i>
                                <p>No couples registered yet.</p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="lightbox" id="lightbox" onclick="closeLightbox()">
    <div class="lightbox-inner" onclick="event.stopPropagation()">
        <div class="lightbox-close" onclick="closeLightbox()"><i class="fas fa-xmark"></i></div>
        <img src="" id="lightbox-img" alt="Payment slip">
    </div>
</div>

<script>
document.getElementById('admin-search').addEventListener('input', function () {
    const q = this.value.toLowerCase().trim();
    document.querySelectorAll('#admin-table tbody tr').forEach(row => {
        row.style.display = (row.dataset.search || '').includes(q) ? '' : 'none';
    });
});

function openLightbox(src) {
    document.getElementById('lightbox-img').src = src;
    document.getElementById('lightbox').classList.add('open');
    document.body.style.overflow = 'hidden';
}
function closeLightbox() {
    document.getElementById('lightbox').classList.remove('open');
    document.body.style.overflow = '';
    setTimeout(() => { document.getElementById('lightbox-img').src = ''; }, 300);
}
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeLightbox(); });

function updateLiveText(id, newValue) {
    const el = document.getElementById(id);
    if (el && el.textContent != newValue) {
        el.style.transition = 'opacity 0.18s, transform 0.18s';
        el.style.opacity    = '0';
        el.style.transform  = 'translateY(-6px)';
        setTimeout(() => {
            el.textContent  = newValue;
            el.style.opacity   = '1';
            el.style.transform = 'translateY(0)';
        }, 180);
    }
}

function esc(str) {
    return String(str).replace(/'/g, "\\'");
}

function buildCoupleRow(user) {
    let badges = '';
    if (user.refund_requested) {
        badges += `<span class="badge-pill badge-refund"><i class="fas fa-triangle-exclamation"></i> Refund Req.</span>`;
    }
    if (user.upgrade_pending) {
        badges += `<span class="badge-pill badge-upgrade"><i class="fas fa-arrow-up"></i> Upgrade Pending</span>`;
    }
    const badgesHtml = badges ? `<div class="couple-badges">${badges}</div>` : '';

    const pkgHtml = `<span class="badge-pill badge-pkg">${user.package}</span>` +
        (user.has_guest_gallery ? `<br><span class="badge-pill badge-gallery" style="margin-top:4px;"><i class="fas fa-images"></i> +Gallery</span>` : '');

    let dateHtml = `<span style="color:#d1d5db;">—</span>`;
    if (user.wedding_date) {
        dateHtml = user.wedding_date;
        if (user.wedding_past) {
            dateHtml += `<span class="badge-passed"><i class="fas fa-clock" style="font-size:0.6rem;"></i> Passed</span>`;
        }
    }

    let slipHtml = `<span class="slip-none">No slip yet</span>`;
    if (user.payment_slip) {
        slipHtml = user.slip_is_pdf
            ? `<a href="${user.payment_slip}" target="_blank" class="slip-pdf-link"><i class="fas fa-file-pdf"></i> View PDF</a>`
            : `<img src="${user.payment_slip}" class="slip-thumb" onclick="openLightbox(this.src)" alt="Payment slip">`;
    }

    const statusHtml = user.status === 'active'
        ? `<span class="badge-pill badge-active"><span class="status-dot dot-active"></span> Active</span>`
        : `<span class="badge-pill badge-pending"><span class="status-dot dot-pending"></span> Pending</span>`;

    let linkHtml = `<span style="color:#d1d5db;">—</span>`;
    if (user.wedding_id && user.has_slug) {
        linkHtml = `<div class="invite-link-cell">
            <span class="invite-link-text" title="${user.invite_url}">${user.invite_url}</span>
            <button class="btn-copy" onclick="adminCopyLink('${user.invite_url}', this)" title="Copy link"><i class="fas fa-copy"></i></button>
        </div>`;
    } else if (user.wedding_id) {
        linkHtml = `<span style="font-size:0.75rem;color:#d1d5db;">No slug yet</span>`;
    }

    let actionsHtml = '';
    if (user.wedding_id) {
        actionsHtml += `<a href="../../view_invitation.php?w_id=${user.wedding_id}&preview=1" target="_blank" class="btn-ap btn-preview"><i class="fas fa-eye"></i> Preview</a>`;
    }
    if (user.status === 'pending') {
        actionsHtml += `<a href="index.php?action=activate&uid=${user.id}&csrf_token=${csrfTokenJS}" class="btn-ap btn-activate" onclick="return confirm('Activate account for ${esc(user.name)}?');"><i class="fas fa-check"></i> Activate</a>`;
    } else {
        actionsHtml += `<a href="index.php?action=deactivate&uid=${user.id}&csrf_token=${csrfTokenJS}" class="btn-ap btn-deactivate" onclick="return confirm('Deactivate account for ${esc(user.name)}?');"><i class="fas fa-xmark"></i> Deactivate</a>`;
    }
    if (user.wedding_past) {
        if (!user.notice_sent) {
            actionsHtml += `<a href="index.php?action=notify_delete&uid=${user.id}&csrf_token=${csrfTokenJS}" class="btn-ap btn-notify" onclick="return confirm('Email ${esc(user.name)} that their invitation will be deleted in 7 days?');" title="Send 7-day deletion notice"><i class="fas fa-bell"></i> Notify</a>`;
        } else if (!user.can_delete_now) {
            actionsHtml += `<span class="badge-countdown" title="Notice sent on ${user.notice_sent_at}"><i class="fas fa-hourglass-half"></i> ${user.days_left}d left</span>`;
        } else {
            actionsHtml += `<a href="admin_delete_account.php?uid=${user.id}&csrf_token=${csrfTokenJS}" class="btn-delete-icon" onclick="return confirm('Permanently delete this account? The 7-day notice period has ended.');" title="Notice period ended — delete this account"><i class="fas fa-trash-alt"></i></a>`;
        }
    }

    return `<tr data-search="${(user.name + ' ' + user.email).toLowerCase()}">
        <td data-label="Couple">
            <div class="couple-name">${user.name}</div>
            <div class="couple-email">${user.email}</div>
            ${badgesHtml}
        </td>
        <td data-label="Plan">${pkgHtml}</td>
        <td data-label="Wedding Date">${dateHtml}</td>
        <td data-label="Bank Slip">${slipHtml}</td>
        <td data-label="Status">${statusHtml}</td>
        <td data-label="Invite Link">${linkHtml}</td>
        <td data-label="Actions"><div class="actions-cell">${actionsHtml}</div></td>
    </tr>`;
}

let csrfTokenJS       = "<?php echo $csrf_token; ?>";
let lastUsersSnapshot = null;
let adminPollPaused   = false;
let pollingInterval   = 5000;
let consecutiveErrors = 0;
let adminStatsTimer   = null;

document.addEventListener('mousedown', function (e) {
    if (e.target.closest('#admin-table-tbody')) {
        adminPollPaused = true;
        setTimeout(() => { adminPollPaused = false; }, 3000);
    }
});

function fetchAdminLiveStats() {
    if (adminPollPaused || document.hidden) return;

    fetch('index.php?action=live_stats')
        .then(r => r.json())
        .then(data => {
            consecutiveErrors = 0;
            if (data.error) return;

            updateLiveText('live-admin-total',   data.total);
            updateLiveText('live-admin-active',  data.active);
            updateLiveText('live-admin-pending', data.pending);
            updateLiveText('live-admin-refunds', data.refund_requests_count);
            updateLiveText('live-admin-upgrades', data.upgrade_requests_count);
            updateLiveText('couples-count-badge', data.total);

            const usersSnapshot = JSON.stringify(data.users);
            if (usersSnapshot !== lastUsersSnapshot) {
                lastUsersSnapshot = usersSnapshot;
                const tbody = document.getElementById('admin-table-tbody');
                if (tbody && data.users) {
                    if (data.users.length > 0) {
                        tbody.innerHTML = data.users.map(buildCoupleRow).join('');
                    } else {
                        tbody.innerHTML = `<tr><td colspan="7"><div class="empty-state"><i class="fas fa-heart-crack"></i><p>No couples registered yet.</p></div></td></tr>`;
                    }
                    const searchBox = document.getElementById('admin-search');
                    if (searchBox && searchBox.value) {
                        const q = searchBox.value.toLowerCase();
                        tbody.querySelectorAll('tr').forEach(row => {
                            row.style.display = (row.dataset.search || '').includes(q) ? '' : 'none';
                        });
                    }
                }
            }

            if (pollingInterval > 5000) {
                pollingInterval = 5000;
                resetAdminStatsTimer();
            }
        })
        .catch(err => {
            console.error('Admin live stats error:', err);
            consecutiveErrors++;
            if (consecutiveErrors > 2) {
                pollingInterval = Math.min(60000, pollingInterval * 2);
                resetAdminStatsTimer();
            }
        });
}

function resetAdminStatsTimer() {
    if (adminStatsTimer) clearInterval(adminStatsTimer);
    adminStatsTimer = setInterval(fetchAdminLiveStats, pollingInterval);
}

document.addEventListener('visibilitychange', () => {
    if (!document.hidden) fetchAdminLiveStats();
});
resetAdminStatsTimer();

function adminCopyLink(url, btn) {
    navigator.clipboard.writeText(url).then(() => {
        const orig = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check" style="color:#10b981;"></i>';
        btn.style.borderColor = '#10b981';
        setTimeout(() => {
            btn.innerHTML = orig;
            btn.style.borderColor = '';
        }, 2000);
        
        // Show existing header toast if function exists
        if(typeof showToast === 'function') {
            showToast('✓ Invite link copied!');
        }
    }).catch(() => {
        prompt('Copy this invite link:', url);
    });
}
</script>

<?php require '../layouts/footer.php'; ?>
