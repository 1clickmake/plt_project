<?php
require __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$host = $_ENV['DB_HOST'] ?? 'localhost';
$db   = $_ENV['DB_NAME'] ?? 'asamiya';
$user = $_ENV['DB_USER'] ?? 'root';
$pass = $_ENV['DB_PASS'] ?? '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "ALTER TABLE quote_requests 
            ADD COLUMN status VARCHAR(50) DEFAULT 'pending' AFTER summary,
            ADD COLUMN admin_price DECIMAL(15,2) DEFAULT 0 AFTER status,
            ADD COLUMN admin_margin DECIMAL(5,2) DEFAULT 0 AFTER admin_price,
            ADD COLUMN admin_notes TEXT NULL AFTER admin_margin";
            
    $pdo->exec($sql);
    echo "Columns added successfully.\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "Columns already exist.\n";
    } else {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
