<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/config.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$db = App\Core\Database::getInstance();
$stmt = $db->query("DESCRIBE quote_requests");
$rows = $stmt->fetchAll();
foreach ($rows as $row) {
    echo $row['Field'] . " - " . $row['Type'] . "\n";
}
