<?php
session_start();
// config.php සම්බන්ධ කිරීම
require '../config/config.php';

// ආරක්ෂාව සඳහා ලොග් වී නැත්නම් JSON Error එකක් ලබාදෙයි
if (!isset($_SESSION['user_id']) || !isset($_SESSION['wedding_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$wedding_id = $_SESSION['wedding_id'];

// 1. මුළු අමුත්තන්/ආසන ගණන
$stmtTotal = $pdo->prepare("SELECT SUM(seats_reserved) as total FROM guests WHERE wedding_id = ?");
$stmtTotal->execute([$wedding_id]);
$total_guests = $stmtTotal->fetch()['total'] ?? 0;

// 2. Opened Invitations
$stmtOpened = $pdo->prepare("SELECT SUM(seats_reserved) as opened FROM guests WHERE wedding_id = ? AND is_opened = 1");
$stmtOpened->execute([$wedding_id]);
$opened_invitations = $stmtOpened->fetch()['opened'] ?? 0;

// 3. Attending (RSVP)
$stmtAccepted = $pdo->prepare("SELECT SUM(seats_reserved) as accepted FROM guests WHERE wedding_id = ? AND rsvp_status = 'accepted'");
$stmtAccepted->execute([$wedding_id]);
$accepted_rsvp = $stmtAccepted->fetch()['accepted'] ?? 0;

// 4. Not Attending
$stmtRejected = $pdo->prepare("SELECT SUM(seats_reserved) as rejected FROM guests WHERE wedding_id = ? AND rsvp_status = 'rejected'");
$stmtRejected->execute([$wedding_id]);
$rejected_rsvp = $stmtRejected->fetch()['rejected'] ?? 0;

// 5. මෑතකදී ඇතුලත් කල/Update වූ අමුත්තන් 5 දෙනා
$stmtRecent = $pdo->prepare("SELECT * FROM guests WHERE wedding_id = ? ORDER BY id DESC LIMIT 5");
$stmtRecent->execute([$wedding_id]);
$recent_guests = $stmtRecent->fetchAll(PDO::FETCH_ASSOC);

// XSS ආරක්ෂාව සඳහා දත්ත පිරිසිදු කර සකස් කිරීම
$formatted_recent = [];
foreach ($recent_guests as $g) {
    $formatted_recent[] = [
        'name' => htmlspecialchars($g['name']),
        'whatsapp_number' => htmlspecialchars($g['whatsapp_number']),
        'is_opened' => intval($g['is_opened']),
        'rsvp_status' => $g['rsvp_status']
    ];
}

// සියලුම සජීවී දත්ත JSON එකක් ලෙස මුදාහැරීම
header('Content-Type: application/json');
echo json_encode([
    'total_guests' => intval($total_guests),
    'opened_invitations' => intval($opened_invitations),
    'accepted_rsvp' => intval($accepted_rsvp),
    'rejected_rsvp' => intval($rejected_rsvp),
    'recent_guests' => $formatted_recent
]);
exit();