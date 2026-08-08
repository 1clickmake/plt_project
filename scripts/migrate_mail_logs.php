<?php
require_once dirname(__DIR__) . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();
use App\Core\Database;

try {
    $db = Database::getInstance();
    $db->exec("ALTER TABLE mail_logs 
               ADD COLUMN sender_name VARCHAR(100) DEFAULT NULL AFTER target_info, 
               ADD COLUMN sender_phone VARCHAR(50) DEFAULT NULL AFTER sender_name, 
               ADD COLUMN sender_email VARCHAR(255) DEFAULT NULL AFTER sender_phone, 
               ADD COLUMN target_type VARCHAR(50) DEFAULT NULL AFTER sender_email");
    echo "Columns added to mail_logs successfully.";
} catch (Exception $e) {
    // If columns already exist, it will throw an error, but that's fine for now
    echo "Info: " . $e->getMessage();
}
