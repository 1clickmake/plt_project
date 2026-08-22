<?php
try {
    $db = new PDO('mysql:host=localhost;dbname=asamiya;charset=utf8mb4', 'root', '');
    $db->exec('ALTER TABLE employee_logs ADD COLUMN ip_address VARCHAR(45) NULL AFTER employee_id, ADD COLUMN user_agent VARCHAR(255) NULL AFTER ip_address');
    echo 'Done';
} catch (Exception $e) {
    echo $e->getMessage();
}
