<?php
require 'vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

require 'app/Core/Database.php';
$db = \App\Core\Database::getInstance();
$stmt = $db->query("SHOW COLUMNS FROM vendor_settings");
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo $row['Field'] . " - " . $row['Type'] . "\n";
}
