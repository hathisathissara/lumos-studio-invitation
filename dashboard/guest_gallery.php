<?php
session_start();
require '../config/config.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['wedding_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$wedding_id = $_SESSION['wedding_id'];
$msg = "";

// 1. පරිශීලකයාගේ පැකේජය අනුව Guest Gallery සක්‍රීයදැයි බැලීම
$stmtStatus = $pdo->prepare("SELECT package, has_guest_gallery FROM users WHERE id = ?");
$stmtStatus->execute([$user_id]);
$user_plan = $stmtStatus->fetch();

$has_guest_gallery = ($user_plan['package'] === 'premium' || intval($user_plan['has_guest_gallery']) === 1);

// 2. අමුත්තෙක් එවූ පින්තූරයක් mකා දැමීම
if ($has_guest_gallery && isset($_GET['delete_img'])) {
    $img_id = intval($_GET['delete_img']);
    
    $stmtImg = $pdo->prepare("SELECT image_path FROM guest_gallery WHERE id = ? AND wedding_id = ?");
    $stmtImg->execute([$img_id, $wedding_id]);
    $img = $stmtImg->fetch();
    
    if ($img) {
        $physical_path = '../' . $img['image_path'];
        
        if (file_exists($physical_path)) {
            unlink($physical_path);
        }
        
        $stmtDel = $pdo->prepare("DELETE FROM guest_gallery WHERE id = ?");
        $stmtDel->execute([$img_id]);
        
        $msg = "<div class='flash flash-success'><i class='fas fa-check-circle'></i> Photo deleted successfully.</div>";
    }
}

// 3. අමුත්තන් අප්ලෝඩ් කල සියලුම පින්තූර DB එකෙන් ලබාගැනීම
$guest_images = [];
if ($has_guest_gallery) {
    $stmtGet = $pdo->prepare("SELECT * FROM guest_gallery WHERE wedding_id = ? ORDER BY id DESC");
    $stmtGet->execute([$wedding_id]);
    $guest_images = $stmtGet->fetchAll();
}

require 'layouts/header.php';
?>

