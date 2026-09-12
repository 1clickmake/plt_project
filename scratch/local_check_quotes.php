<?php
require 'vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();
require 'app/Core/Database.php';
$db = App\Core\Database::getInstance();
$stmt = $db->query('SELECT id, created_at, rack_indep, rack_conn, rack_small_conn, rack_bypass, rack_holders, rack_pallets, rack_spec, edge_lengths, canvas_data FROM quote_requests ORDER BY id DESC LIMIT 5');
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    echo "ID: {$r['id']}, Time: {$r['created_at']}, Indep: {$r['rack_indep']}, Conn: {$r['rack_conn']}, Small: {$r['rack_small_conn']}, Bypass: {$r['rack_bypass']}, Holders: {$r['rack_holders']}, Pallets: {$r['rack_pallets']}" . PHP_EOL;
    echo "  Spec: {$r['rack_spec']}" . PHP_EOL;
    echo "  Edge: {$r['edge_lengths']}" . PHP_EOL;
    $c = json_decode($r['canvas_data'], true);
    if (!empty($c['floors'])) {
        foreach ($c['floors'] as $idx => $flr) {
            echo "    Floor $idx ({$flr['name']}): indep={$flr['indep']}, conn={$flr['conn']}, smallConn={$flr['smallConn']}, bypass={$flr['bypass']}, pallets={$flr['pallets']}, spec=" . ($flr['spec'] ?? 'none') . PHP_EOL;
            if (isset($flr['edgeLengths'])) {
                echo "      edgeLengths: " . json_encode($flr['edgeLengths']) . PHP_EOL;
            }
        }
    }
}
