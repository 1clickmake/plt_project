filepath = 'views/vendor/quote_price.php'
with open(filepath, 'r', encoding='utf-8') as f:
    content = f.read()

target = '''            // BOM 부품 삭제
            if (e.target.closest('.btn-del-bom')) {
                const row = e.target.closest('.bom-item-row');
                if (row) {
                    row.remove();
                    recalculateAll();
                }
            }'''

replacement = '''            // BOM 부품 삭제 (모든 부품 삭제 시 상단 리스트 행도 함께 삭제)
            if (e.target.closest('.btn-del-bom')) {
                const row = e.target.closest('.bom-item-row');
                if (row) {
                    const tbody = row.closest('.bom-tbody');
                    row.remove();
                    if (tbody && tbody.querySelectorAll('.bom-item-row').length === 0) {
                        const collapseRow = tbody.closest('.bom-collapse-row');
                        if (collapseRow) {
                            const moduleRow = collapseRow.previousElementSibling;
                            if (moduleRow && moduleRow.classList.contains('module-row')) {
                                moduleRow.remove();
                            }
                            collapseRow.remove();
                        }
                    }
                    recalculateAll();
                }
            }'''

content = content.replace(target, replacement)

with open(filepath, 'w', encoding='utf-8', newline='') as f:
    f.write(content)

print("Updated BOM delete logic successfully!")
