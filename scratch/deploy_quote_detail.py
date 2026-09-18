import paramiko
import os

host = "115.68.223.138"
port = 22
username = "root"
password = "01055403957w"

local_path = r"C:\Users\hades\OneDrive\Desktop\작업폴더\plt_project\views\vendor\quote_detail.php"
remote_path = "/www/wwwroot/cmake.work/views/vendor/quote_detail.php"

print(f"Uploading {local_path} to {remote_path}...")

try:
    transport = paramiko.Transport((host, port))
    transport.connect(username=username, password=password)
    sftp = paramiko.SFTPClient.from_transport(transport)
    sftp.put(local_path, remote_path)
    sftp.close()
    transport.close()
    print("Upload successful!")
except Exception as e:
    print(f"Error: {e}")
