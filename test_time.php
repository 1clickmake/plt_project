<?php
require 'vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
date_default_timezone_set('Asia/Seoul');
require 'app/Core/Database.php';

$db = \App\Core\Database::getInstance();
$stmt = $db->query("SELECT active_employee_at, NOW() as db_now FROM quote_requests WHERE active_employee_at IS NOT NULL ORDER BY active_employee_at DESC LIMIT 1");
$row = $stmt->fetch();
print_r($row);
echo "PHP time(): " . time() . " / " . date('Y-m-d H:i:s') . "\n";
if ($row) {
    $db_time_str = $row['active_employee_at'];
    $db_time = strtotime($db_time_str);
    $now = time();
    echo "DB time str: $db_time_str\n";
    echo "DB time: $db_time\n";
    echo "Diff: " . ($now - $db_time) . " seconds\n";
}
