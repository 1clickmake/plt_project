import paramiko

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
ssh.connect('115.68.223.138', port=22, username='root', password='01055403957w')
sftp = ssh.open_sftp()
with sftp.file('/tmp/dump_recent.php', 'w') as f:
    f.write("""<?php
require '/www/wwwroot/cmake.work/vendor/autoload.php';
$dotenv = Dotenv\\Dotenv::createImmutable('/www/wwwroot/cmake.work');
$dotenv->load();
require '/www/wwwroot/cmake.work/app/Core/Database.php';
$db = App\\Core\\Database::getInstance();
$stmt = $db->query('SELECT id, created_at, rack_indep, rack_conn, rack_small_conn, rack_bypass, rack_holders, rack_pallets, rack_spec, edge_lengths FROM quote_requests ORDER BY id DESC LIMIT 5');
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    echo "ID: {$r['id']}, Time: {$r['created_at']}, Indep: {$r['rack_indep']}, Conn: {$r['rack_conn']}, Small: {$r['rack_small_conn']}, Bypass: {$r['rack_bypass']}, Holders: {$r['rack_holders']}, Pallets: {$r['rack_pallets']}" . PHP_EOL;
    echo "  Spec: {$r['rack_spec']}" . PHP_EOL;
    echo "  Edge: {$r['edge_lengths']}" . PHP_EOL;
}
""")
cmd = '/www/server/php/82/bin/php /tmp/dump_recent.php'
stdin, stdout, stderr = ssh.exec_command(cmd)
print(stdout.read().decode('utf-8', errors='ignore'))
sftp.remove('/tmp/dump_recent.php')
sftp.close()
ssh.close()
