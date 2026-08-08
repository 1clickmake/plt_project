<?php
$root = dirname(__DIR__);
require_once $root . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable($root);
$dotenv->load();
require_once $root . '/config/config.php';
require_once $root . '/lib/common.lib.php';

require_once $root . '/plugins/chatbot/Controllers/ChatbotController.php';

use Plugins\chatbot\Controllers\ChatbotController;

try {
    $_POST['message'] = "이 회사 홈페이지 특징이 뭔지 간단하게 설명해줘";
    $controller = new ChatbotController();
    
    // Capture output
    ob_start();
    $controller->ask();
    $output = ob_get_clean();
    
    echo "Output: " . $output . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
