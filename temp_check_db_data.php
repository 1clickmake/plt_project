<?php
$pdo = new PDO('mysql:host=localhost;dbname=asamiya;charset=utf8mb4', 'root', '');
$stmt = $pdo->query('SELECT canvas_data FROM quote_requests WHERE id = 8');
$data = $stmt->fetchColumn();
echo "Data length: " . strlen($data) . "\n";
$j = json_decode($data, true);
if ($j) {
    echo 'points: ' . (isset($j['points']) ? count($j['points']) : 'none') . "\n";
    echo 'racks: ' . (isset($j['racks']) ? count($j['racks']) : 'none') . "\n";
    echo 'scale: ' . ($j['currentScale'] ?? 'none') . "\n";
} else {
    echo "JSON invalid\n";
}
