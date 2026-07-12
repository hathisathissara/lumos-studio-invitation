<?php
session_start();
require '../../config/config.php';
require '../../config/mailer.php';

// Admin only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET['uid'])) {
    header("Location: index.php");
    exit();
}

if (!isset($_GET['csrf_token']) || $_GET['csrf_token'] !== $_SESSION['csrf_token']) {
    die("CSRF token validation failed.");
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
    header("Location: index.php");
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
                $full = '../../' . $imgPath;
                if (file_exists($full)) unlink($full);
            }
            $pdo->prepare("DELETE FROM gallery WHERE wedding_id = ?")->execute([$wedding_id]);

            // B. Delete Guest Photo Gallery images from disk & DB
            $stmtGuestGallery = $pdo->prepare("SELECT image_path FROM guest_gallery WHERE wedding_id = ?");
            $stmtGuestGallery->execute([$wedding_id]);
            $guestGalleryImages = $stmtGuestGallery->fetchAll(PDO::FETCH_COLUMN);
            foreach ($guestGalleryImages as $gImgPath) {
                $gFull = '../../' . $gImgPath;
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
        if (!empty($slipPath) && file_exists('../../' . $slipPath)) {
            unlink('../../' . $slipPath);
        }

        // F. Delete user account
        $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$u_id]);

        $pdo->commit();

        send_deletion_confirmed_mail($coupleInfo['email'], $coupleInfo['name']);

        header("Location: index.php?deleted=1");
        exit();

    } catch (Exception $e) {
        $pdo->rollBack();
        $deleteError = "Delete failed: " . $e->getMessage();
    }
}

require '../layouts/header.php';
?>

