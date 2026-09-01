<?php
require 'vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
require 'app/Core/Database.php';
$db = \App\Core\Database::getInstance();
$db->query("UPDATE vendor_settings SET price_excel_path = NULL WHERE (SELECT COUNT(*) FROM vendor_pricing_rules WHERE vendor_id = user_id) = 0");
echo "Cleaned up orphaned records.";
