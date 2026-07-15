<?php
session_start();
require '../../config/config.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['wedding_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id    = $_SESSION['user_id'];
$wedding_id = $_SESSION['wedding_id'];
$msg_wedding = "";
$msg_pw = "";
$msg_delete = "";

// Ensure column exists
try {
    $pdo->exec("ALTER TABLE weddings ADD COLUMN hero_image VARCHAR(255) DEFAULT NULL");
    $pdo->exec("ALTER TABLE weddings ADD COLUMN template_name VARCHAR(50) DEFAULT 'premium_gold'");
} catch (Exception $e) {}

// Update wedding details
// Update wedding details
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_wedding'])) {
    $bride = trim($_POST['bride_name']);
    $groom = trim($_POST['groom_name']);
    $date  = $_POST['wedding_date'];
    $venue = trim($_POST['venue']);
    $template = isset($_POST['template_name']) ? $_POST['template_name'] : 'premium_gold';

    if (!empty($bride) && !empty($groom) && !empty($date) && !empty($venue)) {

        // Get current bride/groom to check if names actually changed
        $stmtOld = $pdo->prepare("SELECT bride_name, groom_name, slug FROM weddings WHERE id = ? AND user_id = ?");
        $stmtOld->execute([$wedding_id, $user_id]);
        $old = $stmtOld->fetch();

        $slug_to_use = $old['slug'];
        $link_changed = false;

        // Only regenerate slug if bride or groom name actually changed
        if ($old['bride_name'] !== $bride || $old['groom_name'] !== $groom) {
            $new_slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $bride . '-' . $groom));
            $new_slug = trim($new_slug, '-');

            // Ensure uniqueness, excluding this wedding's own row
            $checkSlug = $pdo->prepare("SELECT COUNT(*) FROM weddings WHERE slug = ? AND id != ?");
            $checkSlug->execute([$new_slug, $wedding_id]);
            if ($checkSlug->fetchColumn() > 0) {
                $new_slug .= '-' . rand(100, 999);
            }

            $slug_to_use = $new_slug;
            $link_changed = true;
        }

        $pdo->prepare("UPDATE weddings SET bride_name = ?, groom_name = ?, wedding_date = ?, venue = ?, template_name = ?, slug = ? WHERE id = ? AND user_id = ?")
            ->execute([$bride, $groom, $date, $venue, $template, $slug_to_use, $wedding_id, $user_id]);

        $new_name = $bride . " & " . $groom;
        $pdo->prepare("UPDATE users SET name = ? WHERE id = ?")
            ->execute([$new_name, $user_id]);
        $_SESSION['user_name'] = $new_name;

        if ($link_changed) {
            $msg_wedding = "<div class='flash flash-success'><i class='fas fa-check-circle'></i> Wedding details updated! Your invitation link has changed — please re-share the new link with guests.</div>";
        } else {
            $msg_wedding = "<div class='flash flash-success'><i class='fas fa-check-circle'></i> Wedding details updated!</div>";
        }
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

// Delete Account
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_account'])) {
    $del_pw = $_POST['delete_password'];
    $stmtPw = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmtPw->execute([$user_id]);
    $user_data = $stmtPw->fetch();

    if ($user_data && password_verify($del_pw, $user_data['password'])) {
        // Delete related data manually to ensure complete wipe if ON DELETE CASCADE is not set
        $pdo->prepare("DELETE FROM guest_gallery WHERE wedding_id = ?")->execute([$wedding_id]);
        $pdo->prepare("DELETE FROM gallery WHERE wedding_id = ?")->execute([$wedding_id]);
        $pdo->prepare("DELETE FROM events WHERE wedding_id = ?")->execute([$wedding_id]);
        $pdo->prepare("DELETE FROM guests WHERE wedding_id = ?")->execute([$wedding_id]);
        $pdo->prepare("DELETE FROM tasks WHERE wedding_id = ?")->execute([$wedding_id]);
        $pdo->prepare("DELETE FROM weddings WHERE id = ?")->execute([$wedding_id]);
        $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$user_id]);

        session_destroy();
        header("Location: ../login.php?deleted=1");
        exit();
    } else {
        $msg_delete = "<div class='flash flash-error'><i class='fas fa-times-circle'></i> Incorrect password. Account not deleted.</div>";
    }
}

// Load current wedding data
$stmtGetWed = $pdo->prepare("SELECT bride_name, groom_name, wedding_date, venue, hero_image, template_name FROM weddings WHERE id = ? AND user_id = ?");
$stmtGetWed->execute([$wedding_id, $user_id]);
$wedding_data = $stmtGetWed->fetch();

require '../layouts/header.php';
?>

