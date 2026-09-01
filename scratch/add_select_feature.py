filepath = 'views/vendor/quote_price.php'
with open(filepath, 'r', encoding='utf-8') as f:
    content = f.read()

target = '''        // 🌟 실시간 이벤트 리스너 등록 (BOM 수량/단가/품명/규격 입력 시 자동 재계산 및 리스트 동기화)'''

replacement = '''        // 🌟 모든 인풋박스 클릭/포커스 시 전체 텍스트 자동 선택(Select) 기능
        document.addEventListener('focusin', function(e) {
            if (e.target && e.target.tagName === 'INPUT' && e.target.type !== 'button' && e.target.type !== 'submit' && e.target.type !== 'hidden') {
                setTimeout(() => {
                    try { e.target.select(); } catch(err) {}
                }, 30);
            }
        });

        // 🌟 실시간 이벤트 리스너 등록 (BOM 수량/단가/품명/규격 입력 시 자동 재계산 및 리스트 동기화)'''

content = content.replace(target, replacement)

with open(filepath, 'w', encoding='utf-8', newline='') as f:
    f.write(content)

print("Added auto-select input text functionality successfully!")
