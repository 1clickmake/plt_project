<?php
require 'vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
$db = \App\Core\Database::getInstance();
try {
    $codes = ['col_4000_1000', 'beam_125_2585', 'tie_1000'];
    $placeholders = implode(',', array_fill(0, count($codes), '?'));
    $stmt = $db->prepare("SELECT item_code, unit_price FROM vendor_prices_manual WHERE item_code IN ($placeholders)");
    $stmt->execute($codes);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $row) {
        $dec = \App\Services\SecurityService::decryptValue($row['unit_price']);
        echo $row['item_code'] . " => " . $dec . "\n";
    }
} catch (Exception $e) {
    echo 'ERROR: ' . $e->getMessage();
}
