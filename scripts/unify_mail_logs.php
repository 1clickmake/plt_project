<?php
require_once dirname(__DIR__) . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();
use App\Core\Database;

try {
    $db = Database::getInstance();
    
    // 1. If target_type exists, copy its value to target_info where target_info is empty or null
    $checkType = $db->query("SHOW COLUMNS FROM mail_logs LIKE 'target_type'");
    if ($checkType->rowCount() > 0) {
        // Update target_info with target_type values for 'contact'
        $db->exec("UPDATE mail_logs SET target_info = 'contact' WHERE target_type = 'contact' AND (target_info IS NULL OR target_info = '')");
        
        // 2. Drop target_type column
        $db->exec("ALTER TABLE mail_logs DROP COLUMN target_type");
        echo "Successfully unified target_type into target_info and dropped target_type column.\n";
    } else {
        echo "target_type column does not exist. No action needed.\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