<style>
    .flash { padding: 13px 18px; border-radius: 12px; font-size: 0.87rem; margin-bottom: 20px; display:flex; align-items:center; gap:10px; }
    .flash-success { background: rgba(34,197,94,0.1); border:1px solid rgba(34,197,94,0.25); color: #16a34a; }
    
    .gallery-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 16px; }
    .gallery-item-card { background: white; border: 1px solid #e8ecf0; border-radius: 14px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.03); transition: transform 0.2s; display: flex; flex-direction: column; }
    .gallery-item-card:hover { transform: translateY(-2px); }
    
    .img-container { height: 160px; overflow: hidden; position: relative; }
    .img-container img { width: 100%; height: 100%; object-fit: cover; }
    
    .meta-container { padding: 12px; text-align: left; flex-grow: 1; }
    .meta-uploader { font-size: 0.82rem; font-weight: 700; color: #1a1a2e; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .meta-date { font-size: 0.7rem; color: #9ea3b0; margin-top: 2px; }
    
    /* Payout/Download Button Actions */
    .action-button-row { display: flex; gap: 6px; padding: 0 12px 12px; }
    
    .btn-download-moment { flex: 1; background: rgba(34,197,94,0.08); border: 1px solid rgba(34,197,94,0.2); color: #16a34a; padding: 8px; border-radius: 8px; font-size: 0.75rem; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 4px; transition: background 0.2s; text-decoration: none; }
    .btn-download-moment:hover { background: #16a34a; color: white; }

    .btn-delete-moment { flex: 1; border: 1px solid rgba(225,29,72,0.15); background: #fff1f2; color: #e11d48; padding: 8px; border-radius: 8px; font-size: 0.75rem; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 4px; transition: background 0.2s; text-decoration: none; }
    .btn-delete-moment:hover { background: #ffe4e6; }
    
    .locked-upgrade-card { background: white; border: 1px solid #e8ecf0; border-radius: 20px; padding: 40px; text-align: center; max-width: 580px; margin: 40px auto; }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Guest Shared Photos (අමුත්තන් එවූ ඡායාරූප)</h2>
</div>

<?php if ($msg) echo $msg; ?>

<?php if (!$has_guest_gallery): ?>
    <!-- 🔒 UPGRADE LOCK BANNER -->
    <div class="locked-upgrade-card">
        <i class="fas fa-lock text-warning" style="font-size: 3rem; margin-bottom: 20px;"></i>
        <h4 class="fw-bold text-dark mb-2">Guest Gallery Support is Locked</h4>
        <p class="text-muted small mb-4">අමුත්තන්ට සජීවීව පින්තූර අප්ලෝඩ් කිරීමට සහ ඒවා කළමනාකරණය කිරීමට ඇති මෙම සුවිශේෂී පහසුකම ක්‍රියාත්මක වන්නේ <strong>Premium Plan</strong> එකෙහි හෝ <strong>Guest Gallery Add-on</strong> එක මිලදී ගත් අයට පමණි.</p>
        <a href="payment.php" class="topbar-btn topbar-btn-gold py-2 px-4 text-decoration-none">
            <i class="fas fa-arrow-circle-up"></i> Upgrade Plan / Activate Add-on
        </a>
    </div>

<?php else: ?>
    <!-- 📸 ACTUALLY ACTIVE: PHOTO GRID DISPLAY -->
    <div class="card card-custom bg-white p-4">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h5 class="m-0">Shared Moments (<?php echo count($guest_images); ?> Photos)</h5>
            <span class="badge bg-success" style="font-size: 0.72rem; padding: 5px 12px;"><i class="fas fa-check-circle"></i> Live Sharing Active</span>
        </div>

        <?php if (count($guest_images) > 0): ?>
            <div class="gallery-grid">
                <?php foreach ($guest_images as $g_pic): ?>
                    <div class="gallery-item-card">
                        <div class="img-container">
                            <img src="../<?php echo htmlspecialchars($g_pic['image_path']); ?>" alt="Guest upload">
                        </div>
                        <div class="meta-container">
                            <div class="meta-uploader" title="<?php echo htmlspecialchars($g_pic['guest_name']); ?>">
                                <i class="far fa-user text-muted me-1"></i> By <?php echo htmlspecialchars($g_pic['guest_name']); ?>
                            </div>
                            <div class="meta-date">
                                <i class="far fa-clock text-muted me-1"></i> <?php echo date("d M, h:i A", strtotime($g_pic['uploaded_at'])); ?>
                            </div>
                        </div>
                        
                        <!-- Action buttons row (Download JPG සහ Delete) -->
                        <div class="action-button-row">
                            <!-- 📥 WebP to JPG Dynamic Converter Download බටන් එක -->
                            <a href="download_jpg.php?id=<?php echo $g_pic['id']; ?>" class="btn-download-moment" title="Download as high-quality JPG image">
                                <i class="fas fa-download"></i> JPG
                            </a>
                            
                            <!-- 🗑️ Delete බටන් එක -->
                            <a href="guest_gallery.php?delete_img=<?php echo $g_pic['id']; ?>" 
                               class="btn-delete-moment"
                               onclick="return confirm('මෙම ඡායාරූපය සදහටම මකා දැමීමට අවශ්‍ය බව විශ්වාසද?');"
                               title="Permanently delete from platform">
                                <i class="fas fa-trash-alt"></i> Delete
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center text-muted py-5" style="font-style: italic;">
                <i class="fas fa-camera-retro mb-2" style="font-size: 2.2rem; opacity: 0.3; display:block; margin: 0 auto;"></i>
                තවමත් කිසිදු අමුත්තෙක් ඡායාරූපයක් අප්ලෝඩ් කර නැත. <br>විවාහ උත්සවය දවසේදී අමුත්තන් ලින්ක් එකෙන් පින්තූර එවූ පසු ඒවා මෙහි දිස්වේවි!
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php require 'layouts/footer.php'; ?>