import paramiko

host = '115.68.223.138'
port = 22
user = 'root'
password = '01055403957w'

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
try:
    ssh.connect(host, port, user, password)
    stdin, stdout, stderr = ssh.exec_command("tail -n 30 /www/wwwlogs/cmake.work-error_log")
    print("STDOUT:", stdout.read().decode())
except Exception as e:
    print(f"Error: {e}")
finally:
    ssh.close()
