import paramiko
import os

host = '115.68.223.138'
port = 22
username = 'root'
password = '01055403957w'
remote_path = '/www/wwwroot/cmake.work'

local_file = r'c:\Users\hades\OneDrive\Desktop\작업폴더\plt_project\app\Services\AuditService.php'
remote_file = remote_path + '/app/Services/AuditService.php'

try:
    transport = paramiko.Transport((host, port))
    transport.connect(username=username, password=password)
    
    sftp = paramiko.SFTPClient.from_transport(transport)
    
    # Upload AuditService.php
    print("Uploading AuditService.php...")
    sftp.put(local_file, remote_file)
    print("Uploaded successfully.")
    
    sftp.close()
    
    # Execute command to append AUDIT_SALT to .env
    print("Updating .env...")
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    ssh.connect(host, port, username, password)
    
    env_append_cmd = f"grep -q 'AUDIT_SALT' {remote_path}/.env || echo '\nAUDIT_SALT=\"aJ9#vL2!mP8$xK1@qN4%cW7^bZ0&yH5*\"' >> {remote_path}/.env"
    stdin, stdout, stderr = ssh.exec_command(env_append_cmd)
    print(stdout.read().decode())
    print(stderr.read().decode())
    print(".env updated successfully.")
    
    ssh.close()
    transport.close()
    
except Exception as e:
    print(f"Error: {e}")
