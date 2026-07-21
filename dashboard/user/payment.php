<?php
session_start();
require '../../config/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'couple') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$msg = "";

// Pick up a flash message left by a previous POST-then-redirect
if (isset($_SESSION['flash_msg'])) {
    $msg = $_SESSION['flash_msg'];
    unset($_SESSION['flash_msg']);
}

// .env file එකෙන් PayPal Client ID එක ආරක්ෂිතව කියවා ගැනීම (Root folder එකේ ඇති නිසා)
$envPath = __DIR__ . '/../.env';
$paypal_client_id = '';
if (file_exists($envPath)) {
    $envVars = @parse_ini_file($envPath);
    if ($envVars) {
        $paypal_client_id = $envVars['PAYPAL_CLIENT_ID'] ?? '';
    }
}

// 1. Handle Dismiss Refund
if (isset($_GET['dismiss_refund'])) {
    $pdo->prepare("UPDATE users SET refund_status = 'none', refund_reason = NULL, refund_bank_details = NULL WHERE id = ?")
        ->execute([$user_id]);
    header("Location: payment.php");
    exit();
}

// 2. Handle AJAX Refund Request DB Save
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] === 'request_refund') {
    header('Content-Type: application/json');
    $reason = trim($_POST['reason']);
    $stmt = $pdo->prepare("UPDATE users SET refund_status = 'pending', refund_requested_at = NOW(), refund_reason = ? WHERE id = ?");
    if ($stmt->execute([$reason, $user_id])) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
    exit();
}

// 3. Handle AJAX Bank Details Submission (Refund Approved වලදී)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_bank_details') {
    header('Content-Type: application/json');
    $bank_name = trim($_POST['bank_name']);
    $acc_name  = trim($_POST['acc_name']);
    $acc_num   = trim($_POST['acc_num']);
    $branch    = trim($_POST['branch']);
    
    $bank_info = "Bank Name: " . $bank_name . " | Holder Name: " . $acc_name . " | Acc No: " . $acc_num . " | Branch: " . $branch;
    
    $stmt = $pdo->prepare("UPDATE users SET refund_status = 'details_submitted', refund_bank_details = ? WHERE id = ?");
    if ($stmt->execute([$bank_info, $user_id])) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
    exit();
}

// Helper: PHP upload error codes -> human readable messages
function upload_error_message($errorCode) {
    switch ($errorCode) {
        case UPLOAD_ERR_OK:
            return null;
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            return "The file is too large. Please upload a smaller image or PDF.";
        case UPLOAD_ERR_PARTIAL:
            return "The file was only partially uploaded. Please check your connection and try again.";
        case UPLOAD_ERR_NO_FILE:
            return "No file was selected. Please choose a file before submitting.";
        case UPLOAD_ERR_NO_TMP_DIR:
            return "Server is missing a temporary folder for uploads. Please contact support.";
        case UPLOAD_ERR_CANT_WRITE:
            return "Server failed to write the file to disk. Please contact support.";
        case UPLOAD_ERR_EXTENSION:
            return "A server extension blocked this upload. Please contact support.";
        default:
            return "Upload failed due to an unknown error (code {$errorCode}). Please contact support.";
    }
}

// 4. Handle Bank Slip Submission WITH chosen Plan configuration (මුල්වරට Activate කරද්දී)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['bank_slip'])) {
    $file = $_FILES['bank_slip'];
    $selected_package = $_POST['package'] ?? 'basic';
    $add_gallery = isset($_POST['add_gallery']) ? 1 : 0;
    
    if ($selected_package === 'premium') {
        $add_gallery = 1;
    }

    $upload_error = upload_error_message($file['error']);

    if ($upload_error) {
        $_SESSION['flash_msg'] = "<div class='flash flash-error'><i class='fas fa-times-circle'></i> " . htmlspecialchars($upload_error) . "</div>";
    } else {
        $target_dir = "../uploads/slips/";
        if (!file_exists($target_dir)) {
            @mkdir($target_dir, 0777, true);
        }

        if (!is_dir($target_dir) || !is_writable($target_dir)) {
            $_SESSION['flash_msg'] = "<div class='flash flash-error'><i class='fas fa-times-circle'></i> Upload failed. Please try again.</div>";
        } else {
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'webp', 'pdf'];

            if (in_array($ext, $allowed)) {
                $new_filename = "slip_" . $user_id . "_" . time() . "." . $ext;
                $target_file  = $target_dir . $new_filename;

                if (move_uploaded_file($file['tmp_name'], $target_file)) {
                    $db_path = "uploads/slips/" . $new_filename;

                    $stmt = $pdo->prepare("UPDATE users SET payment_slip = ?, status = 'pending', package = ?, has_guest_gallery = ?, refund_status = 'none', refund_requested_at = NULL WHERE id = ?");
                    if ($stmt->execute([$db_path, $selected_package, $add_gallery, $user_id])) {
                        $_SESSION['flash_msg'] = "<div class='flash flash-success'><i class='fas fa-check-circle'></i> Bank slip uploaded! We will review and activate your <strong>" . ucfirst($selected_package) . " plan</strong> soon.</div>";
                    }
                } else {
                    $_SESSION['flash_msg'] = "<div class='flash flash-error'><i class='fas fa-times-circle'></i> Upload failed. Please try again.</div>";
                }
            } else {
                $_SESSION['flash_msg'] = "<div class='flash flash-error'><i class='fas fa-times-circle'></i> Please upload a JPG, PNG, WEBP, or PDF file.</div>";
            }
        }
    }

    header("Location: payment.php");
    exit();
}

// 5. Handle UPGRADE Slip Submission (දැනටමත් සක්‍රීය ගිණුමක Upgrade slip එකක් එවද්දී)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['upgrade_slip_file'])) {
    $file = $_FILES['upgrade_slip_file'];
    $target_str = $_POST['upgrade_package_target'] ?? 'standard|0';

    $upload_error = upload_error_message($file['error']);

    if ($upload_error) {
        $_SESSION['flash_msg'] = "<div class='flash flash-error'><i class='fas fa-times-circle'></i> " . htmlspecialchars($upload_error) . "</div>";
    } else {
        $target_dir = "../uploads/slips/";
        if (!file_exists($target_dir)) {
            @mkdir($target_dir, 0777, true);
        }

        if (!is_dir($target_dir) || !is_writable($target_dir)) {
            $_SESSION['flash_msg'] = "<div class='flash flash-error'><i class='fas fa-times-circle'></i> Upload failed. Please try again.</div>";
        } else {
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'webp', 'pdf'];

            if (in_array($ext, $allowed)) {
                $new_filename = "upgrade_slip_" . $user_id . "_" . time() . "." . $ext;
                $target_file  = $target_dir . $new_filename;

                if (move_uploaded_file($file['tmp_name'], $target_file)) {
                    $db_path = "uploads/slips/" . $new_filename;

                    $stmt = $pdo->prepare("UPDATE users SET upgrade_slip = ?, pending_upgrade_plan = ? WHERE id = ?");
                    if ($stmt->execute([$db_path, $target_str, $user_id])) {
                        $_SESSION['flash_msg'] = "<div class='flash flash-success'><i class='fas fa-check-circle'></i> Upgrade slip submitted! We will process your upgrade shortly. Your current invitation remains LIVE.</div>";
                    }
                } else {
                    $_SESSION['flash_msg'] = "<div class='flash flash-error'><i class='fas fa-times-circle'></i> Upload failed. Please try again.</div>";
                }
            } else {
                $_SESSION['flash_msg'] = "<div class='flash flash-error'><i class='fas fa-times-circle'></i> Please upload a JPG, PNG, WEBP, or PDF file.</div>";
            }
        }
    }

    header("Location: payment.php");
    exit();
}

