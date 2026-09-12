<?php
$pdo = new PDO('mysql:host=localhost', 'root', '');
$dbs = $pdo->query('SHOW DATABASES')->fetchAll(PDO::FETCH_COLUMN);
foreach ($dbs as $d) {
    try {
        $stmt = $pdo->query("SHOW TABLES FROM `{$d}`");
        $tbls = $stmt->fetchAll(PDO::FETCH_COLUMN);
        foreach ($tbls as $t) {
            if (stripos($t, 'quote') !== false) {
                echo "Found: {$d}.{$t}\n";
                $cstmt = $pdo->query("SHOW COLUMNS FROM `{$d}`.`{$t}`");
                $cols = $cstmt->fetchAll(PDO::FETCH_COLUMN);
                echo "  source_mode? " . (in_array('source_mode', $cols) ? "YES" : "NO") . "\n";
            }
        }
    } catch (Exception $e) {}
}
