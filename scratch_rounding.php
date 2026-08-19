<?php
require 'vendor/autoload.php';
require 'app/Core/Database.php';

$_ENV['DB_HOST'] = 'localhost';
$_ENV['DB_NAME'] = 'asamiya';
$_ENV['DB_USER'] = 'root';
$_ENV['DB_PASS'] = '';

$weight = 243 * 5500 * 1.8 * 7.85 / 1000000;
echo "Exact weight: $weight\n";
echo "Rounded 3: " . round($weight, 3) . "\n";
