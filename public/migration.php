<?php
require __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

require __DIR__ . '/../app/Core/Database.php';

$db = \App\Core\Database::getInstance();
if ($db === null) {
    die("Database connection failed.\n");
}
try {
    // Check if columns exist
    $stmt = $db->query("SHOW COLUMNS FROM quote_requests LIKE 'active_employee_id'");
    if ($stmt->rowCount() == 0) {
        $db->exec("ALTER TABLE quote_requests ADD COLUMN active_employee_id INT NULL, ADD COLUMN active_employee_at DATETIME NULL");
        echo "Columns added successfully!\n";
    } else {
        echo "Columns already exist.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
