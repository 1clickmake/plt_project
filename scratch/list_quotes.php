<?php
// .env 파일 수동 로드
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        list($key, $value) = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value, '"');
    }
}

$host = $_ENV['DB_HOST'] ?? 'localhost';
$dbname = $_ENV['DB_NAME'] ?? 'asamiya';
$user = $_ENV['DB_USER'] ?? 'root';
$pass = $_ENV['DB_PASS'] ?? '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    
    // 모든 견적들의 liner 관련 데이터 확인
    $stmt = $pdo->query("SELECT id, company, pricing_rule_id, source_mode, 
        JSON_EXTRACT(admin_quote_details, '$.liner_unit_price') as liner_unit_price,
        JSON_EXTRACT(admin_quote_details, '$.liner_qty') as liner_qty,
        CASE WHEN admin_quote_details IS NOT NULL AND admin_quote_details != '' THEN 'YES' ELSE 'NO' END as has_details
        FROM quote_requests ORDER BY id DESC LIMIT 15");
    
    echo "=== 최근 견적 목록 ===\n";
    echo str_pad("ID", 5) . str_pad("company", 20) . str_pad("pricing_rule", 15) . str_pad("source_mode", 20) . str_pad("liner_qty", 12) . str_pad("liner_price", 12) . "has_details\n";
    echo str_repeat("-", 90) . "\n";
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo str_pad($row['id'], 5) 
            . str_pad(substr($row['company'] ?? 'N/A', 0, 18), 20)
            . str_pad($row['pricing_rule_id'] ?? 'NULL', 15)
            . str_pad($row['source_mode'] ?? 'N/A', 20)
            . str_pad($row['liner_qty'] ?? 'N/A', 12)
            . str_pad($row['liner_unit_price'] ?? 'N/A', 12)
            . $row['has_details'] . "\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
