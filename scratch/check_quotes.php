<?php
require_once __DIR__ . '/../app/Core/Database.php';
use App\Core\Database;

try {
    $db = new PDO("mysql:host=localhost;dbname=asamiya;charset=utf8mb4", "root", "");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== QUOTE REQUESTS ===\n";
    $stmt = $db->query("SELECT * FROM quote_requests ORDER BY id DESC LIMIT 5");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

    echo "\n=== VENDOR INQUIRIES ===\n";
    $stmt2 = $db->query("SELECT id, vendor_user_id, title, company, created_at FROM vendor_inquiries ORDER BY id DESC LIMIT 10");
    print_r($stmt2->fetchAll(PDO::FETCH_ASSOC));

    echo "\n=== USERS ===\n";
    $stmt3 = $db->query("SELECT id, user_id, username FROM users LIMIT 10");
    print_r($stmt3->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
