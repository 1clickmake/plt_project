<?php
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'asamiya');
define('DB_USER', 'root');
define('DB_PASS', '');

try {
    $pdo = new PDO('mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS);
    $stmt = $pdo->prepare("SELECT * FROM quote_requests WHERE id = 40");
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        echo "--- Row found ---" . PHP_EOL;
        print_r($row);
        $details = $row['admin_quote_details'];
        if ($details) {
            $decoded = json_decode($details, true);
            echo "liner_qty: " . var_export($decoded['liner_qty'] ?? 'NOT SET', true) . PHP_EOL;
            echo "liner_unit_price: " . var_export($decoded['liner_unit_price'] ?? 'NOT SET', true) . PHP_EOL;
            echo "Full JSON:" . PHP_EOL . $details . PHP_EOL;
        } else {
            echo "admin_quote_details: NULL or empty" . PHP_EOL;
        }
    } else {
        echo "No row found with id=40" . PHP_EOL;
        // Try to list all quotes
        $all = $pdo->query('SELECT id, company FROM quote_requests ORDER BY id DESC LIMIT 10');
        foreach ($all->fetchAll() as $r) {
            echo "ID=" . $r['id'] . " company=" . $r['company'] . PHP_EOL;
        }
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . PHP_EOL;
}
