<?php
require __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
require __DIR__ . '/app/Core/Database.php';

$db = App\Core\Database::getInstance();

// 모든 메일 발송된 견적서의 admin_quote_details 확인
$stmt = $db->query("SELECT id, is_mailed, admin_margin, admin_price, LENGTH(admin_quote_details) as detail_len, admin_quote_details, mailed_at FROM quote_requests WHERE is_mailed = 1 ORDER BY mailed_at DESC LIMIT 5");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: text/plain; charset=utf-8');
echo "=== 메일 발송된 견적서 전체 조회 ===\n\n";
foreach ($rows as $row) {
    echo "ID: {$row['id']} | mailed_at: {$row['mailed_at']} | margin: {$row['admin_margin']} | price: {$row['admin_price']} | details_len: {$row['detail_len']}\n";
    if (!empty($row['admin_quote_details'])) {
        echo "  FULL JSON: {$row['admin_quote_details']}\n";
    } else {
        echo "  DETAILS: NULL/EMPTY\n";
    }
    echo "\n";
}
