import paramiko

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
ssh.connect('115.68.223.138', username='root', password='01055403957w')

cmd = """mysql -u sql_cmake_sv26ab -p'01055403957w' sql_cmake_sv26ab -e "
UPDATE suppliers SET vendor_user_id = (SELECT id FROM users ORDER BY id ASC LIMIT 1) WHERE vendor_user_id = 0 OR vendor_user_id IS NULL;
SELECT * FROM suppliers;
"
"""

stdin, stdout, stderr = ssh.exec_command(cmd)
print("STDOUT:", stdout.read().decode('utf-8'))
print("STDERR:", stderr.read().decode('utf-8'))
ssh.close()
