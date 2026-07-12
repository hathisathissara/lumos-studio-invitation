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
if (isset($_GET['action']) && in_array($_GET['action'], ['activate', 'deactivate', 'approve_upgrade', 'reject_upgrade', 'notify_delete'])) {
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

// 2. Approve Package Upgrade Request (Upgrade අනුමත කිරීම)
if (isset($_GET['action']) && $_GET['action'] === 'approve_upgrade' && isset($_GET['uid'])) {
    $u_id = intval($_GET['uid']);
    
    $stmtGetUpgrade = $pdo->prepare("SELECT name, email, pending_upgrade_plan, upgrade_slip FROM users WHERE id = ?");
    $stmtGetUpgrade->execute([$u_id]);
    $upgradeData = $stmtGetUpgrade->fetch();

    if ($upgradeData && !empty($upgradeData['pending_upgrade_plan'])) {
        // 'standard|1' හෝ 'premium|1' වැනි string එක package සහ gallery ලෙස වෙන්කර ගැනීම
        $parts = explode('|', $upgradeData['pending_upgrade_plan']);
        $target_package = $parts[0] ?? 'standard';
        $target_gallery = intval($parts[1] ?? 0);
        
        // පැරණි slip එක delete කර අලුත් එක save කිරීම
        $stmtOldSlip = $pdo->prepare("SELECT payment_slip FROM users WHERE id = ?");
        $stmtOldSlip->execute([$u_id]);
        $oldSlipFile = $stmtOldSlip->fetchColumn();
        if (!empty($oldSlipFile) && file_exists('../../' . $oldSlipFile)) {
            unlink('../../' . $oldSlipFile);
        }

        $stmtUpdatePkg = $pdo->prepare("UPDATE users SET package = ?, has_guest_gallery = ?, payment_slip = upgrade_slip, upgrade_slip = NULL, pending_upgrade_plan = NULL WHERE id = ?");
        if ($stmtUpdatePkg->execute([$target_package, $target_gallery, $u_id])) {
            
            if (function_exists('send_upgrade_success_mail')) {
                $new_plan_readable = ucfirst($target_package) . ($target_gallery ? " + Guest Gallery" : "");
                send_upgrade_success_mail($upgradeData['email'], $upgradeData['name'], $new_plan_readable);
            }
            $msg = "<div class='flash flash-success'><i class='fas fa-check-circle'></i> Upgrade approved! Couple successfully promoted to <strong>" . ucfirst($target_package) . " Plan</strong> and notified via email.</div>";
        }
    }
}

// 3. Reject Package Upgrade Request (Upgrade ප්‍රතික්ෂේප කිරීම)
if (isset($_GET['action']) && $_GET['action'] === 'reject_upgrade' && isset($_GET['uid'])) {
    $u_id = intval($_GET['uid']);
    
    $stmtGetUpgrade = $pdo->prepare("SELECT upgrade_slip FROM users WHERE id = ?");
    $stmtGetUpgrade->execute([$u_id]);
    $upgradeSlipFile = $stmtGetUpgrade->fetchColumn();

    if (!empty($upgradeSlipFile) && file_exists('../../' . $upgradeSlipFile)) {
        unlink('../../' . $upgradeSlipFile);
    }

    $stmtReject = $pdo->prepare("UPDATE users SET upgrade_slip = NULL, pending_upgrade_plan = NULL WHERE id = ?");
    if ($stmtReject->execute([$u_id])) {
        $msg = "<div class='flash flash-success'><i class='fas fa-check-circle'></i> Upgrade request rejected and slip file deleted. Account remains on previous active plan.</div>";
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

// 5. AJAX: සජීවීව (Live) Admin Stat Cards + Registered Couples Table Update කිරීම
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

    $liveUpgradeRequests = array_filter($liveUsersList, fn($u) => !empty($u['pending_upgrade_plan']) && !empty($u['upgrade_slip']));
    $liveUpgradeFormatted = [];
    foreach ($liveUpgradeRequests as $upg) {
        $parts = explode('|', $upg['pending_upgrade_plan']);
        $req_pkg = $parts[0] ?? 'standard';
        $req_gal = intval($parts[1] ?? 0);
        $req_text = ucfirst($req_pkg) . ($req_gal ? " + Guest Gallery" : "");

        $liveUpgradeFormatted[] = [
            'id' => (int) $upg['id'],
            'name' => htmlspecialchars($upg['name']),
            'email' => htmlspecialchars($upg['email']),
            'package' => ucfirst($upg['package'] ?? 'Basic'),
            'has_guest_gallery' => !empty($upg['has_guest_gallery']),
            'req_text' => htmlspecialchars($req_text),
            'upgrade_slip' => !empty($upg['upgrade_slip']) ? htmlspecialchars('../../' . $upg['upgrade_slip']) : null,
        ];
    }

    echo json_encode([
        'total' => $liveTotal,
        'active' => $liveActive,
        'pending' => $livePending,
        'refund_requests_count' => $liveRefunds,
        'users' => $liveRowsFormatted,
        'upgrade_requests' => $liveUpgradeFormatted,
    ]);
    exit();
}

// Main Users List Retrieval (සියලුම upgrade properties නිවැරදිව ලබාගෙන ඇත)
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

// Stats (100% ක්ම නිවැරදි $allUsersList යොදා ඇත)
$total   = count($allUsersList);
$active  = count(array_filter($allUsersList, fn($u) => $u['status'] === 'active'));
$pending = $total - $active;
$refund_requests_count = count(array_filter($allUsersList, fn($u) => !empty($u['refund_requested_at'])));

require '../layouts/header.php';
?>

<style>
    :root {
        --primary: #1a1a2e;
        --border-color: #e8ecf0;
        --gold: #c9a96e;
        --success: #10b981;
        --danger: #ef4444;
        --warning: #f59e0b;
        --info: #3b82f6;
        --text-dark: #1e293b;
        --text-muted: #64748b;
    }

    .flash { padding: 14px 20px; border-radius: 12px; font-size: 0.88rem; margin-bottom: 24px; display:flex; align-items:center; gap:10px; font-family: 'Inter', sans-serif; font-weight: 500; }
    .flash-success { background: rgba(16,185,129,0.08); border: 1px solid rgba(16,185,129,0.2); color: var(--success); }
    .flash-error   { background: rgba(239,68,68,0.08); border: 1px solid rgba(239,68,68,0.2); color: var(--danger); }

    .admin-stat { background: white; border: 1px solid #e8ecf0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); transition: transform 0.2s, box-shadow 0.2s; }
    .admin-stat:hover { transform: translateY(-3px); box-shadow: 0 12px 20px -3px rgba(0,0,0,0.06); }
    .admin-stat-icon { width: 42px; height: 42px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1rem; }
    .icon-gold  { background: rgba(201,169,110,0.12); color: #c9a96e; }
    .icon-green { background: rgba(34,197,94,0.12);  color: #22c55e; }
    .icon-amber { background: rgba(245,158,11,0.12); color: #d97706; }
    .icon-red   { background: rgba(239,68,68,0.12); color: #dc2626; }
    .admin-stat-num { font-size: 1.8rem; font-weight: 800; color: #1a1a2e; line-height: 1; }
    .admin-stat-label { font-size: 0.75rem; color: #9ea3b0; font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px; }

    .table-card { background: white; border: 1px solid #e8ecf0; border-radius: 16px; overflow: hidden; margin-bottom:28px; }
    .table-card-header { padding: 20px 24px; border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; }
    .table-card-header h5 { font-size: 1.05rem; font-weight: 700; color: #1a1a2e; margin: 0; }
    .search-wrap { position: relative; }
    .search-wrap i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #9ea3b0; font-size: 0.8rem; }
    .search-input { border: 1px solid #e8ecf0; border-radius: 10px; padding: 8px 12px 8px 34px; font-family: 'Inter', sans-serif; font-size: 0.82rem; outline: none; width: 220px; }
    .search-input:focus { border-color: #c9a96e; }

    .admin-table { width: 100%; border-collapse: collapse; }
    .admin-table th { padding: 12px 16px; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: #9ea3b0; background: #f8fafc; border-bottom: 1px solid #e8ecf0; text-align: left; white-space: nowrap; }
    .admin-table td { padding: 14px 16px; border-bottom: 1px solid #f1f5f9; font-size: 0.86rem; color: #4a5568; vertical-align: middle; }
    .admin-table tr:hover td { background: #fafbfc; }

    .couple-name { font-weight: 700; color: #1a1a2e; }
    .couple-email { font-size: 0.78rem; color: #9ea3b0; margin-top: 2px; }

    .badge-active  { display:inline-flex; align-items:center; gap:4px; background:rgba(34,197,94,0.1); color:#16a34a; border-radius:20px; padding:4px 10px; font-size:0.72rem; font-weight:700; }
    .badge-pending { display:inline-flex; align-items:center; gap:4px; background:rgba(245,158,11,0.1); color:#d97706; border-radius:20px; padding:4px 10px; font-size:0.72rem; font-weight:700; }
    .badge-refund-req { display:inline-flex; align-items:center; gap:4px; background:rgba(239,68,68,0.1); color:#dc2626; border-radius:20px; padding:4px 10px; font-size:0.72rem; font-weight:700; margin-top:4px; }
    .badge-upgrade-req { display:inline-flex; align-items:center; gap:4px; background:rgba(59,130,246,0.1); color:#2563eb; border-radius:20px; padding:4px 10px; font-size:0.72rem; font-weight:700; margin-top:4px; }

    .slip-thumb { width: 44px; height: 44px; border-radius: 8px; object-fit: cover; border: 1px solid #e8ecf0; cursor: pointer; transition: transform 0.2s; }
    .slip-thumb:hover { transform: scale(1.08); }

    .btn-action { display: inline-flex; align-items: center; gap: 6px; border-radius: 10px; padding: 8px 14px; font-size: 0.76rem; font-weight: 700; text-decoration: none; transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); cursor: pointer; border: 1px solid transparent; }
    .btn-activate { background: rgba(16,185,129,0.08); color: var(--success); border-color: rgba(16,185,129,0.12); }
    .btn-activate:hover { background: var(--success); color: white; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(16,185,129,0.25); }
    .btn-deactivate { background: rgba(245,158,11,0.08); color: var(--warning); border-color: rgba(245,158,11,0.12); }
    .btn-deactivate:hover { background: var(--warning); color: white; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(245,158,11,0.25); }
    .btn-preview { background: #fafbfc; color: var(--text-muted); border-color: var(--border-color); }
    .btn-preview:hover { border-color: var(--gold); color: var(--gold); background: var(--gold-light); }
    .btn-delete-account { display: inline-flex; align-items: center; justify-content: center; width: 34px; height: 34px; background: rgba(239,68,68,0.06); color: var(--danger); border: 1px solid rgba(239,68,68,0.15); border-radius: 10px; font-size: 0.8rem; text-decoration: none; transition: all 0.2s; margin-left: 4px; }
    .btn-delete-account:hover { background: var(--danger); color: white; box-shadow: 0 4px 10px rgba(239,68,68,0.2); }
    .btn-notify-delete { background: rgba(245,158,11,0.08); color: var(--warning); border-color: rgba(245,158,11,0.15); }
    .btn-notify-delete:hover { background: var(--warning); color: white; box-shadow: 0 4px 12px rgba(245,158,11,0.2); }
    .badge-countdown { display: inline-flex; align-items: center; gap: 6px; background: rgba(100,116,139,0.06); color: var(--text-muted); border: 1px solid var(--border-color); border-radius: 10px; padding: 8px 14px; font-size: 0.74rem; font-weight: 700; margin-left: 4px; white-space: nowrap; }

    .lightbox { display: none; position: fixed; inset: 0; background: rgba(15,15,26,0.92); backdrop-filter: blur(10px); z-index: 9999; align-items: center; justify-content: center; }
    .lightbox.open { display: flex; }
    .lightbox img { max-width: 90vw; max-height: 85vh; border-radius: 16px; object-fit: contain; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5); border: 2px solid rgba(255,255,255,0.05); }
    .lightbox-close { position: absolute; top: 24px; right: 28px; color: white; font-size: 2.2rem; cursor: pointer; transition: transform 0.2s; }
    .lightbox-close:hover { transform: scale(1.1) rotate(90deg); }

    .empty-table { text-align: center; padding: 50px; color: #9ea3b0; }
    
    @media (max-width: 767.98px) {
        .table-card-header { flex-direction: column; align-items: flex-start; }
        .admin-table, .admin-table thead, .admin-table tbody, .admin-table th, .admin-table td, .admin-table tr { display: block; }
        .admin-table thead tr { position: absolute; top: -9999px; left: -9999px; }
        .admin-table tr { border: 1px solid var(--border-color); border-radius: 12px; margin: 14px; padding: 6px 0; box-shadow: 0 1px 2px rgba(16,24,40,0.04); }
        .admin-table td { border-bottom: 1px dashed #f1f5f9; padding: 10px 16px; display: flex; align-items: flex-start; gap: 10px; }
        .admin-table td:last-child { border-bottom: none; }
        .admin-table td::before { content: attr(data-label); font-size: 0.64rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.6px; color: var(--text-muted); flex: 0 0 100px; padding-top: 2px; }
    }
</style>

<?php if ($msg) echo $msg; ?>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="admin-stat p-4 rounded-4 d-flex align-items-center gap-3 h-100">
            <div class="admin-stat-icon icon-gold flex-shrink-0"><i class="fas fa-users"></i></div>
            <div>
                <div class="admin-stat-num" id="live-admin-total"><?php echo $total; ?></div>
                <div class="admin-stat-label">Total Couples</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="admin-stat p-4 rounded-4 d-flex align-items-center gap-3 h-100">
            <div class="admin-stat-icon icon-green flex-shrink-0"><i class="fas fa-check-circle"></i></div>
            <div>
                <div class="admin-stat-num" id="live-admin-active"><?php echo $active; ?></div>
                <div class="admin-stat-label">Active</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="admin-stat p-4 rounded-4 d-flex align-items-center gap-3 h-100">
            <div class="admin-stat-icon icon-amber flex-shrink-0"><i class="fas fa-clock"></i></div>
            <div>
                <div class="admin-stat-num" id="live-admin-pending"><?php echo $pending; ?></div>
                <div class="admin-stat-label">Pending Review</div>
            </div>
        </div>
    </div>
    <!-- Refund Requests Stat Card -->
    <div class="col-6 col-md-3">
        <a href="admin_refunds.php" class="admin-stat p-4 rounded-4 d-flex align-items-center gap-3 h-100 text-decoration-none">
            <div class="admin-stat-icon icon-red flex-shrink-0"><i class="fas fa-undo-alt"></i></div>
            <div>
                <div class="admin-stat-num text-danger" id="live-admin-refunds"><?php echo $refund_requests_count; ?></div>
                <div class="admin-stat-label">Refund Requests</div>
            </div>
        </a>
    </div>
</div>

<!-- =====================================================================
     💡 UPGRADE REQUESTS SECTION
     ===================================================================== -->
<?php
$upgradeRequests = array_filter($allUsersList, fn($u) => !empty($u['pending_upgrade_plan']) && !empty($u['upgrade_slip']));
?>
<?php $hasUpgradeRequests = count($upgradeRequests) > 0; ?>
<div class="table-card border border-primary" id="upgrade-requests-card" style="<?php echo $hasUpgradeRequests ? '' : 'display:none;'; ?>">
    <div class="table-card-header bg-light text-primary" style="background: rgba(59,130,246,0.03) !important;">
        <h5 class="fw-bold" style="color: var(--info);"><i class="fas fa-arrow-circle-up me-1"></i> Pending Package Upgrade Reviews (පැකේජ් උසස් කිරීම්)</h5>
    </div>
    <div style="overflow-x:auto;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Couple Info</th>
                    <th>Current Plan</th>
                    <th>Requested Upgrade Plan</th>
                    <th>Upgrade Receipt</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="upgrade-requests-tbody">
                <?php foreach ($upgradeRequests as $upg): 
                    $parts = explode('|', $upg['pending_upgrade_plan']);
                    $req_pkg = $parts[0] ?? 'standard';
                    $req_gal = intval($parts[1] ?? 0);
                    $req_text = ucfirst($req_pkg) . ($req_gal ? " + Guest Gallery" : "");
                ?>
                <tr>
                    <td data-label="Couple Info">
                        <div class="fw-bold text-dark"><?php echo htmlspecialchars($upg['name']); ?></div>
                        <div class="text-muted small"><?php echo htmlspecialchars($upg['email']); ?></div>
                    </td>
                    <td data-label="Current Plan">
                        <span class="badge bg-secondary"><?php echo ucfirst($upg['package'] ?? 'Basic'); ?></span>
                        <?php echo !empty($upg['has_guest_gallery']) ? '<br><small class="text-success fw-bold">With Gallery</small>' : ''; ?>
                    </td>
                    <td data-label="Requested Plan">
                        <strong class="text-primary"><i class="fas fa-chevron-circle-right text-primary me-1"></i> <?php echo $req_text; ?></strong>
                    </td>
                    <td data-label="Receipt">
                        <?php if (!empty($upg['upgrade_slip'])): ?>
                            <img src="../../<?php echo htmlspecialchars($upg['upgrade_slip']); ?>" 
                                 class="slip-thumb border border-primary" 
                                 onclick="openLightbox(this.src)" 
                                 alt="Upgrade Receipt">
                        <?php endif; ?>
                    </td>
                    <td data-label="Actions" style="white-space:nowrap;">
                        <a href="index.php?action=approve_upgrade&uid=<?php echo $upg['id']; ?>&csrf_token=<?php echo $csrf_token; ?>" 
                           class="btn-action btn-activate"
                           onclick="return confirm('Approve package upgrade to <?php echo $req_text; ?> for <?php echo addslashes($upg['name']); ?>?');">
                            <i class="fas fa-check"></i> Approve Upgrade
                        </a>
                        <a href="index.php?action=reject_upgrade&uid=<?php echo $upg['id']; ?>&csrf_token=<?php echo $csrf_token; ?>" 
                           class="btn-action btn-deactivate"
                           style="margin-left:4px; color: var(--danger); background: rgba(239,68,68,0.06); border-color: rgba(239,68,68,0.12);"
                           onclick="return confirm('Reject upgrade request for <?php echo addslashes($upg['name']); ?>? This will delete the slip receipt.');">
                            <i class="fas fa-times"></i> Reject
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Table: Registered Couples -->
<div class="table-card">
    <div class="table-card-header">
        <h5>Registered Couples</h5>
        <div class="search-wrap">
            <i class="fas fa-search"></i>
            <input type="text" class="search-input" id="admin-search" placeholder="Search couples...">
        </div>
    </div>

    <div style="overflow-x:auto;">
        <table class="admin-table" id="admin-table">
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

                        $notice_sent = !empty($user['deletion_notice_sent_at']);
                        $days_left = 0;
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
                            <div class="couple-name">
                                <?php echo htmlspecialchars($user['name']); ?>
                            </div>
                            <div class="couple-email"><?php echo htmlspecialchars($user['email']); ?></div>
                            
                            <!-- Badges -->
                            <?php if (!empty($user['refund_requested_at'])): ?>
                                <span class="badge-refund-req">
                                    <i class="fas fa-exclamation-triangle"></i> Refund Requested
                                </span>
                            <?php endif; ?>
                            <?php if (!empty($user['pending_upgrade_plan'])): ?>
                                <span class="badge-upgrade-req">
                                    <i class="fas fa-arrow-circle-up"></i> Upgrade Pending
                                </span>
                            <?php endif; ?>
                        </td>
                        <!-- Plan/Package Display -->
                        <td data-label="Plan / Add-on">
                            <span class="badge bg-secondary"><?php echo ucfirst($user['package'] ?? 'Basic'); ?></span>
                            <?php if ($user['has_guest_gallery'] == 1): ?>
                                <br><small class="text-success fw-bold"><i class="fas fa-images"></i> Guest Gallery</small>
                            <?php endif; ?>
                        </td>
                        <td data-label="Wedding Date">
                            <?php if ($user['wedding_date']): ?>
                                <?php echo date("d M Y", strtotime($user['wedding_date'])); ?>
                                <?php if ($wedding_past): ?>
                                    <span style="display:inline-flex;align-items:center;gap:3px;background:rgba(239,68,68,0.08);color:#dc2626;border-radius:6px;padding:2px 7px;font-size:0.68rem;font-weight:700;margin-left:4px;"><i class="fas fa-clock" style="font-size:0.6rem;"></i> Passed</span>
                                <?php endif; ?>
                            <?php else: ?>
                                <span style="color:#d1d5db;">—</span>
                            <?php endif; ?>
                        </td>
                        <td data-label="Bank Slip">
                            <?php if (!empty($user['payment_slip'])): ?>
                                <?php
                                $slip_path = $user['payment_slip'];
                                $ext_slip = strtolower(pathinfo($user['payment_slip'], PATHINFO_EXTENSION));
                                ?>
                                <?php if ($ext_slip === 'pdf'): ?>
                                    <a href="<?php echo $slip_path; ?>" target="_blank"
                                       style="font-size:0.78rem; color:#c9a96e; text-decoration:none;">
                                        <i class="fas fa-file-pdf"></i> View PDF
                                    </a>
                                <?php else: ?>
                                    <img src="../../<?php echo htmlspecialchars($slip_path); ?>"
                                         class="slip-thumb"
                                         onclick="openLightbox(this.src)"
                                         alt="Payment slip">
                                <?php endif; ?>
                            <?php else: ?>
                                <span style="color:#d1d5db; font-size:0.78rem; font-style:italic;">No slip yet</span>
                            <?php endif; ?>
                        </td>
                        <td data-label="Status">
                            <?php if ($user['status'] === 'active'): ?>
                                <span class="badge-active"><i class="fas fa-circle" style="font-size:0.5rem;"></i> Active</span>
                            <?php else: ?>
                                <span class="badge-pending"><i class="fas fa-circle" style="font-size:0.5rem;"></i> Pending</span>
                            <?php endif; ?>
                        </td>
                        <td data-label="Invite Link">
                            <?php if ($user['wedding_id'] && !empty($user['slug'])): ?>
                                <div style="display:flex;align-items:center;gap:6px;">
                                    <span style="font-size:0.78rem;color:#4a5568;max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?php echo htmlspecialchars($invite_url); ?>"><?php echo htmlspecialchars($invite_url); ?></span>
                                    <button onclick="adminCopyLink('<?php echo addslashes($invite_url); ?>', this)" style="background:none;border:1px solid #e8ecf0;border-radius:6px;padding:3px 8px;cursor:pointer;font-size:0.72rem;color:#9ea3b0;flex-shrink:0;" title="Copy link"><i class="fas fa-copy"></i></button>
                                </div>
                            <?php elseif ($user['wedding_id']): ?>
                                <span style="font-size:0.75rem;color:#d1d5db;">No slug yet</span>
                            <?php else: ?>
                                <span style="color:#d1d5db;">—</span>
                            <?php endif; ?>
                        </td>
                        <td data-label="Actions" style="white-space:nowrap;">
                            <?php if ($user['wedding_id']): ?>
                            <a href="../../view_invitation.php?w_id=<?php echo $user['wedding_id']; ?>&preview=1"
                               target="_blank" class="btn-preview">
                                <i class="fas fa-eye"></i> Preview
                            </a>
                            <?php endif; ?>

                            <?php if ($user['status'] === 'pending'): ?>
                            <a href="index.php?action=activate&uid=<?php echo $user['id']; ?>&csrf_token=<?php echo $csrf_token; ?>"
                               class="btn-action btn-activate"
                               onclick="return confirm('Activate account for <?php echo addslashes($user['name']); ?>?');">
                                <i class="fas fa-check"></i> Activate
                            </a>
                            <?php else: ?>
                            <a href="index.php?action=deactivate&uid=<?php echo $user['id']; ?>&csrf_token=<?php echo $csrf_token; ?>"
                               class="btn-action btn-deactivate"
                               onclick="return confirm('Deactivate account for <?php echo addslashes($user['name']); ?>?');">
                                <i class="fas fa-times"></i> Deactivate
                            </a>
                            <?php endif; ?>

                            <?php if ($wedding_past): ?>
                                <?php if (!$notice_sent): ?>
                                <a href="index.php?action=notify_delete&uid=<?php echo $user['id']; ?>&csrf_token=<?php echo $csrf_token; ?>"
                                   class="btn-action btn-notify-delete"
                                   onclick="return confirm('Email <?php echo addslashes($user['name']); ?> that their invitation will be deleted in 7 days?');"
                                   title="Send 7-day deletion notice">
                                    <i class="fas fa-bell"></i> Notify
                                </a>
                                <?php elseif (!$can_delete_now): ?>
                                <span class="badge-countdown" title="Notice sent on <?php echo date('d M Y', strtotime($user['deletion_notice_sent_at'])); ?>">
                                    <i class="fas fa-hourglass-half"></i> <?php echo $days_left; ?>d left
                                </span>
                                <?php else: ?>
                                <a href="admin_delete_account.php?uid=<?php echo $user['id']; ?>&csrf_token=<?php echo $csrf_token; ?>"
                                   class="btn-delete-account"
                                   onclick="return confirm('Permanently delete this account? The 7-day notice period has ended.');"
                                   title="Notice period ended — delete this account">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="8" class="empty-table">No couples registered yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Lightbox for slip images with Backdrop blur -->
<div class="lightbox" id="lightbox" onclick="closeLightbox()">
    <span class="lightbox-close"><i class="fas fa-times"></i></span>
    <img src="" id="lightbox-img" alt="Payment slip">
</div>

<script>
// Search
document.getElementById('admin-search').addEventListener('input', function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('#admin-table tbody tr').forEach(row => {
        row.style.display = (row.dataset.search || '').includes(q) ? '' : 'none';
    });
});

// Lightbox
function openLightbox(src) {
    document.getElementById('lightbox-img').src = src;
    document.getElementById('lightbox').classList.add('open');
}
function closeLightbox() {
    document.getElementById('lightbox').classList.remove('open');
}
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeLightbox(); });

// =====================================================================
// 🔥 සජීවීව Admin Stat Cards Update කිරීම
// =====================================================================
function updateLiveText(id, newValue) {
    const el = document.getElementById(id);
    if (el && el.textContent != newValue) {
        el.style.transition = 'opacity 0.2s';
        el.style.opacity = '0.2';
        setTimeout(() => {
            el.textContent = newValue;
            el.style.opacity = '1';
        }, 200);
    }
}

function esc(str) {
    // Basic JS-side escape as a safety net (server already sends pre-escaped strings)
    return String(str).replace(/'/g, "\\'");
}

function buildCoupleRow(user) {
    let badges = '';
    if (user.refund_requested) {
        badges += `<span class="badge-refund-req"><i class="fas fa-exclamation-triangle"></i> Refund Requested</span>`;
    }
    if (user.upgrade_pending) {
        badges += `<span class="badge-upgrade-req"><i class="fas fa-arrow-circle-up"></i> Upgrade Pending</span>`;
    }

    const packageHtml = `<span class="badge bg-secondary">${user.package}</span>` +
        (user.has_guest_gallery ? `<br><small class="text-success fw-bold"><i class="fas fa-images"></i> Guest Gallery</small>` : '');

    let dateHtml = `<span style="color:#d1d5db;">—</span>`;
    if (user.wedding_date) {
        dateHtml = user.wedding_date;
        if (user.wedding_past) {
            dateHtml += `<span style="display:inline-flex;align-items:center;gap:3px;background:rgba(239,68,68,0.08);color:#dc2626;border-radius:6px;padding:2px 7px;font-size:0.68rem;font-weight:700;margin-left:4px;"><i class="fas fa-clock" style="font-size:0.6rem;"></i> Passed</span>`;
        }
    }

    let slipHtml = `<span style="color:#d1d5db; font-size:0.78rem; font-style:italic;">No slip yet</span>`;
    if (user.payment_slip) {
        slipHtml = user.slip_is_pdf
            ? `<a href="${user.payment_slip}" target="_blank" style="font-size:0.78rem; color:#c9a96e; text-decoration:none;"><i class="fas fa-file-pdf"></i> View PDF</a>`
            : `<img src="${user.payment_slip}" class="slip-thumb" onclick="openLightbox(this.src)" alt="Payment slip">`;
    }

    const statusHtml = user.status === 'active'
        ? `<span class="badge-active"><i class="fas fa-circle" style="font-size:0.5rem;"></i> Active</span>`
        : `<span class="badge-pending"><i class="fas fa-circle" style="font-size:0.5rem;"></i> Pending</span>`;

    let linkHtml = `<span style="color:#d1d5db;">—</span>`;
    if (user.wedding_id && user.has_slug) {
        linkHtml = `<div style="display:flex;align-items:center;gap:6px;">
            <span style="font-size:0.78rem;color:#4a5568;max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="${user.invite_url}">${user.invite_url}</span>
            <button onclick="adminCopyLink('${user.invite_url}', this)" style="background:none;border:1px solid #e8ecf0;border-radius:6px;padding:3px 8px;cursor:pointer;font-size:0.72rem;color:#9ea3b0;flex-shrink:0;" title="Copy link"><i class="fas fa-copy"></i></button>
        </div>`;
    } else if (user.wedding_id) {
        linkHtml = `<span style="font-size:0.75rem;color:#d1d5db;">No slug yet</span>`;
    }

    let actionsHtml = '';
    if (user.wedding_id) {
        actionsHtml += `<a href="../../view_invitation.php?w_id=${user.wedding_id}&preview=1" target="_blank" class="btn-preview"><i class="fas fa-eye"></i> Preview</a>`;
    }
    if (user.status === 'pending') {
        actionsHtml += `<a href="index.php?action=activate&uid=${user.id}&csrf_token=${csrfTokenJS}" class="btn-action btn-activate" onclick="return confirm('Activate account for ${esc(user.name)}?');"><i class="fas fa-check"></i> Activate</a>`;
    } else {
        actionsHtml += `<a href="index.php?action=deactivate&uid=${user.id}&csrf_token=${csrfTokenJS}" class="btn-action btn-deactivate" onclick="return confirm('Deactivate account for ${esc(user.name)}?');"><i class="fas fa-times"></i> Deactivate</a>`;
    }
    if (user.wedding_past) {
        if (!user.notice_sent) {
            actionsHtml += `<a href="index.php?action=notify_delete&uid=${user.id}&csrf_token=${csrfTokenJS}" class="btn-action btn-notify-delete" onclick="return confirm('Email ${esc(user.name)} that their invitation will be deleted in 7 days?');" title="Send 7-day deletion notice"><i class="fas fa-bell"></i> Notify</a>`;
        } else if (!user.can_delete_now) {
            actionsHtml += `<span class="badge-countdown" title="Notice sent on ${user.notice_sent_at}"><i class="fas fa-hourglass-half"></i> ${user.days_left}d left</span>`;
        } else {
            actionsHtml += `<a href="admin_delete_account.php?uid=${user.id}&csrf_token=${csrfTokenJS}" class="btn-delete-account" onclick="return confirm('Permanently delete this account? The 7-day notice period has ended.');" title="Notice period ended — delete this account"><i class="fas fa-trash-alt"></i></a>`;
        }
    }

    return `<tr data-search="${(user.name + ' ' + user.email).toLowerCase()}">
        <td data-label="Couple">
            <div class="couple-name">${user.name}</div>
            <div class="couple-email">${user.email}</div>
            ${badges}
        </td>
        <td data-label="Plan / Add-on">${packageHtml}</td>
        <td data-label="Wedding Date">${dateHtml}</td>
        <td data-label="Bank Slip">${slipHtml}</td>
        <td data-label="Status">${statusHtml}</td>
        <td data-label="Invite Link">${linkHtml}</td>
        <td data-label="Actions" style="white-space:nowrap;">${actionsHtml}</td>
    </tr>`;
}

function buildUpgradeRow(upg) {
    const galleryNote = upg.has_guest_gallery ? `<br><small class="text-success fw-bold">With Gallery</small>` : '';
    const slipHtml = upg.upgrade_slip
        ? `<img src="${upg.upgrade_slip}" class="slip-thumb border border-primary" onclick="openLightbox(this.src)" alt="Upgrade Receipt">`
        : '';

    return `<tr>
        <td data-label="Couple Info">
            <div class="fw-bold text-dark">${upg.name}</div>
            <div class="text-muted small">${upg.email}</div>
        </td>
        <td data-label="Current Plan"><span class="badge bg-secondary">${upg.package}</span>${galleryNote}</td>
        <td data-label="Requested Plan"><strong class="text-primary"><i class="fas fa-chevron-circle-right text-primary me-1"></i> ${upg.req_text}</strong></td>
        <td data-label="Receipt">${slipHtml}</td>
        <td data-label="Actions" style="white-space:nowrap;">
            <a href="index.php?action=approve_upgrade&uid=${upg.id}&csrf_token=${csrfTokenJS}" class="btn-action btn-activate" onclick="return confirm('Approve package upgrade to ${esc(upg.req_text)} for ${esc(upg.name)}?');"><i class="fas fa-check"></i> Approve Upgrade</a>
            <a href="index.php?action=reject_upgrade&uid=${upg.id}&csrf_token=${csrfTokenJS}" class="btn-action btn-deactivate" style="margin-left:4px; color: var(--danger); background: rgba(239,68,68,0.06); border-color: rgba(239,68,68,0.12);" onclick="return confirm('Reject upgrade request for ${esc(upg.name)}? This will delete the slip receipt.');"><i class="fas fa-times"></i> Reject</a>
        </td>
    </tr>`;
}

let csrfTokenJS = "<?php echo $csrf_token; ?>";
let lastUsersSnapshot = null;
let lastUpgradeSnapshot = null;
let adminPollPaused = false;
let pollingInterval = 5000;
let consecutiveErrors = 0;
let adminStatsTimer = null;

// Pause live refresh briefly whenever an action link/button inside the tables is pressed,
// so an in-flight 5s poll can never replace the row out from under a click mid-navigation.
document.addEventListener('mousedown', function(e) {
    if (e.target.closest('#admin-table-tbody') || e.target.closest('#upgrade-requests-tbody')) {
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
            updateLiveText('live-admin-total', data.total);
            updateLiveText('live-admin-active', data.active);
            updateLiveText('live-admin-pending', data.pending);
            updateLiveText('live-admin-refunds', data.refund_requests_count);

            // Only touch the DOM if the underlying data actually changed —
            // avoids wiping out rows mid-click and avoids needless flicker.
            const usersSnapshot = JSON.stringify(data.users);
            if (usersSnapshot !== lastUsersSnapshot) {
                lastUsersSnapshot = usersSnapshot;

                const tbody = document.getElementById('admin-table-tbody');
                if (tbody && data.users) {
                    if (data.users.length > 0) {
                        tbody.innerHTML = data.users.map(buildCoupleRow).join('');
                    } else {
                        tbody.innerHTML = `<tr><td colspan="8" class="empty-table">No couples registered yet.</td></tr>`;
                    }
                    // Re-apply the active search filter to the freshly rebuilt rows
                    const searchBox = document.getElementById('admin-search');
                    if (searchBox && searchBox.value) {
                        const q = searchBox.value.toLowerCase();
                        tbody.querySelectorAll('tr').forEach(row => {
                            row.style.display = (row.dataset.search || '').includes(q) ? '' : 'none';
                        });
                    }
                }
            }

            // Upgrade Requests card — show/hide as a whole, and rebuild rows only if changed
            const upgradeSnapshot = JSON.stringify(data.upgrade_requests);
            if (upgradeSnapshot !== lastUpgradeSnapshot) {
                lastUpgradeSnapshot = upgradeSnapshot;

                const upgradeCard = document.getElementById('upgrade-requests-card');
                const upgradeTbody = document.getElementById('upgrade-requests-tbody');
                if (upgradeCard && upgradeTbody && data.upgrade_requests) {
                    if (data.upgrade_requests.length > 0) {
                        upgradeTbody.innerHTML = data.upgrade_requests.map(buildUpgradeRow).join('');
                        upgradeCard.style.display = '';
                    } else {
                        upgradeTbody.innerHTML = '';
                        upgradeCard.style.display = 'none';
                    }
                }
            }

            if (pollingInterval > 5000) {
                pollingInterval = 5000;
                resetAdminStatsTimer();
            }
        })
        .catch(err => {
            console.error('Error syncing admin live stats:', err);
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

document.addEventListener("visibilitychange", () => {
    if (!document.hidden) {
        fetchAdminLiveStats();
    }
});
resetAdminStatsTimer();

// Admin copy invite link
function adminCopyLink(url, btn) {
    navigator.clipboard.writeText(url).then(() => {
        const orig = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check" style="color:#22c55e;"></i>';
        setTimeout(() => { btn.innerHTML = orig; }, 2000);
        showToast('\u2713 Invite link copied!');
    }).catch(() => {
        prompt('Copy this invite link:', url);
    });
}
</script>

<?php require '../layouts/footer.php'; ?>
