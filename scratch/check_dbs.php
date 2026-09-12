<?php
$pdo = new PDO('mysql:host=localhost', 'root', '');
$dbs = $pdo->query('SHOW DATABASES')->fetchAll(PDO::FETCH_COLUMN);
echo "DATABASES:\n" . implode(", ", $dbs) . "\n\n";

foreach ($dbs as $db) {
    if (in_array($db, ['information_schema', 'mysql', 'performance_schema', 'sys'])) continue;
    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM `{$db}`.`quote_requests`");
        $cols = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "DB: {$db} -> quote_requests columns: " . (in_array('source_mode', $cols) ? "HAS source_mode" : "MISSING source_mode") . "\n";
        if (!in_array('source_mode', $cols)) {
            echo "  Columns: " . implode(", ", $cols) . "\n";
        }
    } catch (Exception $e) {
        // no quote_requests table in this db
    }
}
