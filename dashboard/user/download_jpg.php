<?php
session_start();
require '../config/config.php';

// පරිශීලකයා ලොග් වී ඇත්දැයි බැලීම
if (!isset($_SESSION['user_id'])) {
    die("Unauthorized access.");
}

$user_id = $_SESSION['user_id'];
$wedding_id = $_SESSION['wedding_id'] ?? 0;

$img_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($img_id <= 0) {
    die("Invalid Image ID.");
}

// guest_gallery table එකෙන් පින්තූරයේ path එක ලබාගැනීම
$stmt = $pdo->prepare("SELECT image_path, guest_name FROM guest_gallery WHERE id = ? AND wedding_id = ?");
$stmt->execute([$img_id, $wedding_id]);
$img = $stmt->fetch();

if (!$img) {
    die("Image not found or unauthorized.");
}

$relative_path = '../' . $img['image_path'];

if (!file_exists($relative_path)) {
    die("Original image file not found on server.");
}

// PHP GD Library එක සර්වර් එකේ සක්‍රීයදැයි බැලීම
if (!function_exists('imagecreatefromwebp')) {
    die("GD library with WebP support is disabled on this server. Please contact support.");
}

// WebP පින්තූරය GD image object එකක් ලෙස කියවා ගැනීම
$webp_image = imagecreatefromwebp($relative_path);

if (!$webp_image) {
    die("Failed to process WebP image.");
}

// අමුත්තාගේ නම අනුව සුදුසු ගොනු නාමයක් සෑදීම (Filename sanitize)
$clean_guest_name = preg_replace('/[^A-Za-z0-9_\-]/', '_', $img['guest_name']);
$download_filename = "wedding_moment_by_" . $clean_guest_name . "_" . $img_id . ".jpg";

// =====================================================================
// 💡 නිවැරදි කිරීම: සැබෑ JPG පින්තූරයේ ප්‍රමාණය (Byte count) ගණනය කිරීම
// =====================================================================
ob_start(); // Output Buffer එක ආරම්භ කරයි
imagejpeg($webp_image, NULL, 90); // JPG පින්තූරය buffer එක තුල සාදයි (90% Quality)
$image_data = ob_get_clean(); // Buffer එකේ ඇති දත්ත variable එකකට ගෙන buffer එක පිරිසිදු කරයි
$image_size = strlen($image_data); // සැබෑම JPG file size එක ගණනය කරයි!

// Download කිරීමට අවශ්‍ය වන Headers සකස් කිරීම
header('Content-Description: File Transfer');
header('Content-Type: image/jpeg');
header('Content-Disposition: attachment; filename="' . $download_filename . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . $image_size); // 100% නිවැරදි සැබෑ JPG size එක ලබාදෙයි!

// පින්තූර දත්ත බ්‍රවුසරයට මුදා හැරීම
echo $image_data;

// Memory එක නිදහස් කිරීම
imagedestroy($webp_image);
exit();