// 6. AJAX: සජීවීව (Live) Account Status Check කිරීම
if (isset($_GET['action']) && $_GET['action'] === 'status_check') {
    header('Content-Type: application/json');
    $stmtLiveStatus = $pdo->prepare("SELECT status, refund_status, package, has_guest_gallery, pending_upgrade_plan FROM users WHERE id = ?");
    $stmtLiveStatus->execute([$user_id]);
    $live = $stmtLiveStatus->fetch();
    echo json_encode([
        'fingerprint' => md5(implode('|', [
            $live['status'],
            $live['refund_status'],
            $live['package'],
            $live['has_guest_gallery'],
            !empty($live['pending_upgrade_plan']) ? '1' : '0',
        ]))
    ]);
    exit();
}

// Get user status & plan details
$stmtGetSlip = $pdo->prepare("SELECT name, email, payment_slip, status, refund_status, package, has_guest_gallery, refund_requested_at, upgrade_slip, pending_upgrade_plan FROM users WHERE id = ?");
$stmtGetSlip->execute([$user_id]);
$user_data = $stmtGetSlip->fetch();

$couple_name = !empty($user_data['name']) ? $user_data['name'] : ($_SESSION['user_name'] ?? 'Couple');
$couple_email = $user_data['email'] ?? '';

$initial_status_fingerprint = md5(implode('|', [
    $user_data['status'],
    $user_data['refund_status'],
    $user_data['package'],
    $user_data['has_guest_gallery'],
    !empty($user_data['pending_upgrade_plan']) ? '1' : '0',
]));

// වර්තමාන පැකේජයේ මුළු වටිනාකම හැදීම (Dynamic calculations)
$current_val = 2500;
if ($user_data['package'] === 'standard') $current_val = 5000;
if ($user_data['package'] === 'premium') $current_val = 10000;
if ($user_data['has_guest_gallery'] == 1 && $user_data['package'] !== 'premium') $current_val += 2000;

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$domain = $protocol . $_SERVER['HTTP_HOST'] . dirname(dirname(dirname($_SERVER['PHP_SELF'])));

require '../layouts/header.php';
?>

<!-- 💡 නිවැරදි කිරීම: credit/card බ්ලොක් කර තිබූ කේතය ඉවත් කර, auto client-id එක ලබාගෙන ඇත -->
<script src="https://www.paypal.com/sdk/js?client-id=<?php echo htmlspecialchars($paypal_client_id); ?>&currency=USD"></script>

