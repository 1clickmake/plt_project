<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=plt_project;charset=utf8mb4', 'root', '');
    $stmt = $pdo->query('SELECT canvas_data FROM quote_requests WHERE id = 8');
    $data = $stmt->fetchColumn();
    echo "LENGTH: " . strlen($data) . "\n";
    $json = json_decode($data, true);
    if ($json === null) {
        echo "JSON DECODE FAILED: " . json_last_error_msg() . "\n";
    } else {
        echo "RACKS: " . count($json['racks'] ?? []) . "\n";
        if (!empty($json['racks'])) {
            print_r($json['racks'][0]);
        }
    }
} catch(Exception $e) {
    echo $e->getMessage();
}
