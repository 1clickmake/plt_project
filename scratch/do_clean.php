<?php
require 'vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();
require 'app/Core/Database.php';
$db = App\Core\Database::getInstance();
$stmt = $db->query("SELECT id, extra_files FROM quote_requests WHERE extra_files LIKE '%도면.jpg%'");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    $files = json_decode($r['extra_files'], true) ?: [];
    $filtered = array_values(array_filter($files, function($f) {
        return strpos($f['original_name'] ?? '', '도면.jpg') === false;
    }));
    $newVal = !empty($filtered) ? json_encode($filtered, JSON_UNESCAPED_UNICODE) : null;
    $upd = $db->prepare('UPDATE quote_requests SET extra_files = :extra WHERE id = :id');
    $upd->execute(['extra' => $newVal, 'id' => $r['id']]);
    echo "Cleaned local quote #{$r['id']}\n";
}
echo "Local clean done.\n";
