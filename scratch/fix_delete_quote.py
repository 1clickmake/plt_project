import re

file_path = 'app/Controllers/VendorController.php'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

# Replace "public function deleteQuote($id) {" with "public function deleteQuote($vars) { $id = $vars['id'] ?? null;"
new_content = content.replace("public function deleteQuote($id) {", "public function deleteQuote($vars) {\n        $id = $vars['id'] ?? null;")

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(new_content)

print("Fixed deleteQuote signature!")
