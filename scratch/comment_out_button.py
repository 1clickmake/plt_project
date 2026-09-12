import os
import glob

directory = r"c:\Users\hades\OneDrive\Desktop\작업폴더\plt_project\views\vendor"
files = glob.glob(os.path.join(directory, "*.php"))

target_str = '''<a href="/vendor/addon_payment" class="btn btn-outline-warning btn-sm fw-bold px-3 py-1 me-3" style="border-radius: 10px;">
                    <i class="fa-solid fa-bolt"></i> 횟수 충전
                </a>'''

replacement_str = '''<!-- <a href="/vendor/addon_payment" class="btn btn-outline-warning btn-sm fw-bold px-3 py-1 me-3" style="border-radius: 10px;">
                    <i class="fa-solid fa-bolt"></i> 횟수 충전
                </a> -->'''

for file_path in files:
    with open(file_path, 'r', encoding='utf-8') as f:
        content = f.read()
    
    if target_str in content:
        new_content = content.replace(target_str, replacement_str)
        with open(file_path, 'w', encoding='utf-8') as f:
            f.write(new_content)
        print(f"Replaced in {os.path.basename(file_path)}")
