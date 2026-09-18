import re

filepath = r'c:\Users\hades\OneDrive\Desktop\작업폴더\plt_project\public\assets\js\canvas2d-easy.js'
with open(filepath, 'r', encoding='utf-8') as f:
    content = f.read()

# We want to find window.updateEdgeLength = function (index, value) { ... }
# and replace it with the new version.

pattern = re.compile(r'window\.updateEdgeLength = function\s*\(\s*index,\s*value\s*\)\s*\{.*?(?=// --- 도면 자동 정렬|\Z)', re.DOTALL)

new_func = """window.updateEdgeLength = function(index, value, isAutoSync = false) {
    let valInt = parseInt(value) || 0;
    
    // 30m(30,000mm) 소형 창고 초과 감지 및 안내
    if (!isAutoSync && valInt > 30000) {
        valInt = 30000;
        const inputEl = document.getElementById(`edge-input-${index}`);
        if (inputEl) inputEl.value = 30000;
        if (typeof showCustomToast === 'function') {
            showCustomToast('⚠️ 웹 도면 그리기는 최대 30m(30,000mm) 창고까지 최적화되어 있습니다. 30m 초과 대형 물류창고는 현장 방문 실측 상담을 권장합니다.');
        } else if (typeof alert === 'function' && !window.__suppressLimitAlert) {
            window.__suppressLimitAlert = true;
            setTimeout(() => { window.__suppressLimitAlert = false; }, 3000);
            alert('⚠️ 웹 자동 설계는 최대 30m(30,000mm) 창고까지 지원됩니다.\\n30,000mm로 자동 조정되며, 초과 창고는 현장 실측 상담을 이용해주세요.');
        }
    }
    
    // 수동 입력 이력 배열에서 기존 인덱스 제거
    if (!isAutoSync) {
        const idx = userEnteredEdges.indexOf(index);
        if (idx > -1) {
            userEnteredEdges.splice(idx, 1);
        }
    }
    
    if (value !== '' && valInt > 0) {
        if (!isAutoSync) userEnteredEdges.push(index);
        edgeLengths[index] = valInt;
    } else {
        edgeLengths[index] = 0;
    }
    
    const numEdges = points.length - 1;

    // 4각형일 때 마주보는 변 자동 입력 (사용자가 직접 입력할 때만)
    if (!isAutoSync && numEdges === 4) {
        const oppositeIndex = (index + 2) % 4;
        const oppInput = document.getElementById(`edge-input-${oppositeIndex}`);
        if (oppInput) {
            oppInput.value = valInt > 0 ? valInt : '';
        }
        // 마주보는 변 데이터 동기화 (isAutoSync = true)
        updateEdgeLength(oppositeIndex, valInt > 0 ? valInt.toString() : '', true);
    }
    
    if (!isAutoSync) {
        // 만약 모든 변이 수동 입력되었다면, 가장 예전에 입력한 변 1개를 자동 계산 변으로 실시간 양보
        if (userEnteredEdges.length >= numEdges) {
            const oldestIndex = userEnteredEdges.shift();
            edgeLengths[oldestIndex] = 0;
            
            // 고유 ID 기반으로 폼 입력창 값 실시간 초기화
            const inputEl = document.getElementById(`edge-input-${oldestIndex}`);
            if (inputEl) {
                inputEl.value = '';
            }
        }
        
        alignAndScalePolygon(); 
        draw();
    }
};

"""

if pattern.search(content):
    new_content = pattern.sub(new_func.replace('\\', '\\\\'), content)
    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(new_content)
    print("Updated canvas2d-easy.js")
else:
    print("Pattern not found in canvas2d-easy.js")
