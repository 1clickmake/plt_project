import paramiko
import os

host = "115.68.223.138"
port = 22
username = "root"
password = "01055403957w"

files_to_upload = [
    ("C:\\Users\\hades\\OneDrive\\Desktop\\작업폴더\\plt_project\\views\\vendor\\quote_detail.php", "/www/wwwroot/cmake.work/views/vendor/quote_detail.php"),
    ("C:\\Users\\hades\\OneDrive\\Desktop\\작업폴더\\plt_project\\views\\vendor\\quote_price.php", "/www/wwwroot/cmake.work/views/vendor/quote_price.php"),
    ("C:\\Users\\hades\\OneDrive\\Desktop\\작업폴더\\plt_project\\views\\vendor\\quote_document.php", "/www/wwwroot/cmake.work/views/vendor/quote_document.php")
]

try:
    transport = paramiko.Transport((host, port))
    transport.connect(username=username, password=password)
    sftp = paramiko.SFTPClient.from_transport(transport)
    
    for local_path, remote_path in files_to_upload:
        print(f"Uploading {local_path} to {remote_path}...")
        sftp.put(local_path, remote_path)
        print(f"Upload successful for {remote_path}!")
        
    sftp.close()
    transport.close()
except Exception as e:
    print(f"Error: {e}")
