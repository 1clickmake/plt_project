<?php
require_once dirname(__DIR__) . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

use App\Core\Database;

try {
    $db = Database::getInstance();
    
    $sql = "CREATE TABLE IF NOT EXISTS `quote_requests` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `vendor_user_id` int(11) NOT NULL COMMENT '공급사 회원 고유 ID',
        `company` varchar(255) NOT NULL COMMENT '요청 회사명',
        `name` varchar(255) NOT NULL COMMENT '담당자 이름',
        `phone` varchar(50) NOT NULL COMMENT '연락처',
        `address` varchar(255) NOT NULL COMMENT '현장 주소',
        `canvas_data` longtext DEFAULT NULL COMMENT '랙 및 도면 정보 JSON',
        `summary` longtext DEFAULT NULL COMMENT 'AI 시공 요약 리포트',
        `created_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT '요청 일시',
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='캔버스 견적 요청 내역';";

    $db->exec($sql);
    echo "Database table 'quote_requests' has been set up successfully.\n";

} catch (Exception $e) {
    echo "Error setting up quote_requests table: " . $e->getMessage() . "\n";
}
