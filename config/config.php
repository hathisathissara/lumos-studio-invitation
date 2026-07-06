<?php
// Force UTF-8 end-to-end: the HTTP response header, PHP's internal string
// handling, and the DB connection must all agree — otherwise WAMP's PHP
// (which often defaults to ISO-8859-1) can send the wrong charset header,
// the browser misreads the emoji bytes, and they turn into "�".
if (!headers_sent()) {
    header('Content-Type: text/html; charset=UTF-8');
}
mb_internal_encoding('UTF-8');

$host = 'localhost';
$dbname = 'invite';
$username = 'root';
$password = ''; // wamp server එකේ සාමාන්‍යයෙන් password එකක් නෑ, ඒ නිසා මේක හිස්ව තියන්න.

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    // Error mode එක Exception වලට set කිරීම
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Extra safety net so the connection always talks utf8mb4 even if the
    // DSN charset param is ever ignored by a driver/version quirk.
    $pdo->exec("SET NAMES utf8mb4");
} catch(PDOException $e) {
    die("Database Connection failed: " . $e->getMessage());
}
?>