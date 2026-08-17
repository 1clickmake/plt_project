<?php
require 'config/database.php';
$stmt = $pdo->query('SELECT canvas_data FROM quote_requests ORDER BY id DESC LIMIT 1');
echo $stmt->fetchColumn();
