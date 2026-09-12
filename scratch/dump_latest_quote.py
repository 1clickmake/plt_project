import paramiko

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
ssh.connect('115.68.223.138', port=22, username='root', password='01055403957w')
sftp = ssh.open_sftp()
with sftp.file('/tmp/dump_q.php', 'w') as f:
    f.write("""<?php
require '/www/wwwroot/cmake.work/vendor/autoload.php';
$dotenv = Dotenv\\Dotenv::createImmutable('/www/wwwroot/cmake.work');
$dotenv->load();
require '/www/wwwroot/cmake.work/app/Core/Database.php';
$db = App\\Core\\Database::getInstance();
$stmt = $db->query('SELECT id, company, canvas_data, rack_spec, rack_indep, rack_conn, rack_small_conn, rack_bypass, rack_holders, rack_pallets, edge_lengths FROM quote_requests ORDER BY id DESC LIMIT 1');
$r = $stmt->fetch(PDO::FETCH_ASSOC);
echo "=== QUOTE ID: {$r['id']} ===" . PHP_EOL;
echo "Rack spec: {$r['rack_spec']}" . PHP_EOL;
echo "Indep: {$r['rack_indep']}, Conn: {$r['rack_conn']}, Small: {$r['rack_small_conn']}, Bypass: {$r['rack_bypass']}, Holders: {$r['rack_holders']}, Pallets: {$r['rack_pallets']}" . PHP_EOL;
echo "Edge lengths: {$r['edge_lengths']}" . PHP_EOL;
$c = json_decode($r['canvas_data'], true);
if (!empty($c['floors'])) {
    foreach ($c['floors'] as $i => $f) {
        echo "--- FLOOR $i ({$f['name']}) ---" . PHP_EOL;
        echo "  bays: " . ($f['bays'] ?? '') . ", indep: " . ($f['indep'] ?? '') . ", conn: " . ($f['conn'] ?? '') . ", smallConn: " . ($f['smallConn'] ?? '') . ", bypass: " . ($f['bypass'] ?? '') . ", pallets: " . ($f['pallets'] ?? '') . PHP_EOL;
        echo "  palletSpec: " . json_encode($f['palletSpec'] ?? [], JSON_UNESCAPED_UNICODE) . PHP_EOL;
        echo "  edgeLengths: " . json_encode($f['edgeLengths'] ?? [], JSON_UNESCAPED_UNICODE) . PHP_EOL;
        echo "  userEnteredEdges: " . json_encode($f['userEnteredEdges'] ?? [], JSON_UNESCAPED_UNICODE) . PHP_EOL;
        echo "  racks count: " . (isset($f['racks']) ? count($f['racks']) : 'none') . PHP_EOL;
    }
} else {
    echo "NO FLOORS in canvas_data\n";
}
""")
cmd = '/www/server/php/82/bin/php /tmp/dump_q.php'
stdin, stdout, stderr = ssh.exec_command(cmd)
print(stdout.read().decode('utf-8', errors='ignore'))
sftp.remove('/tmp/dump_q.php')
sftp.close()
ssh.close()
