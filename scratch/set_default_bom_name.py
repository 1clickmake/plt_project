filepath = 'views/vendor/quote_price.php'
with open(filepath, 'r', encoding='utf-8') as f:
    content = f.read()

# 1. Update PHP loop for initial BOM items if name is empty
content = content.replace(
    'value="<?= htmlspecialchars($b[\'name\']) ?>"',
    'value="<?= htmlspecialchars($b[\'name\'] ?: \'파렛트랙\') ?>"'
)

# 2. Update JS dynamically generated BOM rows to include value="파렛트랙"
content = content.replace(
    'placeholder="부품명 (예: 추가 타이빔)">',
    'value="파렛트랙" placeholder="부품명 (예: 파렛트랙)">'
)

content = content.replace(
    'placeholder="부품명 (예: 파렛트랙)">',
    'value="파렛트랙" placeholder="부품명 (예: 파렛트랙)">'
)

with open(filepath, 'w', encoding='utf-8', newline='') as f:
    f.write(content)

print("Updated default BOM part name to '파렛트랙' successfully!")
