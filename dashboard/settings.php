<?php
session_start();
require '../config/config.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['wedding_id'])) {
    header("Location: login.php");
    exit();
}

$user_id    = $_SESSION['user_id'];
$wedding_id = $_SESSION['wedding_id'];
$msg_wedding = "";
$msg_pw = "";

// Ensure column exists
try {
    $pdo->exec("ALTER TABLE weddings ADD COLUMN hero_image VARCHAR(255) DEFAULT NULL");
    $pdo->exec("ALTER TABLE weddings ADD COLUMN template_name VARCHAR(50) DEFAULT 'premium_gold'");
} catch (Exception $e) {}

// Update wedding details
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_wedding'])) {
    $bride = trim($_POST['bride_name']);
    $groom = trim($_POST['groom_name']);
    $date  = $_POST['wedding_date'];
    $template = isset($_POST['template_name']) ? $_POST['template_name'] : 'premium_gold';
    
    if (!empty($bride) && !empty($groom) && !empty($date)) {
        $pdo->prepare("UPDATE weddings SET bride_name = ?, groom_name = ?, wedding_date = ?, template_name = ? WHERE id = ? AND user_id = ?")
            ->execute([$bride, $groom, $date, $template, $wedding_id, $user_id]);
        
        $new_name = $bride . " & " . $groom;
        $pdo->prepare("UPDATE users SET name = ? WHERE id = ?")
            ->execute([$new_name, $user_id]);
        $_SESSION['user_name'] = $new_name;
        $msg_wedding = "<div class='flash flash-success'><i class='fas fa-check-circle'></i> Wedding details updated!</div>";
    } else {
        $msg_wedding = "<div class='flash flash-error'><i class='fas fa-times-circle'></i> Please fill in all fields.</div>";
    }
}

// Change password
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_password'])) {
    $current_pw  = $_POST['current_password'];
    $new_pw      = $_POST['new_password'];
    $confirm_pw  = $_POST['confirm_password'];

    $stmtPw = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmtPw->execute([$user_id]);
    $user_data = $stmtPw->fetch();

    if ($user_data && password_verify($current_pw, $user_data['password'])) {
        if ($new_pw === $confirm_pw) {
            if (strlen($new_pw) >= 6) {
                $new_hash = password_hash($new_pw, PASSWORD_DEFAULT);
                $pdo->prepare("UPDATE users SET password = ? WHERE id = ?")
                    ->execute([$new_hash, $user_id]);
                $msg_pw = "<div class='flash flash-success'><i class='fas fa-check-circle'></i> Password updated successfully!</div>";
            } else {
                $msg_pw = "<div class='flash flash-error'><i class='fas fa-times-circle'></i> Password must be at least 6 characters.</div>";
            }
        } else {
            $msg_pw = "<div class='flash flash-error'><i class='fas fa-times-circle'></i> Passwords do not match.</div>";
        }
    } else {
        $msg_pw = "<div class='flash flash-error'><i class='fas fa-times-circle'></i> Current password is incorrect.</div>";
    }
}

// Load current wedding data
$stmtGetWed = $pdo->prepare("SELECT bride_name, groom_name, wedding_date, hero_image, template_name FROM weddings WHERE id = ? AND user_id = ?");
$stmtGetWed->execute([$wedding_id, $user_id]);
$wedding_data = $stmtGetWed->fetch();

require 'layouts/header.php';
?>

