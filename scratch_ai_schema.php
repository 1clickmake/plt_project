<?php
try {
    $db = new PDO("mysql:host=localhost;dbname=asamiya;charset=utf8mb4", "root", "");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if ai_config exists
    $stmt = $db->query("SHOW TABLES LIKE 'ai_config'");
    if ($stmt->rowCount() > 0) {
        echo "ai_config table exists.\n";
        $stmt = $db->query("SELECT * FROM ai_config");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        print_r($rows);
    } else {
        echo "ai_config table does not exist. Creating it...\n";
        $db->exec("CREATE TABLE ai_config (
            id INT AUTO_INCREMENT PRIMARY KEY,
            gemini_key VARCHAR(255) NOT NULL
        )");
        echo "Created ai_config.\n";
    }

    // Create vendor_pricing_rules
    $stmt = $db->query("SHOW TABLES LIKE 'vendor_pricing_rules'");
    if ($stmt->rowCount() == 0) {
        $db->exec("CREATE TABLE vendor_pricing_rules (
            id INT AUTO_INCREMENT PRIMARY KEY,
            vendor_id INT NOT NULL DEFAULT 1,
            applied_month VARCHAR(50) NOT NULL,
            pricing_data JSON NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        echo "Created vendor_pricing_rules table.\n";
    } else {
        echo "vendor_pricing_rules table exists.\n";
    }
} catch (PDOException $e) {
    echo "DB Error: " . $e->getMessage();
}
