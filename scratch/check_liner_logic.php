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
    $stmt = $pdo->query("SELECT id, pricing_rule_id, admin_quote_details, source_mode FROM quote_requests WHERE id = 40");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        echo "pricing_rule_id: " . var_export($row['pricing_rule_id'], true) . "\n";
        echo "source_mode: " . var_export($row['source_mode'], true) . "\n";
        $details = $row['admin_quote_details'];
        if ($details) {
            $adminDetails = json_decode($details, true);
            echo "liner_unit_price in JSON: " . var_export($adminDetails['liner_unit_price'] ?? 'NOT SET', true) . "\n";
            echo "isset liner_unit_price: " . var_export(isset($adminDetails['liner_unit_price']), true) . "\n";
            
            // Simulate the PHP logic
            if (isset($adminDetails['liner_unit_price'])) {
                $linerUnitPrice = floatval($adminDetails['liner_unit_price']);
                echo "=> linerUnitPrice (from admin details): " . $linerUnitPrice . "\n";
            } else {
                $linerUnitPrice = empty($row['pricing_rule_id']) ? 0 : 500;
                echo "=> linerUnitPrice (fallback): " . $linerUnitPrice . "\n";
            }
            
            echo "floor result: " . floor((float)$linerUnitPrice) . "\n";
        } else {
            echo "admin_quote_details is empty/null\n";
        }
    } else {
        echo "Quote 40 not found\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
