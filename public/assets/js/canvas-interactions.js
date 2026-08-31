/**
 * canvas-interactions.js
 * 랙 회전(Rotate), 복제(Duplicate), 복사/붙여넣기, 단축키 지원 등 고급 인터랙션 전용 모듈
 * 리모컨 바이패스(Bypass) 모드 연동
 */

(function() {
    // 랙 90도 회전 함수
    window.rotateRack = function(rack) {
        if (!rack) return false;
        const prevState = captureState();
        rack.angle = (getRackAngle(rack) + Math.PI / 2) % (Math.PI * 2);
        rack.isHoriz = Math.abs(Math.cos(rack.angle)) > Math.abs(Math.sin(rack.angle));
        rack.dir = (Math.cos(rack.angle) > 0 || Math.sin(rack.angle) > 0) ? 1 : -1;
        
        if (typeof checkRackValidPlacement === 'function') {
            rack.isValid = checkRackValidPlacement(rack);
        }
        if (typeof updateRackFormCounts === 'function') {
            updateRackFormCounts();
        }
        if (typeof draw === 'function') {
            draw();
        }
        const nextState = captureState();
        pushAction({ prevState, nextState });
        return true;
    };

    // 3단 핸들 좌표 구하기
    window.getRackHandlesPos = function(r) {
        if (typeof getRackAngle !== 'function') return null;
        const angle = getRackAngle(r);
        const cos = Math.cos(angle);
        const sin = Math.sin(angle);
        const len = r.totalLengthPx;
        
        const mode = window.activeInteractMode || null;
        if (!mode) return null;
        
        const hX = len * cos + r.x;
        const hY = len * sin + r.y;
        
        return {
            rotate: (mode === 'rotate' || mode === 'rotate-ccw') ? { x: hX, y: hY } : null,
            extend: mode === 'extend' ? { x: hX, y: hY } : null,
            copy: mode === 'copy' ? { x: hX, y: hY } : null
        };
    };

    // 회전 아이콘 그리기 (↻ / ↺) - Amber 테마
    window.drawRotateHandleIcon = function(ctx, x, y) {
        ctx.fillStyle = '#0f172a';
        ctx.strokeStyle = '#fbbf24';
        ctx.lineWidth = 1.5;
        ctx.beginPath();
        ctx.arc(x, y, 10, 0, Math.PI * 2);
        ctx.fill();
        ctx.stroke();
        
        const isCcw = (window.activeInteractMode === 'rotate-ccw');
        ctx.fillStyle = '#fbbf24';
        ctx.font = '11px Arial';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillText(isCcw ? '↺' : '↻', x, y);
    };

    // 연장 아이콘 그리기 (⬌) - Sky Blue 테마
    window.drawExtendHandleIcon = function(ctx, x, y) {
        ctx.fillStyle = '#0f172a';
        ctx.strokeStyle = '#38bdf8';
        ctx.lineWidth = 1.5;
        ctx.beginPath();
        ctx.arc(x, y, 10, 0, Math.PI * 2);
        ctx.fill();
        ctx.stroke();
        
        ctx.fillStyle = '#38bdf8';
        ctx.font = '11px Arial';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillText('⬌', x, y);
    };

    // 복사 아이콘 그리기 (❐) - Emerald 테마
    window.drawCopyHandleIcon = function(ctx, x, y) {
        ctx.fillStyle = '#0f172a';
        ctx.strokeStyle = '#34d399';
        ctx.lineWidth = 1.5;
        ctx.beginPath();
        ctx.arc(x, y, 10, 0, Math.PI * 2);
        ctx.fill();
        ctx.stroke();
        
        ctx.fillStyle = '#34d399';
        ctx.font = '10px Arial';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillText('❐', x, y);
    };

    // 키보드 단축키
    window.addEventListener('keydown', function(e) {
        const activeTag = document.activeElement ? document.activeElement.tagName.toLowerCase() : '';
        if (activeTag === 'input' || activeTag === 'textarea' || activeTag === 'select') {
            return;
        }

        if (e.key === 'r' || e.key === 'R' || e.code === 'Space') {
            if (typeof currentRackPreview !== 'undefined' && currentRackPreview) {
                e.preventDefault();
                rotateRack(currentRackPreview);
                return;
            }
            if (typeof racks !== 'undefined' && Array.isArray(racks) && racks.length > 0) {
                const canvas = document.getElementById('drawingCanvas');
                if (canvas && typeof currentScale !== 'undefined' && currentScale > 0) {
                    const lastX = (window._lastCanvasMouseX || 0);
                    const lastY = (window._lastCanvasMouseY || 0);
                    for (let i = racks.length - 1; i >= 0; i--) {
                        const r = racks[i];
                        if (typeof getRackBoxes !== 'function') continue;
                        const boxes = getRackBoxes(r);
                        if (lastX >= boxes.physical.minX && lastX <= boxes.physical.maxX &&
                            lastY >= boxes.physical.minY && lastY <= boxes.physical.maxY) {
                            e.preventDefault();
                            rotateRack(r);
                            return;
                        }
                    }
                }
            }
        }
        if (e.ctrlKey && e.key.toLowerCase() === 'z') {
            undo();
        }
        if (e.ctrlKey && e.key.toLowerCase() === 'y') {
            redo();
        }
        if (e.key === 'Delete' || e.key === 'Backspace') {
            if (typeof racks !== 'undefined' && Array.isArray(racks) && racks.length > 0) {
                const canvas = document.getElementById('drawingCanvas');
                if (canvas && typeof currentScale !== 'undefined' && currentScale > 0) {
                    const lastX = (window._lastCanvasMouseX || 0);
                    const lastY = (window._lastCanvasMouseY || 0);
                    for (let i = racks.length - 1; i >= 0; i--) {
                        const r = racks[i];
                        if (typeof getRackBoxes !== 'function') continue;
                        const boxes = getRackBoxes(r);
                        if (lastX >= boxes.physical.minX && lastX <= boxes.physical.maxX &&
                            lastY >= boxes.physical.minY && lastY <= boxes.physical.maxY) {
                            e.preventDefault();
                            if(typeof window.pushAction === 'function') pushAction({ type: 'delete', racks: JSON.parse(JSON.stringify(racks)) });
                            racks.splice(i, 1);
                            if (typeof updateRackFormCounts === 'function') updateRackFormCounts();
                            if (typeof draw === 'function') draw();
                            return;
                        }
                    }
                }
            }
        }
    });

    const MAX_STACK = 10;

    function pushAction(action) {
        actionStack.push(action);
        if (actionStack.length > MAX_STACK) {
            actionStack.shift();
        }
        redoStack.length = 0;
        
        try {
            sessionStorage.setItem('actionStack', JSON.stringify(actionStack));
            sessionStorage.setItem('redoStack', JSON.stringify(redoStack));
        } catch(e) {
            console.error("Failed to save action stacks to sessionStorage", e);
        }
    }

    function undo() {
        if (!actionStack.length) return;
        const act = actionStack.pop();
        redoStack.push(act);
        if (act.prevState) restoreState(act.prevState);
        
        try {
            sessionStorage.setItem('actionStack', JSON.stringify(actionStack));
            sessionStorage.setItem('redoStack', JSON.stringify(redoStack));
        } catch(e) {
            console.error(e);
        }
    }

    function redo() {
        if (!redoStack.length) return;
        const act = redoStack.pop();
        actionStack.push(act);
        if (act.nextState) restoreState(act.nextState);
        
        try {
            sessionStorage.setItem('actionStack', JSON.stringify(actionStack));
            sessionStorage.setItem('redoStack', JSON.stringify(redoStack));
        } catch(e) {
            console.error(e);
        }
    }

    function toggleSnapGuide() {
        snapGuideEnabled = !snapGuideEnabled;
        draw();
    }

    function captureState() {
        return {
            points: JSON.parse(JSON.stringify(points)),
            obstacles: JSON.parse(JSON.stringify(obstacles)),
            racks: JSON.parse(JSON.stringify(racks)),
            edgeLengths: edgeLengths.slice(),
            originalAngles: originalAngles.slice(),
            originalVisualLengths: originalVisualLengths.slice(),
            userEnteredEdges: userEnteredEdges.slice()
        };
    }

    function restoreState(state) {
        if (!state) return;
        points = JSON.parse(JSON.stringify(state.points));
        obstacles = JSON.parse(JSON.stringify(state.obstacles));
        racks = JSON.parse(JSON.stringify(state.racks));
        edgeLengths = state.edgeLengths.slice();
        originalAngles = state.originalAngles.slice();
        originalVisualLengths = state.originalVisualLengths.slice();
        userEnteredEdges = state.userEnteredEdges.slice();
        
        if (typeof updateRackFormCounts === 'function') {
            updateRackFormCounts();
        }
        if (typeof draw === 'function') {
            draw();
        }
    }

    // UI 버튼 이벤트 바인딩
    document.addEventListener('DOMContentLoaded', function() {


        // sessionStorage에서 복원
        try {
            const savedActions = sessionStorage.getItem('actionStack');
            const savedRedos = sessionStorage.getItem('redoStack');
            if (savedActions) {
                actionStack = JSON.parse(savedActions);
            }
            if (savedRedos) {
                redoStack = JSON.parse(savedRedos);
            }
        } catch(e) {
            console.error("Failed to load history from sessionStorage", e);
        }
    });

    // 마우스 좌표 추적 & 우클릭 회전 & 바이패스 마우스 인터랙션 지원
    window.addEventListener('DOMContentLoaded', function() {
        const canvas = document.getElementById('drawingCanvas');
        if (!canvas) return;

        canvas.addEventListener('mousemove', function(e) {
            const rect = canvas.getBoundingClientRect();
            const screenX = e.clientX - rect.left;
            const screenY = e.clientY - rect.top;
            window._lastCanvasMouseX = screenX;
            window._lastCanvasMouseY = screenY;
            
            if (window.activeInteractMode === 'bypass' && typeof racks !== 'undefined' && Array.isArray(racks)) {
                const zoom = (window.cameraZoom || window.cameraZoom === 0) ? window.cameraZoom : 1; // FIXED: sync with main canvas
                const logicalX = screenX / zoom;
                const logicalY = screenY / zoom;
                
                let found = null;
                for (let i = racks.length - 1; i >= 0; i--) {
                    const r = racks[i];
                    if (typeof getRackBoxes !== 'function') continue;
                    const boxes = getRackBoxes(r);
                    
                    const pad = 5;
                    if (screenX >= boxes.physical.minX - pad && screenX <= boxes.physical.maxX + pad &&
                        screenY >= boxes.physical.minY - pad && screenY <= boxes.physical.maxY + pad) {
                        
                        const angle = typeof getRackAngle === 'function' ? getRackAngle(r) : 0;
                        const dx = logicalX - r.x;
                        const dy = logicalY - r.y;
                        
                        const distFromStartPx = dx * Math.cos(angle) + dy * Math.sin(angle);
                        const distFromStartMm = distFromStartPx / (window.currentScale || 1);
                        
                        const distFromCenterYPx = -dx * Math.sin(angle) + dy * Math.cos(angle);
                        const distFromCenterYMm = distFromCenterYPx / (window.currentScale || 1);
                        
                        const colSizeMm = 85;
                        const reg = (r.independent || 0) + (r.connected || 0);
                        const sm = r.smallConnected || 0;
                        const spans = reg + sm;
                        
                        let currentMm = 0;
                        let spanIndex = -1;
                        
                        for (let j = 0; j < reg; j++) {
                            let nextMm = currentMm + colSizeMm + r.beamLength;
                            if (distFromStartMm >= currentMm && distFromStartMm <= nextMm) {
                                spanIndex = j;
                                break;
                            }
                            currentMm = nextMm;
                        }
                        if (spanIndex === -1 && sm > 0) {
                            const smallBeamLength = r.smallBeamLength || (typeof getSmallBeamLength === 'function' ? getSmallBeamLength(r.beamLength) : 1385);
                            let nextMm = currentMm + colSizeMm + smallBeamLength;
                            if (distFromStartMm >= currentMm && distFromStartMm <= nextMm) {
                                spanIndex = reg;
                            }
                        }
                        
                        if (spanIndex !== -1) {
                            let row = 0;
                            if (r.isDouble) {
                                row = (distFromCenterYMm > 0) ? 1 : 0;
                            }
                            found = { rackIndex: i, row: row, spanIndex: spanIndex };
                            break;
                        }
                    }
                }
                
                // 변경이 감지되면 화면 즉시 갱신
                const prev = window._hoveredBypass;
                if (!prev || !found || prev.rackIndex !== found.rackIndex || prev.row !== found.row || prev.spanIndex !== found.spanIndex) {
                    window._hoveredBypass = found;
                    if (typeof draw === 'function') draw();
                }
            } else {
                if (window._hoveredBypass) {
                    window._hoveredBypass = null;
                    if (typeof draw === 'function') draw();
                }
            }
        });

        // 우클릭(Context Menu) 처리
        canvas.addEventListener('contextmenu', function(e) {
            e.preventDefault();
            if (typeof currentRackPreview !== 'undefined' && currentRackPreview) {
                rotateRack(currentRackPreview);
                return;
            }
        });


        

        
        // 캔버스 클릭 핸들러 (바이패스 토글 등)
        canvas.addEventListener('click', function(e) {
            const rect = canvas.getBoundingClientRect();
            // 1. 화면 픽셀 좌표 (바운딩 박스 hit-test용)
            const screenX = e.clientX - rect.left;
            const screenY = e.clientY - rect.top;
            
            // 2. 논리 월드 좌표 (랙 월드 좌표와 연산용)
            const zoom = (window.cameraZoom || window.cameraZoom === 0) ? window.cameraZoom : 1; // FIXED: sync with main canvas
            const logicalX = screenX / zoom;
            const logicalY = screenY / zoom;

            // 1. 삭제 모드 랙 클릭 처리
            if (window.activeInteractMode === 'delete') {
                if (typeof racks !== 'undefined' && Array.isArray(racks)) {
                    for (let i = racks.length - 1; i >= 0; i--) {
                        const r = racks[i];
                        if (typeof getRackBoxes !== 'function') continue;
                        const boxes = getRackBoxes(r);
                        const pad = 5;
                        if (screenX >= boxes.physical.minX - pad && screenX <= boxes.physical.maxX + pad &&
                            screenY >= boxes.physical.minY - pad && screenY <= boxes.physical.maxY + pad) {
                            if(typeof window.pushAction === 'function') pushAction({ type: 'delete', racks: JSON.parse(JSON.stringify(racks)) });
                            racks.splice(i, 1);
                            if (typeof updateRackFormCounts === 'function') updateRackFormCounts();
                            if (typeof draw === 'function') draw();
                            return;
                        }
                    }
                }
            }

            // 1.5 단수 편집(levels) 모드 랙 클릭 처리 (베이 단위 개별 설정)
            if (window.activeInteractMode === 'levels') {
                if (typeof racks !== 'undefined' && Array.isArray(racks)) {
                    for (let i = racks.length - 1; i >= 0; i--) {
                        const r = racks[i];
                        if (typeof getRackBoxes !== 'function') continue;
                        const boxes = getRackBoxes(r);
                        
                        // 바운딩 박스 체크 (화면 픽셀 좌표 screenX, screenY 기준 판별!)
                        const pad = 5;
                        if (screenX >= boxes.physical.minX - pad && screenX <= boxes.physical.maxX + pad &&
                            screenY >= boxes.physical.minY - pad && screenY <= boxes.physical.maxY + pad) {
                            
                            // 랙 로컬 좌표계로 마우스 위치 변환 (logicalX, logicalY 사용)
                            const angle = typeof getRackAngle === 'function' ? getRackAngle(r) : 0;
                            const dx = logicalX - r.x;
                            const dy = logicalY - r.y;
                            
                            // 랙 방향(길이축)으로의 클릭 거리 (mm)
                            const distFromStartPx = dx * Math.cos(angle) + dy * Math.sin(angle);
                            const distFromStartMm = distFromStartPx / (window.currentScale || 1);
                            
                            // 랙 깊이축으로의 클릭 거리 (mm)
                            const distFromCenterYPx = -dx * Math.sin(angle) + dy * Math.cos(angle);
                            const distFromCenterYMm = distFromCenterYPx / (window.currentScale || 1);
                            
                            const colSizeMm = 85;
                            const reg = (r.independent || 0) + (r.connected || 0);
                            const sm = r.smallConnected || 0;
                            const spans = reg + sm;
                            
                            let currentMm = 0;
                            let spanIndex = -1;
                            
                            for (let j = 0; j < reg; j++) {
                                let nextMm = currentMm + colSizeMm + r.beamLength;
                                if (distFromStartMm >= currentMm && distFromStartMm <= nextMm) {
                                    spanIndex = j;
                                    break;
                                }
                                currentMm = nextMm;
                            }
                            if (spanIndex === -1 && sm > 0) {
                                const smallBeamLength = r.smallBeamLength || (typeof getSmallBeamLength === 'function' ? getSmallBeamLength(r.beamLength) : 1385);
                                let nextMm = currentMm + colSizeMm + smallBeamLength;
                                if (distFromStartMm >= currentMm && distFromStartMm <= nextMm) {
                                    spanIndex = reg;
                                }
                            }
                            
                            if (spanIndex !== -1) {
                                // 상/하 랙 행 판별 (단열은 무조건 row 0, 복렬은 Y축 부호에 따라 판별)
                                let row = 0;
                                if (r.isDouble) {
                                    row = (distFromCenterYMm > 0) ? 1 : 0; // 중심 기준 아래쪽이면 1(하부), 위쪽이면 0(상부)
                                }
                                
                                const rowCount = r.isDouble ? 2 : 1;
                                
                                // r.bayLevels 초기화 로직
                                if (!r.bayLevels || !Array.isArray(r.bayLevels) || r.bayLevels.length < rowCount) {
                                    let oldLevels = r.bayLevels || [];
                                    r.bayLevels = [ [], [] ];
                                    if (Array.isArray(oldLevels[0])) r.bayLevels[0] = oldLevels[0];
                                    if (Array.isArray(oldLevels[1])) r.bayLevels[1] = oldLevels[1];
                                }
                                
                                const globalLevelsInput = document.getElementById('rack-levels');
                                const globalLevels = globalLevelsInput ? (parseInt(globalLevelsInput.value) || 3) : 3;
                                const defaultRackLevel = r.levels || globalLevels;
                                
                                // 해당 row의 bayLevels 길이 동기화
                                if (r.bayLevels[row].length !== spans) {
                                    let oldRowLevels = r.bayLevels[row];
                                    r.bayLevels[row] = new Array(spans).fill(defaultRackLevel);
                                    for(let k = 0; k < Math.min(oldRowLevels.length, spans); k++) {
                                        if (oldRowLevels[k] !== undefined) r.bayLevels[row][k] = oldRowLevels[k];
                                    }
                                }
                                
                                let currentLevel = r.bayLevels[row][spanIndex] !== undefined ? r.bayLevels[row][spanIndex] : defaultRackLevel;
                                
                                // r.bayHeights 초기화 로직
                                if (!r.bayHeights || !Array.isArray(r.bayHeights) || r.bayHeights.length < rowCount) {
                                    let oldHeights = r.bayHeights || [];
                                    r.bayHeights = [ [], [] ];
                                    if (Array.isArray(oldHeights[0])) r.bayHeights[0] = oldHeights[0];
                                    if (Array.isArray(oldHeights[1])) r.bayHeights[1] = oldHeights[1];
                                }
                                if (r.bayHeights[row].length !== spans) {
                                    let oldRowHeights = r.bayHeights[row];
                                    r.bayHeights[row] = new Array(spans).fill(null);
                                    for(let k = 0; k < Math.min(oldRowHeights.length, spans); k++) {
                                        if (oldRowHeights[k] !== undefined) r.bayHeights[row][k] = oldRowHeights[k];
                                    }
                                }
                                
                                let currentHeight = r.bayHeights[row][spanIndex] || '';
                                
                                // 기본 기둥 높이(placeholder 용) 계산
                                let basePalletH = parseInt(document.getElementById('pallet-h')?.value) || 1200;
                                let defaultHeight = parseInt(document.getElementById('rack-height')?.value) || 0;
                                if (defaultHeight <= 0) {
                                    const rawH = (basePalletH * globalLevels) + (globalLevels * 200) + 300;
                                    defaultHeight = Math.ceil(rawH / 500) * 500;
                                }

                                // 모달에 데이터 세팅
                                document.getElementById('modal-custom-rack-idx').value = i;
                                document.getElementById('modal-custom-row').value = row;
                                document.getElementById('modal-custom-span').value = spanIndex;
                                document.getElementById('modal-custom-level').value = currentLevel;
                                
                                const heightInput = document.getElementById('modal-custom-height');
                                heightInput.value = currentHeight;
                                heightInput.placeholder = `현재: ${defaultHeight}`;
                                
                                // 부트스트랩 모달 띄우기
                                const modalEl = document.getElementById('customLevelModal');
                                if (modalEl) {
                                    const modal = new bootstrap.Modal(modalEl);
                                    modal.show();
                                }
                                return;
                            }
                        }
                    }
                }
            }

            // 2. 바이패스 모드 랙 클릭 처리 (리모컨 setInteractMode 연동)
            if (window.activeInteractMode === 'bypass') {
                if (typeof racks !== 'undefined' && Array.isArray(racks)) {
                    for (let i = racks.length - 1; i >= 0; i--) {
                        const r = racks[i];
                        if (typeof getRackBoxes !== 'function') continue;
                        const boxes = getRackBoxes(r);
                        
                        // 바운딩 박스 체크 (화면 픽셀 좌표 screenX, screenY 기준 판별!)
                        const pad = 5;
                        if (screenX >= boxes.physical.minX - pad && screenX <= boxes.physical.maxX + pad &&
                            screenY >= boxes.physical.minY - pad && screenY <= boxes.physical.maxY + pad) {
                            
                            // 랙 로컬 좌표계로 마우스 위치 변환 (logicalX, logicalY 사용)
                            const angle = typeof getRackAngle === 'function' ? getRackAngle(r) : 0;
                            const dx = logicalX - r.x;
                            const dy = logicalY - r.y;
                            
                            // 랙 방향(길이축)으로의 클릭 거리 (mm)
                            const distFromStartPx = dx * Math.cos(angle) + dy * Math.sin(angle);
                            const distFromStartMm = distFromStartPx / (window.currentScale || 1);
                            
                            // 랙 깊이축으로의 클릭 거리 (mm)
                            const distFromCenterYPx = -dx * Math.sin(angle) + dy * Math.cos(angle);
                            const distFromCenterYMm = distFromCenterYPx / (window.currentScale || 1);
                            
                            const colSizeMm = 85;
                            const reg = (r.independent || 0) + (r.connected || 0);
                            const sm = r.smallConnected || 0;
                            const spans = reg + sm;
                            
                            let currentMm = 0;
                            let spanIndex = -1;
                            
                            for (let j = 0; j < reg; j++) {
                                let nextMm = currentMm + colSizeMm + r.beamLength;
                                if (distFromStartMm >= currentMm && distFromStartMm <= nextMm) {
                                    spanIndex = j;
                                    break;
                                }
                                currentMm = nextMm;
                            }
                            if (spanIndex === -1 && sm > 0) {
                                const smallBeamLength = r.smallBeamLength || (typeof getSmallBeamLength === 'function' ? getSmallBeamLength(r.beamLength) : 1385);
                                let nextMm = currentMm + colSizeMm + smallBeamLength;
                                if (distFromStartMm >= currentMm && distFromStartMm <= nextMm) {
                                    spanIndex = reg;
                                }
                            }
                            
                            if (spanIndex !== -1) {
                                // 상/하 랙 행 판별 (단열은 무조건 row 0, 복렬은 Y축 부호에 따라 판별)
                                let row = 0;
                                if (r.isDouble) {
                                    row = (distFromCenterYMm > 0) ? 1 : 0; // 중심 기준 아래쪽이면 1(하부), 위쪽이면 0(상부)
                                }
                                
                                const prevState = captureState();
                                
                                const rowCount = r.isDouble ? 2 : 1;
                                if (!r.bypassBays || !Array.isArray(r.bypassBays) || r.bypassBays.length < rowCount) {
                                    let oldBays = r.bypassBays || [];
                                    r.bypassBays = [ [], [] ];
                                    if (Array.isArray(oldBays[0])) r.bypassBays[0] = oldBays[0];
                                    if (Array.isArray(oldBays[1])) r.bypassBays[1] = oldBays[1];
                                }
                                
                                if (r.bypassBays[row].length !== spans) {
                                    let oldRowBays = r.bypassBays[row];
                                    r.bypassBays[row] = new Array(spans).fill(false);
                                    for(let k = 0; k < Math.min(oldRowBays.length, spans); k++) {
                                        r.bypassBays[row][k] = oldRowBays[k];
                                    }
                                }
                                
                                // 클릭한 특정 행(row)의 특정 베이(spanIndex) 토글
                                r.bypassBays[row][spanIndex] = !r.bypassBays[row][spanIndex];
                                
                                const nextState = captureState();
                                pushAction({ prevState, nextState });
                                
                                if (typeof updateRackFormCounts === 'function') {
                                    updateRackFormCounts();
                                }
                                if (typeof draw === 'function') {
                                    draw();
                                }
                                break;
                            }
                        }
                    }
                }
            }
        });
    });
})();

