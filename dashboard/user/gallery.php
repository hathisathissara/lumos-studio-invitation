<?php
session_start();
require '../../config/config.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['wedding_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id    = $_SESSION['user_id'];
$wedding_id = $_SESSION['wedding_id'];
$msg_story  = "";

// AJAX image upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'upload_image') {
    header('Content-Type: application/json');
    if (isset($_FILES['gallery_image'])) {
        $file = $_FILES['gallery_image'];
        $target_dir = "../uploads/gallery/";
        if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
        $new_filename = uniqid() . '.webp';
        $target_file  = $target_dir . $new_filename;
        if (move_uploaded_file($file['tmp_name'], $target_file)) {
            $db_path = "uploads/gallery/" . $new_filename;
            $stmt = $pdo->prepare("INSERT INTO gallery (wedding_id, image_path) VALUES (?, ?)");
            $stmt->execute([$wedding_id, $db_path]);
            echo json_encode(['success' => true]);
            exit();
        }
    }
    echo json_encode(['success' => false, 'message' => 'Upload failed.']);
    exit();
}

// Update love story
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_story'])) {
    $love_story = trim($_POST['love_story']);
    $stmt = $pdo->prepare("UPDATE weddings SET love_story = ? WHERE id = ? AND user_id = ?");
    if ($stmt->execute([$love_story, $wedding_id, $user_id])) {
        $msg_story = "<div class='flash flash-success'><i class='fas fa-check-circle'></i> Love story saved!</div>";
    }
}

// Delete image
if (isset($_GET['delete_img'])) {
    $img_id = intval($_GET['delete_img']);
    $stmtImg = $pdo->prepare("SELECT image_path FROM gallery WHERE id = ? AND wedding_id = ?");
    $stmtImg->execute([$img_id, $wedding_id]);
    $img = $stmtImg->fetch();
    if ($img) {
        $physical = "../../" . $img['image_path'];
        if (file_exists($physical)) unlink($physical);
        $pdo->prepare("DELETE FROM gallery WHERE id = ?")->execute([$img_id]);
    }
    header("Location: gallery.php");
    exit();
}

// Ensure hero_image column exists
try {
    $pdo->exec("ALTER TABLE weddings ADD COLUMN hero_image VARCHAR(255) DEFAULT NULL");
} catch (Exception $e) {}

// Set cover image
if (isset($_GET['set_cover'])) {
    $img_id = intval($_GET['set_cover']);
    $stmtImg = $pdo->prepare("SELECT image_path FROM gallery WHERE id = ? AND wedding_id = ?");
    $stmtImg->execute([$img_id, $wedding_id]);
    $img = $stmtImg->fetch();
    if ($img) {
        $pdo->prepare("UPDATE weddings SET hero_image = ? WHERE id = ? AND user_id = ?")
            ->execute([$img['image_path'], $wedding_id, $user_id]);
    }
    header("Location: gallery.php");
    exit();
}

// Fetch data
$stmtGetWed = $pdo->prepare("SELECT love_story, hero_image FROM weddings WHERE id = ? AND user_id = ?");
$stmtGetWed->execute([$wedding_id, $user_id]);
$wedding_data = $stmtGetWed->fetch();

$stmtGetImgs = $pdo->prepare("SELECT * FROM gallery WHERE wedding_id = ? ORDER BY id DESC");
$stmtGetImgs->execute([$wedding_id]);
$imagesList = $stmtGetImgs->fetchAll();

require '../layouts/header.php';
?>

<script src="https://cdn.jsdelivr.net/npm/compressorjs@1.2.1/dist/compressor.min.js"></script>

