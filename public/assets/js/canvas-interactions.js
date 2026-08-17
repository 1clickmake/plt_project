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
                const zoom = window.cameraZoom || 1;
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
            const zoom = window.cameraZoom || 1;
            const logicalX = screenX / zoom;
            const logicalY = screenY / zoom;

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