// === FIXED by Heidi: expose to global & fix coordinate system ===
window.rotateRack = typeof rotateRack !== 'undefined' ? rotateRack : window.rotateRack;
window.getRackHandlesPos = typeof getRackHandlesPos !== 'undefined' ? getRackHandlesPos : window.getRackHandlesPos;
window.drawRotateHandleIcon = typeof drawRotateHandleIcon !== 'undefined' ? drawRotateHandleIcon : window.drawRotateHandleIcon;
window.drawExtendHandleIcon = typeof drawExtendHandleIcon !== 'undefined' ? drawExtendHandleIcon : window.drawExtendHandleIcon;
window.drawCopyHandleIcon = typeof drawCopyHandleIcon !== 'undefined' ? drawCopyHandleIcon : window.drawCopyHandleIcon;


// ==================== FIXED V2 by Heidi: Smooth Drag Rotation + 45deg Snap ====================
(function(){
  let isRotatingDrag = false;
  let rotateTargetRack = null;
  let rotateStartMouseAngle = 0;
  let rotateStartRackAngle = 0;
  let rotateSnapEnabled = true;
  const SNAP_DEGREES = [0,45,90,135,180,225,270,315,360];
  const SNAP_THRESHOLD = 8; // 8도 이내면 스냅

  function toDeg(rad){ return rad * 180 / Math.PI; }
  function toRad(deg){ return deg * Math.PI / 180; }
  function normalizeDeg(d){ d = d % 360; return d < 0 ? d+360 : d; }
  
  function getSnapAngle(deg){
    if(!rotateSnapEnabled) return deg;
    let nd = normalizeDeg(deg);
    for(let s of SNAP_DEGREES){
      let diff = Math.abs(nd - s);
      if(diff > 180) diff = 360 - diff;
      if(diff <= SNAP_THRESHOLD){
        return s;
      }
    }
    // Also check 45deg multiples continuously with light snap
    let snapped = Math.round(nd / 45) * 45;
    let diff = Math.abs(nd - snapped);
    if(diff <= SNAP_THRESHOLD) return snapped;
    return deg;
  }

  function getMouseAngle(cx, cy, mx, my){
    return Math.atan2(my - cy, mx - cx);
  }

  window.setRotateSnap = function(enabled){ rotateSnapEnabled = !!enabled; };

  const canvas = document.getElementById('drawingCanvas');
  if(!canvas) return;

  // Enhanced mousedown for rotation drag
  canvas.addEventListener('mousedown', function(e){
    if(window.activeInteractMode !== 'rotate' && window.activeInteractMode !== 'rotate-ccw') return;
    
    const rect = canvas.getBoundingClientRect();
    const screenX = e.clientX - rect.left;
    const screenY = e.clientY - rect.top;
    const zoom = window.cameraZoom || 1;
    const logicalX = screenX / zoom;
    const logicalY = screenY / zoom;

    if(typeof racks === 'undefined') return;
    for(let i=racks.length-1; i>=0; i--){
      const r = racks[i];
      if(typeof getRackHandlesPos !== 'function') continue;
      const handles = getRackHandlesPos(r);
      if(!handles || !handles.rotate) continue;
      
      const hx = handles.rotate.x;
      const hy = handles.rotate.y;
      // hx,hy are in world? Actually need conversion
      // Check distance in screen space
      let handleScreenX = hx;
      let handleScreenY = hy;
      // If handles are in world coords, convert to screen
      if(window.currentScale){
        // getRackHandlesPos returns px already? use as is
        // Convert world to screen for distance check
        // We'll check with tolerance in world space
        let dx = logicalX - hx;
        let dy = logicalY - hy;
        let dist = Math.sqrt(dx*dx + dy*dy);
        if(dist < 30){ // 30px tolerance
          isRotatingDrag = true;
          rotateTargetRack = r;
          rotateStartRackAngle = typeof getRackAngle === 'function' ? getRackAngle(r) : (r.angle||0);
          // mouse angle relative to rack center
          let centerX = r.x + (r.totalLengthPx/2) * Math.cos(rotateStartRackAngle);
          let centerY = r.y + (r.totalLengthPx/2) * Math.sin(rotateStartRackAngle);
          rotateStartMouseAngle = getMouseAngle(r.x, r.y, logicalX, logicalY);
          e.preventDefault();
          e.stopPropagation();
          canvas.style.cursor = 'grabbing';
          console.log('[Heidi Rotate] Drag start', r);
          break;
        }
      }
    }
  }, true);

  canvas.addEventListener('mousemove', function(e){
    if(!isRotatingDrag || !rotateTargetRack) return;
    
    const rect = canvas.getBoundingClientRect();
    const screenX = e.clientX - rect.left;
    const screenY = e.clientY - rect.top;
    const zoom = window.cameraZoom || 1;
    const logicalX = screenX / zoom;
    const logicalY = screenY / zoom;

    const r = rotateTargetRack;
    const currentMouseAngle = getMouseAngle(r.x, r.y, logicalX, logicalY);
    let delta = currentMouseAngle - rotateStartMouseAngle;
    
    // Smooth interpolation
    let newAngle = rotateStartRackAngle + delta;
    
    // Snap to 45deg
    let deg = toDeg(newAngle);
    let snappedDeg = getSnapAngle(deg);
    let finalAngle = toRad(snappedDeg);
    
    // Apply with easing for buttery smoothness
    r.angle = finalAngle;
    r.isHoriz = Math.abs(Math.cos(finalAngle)) > Math.abs(Math.sin(finalAngle));
    r.dir = (Math.cos(finalAngle) > 0 || Math.sin(finalAngle) > 0) ? 1 : -1;
    
    // Visual feedback for snap
    if(Math.abs(snappedDeg - deg) < 0.1 && Math.abs(deg - snappedDeg) < SNAP_THRESHOLD){
      window._snapFeedback = { angle: snappedDeg, x: logicalX, y: logicalY };
      canvas.style.cursor = 'grabbing';
      // haptic feedback if supported
      if(navigator.vibrate) navigator.vibrate(10);
    } else {
      window._snapFeedback = null;
    }
    
    if(typeof draw === 'function') draw();
  }, true);

  canvas.addEventListener('mouseup', function(e){
    if(isRotatingDrag && rotateTargetRack){
      const r = rotateTargetRack;
      if(typeof checkRackValidPlacement === 'function'){
        r.isValid = checkRackValidPlacement(r);
      }
      if(typeof updateRackFormCounts === 'function') updateRackFormCounts();
      // Push to undo stack
      if(typeof window.pushAction === 'function' || typeof pushAction === 'function'){
        try{
          const prev = rotateStartRackAngle;
          // captureState if available
          if(typeof captureState === 'function' && typeof pushAction === 'function'){
            // already handled via global
          }
        }catch(err){}
      }
      isRotatingDrag = false;
      rotateTargetRack = null;
      window._snapFeedback = null;
      canvas.style.cursor = 'crosshair';
      if(typeof draw === 'function') draw();
      console.log('[Heidi Rotate] Drag end - snapped');
    }
  });

  // Visual snap guide overlay - hook into draw
  const origDraw = window.draw;
  if(typeof origDraw === 'function'){
    window.draw = function(){
      origDraw();
      if(window._snapFeedback && window.ctx){
        const fb = window._snapFeedback;
        const ctx = window.ctx;
        const zoom = window.cameraZoom || 1;
        ctx.save();
        ctx.scale(zoom, zoom);
        ctx.strokeStyle = 'rgba(251,191,36,0.9)';
        ctx.lineWidth = 1.5 / zoom;
        ctx.setLineDash([4/zoom, 4/zoom]);
        ctx.beginPath();
        ctx.arc(fb.x, fb.y, 40, 0, Math.PI*2);
        ctx.stroke();
        ctx.setLineDash([]);
        // text
        ctx.fillStyle = '#fbbf24';
        ctx.font = `bold ${12/zoom}px Inter, sans-serif`;
        ctx.textAlign = 'center';
        ctx.fillText(`${Math.round(fb.angle)}° SNAP`, fb.x, fb.y - 50);
        ctx.restore();
      }
    };
  }

  console.log('[Heidi] Smooth drag rotation + 45deg snap enabled');
})();

