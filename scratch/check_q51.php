<?php
require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();
$db = \App\Core\Database::getInstance();
$stmt = $db->prepare("SELECT canvas_data FROM quote_requests WHERE id = 51");
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);

echo "=== canvas_data ===\n";
$cd = json_decode($row['canvas_data'] ?? '', true);
echo json_encode($cd, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

echo "\n=== floors_data ===\n";
echo $row['floors_data'] ?? '';
