<?php
session_start();
require '../config/config.php';
require '../config/mailer.php';

// ආරක්ෂාව සඳහා Admin පමණක් ඇතුලත් කර ගැනීම
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$msg = "";

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

// 3. Action: Complete Payout (බැංකුවට මුදල් දමා අවසන් කිරීම)
if (isset($_GET['action']) && $_GET['action'] === 'complete' && isset($_GET['uid'])) {
    $u_id = intval($_GET['uid']);
    
    $stmtUser = $pdo->prepare("SELECT name, email FROM users WHERE id = ?");
    $stmtUser->execute([$u_id]);
    $userInfo = $stmtUser->fetch();

    if ($userInfo) {
        // refund_status එක 'completed' කරයි
        $stmt = $pdo->prepare("UPDATE users SET refund_status = 'completed' WHERE id = ?");
        if ($stmt->execute([$u_id])) {
            if (function_exists('send_refund_completed_mail')) {
                send_refund_completed_mail($userInfo['email'], $userInfo['name']);
            }
            $msg = "<div class='flash flash-success'><i class='fas fa-check-circle'></i> Refund marked as fully completed! Payout finished and couple notified.</div>";
        }
    }
}

// A. Refund ඉල්ලීම් කර ඇති (refund_status = 'pending') අය
$stmtRefundRequests = $pdo->prepare("SELECT users.id as user_id, users.name, users.email, users.payment_slip, users.refund_requested_at, users.refund_reason, weddings.id as wedding_id
                                     FROM users 
                                     JOIN weddings ON users.id = weddings.user_id 
                                     WHERE users.refund_status = 'pending' AND users.refund_requested_at IS NOT NULL 
                                     ORDER BY users.refund_requested_at DESC");
$stmtRefundRequests->execute();
$refundRequests = $stmtRefundRequests->fetchAll();

// B. බැංකු විස්තර එවූ (refund_status = 'details_submitted') අය (Payouts Awaiting Processing)
$stmtPayouts = $pdo->prepare("SELECT users.id as user_id, users.name, users.email, users.refund_bank_details, users.payment_slip
                              FROM users 
                              WHERE users.refund_status = 'details_submitted' 
                              ORDER BY users.id DESC");
$stmtPayouts->execute();
$payoutsList = $stmtPayouts->fetchAll();

require 'layouts/header.php';
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
    }

    .flash { padding: 14px 20px; border-radius: 12px; font-size: 0.88rem; margin-bottom: 24px; display:flex; align-items:center; gap:10px; font-family: 'Inter', sans-serif; font-weight: 500; }
    .flash-success { background: rgba(16,185,129,0.08); border: 1px solid rgba(16,185,129,0.2); color: var(--success); }
    
    .table-card {
        background: white;
        border: 1px solid var(--border-color);
        border-radius: 18px;
        overflow: hidden;
        margin-bottom: 30px;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.01);
    }
    .table-card-header {
        padding: 24px 28px;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .table-card-header h5 { font-size: 1.05rem; font-weight: 800; margin: 0; font-family: 'Inter', sans-serif; letter-spacing: 0.3px; }
    
    /* Table UI Design */
    .admin-table { width: 100%; border-collapse: collapse; }
    .admin-table th {
        padding: 14px 20px;
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: var(--text-muted);
        background: #f8fafc;
        border-bottom: 1px solid var(--border-color);
    }
    .admin-table td {
        padding: 18px 20px;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.88rem;
        color: var(--text-dark);
        vertical-align: middle;
    }
    .admin-table tr:hover td { background: #fafbfc; }

    /* Custom Verification Badges */
    .badge-eligible { display:inline-flex; align-items:center; gap:5px; background:rgba(16,185,129,0.08); color:var(--success); border:1px solid rgba(16,185,129,0.15); border-radius:20px; padding:4px 12px; font-size:0.72rem; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; }
    .badge-non-eligible { display:inline-flex; align-items:center; gap:5px; background:rgba(239,68,68,0.08); color:var(--danger); border:1px solid rgba(239,68,68,0.15); border-radius:20px; padding:4px 12px; font-size:0.72rem; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; }

    /* Custom Content Boxes */
    .reason-box {
        background: #f8fafc;
        border: 1px solid var(--border-color);
        border-radius: 10px;
        padding: 12px 16px;
        font-size: 0.82rem;
        color: #334155;
        margin-top: 8px;
        font-style: italic;
        line-height: 1.5;
        max-width: 320px;
        white-space: normal;
        word-break: break-word;
    }
    .bank-box {
        background: #fffdf5;
        border-left: 4px solid var(--success);
        border-top: 1px solid var(--border-color);
        border-right: 1px solid var(--border-color);
        border-bottom: 1px solid var(--border-color);
        padding: 14px 18px;
        font-size: 0.84rem;
        color: #15803d;
        border-radius: 8px;
        font-family: 'Inter', monospace;
        line-height: 1.6;
        font-weight: 600;
        white-space: pre-line;
    }
    
    /* Interactive Action Buttons */
    .btn-action {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-radius: 10px;
        padding: 8px 14px;
        font-size: 0.76rem;
        font-weight: 700;
        text-decoration: none;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
        border: 1px solid transparent;
    }
    .btn-action-approve { background: rgba(16,185,129,0.08); color: var(--success); border-color: rgba(16,185,129,0.12); }
    .btn-action-approve:hover { background: var(--success); color: white; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(16,185,129,0.25); }
    
    .btn-action-reject { background: rgba(239,68,68,0.06); color: var(--danger); border-color: rgba(239,68,68,0.12); }
    .btn-action-reject:hover { background: var(--danger); color: white; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(239,68,68,0.25); }

    .btn-action-complete {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: linear-gradient(135deg, var(--success) 0%, #047857 100%);
        color: white;
        border: none;
        border-radius: 10px;
        padding: 10px 16px;
        font-size: 0.78rem;
        font-weight: 700;
        text-decoration: none;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
        box-shadow: 0 4px 12px rgba(16,185,129,0.2);
    }
    .btn-action-complete:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 20px rgba(16,185,129,0.35);
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Refund Requests Dashboard (පරිපාලක මුදල් ආපසු ගෙවීම්)</h2>
</div>

<?php if ($msg) echo $msg; ?>

<!-- 1. TABLE: PENDING REFUND REQUESTS REVIEWS -->
<div class="table-card border border-danger" style="border-color: rgba(239,68,68,0.2) !important;">
    <div class="table-card-header text-danger" style="background: rgba(239,68,68,0.03) !important; border-bottom: 1px solid rgba(239,68,68,0.15);">
        <h5><i class="fas fa-exclamation-circle me-1"></i> Phase 1: Pending Refund Reviews (අනුමැතිය අපේක්ෂාවෙන්)</h5>
    </div>

    <div style="overflow-x:auto;">
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
            <tbody>
                <?php if (count($refundRequests) > 0): ?>
                    <?php foreach ($refundRequests as $ref):
                        $stmtCheckGuest = $pdo->prepare("SELECT COUNT(*) as c FROM guests WHERE wedding_id = ? AND (is_opened = 1 OR rsvp_status != 'pending')");
                        $stmtCheckGuest->execute([$ref['wedding_id']]);
                        $openedGuestsCount = $stmtCheckGuest->fetch()['c'] ?? 0;
                        $isEligible = ($openedGuestsCount == 0);
                    ?>
                    <tr>
                        <td>
                            <div class="fw-bold text-dark" style="font-size:0.92rem;"><?php echo htmlspecialchars($ref['name']); ?></div>
                            <div class="text-muted small" style="margin-top:2px;"><?php echo htmlspecialchars($ref['email']); ?></div>
                        </td>
                        <td>
                            <div class="small fw-bold text-muted"><i class="far fa-clock"></i> <?php echo date('d M Y, h:i A', strtotime($ref['refund_requested_at'])); ?></div>
                            <div class="reason-box">"<?php echo htmlspecialchars($ref['refund_reason']); ?>"</div>
                        </td>
                        <td>
                            <?php if ($isEligible): ?>
                                <span class="badge-eligible"><i class="fas fa-check-circle"></i> Eligible (0 opened)</span>
                            <?php else: ?>
                                <span class="badge-non-eligible" title="This couple has already shared the link with guests.">
                                    <i class="fas fa-times-circle"></i> Non-Refundable (<?php echo $openedGuestsCount; ?> opened)
                                </span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($ref['payment_slip'])): ?>
                                <a href="../<?php echo htmlspecialchars($ref['payment_slip']); ?>" target="_blank" class="btn btn-sm btn-outline-secondary p-2 fw-semibold" style="font-size:0.75rem; border-radius:8px;">
                                    <i class="fas fa-file-invoice"></i> View Slip
                                </a>
                            <?php else: ?>
                                <span class="text-muted small">No Slip</span>
                            <?php endif; ?>
                        </td>
                        <td style="white-space:nowrap;">
                            <a href="admin_refunds.php?action=approve&uid=<?php echo $ref['user_id']; ?>" 
                               class="btn-action btn-action-approve"
                               onclick="return confirm('Approve refund for <?php echo addslashes($ref['name']); ?>? This will deactivated their account and ask them for bank details.');">
                                <i class="fas fa-check"></i> Approve Refund
                            </a>
                            <a href="admin_refunds.php?action=reject&uid=<?php echo $ref['user_id']; ?>" 
                               class="btn-action btn-action-reject"
                               style="margin-left:4px;"
                               onclick="return confirm('Reject refund request for <?php echo addslashes($ref['name']); ?>?');">
                                <i class="fas fa-times"></i> Reject
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="text-center py-5 text-muted font-style-italic"><i class="fas fa-inbox d-block mb-2" style="font-size:2rem; opacity:0.3;"></i>Review කිරීමට කිසිදු Refund ඉල්ලීමක් දැනට නැත.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- 2. TABLE: APPROVED REFUNDS - AWAITING BANK PAYOUT -->
<div class="table-card border border-success" style="border-color: rgba(16,185,129,0.2) !important;">
    <div class="table-card-header text-success" style="background: rgba(16,185,129,0.03) !important; border-bottom: 1px solid rgba(16,185,129,0.15);">
        <h5><i class="fas fa-university me-1"></i> Phase 2: Pending Bank Payouts (බැංකු විස්තර ලැබී ඇති - ගෙවීම් කිරීමට ඇති ගිණුම්)</h5>
    </div>

    <div style="overflow-x:auto;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Couple Info</th>
                    <th>Submitted Bank Account Details</th>
                    <th>Initial Receipt</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($payoutsList) > 0): ?>
                    <?php foreach ($payoutsList as $pay): ?>
                    <tr>
                        <td>
                            <div class="fw-bold text-dark" style="font-size:0.92rem;"><?php echo htmlspecialchars($pay['name']); ?></div>
                            <div class="text-muted small" style="margin-top:2px;"><?php echo htmlspecialchars($pay['email']); ?></div>
                        </td>
                        <td>
                            <div class="bank-box"><i class="fas fa-university me-1 text-success"></i> <?php echo htmlspecialchars($pay['refund_bank_details']); ?></div>
                        </td>
                        <td>
                            <?php if (!empty($pay['payment_slip'])): ?>
                                <a href="../<?php echo htmlspecialchars($pay['payment_slip']); ?>" target="_blank" class="btn btn-sm btn-outline-secondary p-2 fw-semibold" style="font-size:0.75rem; border-radius:8px;">
                                    <i class="fas fa-file-invoice"></i> View Slip
                                </a>
                            <?php else: ?>
                                <span class="text-muted small">No Slip</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="admin_refunds.php?action=complete&uid=<?php echo $pay['user_id']; ?>" 
                               class="btn-action-complete"
                               onclick="return confirm('Confirm payout of Rs. 1000 to <?php echo addslashes($pay['name']); ?>? This will send a refund completed receipt email and close this request.');">
                                <i class="fas fa-check-circle"></i> Mark Payout as Completed
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="4" class="text-center py-5 text-muted font-style-italic"><i class="fas fa-check-double d-block mb-2" style="font-size:2rem; opacity:0.3;"></i>ගෙවීම් කිරීමට ඇති කිසිදු බැංකු ගිණුමක් දැනට ලැබී නැත.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require 'layouts/footer.php'; ?>