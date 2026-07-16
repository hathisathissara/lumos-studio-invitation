<?php
/**
 * installer.php
 *
 * Auto-setup + migration script.
 *
 *  - Palaweni host karaddi: database eka + tables okkoma auto-create wenawa.
 *  - Passe collata alut column ekak / table ekak one unoth: pahalin thiyena
 *    $migrations array ekata entry ekak dala, SCHEMA_VERSION eka 1kin
 *    wadi karanna. Ithuru requests walata meka automatic wenna one.
 *
 * config.php eken PDO connect wena eka before meka require karanna one,
 * naththam "Unknown database" error ekak enawa (database ekama nathi nisa).
 */

// =================================================================
// SCHEMA_VERSION eka - alut migration ekak danakota meka 1kin wadi karanna
// =================================================================
define('SCHEMA_VERSION', 3);

// =================================================================
// Fast path: version eka already up-to-date nam, DB ekata query
// ekakwath yawanna one na - file read ekak witharai.
// =================================================================
$versionFile    = __DIR__ . '/.schema_version';
$currentVersion = file_exists($versionFile) ? (int) trim(file_get_contents($versionFile)) : 0;

if ($currentVersion >= SCHEMA_VERSION) {
    return;
}

// =================================================================
// .env load karanna (config.php ekema pattern eka)
// =================================================================
$envPath = __DIR__ . '/../.env';
if (file_exists($envPath)) {
    $envVars = parse_ini_file($envPath);
    if ($envVars) {
        foreach ($envVars as $key => $value) {
            $_ENV[$key] = $value;
            putenv("$key=$value");
        }
    }
}

$host     = $_ENV['DB_HOST'];
$dbname   = $_ENV['DB_NAME'];
$username = $_ENV['DB_USER'];
$password = $_ENV['DB_PASS'];

/**
 * Helper — table ekaka column ekak dannaganatama thiyenawada kiyala
 * check karanawa (INFORMATION_SCHEMA eken).
 */
function columnExists(PDO $pdo, string $dbname, string $table, string $column): bool
{
    $stmt = $pdo->prepare(
        "SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
         WHERE TABLE_SCHEMA = :dbname AND TABLE_NAME = :table AND COLUMN_NAME = :column"
    );
    $stmt->execute([
        'dbname' => $dbname,
        'table'  => $table,
        'column' => $column,
    ]);

    return (int) $stmt->fetchColumn() > 0;
}

