<?php
require __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
$pdo = new PDO('mysql:host='.$_ENV['DB_HOST'].';dbname='.$_ENV['DB_NAME'].';charset=utf8mb4', $_ENV['DB_USER'], $_ENV['DB_PASS']);
$stmt = $pdo->query('DESCRIBE quote_requests');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
