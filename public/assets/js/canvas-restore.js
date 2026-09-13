/**
 * canvas-restore.js
 * 데이터베이스에 저장된 도면 데이터를 완벽하게 복원하고 캔버스 인터랙션을 활성화하는 전용 스크립트
 */
document.addEventListener('DOMContentLoaded', function() {
    if (typeof window.RAW_RESTORE_DATA === 'undefined') return;
    
    setTimeout(() => {
        try {
            const raw = window.RAW_RESTORE_DATA;
            if (!raw) return;

            console.log("🔄 [Restore] 도면 데이터 복원을 시작합니다...", raw);
            
            // 1. canvas2d 내부 핵심 변수 및 캔버스 복원
            if (typeof window.restoreCanvasData === 'function') {
                window.restoreCanvasData(raw);
            } else {
                // fallback
                if (raw.points && Array.isArray(raw.points) && typeof points !== 'undefined') {
                    points.length = 0;
                    points.push(...raw.points);
                    let minX = Infinity, minY = Infinity;
                    points.forEach(p => {
                        if (p.x < minX) minX = p.x;
                        if (p.y < minY) minY = p.y;
                    });
                    if (isFinite(minX)) window.canvasOriginX = minX;
                    if (isFinite(minY)) window.canvasOriginY = minY;
                }
                if (raw.racks && Array.isArray(raw.racks) && typeof racks !== 'undefined') {
                    racks.length = 0;
                    racks.push(...raw.racks);
                }
                if (raw.obstacles && Array.isArray(raw.obstacles) && typeof obstacles !== 'undefined') {
                    obstacles.length = 0;
                    obstacles.push(...raw.obstacles);
                }
                if (raw.currentScale) {
                    window.currentScale = raw.currentScale;
                }
                if (typeof draw === 'function') draw();
            }
            
            // 2. 파렛트 및 랙 상세 사양 폼 필드 복원
            if (raw.pallet_w && document.getElementById('pallet-w')) document.getElementById('pallet-w').value = raw.pallet_w;
            if (raw.pallet_d && document.getElementById('pallet-d')) document.getElementById('pallet-d').value = raw.pallet_d;
            if (raw.pallet_h && document.getElementById('pallet-h')) document.getElementById('pallet-h').value = raw.pallet_h;
            if (raw.pallet_weight && document.getElementById('pallet-weight')) document.getElementById('pallet-weight').value = raw.pallet_weight;
            if (raw.rack_levels && document.getElementById('rack-levels')) document.getElementById('rack-levels').value = raw.rack_levels;
            if (raw.rack_height && document.getElementById('rack-height')) document.getElementById('rack-height').value = raw.rack_height;
            
            // 3. 멀티 플로어 지원
            if (raw.floors && Array.isArray(raw.floors)) {
                window.multiFloorsData = raw.floors;
            }
            
            // 4. UI 뱃지 업데이트
            if (typeof updateRackFormCounts === 'function') {
                updateRackFormCounts();
            }
            
            // 5. 리모컨 및 견적/도면저장 버튼 활성화
            const btn = document.getElementById('run-layout-btn');
            if (btn) {
                btn.innerHTML = '✅ 도면 활성화 완료';
                btn.classList.remove('btn-primary-gradient');
                btn.classList.add('btn-success');
            }
            const remoteCtrl = document.getElementById('canvas-remote-ctrl');
            if (remoteCtrl) remoteCtrl.classList.remove('d-none');
            
            const remoteQuoteBtn = document.getElementById('remote-quote-btn');
            if (remoteQuoteBtn) {
                remoteQuoteBtn.classList.remove('d-none');
                remoteQuoteBtn.innerHTML = '💾 도면 저장';
            }
            
            // 6. 왼쪽 패널 2단계/3단계 블록 열기
            const lengthSection = document.getElementById('wall-length-section');
            if (lengthSection) lengthSection.style.display = 'block';
            
            const reqSection = document.getElementById('quote-req-section');
            if (reqSection) reqSection.style.display = 'block';
            
            // 7. 선분 입력칸들 활성화 및 값 주입
            const pts = (raw.points && raw.points.length > 2) ? raw.points : (window.points || []);
            if (pts && pts.length > 2) {
                let html = '';
                const numEdges = pts.length - 1;
                
                let savedEdgeValues = [];
                if (raw.edgeLengths && Array.isArray(raw.edgeLengths)) {
                    savedEdgeValues = raw.edgeLengths;
                } else if (raw.edge_lengths_str) {
                    savedEdgeValues = raw.edge_lengths_str.split(',').map(v => v.trim());
                }

                for (let i = 1; i <= numEdges; i++) {
                    let existingVal = savedEdgeValues[i-1] || '';
                    if (!existingVal && typeof edgeLengths !== 'undefined' && edgeLengths[i-1] > 0) {
                        existingVal = edgeLengths[i-1];
                    }
                    html += `
                    <div class="col-6 mb-3">
                        <label class="form-label small mb-1 fw-bold text-secondary">${i}번 선분 (mm)</label>
                        <input type="text" inputmode="numeric" pattern="[0-9]*" id="edge-input-${i-1}" class="form-control form-control-sm bg-white text-dark border-secondary small" value="${existingVal}" oninput="this.value=this.value.replace(/[^0-9]/g, ''); updateEdgeLength(${i-1}, this.value)">
                    </div>`;
                }
                const inputsContainer = document.getElementById('chat-inputs-container');
                if (inputsContainer) {
                    inputsContainer.innerHTML = `<div class="row g-2">${html}</div>`;
                }
                const diag = document.getElementById('diagram-container');
                if (diag) {
                    diag.innerHTML = `<p class="text-success fw-bold py-3 m-0">🎉 총 ${numEdges}각형 도면이 복원되었습니다!</p>`;
                }
            }
            
            // 8. 캔버스 최종 리드로우
            if (typeof draw === 'function') draw();

            console.log("✅ [Restore] 저장된 도면 완벽 복원 및 인터랙션 활성화 완료!");
        } catch (e) {
            console.error("❌ 도면 복원 중 오류 발생:", e);
        }
    }, 450);
});
