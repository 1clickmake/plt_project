<?php
require_once __DIR__ . '/../vendor/autoload.php';

// Dotenv 로드 혹은 직접 연결
$pdo = new PDO('mysql:host=localhost;dbname=asamiya;charset=utf8mb4', 'root', '');
$stmt = $pdo->query('SHOW COLUMNS FROM quote_requests');
$columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
echo "COLUMNS:\n" . implode(", ", $columns) . "\n";
