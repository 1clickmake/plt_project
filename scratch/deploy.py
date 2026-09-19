import paramiko
import os

host = '115.68.223.138'
port = 22
user = 'root'
password = '01055403957w'
remote_base = '/www/wwwroot/cmake.work'

files_to_upload = [
    'views/vendor/index.php'
]

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
try:
    ssh.connect(host, port, user, password)
    sftp = ssh.open_sftp()
    
    for f in files_to_upload:
        local_path = os.path.join(os.getcwd(), f)
        remote_path = f"{remote_base}/{f}"
        print(f"Uploading {f} to {remote_path}...")
        sftp.put(local_path, remote_path)
        
    sftp.close()
    print("All files uploaded successfully!")
except Exception as e:
    print(f"Error: {e}")
finally:
    ssh.close()
