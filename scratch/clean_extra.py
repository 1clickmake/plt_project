import paramiko, json

# 1. Clean local
import subprocess
clean_local_php = """<?php
require 'vendor/autoload.php';
$dotenv = Dotenv\\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();
require 'app/Core/Database.php';
$db = App\\Core\\Database::getInstance();
$stmt = $db->query("SELECT id, extra_files FROM quote_requests WHERE extra_files LIKE '%도면.jpg%'");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    $files = json_decode($r['extra_files'], true) ?: [];
    $filtered = array_values(array_filter($files, function($f) {
        return strpos($f['original_name'] ?? '', '도면.jpg') === false;
    }));
    $newVal = !empty($filtered) ? json_encode($filtered, JSON_UNESCAPED_UNICODE) : null;
    $upd = $db->prepare('UPDATE quote_requests SET extra_files = :extra WHERE id = :id');
    $upd->execute(['extra' => $newVal, 'id' => $r['id']]);
    echo "Cleaned local quote #{$r['id']}\\n";
}
echo "Local clean done.\\n";
"""
with open('scratch/do_clean.php', 'w', encoding='utf-8') as f:
    f.write(clean_local_php)

subprocess.run(['php', 'scratch/do_clean.php'])

# 2. Clean remote
ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
ssh.connect('115.68.223.138', port=22, username='root', password='01055403957w')
sftp = ssh.open_sftp()
with sftp.file('/tmp/clean_remote_extra.php', 'w') as f:
    f.write("""<?php
require '/www/wwwroot/cmake.work/vendor/autoload.php';
$dotenv = Dotenv\\Dotenv::createImmutable('/www/wwwroot/cmake.work');
$dotenv->load();
require '/www/wwwroot/cmake.work/app/Core/Database.php';
$db = App\\Core\\Database::getInstance();
$stmt = $db->query("SELECT id, extra_files FROM quote_requests WHERE extra_files LIKE '%도면.jpg%'");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    $files = json_decode($r['extra_files'], true) ?: [];
    $filtered = array_values(array_filter($files, function($f) {
        return strpos($f['original_name'] ?? '', '도면.jpg') === false;
    }));
    $newVal = !empty($filtered) ? json_encode($filtered, JSON_UNESCAPED_UNICODE) : null;
    $upd = $db->prepare('UPDATE quote_requests SET extra_files = :extra WHERE id = :id');
    $upd->execute(['extra' => $newVal, 'id' => $r['id']]);
    echo "Cleaned remote quote #{$r['id']}\\n";
}
echo "Remote clean done.\\n";
""")
cmd = '/www/server/php/82/bin/php /tmp/clean_remote_extra.php'
stdin, stdout, stderr = ssh.exec_command(cmd)
print(stdout.read().decode('utf-8', errors='ignore'))
sftp.remove('/tmp/clean_remote_extra.php')
sftp.close()
ssh.close()
