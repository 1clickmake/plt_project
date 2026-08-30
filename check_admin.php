<?php
require 'app/Core/Database.php';
$db = \App\Core\Database::getInstance();

// admin 유저 정보 가져오기
$stmt = $db->prepare("SELECT user_id, username FROM users WHERE username = 'admin' OR user_id = 'admin'");
$stmt->execute();
$user = $stmt->fetch();
if ($user) {
    echo "User ID: " . $user['user_id'] . "\n";
    // vendor_settings 가져오기
    $stmt2 = $db->prepare("SELECT url_slug FROM vendor_settings WHERE user_id = ?");
    $stmt2->execute([$user['user_id']]);
    $settings = $stmt2->fetch();
    echo "Slug: " . ($settings ? $settings['url_slug'] : 'N/A') . "\n";
} else {
    echo "Admin user not found.\n";
}
