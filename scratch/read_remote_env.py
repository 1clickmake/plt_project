import paramiko

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
ssh.connect('115.68.223.138', username='root', password='01055403957w')

cmd = "cat /www/wwwroot/cmake.work/.env"
stdin, stdout, stderr = ssh.exec_command(cmd)
print("REMOTE .ENV:\n", stdout.read().decode('utf-8'))
ssh.close()
