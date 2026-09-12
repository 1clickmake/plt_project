import paramiko

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
ssh.connect('115.68.223.138', port=22, username='root', password='01055403957w')

sftp = ssh.open_sftp()
with sftp.file('/tmp/sync_extra_files.php', 'w') as f:
    f.write("""<?php
require '/www/wwwroot/cmake.work/vendor/autoload.php';
$dotenv = Dotenv\\Dotenv::createImmutable('/www/wwwroot/cmake.work');
$dotenv->load();
require '/www/wwwroot/cmake.work/app/Core/Database.php';
$db = App\\Core\\Database::getInstance();
$stmt = $db->query("SELECT id, canvas_data, extra_files FROM quote_requests WHERE canvas_data LIKE '%floors%'");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    $c = json_decode($r['canvas_data'] ?? '', true);
    if (!empty($c['floors']) && count($c['floors']) > 1) {
        $existing = json_decode($r['extra_files'] ?? '[]', true) ?: [];
        $existingPaths = array_column($existing, 'path');
        $added = false;
        foreach ($c['floors'] as $flr) {
            if (!empty($flr['image_path']) && !in_array($flr['image_path'], $existingPaths)) {
                $existing[] = [
                    'path' => $flr['image_path'],
                    'original_name' => ($flr['name'] ?? '구역') . ' 설계 도면.jpg'
                ];
                $added = true;
            }
        }
        if ($added) {
            $upd = $db->prepare("UPDATE quote_requests SET extra_files = :extra WHERE id = :id");
            $upd->execute([
                'extra' => json_encode($existing, JSON_UNESCAPED_UNICODE),
                'id' => $r['id']
            ]);
            echo "Updated quote #{$r['id']} with floor drawings in extra_files." . PHP_EOL;
        }
    }
}
echo "Done sync." . PHP_EOL;
""")

cmd = "/www/server/php/82/bin/php /tmp/sync_extra_files.php"
stdin, stdout, stderr = ssh.exec_command(cmd)
print(stdout.read().decode('utf-8', errors='ignore'))
err = stderr.read().decode('utf-8', errors='ignore')
if err:
    print('ERR:', err)
sftp.remove('/tmp/sync_extra_files.php')
sftp.close()
ssh.close()
