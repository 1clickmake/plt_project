<?php
$db = new PDO('mysql:host=localhost;dbname=asamiya','root','');
$stmt = $db->query("SELECT canvas_data FROM quote_requests WHERE id=8");
$row = $stmt->fetch();
file_put_contents('test_canvas_data.json', $row['canvas_data']);
echo "Dumped\n";
