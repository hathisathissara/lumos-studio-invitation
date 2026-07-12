<?php
session_start();
require '../../config/config.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['wedding_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$wedding_id = intval($_SESSION['wedding_id']);

try {
    // 1. Total Guests (Seats)
    $stmtTotal = $pdo->prepare("SELECT SUM(seats_reserved) as total FROM guests WHERE wedding_id = ?");
    $stmtTotal->execute([$wedding_id]);
    $total_guests = $stmtTotal->fetch()['total'] ?? 0;

    // 2. Opened
    $stmtOpened = $pdo->prepare("SELECT SUM(seats_reserved) as opened FROM guests WHERE wedding_id = ? AND is_opened = 1");
    $stmtOpened->execute([$wedding_id]);
    $opened_invitations = $stmtOpened->fetch()['opened'] ?? 0;

    // 3. Accepted
    $stmtAccepted = $pdo->prepare("SELECT SUM(seats_reserved) as accepted FROM guests WHERE wedding_id = ? AND rsvp_status = 'accepted'");
    $stmtAccepted->execute([$wedding_id]);
    $accepted_rsvp = $stmtAccepted->fetch()['accepted'] ?? 0;

    // 4. Rejected
    $stmtRejected = $pdo->prepare("SELECT SUM(seats_reserved) as rejected FROM guests WHERE wedding_id = ? AND rsvp_status = 'rejected'");
    $stmtRejected->execute([$wedding_id]);
    $rejected_rsvp = $stmtRejected->fetch()['rejected'] ?? 0;

    // 5. Recent Guests (Latest 5)
    $stmtRecent = $pdo->prepare("SELECT * FROM guests WHERE wedding_id = ? ORDER BY id DESC LIMIT 5");
    $stmtRecent->execute([$wedding_id]);
    $recent_guests = $stmtRecent->fetchAll(PDO::FETCH_ASSOC);

    // XSS ආරක්ෂාව
    $formatted_recent = [];
    foreach ($recent_guests as $g) {
        $formatted_recent[] = [
            'name' => htmlspecialchars($g['name']),
            'whatsapp_number' => htmlspecialchars($g['whatsapp_number']),
            'is_opened' => intval($g['is_opened']),
            'rsvp_status' => $g['rsvp_status']
        ];
    }

    // 6. Tasks / Checklist Progress සජීවීව ලබාගැනීම
    $stmtTasksTotal = $pdo->prepare("SELECT COUNT(*) as t FROM tasks WHERE wedding_id = ?");
    $stmtTasksTotal->execute([$wedding_id]);
    $tasks_total = $stmtTasksTotal->fetch()['t'];

    $stmtTasksDone = $pdo->prepare("SELECT COUNT(*) as c FROM tasks WHERE wedding_id = ? AND is_completed = 1");
    $stmtTasksDone->execute([$wedding_id]);
    $tasks_done = $stmtTasksDone->fetch()['c'];
    $task_pct = $tasks_total > 0 ? round(($tasks_done / $tasks_total) * 100) : 0;

    header('Content-Type: application/json');
    echo json_encode([
        'total_guests' => intval($total_guests),
        'opened_invitations' => intval($opened_invitations),
        'accepted_rsvp' => intval($accepted_rsvp),
        'rejected_rsvp' => intval($rejected_rsvp),
        'recent_guests' => $formatted_recent,
        'tasks_total' => intval($tasks_total),
        'tasks_done' => intval($tasks_done),
        'task_pct' => intval($task_pct)
    ]);
    exit();
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Database error occurred while fetching stats.']);
    exit();
}