try {
    // -----------------------------------------------------------
    // Step 1: dbname ekak nodila server ekatama connect wenawa
    // -----------------------------------------------------------
    $pdoServer = new PDO("mysql:host=$host;charset=utf8mb4", $username, $password);
    $pdoServer->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $pdoServer->exec(
        "CREATE DATABASE IF NOT EXISTS `$dbname`
         CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
    );

    // -----------------------------------------------------------
    // Step 2: dan database ekata reconnect wenawa
    // -----------------------------------------------------------
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("SET NAMES utf8mb4");

    // -----------------------------------------------------------
    // Step 3: base tables — IF NOT EXISTS widiyatama (palaweni
    // host ekedi witharak thama create wenne, passe run unath
    // repeat karanna aparadayak na, thiyena eka athulat wenne na)
    // -----------------------------------------------------------
    $tableStatements = [

        "CREATE TABLE IF NOT EXISTS `users` (
            `id` int NOT NULL AUTO_INCREMENT,
            `name` varchar(100) DEFAULT NULL,
            `email` varchar(100) DEFAULT NULL,
            `password` varchar(255) DEFAULT NULL,
            `role` enum('admin','couple') DEFAULT 'couple',
            `status` enum('pending','active') DEFAULT 'pending',
            `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
            `payment_slip` varchar(255) DEFAULT NULL,
            `deletion_notice_sent_at` DATETIME DEFAULT NULL,
            `refund_requested_at` DATETIME NULL,
            `refund_status` ENUM('none', 'pending', 'approved', 'details_submitted', 'rejected', 'completed') DEFAULT 'none',
            `refund_bank_details` TEXT NULL,
            `refund_reason` TEXT NULL,
            `package` ENUM('basic', 'standard', 'premium') DEFAULT 'basic',
            `has_guest_gallery` TINYINT(1) DEFAULT 0,
            `upgrade_slip` VARCHAR(255) NULL,
            `pending_upgrade_plan` VARCHAR(100) NULL,
            `reset_code` VARCHAR(10) DEFAULT NULL,
            `reset_expires` DATETIME DEFAULT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `email` (`email`)
        )",

        "CREATE TABLE IF NOT EXISTS `weddings` (
            `id` int NOT NULL AUTO_INCREMENT,
            `user_id` int DEFAULT NULL,
            `bride_name` varchar(100) DEFAULT NULL,
            `groom_name` varchar(100) DEFAULT NULL,
            `wedding_date` date DEFAULT NULL,
            `venue` VARCHAR(255) DEFAULT NULL,
            `cover_image` varchar(255) DEFAULT NULL,
            `love_story` text DEFAULT NULL,
            `hero_image` varchar(255) DEFAULT NULL,
            `template_name` varchar(100) DEFAULT 'default',
            `invite_language` VARCHAR(5) NOT NULL DEFAULT 'en' COMMENT 'en, si, ta',
            `music_track` VARCHAR(50) DEFAULT NULL COMMENT 'preset key from music_library.php, NULL = off',
            `slug` varchar(255) DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `user_id` (`user_id`)
        )",

        "CREATE TABLE IF NOT EXISTS `events` (
            `id` int NOT NULL AUTO_INCREMENT,
            `wedding_id` int DEFAULT NULL,
            `event_name` varchar(100) DEFAULT NULL,
            `event_date_time` datetime DEFAULT NULL,
            `location_name` varchar(255) DEFAULT NULL,
            `google_map_link` text,
            PRIMARY KEY (`id`),
            KEY `wedding_id` (`wedding_id`)
        )",

        "CREATE TABLE IF NOT EXISTS `gallery` (
            `id` int NOT NULL AUTO_INCREMENT,
            `wedding_id` int DEFAULT NULL,
            `image_path` varchar(255) DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `wedding_id` (`wedding_id`)
        )",

        "CREATE TABLE IF NOT EXISTS `guests` (
            `id` int NOT NULL AUTO_INCREMENT,
            `wedding_id` int DEFAULT NULL,
            `name` varchar(150) DEFAULT NULL,
            `whatsapp_number` varchar(20) DEFAULT NULL,
            `category` varchar(50) DEFAULT NULL,
            `side` varchar(50) DEFAULT NULL,
            `is_opened` tinyint(1) DEFAULT '0',
            `opened_at` datetime DEFAULT NULL,
            `rsvp_status` enum('pending','accepted','rejected') DEFAULT 'pending',
            `guest_note` text DEFAULT NULL,
            `seats_reserved` int DEFAULT '1',
            `is_sent` TINYINT(1) DEFAULT '0',
            `sent_at` DATETIME NULL,
            `invite_token` VARCHAR(20) DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `wedding_id` (`wedding_id`)
        )",

        "CREATE TABLE IF NOT EXISTS `tasks` (
            `id` int NOT NULL AUTO_INCREMENT,
            `wedding_id` int DEFAULT NULL,
            `task_name` varchar(255) DEFAULT NULL,
            `is_completed` tinyint(1) DEFAULT '0',
            PRIMARY KEY (`id`),
            KEY `wedding_id` (`wedding_id`)
        )",

        "CREATE TABLE IF NOT EXISTS `guest_gallery` (
            id INT AUTO_INCREMENT PRIMARY KEY,
            wedding_id INT,
            guest_name VARCHAR(150),
            image_path VARCHAR(255),
            uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (wedding_id) REFERENCES weddings(id) ON DELETE CASCADE
        )",

        "CREATE TABLE IF NOT EXISTS `login_attempts` (
            id INT AUTO_INCREMENT PRIMARY KEY,
            ip_address VARCHAR(45) NOT NULL,
            email VARCHAR(255) NOT NULL,
            attempt_time DATETIME NOT NULL
        )",
    ];

    foreach ($tableStatements as $sql) {
        $pdo->exec($sql);
    }

    // unique index eka - duplicate error (1061) nam ignore karanawa
    try {
        $pdo->exec("CREATE UNIQUE INDEX idx_guests_invite_token ON guests (invite_token)");
    } catch (PDOException $e) {
        if ((int) $e->errorInfo[1] !== 1061) {
            throw $e;
        }
    }

    // -----------------------------------------------------------
    // Step 4: MIGRATIONS — alut column ekak / table ekak one unoth
    // methanata entry ekak damma, SCHEMA_VERSION eka wadi karanna.
    // 'version' eka anivarayenma kalin ekata wada wadi anka ekak
    // wenna one (1, 2, 3...). Meka run wenne once witharai —
    // ithuru requests walata SCHEMA_VERSION eka ekathu welath
    // uda thiyena file eka nisa mehema try karanne na.
    // -----------------------------------------------------------
    $migrations = [
        // Udaharanayak witharai — anaganna one nam methana comment
        // eka ain karala, values danna:

        // [
        //     'version' => 3,
        //     'table'   => 'weddings',
        //     'column'  => 'music_track',
        //     'sql'     => "ALTER TABLE `weddings` ADD COLUMN `music_track` VARCHAR(50) DEFAULT NULL COMMENT 'preset key from music_library.php, NULL = off' AFTER `invite_language`",
        // ],
    ];

    foreach ($migrations as $migration) {
        if ($migration['version'] <= $currentVersion) {
            continue; // methana kalinma run karala thiyenawa
        }

        $columnExists = columnExists($pdo, $dbname, $migration['table'], $migration['column']);

        if (!$columnExists) {
            $pdo->exec($migration['sql']);
        }
    }

    // -----------------------------------------------------------
    // Step 5: version file eka update karanawa
    // -----------------------------------------------------------
    file_put_contents($versionFile, SCHEMA_VERSION);

    // .installed eka pahala compatibility ekata thiyenawa (dawal
    // ekakuth eka check karanawa nam)
    file_put_contents(__DIR__ . '/.installed', date('Y-m-d H:i:s'));

} catch (PDOException $e) {
    die("Database auto-setup failed: " . $e->getMessage());
}