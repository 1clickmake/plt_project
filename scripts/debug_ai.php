<?php
require_once dirname(__DIR__) . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();
use App\Core\Database;

try {
    $db = Database::getInstance();
    $config = $db->query("SELECT * FROM ai_config WHERE id = 1")->fetch();
    echo "AI Config Found:\n";
    if ($config) {
        foreach ($config as $k => $v) {
            if (strpos($k, 'key') !== false) {
                $v = $v ? substr($v, 0, 5) . '...' : '(empty)';
            }
            echo " - $k: $v\n";
        }
    } else {
        echo "No AI Config found.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
