import paramiko
import base64

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
ssh.connect('115.68.223.138', port=22, username='root', password='01055403957w')

php_code = """<?php
require_once 'vendor/autoload.php';
$dotenv = Dotenv\\Dotenv::createImmutable(__DIR__);
$dotenv->load();
require_once 'config/config.php';
$db = App\\Core\\Database::getInstance();
$stmt = $db->query('DESCRIBE quote_requests');
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo $row['Field'] . " (" . $row['Type'] . ")\\n";
}
"""

b64 = base64.b64encode(php_code.encode('utf-8')).decode('ascii')
stdin, stdout, stderr = ssh.exec_command(f'cd /www/wwwroot/cmake.work && echo "{b64}" | base64 -d | /www/server/php/82/bin/php')
print(stdout.read().decode('utf-8'))
print(stderr.read().decode('utf-8'))
ssh.close()
