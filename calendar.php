<?php
// calendar.php — Generates and downloads an .ics calendar file
require 'config/config.php';

$wedding_id = isset($_GET['wedding_id']) ? intval($_GET['wedding_id']) : 0;
$event_id   = isset($_GET['event_id'])   ? intval($_GET['event_id'])   : 0;

if (!$wedding_id) {
    http_response_code(404);
    die("Invalid request.");
}

// Fetch wedding info
$stmtWed = $pdo->prepare("SELECT bride_name, groom_name FROM weddings WHERE id = ?");
$stmtWed->execute([$wedding_id]);
$wedding = $stmtWed->fetch();

if (!$wedding) {
    http_response_code(404);
    die("Not found.");
}

$events = [];

if ($event_id) {
    // Single event
    $stmt = $pdo->prepare("SELECT * FROM events WHERE id = ? AND wedding_id = ?");
    $stmt->execute([$event_id, $wedding_id]);
    $ev = $stmt->fetch();
    if ($ev) $events[] = $ev;
} else {
    // All events
    $stmt = $pdo->prepare("SELECT * FROM events WHERE wedding_id = ? ORDER BY event_date_time ASC");
    $stmt->execute([$wedding_id]);
    $events = $stmt->fetchAll();
}

if (empty($events)) {
    http_response_code(404);
    die("No events found.");
}

$couple = htmlspecialchars($wedding['bride_name'] . ' & ' . $wedding['groom_name']);
$filename = preg_replace('/[^a-z0-9]/i', '_', $couple) . '_Wedding.ics';

header('Content-Type: text/calendar; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

echo "BEGIN:VCALENDAR\r\n";
echo "VERSION:2.0\r\n";
echo "PRODID:-//Lumus Studio//Wedding Invitation//EN\r\n";
echo "CALSCALE:GREGORIAN\r\n";
echo "METHOD:PUBLISH\r\n";

foreach ($events as $ev) {
    $uid = uniqid('invite-', true) . '@lumusstudio.lk';
    $dtstart = gmdate('Ymd\THis\Z', strtotime($ev['event_date_time']));
    $dtend   = gmdate('Ymd\THis\Z', strtotime($ev['event_date_time']) + 7200); // +2 hours
    $dtstamp = gmdate('Ymd\THis\Z');

    $summary     = $ev['event_name'] . ' — ' . $couple;
    $location    = $ev['location_name'];
    $description = 'You are warmly invited to the wedding of ' . $couple . '.';

    echo "BEGIN:VEVENT\r\n";
    echo "UID:{$uid}\r\n";
    echo "DTSTAMP:{$dtstamp}\r\n";
    echo "DTSTART:{$dtstart}\r\n";
    echo "DTEND:{$dtend}\r\n";
    echo "SUMMARY:" . fold_ical_line($summary) . "\r\n";
    echo "LOCATION:" . fold_ical_line($location) . "\r\n";
    echo "DESCRIPTION:" . fold_ical_line($description) . "\r\n";
    echo "STATUS:CONFIRMED\r\n";
    echo "END:VEVENT\r\n";
}

echo "END:VCALENDAR\r\n";

/**
 * Fold long iCal lines at 75 chars (RFC 5545)
 */
function fold_ical_line(string $str): string {
    $str = str_replace(["\r\n", "\r", "\n"], "\\n", $str);
    $out = '';
    while (mb_strlen($str) > 75) {
        $out .= mb_substr($str, 0, 75) . "\r\n ";
        $str = mb_substr($str, 75);
    }
    return $out . $str;
}
