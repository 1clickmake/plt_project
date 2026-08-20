<?php
require 'vendor/autoload.php';
require 'app/Core/Database.php';

try {
    $db = new PDO('mysql:host=localhost;dbname=asamiya;charset=utf8', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. Create vendor_employees table
    $sql1 = "
    CREATE TABLE IF NOT EXISTS vendor_employees (
        id INT AUTO_INCREMENT PRIMARY KEY,
        vendor_id INT UNSIGNED NOT NULL,
        name VARCHAR(100) NOT NULL,
        title VARCHAR(100) DEFAULT '',
        phone VARCHAR(100) DEFAULT '',
        color_code VARCHAR(20) DEFAULT '#ffffff',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $db->exec($sql1);
    echo "Table vendor_employees created successfully.\n";

    // 2. Create employee_logs table
    $sql2 = "
    CREATE TABLE IF NOT EXISTS employee_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        vendor_id INT NOT NULL,
        employee_id INT NOT NULL,
        login_time DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $db->exec($sql2);
    echo "Table employee_logs created successfully.\n";

    // 3. Alter quote_requests table to add processed_by and processed_at
    try {
        $db->exec("ALTER TABLE quote_requests ADD COLUMN processed_by INT DEFAULT NULL");
        echo "Column processed_by added to quote_requests.\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
            echo "Column processed_by already exists.\n";
        } else {
            echo "Error adding processed_by: " . $e->getMessage() . "\n";
        }
    }

    try {
        $db->exec("ALTER TABLE quote_requests ADD COLUMN processed_at DATETIME DEFAULT NULL");
        echo "Column processed_at added to quote_requests.\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
            echo "Column processed_at already exists.\n";
        } else {
            echo "Error adding processed_at: " . $e->getMessage() . "\n";
        }
    }

} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage();
}
