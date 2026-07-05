<?php
$host = 'localhost';
$dbname = 'invite';
$username = 'root';
$password = ''; // wamp server එකේ සාමාන්‍යයෙන් password එකක් නෑ, ඒ නිසා මේක හිස්ව තියන්න.

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    // Error mode එක Exception වලට set කිරීම
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Database Connection failed: " . $e->getMessage());
}
?>