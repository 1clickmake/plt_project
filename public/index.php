<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';

// 1. Installation Check
$envPath = __DIR__ . '/../.env';
$uri = $_SERVER['REQUEST_URI'];
if (false !== $pos = strpos($uri, '?')) { $uri = substr($uri, 0, $pos); }
$uri = rawurldecode($uri);

// Calculate base path for subdirectory support
$basePath = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
if ($basePath === '/') $basePath = '';

// Determine internal URI (without base path)
$internalUri = $uri;
if ($basePath && strpos($uri, $basePath) === 0) {
    $internalUri = substr($uri, strlen($basePath));
}
if ($internalUri === '' || $internalUri === false) $internalUri = '/';

// Redirect to install if .env is missing and not already on install page
if (!file_exists($envPath) && !str_starts_with($internalUri, '/install')) {
    header('Location: ' . $basePath . '/install');
    exit;
}

// 2. Load Environment Variables & DB
if (file_exists($envPath)) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
}


require_once CM_LIB_PATH . '/common.lib.php';

// Extract hostname without port for session cookie domain
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$hostname = explode(':', $host)[0]; // Remove port if present

session_set_cookie_params([
    'lifetime' => 86400,
    'path' => '/',
    'domain' => '', // Empty string for localhost compatibility
    'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
    'httponly' => true,
    'samesite' => 'Lax'
]);
session_start();

// 2.5 Initialize User Variables
setup_user_variables();

// Skip logging if installing or .env missing
if (file_exists($envPath) && !str_starts_with($internalUri, '/install')) {
    log_visitor();

    // 3. IP Access Control (Whitelist/Blacklist)
    try {
        $db = App\Core\Database::getInstance();
        $stmt = $db->query("SHOW TABLES LIKE 'config'");
        if ($stmt->rowCount() > 0) {
            $config = $db->query("SELECT allowed_ips, blocked_ips FROM config WHERE id = 1")->fetch();
            if ($config) {
                $clientIp = $_SERVER['REMOTE_ADDR'];
                
                // Allow List Check (Whitelist)
                // If whitelist rules exist, ONLY IPs starting with those rules are allowed.
                if (!empty($config['allowed_ips'])) {
                    $allowed_lines = explode("\n", $config['allowed_ips']);
                    $is_allowed = false;
                    $has_rules = false;
                    
                    foreach ($allowed_lines as $allow_ip) {
                        $allow_ip = trim($allow_ip);
                        if (empty($allow_ip)) continue;
                        $has_rules = true;
                        
                        // Check if client IP starts with the allowed IP segment
                        if (str_starts_with($clientIp, $allow_ip)) {
                            $is_allowed = true;
                            break;
                        }
                    }

                    if ($has_rules && !$is_allowed) {
                        http_response_code(403);
                        die("Access Denied: Your IP ($clientIp) is not allowed.");
                    }
                }

                // Block List Check (Blacklist)
                // If matched, access is denied.
                if (!empty($config['blocked_ips'])) {
                    $blocked_lines = explode("\n", $config['blocked_ips']);
                    foreach ($blocked_lines as $block_ip) {
                        $block_ip = trim($block_ip);
                        if (empty($block_ip)) continue;
                        
                        // Check if client IP starts with the blocked IP segment
                        if (str_starts_with($clientIp, $block_ip)) {
                            http_response_code(403);
                            die("Access Denied: Your IP ($clientIp) has been blocked.");
                        }
                    }
                }
            }
        }
    } catch (Exception $e) {
        // Ignore DB errors during setup
    }
}

$dispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r) use ($envPath, $internalUri) {
    $routes = require __DIR__ . '/../app/routes.php';
    $routes($r);

    // Register Plugin Routes if installed
    if (file_exists($envPath) && !str_starts_with($internalUri, '/install')) {
        \App\Core\PluginManager::getInstance()->registerRoutes($r);
    }
});

// Fetch method and URI
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $internalUri;

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);
switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        http_response_code(404);
        echo "404 Not Found";
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        $allowedMethods = $routeInfo[1];
        http_response_code(405);
        echo "405 Method Not Allowed";
        break;
    case FastRoute\Dispatcher::FOUND:
        $handler = $routeInfo[1];
        $vars = $routeInfo[2];
        
        $controllerName = $handler[0];
        $methodName = $handler[1];
        
        $controller = new $controllerName();
        $controller->$methodName($vars);
        break;
}
