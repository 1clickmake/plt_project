<?php
try {
    $db = new PDO('mysql:host=localhost;dbname=asamiya;charset=utf8mb4', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $sql = "DROP TABLE IF EXISTS `vendor_inquiries`;
    CREATE TABLE `vendor_inquiries` (
      `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '문의 고유 ID',
      `vendor_user_id` varchar(255) NOT NULL COMMENT '공급사 아이디',
      `company` varchar(255) DEFAULT NULL COMMENT '회사명',
      `name` varchar(255) NOT NULL COMMENT '담당자명',
      `phone` varchar(50) NOT NULL COMMENT '연락처',
      `email` varchar(100) DEFAULT NULL COMMENT '이메일',
      `address` varchar(255) DEFAULT NULL COMMENT '현장 주소',
      `title` varchar(255) NOT NULL COMMENT '문의 제목',
      `content` text NOT NULL COMMENT '문의 내용',
      `status` enum('pending','answered','closed') DEFAULT 'pending' COMMENT '답변 상태',
      `created_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT '작성 일시',
      `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT '수정 일시',
      PRIMARY KEY (`id`),
      KEY `idx_vendor_user_id` (`vendor_user_id`),
      KEY `idx_status` (`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='공급사 전용 게시판 문의 테이블';";
    $db->exec($sql);
    echo "vendor_inquiries table created successfully.\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