<style>
    .flash { padding: 13px 18px; border-radius: 12px; font-size: 0.87rem; margin-bottom: 20px; display:flex; align-items:center; gap:10px; }
    .flash-success { background: rgba(34,197,94,0.1); border:1px solid rgba(34,197,94,0.25); color: #16a34a; }

    .section-card {
        background: white;
        border: 1px solid #e8ecf0;
        border-radius: 16px;
        padding: 28px;
    }
    .section-card h5 {
        font-size: 0.95rem;
        font-weight: 700;
        color: #1a1a2e;
        margin-bottom: 20px;
        padding-bottom: 14px;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .section-card h5 i { color: #c9a96e; }
    .form-field { margin-bottom: 16px; }
    .form-field label {
        display: block;
        font-size: 0.73rem;
        font-weight: 600;
        color: #9ea3b0;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        margin-bottom: 7px;
    }
    .form-field textarea {
        width: 100%;
        border: 1px solid #e8ecf0;
        border-radius: 10px;
        padding: 12px 14px;
        font-family: 'Inter', sans-serif;
        font-size: 0.88rem;
        color: #1a1a2e;
        background: #fafbfc;
        outline: none;
        resize: vertical;
        transition: border-color 0.2s;
    }
    .form-field textarea:focus { border-color: #c9a96e; }
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
    }
    .btn-save:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(26,26,46,0.3); }

    /* Drop zone */
    .drop-zone {
        border: 2px dashed #e8ecf0;
        border-radius: 14px;
        padding: 40px 20px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s;
        background: #fafbfc;
        position: relative;
    }
    .drop-zone:hover, .drop-zone.drag-over {
        border-color: #c9a96e;
        background: rgba(201,169,110,0.03);
    }
    .drop-zone input[type="file"] {
        position: absolute;
        inset: 0;
        opacity: 0;
        cursor: pointer;
        width: 100%;
        height: 100%;
    }
    .drop-zone-icon { font-size: 2rem; color: rgba(201,169,110,0.4); margin-bottom: 10px; }
    .drop-zone-text { font-size: 0.88rem; color: #9ea3b0; line-height: 1.6; }
    .drop-zone-text strong { color: #c9a96e; }
    .upload-progress {
        display: none;
        margin-top: 14px;
        font-size: 0.82rem;
        color: #c9a96e;
        font-weight: 500;
    }
    .upload-progress i { animation: spin 1s linear infinite; margin-right: 6px; }
    @keyframes spin { to { transform: rotate(360deg); } }

    /* Gallery grid */
    .gallery-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
        gap: 10px;
        margin-top: 16px;
    }
    .gallery-item {
        position: relative;
        border-radius: 12px;
        overflow: hidden;
        aspect-ratio: 1;
        background: #f1f5f9;
        group: true;
    }
    .gallery-item img {
        width: 100%; height: 100%;
        object-fit: cover;
        transition: transform 0.4s;
    }
    .gallery-item:hover img { transform: scale(1.06); }
    .gallery-item-overlay {
        position: absolute;
        inset: 0;
        background: rgba(0,0,0,0);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 6px;
        transition: background 0.3s;
    }
    .gallery-item:hover .gallery-item-overlay { background: rgba(0,0,0,0.45); }
    .gallery-action-btn {
        opacity: 0;
        color: white;
        border: none;
        border-radius: 8px;
        padding: 6px 12px;
        font-size: 0.75rem;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s;
        width: 85%;
        justify-content: center;
    }
    .btn-del { background: rgba(239,68,68,0.9); }
    .btn-del:hover { background: #dc2626; color:white; }
    .btn-cover { background: rgba(34,197,94,0.9); }
    .btn-cover:hover { background: #16a34a; color:white; }
    .gallery-item:hover .gallery-action-btn { opacity: 1; }

    .cover-badge {
        position: absolute;
        top: 6px; left: 6px;
        background: #c9a96e;
        color: white;
        padding: 4px 8px;
        border-radius: 6px;
        font-size: 0.65rem;
        font-weight: 700;
        z-index: 2;
        box-shadow: 0 2px 5px rgba(0,0,0,0.3);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .gallery-count {
        font-size: 0.78rem;
        color: #9ea3b0;
        margin-bottom: 12px;
    }
    .empty-gallery {
        text-align: center;
        padding: 30px;
        color: #9ea3b0;
        font-size: 0.85rem;
    }
</style>

<?php if ($msg_story) echo $msg_story; ?>

<div class="row g-3">
    <!-- Left: Love Story -->
    <div class="col-lg-5">
        <div class="section-card" style="position:sticky; top:80px;">
            <h5><i class="fas fa-book-open"></i> Our Love Story</h5>
            <form method="POST" action="gallery.php">
                <div class="form-field">
                    <label>Tell guests how you met, your proposal, or a sweet message</label>
                    <textarea name="love_story" rows="12"
                        placeholder="We first met at university in 2018..."
                    ><?php echo htmlspecialchars($wedding_data['love_story'] ?? ''); ?></textarea>
                </div>
                <button type="submit" name="update_story" class="btn-save">
                    <i class="fas fa-save"></i> Save Love Story
                </button>
            </form>
        </div>
    </div>

    <!-- Right: Upload + Gallery -->
    <div class="col-lg-7">
        <div class="section-card mb-3">
            <h5><i class="fas fa-camera"></i> Upload Engagement Photos</h5>
            <p style="font-size:0.82rem; color:#9ea3b0; margin-bottom:16px;">
                Images are automatically compressed and converted to WebP format for fast loading.
            </p>
            <div class="drop-zone" id="drop-zone">
                <input type="file" id="image-upload" accept="image/*" multiple>
                <div class="drop-zone-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                <p class="drop-zone-text">
                    <strong>Click to upload</strong> or drag & drop<br>
                    JPG, PNG, WEBP — automatically optimized
                </p>
            </div>
            <div class="upload-progress" id="upload-progress">
                <i class="fas fa-circle-notch"></i> Compressing and uploading... please wait
            </div>
        </div>

        <div class="section-card">
            <h5><i class="fas fa-images"></i> Your Gallery</h5>
            <p class="gallery-count"><?php echo count($imagesList); ?> photo<?php echo count($imagesList) !== 1 ? 's' : ''; ?> uploaded</p>

            <?php if (count($imagesList) > 0): ?>
            <div class="gallery-grid">
                <?php foreach ($imagesList as $img): ?>
                <div class="gallery-item">
                    <?php if (!empty($wedding_data['hero_image']) && $wedding_data['hero_image'] === $img['image_path']): ?>
                        <div class="cover-badge"><i class="fas fa-star"></i> Cover</div>
                    <?php endif; ?>
                    <img src="../../<?php echo htmlspecialchars($img['image_path']); ?>" alt="Gallery photo" loading="lazy">
                    <div class="gallery-item-overlay">
                        <?php if (empty($wedding_data['hero_image']) || $wedding_data['hero_image'] !== $img['image_path']): ?>
                        <a href="gallery.php?set_cover=<?php echo $img['id']; ?>" class="gallery-action-btn btn-cover">
                            <i class="fas fa-image"></i> Set Cover
                        </a>
                        <?php endif; ?>
                        <a href="gallery.php?delete_img=<?php echo $img['id']; ?>"
                           class="gallery-action-btn btn-del"
                           onclick="return confirm('Remove this photo?');">
                            <i class="fas fa-trash-alt"></i> Remove
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="empty-gallery">
                <i class="fas fa-image" style="font-size:2rem; opacity:0.2; display:block; margin-bottom:10px;"></i>
                No photos uploaded yet. Share your special moments with your guests!
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
const dropZone = document.getElementById('drop-zone');
const fileInput = document.getElementById('image-upload');
const progressDiv = document.getElementById('upload-progress');

// Drag & drop styling
dropZone.addEventListener('dragover', (e) => { e.preventDefault(); dropZone.classList.add('drag-over'); });
dropZone.addEventListener('dragleave', () => dropZone.classList.remove('drag-over'));
dropZone.addEventListener('drop', (e) => {
    e.preventDefault();
    dropZone.classList.remove('drag-over');
    const files = e.dataTransfer.files;
    if (files.length) uploadFiles(files);
});

fileInput.addEventListener('change', function() {
    if (this.files.length) uploadFiles(this.files);
});

function uploadFiles(files) {
    const fileArray = Array.from(files);
    let completed = 0;

    progressDiv.style.display = 'block';

    fileArray.forEach(file => {
        new Compressor(file, {
            quality: 0.7,
            mimeType: 'image/webp',
            maxWidth: 1400,
            success(result) {
                const formData = new FormData();
                const cleanName = file.name.replace(/\.[^/.]+$/, '') + '.webp';
                formData.append('gallery_image', result, cleanName);
                formData.append('action', 'upload_image');

                fetch('gallery.php', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(data => {
                    completed++;
                    if (completed === fileArray.length) {
                        progressDiv.style.display = 'none';
                        if (data.success) {
                            location.reload();
                        } else {
                            alert('Upload failed: ' + (data.message || 'Unknown error'));
                        }
                    }
                });
            },
            error(err) {
                completed++;
                progressDiv.style.display = 'none';
                alert('Compression failed: ' + err.message);
            }
        });
    });
}
</script>

<?php require '../layouts/footer.php'; ?>
