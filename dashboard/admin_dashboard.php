<?php
session_start();
require '../config/config.php';
require '../config/mailer.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$msg = "";

if (isset($_GET['deleted'])) {
    $msg = "<div class='flash flash-success'><i class='fas fa-check-circle'></i> Account and all associated data permanently deleted.</div>";
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
                $domain_for_mail = rtrim('http://' . $_SERVER['HTTP_HOST'] . dirname(dirname($_SERVER['PHP_SELF'])), '/');
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
        $parts = explode('|', $upgradeData['pending_upgrade_plan']);
        $target_package = $parts[0] ?? 'standard';
        $target_gallery = intval($parts[1] ?? 0);
        
        // පැරණි slip එක delete කර අලුත් එක save කිරීම
        $stmtOldSlip = $pdo->prepare("SELECT payment_slip FROM users WHERE id = ?");
        $stmtOldSlip->execute([$u_id]);
        $oldSlipFile = $stmtOldSlip->fetchColumn();
        if (!empty($oldSlipFile) && file_exists('../' . $oldSlipFile)) {
            unlink('../' . $oldSlipFile);
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

    if (!empty($upgradeSlipFile) && file_exists('../' . $upgradeSlipFile)) {
        unlink('../' . $upgradeSlipFile);
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

$domain = rtrim('http://' . $_SERVER['HTTP_HOST'] . dirname(dirname($_SERVER['PHP_SELF'])), '/');

// Stats (මෙහි $usersList වෙනුවට $allUsersList ලෙස නිවැරදි කර ඇත!)
$total   = count($allUsersList);
$active  = count(array_filter($allUsersList, fn($u) => $u['status'] === 'active'));
$pending = $total - $active;
$refund_requests_count = count(array_filter($allUsersList, fn($u) => !empty($u['refund_requested_at'])));

require 'layouts/header.php';
?>

<style>
    .flash { padding: 13px 18px; border-radius: 12px; font-size: 0.87rem; margin-bottom: 20px; display:flex; align-items:center; gap:10px; }
    .flash-success { background: rgba(34,197,94,0.1); border:1px solid rgba(34,197,94,0.25); color: #16a34a; }
    .flash-error   { background: rgba(239,68,68,0.1); border:1px solid rgba(239,68,68,0.25); color: #dc2626; }

    .admin-stats { display: flex; gap: 16px; margin-bottom: 24px; flex-wrap: wrap; }
    .admin-stat { background: white; border: 1px solid #e8ecf0; border-radius: 14px; padding: 18px 24px; display: flex; align-items: center; gap: 14px; flex: 1; min-width: 160px; }
    .admin-stat-icon { width: 42px; height: 42px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1rem; }
    .icon-gold  { background: rgba(201,169,110,0.12); color: #c9a96e; }
    .icon-green { background: rgba(34,197,94,0.12);  color: #22c55e; }
    .icon-amber { background: rgba(245,158,11,0.12); color: #d97706; }
    .icon-red   { background: rgba(239,68,68,0.12); color: #dc2626; }
    .admin-stat-num { font-size: 1.8rem; font-weight: 800; color: #1a1a2e; line-height: 1; }
    .admin-stat-label { font-size: 0.75rem; color: #9ea3b0; font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px; }

    .table-card { background: white; border: 1px solid #e8ecf0; border-radius: 16px; overflow: hidden; margin-bottom:28px; }
    .table-card-header { padding: 20px 24px; border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; }
    .table-card-header h5 { font-size: 0.95rem; font-weight: 700; color: #1a1a2e; margin: 0; }
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

    .btn-activate { display: inline-flex; align-items: center; gap: 5px; background: rgba(34,197,94,0.1); color: #16a34a; border: 1px solid rgba(34,197,94,0.2); border-radius: 8px; padding: 6px 12px; font-size: 0.75rem; font-weight: 700; text-decoration: none; transition: all 0.2s; cursor: pointer; }
    .btn-activate:hover { background: rgba(34,197,94,0.2); color: #16a34a; }
    .btn-deactivate { display: inline-flex; align-items: center; gap: 5px; background: rgba(245,158,11,0.1); color: #d97706; border: 1px solid rgba(245,158,11,0.2); border-radius: 8px; padding: 6px 12px; font-size: 0.75rem; font-weight: 700; text-decoration: none; transition: all 0.2s; cursor: pointer; }
    .btn-deactivate:hover { background: rgba(245,158,11,0.2); color: #d97706; }
    .btn-preview { display: inline-flex; align-items: center; gap: 5px; background: transparent; color: #9ea3b0; border: 1px solid #e8ecf0; border-radius: 8px; padding: 6px 12px; font-size: 0.75rem; font-weight: 500; text-decoration: none; transition: all 0.2s; margin-right: 4px; }
    .btn-preview:hover { border-color: #c9a96e; color: #c9a96e; }
    .btn-delete-account { display: inline-flex; align-items: center; justify-content: center; width: 30px; height: 30px; background: rgba(239,68,68,0.08); color: #dc2626; border: 1px solid rgba(239,68,68,0.2); border-radius: 8px; font-size: 0.75rem; text-decoration: none; transition: all 0.2s; margin-left: 2px; }
    .btn-delete-account:hover { background: rgba(239,68,68,0.18); color: #dc2626; }
    .btn-notify-delete { display: inline-flex; align-items: center; gap: 5px; background: rgba(245,158,11,0.1); color: #d97706; border: 1px solid rgba(245,158,11,0.2); border-radius: 8px; padding: 6px 12px; font-size: 0.75rem; font-weight: 700; text-decoration: none; transition: all 0.2s; margin-left: 2px; }
    .btn-notify-delete:hover { background: rgba(245,158,11,0.2); color: #d97706; }
    .badge-countdown { display: inline-flex; align-items: center; gap: 5px; background: rgba(107,114,128,0.08); color: #6b7280; border-radius: 8px; padding: 6px 12px; font-size: 0.72rem; font-weight: 700; margin-left: 2px; white-space: nowrap; }

    .lightbox { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.85); z-index: 9999; align-items: center; justify-content: center; }
    .lightbox.open { display: flex; }
    .lightbox img { max-width: 90vw; max-height: 90vh; border-radius: 12px; object-fit: contain; }
    .lightbox-close { position: absolute; top: 20px; right: 20px; color: white; font-size: 1.8rem; cursor: pointer; }

    .empty-table { text-align: center; padding: 50px; color: #9ea3b0; }
</style>

<?php if ($msg) echo $msg; ?>

<!-- Stats Strip -->
<div class="admin-stats">
    <div class="admin-stat">
        <div class="admin-stat-icon icon-gold"><i class="fas fa-users"></i></div>
        <div>
            <div class="admin-stat-num"><?php echo $total; ?></div>
            <div class="admin-stat-label">Total Couples</div>
        </div>
    </div>
    <div class="admin-stat">
        <div class="admin-stat-icon icon-green"><i class="fas fa-check-circle"></i></div>
        <div>
            <div class="admin-stat-num"><?php echo $active; ?></div>
            <div class="admin-stat-label">Active</div>
        </div>
    </div>
    <div class="admin-stat">
        <div class="admin-stat-icon icon-amber"><i class="fas fa-clock"></i></div>
        <div>
            <div class="admin-stat-num"><?php echo $pending; ?></div>
            <div class="admin-stat-label">Pending Review</div>
        </div>
    </div>
    <!-- Refund Requests Stat Card -->
    <a href="admin_refunds.php" class="admin-stat text-decoration-none">
        <div class="admin-stat-icon icon-red"><i class="fas fa-undo-alt"></i></div>
        <div>
            <div class="admin-stat-num text-danger"><?php echo $refund_requests_count; ?></div>
            <div class="admin-stat-label text-danger">Refund Requests</div>
        </div>
    </a>
</div>

<!-- =====================================================================
     💡 UPGRADE REQUESTS SECTION
     ===================================================================== -->
<?php
// Upgrade ඉල්ලීම් ඇති අය පමණක් DB එකෙන් පෙරා ගැනීම (දැන් මෙය 100% ක්ම ක්‍රියාත්මක වේ)
$upgradeRequests = array_filter($allUsersList, fn($u) => !empty($u['pending_upgrade_plan']) && !empty($u['upgrade_slip']));
?>
<?php if (count($upgradeRequests) > 0): ?>
<div class="table-card border border-primary">
    <div class="table-card-header bg-light text-primary">
        <h5 class="fw-bold"><i class="fas fa-arrow-circle-up me-1"></i> Pending Package Upgrade Reviews (පැකේජ් උසස් කිරීම්)</h5>
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
            <tbody>
                <?php foreach ($upgradeRequests as $upg): 
                    $parts = explode('|', $upg['pending_upgrade_plan']);
                    $req_pkg = $parts[0] ?? 'standard';
                    $req_gal = intval($parts[1] ?? 0);
                    $req_text = ucfirst($req_pkg) . ($req_gal ? " + Guest Gallery" : "");
                ?>
                <tr>
                    <td>
                        <div class="fw-bold text-dark"><?php echo htmlspecialchars($upg['name']); ?></div>
                        <div class="text-muted small"><?php echo htmlspecialchars($upg['email']); ?></div>
                    </td>
                    <td>
                        <span class="badge bg-secondary"><?php echo ucfirst($upg['package'] ?? 'Basic'); ?></span>
                        <?php echo !empty($upg['has_guest_gallery']) ? '<br><small class="text-success fw-bold">With Gallery</small>' : ''; ?>
                    </td>
                    <td>
                        <strong class="text-primary"><i class="fas fa-chevron-circle-right text-primary me-1"></i> <?php echo $req_text; ?></strong>
                    </td>
                    <td>
                        <?php if (!empty($upg['upgrade_slip'])): ?>
                            <img src="../<?php echo htmlspecialchars($upg['upgrade_slip']); ?>" 
                                 class="slip-thumb border border-primary" 
                                 onclick="openLightbox(this.src)" 
                                 alt="Upgrade Receipt">
                        <?php endif; ?>
                    </td>
                    <td style="white-space:nowrap;">
                        <a href="admin_dashboard.php?action=approve_upgrade&uid=<?php echo $upg['id']; ?>" 
                           class="btn-activate"
                           onclick="return confirm('Approve package upgrade to <?php echo $req_text; ?> for <?php echo addslashes($upg['name']); ?>?');">
                            <i class="fas fa-check"></i> Approve Upgrade
                        </a>
                        <a href="admin_dashboard.php?action=reject_upgrade&uid=<?php echo $upg['id']; ?>" 
                           class="btn-deactivate"
                           style="margin-left:4px;"
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
<?php endif; ?>

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
                    <th>Email</th>
                    <th>Plan / Add-on</th>
                    <th>Wedding Date</th>
                    <th>Bank Slip</th>
                    <th>Status</th>
                    <th>Invite Link</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
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
                        <td>
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
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <!-- Plan/Package Display -->
                        <td>
                            <span class="badge bg-secondary"><?php echo ucfirst($user['package'] ?? 'Basic'); ?></span>
                            <?php if ($user['has_guest_gallery'] == 1): ?>
                                <br><small class="text-success fw-bold"><i class="fas fa-images"></i> Guest Gallery</small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($user['wedding_date']): ?>
                                <?php echo date("d M Y", strtotime($user['wedding_date'])); ?>
                                <?php if ($wedding_past): ?>
                                    <span style="display:inline-flex;align-items:center;gap:3px;background:rgba(239,68,68,0.08);color:#dc2626;border-radius:6px;padding:2px 7px;font-size:0.68rem;font-weight:700;margin-left:4px;"><i class="fas fa-clock" style="font-size:0.6rem;"></i> Passed</span>
                                <?php endif; ?>
                            <?php else: ?>
                                <span style="color:#d1d5db;">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($user['payment_slip'])): ?>
                                <?php
                                $slip_path = '../' . $user['payment_slip'];
                                $ext_slip = strtolower(pathinfo($user['payment_slip'], PATHINFO_EXTENSION));
                                ?>
                                <?php if ($ext_slip === 'pdf'): ?>
                                    <a href="<?php echo $slip_path; ?>" target="_blank"
                                       style="font-size:0.78rem; color:#c9a96e; text-decoration:none;">
                                        <i class="fas fa-file-pdf"></i> View PDF
                                    </a>
                                <?php else: ?>
                                    <img src="<?php echo htmlspecialchars($slip_path); ?>"
                                         class="slip-thumb"
                                         onclick="openLightbox(this.src)"
                                         alt="Payment slip">
                                <?php endif; ?>
                            <?php else: ?>
                                <span style="color:#d1d5db; font-size:0.78rem; font-style:italic;">No slip yet</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($user['status'] === 'active'): ?>
                                <span class="badge-active"><i class="fas fa-circle" style="font-size:0.5rem;"></i> Active</span>
                            <?php else: ?>
                                <span class="badge-pending"><i class="fas fa-circle" style="font-size:0.5rem;"></i> Pending</span>
                            <?php endif; ?>
                        </td>
                        <td>
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
                        <td style="white-space:nowrap;">
                            <?php if ($user['wedding_id']): ?>
                            <a href="../view_invitation.php?w_id=<?php echo $user['wedding_id']; ?>&preview=1"
                               target="_blank" class="btn-preview">
                                <i class="fas fa-eye"></i> Preview
                            </a>
                            <?php endif; ?>

                            <?php if ($user['status'] === 'pending'): ?>
                            <a href="admin_dashboard.php?action=activate&uid=<?php echo $user['id']; ?>"
                               class="btn-activate"
                               onclick="return confirm('Activate account for <?php echo addslashes($user['name']); ?>?');">
                                <i class="fas fa-check"></i> Activate
                            </a>
                            <?php else: ?>
                            <a href="admin_dashboard.php?action=deactivate&uid=<?php echo $user['id']; ?>"
                               class="btn-deactivate"
                               onclick="return confirm('Deactivate account for <?php echo addslashes($user['name']); ?>?');">
                                <i class="fas fa-times"></i> Deactivate
                            </a>
                            <?php endif; ?>

                            <?php if ($wedding_past): ?>
                                <?php if (!$notice_sent): ?>
                                <a href="admin_dashboard.php?action=notify_delete&uid=<?php echo $user['id']; ?>"
                                   class="btn-notify-delete"
                                   onclick="return confirm('Email <?php echo addslashes($user['name']); ?> that their invitation will be deleted in 7 days?');"
                                   title="Send 7-day deletion notice">
                                    <i class="fas fa-bell"></i> Notify
                                </a>
                                <?php elseif (!$can_delete_now): ?>
                                <span class="badge-countdown" title="Notice sent on <?php echo date('d M Y', strtotime($user['deletion_notice_sent_at'])); ?>">
                                    <i class="fas fa-hourglass-half"></i> <?php echo $days_left; ?>d left
                                </span>
                                <?php else: ?>
                                <a href="admin_delete_account.php?uid=<?php echo $user['id']; ?>"
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

<!-- Lightbox for slip images -->
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

<?php require 'layouts/footer.php'; ?>