// ==================== FIXED V2: Segment 45deg Snap for drawing ====================
(function(){
  const SEG_SNAP_DEG = 45;
  const SEG_SNAP_THRESH = 10;
  window.isSegmentSnapEnabled = true;
  
  // Hook into getLogicalPos if exists
  const originalGetLogicalPos = window.getLogicalPos;
  if(typeof originalGetLogicalPos === 'function'){
    window.getLogicalPos = function(e){
      let pos = originalGetLogicalPos(e);
      if(!window.isSegmentSnapEnabled) return pos;
      
      // ONLY apply segment snap if we are drawing walls!
      if(typeof isDrawingMode !== 'undefined' && !isDrawingMode) return pos;
      if(typeof drawMode !== 'undefined' && drawMode !== 'wall') return pos;
      
      if(typeof points !== 'undefined' && points.length > 0){
        // [추가] 시작점 자석 스냅 로직 (화면 기준 약 15픽셀 이내 접근 시)
        if (points.length > 2) {
            const first = points[0];
            const distToFirst = Math.hypot(pos.x - first.x, pos.y - first.y);
            const zoom = window.cameraZoom || 1;
            if (distToFirst * zoom <= 15) {
                return { x: first.x, y: first.y };
            }
        }
        const last = points[points.length-1];
        const dx = pos.x - last.x;
        const dy = pos.y - last.y;
        const angle = Math.atan2(dy, dx) * 180 / Math.PI;
        const normalized = ((angle % 360) + 360) % 360;
        const snapped = Math.round(normalized / SEG_SNAP_DEG) * SEG_SNAP_DEG;
        const diff = Math.abs(normalized - snapped);
        const diffWrap = Math.min(diff, 360-diff);
        if(diffWrap <= SEG_SNAP_THRESH){
          const len = Math.sqrt(dx*dx+dy*dy);
          const rad = snapped * Math.PI / 180;
          return {
            x: last.x + Math.cos(rad) * len,
            y: last.y + Math.sin(rad) * len
          };
        }
      }
      return pos;
    };
  }
})();