<style>
    .flash { padding: 13px 18px; border-radius: 12px; font-size: 0.87rem; margin-bottom: 20px; display:flex; align-items:center; gap:10px; }
    .flash-success { background: rgba(34,197,94,0.1); border:1px solid rgba(34,197,94,0.25); color: #16a34a; }
    .flash-error   { background: rgba(239,68,68,0.1); border:1px solid rgba(239,68,68,0.25); color: #dc2626; }

    .payment-wrapper { max-width: 780px; margin: 0 auto; }
    .active-banner { background: linear-gradient(135deg, rgba(34,197,94,0.08), rgba(34,197,94,0.04)); border: 1px solid rgba(34,197,94,0.25); border-radius: 20px; padding: 40px; text-align: center; }
    .active-icon { width: 72px; height: 72px; border-radius: 50%; background: rgba(34,197,94,0.12); border: 2px solid rgba(34,197,94,0.25); display: flex; align-items: center; justify-content: center; font-size: 1.8rem; color: #22c55e; margin: 0 auto 20px; }
    .active-banner h3 { font-size: 1.4rem; font-weight: 700; color: #1a1a2e; margin-bottom: 8px; }
    .active-banner p { color: #6b7280; font-size: 0.9rem; }

    .btn-dashboard-go { display: inline-flex; align-items: center; gap: 8px; background: linear-gradient(135deg, #c9a96e, #a07840); color: #0f0f1a; padding: 12px 24px; border-radius: 12px; text-decoration: none; font-weight: 700; font-size: 0.88rem; transition: all 0.2s; border: none; }
    .btn-dashboard-go:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(201,169,110,0.3); color: #0f0f1a; }
    .btn-refund-toggle { display: inline-flex; align-items: center; gap: 8px; background: transparent; border: 1px solid #fee2e2; color: #dc2626; padding: 12px 24px; border-radius: 12px; font-size: 0.88rem; font-weight: 600; cursor: pointer; transition: all 0.2s; }
    .btn-refund-toggle:hover { background: #fee2e2; }
    .btn-submit-refund { width: 100%; background: linear-gradient(135deg, #ef4444, #991b1b); color: white; border: none; border-radius: 12px; padding: 14px; font-family: 'Inter', sans-serif; font-size: 0.9rem; font-weight: 700; letter-spacing: 0.5px; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; justify-content: center; gap: 8px; }
    .btn-submit-refund:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(239,68,68,0.25); }

    /* Visual Package Selection Cards */
    .package-selector-title { font-size: 1.1rem; font-weight: 700; color: #1a1a2e; margin-bottom: 18px; text-align: left; }
    .package-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px; }
    
    .package-card { background: white; border: 2px solid #e8ecf0; border-radius: 16px; padding: 24px 20px; text-align: center; cursor: pointer; position: relative; transition: all 0.3s ease; }
    .package-card:hover { border-color: #c9a96e; transform: translateY(-2px); box-shadow: 0 8px 24px rgba(201,169,110,0.06); }
    .package-card.selected { border-color: #c9a96e; background: #fffefb; box-shadow: 0 8px 30px rgba(201,169,110,0.12); }
    
    .pop-badge { position: absolute; top: -12px; left: 50%; transform: translateX(-50%); background: linear-gradient(135deg, #c9a96e, #a07840); color: #0f0f1a; font-size: 0.65rem; font-weight: 800; padding: 3px 12px; border-radius: 20px; text-transform: uppercase; letter-spacing: 0.8px; }
    .pkg-name { font-size: 1.1rem; font-weight: 700; color: #1a1a2e; margin-bottom: 4px; }
    .pkg-desc { font-size: 0.78rem; color: #9ea3b0; margin-bottom: 12px; }
    .pkg-price { font-size: 1.6rem; font-weight: 800; color: #c9a96e; line-height: 1; margin-bottom: 16px; }
    .pkg-price span { font-size: 0.8rem; font-weight: 500; color: #9ea3b0; }
    .pkg-features { list-style: none; text-align: left; display: flex; flex-direction: column; gap: 8px; }
    .pkg-features li { font-size: 0.78rem; color: #4a5568; padding: 5px 0; display: flex; align-items: center; gap: 8px; }
    .pkg-features li i { color: #22c55e; font-size: 0.8rem; }

    /* Gallery Add-on Box */
    .addon-card { background: #fffdf9; border: 1px dashed rgba(201,169,110,0.4); border-radius: 12px; padding: 16px 20px; margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; gap: 16px; transition: opacity 0.3s; }

    /* Standard elements */
    .steps-card { background: white; border: 1px solid #e8ecf0; border-radius: 20px; padding: 32px; margin-bottom: 20px; }
    .steps-card h4 { font-size: 1.1rem; font-weight: 700; color: #1a1a2e; margin-bottom: 24px; }
    .bank-details { background: #f8fafc; border: 1px solid #e8ecf0; border-radius: 14px; padding: 20px 24px; margin-bottom: 24px; }
    .bank-details-title { font-size: 0.72rem; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; color: #9ea3b0; margin-bottom: 14px; }
    .bank-row { display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px solid #f1f5f9; font-size: 0.87rem; }
    .bank-row:last-child { border-bottom: none; }
    .bank-row-label { color: #9ea3b0; }
    .bank-row-value { font-weight: 700; color: #1a1a2e; }
    .bank-row-value.amount { color: #c9a96e; font-size: 1.25rem; font-weight: 800; }

    .upload-card { background: white; border: 1px solid #e8ecf0; border-radius: 20px; padding: 28px; }
    .upload-card h5 { font-size: 0.95rem; font-weight: 700; color: #1a1a2e; margin-bottom: 16px; }
    .drop-zone { border: 2px dashed #e8ecf0; border-radius: 14px; padding: 36px 20px; text-align: center; cursor: pointer; transition: all 0.3s; background: #fafbfc; position: relative; margin-bottom: 14px; }
    .drop-zone:hover { border-color: #c9a96e; background: rgba(201,169,110,0.03); }
    .drop-zone input[type="file"] { position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%; }
    .drop-zone-icon { font-size: 2rem; color: rgba(201,169,110,0.4); margin-bottom: 10px; }
    .drop-zone-text { font-size: 0.85rem; color: #9ea3b0; line-height: 1.6; }
    .drop-zone-text strong { color: #c9a96e; }
    .selected-file { display: none; background: rgba(201,169,110,0.08); border: 1px solid rgba(201,169,110,0.2); border-radius: 10px; padding: 10px 14px; font-size: 0.83rem; color: #a07840; margin-bottom: 14px; }
    .btn-upload { width: 100%; background: linear-gradient(135deg, #c9a96e, #a07840); color: #0f0f1a; border: none; border-radius: 12px; padding: 13px; font-family: 'Inter', sans-serif; font-size: 0.9rem; font-weight: 700; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; justify-content: center; gap: 8px; }
    .btn-upload:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(201,169,110,0.3); }

    .slip-submitted { background: rgba(245,158,11,0.06); border: 1px solid rgba(245,158,11,0.2); border-radius: 14px; padding: 20px; text-align: center; margin-bottom: 16px; }
    .slip-submitted i { font-size: 2rem; color: #d97706; margin-bottom: 10px; display: block; }
    .slip-submitted p { font-size: 0.87rem; color: #6b7280; margin: 0; }
    .slip-preview { max-width: 160px; border-radius: 10px; border: 1px solid #e8ecf0; margin: 12px auto 0; display: block; box-shadow: 0 4px 12px rgba(0,0,0,0.06); }

    .wa-note { background: rgba(37,211,102,0.06); border: 1px solid rgba(37,211,102,0.2); border-radius: 12px; padding: 14px 18px; font-size: 0.83rem; color: #16a34a; display: flex; align-items: center; gap: 10px; margin-top: 14px; }
    .wa-note i { font-size: 1.2rem; color: #25d366; }
    .wa-button { display: inline-flex; align-items: center; justify-content: center; gap: 8px; margin-top: 12px; width: 100%; padding: 12px 14px; border-radius: 10px; background: #25d366; color: white; text-decoration: none; font-weight: 700; font-size: 0.9rem; }
    .wa-button:hover { background: #1ebe5d; color: white; }

    /* PayPal Instant-Pay redesigned button */
    .paypal-divider { display: flex; align-items: center; gap: 12px; margin: 20px 0 16px; color: #9ea3b0; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; }
    .paypal-divider::before, .paypal-divider::after { content: ''; flex: 1; height: 1px; background: #e8ecf0; }
    .btn-paypal { display: flex; align-items: center; justify-content: center; gap: 10px; width: 100%; background: linear-gradient(135deg, #0070ba, #003087); color: white; border: none; border-radius: 10px; padding: 13px 14px; font-family: 'Inter', sans-serif; font-size: 0.9rem; font-weight: 700; cursor: pointer; transition: all 0.2s; }
    .btn-paypal:hover { transform: translateY(-1px); box-shadow: 0 8px 20px rgba(0,112,186,0.3); color: white; }
    .btn-paypal:disabled { opacity: 0.65; cursor: not-allowed; transform: none; box-shadow: none; }
    .paypal-note { font-size: 0.74rem; color: #9ea3b0; text-align: center; margin-top: 8px; }
    .pp-capture-overlay { position: fixed; inset: 0; background: rgba(15,15,26,0.92); backdrop-filter: blur(6px); z-index: 9999; display: flex; align-items: center; justify-content: center; color: white; font-family: 'Inter', sans-serif; text-align: center; padding: 24px; }
    .pp-capture-overlay .pp-icon-ok { font-size: 2.6rem; color: #22c55e; }
    .pp-capture-overlay .pp-icon-err { font-size: 2.6rem; color: #ef4444; }
    .pp-capture-overlay p { margin-top: 14px; font-size: 0.92rem; color: #e8e4dc; }
    .pp-capture-overlay .btn-pp-close { margin-top: 16px; background: #c9a96e; color: #0f0f1a; border: none; border-radius: 10px; padding: 10px 22px; font-weight: 700; cursor: pointer; }

    /* Refund/Upgrade Styles */
    .form-control-custom { width: 100%; border: 1px solid #e8ecf0; border-radius: 10px; padding: 10px 14px; font-size: 0.88rem; outline: none; background: #fafbfc; transition: border-color 0.2s; }
    .form-control-custom:focus { border-color: #ef4444; background: #fffdfd; }
    .btn-dismiss-refund { display: inline-block; margin-top: 15px; border: 1px solid #e8ecf0; border-radius: 8px; background: white; color: #4a5568; padding: 6px 14px; font-size: 0.78rem; font-weight: 600; text-decoration: none; transition: all 0.2s; }
    .btn-dismiss-refund:hover { background: #f8fafc; color: #1a1a2e; }
    .btn-submit-bank { width: 100%; background: linear-gradient(135deg, #16a34a, #15803d); color: white; border: none; border-radius: 12px; padding: 13px; font-size: 0.9rem; font-weight: 700; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; justify-content: center; gap: 8px; }
    .btn-submit-bank:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(34,197,94,0.2); }

    /* Upgrade styles */
    .upgrade-box { background:#fffdf5; border:1px solid #c9a96e; border-radius:14px; padding:18px; margin-bottom:20px; text-align:left; }
    .btn-upgrade-toggle { display:inline-flex; align-items:center; gap:8px; background:linear-gradient(135deg, #1a1a2e, #2d2d50); color:#c9a96e; padding:12px 24px; border-radius:12px; font-size:0.88rem; font-weight:700; border:none; cursor:pointer; }
    .btn-upgrade-toggle:hover { transform:translateY(-1px); box-shadow:0 6px 20px rgba(26,26,46,0.25); }
</style>

<div class="payment-wrapper">
    <?php if ($msg) echo $msg; ?>

    <!-- 1. NOTIFICATION: REFUND APPROVED ➡️ ASKING FOR BANK DETAILS -->
    <?php if ($user_data['status'] === 'pending' && $user_data['refund_status'] === 'approved'): ?>
        <div class="card card-custom bg-white p-4 text-start mb-4 border-success">
            <div class="active-icon" style="color:#22c55e; background:rgba(34,197,94,0.08); border-color:rgba(34,197,94,0.2); margin:0 auto 20px;"><i class="fas fa-check-circle"></i></div>
            <h4 class="fw-bold text-success text-center">Refund Approved! 💸</h4>
            <!-- සැබෑම ගෙවූ මුදල පෙන්වයි -->
            <p class="text-muted small text-center">We have approved your refund request. Please provide your bank account details below so we can process your <strong>Rs. <?php echo number_format($current_val); ?></strong> transfer reversal instantly.</p>
            
            <!-- බැංකු විස්තර ලබාගන්නා Form එක -->
            <form id="bankDetailsForm" class="mt-4">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold small text-uppercase text-muted" style="font-size:0.7rem; letter-spacing:0.8px;">Bank Name</label>
                        <input type="text" id="bank_name" class="form-control-custom" placeholder="e.g. Sampath Bank, BOC" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold small text-uppercase text-muted" style="font-size:0.7rem; letter-spacing:0.8px;">Account Holder's Name</label>
                        <input type="text" id="acc_name" class="form-control-custom" placeholder="As shown on passbook" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold small text-uppercase text-muted" style="font-size:0.7rem; letter-spacing:0.8px;">Account Number</label>
                        <input type="text" id="acc_num" class="form-control-custom" placeholder="e.g. 1234567890" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold small text-uppercase text-muted" style="font-size:0.7rem; letter-spacing:0.8px;">Branch</label>
                        <input type="text" id="branch" class="form-control-custom" placeholder="e.g. Colombo, Kandy" required>
                    </div>
                </div>
                <button type="submit" class="btn-submit-bank mt-2">
                    <i class="fas fa-university"></i> Submit Bank Details
                </button>
            </form>
        </div>
    <?php endif; ?>

    <!-- 2. NOTIFICATION: BANK DETAILS SUBMITTED ➡️ PROCESSING PAYOUT -->
    <?php if ($user_data['status'] === 'pending' && $user_data['refund_status'] === 'details_submitted'): ?>
        <div class="card card-custom bg-white p-4 text-center mb-4 border-warning">
            <div class="active-icon" style="color:#f59e0b; background:rgba(245,158,11,0.08); border-color:rgba(245,158,11,0.2);"><i class="fas fa-university"></i></div>
            <h4 class="fw-bold text-warning">Payout Processing ⏳</h4>
            <!-- සැබෑම ගෙවූ මුදල පෙන්වයි -->
            <p class="text-muted small">Your bank account details have been securely logged. Our finance team is currently transferring your Rs. <?php echo number_format($current_val); ?> refund. You will receive an email confirmation once completed.</p>
            <div class="spinner-border text-warning my-2" role="status"><span class="visually-hidden">Loading...</span></div>
        </div>
    <?php endif; ?>

    <!-- 3. NOTIFICATION: REFUND COMPLETED -->
    <?php if ($user_data['status'] === 'pending' && $user_data['refund_status'] === 'completed'): ?>
        <div class="card card-custom bg-white p-4 text-center mb-4 border-success">
            <div class="active-icon" style="color:#16a34a; background:rgba(34,197,94,0.08); border-color:rgba(34,197,94,0.2);"><i class="fas fa-receipt"></i></div>
            <h4 class="fw-bold text-success">Refund Completed! 💸</h4>
            <!-- සැබෑම ගෙවූ මුදල පෙන්වයි -->
            <p class="text-muted small">Your Rs. <?php echo number_format($current_val); ?> refund has been successfully transferred to your bank account. We appreciate your journey with us.</p>
            <a href="payment.php?dismiss_refund=1" class="btn-dismiss-refund">Okay, Close Banner</a>
        </div>
    <?php endif; ?>

    <!-- 4. NOTIFICATION: REFUND REJECTED (status 'active' & refund_status 'rejected') -->
    <?php if ($user_data['status'] === 'active' && $user_data['refund_status'] === 'rejected'): ?>
        <div class="card card-custom bg-white p-4 text-center mb-4" style="border-color:#fee2e2;">
            <div class="active-icon" style="color:#dc2626; background:rgba(239,68,68,0.08); border-color:rgba(239,68,68,0.2);"><i class="fas fa-times-circle"></i></div>
            <h4 class="fw-bold text-danger">Refund Request Declined ⚠️</h4>
            <p class="text-muted small">We are unable to approve your refund request because your invitation link has already been opened by guests or RSVP responses have been logged on your platform.</p>
            <a href="payment.php?dismiss_refund=1" class="btn-dismiss-refund">Okay, Dismiss</a>
        </div>
    <?php endif; ?>

    <!-- 5. ACTUAL PAYMENT / ACTIVE AREA -->
    <?php if ($user_data['status'] === 'active'): ?>
    <!-- Active Banner -->
    <div class="active-banner mb-4">
        <div class="active-icon"><i class="fas fa-check"></i></div>
        <h3>Your Account is Active! 🎉</h3>
        <p>You are currently on the <strong><?php echo ucfirst($user_data['package']); ?> Plan</strong> 
        <?php echo $user_data['has_guest_gallery'] ? '(with Guest Gallery Unlocked)' : ''; ?>.</p>
        
        <div style="display:flex; justify-content:center; gap:12px; flex-wrap:wrap; margin-top:20px;">
            <a href="index.php" class="btn-dashboard-go">
                <i class="fas fa-tachometer-alt"></i> Go to Dashboard
            </a>
            
            <!-- Upgrade Button: Premium නොවන අයට හෝ Add-on නොගත් අයට පෙන්වයි -->
            <?php if ($user_data['package'] !== 'premium' || $user_data['has_guest_gallery'] == 0): ?>
                <button class="btn-upgrade-toggle" onclick="toggleUpgradeForm()">
                    <i class="fas fa-arrow-circle-up" style="color:#c9a96e;"></i> Upgrade Plan / Add-ons
                </button>
            <?php endif; ?>

            <?php if ($user_data['refund_status'] === 'none'): ?>
                <!-- Refund Request Toggle -->
                <button class="btn-refund-toggle" onclick="toggleRefundForm()">
                    <i class="fas fa-undo-alt"></i> Request Refund
                </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- 6. PENDING REFUND REVIEW BANNER -->
    <?php if ($user_data['refund_status'] === 'pending'): ?>
        <div class="alert alert-warning text-center rounded-3 p-4 mb-4">
            <h5 class="fw-bold"><i class="fas fa-clock"></i> Refund Request Pending</h5>
            <p class="mb-0 small">You have submitted a refund request. Our team is manually reviewing your request and checking guest opening status. Please wait.</p>
        </div>
    <?php endif; ?>

    <!-- 7. PENDING UPGRADE BANNER -->
    <?php if (!empty($user_data['pending_upgrade_plan'])): ?>
        <div class="alert alert-info text-center rounded-3 p-4 mb-4">
            <h5 class="fw-bold" style="color:#1a1a2e;"><i class="fas fa-clock"></i> Upgrade Request Under Review</h5>
            <p class="mb-0 small">You have successfully uploaded a bank slip for a package upgrade. Our team is verifying the slip. **Your wedding invitation remains 100% active and live during this time!**</p>
        </div>
    <?php endif; ?>

    <!-- DYNAMIC UPGRADE FORM CARD (Active State එකේදී පමණක් පෙනේ) -->
    <?php if ($user_data['package'] !== 'premium' || $user_data['has_guest_gallery'] == 0): ?>
    <div class="card card-custom bg-white p-4 mb-4" id="upgrade-form-card" style="display: none; animation: slideDown 0.3s ease-out;">
        <h5 class="mb-3 text-start" style="color:#1a1a2e;"><i class="fas fa-arrow-circle-up me-2" style="color:#c9a96e;"></i> Upgrade Your Package</h5>
        <p class="text-muted small text-start mb-4">ඔබට අවශ්‍ය නව පැකේජය තෝරා, පහත දැක්වෙන <strong>මිල වෙනස පමණක් (Upgrade Balance)</strong> අපගේ බැංකු ගිණුමට තැන්පත් කර රිසිට්පත මෙතැනින් යොමු කරන්න.</p>
        
        <form method="POST" action="payment.php" enctype="multipart/form-data">
            <!-- Hidden inputs used for calculation -->
            <input type="hidden" id="current_val" value="<?php echo $current_val; ?>">
            
            <div class="row text-start">
                <div class="col-12 mb-3">
                    <label class="form-label fw-bold small text-muted text-uppercase" style="font-size:0.7rem; letter-spacing:0.8px;">Choose Upgrade Target (පැකේජය තෝරන්න)</label>
                    <select name="upgrade_package_target" id="upgrade_package_target" class="form-control-custom" onchange="calculateUpgradePrice()" required>
                        <?php 
                        $pkg = $user_data['package'];
                        $gallery = intval($user_data['has_guest_gallery']);
                        ?>
                        <?php if ($pkg === 'basic' && $gallery === 0): ?>
                            <option value="basic|1" data-price="4500">Buy Guest Gallery Add-on Only (+ Rs. 2,000)</option>
                            <option value="standard|0" data-price="5000">Upgrade to Standard Plan (Max 300 Guests) (+ Rs. 2,500)</option>
                            <option value="standard|1" data-price="7000">Upgrade to Standard Plan + Guest Gallery (+ Rs. 4,500)</option>
                            <option value="premium|1" data-price="10000">Upgrade to Premium Plan (Unlimited Guests + Gallery) (+ Rs. 7,500)</option>
                        <?php elseif ($pkg === 'basic' && $gallery === 1): ?>
                            <option value="standard|1" data-price="7000">Upgrade to Standard Plan (Keep Gallery) (+ Rs. 2,500)</option>
                            <option value="premium|1" data-price="10000">Upgrade to Premium Plan (Unlimited) (+ Rs. 5,500)</option>
                        <?php elseif ($pkg === 'standard' && $gallery === 0): ?>
                            <option value="standard|1" data-price="7000">Buy Guest Gallery Add-on Only (+ Rs. 2,000)</option>
                            <option value="premium|1" data-price="10000">Upgrade to Premium Plan (Unlimited + Gallery) (+ Rs. 5,000)</option>
                        <?php elseif ($pkg === 'standard' && $gallery === 1): ?>
                            <option value="premium|1" data-price="10000">Upgrade to Premium Plan (Unlimited) (+ Rs. 3,000)</option>
                        <?php endif; ?>
                    </select>
                </div>
            </div>

            <!-- Calculated Balance Box -->
            <div class="bank-details mt-2">
                <div class="bank-details-title">Upgrade Payout Summary</div>
                <div class="bank-row">
                    <span class="bank-row-label">Target Package Value</span>
                    <span class="bank-row-value" id="target-value-display">Rs. 5,000</span>
                </div>
                <div class="bank-row">
                    <span class="bank-row-label">Current Plan Value Credit</span>
                    <span class="bank-row-value text-muted">- Rs. <?php echo number_format($current_val); ?></span>
                </div>
                <div class="bank-row">
                    <span class="bank-row-label fw-bold" style="color:#1a1a2e;">Total Balance Due (ගෙවිය යුතු මිල වෙනස)</span>
                    <span class="bank-row-value amount text-success" id="upgrade-amount-display" style="font-size:1.35rem;">Rs. 2,500</span>
                </div>
            </div>

            <!-- Upgrade Slip Upload -->
            <?php if (empty($user_data['pending_upgrade_plan'])): ?>
            <div class="mb-3 text-start">
                <label class="form-label fw-bold small text-muted text-uppercase" style="font-size:0.7rem; letter-spacing:0.8px;">Upload Upgrade Receipt / Bank Slip</label>
                <input type="file" name="upgrade_slip_file" class="form-control-custom" required accept="image/*,.pdf">
            </div>
            <button type="submit" class="btn-dashboard-go w-100 py-3 text-center d-flex align-items-center justify-content-center" style="background: linear-gradient(135deg,#c9a96e,#a07840); color:#0f0f1a; font-weight:bold;">
                <i class="fas fa-upload"></i> Submit Upgrade Receipt
            </button>
            <?php else: ?>
                <div class="alert alert-secondary text-center small py-2"><i class="fas fa-lock"></i> Upgrade submission is locked while another request is under review.</div>
            <?php endif; ?>
        </form>
    </div>
    <?php endif; ?>

    <!-- Hidden Refund Request Form -->
    <div class="card card-custom bg-white p-4 mb-4" id="refund-form-card" style="display: none; animation: slideDown 0.3s ease-out;">
        <h5 class="mb-3 text-start" style="color:#dc2626;"><i class="fas fa-file-invoice-dollar me-2"></i> Request Refund</h5>
        <p class="text-muted small text-start mb-4">ප්‍රතිපත්තියට අනුකූලව ඔබේ මුදල් ආපසු ලබා ගැනීමට කරුණාකර පහත තොරතුරු සම්පූර්ණ කර WhatsApp සහාය සේවාව වෙත යොමු කරන්න.</p>
        
        <form id="refundForm">
            <div class="row text-start">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold small text-uppercase text-muted" style="font-size:0.7rem; letter-spacing:0.8px;">Full Name</label>
                    <input type="text" id="ref_name" class="form-control-custom" value="<?php echo htmlspecialchars($couple_name); ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <!-- ගෙවා ඇති සැබෑම පැකේජයේ නම dynamically පෙන්වයි -->
                    <label class="form-label fw-bold small text-uppercase text-muted" style="font-size:0.7rem; letter-spacing:0.8px;">Plan Purchased</label>
                    <input type="text" id="ref_plan" class="form-control-custom" value="<?php echo ucfirst($user_data['package'] ?? 'basic') . ($user_data['has_guest_gallery'] ? ' + Guest Gallery' : ''); ?> Plan - Rs. <?php echo number_format($current_val); ?>" readonly>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold small text-uppercase text-muted" style="font-size:0.7rem; letter-spacing:0.8px;">Date of Payment</label>
                    <input type="date" id="ref_date" class="form-control-custom" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold small text-uppercase text-muted" style="font-size:0.7rem; letter-spacing:0.8px;">Payment Method</label>
                    <input type="text" id="ref_method" class="form-control-custom" value="Bank Transfer" readonly>
                </div>
                <div class="col-12 mb-3">
                    <label class="form-label fw-bold small text-uppercase text-muted" style="font-size:0.7rem; letter-spacing:0.8px;">Payment Reference / Receipt Details</label>
                    <input type="text" id="ref_code" class="form-control-custom" placeholder="e.g. Transaction ID, Branch, or slip upload context" required>
                </div>
                <div class="col-12 mb-4">
                    <label class="form-label fw-bold small text-uppercase text-muted" style="font-size:0.7rem; letter-spacing:0.8px;">Reason for Refund (කෙටි විස්තරයක්)</label>
                    <textarea id="ref_reason" class="form-control-custom" rows="3" placeholder="මුදල් ආපසු ලබා ගැනීමට බලාපොරොත්තු වන කෙටි හේතුව සටහන් කරන්න..." required></textarea>
                </div>
            </div>
            <button type="submit" class="btn-submit-refund">
                <i class="fab fa-whatsapp"></i> Submit Refund Request via WhatsApp
            </button>
        </form>
    </div>

    <?php else: ?>
    
    <!-- UNACTIVATED STATE: CHOOSE PACKAGE & UPLOAD SLIP -->
    <form method="POST" action="payment.php" enctype="multipart/form-data">
        
        <!-- Package Selector Section -->
        <div class="steps-card">
            <h5 class="package-selector-title"><i class="fas fa-box-open" style="color:#c9a96e; margin-right:8px;"></i> 1. Choose Your Pricing Plan (පැකේජය තෝරන්න)</h5>
            
            <div class="package-grid">
                <!-- BASIC -->
                <div class="package-card" id="pkg-basic-card" onclick="selectPackage('basic')">
                    <input type="radio" name="package" id="pkg-basic" value="basic" checked style="display:none;">
                    <div class="pkg-name">Basic</div>
                    <div class="pkg-desc">Simple & Elegant</div>
                    <div class="pkg-price">Rs. 2,500 <span>/one-time</span></div>
                    <ul class="pkg-features">
                        <li><i class="fas fa-check-circle"></i> 1 Invitation Template</li>
                        <li><i class="fas fa-check-circle"></i> Up to 150 guests (seats)</li>
                        <li><i class="fas fa-check-circle"></i> RSVP & Open Tracking</li>
                        <li><i class="fas fa-check-circle"></i> Countdown, Maps, Calendar</li>
                    </ul>
                </div>

                <!-- STANDARD -->
                <div class="package-card selected" id="pkg-standard-card" onclick="selectPackage('standard')">
                    <div class="pop-badge">Most Popular</div>
                    <input type="radio" name="package" id="pkg-standard" value="standard" style="display:none;">
                    <div class="pkg-name">Standard</div>
                    <div class="pkg-desc">Best for most weddings</div>
                    <div class="pkg-price">Rs. 5,000 <span>/one-time</span></div>
                    <ul class="pkg-features">
                        <li><i class="fas fa-check-circle"></i> 2 Invitation Templates</li>
                        <li><i class="fas fa-check-circle"></i> <strong>Up to 300 guests (seats)</strong></li>
                        <li><i class="fas fa-check-circle"></i> RSVP & Open Tracking</li>
                        <li><i class="fas fa-check-circle"></i> Countdown, Maps, Calendar</li>
                    </ul>
                </div>

                <!-- PREMIUM -->
                <div class="package-card" id="pkg-premium-card" onclick="selectPackage('premium')">
                    <input type="radio" name="package" id="pkg-premium" value="premium" style="display:none;">
                    <div class="pkg-name">Premium</div>
                    <div class="pkg-desc">Fully Custom Design</div>
                    <div class="pkg-price">Rs. 10,000 <span>/one-time</span></div>
                    <ul class="pkg-features">
                        <li><i class="fas fa-check-circle"></i> Custom built design</li>
                        <li><i class="fas fa-check-circle"></i> Unlimited guests</li>
                        <li><i class="fas fa-check-circle"></i> **Guest Gallery Included**</li>
                        <li><i class="fas fa-check-circle"></i> Priority Support</li>
                    </ul>
                </div>
            </div>

            <!-- Guest Gallery Add-on Checkbox (Basic & Standard වලදී පමණක් තේරිය හැක) -->
            <div class="addon-card" id="gallery-addon-wrapper">
                <div style="text-align:left;">
                    <strong style="font-size:0.88rem; color:#1a1a2e; display:block;">Add Guest Gallery Support (+ Rs. 2,000)</strong>
                    <span style="font-size:0.76rem; color:#9ea3b0;">Allow guests to upload their photos & videos directly inside the invitation.</span>
                </div>
                <div class="form-check form-switch fs-4">
                    <input class="form-check-input" type="checkbox" name="add_gallery" id="add_gallery" onchange="updatePrice()" style="cursor:pointer;">
                </div>
            </div>
        </div>

        <!-- Bank Details and Steps -->
        <div class="steps-card">
            <h4><i class="fas fa-credit-card" style="color:#c9a96e; margin-right:10px;"></i> 2. Bank Transfer Payment</h4>

            <div class="payment-steps">
                <div class="payment-step">
                    <div class="step-num">1</div>
                    <div class="step-content">
                        <h6>Make the bank transfer</h6>
                        <p>Transfer the dynamically calculated package fee below</p>
                    </div>
                </div>
                <div class="payment-step">
                    <div class="step-num">2</div>
                    <div class="step-content">
                        <h6>Upload bank slip below</h6>
                        <p>Upload a screenshot or photo of your transaction receipt</p>
                    </div>
                </div>
            </div>

            <div class="bank-details">
                <div class="bank-details-title">Payment Target Account</div>
                <div class="bank-row">
                    <span class="bank-row-label">Bank</span>
                    <span class="bank-row-value">Bank Of Ceylon</span>
                </div>
                <div class="bank-row">
                    <span class="bank-row-label">Account Name</span>
                    <span class="bank-row-value">Hathisa Thissara</span>
                </div>
                <div class="bank-row">
                    <span class="bank-row-label">Account Number</span>
                    <span class="bank-row-value">6819732</span>
                </div>
                <div class="bank-row">
                    <span class="bank-row-label">Amount Due (ගෙවිය යුතු මුදල)</span>
                    <span class="bank-row-value amount" id="bank-amount-display">Rs. 5,000</span>
                </div>
            </div>

            <div class="wa-note">
                <i class="fab fa-whatsapp"></i>
                <span>You can also send the slip directly via <strong>WhatsApp</strong> for manual instant activation.</span>
            </div>
            <a href="#" target="_blank" rel="noopener" class="wa-button" id="wa-activation-btn">
                <i class="fab fa-whatsapp"></i> Instantly Activate via WhatsApp
            </a>

            <div class="paypal-divider"><span>Or</span></div>
            <button type="button" class="btn-paypal" id="paypal-pay-btn">
                <i class="fab fa-paypal"></i> Pay with PayPal — Instant Activation
            </button>
            <p class="paypal-note">Skip the manual review — pay online and your invitation activates immediately.</p>
        </div>

        <!-- Slip Upload Card -->
        <div class="upload-card">
            <?php if (!empty($user_data['payment_slip'])): ?>
            <h5>Slip Submitted — Awaiting Review</h5>
            <div class="slip-submitted">
                <i class="fas fa-clock"></i>
                <p>Your bank slip has been submitted. We're reviewing it and will activate your account shortly. Thank you for your patience!</p>
                <img src="../../<?php echo htmlspecialchars($user_data['payment_slip']); ?>"
                     class="slip-preview"
                     alt="Your submitted slip"
                     onerror="this.style.display='none'">
            </div>
            <p style="font-size:0.8rem; color:#9ea3b0; text-align:center; margin-bottom: 18px;">
                Need to update your slip? Upload a new one below.
            </p>
            <?php else: ?>
            <h5>Upload Bank Slip / Receipt</h5>
            <?php endif; ?>

            <div class="drop-zone" id="drop-zone">
                <input type="file" name="bank_slip" id="slip-file" accept="image/*,.pdf" required>
                <div class="drop-zone-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                <p class="drop-zone-text">
                    <strong>Click to select file</strong> or drag & drop<br>
                    JPG, PNG, WEBP, or PDF accepted
                </p>
            </div>
            <div class="selected-file" id="selected-file">
                <i class="fas fa-file-alt" style="margin-right:6px;"></i>
                <span id="file-name"></span>
            </div>
            <button type="submit" class="btn-upload">
                <i class="fas fa-upload"></i> Submit Bank Slip
            </button>
        </div>
    </form>
    <?php endif; ?>
</div>

<script>
document.getElementById('slip-file')?.addEventListener('change', function() {
    if (this.files[0]) {
        document.getElementById('file-name').textContent = this.files[0].name;
        document.getElementById('selected-file').style.display = 'flex';
    }
});

// Toggle Upgrade Form Card
function toggleUpgradeForm() {
    const card = document.getElementById('upgrade-form-card');
    if (card) {
        if (card.style.display === 'none' || card.style.display === '') {
            card.style.display = 'block';
            card.scrollIntoView({ behavior: 'smooth' });
            calculateUpgradePrice(); // Calculate initial upgrade fee
        } else {
            card.style.display = 'none';
        }
    }
}

// Dynamic Upgrade Balance Price Calculator (Highly Optimized!)
function calculateUpgradePrice() {
    const currentVal = parseInt(document.getElementById('current_val').value) || 2500;
    const upgradeSelect = document.getElementById('upgrade_package_target');
    if (!upgradeSelect) return;

    const selectedOption = upgradeSelect.options[upgradeSelect.selectedIndex];
    const targetPrice = parseInt(selectedOption.getAttribute('data-price')) || 5000;
    
    let balance = targetPrice - currentVal;
    if (balance < 0) balance = 0;

    document.getElementById('target-value-display').textContent = `Rs. ${targetPrice.toLocaleString()}`;
    document.getElementById('upgrade-amount-display').textContent = `Rs. ${balance.toLocaleString()}`;
}

// Toggle Refund Form
function toggleRefundForm() {
    const card = document.getElementById('refund-form-card');
    if (card.style.display === 'none' || card.style.display === '') {
        card.style.display = 'block';
        card.scrollIntoView({ behavior: 'smooth' });
    } else {
        card.style.display = 'none';
    }
}

// Package Selector (For Initial Activations)
function selectPackage(pkg) {
    const radio = document.getElementById('pkg-' + pkg);
    if (radio) radio.checked = true;

    document.getElementById('pkg-basic-card').classList.remove('selected');
    document.getElementById('pkg-standard-card').classList.remove('selected');
    document.getElementById('pkg-premium-card').classList.remove('selected');

    document.getElementById('pkg-' + pkg + '-card').classList.add('selected');

    updatePrice();
}

function updatePrice() {
    const packageSelect = document.querySelector('input[name="package"]:checked').value;
    const galleryCheckbox = document.getElementById('add_gallery');
    const bankAmountDisplay = document.getElementById('bank-amount-display');
    const waButton = document.getElementById('wa-activation-btn');
    
    let basePrice = 2500;
    let planText = "Basic Plan";
    
    if (packageSelect === 'standard') {
        basePrice = 5000;
        planText = "Standard Plan";
        galleryCheckbox.disabled = false;
        document.getElementById('gallery-addon-wrapper').style.opacity = '1';
    } else if (packageSelect === 'premium') {
        basePrice = 10000;
        planText = "Premium Plan (Includes Guest Gallery)";
        galleryCheckbox.checked = true;
        galleryCheckbox.disabled = true;
        document.getElementById('gallery-addon-wrapper').style.opacity = '0.5';
    } else { // basic
        basePrice = 2500;
        planText = "Basic Plan";
        galleryCheckbox.disabled = false;
        document.getElementById('gallery-addon-wrapper').style.opacity = '1';
    }
    
    let addonPrice = 0;
    if (galleryCheckbox.checked && packageSelect !== 'premium') {
        addonPrice = 2000;
        planText += " + Guest Gallery Add-on";
    }
    
    const total = basePrice + addonPrice;
    
    bankAmountDisplay.textContent = `Rs. ${total.toLocaleString()}`;
    
    const coupleName = <?php echo json_encode($couple_name); ?>;
    const coupleEmail = <?php echo json_encode($couple_email); ?>;
    
    const waMsg = `Hello Lumus Studio, this is ${coupleName}. My email is ${coupleEmail}. I have chosen the ${planText}. I would like to submit my bank slip for manual activation (Total Amount: Rs. ${total.toLocaleString()}). Thank you!`;
    waButton.href = `https://wa.me/94701207991?text=${encodeURIComponent(waMsg)}`;
}

window.addEventListener('DOMContentLoaded', () => {
    // Normal Flow initialization
    if (document.getElementById('pkg-standard-card')) {
        selectPackage('standard');
    }
});

// Compile Refund Request
document.getElementById('refundForm')?.addEventListener('submit', function(e) {
    e.preventDefault();

    const name = document.getElementById('ref_name').value;
    const plan = document.getElementById('ref_plan').value;
    const date = document.getElementById('ref_date').value;
    const method = document.getElementById('ref_method').value;
    const ref = document.getElementById('ref_code').value;
    const reason = document.getElementById('ref_reason').value;

    const slipPath = "<?php echo !empty($user_data['payment_slip']) ? $domain . '/' . ltrim($user_data['payment_slip'], './') : 'None'; ?>";

    const alertEmoji = "\u{26A0}\u{FE0F}"; // ⚠️
    const docEmoji = "\u{1F4C4}";   // 📄
    const infoEmoji = "\u{2139}\u{FE0F}"; // ℹ️

    const message = `${alertEmoji} REFUND REQUEST - LUMUS STUDIO ${alertEmoji}\n\n`
        + `👤 *Full Name:* ${name}\n`
        + `💎 *Plan:* ${plan}\n`
        + `📅 *Date of Payment:* ${date}\n`
        + `💳 *Method:* ${method}\n`
        + `🔍 *Reference/Note:* ${ref}\n`
        + `${docEmoji} *Bank Receipt Link:* ${slipPath}\n\n`
        + `${infoEmoji} *Reason for Refund:* \n"${reason}"\n\n`
        + `Please process this request in accordance with the Refund Policy. Thank you!`;

    const encodedMsg = encodeURIComponent(message);
    const adminWhatsApp = "94701207991";

    const formData = new FormData();
    formData.append('action', 'request_refund');
    formData.append('reason', reason);

    fetch('payment.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        let waUrl = "";
        const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);

        if (isMobile) {
            waUrl = `whatsapp://send?phone=${adminWhatsApp}&text=${encodedMsg}`;
            window.open(waUrl, '_blank');
        } else {
            let hasApp = false;
            const checkBlur = () => { hasApp = true; };
            window.addEventListener('blur', checkBlur);
            window.location.href = `whatsapp://send?phone=${adminWhatsApp}&text=${encodedMsg}`;

            setTimeout(() => {
                window.removeEventListener('blur', checkBlur);
                if (!hasApp) {
                    const webUrl = `https://web.whatsapp.com/send?phone=${adminWhatsApp}&text=${encodedMsg}`;
                    window.open(webUrl, '_blank');
                }
            }, 1000);
        }
        
        setTimeout(() => {
            location.reload();
        }, 1500);
    })
    .catch(err => {
        alert("Refund submission failed. Please try again.");
    });
});

// SUBMIT BANK DETAILS
document.getElementById('bankDetailsForm')?.addEventListener('submit', function(e) {
    e.preventDefault();

    const bank = document.getElementById('bank_name').value;
    const name = document.getElementById('acc_name').value;
    const num = document.getElementById('acc_num').value;
    const branch = document.getElementById('branch').value;

    const formData = new FormData();
    formData.append('action', 'submit_bank_details');
    formData.append('bank_name', bank);
    formData.append('acc_name', name);
    formData.append('acc_num', num);
    formData.append('branch', branch);

    fetch('payment.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert("Bank details submitted successfully! We are now processing your transfer.");
            location.reload();
        } else {
            alert("Failed to submit details. Please try again.");
        }
    })
    .catch(err => {
        alert("An error occurred. Please try again.");
    });
});

// =====================================================================
// 🔥 සජීවීව Account Status Check කිරීම — Admin action ගත්තොත් auto-refresh (8s Polling)
// =====================================================================
const initialStatusFingerprint = <?php echo json_encode($initial_status_fingerprint); ?>;

function checkAccountStatusLive() {
    fetch('payment.php?action=status_check')
        .then(r => r.json())
        .then(data => {
            if (data.fingerprint && data.fingerprint !== initialStatusFingerprint) {
                if (typeof showToast === 'function') {
                    showToast('✨ Your account was just updated! Refreshing...');
                }
                setTimeout(() => location.reload(), 1800);
            }
        })
        .catch(err => console.error('Error checking live account status:', err));
}
setInterval(checkAccountStatusLive, 8000);

// =====================================================================
// 💳 PayPal Instant Payment — create order, redirect to approve, then capture on return
// =====================================================================
document.getElementById('paypal-pay-btn')?.addEventListener('click', function() {
    const btn = this;
    const packageRadio = document.querySelector('input[name="package"]:checked');
    const packageSelect = packageRadio ? packageRadio.value : 'basic';
    const galleryEl = document.getElementById('add_gallery');
    const galleryChecked = galleryEl ? galleryEl.checked : false;

    btn.disabled = true;
    const originalHtml = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Connecting to PayPal...';

    const formData = new FormData();
    formData.append('action', 'create');
    formData.append('package', packageSelect);
    if (galleryChecked) formData.append('add_gallery', '1');

    fetch('api/paypal_payment.php', { method: 'POST', body: formData })
        .then(r => r.text().then(text => ({ ok: r.ok, status: r.status, text })))
        .then(({ ok, status, text }) => {
            let data;
            try {
                data = JSON.parse(text);
            } catch (parseErr) {
                console.error('PayPal create — non-JSON response (HTTP ' + status + '):', text);
                throw new Error('The server returned an unexpected response (HTTP ' + status + '). Check the browser console for details.');
            }

            if (data.success && data.approval_url) {
                localStorage.setItem('pp_package', packageSelect);
                localStorage.setItem('pp_gallery', galleryChecked ? '1' : '0');
                localStorage.setItem('pp_amount', data.amount);
                window.location.href = data.approval_url;
            } else {
                alert('PayPal error: ' + (data.message || 'Could not start PayPal payment. Please try Bank Transfer instead.'));
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            }
        })
        .catch(err => {
            console.error('PayPal create request failed:', err);
            alert((err && err.message) ? err.message : 'Could not connect to PayPal right now. Please try Bank Transfer instead.');
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        });
});

// Handle the return trip from PayPal (approval_url redirects back here with ?paypal_action=...&token=ORDER_ID)
(function handlePayPalReturn() {
    const params = new URLSearchParams(window.location.search);
    const ppAction = params.get('paypal_action');
    if (!ppAction) return;

    if (ppAction === 'cancel') {
        localStorage.removeItem('pp_package');
        localStorage.removeItem('pp_gallery');
        localStorage.removeItem('pp_amount');
        window.history.replaceState({}, document.title, 'payment.php');
        return;
    }

    if (ppAction === 'success') {
        const orderId = params.get('token');
        if (!orderId) return;

        const pkg = localStorage.getItem('pp_package') || 'basic';
        const gallery = localStorage.getItem('pp_gallery') === '1';
        const amount = localStorage.getItem('pp_amount') || '2500';

        const overlay = document.createElement('div');
        overlay.className = 'pp-capture-overlay';
        overlay.innerHTML = '<div><div class="spinner-border text-light mb-2" role="status"></div><p>Confirming your PayPal payment...</p></div>';
        document.body.appendChild(overlay);

        const formData = new FormData();
        formData.append('action', 'capture');
        formData.append('order_id', orderId);
        formData.append('package', pkg);
        if (gallery) formData.append('add_gallery', '1');
        formData.append('amount', amount);

        fetch('api/paypal_payment.php', { method: 'POST', body: formData })
            .then(r => r.json())
            .then(data => {
                localStorage.removeItem('pp_package');
                localStorage.removeItem('pp_gallery');
                localStorage.removeItem('pp_amount');
                window.history.replaceState({}, document.title, 'payment.php');

                if (data.success) {
                    overlay.innerHTML = '<div><i class="fas fa-check-circle pp-icon-ok"></i><p>Payment successful! Activating your account...</p></div>';
                    setTimeout(() => { window.location.href = 'payment.php'; }, 1600);
                } else {
                    overlay.innerHTML = '<div><i class="fas fa-times-circle pp-icon-err"></i><p>' + (data.message || 'Payment could not be confirmed.') + '</p><button class="btn-pp-close" onclick="window.location.href=\'payment.php\'">Close</button></div>';
                }
            })
            .catch(() => {
                localStorage.removeItem('pp_package');
                localStorage.removeItem('pp_gallery');
                localStorage.removeItem('pp_amount');
                overlay.innerHTML = '<div><i class="fas fa-times-circle pp-icon-err"></i><p>Something went wrong confirming your payment.</p><button class="btn-pp-close" onclick="window.location.href=\'payment.php\'">Close</button></div>';
            });
    }
})();
</script>

<?php require '../layouts/footer.php'; ?>