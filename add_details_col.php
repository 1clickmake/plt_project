<?php
require __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
require __DIR__ . '/app/Core/Database.php';

$db = App\Core\Database::getInstance();
try {
    $db->exec("ALTER TABLE quote_requests ADD COLUMN admin_quote_details LONGTEXT NULL");
    echo "Added admin_quote_details.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
