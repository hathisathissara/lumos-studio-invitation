<?php
session_start();
require '../../config/config.php';
require '../../config/mailer.php';

// ආරක්ෂාව සඳහා Admin පමණක් ඇතුලත් කර ගැනීම
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$msg = "";

// AJAX: සජීවීව (Live) Refund Phase Counts + Table Rows Update කිරීම
if (isset($_GET['action']) && $_GET['action'] === 'live_counts') {
    header('Content-Type: application/json');

    // Phase 1: Pending refund requests (full row data, same as main query below)
    $stmtLiveRequests = $pdo->prepare("SELECT users.id as user_id, users.name, users.email, users.payment_slip, users.refund_requested_at, users.refund_reason, weddings.id as wedding_id
                                        FROM users
                                        JOIN weddings ON users.id = weddings.user_id
                                        WHERE users.refund_status = 'pending' AND users.refund_requested_at IS NOT NULL
                                        ORDER BY users.refund_requested_at DESC");
    $stmtLiveRequests->execute();
    $liveRequests = $stmtLiveRequests->fetchAll();

    $liveRequestsFormatted = [];
    foreach ($liveRequests as $ref) {
        $stmtCheckGuest = $pdo->prepare("SELECT COUNT(*) as c FROM guests WHERE wedding_id = ? AND (is_opened = 1 OR rsvp_status != 'pending')");
        $stmtCheckGuest->execute([$ref['wedding_id']]);
        $openedGuestsCount = (int) ($stmtCheckGuest->fetch()['c'] ?? 0);

        $liveRequestsFormatted[] = [
            'user_id' => (int) $ref['user_id'],
            'name' => htmlspecialchars($ref['name']),
            'email' => htmlspecialchars($ref['email']),
            'requested_at' => date('d M Y, h:i A', strtotime($ref['refund_requested_at'])),
            'reason' => htmlspecialchars($ref['refund_reason']),
            'is_eligible' => ($openedGuestsCount == 0),
            'opened_count' => $openedGuestsCount,
            'payment_slip' => !empty($ref['payment_slip']) ? htmlspecialchars($ref['payment_slip']) : null,
        ];
    }

    // Phase 2: Pending bank payouts (full row data)
    $stmtLivePayouts = $pdo->prepare("SELECT users.id as user_id, users.name, users.email, users.refund_bank_details, users.payment_slip
                                       FROM users
                                       WHERE users.refund_status = 'details_submitted'
                                       ORDER BY users.id DESC");
    $stmtLivePayouts->execute();
    $livePayouts = $stmtLivePayouts->fetchAll();

    $livePayoutsFormatted = [];
    foreach ($livePayouts as $pay) {
        $livePayoutsFormatted[] = [
            'user_id' => (int) $pay['user_id'],
            'name' => htmlspecialchars($pay['name']),
            'email' => htmlspecialchars($pay['email']),
            'bank_details' => htmlspecialchars($pay['refund_bank_details']),
            'payment_slip' => !empty($pay['payment_slip']) ? htmlspecialchars($pay['payment_slip']) : null,
        ];
    }

    echo json_encode([
        'pending_count' => count($liveRequestsFormatted),
        'payout_count' => count($livePayoutsFormatted),
        'refund_requests' => $liveRequestsFormatted,
        'payouts' => $livePayoutsFormatted,
    ]);
    exit();
}

// CSRF Protection for actions
if (isset($_GET['action']) && in_array($_GET['action'], ['reject', 'approve', 'complete'])) {
    if (!isset($_GET['csrf_token']) || $_GET['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF token validation failed.");
    }
}

// 1. Action: Reject Refund (Refund එක ප්‍රතික්ෂේප කිරීම)
if (isset($_GET['action']) && $_GET['action'] === 'reject' && isset($_GET['uid'])) {
    $u_id = intval($_GET['uid']);
    
    $stmtUser = $pdo->prepare("SELECT name, email FROM users WHERE id = ?");
    $stmtUser->execute([$u_id]);
    $userInfo = $stmtUser->fetch();

    if ($userInfo) {
        $stmt = $pdo->prepare("UPDATE users SET refund_status = 'rejected', refund_requested_at = NULL WHERE id = ?");
        if ($stmt->execute([$u_id])) {
            if (function_exists('send_refund_rejected_mail')) {
                send_refund_rejected_mail($userInfo['email'], $userInfo['name']);
            }
            $msg = "<div class='flash flash-success'><i class='fas fa-check-circle'></i> Refund request rejected successfully. Notification email sent to couple.</div>";
        }
    }
}

// 2. Action: Approve Refund (Refund එක අනුමත කිරීම - Couple එකෙන් බැංකු විස්තර ඉල්ලයි)
if (isset($_GET['action']) && $_GET['action'] === 'approve' && isset($_GET['uid'])) {
    $u_id = intval($_GET['uid']);
    
    $stmtUser = $pdo->prepare("SELECT name, email FROM users WHERE id = ?");
    $stmtUser->execute([$u_id]);
    $userInfo = $stmtUser->fetch();

    if ($userInfo) {
        $stmt = $pdo->prepare("UPDATE users SET status = 'pending', refund_status = 'approved', refund_requested_at = NULL WHERE id = ?");
        if ($stmt->execute([$u_id])) {
            if (function_exists('send_refund_approved_mail')) {
                send_refund_approved_mail($userInfo['email'], $userInfo['name']);
            }
            $msg = "<div class='flash flash-success'><i class='fas fa-check-circle'></i> Refund approved! Account status set to Pending, and email sent requesting bank details.</div>";
        }
    }
}

// 3. Action: Complete Payout (බැංකුවට මුදල් දමා අවසන් කිරීම සහ Slips මකාදැමීම)
if (isset($_GET['action']) && $_GET['action'] === 'complete' && isset($_GET['uid'])) {
    $u_id = intval($_GET['uid']);
    
    $stmtUser = $pdo->prepare("SELECT name, email, package, has_guest_gallery, payment_slip, upgrade_slip FROM users WHERE id = ?");
    $stmtUser->execute([$u_id]);
    $userInfo = $stmtUser->fetch();

    if ($userInfo) {
        // A. සැබෑම ගෙවූ මුදල ගණනය කිරීම
        $refund_amount = 2500;
        if ($userInfo['package'] === 'standard') $refund_amount = 5000;
        if ($userInfo['package'] === 'premium') $refund_amount = 10000;
        if ($userInfo['has_guest_gallery'] == 1 && $userInfo['package'] !== 'premium') {
            $refund_amount += 2000; // add-on එක ගෙන තිබේ නම් එයද එකතු වේ
        }

        // B. සර්වර් එකේ ඇති Slips ගොනු (Files) දෙකම මකා දැමීම (Unlink)
        if (!empty($userInfo['payment_slip']) && file_exists('../../' . $userInfo['payment_slip'])) {
            unlink('../../' . $userInfo['payment_slip']);
        }
        if (!empty($userInfo['upgrade_slip']) && file_exists('../../' . $userInfo['upgrade_slip'])) {
            unlink('../../' . $userInfo['upgrade_slip']);
        }

        // C. Database එක සම්පූර්ණයෙන්ම Reset කර refund completed තත්ත්වයට පත් කිරීම
        $stmt = $pdo->prepare("UPDATE users SET 
            status = 'pending', 
            refund_status = 'completed', 
            package = 'basic', 
            has_guest_gallery = 0, 
            payment_slip = NULL, 
            upgrade_slip = NULL, 
            refund_requested_at = NULL, 
            refund_reason = NULL, 
            refund_bank_details = NULL 
            WHERE id = ?");
        
        if ($stmt->execute([$u_id])) {
            if (function_exists('send_refund_completed_mail')) {
                send_refund_completed_mail($userInfo['email'], $userInfo['name'], $refund_amount);
            }
            $msg = "<div class='flash flash-success'><i class='fas fa-check-circle'></i> Refund of Rs. " . number_format($refund_amount) . " marked as completed! Slips deleted, Guest Gallery locked, and couple notified.</div>";
        }
    }
}

// A. Refund ඉල්ලීම් කර ඇති අය
$stmtRefundRequests = $pdo->prepare("SELECT users.id as user_id, users.name, users.email, users.payment_slip, users.refund_requested_at, users.refund_reason, weddings.id as wedding_id
                                     FROM users 
                                     JOIN weddings ON users.id = weddings.user_id 
                                     WHERE users.refund_status = 'pending' AND users.refund_requested_at IS NOT NULL 
                                     ORDER BY users.refund_requested_at DESC");
$stmtRefundRequests->execute();
$refundRequests = $stmtRefundRequests->fetchAll();

// B. බැංකු විස්තර එවූ අය
$stmtPayouts = $pdo->prepare("SELECT users.id as user_id, users.name, users.email, users.refund_bank_details, users.payment_slip
                              FROM users 
                              WHERE users.refund_status = 'details_submitted' 
                              ORDER BY users.id DESC");
$stmtPayouts->execute();
$payoutsList = $stmtPayouts->fetchAll();

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
        --text-dark: #1e293b;
        --text-muted: #64748b;
        --radius-lg: 16px;
        --radius-md: 12px;
        --radius-sm: 8px;
        --shadow-sm: 0 1px 2px rgba(16,24,40,0.04);
        --shadow-md: 0 4px 10px -2px rgba(16,24,40,0.06), 0 2px 4px -2px rgba(16,24,40,0.03);
    }

    body { background: #f6f7fb; }
    .page-heading { font-family: 'Cormorant Garamond', serif; font-weight: 800; color: var(--primary); letter-spacing: 0.3px; }
    .page-subheading { color: var(--text-muted); font-size: 0.88rem; }

    .admin-nav-tabs { display: flex; gap: 6px; background: white; border: 1px solid var(--border-color); border-radius: 12px; padding: 5px; box-shadow: var(--shadow-sm); }
    .admin-nav-tab { display: inline-flex; align-items: center; gap: 8px; padding: 9px 16px; border-radius: 9px; font-size: 0.82rem; font-weight: 700; color: var(--text-muted); text-decoration: none; transition: all 0.2s; white-space: nowrap; }
    .admin-nav-tab:hover { background: #f8fafc; color: var(--primary); }
    .admin-nav-tab.active { background: var(--primary); color: #f8f5ef; }
    .admin-nav-tab.active i { color: var(--gold); }
    .admin-nav-tab .tab-badge { background: rgba(239,68,68,0.12); color: var(--danger); border-radius: 20px; font-size: 0.66rem; font-weight: 800; padding: 1px 7px; }
    .admin-nav-tab.active .tab-badge { background: rgba(239,68,68,0.85); color: white; }

    .refund-stat { background: white; border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); transition: transform 0.2s, box-shadow 0.2s; }
    .refund-stat:hover { transform: translateY(-2px); box-shadow: var(--shadow-md); }
    .refund-stat-icon { width: 44px; height: 44px; font-size: 1.05rem; }
    .refund-stat-num { font-size: 1.7rem; font-weight: 800; color: var(--primary); line-height: 1; margin-bottom: 3px; }
    .refund-stat-label { font-size: 0.72rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.6px; }

    .flash { padding: 14px 20px; border-radius: var(--radius-md); font-size: 0.88rem; margin-bottom: 24px; display:flex; align-items:center; gap:10px; font-family: 'Inter', sans-serif; font-weight: 500; box-shadow: var(--shadow-sm); }
    .flash-success { background: rgba(16,185,129,0.08); border: 1px solid rgba(16,185,129,0.2); color: var(--success); }

    .table-card { background: white; border: 1px solid var(--border-color); border-radius: var(--radius-lg); overflow: hidden; margin-bottom: 28px; box-shadow: var(--shadow-sm); }
    .table-card-header { padding: 20px 26px; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; }
    .table-card-header h5 { font-size: 1rem; font-weight: 800; margin: 0; font-family: 'Inter', sans-serif; letter-spacing: 0.3px; display: flex; align-items: center; gap: 8px; }
    .table-card-header .header-count { font-size: 0.72rem; font-weight: 700; border-radius: 20px; padding: 3px 10px; background: rgba(255,255,255,0.7); }

    .admin-table { width: 100%; border-collapse: collapse; }
    .admin-table th { padding: 13px 20px; font-size: 0.68rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.9px; color: var(--text-muted); background: #f8fafc; border-bottom: 1px solid var(--border-color); text-align: left; white-space: nowrap; }
    .admin-table td { padding: 16px 20px; border-bottom: 1px solid #f1f5f9; font-size: 0.87rem; color: var(--text-dark); vertical-align: middle; }
    .admin-table tr:hover td { background: #fafbfc; }

    .couple-avatar { width: 36px; height: 36px; border-radius: 50%; color: #fff; font-weight: 700; font-size: 0.78rem; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .avatar-danger { background: linear-gradient(135deg, var(--danger), #b91c1c); }
    .avatar-success { background: linear-gradient(135deg, var(--success), #047857); }
    .couple-cell { display: flex; align-items: flex-start; gap: 12px; }

    .badge-eligible, .badge-non-eligible { display:inline-flex; align-items:center; gap:5px; border-radius:20px; padding:4px 12px; font-size:0.7rem; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; white-space: nowrap; }
    .badge-eligible { background:rgba(16,185,129,0.08); color:var(--success); border:1px solid rgba(16,185,129,0.18); }
    .badge-non-eligible { background:rgba(239,68,68,0.08); color:var(--danger); border:1px solid rgba(239,68,68,0.18); }

    .reason-box { background: #f8fafc; border: 1px solid var(--border-color); border-radius: var(--radius-sm); padding: 12px 16px; font-size: 0.82rem; color: #334155; margin-top: 8px; font-style: italic; line-height: 1.5; max-width: 320px; white-space: normal; word-break: break-word; }
    .bank-box { background: #fffdf5; border-left: 4px solid var(--success); border-top: 1px solid var(--border-color); border-right: 1px solid var(--border-color); border-bottom: 1px solid var(--border-color); padding: 14px 18px; font-size: 0.84rem; color: #15803d; border-radius: var(--radius-sm); font-family: 'Inter', monospace; line-height: 1.6; font-weight: 600; white-space: pre-line; }

    .action-cell { display: flex; flex-wrap: wrap; align-items: center; gap: 6px; }
    .btn-action { display: inline-flex; align-items: center; gap: 6px; border-radius: var(--radius-sm); padding: 8px 13px; font-size: 0.74rem; font-weight: 700; text-decoration: none; transition: all 0.2s ease; cursor: pointer; border: 1px solid transparent; white-space: nowrap; }
    .btn-action-approve { background: rgba(16,185,129,0.08); color: var(--success); border-color: rgba(16,185,129,0.12); }
    .btn-action-approve:hover { background: var(--success); color: white; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(16,185,129,0.25); }
    .btn-action-reject { background: rgba(239,68,68,0.06); color: var(--danger); border-color: rgba(239,68,68,0.12); }
    .btn-action-reject:hover { background: var(--danger); color: white; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(239,68,68,0.25); }

    .btn-action-complete { display: inline-flex; align-items: center; gap: 6px; background: linear-gradient(135deg, var(--success) 0%, #047857 100%); color: white; border: none; border-radius: var(--radius-sm); padding: 10px 16px; font-size: 0.78rem; font-weight: 700; text-decoration: none; transition: all 0.2s ease; cursor: pointer; box-shadow: 0 2px 8px rgba(16,185,129,0.2); white-space: nowrap; }
    .btn-action-complete:hover { transform: translateY(-1px); box-shadow: 0 8px 20px rgba(16,185,129,0.35); }

    .empty-table-row i { display: block; font-size: 2rem; opacity: 0.3; margin-bottom: 10px; }

    @media (max-width: 767.98px) {
        .table-card-header { flex-direction: column; align-items: flex-start; }
        .admin-table, .admin-table thead, .admin-table tbody, .admin-table th, .admin-table td, .admin-table tr { display: block; }
        .admin-table thead tr { position: absolute; top: -9999px; left: -9999px; }
        .admin-table tr { border: 1px solid var(--border-color); border-radius: var(--radius-md); margin: 14px; padding: 6px 0; box-shadow: var(--shadow-sm); }
        .admin-table td { border-bottom: 1px dashed #f1f5f9; padding: 10px 16px; display: flex; align-items: flex-start; gap: 10px; }
        .admin-table td:last-child { border-bottom: none; }
        .admin-table td::before { content: attr(data-label); font-size: 0.64rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.6px; color: var(--text-muted); flex: 0 0 100px; padding-top: 2px; }
        .action-cell { flex-wrap: wrap; }
        .reason-box, .bank-box { max-width: 100%; }
    }
</style>

<div class="d-flex flex-wrap justify-content-between align-items-end mb-4 gap-2">
    <div>
        <h3 class="page-heading mb-1">Refund Requests Dashboard</h3>
        <p class="page-subheading mb-0">පරිපාලක මුදල් ආපසු ගෙවීම් — review and process couple refund requests</p>
    </div>
    <div class="admin-nav-tabs">
        <a href="index.php" class="admin-nav-tab"><i class="fas fa-shield-alt"></i> Admin Panel</a>
        <a href="admin_refunds.php" class="admin-nav-tab active">
            <i class="fas fa-undo-alt"></i> Refund Requests
            <?php if (count($refundRequests) > 0): ?><span class="tab-badge"><?php echo count($refundRequests); ?></span><?php endif; ?>
        </a>
    </div>
</div>

<?php if ($msg) echo $msg; ?>

<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="refund-stat p-4 rounded-4 d-flex align-items-center gap-3 h-100">
            <div class="refund-stat-icon d-flex align-items-center justify-content-center flex-shrink-0 rounded-3" style="background:rgba(239,68,68,0.12); color:var(--danger);"><i class="fas fa-exclamation-circle"></i></div>
            <div>
                <div class="refund-stat-num" id="live-refund-stat-pending"><?php echo count($refundRequests); ?></div>
                <div class="refund-stat-label">Pending Reviews</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="refund-stat p-4 rounded-4 d-flex align-items-center gap-3 h-100">
            <div class="refund-stat-icon d-flex align-items-center justify-content-center flex-shrink-0 rounded-3" style="background:rgba(16,185,129,0.12); color:var(--success);"><i class="fas fa-university"></i></div>
            <div>
                <div class="refund-stat-num" id="live-refund-stat-payout"><?php echo count($payoutsList); ?></div>
                <div class="refund-stat-label">Awaiting Payout</div>
            </div>
        </div>
    </div>
</div>

<!-- 1. TABLE: PENDING REFUND REQUESTS REVIEWS -->
<div class="table-card border" style="border-color: rgba(239,68,68,0.2) !important;">
    <div class="table-card-header text-danger" style="background: rgba(239,68,68,0.04);">
        <h5><i class="fas fa-exclamation-circle"></i> Phase 1: Pending Refund Reviews (අනුමැතිය අපේක්ෂාවෙන්)
            <span class="header-count" id="live-refund-pending-count" style="color:var(--danger);"><?php echo count($refundRequests); ?></span>
        </h5>
    </div>

    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Couple Info</th>
                    <th>Request Details</th>
                    <th>Shared Track Validation</th>
                    <th>Payment Slip</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="live-refund-requests-tbody">
                <?php if (count($refundRequests) > 0): ?>
                    <?php foreach ($refundRequests as $ref):
                        $stmtCheckGuest = $pdo->prepare("SELECT COUNT(*) as c FROM guests WHERE wedding_id = ? AND (is_opened = 1 OR rsvp_status != 'pending')");
                        $stmtCheckGuest->execute([$ref['wedding_id']]);
                        $openedGuestsCount = $stmtCheckGuest->fetch()['c'] ?? 0;
                        $isEligible = ($openedGuestsCount == 0);
                    ?>
                    <tr>
                        <td data-label="Couple">
                            <div class="couple-cell">
                                <div class="couple-avatar avatar-danger"><?php echo strtoupper(substr($ref['name'], 0, 1)); ?></div>
                                <div>
                                    <div class="fw-bold text-dark" style="font-size:0.9rem;"><?php echo htmlspecialchars($ref['name']); ?></div>
                                    <div class="text-muted small" style="margin-top:2px;"><?php echo htmlspecialchars($ref['email']); ?></div>
                                </div>
                            </div>
                        </td>
                        <td data-label="Request">
                            <div>
                                <div class="small fw-bold text-muted"><i class="far fa-clock"></i> <?php echo date('d M Y, h:i A', strtotime($ref['refund_requested_at'])); ?></div>
                                <div class="reason-box">"<?php echo htmlspecialchars($ref['refund_reason']); ?>"</div>
                            </div>
                        </td>
                        <td data-label="Validation">
                            <?php if ($isEligible): ?>
                                <span class="badge-eligible"><i class="fas fa-check-circle"></i> Eligible (0 opened)</span>
                            <?php else: ?>
                                <span class="badge-non-eligible" title="This couple has already shared the link with guests.">
                                    <i class="fas fa-times-circle"></i> Non-Refundable (<?php echo $openedGuestsCount; ?> opened)
                                </span>
                            <?php endif; ?>
                        </td>
                        <td data-label="Slip">
                            <?php if (!empty($ref['payment_slip'])): ?>
                                <a href="../../<?php echo htmlspecialchars($ref['payment_slip']); ?>" target="_blank" class="btn btn-sm btn-outline-secondary p-2 fw-semibold" style="font-size:0.75rem; border-radius:8px;">
                                    <i class="fas fa-file-invoice"></i> View Slip
                                </a>
                            <?php else: ?>
                                <span class="text-muted small">No Slip</span>
                            <?php endif; ?>
                        </td>
                        <td data-label="Actions" class="action-cell" style="white-space:nowrap;">
                            <a href="admin_refunds.php?action=approve&uid=<?php echo $ref['user_id']; ?>" 
                               class="btn-action btn-action-approve"
                               onclick="return confirm('Approve refund for <?php echo addslashes($ref['name']); ?>? This will deactivated their account and ask them for bank details.');">
                                <i class="fas fa-check"></i> Approve Refund
                            </a>
                            <a href="admin_refunds.php?action=reject&uid=<?php echo $ref['user_id']; ?>" 
                               class="btn-action btn-action-reject"
                               onclick="return confirm('Reject refund request for <?php echo addslashes($ref['name']); ?>?');">
                                <i class="fas fa-times"></i> Reject
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="text-center py-5 text-muted empty-table-row"><i class="fas fa-inbox"></i>Review කිරීමට කිසිදු Refund ඉල්ලීමක් දැනට නැත.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>


<!-- 2. TABLE: APPROVED REFUNDS - AWAITING BANK PAYOUT -->
<div class="table-card border" style="border-color: rgba(16,185,129,0.2) !important;">
    <div class="table-card-header text-success" style="background: rgba(16,185,129,0.04);">
        <h5><i class="fas fa-university"></i> Phase 2: Pending Bank Payouts (බැංකු විස්තර ලැබී ඇති - ගෙවීම් කිරීමට ඇති ගිණුම්)
            <span class="header-count" id="live-refund-payout-count" style="color:var(--success);"><?php echo count($payoutsList); ?></span>
        </h5>
    </div>

    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Couple Info</th>
                    <th>Submitted Bank Account Details</th>
                    <th>Initial Receipt</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="live-payouts-tbody">
                <?php if (count($payoutsList) > 0): ?>
                    <?php foreach ($payoutsList as $pay): ?>
                    <tr>
                        <td data-label="Couple">
                            <div class="couple-cell">
                                <div class="couple-avatar avatar-success"><?php echo strtoupper(substr($pay['name'], 0, 1)); ?></div>
                                <div>
                                    <div class="fw-bold text-dark" style="font-size:0.9rem;"><?php echo htmlspecialchars($pay['name']); ?></div>
                                    <div class="text-muted small" style="margin-top:2px;"><?php echo htmlspecialchars($pay['email']); ?></div>
                                </div>
                            </div>
                        </td>
                        <td data-label="Bank Details">
                            <div class="bank-box"><i class="fas fa-university me-1 text-success"></i> <?php echo htmlspecialchars($pay['refund_bank_details']); ?></div>
                        </td>
                        <td data-label="Receipt">
                            <?php if (!empty($pay['payment_slip'])): ?>
                                <a href="../../<?php echo htmlspecialchars($pay['payment_slip']); ?>" target="_blank" class="btn btn-sm btn-outline-secondary p-2 fw-semibold" style="font-size:0.75rem; border-radius:8px;">
                                    <i class="fas fa-file-invoice"></i> View Slip
                                </a>
                            <?php else: ?>
                                <span class="text-muted small">No Slip</span>
                            <?php endif; ?>
                        </td>
                        <td data-label="Action">
                            <a href="admin_refunds.php?action=complete&uid=<?php echo $pay['user_id']; ?>&csrf_token=<?php echo $csrf_token; ?>" 
                               class="btn-action-complete"
                               onclick="return confirm('Confirm payout to <?php echo addslashes($pay['name']); ?>? This will send a refund completed receipt email and close this request.');">
                                <i class="fas fa-check-circle"></i> Mark Payout as Completed
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="4" class="text-center py-5 text-muted empty-table-row"><i class="fas fa-check-double"></i>ගෙවීම් කිරීමට ඇති කිසිදු බැංකු ගිණුමක් දැනට ලැබී නැත.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
// =====================================================================
// 🔥 සජීවීව Refund Phase Counts Update කිරීම
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

let csrfTokenJS = "<?php echo $csrf_token; ?>";

function buildRefundRequestRow(ref) {
    const avatarLetter = ref.name.charAt(0).toUpperCase();
    const eligibleBadge = ref.is_eligible
        ? `<span class="badge-eligible"><i class="fas fa-check-circle"></i> Eligible (0 opened)</span>`
        : `<span class="badge-non-eligible" title="This couple has already shared the link with guests."><i class="fas fa-times-circle"></i> Non-Refundable (${ref.opened_count} opened)</span>`;
    const slipCell = ref.payment_slip
        ? `<a href="../../${ref.payment_slip}" target="_blank" class="btn btn-sm btn-outline-secondary p-2 fw-semibold" style="font-size:0.75rem; border-radius:8px;"><i class="fas fa-file-invoice"></i> View Slip</a>`
        : `<span class="text-muted small">No Slip</span>`;

    return `<tr>
        <td data-label="Couple">
            <div class="couple-cell">
                <div class="couple-avatar avatar-danger">${avatarLetter}</div>
                <div>
                    <div class="fw-bold text-dark" style="font-size:0.9rem;">${ref.name}</div>
                    <div class="text-muted small" style="margin-top:2px;">${ref.email}</div>
                </div>
            </div>
        </td>
        <td data-label="Request">
            <div>
                <div class="small fw-bold text-muted"><i class="far fa-clock"></i> ${ref.requested_at}</div>
                <div class="reason-box">"${ref.reason}"</div>
            </div>
        </td>
        <td data-label="Validation">${eligibleBadge}</td>
        <td data-label="Slip">${slipCell}</td>
        <td data-label="Actions" class="action-cell" style="white-space:nowrap;">
            <a href="admin_refunds.php?action=approve&uid=${ref.user_id}&csrf_token=${csrfTokenJS}" class="btn-action btn-action-approve" onclick="return confirm('Approve refund for ${ref.name.replace(/'/g, "\\'")}? This will deactivated their account and ask them for bank details.');"><i class="fas fa-check"></i> Approve Refund</a>
            <a href="admin_refunds.php?action=reject&uid=${ref.user_id}&csrf_token=${csrfTokenJS}" class="btn-action btn-action-reject" onclick="return confirm('Reject refund request for ${ref.name.replace(/'/g, "\\'")}?');"><i class="fas fa-times"></i> Reject</a>
        </td>
    </tr>`;
}

function buildPayoutRow(pay) {
    const avatarLetter = pay.name.charAt(0).toUpperCase();
    const slipCell = pay.payment_slip
        ? `<a href="../../${pay.payment_slip}" target="_blank" class="btn btn-sm btn-outline-secondary p-2 fw-semibold" style="font-size:0.75rem; border-radius:8px;"><i class="fas fa-file-invoice"></i> View Slip</a>`
        : `<span class="text-muted small">No Slip</span>`;

    return `<tr>
        <td data-label="Couple">
            <div class="couple-cell">
                <div class="couple-avatar avatar-success">${avatarLetter}</div>
                <div>
                    <div class="fw-bold text-dark" style="font-size:0.9rem;">${pay.name}</div>
                    <div class="text-muted small" style="margin-top:2px;">${pay.email}</div>
                </div>
            </div>
        </td>
        <td data-label="Bank Details"><div class="bank-box"><i class="fas fa-university me-1 text-success"></i> ${pay.bank_details}</div></td>
        <td data-label="Receipt">${slipCell}</td>
        <td data-label="Action">
            <a href="admin_refunds.php?action=complete&uid=${pay.user_id}&csrf_token=${csrfTokenJS}" class="btn-action-complete" onclick="return confirm('Confirm payout to ${pay.name.replace(/'/g, "\\'")}? This will send a refund completed receipt email and close this request.');"><i class="fas fa-check-circle"></i> Mark Payout as Completed</a>
        </td>
    </tr>`;
}

let lastRequestsSnapshot = null;
let lastPayoutsSnapshot = null;
let refundPollPaused = false;
let refundPollingInterval = 5000;
let refundErrors = 0;
let refundTimer = null;

// Pause live refresh briefly whenever an action link inside either table is pressed,
// so an in-flight 5s poll can never replace the row out from under a click mid-navigation.
document.addEventListener('mousedown', function(e) {
    if (e.target.closest('#live-refund-requests-tbody') || e.target.closest('#live-payouts-tbody')) {
        refundPollPaused = true;
        setTimeout(() => { refundPollPaused = false; }, 3000);
    }
});

function fetchRefundLiveCounts() {
    if (refundPollPaused || document.hidden) return;

    fetch('admin_refunds.php?action=live_counts')
        .then(r => r.json())
        .then(data => {
            refundErrors = 0;
            if (data.error) return;

            // 1. Counters (header badges + top stat cards)
            updateLiveText('live-refund-pending-count', data.pending_count);
            updateLiveText('live-refund-payout-count', data.payout_count);
            updateLiveText('live-refund-stat-pending', data.pending_count);
            updateLiveText('live-refund-stat-payout', data.payout_count);

            // 2. Phase 1 table — Pending Refund Requests
            const requestsSnapshot = JSON.stringify(data.refund_requests);
            if (requestsSnapshot !== lastRequestsSnapshot) {
                lastRequestsSnapshot = requestsSnapshot;

                const reqTbody = document.getElementById('live-refund-requests-tbody');
                if (reqTbody && data.refund_requests) {
                    reqTbody.innerHTML = data.refund_requests.length > 0
                        ? data.refund_requests.map(buildRefundRequestRow).join('')
                        : `<tr><td colspan="5" class="text-center py-5 text-muted empty-table-row"><i class="fas fa-inbox"></i>Review කිරීමට කිසිදු Refund ඉල්ලීමක් දැනට නැත.</td></tr>`;
                }
            }

            // 3. Phase 2 table — Pending Bank Payouts
            const payoutsSnapshot = JSON.stringify(data.payouts);
            if (payoutsSnapshot !== lastPayoutsSnapshot) {
                lastPayoutsSnapshot = payoutsSnapshot;

                const payTbody = document.getElementById('live-payouts-tbody');
                if (payTbody && data.payouts) {
                    payTbody.innerHTML = data.payouts.length > 0
                        ? data.payouts.map(buildPayoutRow).join('')
                        : `<tr><td colspan="4" class="text-center py-5 text-muted empty-table-row"><i class="fas fa-university"></i>Payout කිරීමට කිසිදු ගිණුමක් දැනට නැත.</td></tr>`;
                }
            }

            if (refundPollingInterval > 5000) {
                refundPollingInterval = 5000;
                resetRefundTimer();
            }
        })
        .catch(err => {
            console.error('Error syncing admin refund counts:', err);
            refundErrors++;
            if (refundErrors > 2) {
                refundPollingInterval = Math.min(60000, refundPollingInterval * 2);
                resetRefundTimer();
            }
        });
}

function resetRefundTimer() {
    if (refundTimer) clearInterval(refundTimer);
    refundTimer = setInterval(fetchRefundLiveCounts, refundPollingInterval);
}

document.addEventListener("visibilitychange", () => {
    if (!document.hidden) {
        fetchRefundLiveCounts();
    }
});
resetRefundTimer();
</script>

<?php require '../layouts/footer.php'; ?>
