<?php
// MariaDB Configuration
$host = 'localhost';
$dbname = 'inventory_management';
$username = 'root';  // Change this - usually 'root' for local development
$password = '1234';  // Change this - leave empty if no password set

try {
    // MariaDB uses mysql driver but may have different default settings
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>