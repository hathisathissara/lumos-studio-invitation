<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'config/config.php'; // database connection

$wedding_id = 0;
$guest_id = 0;
$guest_name = '';
$msg = '';
$preview_mode = !empty($_GET['preview']) && (!empty($_SESSION['user_id']) || !empty($_SESSION['role']));

if (!empty($_GET['w_id'])) {
    $wedding_id = intval($_GET['w_id']);
} elseif (!empty($_GET['slug'])) {
    $stmtSlug = $pdo->prepare("SELECT id FROM weddings WHERE slug = ? LIMIT 1");
    $stmtSlug->execute([trim($_GET['slug'])]);
    $wedding_id = intval($stmtSlug->fetchColumn());
} elseif (!empty($_SESSION['invite_wedding_id'])) {
    $wedding_id = intval($_SESSION['invite_wedding_id']);
} elseif (!empty($_SESSION['wedding_id'])) {
    $wedding_id = intval($_SESSION['wedding_id']);
}

if ($preview_mode && $wedding_id > 0) {
    $_SESSION['guest_id'] = 0;
    $_SESSION['guest_name'] = 'Preview (Admin/Owner)';
    $_SESSION['invite_wedding_id'] = $wedding_id;
    $guest_id = 0;
    $guest_name = 'Preview (Admin/Owner)';
} else {
    if (!isset($_SESSION['guest_id']) || !isset($_SESSION['invite_wedding_id'])) {
        header("Location: index.php");
        exit();
    }

    $guest_id = intval($_SESSION['guest_id']);
    $guest_name = $_SESSION['guest_name'];
    $wedding_id = intval($_SESSION['invite_wedding_id']);
}

// Security: check status
$stmtStatus = $pdo->prepare("SELECT u.status, w.user_id FROM weddings w JOIN users u ON w.user_id = u.id WHERE w.id = ?");
$stmtStatus->execute([$wedding_id]);
$status_data = $stmtStatus->fetch();

if (!$status_data) { 
    die("Invalid Wedding."); 
}

$is_owner = (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $status_data['user_id']);
$is_admin = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin');

if ($status_data['status'] !== 'active' && !$is_owner && !$is_admin) {
    die("This invitation is currently pending activation.");
}

// RSVP Submit කිරීම්
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_rsvp'])) {
    if ($guest_id == 0) {
        $msg = "<div class='flash flash-info'>This is a preview — RSVP is disabled.</div>";
    } else {
        $rsvp_status = $_POST['rsvp_status'];
        $guest_note = trim($_POST['guest_note']);
        $updateStmt = $pdo->prepare("UPDATE guests SET rsvp_status = ?, guest_note = ? WHERE id = ?");
        if ($updateStmt->execute([$rsvp_status, $guest_note, $guest_id])) {
            $msg = "<div class='flash flash-success'><i class='fas fa-check-circle'></i> Thank you! Your RSVP has been recorded.</div>";
        }
    }
}

// Fetch Wedding Details
$stmtWed = $pdo->prepare("SELECT * FROM weddings WHERE id = ?");
$stmtWed->execute([$wedding_id]);
$wedding = $stmtWed->fetch();

// Fetch Events
$stmtEvents = $pdo->prepare("SELECT * FROM events WHERE wedding_id = ? ORDER BY event_date_time ASC");
$stmtEvents->execute([$wedding_id]);
$wedding_events = $stmtEvents->fetchAll();

// Fetch Gallery Images
$stmtGallery = $pdo->prepare("SELECT * FROM gallery WHERE wedding_id = ? ORDER BY id ASC");
$stmtGallery->execute([$wedding_id]);
$gallery_images = $stmtGallery->fetchAll();

// Fetch Guest Status (මෙහි seats_reserved ද එකතු කර ඇත)
if ($guest_id == 0) {
    $current_guest = ['rsvp_status' => 'pending', 'guest_note' => '', 'seats_reserved' => 1];
} else {
    $stmtGuest = $pdo->prepare("SELECT rsvp_status, guest_note, seats_reserved FROM guests WHERE id = ?");
    $stmtGuest->execute([$guest_id]);
    $current_guest = $stmtGuest->fetch();
}

// Build Google Calendar link from first event
$google_cal_link = '';
$ics_link = "calendar.php?wedding_id=" . $wedding_id;
if (!empty($wedding_events)) {
    $ev = $wedding_events[0];
    $start = date('Ymd\THis', strtotime($ev['event_date_time']));
    $end = date('Ymd\THis', strtotime($ev['event_date_time']) + 7200);
    $title = urlencode($wedding['bride_name'] . ' & ' . $wedding['groom_name'] . ' Wedding');
    $loc = urlencode($ev['location_name']);
    $google_cal_link = "https://calendar.google.com/calendar/render?action=TEMPLATE&text=" . $title . "&dates=" . $start . "/" . $end . "&location=" . $loc;
}
?>

<?php
// Wedding table එකේ ඇති template එක අනුව template file එක load කිරීම
$template = !empty($wedding['template_name']) ? $wedding['template_name'] : 'premium_gold';
$template_path = 'templates/' . $template . '.php';

if (file_exists($template_path)) {
    require $template_path;
} else {
    require 'templates/premium_gold.php';
}
?>