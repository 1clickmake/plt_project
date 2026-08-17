<?php
require_once dirname(__DIR__) . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

use App\Core\Database;

try {
    $db = Database::getInstance();
    $sql = "ALTER TABLE `quote_requests` ADD COLUMN `image_path` VARCHAR(255) DEFAULT NULL COMMENT '고해상도 캔버스 이미지 경로' AFTER `canvas_data`";
    $db->exec($sql);
    echo "Successfully added image_path column to quote_requests table.\n";
} catch (\PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "Column already exists.\n";
    } else {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
