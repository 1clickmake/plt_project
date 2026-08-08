<?php
require_once dirname(__DIR__) . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();
use App\Core\Database;

try {
    $db = Database::getInstance();
    $users = $db->query("SELECT user_id, username, level, point FROM users")->fetchAll();
    echo "Total Users: " . count($users) . "\n";
    foreach ($users as $u) {
        echo "- ID: {$u['user_id']}, Name: {$u['username']}, Level: {$u['level']}, Point: {$u['point']}\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
