<?php
require __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

require __DIR__ . '/../app/Core/Database.php';
$db = \App\Core\Database::getInstance();

// Get all tables
$tablesStmt = $db->query("SHOW TABLES");
$tables = $tablesStmt->fetchAll(\PDO::FETCH_COLUMN);

$selectedTable = $_GET['table'] ?? 'vendor_settings';
if (!in_array($selectedTable, $tables)) {
    $selectedTable = $tables[0] ?? '';
}

$rows = [];
$columns = [];
if ($selectedTable) {
    // Get columns
    $colStmt = $db->query("SHOW COLUMNS FROM `$selectedTable`");
    $columns = $colStmt->fetchAll(\PDO::FETCH_COLUMN);
    
    // Get data
    $stmt = $db->query("SELECT * FROM `$selectedTable` LIMIT 100");
    $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html>
<head>
<title>DB Viewer (Asamiya)</title>
<style>
    body { background: #1e1e1e; color: #fff; font-family: monospace; padding: 20px; display: flex; }
    .sidebar { width: 200px; padding-right: 20px; border-right: 1px solid #444; }
    .content { flex: 1; padding-left: 20px; overflow-x: auto; }
    ul { list-style: none; padding: 0; }
    li { margin-bottom: 10px; }
    a { color: #38bdf8; text-decoration: none; }
    a:hover { color: #a855f7; }
    .active { color: #a855f7; font-weight: bold; }
    table { border-collapse: collapse; width: 100%; margin-top: 20px; }
    th, td { border: 1px solid #444; padding: 10px; text-align: left; white-space: nowrap; max-width: 300px; overflow: hidden; text-overflow: ellipsis; }
    th { background: #333; color: #a855f7; }
</style>
</head>
<body>
    <div class="sidebar">
        <h3>Tables</h3>
        <ul>
            <?php foreach($tables as $t): ?>
                <li><a href="?table=<?= urlencode($t) ?>" class="<?= $t === $selectedTable ? 'active' : '' ?>"><?= htmlspecialchars($t) ?></a></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <div class="content">
        <h2>Table: <?= htmlspecialchars($selectedTable) ?></h2>
        <?php if($rows): ?>
        <table>
            <tr>
                <?php foreach($columns as $col): ?>
                    <th><?= htmlspecialchars($col) ?></th>
                <?php endforeach; ?>
            </tr>
            <?php foreach($rows as $r): ?>
            <tr>
                <?php foreach($columns as $col): ?>
                    <td>
                        <?php 
                        $val = $r[$col];
                        if (is_string($val) && strlen($val) > 100) {
                            echo htmlspecialchars(mb_substr($val, 0, 100)) . '...';
                        } else {
                            echo htmlspecialchars((string)$val);
                        }
                        ?>
                    </td>
                <?php endforeach; ?>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php else: ?>
        <p>No data found in this table.</p>
        <?php endif; ?>
    </div>
</body>
</html>
