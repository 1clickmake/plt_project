<?php
require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();
$db = \App\Core\Database::getInstance();
$stmt = $db->query('SELECT id, user_id, company_name, url_slug FROM vendor_settings');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
