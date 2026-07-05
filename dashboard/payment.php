<?php
session_start();
require '../config/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'couple') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$msg = "";

// Handle slip upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['bank_slip'])) {
    $file = $_FILES['bank_slip'];
    $target_dir = "../uploads/slips/";
    if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp', 'pdf'];

    if (in_array($ext, $allowed)) {
        $new_filename = "slip_" . $user_id . "_" . time() . "." . $ext;
        $target_file  = $target_dir . $new_filename;

        if (move_uploaded_file($file['tmp_name'], $target_file)) {
            $db_path = "uploads/slips/" . $new_filename;
            $pdo->prepare("UPDATE users SET payment_slip = ? WHERE id = ?")
                ->execute([$db_path, $user_id]);
            $_SESSION['status'] = 'pending'; // Still pending until admin approves
            $msg = "<div class='flash flash-success'><i class='fas fa-check-circle'></i> Bank slip uploaded! We'll review and activate your account soon.</div>";
        } else {
            $msg = "<div class='flash flash-error'><i class='fas fa-times-circle'></i> Upload failed. Please try again.</div>";
        }
    } else {
        $msg = "<div class='flash flash-error'><i class='fas fa-times-circle'></i> Please upload a JPG, PNG, WEBP, or PDF file.</div>";
    }
}

// Get current slip/status
$stmtGetSlip = $pdo->prepare("SELECT name, email, payment_slip, status FROM users WHERE id = ?");
$stmtGetSlip->execute([$user_id]);
$user_data = $stmtGetSlip->fetch();

$couple_name = !empty($user_data['name']) ? $user_data['name'] : ($_SESSION['user_name'] ?? 'Couple');
$couple_email = $user_data['email'] ?? '';
$whatsapp_number = '+94701207991';
$whatsapp_message = "Hello Lumus Studio, this is {$couple_name}. My email is {$couple_email}. I would like to submit my payment slip for invitation activation. Please review it and instantly activate my invitation link. Thank you.";
$whatsapp_link = 'https://wa.me/' . $whatsapp_number . '?text=' . rawurlencode($whatsapp_message);

require 'layouts/header.php';
?>