<style>
    /* ===== Flash Messages ===== */
    .flash { padding: 13px 18px; border-radius: 12px; font-size: 0.87rem; margin-bottom: 16px; display:flex; align-items:center; gap:10px; }
    .flash-success { background: rgba(34,197,94,0.1); border:1px solid rgba(34,197,94,0.25); color: #16a34a; }
    .flash-error   { background: rgba(239,68,68,0.1); border:1px solid rgba(239,68,68,0.25); color: #dc2626; }

    /* ===== Accordion Wrapper ===== */
    .settings-accordion { display: flex; flex-direction: column; gap: 12px; max-width: 800px; margin: 0 auto; }

    /* ===== Accordion Item ===== */
    .acc-item {
        background: #ffffff;
        border: 1.5px solid #eef0f5;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 2px 12px rgba(0,0,0,0.04);
        transition: box-shadow 0.3s;
    }
    .acc-item.open { box-shadow: 0 6px 28px rgba(0,0,0,0.09); }
    .acc-item.danger-item { border-color: rgba(239,68,68,0.25); }

    /* ===== Accordion Header (clickable) ===== */
    .acc-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 18px 22px;
        cursor: pointer;
        background: #fafbfc;
        border: none;
        width: 100%;
        text-align: left;
        transition: background 0.2s;
        gap: 14px;
    }
    .acc-header:hover { background: #f4f6fa; }
    .acc-item.danger-item .acc-header { background: rgba(254,242,242,0.6); }
    .acc-item.danger-item .acc-header:hover { background: rgba(254,226,226,0.7); }

    .acc-header-left { display: flex; align-items: center; gap: 14px; }
    .acc-icon {
        width: 42px; height: 42px; border-radius: 11px;
        background: rgba(201,169,110,0.12);
        display: flex; align-items: center; justify-content: center;
        color: #c9a96e; font-size: 1rem; flex-shrink: 0;
    }
    .acc-item.danger-item .acc-icon { background: rgba(239,68,68,0.1); color: #dc2626; }

    .acc-title { font-size: 0.97rem; font-weight: 700; color: #1a1a2e; letter-spacing: -0.1px; }
    .acc-sub   { font-size: 0.78rem; color: #64748b; margin-top: 2px; }
    .acc-item.danger-item .acc-title { color: #b91c1c; }

    .acc-chevron {
        width: 30px; height: 30px; border-radius: 8px;
        background: rgba(201,169,110,0.08);
        display: flex; align-items: center; justify-content: center;
        color: #c9a96e; font-size: 0.8rem;
        transition: transform 0.3s ease, background 0.2s;
        flex-shrink: 0;
    }
    .acc-item.open .acc-chevron { transform: rotate(180deg); background: rgba(201,169,110,0.18); }
    .acc-item.danger-item .acc-chevron { background: rgba(239,68,68,0.08); color: #dc2626; }

    /* ===== Accordion Body ===== */
    .acc-body {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.4s cubic-bezier(0.4,0,0.2,1), padding 0.3s ease;
        padding: 0 22px;
    }
    .acc-item.open .acc-body { max-height: 1200px; padding: 22px 22px 26px; }

    /* ===== Form Fields ===== */
    .form-field { margin-bottom: 18px; }
    .form-field label {
        display: block; font-size: 0.72rem; font-weight: 600;
        color: #94a3b8; text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 7px;
    }
    .form-field .input-wrap { position: relative; }
    .form-field .input-wrap i {
        position: absolute; left: 13px; top: 50%; transform: translateY(-50%);
        color: #cbd5e1; font-size: 0.85rem; pointer-events: none;
    }
    .form-field input, .form-field select {
        width: 100%; border: 1.5px solid #e2e8f0; border-radius: 10px;
        padding: 12px 14px 12px 40px; font-family: 'Inter', sans-serif;
        font-size: 0.9rem; color: #1a1a2e; background: #f8fafc;
        outline: none; transition: all 0.25s ease; appearance: none;
    }
    .form-field input:focus, .form-field select:focus {
        border-color: #c9a96e; background: #ffffff;
        box-shadow: 0 0 0 4px rgba(201,169,110,0.12);
    }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
    @media (max-width: 600px) { .form-row { grid-template-columns: 1fr; } }

    /* ===== Buttons ===== */
    .btn-save {
        background: linear-gradient(135deg, #1a1a2e, #2d2d50); color: #c9a96e;
        border: none; border-radius: 10px; padding: 12px 28px;
        font-family: 'Inter', sans-serif; font-size: 0.88rem; font-weight: 700;
        cursor: pointer; transition: all 0.25s; display: inline-flex;
        align-items: center; gap: 8px;
    }
    .btn-save:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(26,26,46,0.3); }
    .btn-danger-trigger {
        background: transparent; color: #dc2626; border: 1.5px solid rgba(220,38,38,0.4);
        border-radius: 10px; padding: 11px 22px; font-family: 'Inter', sans-serif;
        font-size: 0.87rem; font-weight: 600; cursor: pointer; transition: all 0.25s;
        display: inline-flex; align-items: center; gap: 8px;
    }
    .btn-danger-trigger:hover { background: #dc2626; color: white; border-color: #dc2626; }

    /* ===== Delete Modal ===== */
    .del-modal-backdrop {
        display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.55);
        z-index: 9999; backdrop-filter: blur(4px); align-items: center; justify-content: center;
    }
    .del-modal-backdrop.active { display: flex; }
    .del-modal {
        background: #fff; border-radius: 20px; padding: 36px 32px;
        max-width: 440px; width: 90%; box-shadow: 0 25px 60px rgba(0,0,0,0.2);
        animation: modalIn 0.3s ease;
    }
    @keyframes modalIn { from { opacity:0; transform: scale(0.9) translateY(10px); } to { opacity:1; transform: scale(1) translateY(0); } }
    .del-modal-icon {
        width: 58px; height: 58px; border-radius: 50%; background: rgba(239,68,68,0.1);
        display: flex; align-items: center; justify-content: center;
        color: #dc2626; font-size: 1.5rem; margin: 0 auto 18px;
    }
    .del-modal h3 { font-size: 1.15rem; font-weight: 700; color: #1a1a2e; text-align: center; margin-bottom: 8px; }
    .del-modal p { font-size: 0.86rem; color: #64748b; text-align: center; line-height: 1.6; margin-bottom: 22px; }
    .del-modal-actions { display: flex; gap: 10px; }
    .btn-modal-cancel {
        flex: 1; background: #f1f5f9; color: #475569; border: none; border-radius: 10px;
        padding: 12px; font-family: 'Inter', sans-serif; font-size: 0.88rem; font-weight: 600;
        cursor: pointer; transition: background 0.2s;
    }
    .btn-modal-cancel:hover { background: #e2e8f0; }
    .btn-modal-delete {
        flex: 1; background: #dc2626; color: white; border: none; border-radius: 10px;
        padding: 12px; font-family: 'Inter', sans-serif; font-size: 0.88rem; font-weight: 700;
        cursor: pointer; transition: all 0.2s;
    }
    .btn-modal-delete:hover { background: #b91c1c; }

    /* ===== Divider ===== */
    .section-divider { margin: 6px 0 10px; border: none; border-top: 1px solid #f1f5f9; }
</style>

<?php
// Determine which accordion should auto-open on page load (after POST)
$open_section = 'wedding'; // default
if (!empty($msg_pw))     $open_section = 'password';
if (!empty($msg_delete)) $open_section = 'danger';
?>

<div class="settings-accordion">

    <!-- ======== 1. Wedding Details ======== -->
    <div class="acc-item <?php echo $open_section === 'wedding' || !empty($msg_wedding) ? 'open' : ''; ?>" id="acc-wedding">
        <button type="button" class="acc-header" onclick="toggleAcc('acc-wedding')">
            <div class="acc-header-left">
                <div class="acc-icon"><i class="fas fa-heart"></i></div>
                <div>
                    <div class="acc-title">Wedding Details</div>
                    <div class="acc-sub">Update couple names, date, venue & invitation template</div>
                </div>
            </div>
            <div class="acc-chevron"><i class="fas fa-chevron-down"></i></div>
        </button>
        <div class="acc-body">
            <?php echo $msg_wedding; ?>
            <form method="POST" action="settings.php" enctype="multipart/form-data">
                <div class="form-row">
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
                </div>
                <div class="form-row">
                    <div class="form-field">
                        <label>Wedding Date</label>
                        <div class="input-wrap">
                            <i class="far fa-calendar"></i>
                            <input type="date" name="wedding_date"
                                value="<?php echo htmlspecialchars($wedding_data['wedding_date'] ?? ''); ?>" required>
                        </div>
                    </div>
                    <div class="form-field">
                        <label>Main Venue</label>
                        <div class="input-wrap">
                            <i class="fas fa-map-marker-alt"></i>
                            <input type="text" name="venue" id="venue"
                                value="<?php echo htmlspecialchars($wedding_data['venue'] ?? ''); ?>"
                                placeholder="Hotel or location name" required>
                        </div>
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
                            <option value="royal_classic" <?php echo ($wedding_data['template_name'] == 'royal_classic') ? 'selected' : ''; ?>>Royal Classic (Royal Theme)</option>
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

    <!-- ======== 2. Change Password ======== -->
    <div class="acc-item <?php echo $open_section === 'password' ? 'open' : ''; ?>" id="acc-password">
        <button type="button" class="acc-header" onclick="toggleAcc('acc-password')">
            <div class="acc-header-left">
                <div class="acc-icon"><i class="fas fa-key"></i></div>
                <div>
                    <div class="acc-title">Change Password</div>
                    <div class="acc-sub">Keep your account secure with a strong password</div>
                </div>
            </div>
            <div class="acc-chevron"><i class="fas fa-chevron-down"></i></div>
        </button>
        <div class="acc-body">
            <?php echo $msg_pw; ?>
            <form method="POST" action="settings.php">
                <div class="form-field">
                    <label>Current Password</label>
                    <div class="input-wrap">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="current_password" placeholder="••••••••" required>
                    </div>
                </div>
                <div class="form-row">
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
                </div>
                <button type="submit" name="update_password" class="btn-save">
                    <i class="fas fa-shield-alt"></i> Update Password
                </button>
            </form>
        </div>
    </div>

    <!-- ======== 3. Danger Zone ======== -->
    <div class="acc-item danger-item <?php echo $open_section === 'danger' ? 'open' : ''; ?>" id="acc-danger">
        <button type="button" class="acc-header" onclick="toggleAcc('acc-danger')">
            <div class="acc-header-left">
                <div class="acc-icon"><i class="fas fa-skull-crossbones"></i></div>
                <div>
                    <div class="acc-title">Danger Zone</div>
                    <div class="acc-sub">Permanently delete your account and all associated data</div>
                </div>
            </div>
            <div class="acc-chevron"><i class="fas fa-chevron-down"></i></div>
        </button>
        <div class="acc-body">
            <?php echo $msg_delete; ?>
            <p style="font-size:0.88rem; color:#64748b; line-height:1.7; margin-bottom:20px; background:#fef2f2; border:1px solid rgba(220,38,38,0.12); border-radius:10px; padding:14px 16px;">
                <i class="fas fa-exclamation-triangle" style="color:#dc2626; margin-right:6px;"></i>
                Deleting your account is <strong>permanent and irreversible</strong>. Your digital invitation, guest list, RSVPs, love story, events, and photo galleries will be completely and permanently removed from our servers.
            </p>
            <button type="button" class="btn-danger-trigger" onclick="document.getElementById('deleteModal').classList.add('active')">
                <i class="fas fa-trash-alt"></i> Delete My Account
            </button>
        </div>
    </div>

</div>

<!-- ======== Delete Confirmation Modal ======== -->
<div class="del-modal-backdrop" id="deleteModal">
    <div class="del-modal">
        <div class="del-modal-icon"><i class="fas fa-trash-alt"></i></div>
        <h3>Delete Your Account?</h3>
        <p>This will permanently remove your invitation, guest list, RSVPs, gallery, and all related data. This action <strong>cannot be undone</strong>. Enter your password to confirm.</p>
        <?php if (!empty($msg_delete)) echo $msg_delete; ?>
        <form method="POST" action="settings.php" id="deleteForm">
            <div class="form-field" style="margin-bottom:18px;">
                <label>Your Current Password</label>
                <div class="input-wrap">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="delete_password" id="delete_password" placeholder="••••••••" required autocomplete="current-password">
                </div>
            </div>
            <div class="del-modal-actions">
                <button type="button" class="btn-modal-cancel" onclick="document.getElementById('deleteModal').classList.remove('active')">
                    <i class="fas fa-times" style="margin-right:5px;"></i> Cancel
                </button>
                <button type="submit" name="delete_account" class="btn-modal-delete">
                    <i class="fas fa-trash-alt" style="margin-right:5px;"></i> Yes, Delete
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // ====== Accordion Toggle ======
    function toggleAcc(id) {
        const item = document.getElementById(id);
        const isOpen = item.classList.contains('open');
        // Close all first
        document.querySelectorAll('.acc-item').forEach(el => el.classList.remove('open'));
        // Toggle the clicked one
        if (!isOpen) item.classList.add('open');
    }

    // Close modal on backdrop click
    document.getElementById('deleteModal').addEventListener('click', function(e) {
        if (e.target === this) this.classList.remove('active');
    });

    // Auto-open modal if there was a delete error on POST
    <?php if (!empty($msg_delete)): ?>
    window.addEventListener('DOMContentLoaded', function() {
        document.getElementById('deleteModal').classList.add('active');
        document.getElementById('acc-danger').classList.add('open');
    });
    <?php endif; ?>
</script>

<?php require '../layouts/footer.php'; ?>
