<?php
$db = new PDO('mysql:host=localhost;dbname=asamiya;charset=utf8', 'root', '');
print_r($db->query("DESCRIBE quote_requests")->fetchAll(PDO::FETCH_ASSOC));
