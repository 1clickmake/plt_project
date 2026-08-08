<?php
require_once dirname(__DIR__) . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();
use App\Core\Database;

try {
    $db = Database::getInstance();
    $tables = $db->query("SHOW TABLES LIKE 'pages'")->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables: " . implode(', ', $tables) . "\n";
    
    if (!empty($tables)) {
        echo "\nSchema for pages:\n";
        $columns = $db->query("SHOW COLUMNS FROM `pages`")->fetchAll();
        foreach ($columns as $col) {
            echo " - {$col['Field']} ({$col['Type']})\n";
        }
    } else {
        echo "Table 'pages' does not exist.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
