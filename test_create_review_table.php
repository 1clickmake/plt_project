<?php
$dsn = "mysql:host=localhost;dbname=asamiya;charset=utf8mb4";
$user = "root";
$pass = "";
try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $sql = "
    DROP TABLE IF EXISTS `service_reviews`;
    CREATE TABLE `service_reviews` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `company` varchar(255) DEFAULT NULL COMMENT '회사명',
      `name` varchar(255) NOT NULL COMMENT '담당자명',
      `rating` int(1) NOT NULL DEFAULT 5 COMMENT '별점(1~5)',
      `comment` text DEFAULT NULL COMMENT '후기 내용',
      `created_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT '작성 일시',
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='사용자 서비스 리뷰 테이블';
    ";
    $pdo->exec($sql);
    echo "Table 'service_reviews' created successfully.\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
