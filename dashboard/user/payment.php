<?php
session_start();
require '../../config/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'couple') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$msg = "";

// Pick up a flash message left by a previous POST-then-redirect (see upload blocks below).
// This is what lets us redirect after upload instead of re-rendering a POST result page.
if (isset($_SESSION['flash_msg'])) {
    $msg = $_SESSION['flash_msg'];
    unset($_SESSION['flash_msg']);
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

// 3. Handle AJAX Bank Details Submission (Refund Approved දැනටමත් වලදී)
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

// Helper: PHP upload error codes -> human readable messages (used to diagnose "Upload failed")
function upload_error_message($errorCode) {
    switch ($errorCode) {
        case UPLOAD_ERR_OK:
            return null;
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            return "The file is too large. Please upload a smaller image or PDF (under the server's upload limit).";
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
        $target_dir = "../../uploads/slips/";
        if (!file_exists($target_dir)) {
            @mkdir($target_dir, 0777, true);
        }

        if (!is_dir($target_dir) || !is_writable($target_dir)) {
            error_log("payment.php: upload directory missing or not writable: " . realpath('..') . '/uploads/slips/');
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
                    error_log("payment.php: move_uploaded_file failed for user {$user_id}. tmp_name={$file['tmp_name']} target={$target_file}");
                    $_SESSION['flash_msg'] = "<div class='flash flash-error'><i class='fas fa-times-circle'></i> Upload failed. Please try again.</div>";
                }
            } else {
                $_SESSION['flash_msg'] = "<div class='flash flash-error'><i class='fas fa-times-circle'></i> Please upload a JPG, PNG, WEBP, or PDF file.</div>";
            }
        }
    }

    // Redirect (PRG pattern) instead of falling through to render this POST result directly.
    // Without this, the 8s live status-check poll's location.reload() could resubmit this
    // exact upload a second time (browsers re-POST on reload of a POST result page).
    header("Location: payment.php");
    exit();
}

// 5. Handle UPGRADE Slip Submission (දැනටමත් සක්‍රීය ගිණුමක Upgrade slip එකක් එවද්දී)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['upgrade_slip_file'])) {
    $file = $_FILES['upgrade_slip_file'];

    // Unified option එක කියවා ගැනීම (e.g. "standard|1")
    $target_str = $_POST['upgrade_package_target'] ?? 'standard|0';

    $upload_error = upload_error_message($file['error']);

    if ($upload_error) {
        $_SESSION['flash_msg'] = "<div class='flash flash-error'><i class='fas fa-times-circle'></i> " . htmlspecialchars($upload_error) . "</div>";
    } else {
        $target_dir = "../../uploads/slips/";
        if (!file_exists($target_dir)) {
            @mkdir($target_dir, 0777, true);
        }

        if (!is_dir($target_dir) || !is_writable($target_dir)) {
            error_log("payment.php: upgrade upload directory missing or not writable: " . realpath('..') . '/uploads/slips/');
            $_SESSION['flash_msg'] = "<div class='flash flash-error'><i class='fas fa-times-circle'></i> Upload failed. Please try again.</div>";
        } else {
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'webp', 'pdf'];

            if (in_array($ext, $allowed)) {
                $new_filename = "upgrade_slip_" . $user_id . "_" . time() . "." . $ext;
                $target_file  = $target_dir . $new_filename;

                if (move_uploaded_file($file['tmp_name'], $target_file)) {
                    $db_path = "uploads/slips/" . $new_filename;

                    // upgrade_slip සහ pending_upgrade_plan update කරයි!
                    $stmt = $pdo->prepare("UPDATE users SET upgrade_slip = ?, pending_upgrade_plan = ? WHERE id = ?");
                    if ($stmt->execute([$db_path, $target_str, $user_id])) {
                        $_SESSION['flash_msg'] = "<div class='flash flash-success'><i class='fas fa-check-circle'></i> Upgrade slip submitted! We will process your upgrade shortly. Your current invitation remains LIVE.</div>";
                    }
                } else {
                    error_log("payment.php: move_uploaded_file failed (upgrade) for user {$user_id}. tmp_name={$file['tmp_name']} target={$target_file}");
                    $_SESSION['flash_msg'] = "<div class='flash flash-error'><i class='fas fa-times-circle'></i> Upload failed. Please try again.</div>";
                }
            } else {
                $_SESSION['flash_msg'] = "<div class='flash flash-error'><i class='fas fa-times-circle'></i> Please upload a JPG, PNG, WEBP, or PDF file.</div>";
            }
        }
    }

    // Redirect (PRG pattern) — same reasoning as the bank_slip block above.
    header("Location: payment.php");
    exit();
}

// 6. AJAX: සජීවීව (Live) Account Status Check කිරීම — Admin action gත් ගිණුමට Auto-refresh කිරීම සඳහා
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

// වර්තමාන පැකේජයේ මුළු වටිනාකම හැදීම
$current_val = 2500;
if ($user_data['package'] === 'standard') $current_val = 5000;
if ($user_data['package'] === 'premium') $current_val = 10000;
if ($user_data['has_guest_gallery'] == 1 && $user_data['package'] !== 'premium') $current_val += 2000;

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$domain = $protocol . $_SERVER['HTTP_HOST'] . dirname(dirname(dirname($_SERVER['PHP_SELF'])));

require '../layouts/header.php';
?>