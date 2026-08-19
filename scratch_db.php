<?php
$db = new PDO("mysql:host=localhost;dbname=asamiya;charset=utf8mb4", "root", "");
$stmt = $db->query("SELECT id, created_at, pricing_data FROM vendor_pricing_rules ORDER BY created_at DESC LIMIT 1");
$row = $stmt->fetch(PDO::FETCH_ASSOC);
echo "ID: " . $row['id'] . "\n";
echo "Created: " . $row['created_at'] . "\n";
echo "Data: " . substr($row['pricing_data'], 0, 200) . "...\n";
