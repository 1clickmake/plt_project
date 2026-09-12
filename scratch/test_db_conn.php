<?php
require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();
require_once __DIR__ . '/../app/Core/Database.php';

$db = \App\Core\Database::getInstance();
$currentDb = $db->query('SELECT DATABASE()')->fetchColumn();
echo "Connected DB: " . $currentDb . "\n";

$stmt = $db->query('SHOW COLUMNS FROM quote_requests');
$cols = $stmt->fetchAll(PDO::FETCH_COLUMN);
echo "quote_requests columns count: " . count($cols) . "\n";
echo "Has source_mode? " . (in_array('source_mode', $cols) ? "YES" : "NO") . "\n";
