<?php
require 'vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

require 'app/Core/Database.php';
$db = \App\Core\Database::getInstance();
$stmt = $db->query("SHOW COLUMNS FROM quote_requests");
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo $row['Field'] . ", ";
}
echo "\n\n";
$stmt = $db->query("SELECT id, name, company, email, summary FROM quote_requests ORDER BY id DESC LIMIT 5");
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    print_r($row);
}
