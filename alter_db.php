<?php
require 'vendor/autoload.php';
require 'app/Core/Database.php';

try {
    $db = new PDO('mysql:host=localhost;dbname=asamiya;charset=utf8', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->exec("ALTER TABLE vendor_settings ADD COLUMN bank_account VARCHAR(255) DEFAULT NULL");
    echo "Column bank_account added successfully.";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "Column already exists.";
    } else {
        echo "Error: " . $e->getMessage();
    }
}
