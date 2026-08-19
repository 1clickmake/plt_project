<?php
require 'app/Core/Database.php';

try {
    $db = new PDO("mysql:host=localhost;dbname=asamiya;charset=utf8mb4", "root", "");
    $stmt = $db->query("SELECT * FROM vendor_pricing_rules ORDER BY id DESC LIMIT 1");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        echo "ID: " . $row['id'] . "\n";
        echo "Applied Month: " . $row['applied_month'] . "\n";
        echo "Created At: " . $row['created_at'] . "\n";
        echo "--- JSON DATA ---\n";
        $json = json_decode($row['pricing_data'], true);
        print_r($json);
    } else {
        echo "No data found in vendor_pricing_rules.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