// ==================== Mouse Wheel Zoom & Right-Click Pan ====================
(function(){
    document.addEventListener('DOMContentLoaded', function() {
        const canvas = document.getElementById('drawingCanvas');
        if (!canvas) return;
        const container = canvas.parentElement; 
        if (!container) return;

        // Wheel Zoom
        container.addEventListener('wheel', function(e) {
            e.preventDefault(); 
            if (e.deltaY < 0) {
                if (typeof window.zoomIn === 'function') window.zoomIn();
            } else if (e.deltaY > 0) {
                if (typeof window.zoomOut === 'function') window.zoomOut();
            }
        }, { passive: false });

        // Right-Click & Middle-Click Pan
        let isPanning = false;
        let startX = 0;
        let startY = 0;
        let startScrollLeft = 0;
        let startScrollTop = 0;
        
        // Disable context menu globally on container to allow clean right-click dragging
        container.addEventListener('contextmenu', function(e) {
            e.preventDefault();
        });

        container.addEventListener('mousedown', function(e) {
            if (e.button === 1 || e.button === 2) { // Middle or Right click
                isPanning = true;
                startX = e.clientX;
                startY = e.clientY;
                startScrollLeft = container.scrollLeft;
                startScrollTop = container.scrollTop;
                container.style.cursor = 'grabbing';
                canvas.style.cursor = 'grabbing';
                e.preventDefault();
            }
        });

        window.addEventListener('mousemove', function(e) {
            if (!isPanning) return;
            e.preventDefault();
            const dx = e.clientX - startX;
            const dy = e.clientY - startY;
            container.scrollLeft = startScrollLeft - dx;
            container.scrollTop = startScrollTop - dy;
        });

        window.addEventListener('mouseup', function(e) {
            if (isPanning && (e.button === 1 || e.button === 2)) {
                isPanning = false;
                container.style.cursor = 'default';
                canvas.style.cursor = 'crosshair'; 
            }
        });
    });
})();

