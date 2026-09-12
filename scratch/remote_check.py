import paramiko

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
ssh.connect('115.68.223.138', port=22, username='root', password='01055403957w')

sftp = ssh.open_sftp()
with sftp.file('/tmp/check_q.php', 'w') as f:
    f.write("""<?php
require '/www/wwwroot/cmake.work/vendor/autoload.php';
$dotenv = Dotenv\\Dotenv::createImmutable('/www/wwwroot/cmake.work');
$dotenv->load();
require '/www/wwwroot/cmake.work/app/Core/Database.php';
$db = App\\Core\\Database::getInstance();
$stmt = $db->query('SELECT id, company, name, image_path, canvas_data, created_at FROM quote_requests ORDER BY id DESC LIMIT 5');
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    echo "=== ID: {$r['id']} ({$r['created_at']}) ===" . PHP_EOL;
    echo "Main image_path: {$r['image_path']}" . PHP_EOL;
    $c = json_decode($r['canvas_data'] ?? '', true);
    if (!empty($c['floors'])) {
        echo "Floors count: " . count($c['floors']) . PHP_EOL;
        foreach ($c['floors'] as $i => $f) {
            $img = $f['image_path'] ?? 'NONE';
            $snapLen = isset($f['capturedImage']) ? strlen($f['capturedImage']) : 'UNSET';
            echo "  Floor $i: name={$f['name']}, img=$img, snapLen=$snapLen" . PHP_EOL;
        }
    } else {
        echo "No floors in canvas_data" . PHP_EOL;
    }
}
""")

cmd = "/www/server/php/82/bin/php /tmp/check_q.php"
stdin, stdout, stderr = ssh.exec_command(cmd)
print(stdout.read().decode('utf-8', errors='ignore'))
err = stderr.read().decode('utf-8', errors='ignore')
if err:
    print('ERR:', err)
sftp.remove('/tmp/check_q.php')
sftp.close()
ssh.close()
