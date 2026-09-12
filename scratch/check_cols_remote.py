import paramiko

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
ssh.connect('115.68.223.138', port=22, username='root', password='01055403957w')

sftp = ssh.open_sftp()
with sftp.file('/tmp/check_cols.php', 'w') as f:
    f.write("""<?php
require '/www/wwwroot/cmake.work/vendor/autoload.php';
$dotenv = Dotenv\\Dotenv::createImmutable('/www/wwwroot/cmake.work');
$dotenv->load();
require '/www/wwwroot/cmake.work/app/Core/Database.php';
$db = App\\Core\\Database::getInstance();
$stmt = $db->query('DESCRIBE quote_requests');
$cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $c) {
    echo "{$c['Field']} ({$c['Type']})" . PHP_EOL;
}
""")

cmd = "/www/server/php/82/bin/php /tmp/check_cols.php"
stdin, stdout, stderr = ssh.exec_command(cmd)
print(stdout.read().decode('utf-8', errors='ignore'))
sftp.remove('/tmp/check_cols.php')
sftp.close()
ssh.close()