<style>
    .flash { padding: 13px 18px; border-radius: 12px; font-size: 0.87rem; margin-bottom: 20px; display:flex; align-items:center; gap:10px; }
    .flash-success { background: rgba(34,197,94,0.1); border:1px solid rgba(34,197,94,0.25); color: #16a34a; }
    .flash-error   { background: rgba(239,68,68,0.1); border:1px solid rgba(239,68,68,0.25); color: #dc2626; }

    .payment-wrapper {
        max-width: 640px;
        margin: 0 auto;
    }

    /* Active state */
    .active-banner {
        background: linear-gradient(135deg, rgba(34,197,94,0.08), rgba(34,197,94,0.04));
        border: 1px solid rgba(34,197,94,0.25);
        border-radius: 20px;
        padding: 40px;
        text-align: center;
    }
    .active-icon {
        width: 72px; height: 72px;
        border-radius: 50%;
        background: rgba(34,197,94,0.12);
        border: 2px solid rgba(34,197,94,0.25);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        color: #22c55e;
        margin: 0 auto 20px;
    }
    .active-banner h3 {
        font-size: 1.4rem;
        font-weight: 700;
        color: #1a1a2e;
        margin-bottom: 8px;
    }
    .active-banner p { color: #6b7280; font-size: 0.9rem; }

    /* Steps */
    .steps-card {
        background: white;
        border: 1px solid #e8ecf0;
        border-radius: 20px;
        padding: 32px;
        margin-bottom: 20px;
    }
    .steps-card h4 {
        font-size: 1.1rem;
        font-weight: 700;
        color: #1a1a2e;
        margin-bottom: 24px;
    }

    .payment-steps { display: flex; flex-direction: column; gap: 16px; margin-bottom: 28px; }
    .payment-step {
        display: flex;
        gap: 16px;
        align-items: flex-start;
    }
    .step-num {
        width: 32px; height: 32px;
        flex-shrink: 0;
        border-radius: 50%;
        background: rgba(201,169,110,0.12);
        border: 1px solid rgba(201,169,110,0.25);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8rem;
        font-weight: 700;
        color: #c9a96e;
    }
    .step-content h6 { font-size: 0.88rem; font-weight: 700; color: #1a1a2e; margin-bottom: 3px; }
    .step-content p { font-size: 0.81rem; color: #6b7280; margin: 0; }

    /* Bank details box */
    .bank-details {
        background: #f8fafc;
        border: 1px solid #e8ecf0;
        border-radius: 14px;
        padding: 20px 24px;
        margin-bottom: 24px;
    }
    .bank-details-title {
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 1.5px;
        text-transform: uppercase;
        color: #9ea3b0;
        margin-bottom: 14px;
    }
    .bank-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 0;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.87rem;
    }
    .bank-row:last-child { border-bottom: none; }
    .bank-row-label { color: #9ea3b0; }
    .bank-row-value { font-weight: 700; color: #1a1a2e; }
    .bank-row-value.amount { color: #c9a96e; font-size: 1.1rem; }

    /* Upload area */
    .upload-card {
        background: white;
        border: 1px solid #e8ecf0;
        border-radius: 20px;
        padding: 28px;
    }
    .upload-card h5 {
        font-size: 0.95rem;
        font-weight: 700;
        color: #1a1a2e;
        margin-bottom: 16px;
    }
    .drop-zone {
        border: 2px dashed #e8ecf0;
        border-radius: 14px;
        padding: 36px 20px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s;
        background: #fafbfc;
        position: relative;
        margin-bottom: 14px;
    }
    .drop-zone:hover { border-color: #c9a96e; background: rgba(201,169,110,0.03); }
    .drop-zone input[type="file"] {
        position: absolute;
        inset: 0;
        opacity: 0;
        cursor: pointer;
        width: 100%;
        height: 100%;
    }
    .drop-zone-icon { font-size: 2rem; color: rgba(201,169,110,0.4); margin-bottom: 10px; }
    .drop-zone-text { font-size: 0.85rem; color: #9ea3b0; line-height: 1.6; }
    .drop-zone-text strong { color: #c9a96e; }
    .selected-file {
        display: none;
        background: rgba(201,169,110,0.08);
        border: 1px solid rgba(201,169,110,0.2);
        border-radius: 10px;
        padding: 10px 14px;
        font-size: 0.83rem;
        color: #a07840;
        margin-bottom: 14px;
    }
    .btn-upload {
        width: 100%;
        background: linear-gradient(135deg, #c9a96e, #a07840);
        color: #0f0f1a;
        border: none;
        border-radius: 12px;
        padding: 13px;
        font-family: 'Inter', sans-serif;
        font-size: 0.9rem;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }
    .btn-upload:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(201,169,110,0.3); }

    /* Slip uploaded state */
    .slip-submitted {
        background: rgba(245,158,11,0.06);
        border: 1px solid rgba(245,158,11,0.2);
        border-radius: 14px;
        padding: 20px;
        text-align: center;
        margin-bottom: 16px;
    }
    .slip-submitted i { font-size: 2rem; color: #d97706; margin-bottom: 10px; display: block; }
    .slip-submitted p { font-size: 0.87rem; color: #6b7280; margin: 0; }
    .slip-preview {
        max-width: 160px;
        border-radius: 10px;
        border: 1px solid #e8ecf0;
        margin: 12px auto 0;
        display: block;
        box-shadow: 0 4px 12px rgba(0,0,0,0.06);
    }

    /* WhatsApp note */
    .wa-note {
        background: rgba(37,211,102,0.06);
        border: 1px solid rgba(37,211,102,0.2);
        border-radius: 12px;
        padding: 14px 18px;
        font-size: 0.83rem;
        color: #16a34a;
        display: flex;
        align-items: center;
        gap: 10px;
        margin-top: 14px;
    }
    .wa-note i { font-size: 1.2rem; color: #25d366; }
    .wa-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        margin-top: 12px;
        width: 100%;
        padding: 12px 14px;
        border-radius: 10px;
        background: #25d366;
        color: white;
        text-decoration: none;
        font-weight: 700;
        font-size: 0.9rem;
    }
    .wa-button:hover { background: #1ebe5d; color: white; }
</style>

<div class="payment-wrapper">
    <?php if ($msg) echo $msg; ?>

    <?php if ($user_data['status'] === 'active'): ?>
    <!-- Active -->
    <div class="active-banner">
        <div class="active-icon"><i class="fas fa-check"></i></div>
        <h3>Your Account is Active! 🎉</h3>
        <p>Your invitation is live and ready to share with your guests.</p>
        <a href="index.php" style="display:inline-flex; align-items:center; gap:8px; margin-top:20px; background:linear-gradient(135deg,#c9a96e,#a07840); color:#0f0f1a; padding:11px 24px; border-radius:10px; text-decoration:none; font-weight:700; font-size:0.88rem;">
            <i class="fas fa-tachometer-alt"></i> Go to Dashboard
        </a>
    </div>

    <?php else: ?>
    <!-- How to pay -->
    <div class="steps-card">
        <h4><i class="fas fa-credit-card" style="color:#c9a96e; margin-right:10px;"></i> Activate Your Invitation</h4>

        <div class="payment-steps">
            <div class="payment-step">
                <div class="step-num">1</div>
                <div class="step-content">
                    <h6>Make the bank transfer</h6>
                    <p>Transfer the activation fee to the account below</p>
                </div>
            </div>
            <div class="payment-step">
                <div class="step-num">2</div>
                <div class="step-content">
                    <h6>Upload your bank slip</h6>
                    <p>Upload a screenshot or photo of your transfer receipt</p>
                </div>
            </div>
            <div class="payment-step">
                <div class="step-num">3</div>
                <div class="step-content">
                    <h6>We activate your invitation</h6>
                    <p>We'll review and activate within a few hours</p>
                </div>
            </div>
        </div>

        <div class="bank-details">
            <div class="bank-details-title">Payment Details</div>
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
                <span class="bank-row-label">Amount</span>
                <span class="bank-row-value amount">Rs. 1000</span>
            </div>
        </div>

        <div class="wa-note">
            <i class="fab fa-whatsapp"></i>
            <span>You can also send the slip directly via <strong>WhatsApp</strong> and we'll activate your account manually.</span>
        </div>
        <a href="<?php echo htmlspecialchars($whatsapp_link); ?>" target="_blank" rel="noopener" class="wa-button">
            <i class="fab fa-whatsapp"></i> Instantly Activate Your Link
        </a>
    </div>

    <!-- Upload -->
    <div class="upload-card">
        <?php if (!empty($user_data['payment_slip'])): ?>
        <h5>Slip Submitted — Awaiting Review</h5>
        <div class="slip-submitted">
            <i class="fas fa-clock"></i>
            <p>Your bank slip has been submitted. We're reviewing it and will activate your account shortly. Thank you for your patience!</p>
            <img src="../<?php echo htmlspecialchars($user_data['payment_slip']); ?>"
                 class="slip-preview"
                 alt="Your submitted slip"
                 onerror="this.style.display='none'">
        </div>
        <p style="font-size:0.8rem; color:#9ea3b0; text-align:center;">
            Need to update your slip? Upload a new one below.
        </p>
        <?php else: ?>
        <h5>Upload Bank Slip / Receipt</h5>
        <?php endif; ?>

        <form method="POST" action="payment.php" enctype="multipart/form-data">
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
        </form>
    </div>
    <?php endif; ?>
</div>

<script>
document.getElementById('slip-file').addEventListener('change', function() {
    if (this.files[0]) {
        document.getElementById('file-name').textContent = this.files[0].name;
        document.getElementById('selected-file').style.display = 'flex';
    }
});
</script>

<?php require 'layouts/footer.php'; ?>