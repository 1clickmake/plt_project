<?php
require_once dirname(__DIR__) . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

use App\Core\Database;

try {
    $db = Database::getInstance();
    
    // Drop existing table if exists to start fresh, or just create if not exists
    // Given the user wants a single "create" setup, I will provide the full schema.
    
    $sql = "CREATE TABLE IF NOT EXISTS `mail_logs` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `target_info` varchar(255) DEFAULT NULL COMMENT '발송 대상 그룹 정보',
        `recipient` longtext NOT NULL COMMENT '수신자 이메일 목록 (콤마 구분)',
        `subject` varchar(255) NOT NULL COMMENT '메일 제목',
        `content` longtext NOT NULL COMMENT '메일 본문 (HTML)',
        `attachments` text DEFAULT NULL COMMENT '첨부 파일명 목록 (콤마 구분)',
        `status` varchar(20) NOT NULL DEFAULT 'success' COMMENT '발송 상태 (success, fail, partial)',
        `error_message` text DEFAULT NULL COMMENT '에러 메시지',
        `sent_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT '발송 일시',
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    $db->exec($sql);
    echo "Database table 'mail_logs' has been set up successfully with the latest schema.\n";

} catch (Exception $e) {
    echo "Error setting up database: " . $e->getMessage() . "\n";
}
