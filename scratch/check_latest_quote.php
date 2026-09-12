<?php
$_SERVER['SERVER_PORT'] = '80';
require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();
require_once __DIR__ . '/../config/config.php';
$db = App\Core\Database::getInstance();
$stmt = $db->query("SELECT id, edge_lengths, rack_spec, rack_indep, rack_conn, rack_pallets, summary, canvas_data FROM quote_requests WHERE id = 49");
$q = $stmt->fetch(PDO::FETCH_ASSOC);
echo "Edge lengths: " . $q['edge_lengths'] . "\n";
echo "Rack spec: " . $q['rack_spec'] . "\n";
echo "Rack indep: " . $q['rack_indep'] . ", conn: " . $q['rack_conn'] . ", pallets: " . $q['rack_pallets'] . "\n";
$c = json_decode($q['canvas_data'], true);
foreach ($c['floors'] as $i => $f) {
    unset($f['capturedImage']);
    echo "--- Floor $i ---\n";
    print_r($f);
}

