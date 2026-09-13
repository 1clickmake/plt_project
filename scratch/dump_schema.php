<?php
$db = new PDO('mysql:host=localhost;dbname=asamiya;charset=utf8mb4','root','');
$s = $db->query("SHOW CREATE TABLE quote_requests")->fetch();
echo $s[1];
