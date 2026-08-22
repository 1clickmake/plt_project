<?php
require 'app/Core/Config.php';
require 'app/Core/Database.php';

$db = App\Core\Database::getInstance();

try {
    $db->exec("ALTER TABLE users ADD COLUMN addon_quotes_balance INT DEFAULT 0");
    echo "Added addon_quotes_balance to users.\n";
} catch (Exception $e) {
    echo "Error on users: " . $e->getMessage() . "\n";
}

try {
    $db->exec("ALTER TABLE quote_requests ADD COLUMN is_mailed TINYINT(1) DEFAULT 0");
    $db->exec("ALTER TABLE quote_requests ADD COLUMN mailed_at DATETIME NULL");
    echo "Added is_mailed and mailed_at to quote_requests.\n";
} catch (Exception $e) {
    echo "Error on quote_requests: " . $e->getMessage() . "\n";
}
