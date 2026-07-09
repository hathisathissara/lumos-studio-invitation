<?php
session_start();
require '../config/config.php';
require '../config/mailer.php';

// Admin only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['uid'])) {
    header("Location: admin_dashboard.php");
    exit();
}

$u_id = intval($_GET['uid']);

$stmtInfo = $pdo->prepare("
    SELECT u.name, u.email, w.bride_name, w.groom_name, w.wedding_date, w.id as wedding_id
    FROM users u
    LEFT JOIN weddings w ON u.id = w.user_id
    WHERE u.id = ?
");
$stmtInfo->execute([$u_id]);
$coupleInfo = $stmtInfo->fetch();

if (!$coupleInfo) {
    header("Location: admin_dashboard.php");
    exit();
}

// Process deletion (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {

    try {
        $pdo->beginTransaction();

        $stmtWed = $pdo->prepare("SELECT id, slug FROM weddings WHERE user_id = ?");
        $stmtWed->execute([$u_id]);
        $wedding = $stmtWed->fetch();
        $wedding_id = $wedding ? $wedding['id'] : null;

        $stmtSlip = $pdo->prepare("SELECT payment_slip FROM users WHERE id = ?");
        $stmtSlip->execute([$u_id]);
        $slipPath = $stmtSlip->fetchColumn();

        if ($wedding_id) {
            // A. Delete Couple's Photo Gallery from disk
            $stmtGallery = $pdo->prepare("SELECT image_path FROM gallery WHERE wedding_id = ?");
            $stmtGallery->execute([$wedding_id]);
            $galleryImages = $stmtGallery->fetchAll(PDO::FETCH_COLUMN);
            foreach ($galleryImages as $imgPath) {
                $full = '../' . $imgPath;
                if (file_exists($full)) unlink($full);
            }
            $pdo->prepare("DELETE FROM gallery WHERE wedding_id = ?")->execute([$wedding_id]);

            // =====================================================================
            // 📸 🔥 B. NEW: Delete Guest Photo Gallery images from disk & DB
            // =====================================================================
            $stmtGuestGallery = $pdo->prepare("SELECT image_path FROM guest_gallery WHERE wedding_id = ?");
            $stmtGuestGallery->execute([$wedding_id]);
            $guestGalleryImages = $stmtGuestGallery->fetchAll(PDO::FETCH_COLUMN);
            foreach ($guestGalleryImages as $gImgPath) {
                $gFull = '../' . $gImgPath;
                if (file_exists($gFull)) unlink($gFull);
            }
            $pdo->prepare("DELETE FROM guest_gallery WHERE wedding_id = ?")->execute([$wedding_id]);

            // C. Delete Events, Guests, and Tasks
            $pdo->prepare("DELETE FROM events WHERE wedding_id = ?")->execute([$wedding_id]);
            $pdo->prepare("DELETE FROM guests WHERE wedding_id = ?")->execute([$wedding_id]);
            $pdo->prepare("DELETE FROM tasks WHERE wedding_id = ?")->execute([$wedding_id]);

            // D. Delete Wedding Profile
            $pdo->prepare("DELETE FROM weddings WHERE id = ?")->execute([$wedding_id]);
        }

        // E. Delete payment slip from disk
        if (!empty($slipPath) && file_exists('../' . $slipPath)) {
            unlink('../' . $slipPath);
        }

        // F. Delete user account
        $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$u_id]);

        $pdo->commit();

        send_deletion_confirmed_mail($coupleInfo['email'], $coupleInfo['name']);

        header("Location: admin_dashboard.php?deleted=1");
        exit();

    } catch (Exception $e) {
        $pdo->rollBack();
        $deleteError = "Delete failed: " . $e->getMessage();
    }
}

require 'layouts/header.php';
?>

