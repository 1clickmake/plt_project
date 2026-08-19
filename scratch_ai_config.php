<?php
$db = new PDO("mysql:host=localhost;dbname=asamiya;charset=utf8mb4", "root", "");
$stmt = $db->query("DESCRIBE ai_config");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo $row['Field'] . " - " . $row['Type'] . "\n";
}