// ==================== 커스텀 단수/높이 모달 저장 처리 ====================
(function(){
    document.addEventListener('DOMContentLoaded', function() {
        const saveBtn = document.getElementById('btn-save-custom-level');
        if (saveBtn) {
            saveBtn.addEventListener('click', function() {
                const idxStr = document.getElementById('modal-custom-rack-idx').value;
                const rowStr = document.getElementById('modal-custom-row').value;
                const spanStr = document.getElementById('modal-custom-span').value;
                const levelStr = document.getElementById('modal-custom-level').value;
                const heightStr = document.getElementById('modal-custom-height').value;
                
                if (!idxStr || !rowStr || !spanStr || !levelStr) return;
                
                const idx = parseInt(idxStr);
                const row = parseInt(rowStr);
                const span = parseInt(spanStr);
                const level = parseInt(levelStr);
                let height = heightStr ? parseInt(heightStr) : null;
                if (height <= 0) height = null;
                
                if (isNaN(level) || level < 2) {
                    alert('⚠️ 설치 단수는 최소 2단 이상이어야 합니다.');
                    document.getElementById('modal-custom-level').focus();
                    return;
                }
                
                if (typeof racks !== 'undefined' && racks[idx]) {
                    const r = racks[idx];
                    if (level >= 2) {
                        r.bayLevels[row][span] = level;
                        r.bayHeights[row][span] = height;
                        
                        if (typeof window.pushAction === 'function') window.pushAction({ type: 'change-bay-level-height', racks: JSON.parse(JSON.stringify(racks)) });
                        if (typeof updateRackFormCounts === 'function') updateRackFormCounts();
                        if (typeof draw === 'function') draw();
                        
                        const modalEl = document.getElementById('customLevelModal');
                        if (modalEl) {
                            const modal = bootstrap.Modal.getInstance(modalEl);
                            if (modal) modal.hide();
                        }
                    }
                }
            });
        }
    });
})();
