<?php
$pdo = new PDO('mysql:host=localhost;dbname=asamiya;charset=utf8mb4', 'root', '');
$stmt = $pdo->query('SELECT canvas_data FROM quote_requests WHERE id = 8');
$data = $stmt->fetchColumn();
file_put_contents('temp_quote8.json', json_encode(json_decode($data, true), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
