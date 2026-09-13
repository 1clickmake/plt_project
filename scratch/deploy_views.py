import paramiko
import os

host = '115.68.223.138'
port = 22
username = 'root'
password = '01055403957w'
remote_path = '/www/wwwroot/cmake.work'

local_main = r'c:\Users\hades\OneDrive\Desktop\작업폴더\plt_project\views\templates\basic\main.php'
remote_main = remote_path + '/views/templates/basic/main.php'

local_website = r'c:\Users\hades\OneDrive\Desktop\작업폴더\plt_project\views\website.php'
remote_website = remote_path + '/views/website.php'

local_chat = r'c:\Users\hades\OneDrive\Desktop\작업폴더\plt_project\views\canvas\chat.php'
remote_chat = remote_path + '/views/canvas/chat.php'

local_index = r'c:\Users\hades\OneDrive\Desktop\작업폴더\plt_project\views\canvas\index.php'
remote_index = remote_path + '/views/canvas/index.php'

try:
    transport = paramiko.Transport((host, port))
    transport.connect(username=username, password=password)
    
    sftp = paramiko.SFTPClient.from_transport(transport)
    
    print("Uploading main.php...")
    sftp.put(local_main, remote_main)
    print("Uploading website.php...")
    sftp.put(local_website, remote_website)
    print("Uploading chat.php...")
    sftp.put(local_chat, remote_chat)
    print("Uploading index.php...")
    sftp.put(local_index, remote_index)
    print("Uploaded successfully.")
    
    sftp.close()
    transport.close()
    
except Exception as e:
    print(f"Error: {e}")
