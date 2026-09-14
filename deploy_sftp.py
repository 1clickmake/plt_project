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
    'views/vendor/pricing_sheet.php',
    'views/vendor/quote_price.php',
    'views/vendor/quote_document.php',
    'views/vendor/quote_detail.php',
    'views/vendor/quotes.php',
    'views/vendor/profiles.php',
    'views/vendor/sidebar.php',
    'app/Controllers/VendorController.php',
    'app/Controllers/CanvasController.php',
    'app/Controllers/HomeController.php',
    'app/Controllers/BootpayController.php',
    'app/Controllers/AuthController.php',
    'app/routes.php',
    'views/templates/basic/header.php',
    'views/templates/basic/main.php',
    'views/templates/basic/about.php',
    'views/website.php',
    'public/assets/js/canvas2d.js',
    'public/assets/js/canvas-interactions.js',
    'public/assets/js/canvas2d-easy.js',
    'public/assets/js/canvas-interactions-easy.js',
    'public/assets/js/canvas-restore.js',
    'public/assets/js/tutorial.js',
    'views/canvas/index.php',
    'views/canvas/cad.php',
    'views/canvas/easy.php',
    'views/canvas/chat.php',
    'views/install/setup.sql',
    'views/shop/subscribe.php',
    'views/vendor/addon_payment.php',
    'views/errors/404.php',
    'public/index.php',
    'storage/templates/pallet_rack_price_template.xlsx',
    'public/asamiya_profile.png'
]

def sftp_mkdirs(sftp, remote_dir):
    dirs = []
    while len(remote_dir) > 1:
        dirs.append(remote_dir)
        remote_dir = os.path.dirname(remote_dir)
    while dirs:
        d = dirs.pop()
        try:
            sftp.stat(d)
        except IOError:
            try:
                sftp.mkdir(d)
            except Exception:
                pass

try:
    print(f"Connecting to {host}:{port} as {username}...")
    transport = paramiko.Transport((host, port))
    transport.connect(username=username, password=password)
    sftp = paramiko.SFTPClient.from_transport(transport)
    
    for f in files_to_upload:
        local_path = os.path.join(os.getcwd(), f.replace('/', os.sep))
        if os.path.exists(local_path):
            remote_path = f"{remote_base}/{f}"
            print(f"Uploading {f} -> {remote_path}")
            
            # Create remote directory if not exists
            remote_dir = os.path.dirname(remote_path)
            sftp_mkdirs(sftp, remote_dir)
                
            sftp.put(local_path, remote_path)
        else:
            print(f"Local file not found: {f}")
            
    sftp.close()
    transport.close()
    print("All files uploaded successfully!")

    # Live server DB migration check via SSH
    print("Checking live DB schema via SSH...")
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    ssh.connect(host, port=port, username=username, password=password)
    stdin, stdout, stderr = ssh.exec_command(f"cd {remote_base} && php -r 'require \"vendor/autoload.php\"; \$dotenv = Dotenv\\Dotenv::createImmutable(\".\"); \$dotenv->load(); \$db = \\App\\Core\\Database::getInstance(); try {{ \$db->exec(\"ALTER TABLE suppliers ADD COLUMN pricing_password VARCHAR(255) DEFAULT NULL COMMENT \\'단가표 보안 2차 비밀번호\\' AFTER excel_file\"); echo \"Live DB updated successfully!\"; }} catch (Exception \$e) {{ echo \"Notice: \" . \$e->getMessage(); }}'")
    out = stdout.read().decode('utf-8')
    err = stderr.read().decode('utf-8')
    print("Live DB Result:", out if out else err)
    ssh.close()
except Exception as e:
    print(f"Error: {e}")
    sys.exit(1)
