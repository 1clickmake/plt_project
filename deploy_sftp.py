import paramiko
import os
import sys

host = '115.68.223.138'
port = 22
username = 'root'
password = '01055403957w'
remote_base = '/www/wwwroot/cmake.work'

files_to_upload = [
    'views/vendor/pricing.php',
    'views/vendor/quote_price.php',
    'views/vendor/sidebar.php',
    'app/Controllers/VendorController.php',
    'app/Controllers/CanvasController.php',
    'app/routes.php',
    'public/assets/js/canvas2d.js',
    'public/assets/js/canvas-interactions.js',
    'public/assets/js/canvas2d-easy.js',
    'public/assets/js/canvas-interactions-easy.js',
    'views/canvas/index.php',
    'views/canvas/board.php',
    'views/canvas/easy.php',
    'views/canvas/chat.php',
    'public/asamiya_profile.png',
    'public/assets/images/asamiya_profile.png'
]

try:
    print(f"Connecting to {host}:{port} as {username}...")
    transport = paramiko.Transport((host, port))
    transport.connect(username=username, password=password)
    sftp = paramiko.SFTPClient.from_transport(transport)
    
    for f in files_to_upload:
        local_path = os.path.join(os.getcwd(), f)
        if os.path.exists(local_path):
            remote_path = f"{remote_base}/{f}"
            print(f"Uploading {f} -> {remote_path}")
            
            # Create remote directory if not exists
            remote_dir = os.path.dirname(remote_path)
            try:
                sftp.stat(remote_dir)
            except IOError:
                sftp.mkdir(remote_dir)
                
            sftp.put(local_path, remote_path)
        else:
            print(f"Local file not found: {f}")
            
    sftp.close()
    transport.close()
    print("All files uploaded successfully!")
except Exception as e:
    print(f"Error: {e}")
    sys.exit(1)
