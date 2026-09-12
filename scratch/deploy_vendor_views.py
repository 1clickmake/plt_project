import paramiko
import os
import glob

host = '115.68.223.138'
port = 22
username = 'root'
password = '01055403957w'
remote_base = '/www/wwwroot/cmake.work/views/vendor'
local_dir = r'c:\Users\hades\OneDrive\Desktop\작업폴더\plt_project\views\vendor'

files_to_upload = glob.glob(os.path.join(local_dir, "*.php"))

try:
    print(f"Connecting to {host}:{port} as {username}...")
    transport = paramiko.Transport((host, port))
    transport.connect(username=username, password=password)
    sftp = paramiko.SFTPClient.from_transport(transport)
    
    for local_path in files_to_upload:
        filename = os.path.basename(local_path)
        remote_path = f"{remote_base}/{filename}"
        print(f"Uploading {filename} -> {remote_path}")
        sftp.put(local_path, remote_path)
        
    sftp.close()
    transport.close()
    print("All vendor views uploaded successfully!")
except Exception as e:
    print(f"Deployment failed: {str(e)}")
