<?php
require_once dirname(__DIR__) . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();
use App\Core\Database;

try {
    $db = Database::getInstance();
    $config = $db->query("SELECT * FROM config WHERE id = 1")->fetch();
    echo "Site Intro:\n" . ($config['company_intro'] ?? 'N/A') . "\n\n";
    
    $pages = $db->query("SELECT title, content FROM pages")->fetchAll();
    echo "Pages:\n";
    foreach ($pages as $p) {
        echo "- {$p['title']}: " . substr(strip_tags($p['content'] ?? ''), 0, 200) . "...\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
