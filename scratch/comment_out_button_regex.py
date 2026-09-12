import os
import glob
import re

directory = r"c:\Users\hades\OneDrive\Desktop\작업폴더\plt_project\views\vendor"
files = glob.glob(os.path.join(directory, "*.php"))

pattern = re.compile(r'(<a href="/vendor/addon_payment"[^>]*>.*?횟수 충전.*?</a>)', re.DOTALL)

for file_path in files:
    try:
        with open(file_path, 'r', encoding='utf-8') as f:
            content = f.read()
    except UnicodeDecodeError:
        try:
            with open(file_path, 'r', encoding='utf-16') as f:
                content = f.read()
        except:
            print(f"Failed to read {file_path}")
            continue

    if pattern.search(content):
        new_content = pattern.sub(r'<!-- \1 -->', content)
        with open(file_path, 'w', encoding='utf-8') as f:
            f.write(new_content)
        print(f"Replaced in {os.path.basename(file_path)}")