<style>
    .delete-confirm-card { background: white; border-radius: 20px; padding: 40px; max-width: 540px; margin: 0 auto; border: 1px solid #fee2e2; box-shadow: 0 8px 30px rgba(239, 68, 68, 0.08); }
    .delete-icon { width: 72px; height: 72px; background: rgba(239,68,68,0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; font-size: 1.8rem; color: #dc2626; }
    .delete-title { font-size: 1.3rem; font-weight: 700; color: #1a1a2e; text-align: center; margin-bottom: 8px; }
    .delete-subtitle { font-size: 0.88rem; color: #9ea3b0; text-align: center; margin-bottom: 28px; }
    .delete-info-row { background: #f8fafc; border-radius: 12px; padding: 16px 20px; margin-bottom: 20px; display: flex; gap: 12px; align-items: center; }
    .delete-info-icon { width: 36px; height: 36px; border-radius: 8px; background: rgba(201,169,110,0.1); display: flex; align-items: center; justify-content: center; color: #c9a96e; font-size: 0.9rem; flex-shrink: 0; }
    .delete-info-label { font-size: 0.72rem; color: #9ea3b0; text-transform: uppercase; letter-spacing: 0.8px; }
    .delete-info-value { font-size: 0.92rem; color: #1a1a2e; font-weight: 600; }
    .delete-warning-list { background: rgba(239,68,68,0.04); border: 1px solid rgba(239,68,68,0.15); border-radius: 12px; padding: 16px 20px; margin-bottom: 28px; }
    .delete-warning-list p { font-size: 0.8rem; font-weight: 700; color: #dc2626; margin-bottom: 10px; }
    .delete-warning-list ul { list-style: none; padding: 0; margin: 0; }
    .delete-warning-list li { font-size: 0.83rem; color: #6b7280; padding: 4px 0; display: flex; align-items: center; gap: 8px; }
    .delete-warning-list li i { color: #dc2626; font-size: 0.7rem; }
    .delete-actions { display: flex; gap: 12px; }
    .btn-cancel-del { flex: 1; padding: 13px; border: 1px solid #e8ecf0; border-radius: 12px; background: white; color: #6b7280; font-size: 0.88rem; font-weight: 600; cursor: pointer; text-align: center; text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 6px; transition: all 0.2s; }
    .btn-cancel-del:hover { background: #f8fafc; color: #1a1a2e; }
    .btn-confirm-del { flex: 1; padding: 13px; border: none; border-radius: 12px; background: linear-gradient(135deg, #ef4444, #dc2626); color: white; font-size: 0.88rem; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px; transition: all 0.2s; font-family: 'Inter', sans-serif; }
    .btn-confirm-del:hover { transform: translateY(-2px); box-shadow: 0 8px 20 rgba(239,68,68,0.35); }
</style>

<div class="delete-confirm-card">
    <div class="delete-icon"><i class="fas fa-trash-alt"></i></div>
    <div class="delete-title">Delete Account Permanently?</div>
    <div class="delete-subtitle">This action cannot be undone. All data will be permanently removed.</div>

    <div class="delete-info-row">
        <div class="delete-info-icon"><i class="fas fa-heart"></i></div>
        <div>
            <div class="delete-info-label">Couple</div>
            <div class="delete-info-value"><?php echo htmlspecialchars($coupleInfo['bride_name'] . ' & ' . $coupleInfo['groom_name']); ?></div>
        </div>
    </div>
    <div class="delete-info-row">
        <div class="delete-info-icon"><i class="fas fa-envelope"></i></div>
        <div>
            <div class="delete-info-label">Email</div>
            <div class="delete-info-value"><?php echo htmlspecialchars($coupleInfo['email']); ?></div>
        </div>
    </div>
    <div class="delete-info-row">
        <div class="delete-info-icon"><i class="far fa-calendar"></i></div>
        <div>
            <div class="delete-info-label">Wedding Date</div>
            <div class="delete-info-value"><?php echo $coupleInfo['wedding_date'] ? date("d F Y", strtotime($coupleInfo['wedding_date'])) : '—'; ?></div>
        </div>
    </div>

    <div class="delete-warning-list">
        <p><i class="fas fa-exclamation-triangle" style="margin-right:6px;"></i> The following will be permanently deleted:</p>
        <ul>
            <li><i class="fas fa-circle"></i> User account & login credentials</li>
            <li><i class="fas fa-circle"></i> Wedding profile & all settings</li>
            <li><i class="fas fa-circle"></i> All guests and RSVP responses</li>
            <li><i class="fas fa-circle"></i> All events / schedule entries</li>
            <li><i class="fas fa-circle"></i> Couple photo gallery (files deleted)</li>
            <li><i class="fas fa-circle"></i> <strong>Guest shared gallery (files deleted)</strong></li>
            <li><i class="fas fa-circle"></i> Wedding checklist & tasks</li>
            <li><i class="fas fa-circle"></i> Payment slip file (if any)</li>
        </ul>
    </div>

    <?php if (isset($deleteError)): ?>
        <div style="background:rgba(239,68,68,0.08); border:1px solid rgba(239,68,68,0.2); border-radius:10px; padding:12px 16px; color:#dc2626; font-size:0.84rem; margin-bottom:16px;">
            <i class="fas fa-exclamation-circle" style="margin-right:6px;"></i><?php echo htmlspecialchars($deleteError); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="admin_delete_account.php?uid=<?php echo $u_id; ?>">
        <div class="delete-actions">
            <a href="admin_dashboard.php" class="btn-cancel-del">
                <i class="fas fa-arrow-left"></i> Cancel
            </a>
            <button type="submit" name="confirm_delete" value="1" class="btn-confirm-del">
                <i class="fas fa-trash-alt"></i> Yes, Delete Everything
            </button>
        </div>
    </form>
</div>

<?php require 'layouts/footer.php'; ?>