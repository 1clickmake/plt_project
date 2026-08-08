<?php
require_once dirname(__DIR__) . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();
use App\Core\Database;

try {
    $db = Database::getInstance();
    $db->exec("UPDATE mail_logs SET subject = REPLACE(subject, '[Contact Form] ', '') WHERE target_info = 'contact'");
    echo "Existing subjects cleaned up.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