<style>
    :root {
        --danger: #ef4444;
        --danger-hover: #dc2626;
        --danger-bg: rgba(239, 68, 68, 0.05);
        --text-dark: #1e293b;
        --text-muted: #64748b;
        --border-color: #e2e8f0;
        --bg-light: #f8fafc;
        --primary: #1a1a2e;
    }

    .delete-confirm-card {
        background: #ffffff;
        border-radius: 20px;
        padding: 44px 40px;
        max-width: 560px;
        margin: 20px auto;
        border: 1px solid #fee2e2;
        box-shadow: 0 10px 25px -5px rgba(239, 68, 68, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.01);
        text-align: center;
        animation: fadeIn 0.3s ease-out;
    }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }

    .delete-icon {
        width: 76px; height: 76px;
        background: rgba(239, 68, 68, 0.08);
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 24px;
        font-size: 2rem;
        color: var(--danger);
        animation: pulseDanger 2s infinite;
    }
    @keyframes pulseDanger {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.04); background: rgba(239, 68, 68, 0.12); }
    }

    .delete-title {
        font-family: 'Fraunces', serif;
        font-size: 1.45rem;
        font-weight: 700;
        color: var(--primary);
        margin-bottom: 8px;
    }
    .delete-subtitle {
        font-size: 0.86rem;
        color: var(--text-muted);
        margin-bottom: 30px;
        line-height: 1.5;
    }

    /* Minimalist Info Row */
    .delete-info-group {
        display: flex;
        flex-direction: column;
        gap: 12px;
        margin-bottom: 24px;
    }
    .delete-info-row {
        background: var(--bg-light);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 14px 18px;
        display: flex;
        gap: 14px;
        align-items: center;
        text-align: left;
    }
    .delete-info-icon {
        width: 38px; height: 38px;
        border-radius: 10px;
        background: #ffffff;
        border: 1px solid var(--border-color);
        display: flex; align-items: center; justify-content: center;
        color: var(--gold);
        font-size: 0.95rem;
        flex-shrink: 0;
    }
    .delete-info-label { font-size: 0.68rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.8px; font-weight: 600; }
    .delete-info-value { font-size: 0.9rem; color: var(--primary); font-weight: 700; margin-top: 1px; }

    /* Danger Warning List */
    .delete-warning-list {
        background: var(--danger-bg);
        border: 1px solid rgba(239, 68, 68, 0.15);
        border-radius: 14px;
        padding: 18px 22px;
        margin-bottom: 32px;
        text-align: left;
    }
    .delete-warning-list p {
        font-size: 0.82rem;
        font-weight: 800;
        color: var(--danger-hover);
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .delete-warning-list ul { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 8px; }
    .delete-warning-list li {
        font-size: 0.82rem;
        color: #475569;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .delete-warning-list li i { color: var(--danger); font-size: 0.65rem; opacity: 0.8; }

    /* Action Buttons Design */
    .delete-actions { display: flex; gap: 14px; }
    .btn-cancel-del {
        flex: 1;
        padding: 14px;
        border: 1px solid var(--border-color);
        border-radius: 12px;
        background: #ffffff;
        color: var(--text-muted);
        font-size: 0.88rem;
        font-weight: 700;
        cursor: pointer;
        text-align: center;
        text-decoration: none;
        display: flex; align-items: center; justify-content: center; gap: 6px;
        transition: all 0.25s ease;
    }
    .btn-cancel-del:hover { background: var(--bg-light); color: var(--primary); border-color: var(--text-muted); }
    
    .btn-confirm-del {
        flex: 1;
        padding: 14px;
        border: none;
        border-radius: 12px;
        background: linear-gradient(135deg, #ef4444, #b91c1c);
        color: white;
        font-size: 0.88rem;
        font-weight: 700;
        cursor: pointer;
        display: flex; align-items: center; justify-content: center; gap: 6px;
        transition: all 0.25s ease;
        font-family: 'Inter', sans-serif;
        box-shadow: 0 4px 12px rgba(239,68,68,0.2);
    }
    .btn-confirm-del:hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(239,68,68,0.35); }
</style>

<div class="delete-confirm-card">
    <div class="delete-icon"><i class="fas fa-exclamation-triangle"></i></div>
    <div class="delete-title">Delete Account Permanently?</div>
    <div class="delete-subtitle">This action cannot be undone. All database records and physical image files on the server will be permanently removed.</div>

    <!-- Minimalist Info Group -->
    <div class="delete-info-group">
        <div class="delete-info-row">
            <div class="delete-info-icon"><i class="fas fa-heart"></i></div>
            <div>
                <div class="delete-info-label">Couple Names</div>
                <div class="delete-info-value"><?php echo htmlspecialchars($coupleInfo['bride_name'] . ' & ' . $coupleInfo['groom_name']); ?></div>
            </div>
        </div>
        <div class="delete-info-row">
            <div class="delete-info-icon"><i class="fas fa-envelope"></i></div>
            <div>
                <div class="delete-info-label">Email Address</div>
                <div class="delete-info-value"><?php echo htmlspecialchars($coupleInfo['email']); ?></div>
            </div>
        </div>
        <div class="delete-info-row">
            <div class="delete-info-icon"><i class="far fa-calendar-alt"></i></div>
            <div>
                <div class="delete-info-label">Wedding Date</div>
                <div class="delete-info-value"><?php echo $coupleInfo['wedding_date'] ? date("d F Y", strtotime($coupleInfo['wedding_date'])) : '—'; ?></div>
            </div>
        </div>
    </div>

    <!-- Warning Summary -->
    <div class="delete-warning-list">
        <p><i class="fas fa-shield-alt"></i> Destructive Deletion Checkpoint:</p>
        <ul>
            <li><i class="fas fa-circle"></i> User credentials & system authorizations</li>
            <li><i class="fas fa-circle"></i> Complete wedding profile metadata</li>
            <li><i class="fas fa-circle"></i> All guest reservations & RSVP configurations</li>
            <li><i class="fas fa-circle"></i> Full schedules and event locations</li>
            <li><i class="fas fa-circle"></i> Couple engagement moments (files deleted)</li>
            <li><i class="fas fa-circle"></i> Guest shared candid photos (files deleted)</li>
            <li><i class="fas fa-circle"></i> Planning progress, budgets, & checklist logs</li>
        </ul>
    </div>

    <?php if (isset($deleteError)): ?>
        <div class="error-msg">
            <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($deleteError); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="admin_delete_account.php?uid=<?php echo $u_id; ?>">
        <div class="delete-actions">
            <a href="admin_dashboard.php" class="btn-cancel-del">
                <i class="fas fa-arrow-left"></i> Cancel, Safe back
            </a>
            <button type="submit" name="confirm_delete" value="1" class="btn-confirm-del">
                <i class="fas fa-trash-alt"></i> Yes, Delete Everything
            </button>
        </div>
    </form>
</div>

<?php require '../layouts/footer.php'; ?>
