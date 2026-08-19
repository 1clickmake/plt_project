<?php
require_once __DIR__ . '/app/Core/Database.php';

try {
    $db = \App\Core\Database::getInstance();
    $db->exec("ALTER TABLE ai_config ADD COLUMN meta_key VARCHAR(255) DEFAULT NULL AFTER groq_key");
    echo "SUCCESS\n";
} catch (\PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "Column already exists\n";
    } else {
        echo "ERROR: " . $e->getMessage() . "\n";
    }
}