<style>
    .flash { padding: 13px 18px; border-radius: 12px; font-size: 0.87rem; margin-bottom: 16px; display:flex; align-items:center; gap:10px; }
    .flash-success { background: rgba(34,197,94,0.1); border:1px solid rgba(34,197,94,0.25); color: #16a34a; }
    .flash-error   { background: rgba(239,68,68,0.1); border:1px solid rgba(239,68,68,0.25); color: #dc2626; }

    .settings-card {
        background: white;
        border: 1px solid #e8ecf0;
        border-radius: 16px;
        overflow: hidden;
        height: 100%;
    }
    .settings-card-header {
        padding: 22px 26px;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .settings-card-icon {
        width: 40px; height: 40px;
        border-radius: 10px;
        background: rgba(201,169,110,0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #c9a96e;
        font-size: 1rem;
    }
    .settings-card-title {
        font-size: 0.92rem;
        font-weight: 700;
        color: #1a1a2e;
    }
    .settings-card-sub {
        font-size: 0.76rem;
        color: #9ea3b0;
        margin-top: 1px;
    }
    .settings-card-body { padding: 24px 26px; }

    .form-field { margin-bottom: 18px; }
    .form-field label {
        display: block;
        font-size: 0.73rem;
        font-weight: 600;
        color: #9ea3b0;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        margin-bottom: 8px;
    }
    .form-field .input-wrap { position: relative; }
    .form-field .input-wrap i {
        position: absolute;
        left: 13px;
        top: 50%;
        transform: translateY(-50%);
        color: #d1d5db;
        font-size: 0.85rem;
    }
    .form-field input {
        width: 100%;
        border: 1px solid #e8ecf0;
        border-radius: 10px;
        padding: 11px 14px 11px 38px;
        font-family: 'Inter', sans-serif;
        font-size: 0.88rem;
        color: #1a1a2e;
        background: #fafbfc;
        outline: none;
        transition: border-color 0.2s;
    }
    .form-field input:focus, .form-field select:focus {
        border-color: #c9a96e;
        background: #fffdf9;
    }
    .form-field input[type="date"] { padding-left: 38px; }
    .form-field select {
        width: 100%;
        border: 1px solid #e8ecf0;
        border-radius: 10px;
        padding: 11px 14px 11px 38px;
        font-family: 'Inter', sans-serif;
        font-size: 0.88rem;
        color: #1a1a2e;
        background: #fafbfc;
        outline: none;
        transition: border-color 0.2s;
        appearance: none;
    }

    .btn-save {
        background: linear-gradient(135deg, #1a1a2e, #2d2d50);
        color: #c9a96e;
        border: none;
        border-radius: 10px;
        padding: 11px 24px;
        font-family: 'Inter', sans-serif;
        font-size: 0.85rem;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        width: 100%;
        justify-content: center;
    }
    .btn-save:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 20px rgba(26,26,46,0.3);
    }
</style>

<div class="row g-3">
    <!-- Wedding Details -->
    <div class="col-lg-6">
        <div class="settings-card">
            <div class="settings-card-header">
                <div class="settings-card-icon"><i class="fas fa-heart"></i></div>
                <div>
                    <div class="settings-card-title">Wedding Details</div>
                    <div class="settings-card-sub">Update your couple's names and wedding date</div>
                </div>
            </div>
            <div class="settings-card-body">
                <?php echo $msg_wedding; ?>
                <form method="POST" action="settings.php" enctype="multipart/form-data">
                    <div class="form-field">
                        <label>Bride's Name</label>
                        <div class="input-wrap">
                            <i class="fas fa-heart"></i>
                            <input type="text" name="bride_name"
                                value="<?php echo htmlspecialchars($wedding_data['bride_name'] ?? ''); ?>"
                                placeholder="Bride's name" required>
                        </div>
                    </div>
                    <div class="form-field">
                        <label>Groom's Name</label>
                        <div class="input-wrap">
                            <i class="fas fa-heart"></i>
                            <input type="text" name="groom_name"
                                value="<?php echo htmlspecialchars($wedding_data['groom_name'] ?? ''); ?>"
                                placeholder="Groom's name" required>
                        </div>
                    </div>
                    <div class="form-field">
                        <label>Wedding Date</label>
                        <div class="input-wrap">
                            <i class="far fa-calendar"></i>
                            <input type="date" name="wedding_date"
                                value="<?php echo htmlspecialchars($wedding_data['wedding_date'] ?? ''); ?>"
                                required>
                        </div>
                    </div>
                    <div class="form-field">
                        <label>Design Template</label>
                        <div class="input-wrap">
                            <i class="fas fa-paint-brush"></i>
                            <select name="template_name" required>
                                <option value="premium_gold" <?php echo ($wedding_data['template_name'] == 'premium_gold' || empty($wedding_data['template_name'])) ? 'selected' : ''; ?>>Premium Gold (Dark Theme)</option>
                                <option value="minimal_light" <?php echo ($wedding_data['template_name'] == 'minimal_light') ? 'selected' : ''; ?>>Minimal Light (Clean Theme)</option>
                                <option value="terracotta_bloom" <?php echo ($wedding_data['template_name'] == 'terracotta_bloom') ? 'selected' : ''; ?>>Terracotta Bloom (Warm Theme)</option>
                                <option value="plum_parchment" <?php echo ($wedding_data['template_name'] == 'plum_parchment') ? 'selected' : ''; ?>>Plum Parchment (Elegant Theme)</option>
                                <option value="floral_garden" <?php echo ($wedding_data['template_name'] == 'floral_garden') ? 'selected' : ''; ?>>Floral Garden (Floral Theme)</option>
                                <option value="beach_tropical" <?php echo ($wedding_data['template_name'] == 'beach_tropical') ? 'selected' : ''; ?>>Beach Tropical (Tropical Theme)</option>
                                <option value="rustic_boho" <?php echo ($wedding_data['template_name'] == 'rustic_boho') ? 'selected' : ''; ?>>Rustic Boho (Boho Theme)</option>
                            <option value="royal_classic"<?php echo ($wedding_data['template_name'] == 'royal_classic') ? 'selected' : ''; ?>>Royal Classic (Royal Theme)</option>
                            <option value="indian_royal" <?php echo ($wedding_data['template_name'] == 'indian_royal') ? 'selected' : ''; ?>>Indian Royal (Indian Theme)</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" name="update_wedding" class="btn-save">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Change Password -->
    <div class="col-lg-6">
        <div class="settings-card">
            <div class="settings-card-header">
                <div class="settings-card-icon"><i class="fas fa-key"></i></div>
                <div>
                    <div class="settings-card-title">Change Password</div>
                    <div class="settings-card-sub">Keep your account secure</div>
                </div>
            </div>
            <div class="settings-card-body">
                <?php echo $msg_pw; ?>
                <form method="POST" action="settings.php">
                    <div class="form-field">
                        <label>Current Password</label>
                        <div class="input-wrap">
                            <i class="fas fa-lock"></i>
                            <input type="password" name="current_password" placeholder="••••••••" required>
                        </div>
                    </div>
                    <div class="form-field">
                        <label>New Password</label>
                        <div class="input-wrap">
                            <i class="fas fa-lock"></i>
                            <input type="password" name="new_password" placeholder="At least 6 characters" required minlength="6">
                        </div>
                    </div>
                    <div class="form-field">
                        <label>Confirm New Password</label>
                        <div class="input-wrap">
                            <i class="fas fa-lock"></i>
                            <input type="password" name="confirm_password" placeholder="••••••••" required>
                        </div>
                    </div>
                    <button type="submit" name="update_password" class="btn-save">
                        <i class="fas fa-shield-alt"></i> Update Password
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require 'layouts/footer.php'; ?>