
function isCanvasLightMode() {
    return document.body.classList.contains('theme-light') || window.CANVAS_THEME === 'light';
}

function drawGridBackground() {
    if (currentScale <= 0) return;
    const parent = canvas.parentElement;
    if (!parent) return;

    // Viewport bounds in unzoomed canvas space
    const viewTop = parent.scrollTop / cameraZoom;
    const viewLeft = parent.scrollLeft / cameraZoom;
    const viewWidth = parent.clientWidth / cameraZoom;
    const viewHeight = parent.clientHeight / cameraZoom;

    // 📏 1번 사진처럼 극도로 정밀하고 디테일한 모눈종이 눈금 복원
    // 기본: 500mm 격자선, 1,000mm(1m) 텍스트 눈금
    // 확대 시(1.5배 이상): 100mm 격자선, 500mm 텍스트 눈금
    let gridLineMm = (cameraZoom >= 1.5) ? 100 : 500;
    let textStepMm = (cameraZoom >= 1.5) ? 500 : 1000;

    // 만약 초대형 창고(60m~100m+) 축소로 500mm 격자가 18px 미만으로 너무 빽빽해질 때만 가변 완화
    const effectivePx = gridLineMm * currentScale * cameraZoom;
    if (effectivePx < 18) {
        gridLineMm = 1000;
        textStepMm = 2000;
    }

    const linePx = gridLineMm * currentScale; // 언줌 캔버스 기준 격자 간격 (px)
    const maxW = canvas.width / cameraZoom;
    const maxH = canvas.height / cameraZoom;

    const originX = (window.canvasOriginX !== undefined) ? window.canvasOriginX : 0;
    const originY = (window.canvasOriginY !== undefined) ? window.canvasOriginY : 0;

    // originX, originY를 기준으로 스냅된 시작선 계산 (뷰포트 범위 내에서만)
    const rawStartX = Math.max(0, viewLeft - linePx);
    const startX = originX + Math.floor((rawStartX - originX) / linePx) * linePx;
    const endX = Math.min(maxW, viewLeft + viewWidth + linePx);

    const rawStartY = Math.max(0, viewTop - linePx);
    const startY = originY + Math.floor((rawStartY - originY) / linePx) * linePx;
    const endY = Math.min(maxH, viewTop + viewHeight + linePx);

    ctx.save();
    ctx.scale(cameraZoom, cameraZoom);

    const isLight = isCanvasLightMode();

    // 1. Grid Lines - 디테일한 CAD 모눈종이 격자선
    ctx.strokeStyle = isLight ? 'rgba(0, 0, 0, 0.08)' : 'rgba(255, 255, 255, 0.1)';
    ctx.lineWidth = 1 / cameraZoom;
    ctx.beginPath();
    for (let x = startX; x <= endX; x += linePx) {
        ctx.moveTo(x, startY);
        ctx.lineTo(x, endY);
    }
    for (let y = startY; y <= endY; y += linePx) {
        ctx.moveTo(startX, y);
        ctx.lineTo(endX, y);
    }
    ctx.stroke();

    // 2. Ruler Background Panels
    const rulerThickTop = 22 / cameraZoom;
    const rulerThickLeft = 45 / cameraZoom;
    ctx.fillStyle = isLight ? 'rgba(241, 245, 249, 0.95)' : 'rgba(20, 25, 35, 0.9)'; 
    ctx.fillRect(viewLeft, viewTop, viewWidth, rulerThickTop);
    ctx.fillRect(viewLeft, viewTop, rulerThickLeft, viewHeight);

    // 3. Ruler Text - 1,000 단위(1m) 디테일 표기
    ctx.fillStyle = isLight ? '#334155' : 'rgba(255, 255, 255, 0.9)';
    ctx.font = (10 / cameraZoom) + 'px sans-serif';
    ctx.textAlign = 'left';
    ctx.textBaseline = 'top';

    for (let x = startX; x <= endX; x += linePx) {
        let logicalMm = Math.round((x - originX) / currentScale);
        logicalMm = Math.round(logicalMm / gridLineMm) * gridLineMm;
        if (Math.abs(logicalMm) % textStepMm === 0 && x > viewLeft + rulerThickLeft) {
            ctx.fillText(logicalMm, x + 4 / cameraZoom, viewTop + 6 / cameraZoom);
        }
    }
    for (let y = startY; y <= endY; y += linePx) {
        let logicalMm = Math.round((y - originY) / currentScale);
        logicalMm = Math.round(logicalMm / gridLineMm) * gridLineMm;
        if (Math.abs(logicalMm) % textStepMm === 0 && y > viewTop + rulerThickTop) {
            ctx.fillText(logicalMm, viewLeft + 4 / cameraZoom, y + 4 / cameraZoom);
        }
    }

    ctx.restore();
}

// 창고 도면 직접 그리기 (Custom Canvas Drawing) 엔진

const canvas = document.getElementById('drawingCanvas');
const ctx = canvas.getContext('2d');
let isDrawingMode = false;
let points = [];
let obstacles = []; // 기둥과 문을 저장하는 배열
let edgeLengths = []; // 사용자가 입력한 실제 선분 길이 (mm) 배열
let originalAngles = []; // 처음에 그려진 스냅된 각도 보관
let originalVisualLengths = []; // 처음에 그려진 비주얼 픽셀 길이 보관
let userEnteredEdges = []; // 사용자가 키보드로 수동 입력한 엣지 인덱스 보관 (Array 큐 형식)
let mousePos = null;
let snapGuideEnabled = true; // global flag for snap guide visibility

let draggedItemType = null; // 외부에서 드래그 (HTML -> Canvas)

// 내부 캔버스 드래그용 상태 변수
let draggingObstacleIndex = -1;
let dragOffsetX = 0;
let dragOffsetY = 0;

// 파렛트랙 드래그 배치 관련 상태
let racks = []; // 배치된 랙 그룹들
window.getRackCount = () => racks.length;
let isDrawingRack = false;
let rackStartX = 0;
let rackStartY = 0;
let currentRackPreview = null;
let currentScale = 0; // pixels per mm (도면이 정렬되어야 값이 생김)
window.currentScale = 0;

let isMovingRack = false;
let rackDragOffsetX = 0;
let rackDragOffsetY = 0;

// 🌟 캔버스 스마트 플로팅 토스트 경고창 및 랙 연장 차단 상태
let lastExtendWarningTime = 0;
window.isExtendBlockedByObstacle = false;

function showCanvasAlert(msg, type = 'warning', duration = 3200) {
    const now = Date.now();
    if (now - lastExtendWarningTime < 2500) {
        return; // 마우스 드래그 중 과도한 중복 호출 방지 (2.5초 쿨다운 쓰로틀링)
    }
    lastExtendWarningTime = now;

    let toast = document.getElementById('canvas-floating-toast');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'canvas-floating-toast';
        toast.style.position = 'fixed';
        toast.style.top = '75px';
        toast.style.left = '50%';
        toast.style.transform = 'translateX(-50%) translateY(-20px)';
        toast.style.zIndex = '999999';
        toast.style.padding = '12px 26px';
        toast.style.borderRadius = '30px';
        toast.style.background = 'rgba(15, 23, 42, 0.94)';
        toast.style.backdropFilter = 'blur(10px)';
        toast.style.boxShadow = '0 10px 25px -5px rgba(0, 0, 0, 0.6), 0 0 15px rgba(245, 158, 11, 0.35)';
        toast.style.color = '#ffffff';
        toast.style.fontSize = '0.92rem';
        toast.style.fontWeight = '600';
        toast.style.display = 'flex';
        toast.style.alignItems = 'center';
        toast.style.gap = '10px';
        toast.style.opacity = '0';
        toast.style.pointerEvents = 'none';
        toast.style.transition = 'opacity 0.25s cubic-bezier(0.16, 1, 0.3, 1), transform 0.25s cubic-bezier(0.16, 1, 0.3, 1)';
        document.body.appendChild(toast);
    }

    const icon = type === 'warning' ? '⚠️' : (type === 'danger' ? '🚫' : 'ℹ️');
    const borderColor = type === 'warning' ? '#f59e0b' : (type === 'danger' ? '#ef4444' : '#38bdf8');
    toast.style.border = `1.5px solid ${borderColor}`;
    toast.innerHTML = `<span style="font-size: 1.2rem;">${icon}</span> <span>${msg}</span>`;

    // 부드러운 표시
    toast.style.opacity = '1';
    toast.style.transform = 'translateX(-50%) translateY(0)';

    if (window._canvasToastTimeout) {
        clearTimeout(window._canvasToastTimeout);
    }
    window._canvasToastTimeout = setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(-50%) translateY(-20px)';
    }, duration);
}
window.showCanvasAlert = showCanvasAlert;



let baseWidth = 0;
let baseHeight = 0;
let cameraZoom = 1;

const RACK_FRAME_WIDTH = 100;

function getRackDepth() {
    const el = document.getElementById('rack-depth');
    const innerDepth = el ? parseInt(el.value) || 1000 : 1000;
    return innerDepth + 100; // 외경 = 내경 + 100
}

function isDoubleRow() {
    const el = document.querySelector('input[name="rowType"]:checked');
    return el && el.value === 'double';
}

function getLogicalPos(e, forceRaw = false) {
    let rawX, rawY;
    
    // 터치(Touch) 이벤트 지원: clientX/clientY를 캔버스 기준 좌표로 변환
    if (e && (e.touches || e.changedTouches)) {
        const touch = (e.touches && e.touches.length > 0) ? e.touches[0] : (e.changedTouches && e.changedTouches[0]);
        if (touch && canvas) {
            const rect = canvas.getBoundingClientRect();
            rawX = (touch.clientX - rect.left) / cameraZoom;
            rawY = (touch.clientY - rect.top) / cameraZoom;
        } else {
            rawX = (e.offsetX || 0) / cameraZoom;
            rawY = (e.offsetY || 0) / cameraZoom;
        }
    } else if (e && e.offsetX !== undefined && e.offsetY !== undefined) {
        rawX = e.offsetX / cameraZoom;
        rawY = e.offsetY / cameraZoom;
    } else if (e && e.clientX !== undefined && e.clientY !== undefined && canvas) {
        const rect = canvas.getBoundingClientRect();
        rawX = (e.clientX - rect.left) / cameraZoom;
        rawY = (e.clientY - rect.top) / cameraZoom;
    } else {
        rawX = 0;
        rawY = 0;
    }
    
    // 도면 작성 모드가 아니거나, 랙/장애물 이동 중이거나, Shift키를 누른 경우 픽셀 정밀도로 부드럽게 추적
    if (forceRaw || !isDrawingMode || isMovingRack || (typeof isExtendingRack !== 'undefined' && isExtendingRack) || (e && e.shiftKey)) {
        return { x: rawX, y: rawY };
    }
    
    if (typeof getGridSnapPx === 'function' && currentScale > 0) {
        const snapPx = getGridSnapPx();
        return {
            x: Math.round(rawX / snapPx) * snapPx,
            y: Math.round(rawY / snapPx) * snapPx
        };
    }
    return { x: rawX, y: rawY };
}

// 터치 및 마우스 반응성 강화를 위한 동적 터치/클릭 판정 반경 계산 (줌아웃/모바일 환경에서도 손쉬운 조작 보장)
function getInteractiveHitRadius(baseRadius = 15) {
    const isTouchDevice = window.matchMedia && window.matchMedia('(pointer: coarse)').matches;
    const minR = isTouchDevice ? 24 : 16;
    return Math.max(minR, (baseRadius * 1.5) / (cameraZoom || 1));
}

// 스냅 가이드 토글 함수
function toggleSnapGuide() {
    snapGuideEnabled = !snapGuideEnabled;
    draw();
}

// 레티나 디스플레이 및 사이즈 맞춤 설정
function resizeCanvas(triggerAlign = true) {
    const parent = canvas.parentElement;
    if (!parent) return;
    baseWidth = parent.clientWidth;
    baseHeight = parent.clientHeight;
    if (triggerAlign && cameraZoom === 1 && points.length >= 3 && currentScale > 0) {
        alignAndScalePolygon();
    } else {
        canvas.width = Math.round(baseWidth * cameraZoom);
        canvas.height = Math.round(baseHeight * cameraZoom);
        draw();
    }
}
window.addEventListener('resize', () => resizeCanvas(true));

// 📜 캔버스 스크롤 시 격자(Grid) 및 눈금자(Ruler) 실시간 추적 렌더링
document.addEventListener('DOMContentLoaded', () => {
    const parent = canvas ? canvas.parentElement : null;
    if (parent) {
        parent.addEventListener('scroll', () => {
            requestAnimationFrame(draw);
        }, { passive: true });
    }
});

function applyZoom(oldZoom = null, mousePoint = null) {
    if (baseWidth === 0) return;
    const parent = canvas.parentElement;
    if (!parent) return;
    
    const rect = parent.getBoundingClientRect();
    let cursorViewportX = parent.clientWidth / 2;
    let cursorViewportY = parent.clientHeight / 2;
    
    if (mousePoint && mousePoint.clientX !== undefined) {
        cursorViewportX = mousePoint.clientX - rect.left;
        cursorViewportY = mousePoint.clientY - rect.top;
    } else if (mousePoint && mousePoint.x !== undefined) {
        cursorViewportX = mousePoint.x - parent.scrollLeft;
        cursorViewportY = mousePoint.y - parent.scrollTop;
    } else if (window.lastMouseX !== undefined && window.lastMouseY !== undefined) {
        cursorViewportX = window.lastMouseX - parent.scrollLeft;
        cursorViewportY = window.lastMouseY - parent.scrollTop;
    }

    const prevZoom = (oldZoom && oldZoom > 0) ? oldZoom : 1;
    const worldX = (parent.scrollLeft + cursorViewportX) / prevZoom;
    const worldY = (parent.scrollTop + cursorViewportY) / prevZoom;

    canvas.width = Math.round(baseWidth * cameraZoom);
    canvas.height = Math.round(baseHeight * cameraZoom);
    
    // 🌟 줌 동작 시에는 도면 및 랙 좌표계를 절대 변경하지 않고 순수 카메라 확대/축소만 수행
    draw();
    
    if (oldZoom && oldZoom > 0) {
        parent.scrollLeft = Math.round(worldX * cameraZoom - cursorViewportX);
        parent.scrollTop = Math.round(worldY * cameraZoom - cursorViewportY);
    } else {
        if (cameraZoom > 1) {
            parent.scrollLeft = Math.round((canvas.width - parent.clientWidth) / 2);
            parent.scrollTop = Math.round((canvas.height - parent.clientHeight) / 2);
        } else {
            parent.scrollLeft = 0;
            parent.scrollTop = 0;
        }
    }
}

// 전역 줌 컨트롤 (성능 최적화: 과도한 GPU 렌더링 랙 및 크래시 방지를 위해 최대 줌 배율을 5.0배로 제한)
window.zoomIn = function(mouseEvt = null) {
    const oldZoom = cameraZoom;
    cameraZoom = Math.min(cameraZoom * 1.25, 5.0);
    applyZoom(oldZoom, mouseEvt);
};

window.zoomOut = function(mouseEvt = null) {
    const oldZoom = cameraZoom;
    cameraZoom = Math.max(cameraZoom / 1.25, 0.1);
    applyZoom(oldZoom, mouseEvt);
};

window.resetZoom = function() {
    const oldZoom = cameraZoom;
    cameraZoom = 1;
    applyZoom(oldZoom);
};

// 리모컨 방향 이동: panCanvas(dx, dy) - 스크롤 단위(px)
window.panCanvas = function(dx, dy) {
    const parent = canvas.parentElement;
    if (parent) {
        parent.scrollLeft = Math.max(0, parent.scrollLeft + dx);
        parent.scrollTop  = Math.max(0, parent.scrollTop  + dy);
    }
};

// 전역 함수로 노출
window.startCustomDrawing = function() {
    isDrawingMode = true;
    points = [];
    obstacles = [];
    racks = [];
    edgeLengths = [];
    originalAngles = [];
    mousePos = null;
    currentScale = 0;
    window.currentScale = 0;
    resizeCanvas();
    renderObstacleInputs();
    updateRackFormCounts();
};

window.stopCustomDrawing = function() {
    isDrawingMode = false;
};


// 🔄 랙 초기화 함수 (도면 및 입력란 유지)
window.resetCanvas = function() {
    // 캔버스에 배치된 랙만 비우기
    racks = [];
    
    // 리모컨 숨기기
    const remote = document.getElementById('canvas-remote-ctrl');
    if (remote) remote.classList.add('d-none');
    
    // 실행 버튼 상태 원래대로 돌리기
    const btn = document.getElementById('run-layout-btn');
    if (btn) {
        btn.innerHTML = '🚀 파렛트랙 배치 실행';
        btn.classList.remove('btn-success');
        btn.classList.add('btn-primary-gradient');
    }
    
    // 랙 카운트 초기화
    if (typeof updateRackFormCounts === 'function') {
        updateRackFormCounts();
    }
    
    // 다시 기본 랙(독립 1, 복식 1) 스폰 (도면이 그려져 있을 때만)
    if (typeof window.spawnInitialRacks === 'function' && currentScale > 0) {
        window.spawnInitialRacks();
    } else {
        draw();
    }
};

// 엣지(선분) 길이 저장
window.clearEdgeLengths = function() {
    edgeLengths = [];
    userEnteredEdges = [];
};

window.updateEdgeLength = function(index, value) {
    let valInt = parseInt(value) || 0;
    
    // 30m(30,000mm) 소형 창고 초과 감지 및 안내
    if (valInt > 30000) {
        valInt = 30000;
        const inputEl = document.getElementById(`edge-input-${index}`);
        if (inputEl) inputEl.value = 30000;
        if (typeof showCustomToast === 'function') {
            showCustomToast('⚠️ 웹 도면 그리기는 최대 30m(30,000mm) 창고까지 최적화되어 있습니다. 30m 초과 대형 물류창고는 현장 방문 실측 상담을 권장합니다.');
        } else if (typeof alert === 'function' && !window.__suppressLimitAlert) {
            window.__suppressLimitAlert = true;
            setTimeout(() => { window.__suppressLimitAlert = false; }, 3000);
            alert('⚠️ 웹 자동 설계는 최대 30m(30,000mm) 창고까지 지원됩니다.\n30,000mm로 자동 조정되며, 초과 창고는 현장 실측 상담을 이용해주세요.');
        }
    }
    
    // 수동 입력 이력 배열에서 기존 인덱스 제거
    const idx = userEnteredEdges.indexOf(index);
    if (idx > -1) {
        userEnteredEdges.splice(idx, 1);
    }
    
    if (value !== '' && valInt > 0) {
        userEnteredEdges.push(index);
        edgeLengths[index] = valInt;
    } else {
        edgeLengths[index] = 0;
    }
    
    // 만약 모든 변이 수동 입력되었다면, 가장 예전에 입력한 변 1개를 자동 계산 변으로 실시간 양보
    const numEdges = points.length - 1;
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
};

// --- 도면 자동 정렬 (Parametric Alignment) ---
function alignAndScalePolygon() {
    if (points.length < 4 || originalAngles.length === 0 || originalVisualLengths.length === 0) return;
    const numEdges = points.length - 1;

    // 사용자가 직접 키보드로 입력하지 않은 남은 엣지가 딱 1개 있을 때 실시간 기하학적 폐합 계산
    let emptyIndices = [];
    for (let i = 0; i < numEdges; i++) {
        if (!userEnteredEdges.includes(i)) {
            emptyIndices.push(i);
        }
    }
    if (emptyIndices.length === 1) {
        const m = emptyIndices[0];
        let sumX = 0;
        let sumY = 0;
        for (let i = 0; i < numEdges; i++) {
            if (i !== m && edgeLengths[i] > 0) {
                let len = edgeLengths[i];
                let angle = originalAngles[i];
                sumX += len * Math.cos(angle);
                sumY += len * Math.sin(angle);
            }
        }
        let targetLen = Math.round(Math.hypot(sumX, sumY));
        if (targetLen > 0) {
            edgeLengths[m] = targetLen;
            const inputEl = document.getElementById(`edge-input-${m}`);
            if (inputEl) {
                inputEl.value = targetLen;
            }
        }
    }

    let hasEnteredLength = edgeLengths.some(l => l > 0);
    if (!hasEnteredLength) return;

    let ratio = 100; 
    for(let i = 0; i < numEdges; i++) {
        if(edgeLengths[i] > 0) {
            const visualLen = originalVisualLengths[i];
            ratio = edgeLengths[i] / (visualLen || 1);
            break;
        }
    }

    let mathPoints = [{x: 0, y: 0}];
    for (let i = 0; i < numEdges; i++) {
        let len = edgeLengths[i] > 0 ? edgeLengths[i] : (originalVisualLengths[i] * ratio);
        let angle = originalAngles[i];
        
        let nx = mathPoints[i].x + len * Math.cos(angle);
        let ny = mathPoints[i].y + len * Math.sin(angle);
        
        if (i === numEdges - 1) {
            nx = 0;
            ny = 0;
            
            const calculatedLen = Math.round(Math.hypot(mathPoints[i].x, mathPoints[i].y));
            if (!edgeLengths[i] || edgeLengths[i] <= 0) {
                edgeLengths[i] = calculatedLen;
                const inputEl = document.getElementById(`edge-input-${i}`);
                if (inputEl) inputEl.value = calculatedLen;
            }
        } else {
            if (!edgeLengths[i] || edgeLengths[i] <= 0) {
                const estimatedLen = Math.round(len);
                edgeLengths[i] = estimatedLen;
                const inputEl = document.getElementById(`edge-input-${i}`);
                if (inputEl) inputEl.value = estimatedLen;
            }
        }
        
        mathPoints.push({x: nx, y: ny});
    }

    let minX = 0, maxX = 0, minY = 0, maxY = 0;
    mathPoints.forEach(p => {
        if(p.x < minX) minX = p.x;
        if(p.x > maxX) maxX = p.x;
        if(p.y < minY) minY = p.y;
        if(p.y > maxY) maxY = p.y;
    });

    const polyWidthMm = Math.max(1, maxX - minX);
    const polyHeightMm = Math.max(1, maxY - minY);

    // 부모 컨테이너(캔버스 래퍼) 실제 가시 영역 크기
    const parent = canvas.parentElement;
    const containerW = parent ? parent.clientWidth : (baseWidth || window.innerWidth);
    const containerH = parent ? parent.clientHeight : (baseHeight || window.innerHeight);

    // 🤖 우측 플로팅 챗봇 위젯(Chat Wizard) 감지 및 안전 영역 확보
    const chatEl = document.getElementById('chat-wizard-container');
    const isChatVisible = chatEl && chatEl.style.display !== 'none' && !chatEl.classList.contains('d-none');
    
    // 챗봇이 열려있으면 챗봇 너비(380px) + 안전 여백(40px) = 약 420px를 우측 마진으로 확보!
    let rightMargin = 100;
    if (isChatVisible) {
        const chatW = chatEl.offsetWidth || 380;
        rightMargin = chatW + 40;
    }

    const leftMargin = 90;   // 좌측 눈금자(45px) + 안전 여유(45px)
    const topMargin = 75;    // 상단 눈금자(22px) + 탭 바 감안 여유
    const bottomMargin = 220; // 🌟 하단 치수선, 배지 밑으로 확 트인 여유 공간 대폭 확보 (220px)

    const safeWidth = Math.max(300, containerW - leftMargin - rightMargin);
    const safeHeight = Math.max(300, containerH - topMargin - bottomMargin);

    // 📐 어떤 크기의 창고(19m×4m, 115m×35m 등)라도 챗봇을 침범하지 않고 화면 안전 틀 안에 100% 쏙 들어오도록 자동 스케일 산출
    currentScale = Math.min(safeWidth / polyWidthMm, safeHeight / polyHeightMm);
    window.currentScale = currentScale;

    // 안전 가시 영역에 쾌적하게 배치 (하단 여유 공간 확보를 위해 상단 35% : 하단 65% 비율 배치)
    const drawnWidthPx = polyWidthMm * currentScale;
    const drawnHeightPx = polyHeightMm * currentScale;

    // 이전 도면 원점(0,0) 좌표 및 스케일 백업 (랙 상대 위치 보존용)
    const oldOriginX = (window.canvasOriginX !== undefined) ? window.canvasOriginX : 0;
    const oldOriginY = (window.canvasOriginY !== undefined) ? window.canvasOriginY : 0;
    const oldScale = window.currentScale || currentScale;

    const offsetX = leftMargin + (safeWidth - drawnWidthPx) / 2 - (minX * currentScale);
    const offsetY = topMargin + Math.max(0, (safeHeight - drawnHeightPx) * 0.35) - (minY * currentScale);
    window.canvasOriginX = offsetX + (minX * currentScale);
    window.canvasOriginY = offsetY + (minY * currentScale);
    window.globalPolyMinX = Math.round(offsetX / currentScale);
    window.globalPolyMinY = Math.round(offsetY / currentScale);

    const newOriginX = window.canvasOriginX;
    const newOriginY = window.canvasOriginY;

    // 🌟 도면이 재정렬되거나 창 크기 변화로 스케일이 변경될 때 랙도 도면 내부의 정확한 상대 위치(mm)를 유지하도록 동기화!
    if (oldScale > 0 && typeof racks !== 'undefined' && Array.isArray(racks) && racks.length > 0 && Math.abs(currentScale - oldScale) > 0.00001) {
        racks.forEach(r => {
            const relMmX = (r.x - oldOriginX) / oldScale;
            const relMmY = (r.y - oldOriginY) / oldScale;
            r.x = newOriginX + relMmX * currentScale;
            r.y = newOriginY + relMmY * currentScale;
            const totalMm = (r.totalLengthPx || 0) / oldScale;
            r.totalLengthPx = totalMm * currentScale;
        });
    }

    // 이전 도면 바운딩 박스 기준 장애물(기둥, 사용불가 등) 상대 위치 보정용
    const oldMinX = points.length > 0 ? Math.min(...points.map(p => p.x)) : 0;
    const oldMaxX = points.length > 0 ? Math.max(...points.map(p => p.x)) : 1;
    const oldMinY = points.length > 0 ? Math.min(...points.map(p => p.y)) : 0;
    const oldMaxY = points.length > 0 ? Math.max(...points.map(p => p.y)) : 1;
    const oldWidth = Math.max(1, oldMaxX - oldMinX);
    const oldHeight = Math.max(1, oldMaxY - oldMinY);

    for(let i = 0; i <= numEdges; i++) {
        points[i].x = mathPoints[i].x * currentScale + offsetX;
        points[i].y = mathPoints[i].y * currentScale + offsetY;
    }

    const newMinX = Math.min(...points.map(p => p.x));
    const newMaxX = Math.max(...points.map(p => p.x));
    const newMinY = Math.min(...points.map(p => p.y));
    const newMaxY = Math.max(...points.map(p => p.y));
    const newWidth = Math.max(1, newMaxX - newMinX);
    const newHeight = Math.max(1, newMaxY - newMinY);

    obstacles.forEach(obs => {
        const isDoorLike = obs.type === 'door' || obs.type === 'shutter';
        if (isDoorLike && obs.edgeIndex !== undefined && obs.edgeIndex !== -1 && obs.edgeIndex < numEdges) {
            const p1 = points[obs.edgeIndex];
            const p2 = points[obs.edgeIndex + 1];
            obs.x = p1.x + (p2.x - p1.x) * obs.ratioOnEdge;
            obs.y = p1.y + (p2.y - p1.y) * obs.ratioOnEdge;
            obs.angle = Math.atan2(p2.y - p1.y, p2.x - p1.x);
            obs.visualDistFromStart = Math.hypot(obs.x - p1.x, obs.y - p1.y);
            obs.visualEdgeLength = Math.hypot(p2.x - p1.x, p2.y - p1.y);
        } else if (oldWidth > 10 && oldHeight > 10 && obs.x !== undefined && obs.y !== undefined) {
            // 바닥 장애물(기둥, 기계, 사용불가구역 등)도 도면의 상대적 위치를 보존하여 함께 이동
            const relX = (obs.x - oldMinX) / oldWidth;
            const relY = (obs.y - oldMinY) / oldHeight;
            obs.x = newMinX + relX * newWidth;
            obs.y = newMinY + relY * newHeight;
        }
    });

    if (cameraZoom !== 1) {
        cameraZoom = 1;
        canvas.width = baseWidth;
        canvas.height = baseHeight;
    }
    if (parent) {
        parent.scrollLeft = 0;
        parent.scrollTop = 0;
    }
}

// 🚪 외부 HTML 아이콘 드래그 시작
window.handleDragStart = function(e, type) {
    draggedItemType = type;
    e.dataTransfer.setData('text/plain', type);
};

// --- 캔버스 마우스 이벤트 ---

canvas.addEventListener('contextmenu', (e) => {
    e.preventDefault();
    const {x: clickX, y: clickY} = getLogicalPos(e);

    // 만약 회전 핸들(Rotate Handle) 위에서 우클릭한 것이라면 반시계방향 10도 회전
    if (!isDrawingMode && currentScale > 0) {
        for (let i = racks.length - 1; i >= 0; i--) {
            const r = racks[i];
            if (typeof getRackHandlesPos === 'function') {
                const handles = getRackHandlesPos(r);
                if (handles && handles.rotate && Math.hypot(handles.rotate.x - clickX, handles.rotate.y - clickY) <= 15) {
                    // 우클릭 시 현재 모드의 반대 방향으로 회전
                    const step = (window.activeInteractMode === 'rotate-ccw') ? 10 : -10;
                    r.angle = (getRackAngle(r) + (step * Math.PI / 180)) % (Math.PI * 2);
                    r.isHoriz = Math.abs(Math.cos(r.angle)) > Math.abs(Math.sin(r.angle));
                    r.dir = (Math.cos(r.angle) > 0 || Math.sin(r.angle) > 0) ? 1 : -1;
                    
                    r.isValid = checkRackValidPlacement(r);
                    updateRackFormCounts();
                    draw();
                    return; // 분할/삭제 등 다른 우클릭 로직 방지!
                }
            }
        }
    }

    if (!isDrawingMode && currentScale > 0) {
        for (let i = racks.length - 1; i >= 0; i--) {
            const r = racks[i];
            const depthPx = (r.rackDepth || 1000) * currentScale;
            
            let minX = r.isHoriz ? (r.dir > 0 ? r.x : r.x - r.totalLengthPx) : (r.x - depthPx/2);
            let maxX = r.isHoriz ? (r.dir > 0 ? r.x + r.totalLengthPx : r.x) : (r.x + depthPx/2);
            let minY = r.isHoriz ? (r.y - depthPx/2) : (r.dir > 0 ? r.y : r.y - r.totalLengthPx);
            let maxY = r.isHoriz ? (r.y + depthPx/2) : (r.dir > 0 ? r.y + r.totalLengthPx : r.y);
            
            if (clickX >= minX && clickX <= maxX && clickY >= minY && clickY <= maxY) {
                let distFromStart = 0;
                if (r.isHoriz) {
                    distFromStart = r.dir > 0 ? (clickX - r.x) : (r.x - clickX);
                } else {
                    distFromStart = r.dir > 0 ? (clickY - r.y) : (r.y - clickY);
                }
                
                let spans = r.independent + r.connected + r.smallConnected;
                let frameMarginPx = (85 * currentScale) / (spans + 1); 
                let beamPx = r.beamLength * currentScale;
                let smallBeamPx = r.smallBeamLength * currentScale;
                
                let currentLen = 0;
                let spanIndex = -1;
                
                for(let j = 0; j < r.independent + r.connected; j++) {
                    let nextLen = currentLen + frameMarginPx + beamPx;
                    if (distFromStart >= currentLen && distFromStart <= nextLen + (frameMarginPx/2)) {
                        spanIndex = j;
                        break;
                    }
                    currentLen = nextLen;
                }
                
                if (spanIndex === -1 && r.smallConnected > 0) {
                    spanIndex = r.independent + r.connected;
                }
                
                if (spanIndex !== -1) {
                    splitRackGroup(i, spanIndex);
                    return;
                }
            }
        }
    }
});

function splitRackGroup(rackIndex, spanIndex) {
    let r = racks[rackIndex];
    let totalSpans = r.independent + r.connected + r.smallConnected;
    
    if (totalSpans <= 1) {
        // 단일 스팬이면 그냥 삭제
        racks.splice(rackIndex, 1);
        updateRackFormCounts();
        draw();
        return;
    }
    
    // 분할 전 스팬 길이 정보
    let spansArr = [];
    for(let i=0; i<r.independent + r.connected; i++) spansArr.push(r.beamLength);
    if (r.smallConnected > 0) spansArr.push(r.smallBeamLength);
    
    let frontSpans = spansArr.slice(0, spanIndex);
    let backSpans = spansArr.slice(spanIndex + 1);
    
    // 바이패스 상태 2차원 배열 분할
    let bypassArr0 = (r.bypassBays && r.bypassBays[0]) || new Array(totalSpans).fill(false);
    let bypassArr1 = (r.bypassBays && r.bypassBays[1]) || new Array(totalSpans).fill(false);
    
    let frontBypass0 = bypassArr0.slice(0, spanIndex);
    let backBypass0 = bypassArr0.slice(spanIndex + 1);
    
    let frontBypass1 = bypassArr1.slice(0, spanIndex);
    let backBypass1 = bypassArr1.slice(spanIndex + 1);
    
    racks.splice(rackIndex, 1); // 기존 랙 삭제
    
    // 앞쪽 그룹 생성
    if (frontSpans.length > 0) {
        let nSmall = frontSpans[frontSpans.length-1] === r.smallBeamLength ? 1 : 0;
        let nConn = frontSpans.length - 1 - nSmall;
        let totalLenMm = (frontSpans.length > 0 ? 85 : 0) + frontSpans.reduce((a,b)=>a+b, 0);
        
        let newFront = {
            ...r,
            independent: 1,
            connected: Math.max(0, nConn),
            smallConnected: nSmall,
            totalLengthPx: totalLenMm * currentScale,
            bypassBays: [ frontBypass0, frontBypass1 ]
        };
        newFront.isValid = checkRackValidPlacement(newFront);
        racks.push(newFront);
    }
    
    // 뒤쪽 그룹 생성
    if (backSpans.length > 0) {
        let deletedLenMm = 85 / (totalSpans+1) + spansArr[spanIndex];
        let offsetMm = 0;
        for(let i=0; i<=spanIndex; i++) {
            offsetMm += spansArr[i] + (85 / (totalSpans+1));
        }
        
        let offsetPx = offsetMm * currentScale;
        const currentAngle = typeof getRackAngle === 'function' ? getRackAngle(r) : (r.angle || 0);
        let newX = r.x + Math.cos(currentAngle) * offsetPx;
        let newY = r.y + Math.sin(currentAngle) * offsetPx;
        
        let nSmall = backSpans[backSpans.length-1] === r.smallBeamLength ? 1 : 0;
        let nConn = backSpans.length - 1 - nSmall;
        let totalLenMm = (backSpans.length > 0 ? 85 : 0) + backSpans.reduce((a,b)=>a+b, 0);
        
        let newBack = {
            ...r,
            x: newX,
            y: newY,
            independent: 1,
            connected: Math.max(0, nConn),
            smallConnected: nSmall,
            totalLengthPx: totalLenMm * currentScale,
            bypassBays: [ backBypass0, backBypass1 ]
        };
        newBack.isValid = checkRackValidPlacement(newBack);
        racks.push(newBack);
    }
    
    updateRackFormCounts();
    draw();
}

let isExtendingRack = false;
let extendingRackIndex = -1;
let extendInitialBays = 0;
let extendStartPos = { x: 0, y: 0 };

function getRackAngle(r) {
    if (r.angle !== undefined) return r.angle;
    if (r.isHoriz) {
        return r.dir < 0 ? Math.PI : 0;
    } else {
        return r.dir < 0 ? -Math.PI / 2 : Math.PI / 2;
    }
}

function getRackRotatedCorners(r) {
    const depthPx = getRackTotalDepthPx(r);
    const lenPx = (typeof getRackTotalLengthPx === 'function') ? getRackTotalLengthPx(r) : r.totalLengthPx;
    const angle = getRackAngle(r);
    const cos = Math.cos(angle);
    const sin = Math.sin(angle);
    
    const localCorners = [
        { x: 0, y: -depthPx / 2 },
        { x: lenPx, y: -depthPx / 2 },
        { x: lenPx, y: depthPx / 2 },
        { x: 0, y: depthPx / 2 }
    ];
    
    return localCorners.map(pt => ({
        x: pt.x * cos - pt.y * sin + r.x,
        y: pt.x * sin + pt.y * cos + r.y
    }));
}

function getRackTailPos(r) {
    const angle = getRackAngle(r);
    const lenPx = (typeof getRackTotalLengthPx === 'function') ? getRackTotalLengthPx(r) : r.totalLengthPx;
    const tailX = r.x + Math.cos(angle) * lenPx;
    const tailY = r.y + Math.sin(angle) * lenPx;
    return { x: tailX, y: tailY };
}

canvas.addEventListener('mousedown', (e) => {
    const {x: clickX, y: clickY} = getLogicalPos(e);

    // 0. 랙 끝단 핸들 드래그(연장/축소/회전/복사) 확인
    if (!isDrawingMode && currentScale > 0) {
        for (let i = racks.length - 1; i >= 0; i--) {
            const r = racks[i];
            
            if (typeof getRackHandlesPos === 'function') {
                const handles = getRackHandlesPos(r);
                if (handles) {
                    const hitRadius = (typeof getInteractiveHitRadius === 'function') ? getInteractiveHitRadius(15) : 15;
                    // 1) 회전 핸들 (10도 회전 - 리모컨 모드에 맞게 작동하며 Shift 클릭 시 방향 전환)
                    if (handles.rotate && Math.hypot(handles.rotate.x - clickX, handles.rotate.y - clickY) <= hitRadius) {
                        let step = (window.activeInteractMode === 'rotate-ccw') ? -10 : 10;
                        if (e.shiftKey) {
                            step = -step; // Shift 누르면 방향 반전
                        }
                        r.angle = (getRackAngle(r) + (step * Math.PI / 180)) % (Math.PI * 2);
                        // 기존 속성 동기화
                        r.isHoriz = Math.abs(Math.cos(r.angle)) > Math.abs(Math.sin(r.angle));
                        r.dir = (Math.cos(r.angle) > 0 || Math.sin(r.angle) > 0) ? 1 : -1;
                        
                        r.isValid = checkRackValidPlacement(r);
                        updateRackFormCounts();
                        draw();
                        return;
                    }
                    
                    // 2) 연장/축소 핸들
                    if (handles.extend && Math.hypot(handles.extend.x - clickX, handles.extend.y - clickY) <= hitRadius) {
                        isExtendingRack = true;
                        extendingRackIndex = i;
                        r.smallConnected = 0;
                        extendInitialBays = (r.independent || 1) + (r.connected || 0);
                        r.totalLengthPx = (85 + (extendInitialBays * r.beamLength)) * currentScale;
                        extendStartPos = { x: clickX, y: clickY };
                        updateRackFormCounts();
                        draw();
                        return;
                    }
                    
                    // 3) 복사 핸들 (드래그 복제)
                    if (handles.copy && Math.hypot(handles.copy.x - clickX, handles.copy.y - clickY) <= hitRadius) {
                        isMovingRack = true;
                        currentRackPreview = JSON.parse(JSON.stringify(r));
                        // 복제된 랙은 angle도 포함해야함
                        currentRackPreview.angle = getRackAngle(r);
                        rackDragOffsetX = r.x - clickX;
                        rackDragOffsetY = r.y - clickY;
                        updateRackFormCounts();
                        draw();
                        return;
                    }
                }
            } else {
                // fallback to original circle handle logic
                const tail = getRackTailPos(r);
                const hitRadius = (typeof getInteractiveHitRadius === 'function') ? getInteractiveHitRadius(20) : 20;
                if (Math.hypot(tail.x - clickX, tail.y - clickY) <= hitRadius) {
                    isExtendingRack = true;
                    extendingRackIndex = i;
                    r.smallConnected = 0;
                    extendInitialBays = (r.independent || 1) + (r.connected || 0);
                    r.totalLengthPx = (85 + (extendInitialBays * r.beamLength)) * currentScale;
                    extendStartPos = { x: clickX, y: clickY };
                    updateRackFormCounts();
                    draw();
                    return;
                }
            }
        }
    }

    // 1. 이미 배치된 랙 그룹 이동 확인
    // 바이패스 등 리모컨 모드가 활성화되어 있다면 일반적인 랙 드래그 이동은 비활성화
    const activeMode = window.activeInteractMode || null;
    const canMoveRack = (!activeMode || activeMode === 'move');
    if (!isDrawingMode && currentScale > 0 && canMoveRack) {
        for (let i = racks.length - 1; i >= 0; i--) {
            const r = racks[i];
            
            // 바운딩 박스 판별
            const boxes = getRackBoxes(r);
            let minX = boxes.physical.minX;
            let maxX = boxes.physical.maxX;
            let minY = boxes.physical.minY;
            let maxY = boxes.physical.maxY;
            
            // 약간의 터치 여유(padding)
            const pad = 5;
            if (clickX >= minX - pad && clickX <= maxX + pad && clickY >= minY - pad && clickY <= maxY + pad) {
                // 랙 드래그(이동) 모드 진입
                isMovingRack = true;
                rackDragOffsetX = r.x - clickX;
                rackDragOffsetY = r.y - clickY;
                
                // 기존 배열에서 빼내서 currentRackPreview로 띄움
                currentRackPreview = racks.splice(i, 1)[0];
                currentRackPreview.angle = getRackAngle(currentRackPreview);
                updateRackFormCounts();
                draw(); // 즉시 화면 갱신
                return;
            }
        }
    }

    // 2. 이미 배치된 장애물 드래그 시작 확인
    if (obstacles.length > 0) {
        for (let i = obstacles.length - 1; i >= 0; i--) {
            const obs = obstacles[i];
            const dist = Math.hypot(obs.x - clickX, obs.y - clickY);
            if (dist < 20) {
                draggingObstacleIndex = i;
                dragOffsetX = obs.x - clickX;
                dragOffsetY = obs.y - clickY;
                return;
            }
        }
    }

    // 3. 점 찍기 (도면 그리기 로직)
    if (isDrawingMode) {
        if (points.length > 2 && isPolygonClosed()) return;
        if (points.length >= 3) {
            const dist = Math.hypot(points[0].x - clickX, points[0].y - clickY);
            if (dist < 20) {
                points.push({ ...points[0] }); // 폐합
                finishDrawing();
                draw();
                return;
            }
        }
        points.push({ x: clickX, y: clickY });
        draw();
        return;
    }
});


canvas.addEventListener('mousemove', (e) => {
    const {x: currentX, y: currentY} = getLogicalPos(e);

    // 랙 끝단 드래그 확장/축소 처리 (스마트 장애물 감지 & 랙 자동 병합)
    if (isExtendingRack && extendingRackIndex >= 0 && extendingRackIndex < racks.length) {
        const r = racks[extendingRackIndex];
        const extAngle = getRackAngle(r);
        const deltaPx = (currentX - extendStartPos.x) * Math.cos(extAngle) + (currentY - extendStartPos.y) * Math.sin(extAngle);
        const deltaMm = deltaPx / currentScale;
        const smallBeamLength = r.smallBeamLength || getSmallBeamLength(r.beamLength);

        // 1. 같은 라인에 있는 맞은편 랙과의 병합(Auto-Merge) 스냅 체크
        let snapMergeTarget = null;
        for (let j = 0; j < racks.length; j++) {
            if (j === extendingRackIndex) continue;
            const other = racks[j];
            if (other.isHoriz === r.isHoriz) {
                const isCollinear = r.isHoriz ? (Math.abs(other.y - r.y) < 15) : (Math.abs(other.x - r.x) < 15);
                if (isCollinear) {
                    const myTail = getRackTailPos(r);
                    const distToOtherHead = Math.hypot(other.x - currentX, other.y - currentY);
                    if (distToOtherHead <= 35) {
                        snapMergeTarget = { index: j, rack: other };
                        break;
                    }
                }
            }
        }

        if (snapMergeTarget) {
            window.activeMergePreview = { sourceIndex: extendingRackIndex, targetIndex: snapMergeTarget.index };
            window.isExtendBlockedByObstacle = false;
        } else {
            window.activeMergePreview = null;
            
            let bayDelta = Math.round(deltaMm / r.beamLength);
            let newTotalBays = Math.max(1, extendInitialBays + bayDelta);
            
            // 2. 스마트 장애물 및 충돌 검사 (순수 표준 빔 단위로만 확장/축소)
            r.independent = 1;
            r.connected = Math.max(0, newTotalBays - 1);
            r.smallConnected = 0;
            r.totalLengthPx = (85 + (newTotalBays * r.beamLength)) * currentScale;
            
            let isValid = checkRackValidPlacement(r);
            if (!isValid && newTotalBays > extendInitialBays) {
                let foundSafeBays = extendInitialBays;
                for (let b = newTotalBays - 1; b >= extendInitialBays; b--) {
                    r.connected = Math.max(0, b - 1);
                    r.smallConnected = 0;
                    r.totalLengthPx = (85 + (b * r.beamLength)) * currentScale;
                    if (checkRackValidPlacement(r)) {
                        foundSafeBays = b;
                        break;
                    }
                }
                
                // Try small connection
                r.connected = Math.max(0, foundSafeBays - 1);
                r.smallConnected = 1;
                r.smallBeamLength = smallBeamLength;
                r.totalLengthPx = (85 + (foundSafeBays * r.beamLength) + smallBeamLength) * currentScale;
                
                if (!checkRackValidPlacement(r)) {
                    r.smallConnected = 0;
                    r.smallBeamLength = 0;
                    r.totalLengthPx = (85 + (foundSafeBays * r.beamLength)) * currentScale;
                }

                // 🌟 사용자가 당긴 목표 베이 수보다 장애물로 인해 실제 배치된 베이 수가 적을 때 경고 안내
                const actualBays = (r.independent || 1) + (r.connected || 0) + (r.smallConnected ? 1 : 0);
                if (newTotalBays > actualBays) {
                    window.isExtendBlockedByObstacle = true;
                    showCanvasAlert('장애물(기둥/벽면) 또는 지게차 회전 통로(2,800mm) 간섭으로 인해 더 이상 랙을 늘릴 수 없습니다.', 'warning', 3200);
                } else {
                    window.isExtendBlockedByObstacle = false;
                }
            } else {
                window.isExtendBlockedByObstacle = false;
            }
        }
        
        r.isValid = checkRackValidPlacement(r);
        updateRackFormCounts();
        draw();
        return;
    }

    // 장애물 드래그 중
    if (draggingObstacleIndex !== -1) {
        const obs = obstacles[draggingObstacleIndex];
        const targetX = currentX + dragOffsetX;
        const targetY = currentY + dragOffsetY;
        
        const isDoorLike = obs.type === 'door' || obs.type === 'shutter';
        if (!isDoorLike) {
            let snappedPos = calculatePillarSnap(targetX, targetY, obs);
            obs.x = snappedPos.x;
            obs.y = snappedPos.y;
        } else {
            let closestLine = getClosestLineSegment(targetX, targetY);
            if (closestLine) {
                obs.x = closestLine.x;
                obs.y = closestLine.y;
                obs.angle = closestLine.angle;
                obs.edgeIndex = closestLine.edgeIndex;
                obs.ratioOnEdge = closestLine.ratioOnEdge;
                obs.visualDistFromStart = closestLine.visualDistFromStart;
                obs.visualEdgeLength = closestLine.visualEdgeLength;
            } else {
                obs.x = targetX;
                obs.y = targetY;
                obs.edgeIndex = -1;
            }
        }
        draw();
        return;
    }
    
    // 랙 이동(Drag & Snap) 모드
    if (isMovingRack && currentRackPreview) {
        let targetX = currentX + rackDragOffsetX;
        let targetY = currentY + rackDragOffsetY;
        
        // 마그네틱 스냅 로직 적용 (Shift 키 누를 시 자유 이동)
        let snappedPos = calculateRackSnap(targetX, targetY, currentRackPreview, e.shiftKey);
        currentRackPreview.x = snappedPos.x;
        currentRackPreview.y = snappedPos.y;
        
        // 이동 중인 위치가 유효한지 검사 (도면 밖이거나 간섭이 있으면 빨간색)
        currentRackPreview.isValid = checkRackValidPlacement(currentRackPreview);
        draw();
        return;
    }

    // 마우스 호버 커서 처리 (핸들 위: 크기조절 커서/포인터, 랙 위: grab 커서)
    if (!isDrawingMode && !isMovingRack && !isExtendingRack && currentScale > 0) {
        let isNearTail = false;
        let isNearBody = false;
        for (let r of racks) {
            if (typeof getRackHandlesPos === 'function') {
                const handles = getRackHandlesPos(r);
                if (handles) {
                    // 회전 핸들 또는 복사 핸들 근처면 pointer
                    if (Math.hypot(handles.rotate.x - currentX, handles.rotate.y - currentY) <= 12 ||
                        Math.hypot(handles.copy.x - currentX, handles.copy.y - currentY) <= 12) {
                        canvas.style.cursor = 'pointer';
                        isNearTail = true;
                        break;
                    }
                    // 연장 핸들 근처면 크기조절 커서
                    if (Math.hypot(handles.extend.x - currentX, handles.extend.y - currentY) <= 12) {
                        canvas.style.cursor = r.isHoriz ? 'ew-resize' : 'ns-resize';
                        isNearTail = true;
                        break;
                    }
                }
            } else {
                const tail = getRackTailPos(r);
                if (Math.hypot(tail.x - currentX, tail.y - currentY) <= 20) {
                    canvas.style.cursor = r.isHoriz ? 'ew-resize' : 'ns-resize';
                    isNearTail = true;
                    break;
                }
            }
            
            const boxes = getRackBoxes(r);
            let minX = boxes.physical.minX;
            let maxX = boxes.physical.maxX;
            let minY = boxes.physical.minY;
            let maxY = boxes.physical.maxY;
            if (currentX >= minX && currentX <= maxX && currentY >= minY && currentY <= maxY) {
                isNearBody = true;
            }
        }
        if (!isNearTail) {
            canvas.style.cursor = isNearBody ? 'grab' : 'default';
        }
    }

    // 도면 그리는 중 가이드 선
    if (isDrawingMode) {
        mousePos = { x: currentX, y: currentY };
        draw();
    }
});

canvas.addEventListener('mouseup', (e) => {
    if (isExtendingRack) {
        if (window.activeMergePreview && window.activeMergePreview.sourceIndex < racks.length && window.activeMergePreview.targetIndex < racks.length) {
            const src = racks[window.activeMergePreview.sourceIndex];
            const tgt = racks[window.activeMergePreview.targetIndex];
            if (src && tgt) {
                // 두 랙을 하나로 병합 (독립 1대 + 연결 N대)
                src.connected += (tgt.independent || 1) + (tgt.connected || 0);
                src.smallConnected += (tgt.smallConnected || 0);
                // 바이패스 배열 병합
                src.bypassBays = [
                    ((src.bypassBays && src.bypassBays[0]) || []).concat((tgt.bypassBays && tgt.bypassBays[0]) || []),
                    ((src.bypassBays && src.bypassBays[1]) || []).concat((tgt.bypassBays && tgt.bypassBays[1]) || [])
                ];
                const smallBeamLength = src.smallBeamLength || getSmallBeamLength(src.beamLength);
                src.totalLengthPx = (85 + ((src.independent + src.connected) * src.beamLength) + (src.smallConnected * smallBeamLength)) * currentScale;
                src.isValid = checkRackValidPlacement(src);
                racks.splice(window.activeMergePreview.targetIndex, 1);
            }
            window.activeMergePreview = null;
        }
        window.isExtendBlockedByObstacle = false;
        isExtendingRack = false;
        extendingRackIndex = -1;
        updateRackFormCounts();
        draw();
    }

    if (draggingObstacleIndex !== -1) {
        draggingObstacleIndex = -1;
        renderObstacleInputs();
    }
    
    if (isDrawingRack) {
        isDrawingRack = false;
        currentRackPreview = null;
        draw();
    }
    
    if (isMovingRack) {
        isMovingRack = false;
        if (currentRackPreview) {
            let merged = checkAndMergeRack(currentRackPreview);
            if (!merged) {
                racks.push(currentRackPreview);
            }
            updateRackFormCounts();
            
            if (!currentRackPreview.isValid) {
                console.log("경고: 유효하지 않은 위치에 놓였습니다.");
            }
        }
        currentRackPreview = null;
        draw();
    }
});

// ==================== 모바일 터치(Touch) 지원 및 반응성 최적화 ====================
if (canvas) {
    canvas.addEventListener('touchstart', (e) => {
        if (e.touches.length === 1) {
            const touch = e.touches[0];
            const mouseEvt = new MouseEvent('mousedown', {
                clientX: touch.clientX,
                clientY: touch.clientY,
                bubbles: true,
                cancelable: true
            });
            if (isDrawingMode || isMovingRack || (typeof isExtendingRack !== 'undefined' && isExtendingRack) || currentScale > 0) {
                e.preventDefault();
            }
            canvas.dispatchEvent(mouseEvt);
        }
    }, { passive: false });

    canvas.addEventListener('touchmove', (e) => {
        if (e.touches.length === 1) {
            const touch = e.touches[0];
            const mouseEvt = new MouseEvent('mousemove', {
                clientX: touch.clientX,
                clientY: touch.clientY,
                bubbles: true,
                cancelable: true
            });
            if (isMovingRack || (typeof isExtendingRack !== 'undefined' && isExtendingRack) || isDrawingMode) {
                e.preventDefault();
            }
            canvas.dispatchEvent(mouseEvt);
        }
    }, { passive: false });

    canvas.addEventListener('touchend', (e) => {
        const mouseEvt = new MouseEvent('mouseup', {
            bubbles: true,
            cancelable: true
        });
        if (isMovingRack || (typeof isExtendingRack !== 'undefined' && isExtendingRack)) {
            e.preventDefault();
        }
        canvas.dispatchEvent(mouseEvt);
    }, { passive: false });
}

function checkAndMergeRack(movingRack) {
    const movingAngle = getRackAngle(movingRack);
    for (let i = 0; i < racks.length; i++) {
        let placedRack = racks[i];
        
        const placedAngle = getRackAngle(placedRack);
        if (Math.abs(movingAngle - placedAngle) > 0.02) continue;
        if (movingRack.beamLength !== placedRack.beamLength) continue;
        
        // 일직선상 여부 (Perpendicular distance < 5px)
        const dx = movingRack.x - placedRack.x;
        const dy = movingRack.y - placedRack.y;
        const perpDist = Math.abs(-dx * Math.sin(placedAngle) + dy * Math.cos(placedAngle));
        if (perpDist > 5) continue;
        
        // placedRack 꼬리
        const placedTail = getRackTailPos(placedRack);
        
        // movingRack 머리가 placedRack 꼬리에 닿을 때
        if (Math.hypot(placedTail.x - movingRack.x, placedTail.y - movingRack.y) < 15) {
            placedRack.connected += movingRack.independent + movingRack.connected;
            placedRack.smallConnected += movingRack.smallConnected;
            // 바이패스 배열 병합
            placedRack.bypassBays = (placedRack.bypassBays || []).concat(movingRack.bypassBays || []);
            
            let spans = placedRack.independent + placedRack.connected;
            let lenMm = 85 + (spans * placedRack.beamLength) + (placedRack.smallConnected * placedRack.smallBeamLength);
            placedRack.totalLengthPx = lenMm * currentScale;
            placedRack.isValid = checkRackValidPlacement(placedRack);
            return true;
        }
        
        // movingRack 꼬리가 placedRack 머리에 닿을 때
        const movingTail = getRackTailPos(movingRack);
        
        if (Math.hypot(movingTail.x - placedRack.x, movingTail.y - placedRack.y) < 15) {
            placedRack.x = movingRack.x; 
            placedRack.y = movingRack.y;
            placedRack.connected += movingRack.independent + movingRack.connected;
            placedRack.smallConnected += movingRack.smallConnected;
            // 바이패스 배열 병합
            placedRack.bypassBays = (movingRack.bypassBays || []).concat(placedRack.bypassBays || []);
            
            let spans = placedRack.independent + placedRack.connected;
            let lenMm = 85 + (spans * placedRack.beamLength) + (placedRack.smallConnected * placedRack.smallBeamLength);
            placedRack.totalLengthPx = lenMm * currentScale;
            placedRack.isValid = checkRackValidPlacement(placedRack);
            return true;
        }
    }
    return false;
}

canvas.addEventListener('dragover', (e) => {
    e.preventDefault(); 
});

canvas.addEventListener('drop', (e) => {
    e.preventDefault();
    if (!draggedItemType) return;
    if (!isPolygonClosed()) {
        alert("도면을 먼저 닫아서 완성한 후 장애물을 배치해주세요!");
        draggedItemType = null;
        return;
    }

    const {x: dropX, y: dropY} = getLogicalPos(e);

    if (draggedItemType === 'pillar' || draggedItemType === 'machine' || draggedItemType === 'hydrant' || draggedItemType === 'panel' || draggedItemType === 'forbidden') {
        const typeLabel = {
            'pillar': '기둥',
            'machine': '기계',
            'hydrant': '소화전',
            'panel': '전기판넬',
            'forbidden': '사용불가'
        }[draggedItemType];
        const pCount = obstacles.filter(o => o.type === draggedItemType).length + 1;
        obstacles.push({ type: draggedItemType, name: typeLabel + pCount, x: dropX, y: dropY, width: 500, height: 500 });
    } else if (draggedItemType === 'door' || draggedItemType === 'shutter') {
        const typeLabel = {
            'door': '출입문',
            'shutter': '셔터'
        }[draggedItemType];
        const dCount = obstacles.filter(o => o.type === draggedItemType).length + 1;
        let closestLine = getClosestLineSegment(dropX, dropY);
        if (closestLine) {
            obstacles.push({ 
                type: draggedItemType, name: typeLabel + dCount, 
                x: closestLine.x, y: closestLine.y, 
                angle: closestLine.angle, length: 3500,
                edgeIndex: closestLine.edgeIndex,
                ratioOnEdge: closestLine.ratioOnEdge,
                visualDistFromStart: 10000,
                visualEdgeLength: closestLine.visualEdgeLength
            });
            // 출입문/셔터 드롭 시 기본 위치 10,000mm로 자동 배치
            if (typeof updateObstaclePosition === 'function') {
                updateObstaclePosition(obstacles.length - 1, 10000);
            }
        } else {
            obstacles.push({ type: draggedItemType, name: typeLabel + dCount, x: dropX, y: dropY, angle: 0, length: 3500, edgeIndex: -1, visualDistFromStart: 10000 });
        }
    } else if (draggedItemType === 'rack') {
        if (typeof window.spawnInitialRacks === 'function') {
            window.spawnInitialRacks(dropX, dropY);
        }
    }
    draggedItemType = null;
    draw();
    renderObstacleInputs(); 
});

function getClosestLineSegment(x, y) {
    let minDist = Infinity;
    let bestSnap = null;

    for (let i = 0; i < points.length - 1; i++) {
        const p1 = points[i];
        const p2 = points[i + 1];

        const A = x - p1.x;
        const B = y - p1.y;
        const C = p2.x - p1.x;
        const D = p2.y - p1.y;

        const dot = A * C + B * D;
        const len_sq = C * C + D * D;
        let param = -1;
        if (len_sq != 0) param = dot / len_sq;

        let xx, yy;
        if (param < 0) {
            xx = p1.x; yy = p1.y;
            param = 0;
        } else if (param > 1) {
            xx = p2.x; yy = p2.y;
            param = 1;
        } else {
            xx = p1.x + param * C;
            yy = p1.y + param * D;
        }

        const dx = x - xx;
        const dy = y - yy;
        const dist = Math.sqrt(dx * dx + dy * dy);

        if (dist < minDist) {
            minDist = dist;
            const angle = Math.atan2(p2.y - p1.y, p2.x - p1.x);
            
            if (typeof edgeLengths !== 'undefined' && edgeLengths[i] > 0) {
                const realWallLength = edgeLengths[i];
                let distFromP1Mm = param * realWallLength;
                distFromP1Mm = Math.round(distFromP1Mm / 10) * 10;
                
                param = distFromP1Mm / realWallLength;
                if (param < 0) param = 0;
                if (param > 1) param = 1;
                
                xx = p1.x + param * C;
                yy = p1.y + param * D;
            }

            const visualDistFromStart = Math.hypot(xx - p1.x, yy - p1.y);
            const visualEdgeLength = Math.hypot(p2.x - p1.x, p2.y - p1.y);
            bestSnap = { x: xx, y: yy, angle: angle, edgeIndex: i, ratioOnEdge: param, visualDistFromStart, visualEdgeLength };
        }
    }
    
    if (minDist < 100) return bestSnap;
    return null;
}

function isPolygonClosed() {
    if (points.length < 4) return false;
    const first = points[0];
    const last = points[points.length - 1];
    return first.x === last.x && first.y === last.y;
}

function finishDrawing() {
    isDrawingMode = false;
    const numEdges = points.length - 1;
    
    originalAngles = [];
    originalVisualLengths = [];
    userEnteredEdges = [];
    
    for (let i = 0; i < numEdges; i++) {
        let p1 = points[i];
        let p2 = points[i+1];
        let rawAngle = Math.atan2(p2.y - p1.y, p2.x - p1.x);
        
        // 90도 (수평 0, 180도 / 수직 90, 270도) 근접 검사
        const snapRadian = Math.PI / 2;
        const nearestOrthogonal = Math.round(rawAngle / snapRadian) * snapRadian;
        let diff = Math.abs(rawAngle - nearestOrthogonal);
        while (diff > Math.PI) diff = Math.abs(diff - Math.PI * 2);
        
        let finalAngle = rawAngle;
        // 수평/수직과 12도(약 0.2 라디안) 이내일 때만 직각(수평/수직)으로 스냅
        if (diff <= 12 * (Math.PI / 180)) {
            finalAngle = nearestOrthogonal;
        } else {
            // 45도 사선과 6도 이내일 때만 45도 스냅
            const snap45 = Math.PI / 4;
            const nearest45 = Math.round(rawAngle / snap45) * snap45;
            let diff45 = Math.abs(rawAngle - nearest45);
            while (diff45 > Math.PI) diff45 = Math.abs(diff45 - Math.PI * 2);
            if (diff45 <= 6 * (Math.PI / 180)) {
                finalAngle = nearest45;
            }
            // 그 외의 5각형, 6각형 사선 각도는 본래 그린 각도 그대로 100% 보존
        }
        
        originalAngles.push(finalAngle);
        let dist = Math.hypot(p2.x - p1.x, p2.y - p1.y);
        originalVisualLengths.push(dist);
    }
    
    if (typeof window.generateCustomInputs === 'function') {
        window.generateCustomInputs(numEdges);
    }
}

function getEdgeClearance(p1, p2) {
    let maxPillarDepth = 0;
    const cornerDeadZonePx = 1200 * currentScale; // 코너 데드스페이스 영역
    
    obstacles.forEach(obs => {
        if (obs.type === 'pillar') {
            let w = (obs.width || 500) * currentScale;
            let h = (obs.height || 500) * currentScale;
            
            if (Math.abs(p1.x - p2.x) < 5) { // 수직 벽면
                let wallX = p1.x;
                if (Math.abs(obs.x - w/2 - wallX) < 15 || Math.abs(obs.x + w/2 - wallX) < 15 || Math.abs(obs.x - wallX) < 15) {
                    let minY = Math.min(p1.y, p2.y);
                    let maxY = Math.max(p1.y, p2.y);
                    // 코너 영역(데드스페이스)에 완전히 속해 있는지 검사
                    let distFromP1 = Math.abs(obs.y - p1.y);
                    let distFromP2 = Math.abs(obs.y - p2.y);
                    let isCornerPillar = (distFromP1 < cornerDeadZonePx || distFromP2 < cornerDeadZonePx);
                    
                    if (!isCornerPillar && obs.y >= minY - h/2 - 5 && obs.y <= maxY + h/2 + 5) {
                        maxPillarDepth = Math.max(maxPillarDepth, w);
                    }
                }
            } else if (Math.abs(p1.y - p2.y) < 5) { // 수평 벽면
                let wallY = p1.y;
                if (Math.abs(obs.y - h/2 - wallY) < 15 || Math.abs(obs.y + h/2 - wallY) < 15 || Math.abs(obs.y - wallY) < 15) {
                    let minX = Math.min(p1.x, p2.x);
                    let maxX = Math.max(p1.x, p2.x);
                    // 코너 영역(데드스페이스)에 완전히 속해 있는지 검사
                    let distFromP1 = Math.abs(obs.x - p1.x);
                    let distFromP2 = Math.abs(obs.x - p2.x);
                    let isCornerPillar = (distFromP1 < cornerDeadZonePx || distFromP2 < cornerDeadZonePx);
                    
                    if (!isCornerPillar && obs.x >= minX - w/2 - 5 && obs.x <= maxX + w/2 + 5) {
                        maxPillarDepth = Math.max(maxPillarDepth, h);
                    }
                }
            }
        }
    });
    return maxPillarDepth + (100 * currentScale);
}

// 마그네틱 스냅 로직 (부드럽고 쫀득한 지능형 마그네틱 스냅)
function calculateRackSnap(targetX, targetY, r, isShiftPressed = false) {
    // Shift 키를 누르거나 스냅 가이드가 꺼진 경우 스냅을 완전히 무시하고 100% 부드러운 픽셀 추적 자유 이동
    if (isShiftPressed || (typeof snapGuideEnabled !== 'undefined' && !snapGuideEnabled)) {
        return { x: targetX, y: targetY };
    }

    let snappedX = targetX;
    let snappedY = targetY;
    // 부드럽고 자연스러운 초미세 스냅 반경 (기존 14px -> 4~5px 이내로 정밀 제어하여 툭툭 끊기거나 튀는 현상 제거)
    const SNAP_DIST = Math.min(5, Math.max(3, (typeof currentScale !== 'undefined' && currentScale > 0) ? 100 * currentScale : 5));

    let snappedXApplied = false;
    let snappedYApplied = false;

    // 현재 타겟 좌표 기준 임시 랙 및 정확한 회전 바운딩 박스
    const tempRack = { ...r, x: targetX, y: targetY };
    const tempBoxes = getRackBoxes(tempRack);
    let minX = tempBoxes.physical.minX;
    let maxX = tempBoxes.physical.maxX;
    let minY = tempBoxes.physical.minY;
    let maxY = tempBoxes.physical.maxY;
    let rackCenterX = (minX + maxX) / 2;
    let rackCenterY = (minY + maxY) / 2;

    // [1] 머리-꼬리 합체 스냅 (초미세 반경에서만 정확히 흡착)
    const curAngle = getRackAngle(r);
    for (let i = 0; i < racks.length; i++) {
        let placed = racks[i];
        if (placed === r) continue;
        
        const placedAngle = getRackAngle(placed);
        if (Math.abs(placedAngle - curAngle) < 0.05) {
            const placedTail = getRackTailPos(placed);
            
            // 내 머리가 상대방 꼬리에 스냅
            if (Math.hypot(targetX - placedTail.x, targetY - placedTail.y) < SNAP_DIST) {
                snappedX = placedTail.x;
                snappedY = placedTail.y;
                snappedXApplied = true;
                snappedYApplied = true;
                break;
            }
            
            // 내 꼬리가 상대방 머리에 스냅
            const myTail = getRackTailPos(tempRack);
            if (Math.hypot(myTail.x - placed.x, myTail.y - placed.y) < SNAP_DIST) {
                snappedX = placed.x - (myTail.x - targetX);
                snappedY = placed.y - (myTail.y - targetY);
                snappedXApplied = true;
                snappedYApplied = true;
                break;
            }
        }
    }

    // [2] 벽면 마그네틱 스냅 (벽면 여유거리 CLEARANCE 위치에 가볍게 스냅, 툭툭 끊기게 만드는 벽 중앙 스냅은 제외)
    if (!snappedXApplied || !snappedYApplied) {
        let bestWallDeltaX = null;
        let bestWallDeltaY = null;
        let minWallDistX = SNAP_DIST;
        let minWallDistY = SNAP_DIST;

        for (let i = 0; i < points.length - 1; i++) {
            const p1 = points[i];
            const p2 = points[i+1];
            let CLEARANCE = getEdgeClearance(p1, p2);
            
            // 수직 벽면
            if (Math.abs(p1.x - p2.x) < 5) {
                let wallX = p1.x;
                if (!snappedXApplied) {
                    let d1 = Math.abs(minX - (wallX + CLEARANCE));
                    if (d1 < minWallDistX) {
                        minWallDistX = d1;
                        bestWallDeltaX = (wallX + CLEARANCE) - minX;
                    }
                    let d2 = Math.abs(maxX - (wallX - CLEARANCE));
                    if (d2 < minWallDistX) {
                        minWallDistX = d2;
                        bestWallDeltaX = (wallX - CLEARANCE) - maxX;
                    }
                }
            }
            
            // 수평 벽면
            if (Math.abs(p1.y - p2.y) < 5) {
                let wallY = p1.y;
                if (!snappedYApplied) {
                    let d1 = Math.abs(minY - (wallY + CLEARANCE));
                    if (d1 < minWallDistY) {
                        minWallDistY = d1;
                        bestWallDeltaY = (wallY + CLEARANCE) - minY;
                    }
                    let d2 = Math.abs(maxY - (wallY - CLEARANCE));
                    if (d2 < minWallDistY) {
                        minWallDistY = d2;
                        bestWallDeltaY = (wallY - CLEARANCE) - maxY;
                    }
                }
            }
        }

        if (!snappedXApplied && bestWallDeltaX !== null) {
            snappedX += bestWallDeltaX;
            snappedXApplied = true;
        }
        if (!snappedYApplied && bestWallDeltaY !== null) {
            snappedY += bestWallDeltaY;
            snappedYApplied = true;
        }
    }

    // [3] 통로 간격(Aisle) 2800mm 스냅 (가로 및 세로 회전 랙 지원)
    const AISLE_DIST_PX = 2800 * currentScale;

    // 가로 랙: Y축 통로 스냅
    if (r.isHoriz && !snappedYApplied) {
        let bestAisleDeltaY = null;
        let minAisleDistY = SNAP_DIST;

        for (let i = 0; i < racks.length; i++) {
            let placed = racks[i];
            if (placed === r || !placed.isHoriz) continue;

            const placedBoxes = getRackBoxes(placed);
            let pMinX = placedBoxes.physical.minX;
            let pMaxX = placedBoxes.physical.maxX;

            // X 구간이 서로 겹치는지 검사
            if (!(maxX < pMinX || minX > pMaxX)) {
                let pMinY = placedBoxes.physical.minY;
                let pMaxY = placedBoxes.physical.maxY;

                // 내 랙이 placed 아래에 있을 때
                let dBottom = Math.abs((minY - pMaxY) - AISLE_DIST_PX);
                if (dBottom < minAisleDistY) {
                    minAisleDistY = dBottom;
                    bestAisleDeltaY = (pMaxY + AISLE_DIST_PX) - minY;
                }
                // 내 랙이 placed 위에 있을 때
                let dTop = Math.abs((pMinY - maxY) - AISLE_DIST_PX);
                if (dTop < minAisleDistY) {
                    minAisleDistY = dTop;
                    bestAisleDeltaY = (pMinY - AISLE_DIST_PX) - maxY;
                }
            }
        }
        if (bestAisleDeltaY !== null) {
            snappedY += bestAisleDeltaY;
            snappedYApplied = true;
        }
    }

    // 세로 랙: X축 통로 스냅 (실제 바운딩 박스 기준 정밀 보정)
    if (!r.isHoriz && !snappedXApplied) {
        let bestAisleDeltaX = null;
        let minAisleDistX = SNAP_DIST;

        for (let i = 0; i < racks.length; i++) {
            let placed = racks[i];
            if (placed === r || placed.isHoriz) continue;

            const placedBoxes = getRackBoxes(placed);
            let pMinY = placedBoxes.physical.minY;
            let pMaxY = placedBoxes.physical.maxY;

            // Y 구간이 서로 겹치는지 검사
            if (!(maxY < pMinY || minY > pMaxY)) {
                let pMinX = placedBoxes.physical.minX;
                let pMaxX = placedBoxes.physical.maxX;

                // 내 랙이 placed 오른쪽에 있을 때
                let dRight = Math.abs((minX - pMaxX) - AISLE_DIST_PX);
                if (dRight < minAisleDistX) {
                    minAisleDistX = dRight;
                    bestAisleDeltaX = (pMaxX + AISLE_DIST_PX) - minX;
                }
                // 내 랙이 placed 왼쪽에 있을 때
                let dLeft = Math.abs((pMinX - maxX) - AISLE_DIST_PX);
                if (dLeft < minAisleDistX) {
                    minAisleDistX = dLeft;
                    bestAisleDeltaX = (pMinX - AISLE_DIST_PX) - maxX;
                }
            }
        }
        if (bestAisleDeltaX !== null) {
            snappedX += bestAisleDeltaX;
            snappedXApplied = true;
        }
    }

    // [4] 통로 중앙 스냅 (아주 좁은 3px 이내에서만 보조)
    const CENTER_SNAP_DIST = Math.min(3, SNAP_DIST);
    if (r.isHoriz && !snappedYApplied) {
        let boundTop = null; 
        let boundBottom = null;

        for (let i = 0; i < points.length - 1; i++) {
            let p1 = points[i]; let p2 = points[i+1];
            if (Math.abs(p1.y - p2.y) < 5) {
                let wallY = p1.y;
                let wallMinX = Math.min(p1.x, p2.x);
                let wallMaxX = Math.max(p1.x, p2.x);
                if (!(maxX < wallMinX || minX > wallMaxX)) {
                    let clearance = getEdgeClearance(p1, p2); 
                    if (wallY < targetY) {
                        let effWallY = wallY + clearance;
                        if (boundTop === null || effWallY > boundTop) boundTop = effWallY;
                    } else {
                        let effWallY = wallY - clearance;
                        if (boundBottom === null || effWallY < boundBottom) boundBottom = effWallY;
                    }
                }
            }
        }

        obstacles.forEach(obs => {
            if (obs.type === 'door') {
                let w = (obs.length || 2000) * currentScale;
                let clearance = 2800 * currentScale;
                let isHorizDoor = Math.abs(Math.cos(obs.angle)) > 0.5;
                if (isHorizDoor) {
                    let obsMinX = obs.x - w/2;
                    let obsMaxX = obs.x + w/2;
                    if (!(maxX < obsMinX || minX > obsMaxX)) {
                        if (obs.y < targetY) {
                            let effY = obs.y + clearance;
                            if (boundTop === null || effY > boundTop) boundTop = effY;
                        } else {
                            let effY = obs.y - clearance;
                            if (boundBottom === null || effY < boundBottom) boundBottom = effY;
                        }
                    }
                }
            }
        });

        for (let placed of racks) {
            if (placed === r) continue;
            const placedBoxes = getRackBoxes(placed);
            let pMinX = placedBoxes.physical.minX;
            let pMaxX = placedBoxes.physical.maxX;
            if (!(maxX < pMinX || minX > pMaxX)) {
                if (placedBoxes.physical.maxY < minY) {
                    let effY = placedBoxes.physical.maxY;
                    if (boundTop === null || effY > boundTop) boundTop = effY;
                } else if (placedBoxes.physical.minY > maxY) {
                    let effY = placedBoxes.physical.minY;
                    if (boundBottom === null || effY < boundBottom) boundBottom = effY;
                }
            }
        }

        if (boundTop !== null && boundBottom !== null) {
            let centerSpaceY = (boundTop + boundBottom) / 2;
            if (Math.abs(rackCenterY - centerSpaceY) < CENTER_SNAP_DIST) {
                snappedY += (centerSpaceY - rackCenterY);
                snappedYApplied = true;
            }
        }
    } else if (!r.isHoriz && !snappedXApplied) {
        let boundLeft = null; 
        let boundRight = null;

        for (let i = 0; i < points.length - 1; i++) {
            let p1 = points[i]; let p2 = points[i+1];
            if (Math.abs(p1.x - p2.x) < 5) {
                let wallX = p1.x;
                let wallMinY = Math.min(p1.y, p2.y);
                let wallMaxY = Math.max(p1.y, p2.y);
                if (!(maxY < wallMinY || minY > wallMaxY)) {
                    let clearance = getEdgeClearance(p1, p2);
                    if (wallX < targetX) {
                        let effWallX = wallX + clearance;
                        if (boundLeft === null || effWallX > boundLeft) boundLeft = effWallX;
                    } else {
                        let effWallX = wallX - clearance;
                        if (boundRight === null || effWallX < boundRight) boundRight = effWallX;
                    }
                }
            }
        }

        obstacles.forEach(obs => {
            if (obs.type === 'door') {
                let w = (obs.length || 2000) * currentScale;
                let clearance = 2800 * currentScale;
                let isHorizDoor = Math.abs(Math.cos(obs.angle)) > 0.5;
                if (!isHorizDoor) {
                    let obsMinY = obs.y - w/2;
                    let obsMaxY = obs.y + w/2;
                    if (!(maxY < obsMinY || minY > obsMaxY)) {
                        if (obs.x < targetX) {
                            let effX = obs.x + clearance;
                            if (boundLeft === null || effX > boundLeft) boundLeft = effX;
                        } else {
                            let effX = obs.x - clearance;
                            if (boundRight === null || effX < boundRight) boundRight = effX;
                        }
                    }
                }
            }
        });

        for (let placed of racks) {
            if (placed === r) continue;
            const placedBoxes = getRackBoxes(placed);
            let pMinY = placedBoxes.physical.minY;
            let pMaxY = placedBoxes.physical.maxY;
            if (!(maxY < pMinY || minY > pMaxY)) {
                if (placedBoxes.physical.maxX < minX) {
                    let effX = placedBoxes.physical.maxX;
                    if (boundLeft === null || effX > boundLeft) boundLeft = effX;
                } else if (placedBoxes.physical.minX > maxX) {
                    let effX = placedBoxes.physical.minX;
                    if (boundRight === null || effX < boundRight) boundRight = effX;
                }
            }
        }

        if (boundLeft !== null && boundRight !== null) {
            let centerSpaceX = (boundLeft + boundRight) / 2;
            if (Math.abs(rackCenterX - centerSpaceX) < CENTER_SNAP_DIST) {
                snappedX += (centerSpaceX - rackCenterX);
                snappedXApplied = true;
            }
        }
    }

    return { x: snappedX, y: snappedY };
}

// 기둥 마그네틱 스냅 로직
function calculatePillarSnap(targetX, targetY, obs) {
    let snappedX = targetX;
    let snappedY = targetY;
    const SNAP_DIST = 25;
    let w = (obs.width || 500) * currentScale;
    let h = (obs.height || 500) * currentScale;

    let minX = targetX - w/2;
    let maxX = targetX + w/2;
    let minY = targetY - h/2;
    let maxY = targetY + h/2;

    for (let i = 0; i < points.length - 1; i++) {
        const p1 = points[i];
        const p2 = points[i+1];
        
        // 수직 벽면 스냅
        if (Math.abs(p1.x - p2.x) < 5) {
            let wallX = p1.x;
            if (Math.abs(minX - wallX) < SNAP_DIST) {
                snappedX += wallX - minX;
            } else if (Math.abs(maxX - wallX) < SNAP_DIST) {
                snappedX += wallX - maxX;
            }
        }
        
        // 수평 벽면 스냅
        if (Math.abs(p1.y - p2.y) < 5) {
            let wallY = p1.y;
            if (Math.abs(minY - wallY) < SNAP_DIST) {
                snappedY += wallY - minY;
            } else if (Math.abs(maxY - wallY) < SNAP_DIST) {
                snappedY += wallY - maxY;
            }
        }
    }
    return { x: snappedX, y: snappedY };
}

// --- 랙 배치 제약 알고리즘 (Constraints) ---

function getRackTotalDepthPx(r) {
    const singleDepth = r.rackDepth || 1000;
    if (r.isDouble) {
        const holderSize = r.holderSize || 200;
        return (singleDepth * 2 + holderSize) * currentScale;
    }
    return singleDepth * currentScale;
}

function getRackBoxes(r) {
    const depthPx = getRackTotalDepthPx(r);
    const cornerClearancePx = depthPx + (100 * currentScale);
    
    // 회전된 4개 모서리 좌표 구하기
    const corners = getRackRotatedCorners(r);
    const xs = corners.map(c => c.x);
    const ys = corners.map(c => c.y);
    
    const pxMinX = Math.min(...xs);
    const pxMaxX = Math.max(...xs);
    const pxMinY = Math.min(...ys);
    const pxMaxY = Math.max(...ys);
    
    let physicalBox = { minX: pxMinX, maxX: pxMaxX, minY: pxMinY, maxY: pxMaxY };
    
    // 클리어런스 박스 (코너 사각지대를 위해 길이 방향 양 끝단 확장)
    const angle = getRackAngle(r);
    const cos = Math.cos(angle);
    const sin = Math.sin(angle);
    const lenPx = r.totalLengthPx;
    
    const localClearanceCorners = [
        { x: -cornerClearancePx, y: -depthPx / 2 },
        { x: lenPx + cornerClearancePx, y: -depthPx / 2 },
        { x: lenPx + cornerClearancePx, y: depthPx / 2 },
        { x: -cornerClearancePx, y: depthPx / 2 }
    ];
    
    const cCorners = localClearanceCorners.map(pt => ({
        x: pt.x * cos - pt.y * sin + r.x,
        y: pt.x * sin + pt.y * cos + r.y
    }));
    
    const cXs = cCorners.map(c => c.x);
    const cYs = cCorners.map(c => c.y);
    
    const cxMinX = Math.min(...cXs);
    const cxMaxX = Math.max(...cXs);
    const cxMinY = Math.min(...cYs);
    const cxMaxY = Math.max(...cYs);
    
    let clearanceBox = { minX: cxMinX, maxX: cxMaxX, minY: cxMinY, maxY: cxMaxY };
    return { physical: physicalBox, clearance: clearanceBox };
}

function checkBoxesOverlap(b1, b2) {
    return !(b1.maxX < b2.minX || b1.minX > b2.maxX || b1.maxY < b2.minY || b1.minY > b2.maxY);
}

function ccw(A, B, C) {
    return (C.y - A.y) * (B.x - A.x) > (B.y - A.y) * (C.x - A.x);
}

function intersect(A, B, C, D) {
    return ccw(A, C, D) !== ccw(B, C, D) && ccw(A, B, C) !== ccw(A, B, D);
}

function isPointInPolygon(point, vs) {
    let x = point[0], y = point[1];
    let inside = false;
    for (let i = 0, j = vs.length - 1; i < vs.length; j = i++) {
        let xi = vs[i].x, yi = vs[i].y;
        let xj = vs[j].x, yj = vs[j].y;
        let intersect = ((yi > y) != (yj > y)) && (x < (xj - xi) * (y - yi) / (yj - yi) + xi);
        if (intersect) inside = !inside;
    }
    return inside;
}

function checkRackValidPlacement(newRack, ignoreRack = null) {
    let boxes = getRackBoxes(newRack);
    
    // 🌟 조건 0: 가로/세로 랙 공존 시 코너 데드존(Dead Zone) 침범 검사
    if (typeof getCornerDeadZones === 'function') {
        const deadZones = getCornerDeadZones();
        for (let dz of deadZones) {
            const b1 = boxes.physical;
            const b2 = dz.bounds;
            const overlapX = Math.min(b1.maxX, b2.maxX) - Math.max(b1.minX, b2.minX);
            const overlapY = Math.min(b1.maxY, b2.maxY) - Math.max(b1.minY, b2.minY);
            if (overlapX > 3 && overlapY > 3) {
                return false;
            }
        }
    }
    
    // 조건 A: 겹침 및 지게차 통로(Aisle, 최소 2,800mm) 간격 검사
    const AISLE_DIST_PX = 2800 * currentScale;
    
    for (let r of racks) {
        if (r === newRack || r === ignoreRack) continue; // 자기 자신과의 비교 제외
        
        let existBoxes = getRackBoxes(r);
        const b1 = boxes.physical;
        const b2 = existBoxes.physical;

        // 1. 물리적 박스 겹침 충돌 검사
        const overlapX = Math.min(b1.maxX, b2.maxX) - Math.max(b1.minX, b2.minX);
        const overlapY = Math.min(b1.maxY, b2.maxY) - Math.max(b1.minY, b2.minY);
        if (overlapX > 0 && overlapY > 0) {
            return false;
        }

        // 2. 랙 방향 판별 (회전각 기반)
        const angle1 = (typeof getRackAngle === 'function') ? getRackAngle(newRack) : (newRack.angle || 0);
        const angle2 = (typeof getRackAngle === 'function') ? getRackAngle(r) : (r.angle || 0);
        const isHoriz1 = Math.abs(Math.sin(angle1)) < 0.5;
        const isHoriz2 = Math.abs(Math.sin(angle2)) < 0.5;

        if (isHoriz1 === isHoriz2) {
            // [Case A] 평행한 랙 사이의 지게차 통로(Aisle) 간격 검사
            if (isHoriz1) {
                // 둘 다 가로 랙: X축(길이) 구간이 마주보고 겹칠 때 Y축 통로 거리 검사
                if (overlapX > 10) {
                    const distY = Math.max(b1.minY - b2.maxY, b2.minY - b1.maxY);
                    if (distY >= 0 && distY < AISLE_DIST_PX - 5) {
                        return false;
                    }
                }
            } else {
                // 둘 다 세로 랙: Y축(길이) 구간이 마주보고 겹칠 때 X축 통로 거리 검사
                if (overlapY > 10) {
                    const distX = Math.max(b1.minX - b2.maxX, b2.minX - b1.maxX);
                    if (distX >= 0 && distX < AISLE_DIST_PX - 5) {
                        return false;
                    }
                }
            }
        } else {
            // [Case B] 🌟 단수-복수(가로-세로) 랙이 만나는 T자 직교 지점 지게차 통로 검사
            // 가로 랙(hBox)과 세로 랙(vBox) 식별
            const hBox = isHoriz1 ? b1 : b2;
            const vBox = isHoriz1 ? b2 : b1;

            // 세로 랙의 X 범위가 가로 랙의 X 범위(작업면/통로) 쪽으로 마주볼 때
            const hOverlap = Math.min(hBox.maxX, vBox.maxX) - Math.max(hBox.minX, vBox.minX);
            if (hOverlap > 10) {
                // 세로 랙 끝단과 가로 랙 몸통 사이의 Y축 거리 (지게차가 다닐 통로 공간)
                const distY = Math.max(vBox.minY - hBox.maxY, hBox.minY - vBox.maxY);
                if (distY >= 0 && distY < AISLE_DIST_PX - 5) {
                    return false; // 지게차 통로(2,800mm) 미확보 시 설치 불가(빨간색 오류)!
                }
            }

            // 반대로 가로 랙의 Y 범위가 세로 랙의 Y 범위(작업면/통로) 쪽으로 마주볼 때
            const vOverlap = Math.min(hBox.maxY, vBox.maxY) - Math.max(hBox.minY, vBox.minY);
            if (vOverlap > 10) {
                // 가로 랙 끝단과 세로 랙 몸통 사이의 X축 거리
                const distX = Math.max(hBox.minX - vBox.maxX, vBox.minX - hBox.maxX);
                if (distX >= 0 && distX < AISLE_DIST_PX - 5) {
                    return false; // 지게차 통로(2,800mm) 미확보 시 설치 불가(빨간색 오류)!
                }
            }
        }
    }
    
    // 조건 C: 장애물 직접 간섭 검사
    for (let obs of obstacles) {
        const isDoorLike = obs.type === 'door' || obs.type === 'shutter';
        if (!isDoorLike) {
            let w = (obs.width || 500) * currentScale;
            let h = (obs.height || 500) * currentScale;
            let clearance = 99 * currentScale; 
            
            let obsBox = {
                minX: obs.x - w/2 - clearance,
                maxX: obs.x + w/2 + clearance,
                minY: obs.y - h/2 - clearance,
                maxY: obs.y + h/2 + clearance
            };
            
            if (checkBoxesOverlap(boxes.physical, obsBox)) {
                return false;
            }
        } else {
            let w = (obs.length || 2000) * currentScale;
            let clearance = 2800 * currentScale;
            let isHorizDoor = Math.abs(Math.cos(obs.angle)) > 0.5;
            
            let obsBox;
            if (isHorizDoor) {
                obsBox = {
                    minX: obs.x - w/2,
                    maxX: obs.x + w/2,
                    minY: obs.y - clearance,
                    maxY: obs.y + clearance
                };
            } else {
                obsBox = {
                    minX: obs.x - clearance,
                    maxX: obs.x + clearance,
                    minY: obs.y - w/2,
                    maxY: obs.y + w/2
                };
            }
            
            if (checkBoxesOverlap(boxes.physical, obsBox)) {
                return false;
            }
        }
    }
    
    // 조건 D: 창고 다각형 경계 내부 검사 (회전된 실제 4개 모서리 검사)
    const corners = getRackRotatedCorners(newRack);
    for (let c of corners) {
        if (!isPointInPolygon([c.x, c.y], points)) {
            return false;
        }
    }
    
    return true;
}

// 랙 그룹 렌더링 헬퍼 함수
// CAD 스타일 양방향 화살표 치수선 그리기 헬퍼 함수
function drawDimensionArrow(ctx, x1, y1, x2, y2, text, color = '#f87171', isTextVertical = false, isFlipped = false) {
    ctx.save();
    ctx.strokeStyle = color;
    ctx.fillStyle = color;
    ctx.lineWidth = 1;
    
    // 1. 주선 그리기
    ctx.beginPath();
    ctx.moveTo(x1, y1);
    ctx.lineTo(x2, y2);
    ctx.stroke();
    
    // 2. 화살표 머리 그리기 (양끝)
    const angle = Math.atan2(y2 - y1, x2 - x1);
    const arrowSize = 6;
    
    // 시작점 화살표
    ctx.beginPath();
    ctx.moveTo(x1, y1);
    ctx.lineTo(x1 + arrowSize * Math.cos(angle + Math.PI/6), y1 + arrowSize * Math.sin(angle + Math.PI/6));
    ctx.lineTo(x1 + arrowSize * Math.cos(angle - Math.PI/6), y1 + arrowSize * Math.sin(angle - Math.PI/6));
    ctx.closePath();
    ctx.fill();
    
    // 끝점 화살표
    ctx.beginPath();
    ctx.moveTo(x2, y2);
    ctx.lineTo(x2 - arrowSize * Math.cos(angle + Math.PI/6), y2 - arrowSize * Math.sin(angle + Math.PI/6));
    ctx.lineTo(x2 - arrowSize * Math.cos(angle - Math.PI/6), y2 - arrowSize * Math.sin(angle - Math.PI/6));
    ctx.closePath();
    ctx.fill();
    
    // 3. 치수 텍스트 그리기 (배경 상자 포함)
    const midX = (x1 + x2) / 2;
    const midY = (y1 + y2) / 2;
    
    ctx.font = 'bold 11px Inter';
    const textWidth = ctx.measureText(text).width + 6;
    
    ctx.save();
    ctx.translate(midX, midY);
    if (isFlipped) {
        ctx.rotate(Math.PI); // 180도 뒤집힘 보정 (항상 정방향 가독성 유지)
    }
    if (isTextVertical) {
        ctx.rotate(-Math.PI / 2);
    }
    
    ctx.fillStyle = 'rgba(15, 23, 42, 0.95)'; // 글자 배경
    ctx.fillRect(-textWidth/2, -7, textWidth, 14);
    
    ctx.fillStyle = color;
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    ctx.fillText(text, 0, 0);
    
    ctx.restore();
    ctx.restore();
}

// 작은 연결 빔 규격 계산 헬퍼 함수 (2585→1385, 2785→1485, 2985→1585)
function getSmallBeamLength(beamLength) {
    if (beamLength === 2585) return 1385;
    if (beamLength === 2785) return 1485;
    if (beamLength === 2985) return 1585;
    return Math.max(1000, (beamLength || 2585) - 1200);
}

// 📐 파렛트랙 총 길이 정밀 산출 (기둥 85mm 전수 반영: N개 베이 = N+1개 기둥)
function getRackTotalLengthMm(r) {
    if (!r) return 0;
    const reg = (r.independent || 0) + (r.connected || 0);
    const sm = r.smallConnected || 0;
    const totalSpans = reg + sm;
    if (totalSpans <= 0) return 0;
    const beamLength = r.beamLength || 2585;
    const smallBeamLength = r.smallBeamLength || getSmallBeamLength(beamLength);
    // 총 외경 길이 = (일반 빔 수 * 빔길이) + (작은 빔 수 * 작은빔길이) + (총 기둥 수(스팬+1) * 기둥폭(85mm))
    return (reg * beamLength) + (sm * smallBeamLength) + ((totalSpans + 1) * 85);
}

function getRackTotalLengthPx(r) {
    return getRackTotalLengthMm(r) * (window.currentScale || currentScale || 1);
}
window.getRackTotalLengthMm = getRackTotalLengthMm;
window.getRackTotalLengthPx = getRackTotalLengthPx;

// 랙 그룹 단일 그리기 (복렬 기본 2,200mm 완벽 반영)
function drawRackGroup(r, isPreview = false, rackIdx = -1) {
    const singleDepth = r.rackDepth || 1000;
    const holderSize = r.holderSize || 200; // 가운데 홀더 기본 200mm (300, 500 가변)
    const isDouble = r.isDouble || false;

    const singleDepthPx = singleDepth * currentScale;
    const holderPx = (isDouble ? holderSize : 0) * currentScale;
    const depthPx = isDouble ? (singleDepthPx * 2 + holderPx) : singleDepthPx;

    const colSizePx = 85 * currentScale; // 기둥 규격 85mm
    const beamPx = r.beamLength * currentScale;
    const smallBeamLength = r.smallBeamLength || getSmallBeamLength(r.beamLength);
    const smallBeamPx = smallBeamLength * currentScale;

    // 🌟 랙 전체 길이(totalLengthPx)를 기둥 수(totalSpans + 1)와 빔 길이에 맞춰 100% 정밀 동기화
    r.totalLengthPx = getRackTotalLengthPx(r);
    
    ctx.save();
    ctx.translate(r.x, r.y);
    
    // 회전 처리
    ctx.rotate(getRackAngle(r));

    if (isPreview) {
        ctx.globalAlpha = 0.45; // 투명처리된 복사된 랙 스타일 적용
    }

    const isLight = isCanvasLightMode();
    const isRackInvalid = (r.isValid === false);

    // 1. 배경 채우기
    if (isPreview) {
        ctx.fillStyle = isLight
            ? (r.isValid ? 'rgba(2, 132, 199, 0.15)' : 'rgba(239, 68, 68, 0.25)')
            : (r.isValid ? 'rgba(56, 189, 248, 0.25)' : 'rgba(239, 68, 68, 0.35)');
    } else {
        ctx.fillStyle = isRackInvalid
            ? (isLight ? 'rgba(239, 68, 68, 0.28)' : 'rgba(239, 68, 68, 0.35)')
            : (isLight ? 'rgba(241, 245, 249, 0.85)' : 'rgba(15, 23, 42, 0.6)'); // 반투명 배경
    }
    ctx.fillRect(0, -depthPx/2, r.totalLengthPx, depthPx);

    // 2. 가로 로드빔 평행선 그리기 (이중 선)
    ctx.strokeStyle = isRackInvalid ? '#ef4444' : (isPreview ? (isLight ? '#0284c7' : '#38bdf8') : (isLight ? '#0284c7' : '#0ea5e9'));
    ctx.lineWidth = isRackInvalid ? 2.5 : 2;
    
    if (isDouble) {
        // 상부 랙 & 하부 랙 각각 독립된 1000mm 깊이로 렌더링, 가운데 holderPx 간격 확보
        const topRackY = -depthPx / 2;
        const botRackY = depthPx / 2 - singleDepthPx;
        ctx.strokeRect(0, topRackY, r.totalLengthPx, singleDepthPx);
        ctx.strokeRect(0, botRackY, r.totalLengthPx, singleDepthPx);
    } else {
        ctx.strokeRect(0, -depthPx/2, r.totalLengthPx, depthPx);
    }

    // 3. 기둥 및 고정 홀더 렌더링
    const defaultPostFill = isLight ? '#0284c7' : '#0ea5e9';
    const previewPostFill = isLight ? 'rgba(2, 132, 199, 0.6)' : 'rgba(56, 189, 248, 0.7)';
    ctx.fillStyle = isRackInvalid ? '#ef4444' : (isPreview ? previewPostFill : defaultPostFill);
    ctx.strokeStyle = isRackInvalid ? '#b91c1c' : (isLight ? '#0f172a' : '#fff');
    ctx.lineWidth = 1;
    
    const regularSpans = (r.independent || 0) + (r.connected || 0);
    const smallSpans = r.smallConnected || 0;
    const totalSpans = regularSpans + smallSpans;
    let colX = 0;
    
    for (let i = 0; i <= totalSpans; i++) {
        if (isDouble) {
            const topRackY = -depthPx / 2;
            const botRackY = depthPx / 2 - singleDepthPx;
            
            // 상부 랙 기둥
            ctx.fillRect(colX, topRackY, colSizePx, colSizePx);
            ctx.strokeRect(colX, topRackY, colSizePx, colSizePx);
            ctx.fillRect(colX, topRackY + singleDepthPx - colSizePx, colSizePx, colSizePx);
            ctx.strokeRect(colX, topRackY + singleDepthPx - colSizePx, colSizePx, colSizePx);
            
            // 하부 랙 기둥
            ctx.fillRect(colX, botRackY, colSizePx, colSizePx);
            ctx.strokeRect(colX, botRackY, colSizePx, colSizePx);
            ctx.fillRect(colX, botRackY + singleDepthPx - colSizePx, colSizePx, colSizePx);
            ctx.strokeRect(colX, botRackY + singleDepthPx - colSizePx, colSizePx, colSizePx);

            // 🔗 복렬 고정 홀더(Spacer) 브라켓 시각화
            ctx.fillStyle = '#fbbf24'; // 황금색 홀더
            ctx.strokeStyle = '#f59e0b';
            ctx.lineWidth = 1;
            ctx.fillRect(colX + 1, -holderPx/2, colSizePx - 2, holderPx);
            ctx.strokeRect(colX + 1, -holderPx/2, colSizePx - 2, holderPx);
            ctx.fillStyle = isRackInvalid ? '#ef4444' : (isPreview ? 'rgba(56, 189, 248, 0.7)' : '#0ea5e9');
            ctx.strokeStyle = isRackInvalid ? '#b91c1c' : '#fff';
        } else {
            ctx.fillRect(colX, -depthPx/2, colSizePx, colSizePx);
            ctx.strokeRect(colX, -depthPx/2, colSizePx, colSizePx);
            ctx.fillRect(colX, depthPx/2 - colSizePx, colSizePx, colSizePx);
            ctx.strokeRect(colX, depthPx/2 - colSizePx, colSizePx, colSizePx);
        }
        
        if (i < totalSpans) {
            const isSmall = i >= regularSpans;
            colX += colSizePx + (isSmall ? smallBeamPx : beamPx);
        }
    }

    // 4. CAD 치수선(Dimension Lines) 렌더링
    if (!isPreview || isMovingRack) {
        // 회전된 랙의 치수 텍스트 가독성 보정
        const currentAngle = typeof getRackAngle === 'function' ? getRackAngle(r) : (r.angle || 0);
        const normAngle = ((currentAngle % (Math.PI * 2)) + Math.PI * 2) % (Math.PI * 2);
        const isFlipped = (normAngle > Math.PI / 2 + 0.01 && normAngle < 3 * Math.PI / 2 + 0.01);

        // 연장 보조선 (Extension Lines)
        ctx.strokeStyle = 'rgba(248, 113, 113, 0.6)';
        ctx.lineWidth = 1;
        ctx.beginPath();
        // 세로 연장선
        ctx.moveTo(0, -depthPx/2); ctx.lineTo(-25, -depthPx/2);
        ctx.moveTo(0, depthPx/2); ctx.lineTo(-25, depthPx/2);
        ctx.stroke();

        // 4-1. 세로 깊이 치수선 (단열 1,000 / 복렬 기본 2,200 등)
        const totalDepthMm = isDouble ? (singleDepth * 2 + holderSize) : singleDepth;
        drawDimensionArrow(ctx, -20, -depthPx/2, -20, depthPx/2, totalDepthMm.toLocaleString(), '#f87171', true, isFlipped);

        // 4-2. 가로 정규 로드빔 내측 치수선 (2,585 등) - 첫 번째 베이 하단에 표기
        const dimY = isDouble ? 0 : (depthPx/2 + 15);
        
        if (regularSpans > 0) {
            drawDimensionArrow(ctx, colSizePx, dimY, colSizePx + beamPx, dimY, Math.round(r.beamLength).toLocaleString(), '#f87171', false, isFlipped);
        }

        // 4-3. 작은연결 치수선 (1,385 등) 표기 (작은연결이 있을 때 항상 명확히 표시)
        if (r.smallConnected > 0) {
            const smallStartPx = regularSpans * (colSizePx + beamPx) + colSizePx;
            drawDimensionArrow(ctx, smallStartPx, dimY, smallStartPx + smallBeamPx, dimY, Math.round(smallBeamLength).toLocaleString(), '#38bdf8', false, isFlipped);
        }

        // 5. 랙 끝단 드래그 확장 핸들 (인터랙티브 모드에 따라 활성화된 모드의 아이콘 하나만 렌더링)
        const handleX = r.totalLengthPx;
        const mode = window.activeInteractMode || null;
        
        if (mode && typeof drawRotateHandleIcon === 'function') {
            ctx.save();
            if (mode === 'rotate' || mode === 'rotate-ccw') {
                drawRotateHandleIcon(ctx, handleX, 0);
            } else if (mode === 'extend') {
                drawExtendHandleIcon(ctx, handleX, 0);
            } else if (mode === 'copy') {
                drawCopyHandleIcon(ctx, handleX, 0);
            }
            ctx.restore();
        }
        
        // 호버 아웃라인 (선택/오류 상태)
        if (!isPreview) {
            if (!r.isValid) {
                ctx.strokeStyle = 'rgba(239, 68, 68, 0.8)';
                ctx.lineWidth = 4 / cameraZoom;
                ctx.strokeRect(0, -depthPx/2, r.totalLengthPx, depthPx);
            } else if (r === window.hoveredRack || r === window.selectedRack) {
                ctx.strokeStyle = 'rgba(56, 189, 248, 0.8)';
                ctx.lineWidth = 3 / cameraZoom;
                ctx.strokeRect(0, -depthPx/2, r.totalLengthPx, depthPx);
            }
        }
    }

    // 6. 바이패스 라벨 및 호버 프리뷰 렌더링
    if (r.bypassBays && Array.isArray(r.bypassBays)) {
        const rowCount = isDouble ? 2 : 1;
        const singleDepthPx = (r.rackDepth || 1000) * currentScale;
        const holderPx = (isDouble ? (r.holderSize || 200) : 0) * currentScale;
        const depthPx = isDouble ? (singleDepthPx * 2 + holderPx) : singleDepthPx;
        
        let colX = 0;
        for (let i = 0; i < totalSpans; i++) {
            const isSmall = i >= regularSpans;
            const curBeamPx = isSmall ? smallBeamPx : beamPx;
            const bayCenterX = colX + colSizePx + curBeamPx / 2;
            
            for (let row = 0; row < rowCount; row++) {
                const hover = window._hoveredBypass;
                const isHovered = (hover && hover.rackIndex === rackIdx && hover.row === row && hover.spanIndex === i);
                const isActualBypass = r.bypassBays[row] && r.bypassBays[row][i];
                
                if (isActualBypass || isHovered) {
                    // 각 row별 세로 중심 Y 좌표 계산
                    let labelY = 0;
                    if (isDouble) {
                        const topRackY = -depthPx / 2;
                        const botRackY = depthPx / 2 - singleDepthPx;
                        labelY = (row === 0) ? (topRackY + singleDepthPx / 2) : (botRackY + singleDepthPx / 2);
                    } else {
                        labelY = 0;
                    }
                    
                    ctx.save();
                    ctx.fillStyle = isActualBypass ? '#ef4444' : 'rgba(244, 63, 94, 0.4)';
                    ctx.font = 'bold 10px Inter, sans-serif';
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    
                    const labelText = "Bypass";
                    const textWidth = ctx.measureText(labelText).width + 6;
                    
                    if (!isActualBypass) {
                        // 호버 프리뷰는 빨간색 점선 테두리로 깜빡이게 그려줌
                        ctx.strokeStyle = '#f43f5e';
                        ctx.lineWidth = 1;
                        ctx.setLineDash([2, 2]);
                        ctx.strokeRect(bayCenterX - textWidth / 2, labelY - 8, textWidth, 16);
                    } else {
                        ctx.fillStyle = isCanvasLightMode() ? 'rgba(255, 255, 255, 0.95)' : 'rgba(15, 23, 42, 0.85)';
                        ctx.fillRect(bayCenterX - textWidth / 2, labelY - 8, textWidth, 16);
                    }
                    
                    ctx.fillStyle = isActualBypass ? '#ef4444' : '#f43f5e';
                    
                    // 글자가 뒤집히지 않도록 보정
                    const worldAngle = getRackAngle(r);
                    const isUpsideDown = Math.cos(worldAngle) < -0.1;
                    
                    ctx.save();
                    ctx.translate(bayCenterX, labelY);
                    if (isUpsideDown) {
                        ctx.rotate(Math.PI);
                    }
                    ctx.fillText(labelText, 0, 0);
                    ctx.restore();
                    
                    ctx.restore();
                }
                // --- 커스텀 단수(베이 레벨) 및 높이 라벨 렌더링 ---
                const globalLevelsInput = document.getElementById('rack-levels');
                const globalLevels = globalLevelsInput ? (parseInt(globalLevelsInput.value) || 3) : 3;
                const defaultRackLevel = r.levels || globalLevels;
                
                let hasBayLevels = r.bayLevels && Array.isArray(r.bayLevels) && r.bayLevels.length > row && Array.isArray(r.bayLevels[row]);
                let hasBayHeights = r.bayHeights && Array.isArray(r.bayHeights) && r.bayHeights.length > row && Array.isArray(r.bayHeights[row]);
                
                let bayLevel = (hasBayLevels && r.bayLevels[row][i] !== undefined) ? r.bayLevels[row][i] : defaultRackLevel;
                let bayHeight = (hasBayHeights && r.bayHeights[row][i]) ? r.bayHeights[row][i] : null;

                if ((bayLevel !== defaultRackLevel || bayHeight !== null) && !isActualBypass) {
                    let labelY = 0;
                    if (isDouble) {
                        const topRackY = -depthPx / 2;
                        const botRackY = depthPx / 2 - singleDepthPx;
                        labelY = (row === 0) ? (topRackY + singleDepthPx / 2) : (botRackY + singleDepthPx / 2);
                    } else {
                        labelY = 0;
                    }

                    ctx.save();
                    ctx.font = 'bold 12px Inter, sans-serif';
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    
                    let levelLabelText = bayLevel + "단";
                    if (bayHeight) levelLabelText += ` (${bayHeight})`;
                    
                    const textWidth = ctx.measureText(levelLabelText).width + 8;
                    
                    ctx.fillStyle = isCanvasLightMode() ? 'rgba(255, 255, 255, 0.95)' : 'rgba(15, 23, 42, 0.85)'; // 배경
                    ctx.fillRect(bayCenterX - textWidth / 2, labelY - 10, textWidth, 20);
                    
                    ctx.fillStyle = isCanvasLightMode() ? '#7e22ce' : '#a855f7'; // 텍스트 컬러 (보라색)
                    
                    // 글자가 뒤집히지 않도록 보정
                    const worldAngle = getRackAngle(r);
                    const isUpsideDown = Math.cos(worldAngle) < -0.1;
                    
                    ctx.save();
                    ctx.translate(bayCenterX, labelY);
                    if (isUpsideDown) {
                        ctx.rotate(Math.PI);
                    }
                    ctx.fillText(levelLabelText, 0, 0);
                    ctx.restore();
                    
                    ctx.restore();
                }
            }
            colX += colSizePx + curBeamPx;
        }
    }

    ctx.restore();
}

// 전체 화면 그리기 루프
function draw() {
    applyAiRackSpecs();
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    drawGridBackground();
    ctx.save();
    ctx.scale(cameraZoom, cameraZoom);

    if (points.length === 0) {
        ctx.restore();
        return;
    }

    // 1. 선 그리기
    ctx.beginPath();
    ctx.moveTo(points[0].x, points[0].y);
    for (let i = 1; i < points.length; i++) {
        ctx.lineTo(points[i].x, points[i].y);
    }
    
    if (!isPolygonClosed() && mousePos) {
        ctx.lineTo(mousePos.x, mousePos.y);
    }
    
    ctx.strokeStyle = '#38bdf8';
    ctx.lineWidth = 3;
    ctx.stroke();

    // 2. 점 그리기
    points.forEach((p, index) => {
        if (isPolygonClosed() && index === points.length - 1) return;
        ctx.beginPath();
        ctx.arc(p.x, p.y, 6, 0, Math.PI * 2);
        ctx.fillStyle = '#0ea5e9';
        ctx.fill();
        ctx.strokeStyle = '#fff';
        ctx.lineWidth = 2;
        ctx.stroke();
    });

    // 3. 선분 번호 표시
    for (let i = 0; i < points.length - 1; i++) {
        const p1 = points[i];
        const p2 = points[i+1];
        const midX = (p1.x + p2.x) / 2;
        const midY = (p1.y + p2.y) / 2;
        
        ctx.fillStyle = '#fff';
        ctx.font = 'bold 16px Inter';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        
        ctx.fillStyle = isCanvasLightMode() ? 'rgba(255, 255, 255, 0.95)' : 'rgba(15, 23, 42, 0.8)';
        ctx.fillRect(midX - 12, midY - 12, 24, 24);
        
        ctx.fillStyle = isCanvasLightMode() ? '#0284c7' : '#38bdf8';
        ctx.fillText(i + 1, midX, midY);
    }

// 🌟 코너 설치불가 데드존(Dead Zone) 계산 유틸 함수
// 가로 랙과 세로 랙이 실제로 만나는 코너 지점에만 rackDepth × rackDepth 크기의 데드존 생성
function getCornerDeadZones() {
    if (!isPolygonClosed() || points.length < 4) return [];
    
    // 검사 대상 랙 목록 (배치된 랙 + 드래그 중인 미리보기 랙)
    let checkRacks = (typeof racks !== 'undefined' && Array.isArray(racks)) ? [...racks] : [];
    if (typeof currentRackPreview !== 'undefined' && currentRackPreview) {
        checkRacks.push(currentRackPreview);
    }
    if (checkRacks.length === 0 || currentScale <= 0) return [];

    const rackDepthMm = (checkRacks.length > 0 && checkRacks[0].rackDepth) 
        ? checkRacks[0].rackDepth 
        : (typeof getRackDepth === 'function' ? getRackDepth() : 1000);
    const depthPx = rackDepthMm * currentScale;

    const numCorners = points.length - 1;
    let deadZones = [];

    // 각 랙의 바운딩 박스 및 각도/방향(가로/세로) 정보 사전 계산
    const rackInfoList = checkRacks.map(r => {
        let b = null;
        if (typeof getRackBoxes === 'function') {
            const boxes = getRackBoxes(r);
            if (boxes && boxes.physical) b = boxes.physical;
        }
        if (!b) {
            const rLen = r.totalLengthPx || (1000 * currentScale);
            b = { minX: r.x - 20, maxX: r.x + rLen + 20, minY: r.y - depthPx, maxY: r.y + depthPx };
        }
        const angle = (typeof getRackAngle === 'function') ? getRackAngle(r) : (r.angle || 0);
        // 회전각 기반 가로/세로 판별: 0도 또는 180도(수평) vs 90도 또는 270도(수직)
        const isHoriz = Math.abs(Math.sin(angle)) < 0.5;
        const isVert = Math.abs(Math.cos(angle)) < 0.5;
        return { r, b, isHoriz, isVert };
    });

    for (let i = 0; i < numCorners; i++) {
        const P = points[i];
        const P_prev = points[(i - 1 + numCorners) % numCorners];
        const P_next = points[(i + 1) % numCorners];

        const v1 = { x: P_prev.x - P.x, y: P_prev.y - P.y };
        const v2 = { x: P_next.x - P.x, y: P_next.y - P.y };
        const len1 = Math.hypot(v1.x, v1.y);
        const len2 = Math.hypot(v2.x, v2.y);
        if (len1 < 1e-3 || len2 < 1e-3) continue;

        const u1 = { x: v1.x / len1, y: v1.y / len1 };
        const u2 = { x: v2.x / len2, y: v2.y / len2 };

        // 4개 꼭짓점 (창고 내부 방향으로 형성되는 직사각형/평행사변형)
        const c0 = { x: P.x, y: P.y };
        const c1 = { x: P.x + u1.x * depthPx, y: P.y + u1.y * depthPx };
        const c2 = { x: P.x + (u1.x + u2.x) * depthPx, y: P.y + (u1.y + u2.y) * depthPx };
        const c3 = { x: P.x + u2.x * depthPx, y: P.y + u2.y * depthPx };

        const center = {
            x: (c0.x + c1.x + c2.x + c3.x) / 4,
            y: (c0.y + c1.y + c2.y + c3.y) / 4
        };

        // 창고 내부 검사
        if (!isPointInPolygon([center.x, center.y], points)) continue;

        const minX = Math.min(c0.x, c1.x, c2.x, c3.x);
        const maxX = Math.max(c0.x, c1.x, c2.x, c3.x);
        const minY = Math.min(c0.y, c1.y, c2.y, c3.y);
        const maxY = Math.max(c0.y, c1.y, c2.y, c3.y);

        // 🌟 두목님 핵심 원칙: "가로 세로 랙이 만나는 지점(해당 코너 부근)에만 데드존이 되어야 해"
        // 코너 P 부근(지게차 통로폭 AST 이내)에 가로 랙과 세로 랙이 동시에 존재하는지 검사
        const forkliftAisleMm = (function() {
            let el = document.getElementById('chat-forklift-ast') || document.getElementById('forklift-ast');
            if (el && el.value) {
                let v = parseInt(el.value, 10);
                if (!isNaN(v) && v > 0) return v;
            }
            return 2800; // 기본 지게차 통로폭 2,800mm
        })();
        const searchDistPx = forkliftAisleMm * currentScale;
        let hasNearHoriz = false;
        let hasNearVert = false;

        for (let info of rackInfoList) {
            const b = info.b;
            const dx = Math.max(0, b.minX - P.x, P.x - b.maxX);
            const dy = Math.max(0, b.minY - P.y, P.y - b.maxY);
            const dist = Math.hypot(dx, dy);

            if (dist <= searchDistPx) {
                if (info.isHoriz) hasNearHoriz = true;
                if (info.isVert) hasNearVert = true;
            }
        }

        // 가로 랙과 세로 랙이 "동시에" 이 코너에 모였을 때만 데드존 활성화!
        if (hasNearHoriz && hasNearVert) {
            deadZones.push({
                cornerIndex: i,
                point: P,
                depthMm: rackDepthMm,
                depthPx: depthPx,
                poly: [c0, c1, c2, c3],
                center: center,
                bounds: { minX, maxX, minY, maxY }
            });
        }
    }
    return deadZones;
}
window.getCornerDeadZones = getCornerDeadZones;

    // 3.5. 코너 Dead Zone 표시 (가로/세로 랙이 만나는 지점에만 표시)
    const deadZones = getCornerDeadZones();
    if (deadZones.length > 0) {
        ctx.save();
        deadZones.forEach(dz => {
            const poly = dz.poly;
            ctx.beginPath();
            ctx.moveTo(poly[0].x, poly[0].y);
            ctx.lineTo(poly[1].x, poly[1].y);
            ctx.lineTo(poly[2].x, poly[2].y);
            ctx.lineTo(poly[3].x, poly[3].y);
            ctx.closePath();

            // 창고 내부 연한 앰버/황금색 배경
            ctx.fillStyle = 'rgba(245, 158, 11, 0.12)';
            ctx.fill();

            // 점선 테두리
            ctx.strokeStyle = '#f59e0b';
            ctx.lineWidth = 1.5;
            ctx.setLineDash([4, 4]);
            ctx.stroke();

            // 코너 대각선 엑스(X) 표시
            ctx.beginPath();
            ctx.moveTo(poly[0].x, poly[0].y);
            ctx.lineTo(poly[2].x, poly[2].y);
            ctx.moveTo(poly[1].x, poly[1].y);
            ctx.lineTo(poly[3].x, poly[3].y);
            ctx.strokeStyle = 'rgba(245, 158, 11, 0.25)';
            ctx.lineWidth = 1;
            ctx.stroke();
            // 🌟 데드존 텍스트는 완전히 제거됨
        });
        ctx.restore();
    }

    // 4. 파렛트랙 유효성 실시간 재평가 및 그룹 그리기
    racks.forEach((r, idx) => {
        if (typeof checkRackValidPlacement === 'function') {
            r.isValid = checkRackValidPlacement(r, r);
        }
        drawRackGroup(r, false, idx);
    });

    // 4.5. 랙 연장 중 장애물/지게차통로 간섭으로 확장 차단 시 시각적 경고 뱃지 렌더링
    if (isExtendingRack && window.isExtendBlockedByObstacle && extendingRackIndex >= 0 && extendingRackIndex < racks.length) {
        const blkRack = racks[extendingRackIndex];
        const tail = (typeof getRackTailPos === 'function') ? getRackTailPos(blkRack) : null;
        if (tail) {
            ctx.save();
            ctx.font = 'bold 12px Inter, sans-serif';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            
            const badgeText = '⛔ 확장 한계 (장애물/지게차 통로 간섭)';
            const textW = ctx.measureText(badgeText).width + 18;
            const badgeY = tail.y - 26;
            
            // 붉은색 글래스모피즘 뱃지 배경
            ctx.fillStyle = 'rgba(239, 68, 68, 0.95)';
            ctx.shadowColor = 'rgba(0, 0, 0, 0.5)';
            ctx.shadowBlur = 8;
            ctx.beginPath();
            if (ctx.roundRect) {
                ctx.roundRect(tail.x - textW / 2, badgeY - 13, textW, 26, 6);
            } else {
                ctx.rect(tail.x - textW / 2, badgeY - 13, textW, 26);
            }
            ctx.fill();
            
            ctx.shadowBlur = 0;
            ctx.strokeStyle = '#ffffff';
            ctx.lineWidth = 1.2;
            ctx.stroke();
            
            ctx.fillStyle = '#ffffff';
            ctx.fillText(badgeText, tail.x, badgeY);
            ctx.restore();
        }
    }

    // 5. 드래그 중인 파렛트랙 미리보기 그리기
    if ((isDrawingRack || isMovingRack) && currentRackPreview) {
        drawRackGroup(currentRackPreview, true, -1);
    }

    // 6. 장애물 그리기
    obstacles.forEach(obs => {
        const isDoorLike = obs.type === 'door' || obs.type === 'shutter';
        if (!isDoorLike) {
            let w = (obs.width || 500) * currentScale;
            let h = (obs.height || 500) * currentScale;
            if (w <= 0 || h <= 0) {
                w = 15;
                h = 15;
            }
            const px = obs.x - w / 2;
            const py = obs.y - h / 2;
            
            // 타입별 색상 설정
            let fillColor = 'rgba(239, 68, 68, 0.4)'; 
            let strokeColor = '#ef4444';
            let nameColor = '#fca5a5';
            
            if (obs.type === 'machine') {
                fillColor = 'rgba(156, 163, 175, 0.4)'; 
                strokeColor = '#9ca3af';
                nameColor = '#d1d5db';
            } else if (obs.type === 'hydrant') {
                fillColor = 'rgba(239, 68, 68, 0.6)'; 
                strokeColor = '#ef4444';
                nameColor = '#fca5a5';
            } else if (obs.type === 'panel') {
                fillColor = 'rgba(234, 179, 8, 0.4)'; 
                strokeColor = '#eab308';
                nameColor = '#fef08a';
            } else if (obs.type === 'forbidden') {
                fillColor = 'rgba(220, 38, 38, 0.5)'; 
                strokeColor = '#dc2626';
                nameColor = '#fca5a5';
            }
            
            ctx.fillStyle = fillColor;
            ctx.fillRect(px, py, w, h);
            
            ctx.beginPath();
            ctx.rect(px, py, w, h);
            ctx.strokeStyle = strokeColor;
            ctx.lineWidth = 2;
            ctx.stroke();

            ctx.beginPath();
            ctx.moveTo(px, py);
            ctx.lineTo(px + w, py + h);
            ctx.moveTo(px + w, py);
            ctx.lineTo(px, py + h);
            ctx.strokeStyle = strokeColor;
            ctx.lineWidth = 1;
            ctx.stroke();
            
            ctx.fillStyle = nameColor;
            ctx.font = '10px Inter';
            ctx.textAlign = 'center';
            ctx.fillText(obs.name, obs.x, obs.y - h / 2 - 5);
            ctx.fillText(`${obs.width}x${obs.height}`, obs.x, obs.y + h / 2 + 12);
            
        } else {
            let visualLength = 40; 
            let realOffsetMsg = "";
            let thickness = 6;
            
            if (obs.edgeIndex !== undefined && obs.edgeIndex !== -1 && edgeLengths[obs.edgeIndex] && obs.edgeIndex < points.length - 1) {
                const realWallLength = edgeLengths[obs.edgeIndex];
                if (realWallLength > 0) {
                    const ratio = obs.visualEdgeLength / realWallLength;
                    visualLength = Math.max(obs.length * ratio, 10); 
                    
                    const p1 = points[obs.edgeIndex];
                    const p2 = points[obs.edgeIndex + 1];
                    const isHorizWall = Math.abs(p1.y - p2.y) < Math.abs(p1.x - p2.x);

                    if (isHorizWall) {
                        // 수평 벽면: 시각적 진짜 좌측(X 최소값)과 우측(X 최대값) 코너 기준 거리 계산
                        const leftCornerX = Math.min(p1.x, p2.x);
                        const edgePixelLen = Math.abs(p1.x - p2.x) || obs.visualEdgeLength || 1;
                        const distFromLeftMm = Math.round((Math.abs(obs.x - leftCornerX) / edgePixelLen) * realWallLength);
                        const distFromRightMm = Math.max(0, realWallLength - distFromLeftMm);

                        if (distFromLeftMm <= distFromRightMm) {
                            realOffsetMsg = `(좌측 ${distFromLeftMm.toLocaleString()}mm)`;
                        } else {
                            realOffsetMsg = `(우측 ${distFromRightMm.toLocaleString()}mm)`;
                        }
                    } else {
                        // 수직 벽면: 시각적 진짜 상단(Y 최소값)과 하단(Y 최대값) 코너 기준 거리 계산
                        const topCornerY = Math.min(p1.y, p2.y);
                        const edgePixelLen = Math.abs(p1.y - p2.y) || obs.visualEdgeLength || 1;
                        const distFromTopMm = Math.round((Math.abs(obs.y - topCornerY) / edgePixelLen) * realWallLength);
                        const distFromBottomMm = Math.max(0, realWallLength - distFromTopMm);

                        if (distFromTopMm <= distFromBottomMm) {
                            realOffsetMsg = `(상단 ${distFromTopMm.toLocaleString()}mm)`;
                        } else {
                            realOffsetMsg = `(하단 ${distFromBottomMm.toLocaleString()}mm)`;
                        }
                    }
                }
            }

            ctx.save();
            ctx.translate(obs.x, obs.y);
            ctx.rotate(obs.angle);
            
            if (obs.type === 'shutter') {
                ctx.fillStyle = '#10b981'; 
                ctx.fillRect(-visualLength / 2, -thickness / 2, visualLength, thickness);
            } else {
                ctx.fillStyle = '#f59e0b'; 
                ctx.fillRect(-visualLength / 2, -thickness / 2, visualLength, thickness);
            }
            ctx.restore();
            
            ctx.fillStyle = obs.type === 'shutter' ? '#34d399' : '#fcd34d';
            ctx.font = '10px Inter';
            ctx.textAlign = 'center';
            ctx.fillText(obs.name, obs.x, obs.y - 15);
            ctx.fillText(`${obs.length}mm`, obs.x, obs.y + 15);
            if (realOffsetMsg) {
                ctx.fillStyle = '#a78bfa'; 
                ctx.fillText(realOffsetMsg, obs.x, obs.y + 30);
            }
        }
    });

    // 4. 랙 간 통로 거리 표시
    drawDimensions();
    
    ctx.restore();
}

// 랙 간 거리(Dimension) 실시간 표시
function drawDimensions() {
    ctx.font = 'bold 12px Inter';
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    
    // 수평 배치    // 출입문과 마주보는 랙 간의 거리 표시
    window._placedDimensionLabels = [];
    obstacles.forEach(obs => {
        if (obs.type === 'door') {
            let isHorizDoor = Math.abs(Math.cos(obs.angle)) > 0.5;
            let doorW = (obs.length || 2000) * currentScale;
            
            let doorMinX = obs.x - doorW/2;
            let doorMaxX = obs.x + doorW/2;
            let doorMinY = obs.y - doorW/2;
            let doorMaxY = obs.y + doorW/2;
            
            let closestRack = null;
            let minDist = Infinity;

            const activeDoorRacks = [...racks];
            if ((isDrawingRack || isMovingRack) && currentRackPreview) activeDoorRacks.push(currentRackPreview);

            activeDoorRacks.forEach(r => {
                let singleDepth = r.rackDepth || 1000;
                let holderSize = r.holderSize || 200;
                let totalDepthMm = r.isDouble ? (singleDepth * 2 + holderSize) : singleDepth;
                let rDepthPx = totalDepthMm * currentScale;
                
                let rMinX, rMaxX, rMinY, rMaxY;
                if (r.isHoriz) {
                    rMinX = r.dir > 0 ? r.x : r.x - r.totalLengthPx;
                    rMaxX = r.dir > 0 ? r.x + r.totalLengthPx : r.x;
                    rMinY = r.y - rDepthPx/2;
                    rMaxY = r.y + rDepthPx/2;
                } else {
                    rMinX = r.x - rDepthPx/2;
                    rMaxX = r.x + rDepthPx/2;
                    rMinY = r.dir > 0 ? r.y : r.y - r.totalLengthPx;
                    rMaxY = r.dir > 0 ? r.y + r.totalLengthPx : r.y;
                }
                
                if (isHorizDoor) {
                    let overlapMinX = Math.max(doorMinX, rMinX);
                    let overlapMaxX = Math.min(doorMaxX, rMaxX);
                    if (overlapMaxX > overlapMinX) {
                        let distToTop = Math.abs(obs.y - rMinY);
                        let distToBottom = Math.abs(obs.y - rMaxY);
                        let dist = Math.min(distToTop, distToBottom);
                        let rackY = obs.y < r.y ? rMinY : rMaxY;
                        
                        if (dist < minDist) {
                            minDist = dist;
                            closestRack = { y: rackY, overlapMin: overlapMinX, overlapMax: overlapMaxX, isHoriz: true };
                        }
                    }
                } else {
                    let overlapMinY = Math.max(doorMinY, rMinY);
                    let overlapMaxY = Math.min(doorMaxY, rMaxY);
                    if (overlapMaxY > overlapMinY) {
                        let distToLeft = Math.abs(obs.x - rMinX);
                        let distToRight = Math.abs(obs.x - rMaxX);
                        let dist = Math.min(distToLeft, distToRight);
                        let rackX = obs.x < r.x ? rMinX : rMaxX;
                        
                        if (dist < minDist) {
                            minDist = dist;
                            closestRack = { x: rackX, overlapMin: overlapMinY, overlapMax: overlapMaxY, isHoriz: false };
                        }
                    }
                }
            });
            
            if (closestRack) {
                let distMm = Math.round(minDist / currentScale);
                if (distMm > 10) {
                    if (closestRack.isHoriz) {
                        let cx = (closestRack.overlapMin + closestRack.overlapMax) / 2;
                        let y1 = obs.y;
                        let y2 = closestRack.y;
                        
                        ctx.beginPath();
                        ctx.moveTo(cx, y1);
                        ctx.lineTo(cx, y2);
                        ctx.strokeStyle = 'rgba(251, 146, 60, 0.7)';
                        ctx.lineWidth = 1;
                        ctx.stroke();
                        
                        let dir = y2 > y1 ? 1 : -1;
                        ctx.beginPath(); ctx.moveTo(cx-3, y2-5*dir); ctx.lineTo(cx, y2); ctx.lineTo(cx+3, y2-5*dir); ctx.stroke();
                        
                        ctx.fillStyle = isCanvasLightMode() ? 'rgba(255, 255, 255, 0.95)' : 'rgba(15, 23, 42, 0.8)';
                        let textW = ctx.measureText(`${distMm}mm`).width + 10;
                        let midY = (y1+y2)/2;
                        ctx.fillRect(cx - textW/2, midY - 10, textW, 20);
                        ctx.fillStyle = isCanvasLightMode() ? '#ea580c' : '#fdba74';
                        ctx.fillText(`${distMm}mm`, cx, midY);

                        window._placedDimensionLabels.push({ cx: cx, cy: midY, hw: textW/2 + 10, hh: 15 });
                    } else {
                        let cy = (closestRack.overlapMin + closestRack.overlapMax) / 2;
                        let x1 = obs.x;
                        let x2 = closestRack.x;
                        
                        ctx.beginPath();
                        ctx.moveTo(x1, cy);
                        ctx.lineTo(x2, cy);
                        ctx.strokeStyle = 'rgba(251, 146, 60, 0.7)';
                        ctx.lineWidth = 1;
                        ctx.stroke();
                        
                        let dir = x2 > x1 ? 1 : -1;
                        ctx.beginPath(); ctx.moveTo(x2-5*dir, cy-3); ctx.lineTo(x2, cy); ctx.lineTo(x2-5*dir, cy+3); ctx.stroke();
                        
                        ctx.fillStyle = isCanvasLightMode() ? 'rgba(255, 255, 255, 0.95)' : 'rgba(15, 23, 42, 0.8)';
                        let textW = ctx.measureText(`${distMm}mm`).width + 10;
                        let midX = (x1+x2)/2;
                        ctx.fillRect(midX - textW/2, cy - 10, textW, 20);
                        ctx.fillStyle = isCanvasLightMode() ? '#ea580c' : '#fdba74';
                        ctx.fillText(`${distMm}mm`, midX, cy);

                        window._placedDimensionLabels.push({ cx: midX, cy: cy, hw: textW/2 + 10, hh: 15 });
                    }
                }
            }
        }
    });
    // 5. 선택된 랙과 벽 사이의 거리 표시
    drawWallToRackDimensions();

    ctx.restore();
}

// 랙과 벽/장애물 사이의 거리(Dimension) 실시간 표시
function drawWallToRackDimensions() {
    if (points.length < 2) return;

    // ── 레이 vs 선분 교차 ──
    function segIntersect(px, py, dx, dy, ax, ay, bx, by) {
        let vx = bx - ax, vy = by - ay;
        let cross = vx * dy - vy * dx;
        if (Math.abs(cross) < 1e-6) return Infinity;
        let ex = px - ax, ey = py - ay;
        let t = (vy * ex - vx * ey) / cross;
        let s = (dy * ex - dx * ey) / cross;
        if (t > 1e-4 && s >= 0 && s <= 1) return t;
        return Infinity;
    }

    // ── 레이 vs AABB 교차 ──
    function aabbIntersect(px, py, dx, dy, rx, ry, rw, rh) {
        let best = Infinity;
        [[rx, ry, rx+rw, ry], [rx+rw, ry, rx+rw, ry+rh],
         [rx+rw, ry+rh, rx, ry+rh], [rx, ry+rh, rx, ry]].forEach(s => {
            let t = segIntersect(px, py, dx, dy, s[0], s[1], s[2], s[3]);
            if (t < best) best = t;
        });
        return best;
    }

    // ── 레이 vs 회전된 랙 교차 ──
    function rackIntersect(px, py, dx, dy, targetRack) {
        const corners = getRackRotatedCorners(targetRack);
        let best = Infinity;
        for (let i = 0; i < 4; i++) {
            let c1 = corners[i], c2 = corners[(i + 1) % 4];
            let t = segIntersect(px, py, dx, dy, c1.x, c1.y, c2.x, c2.y);
            if (t < best) best = t;
        }
        return best;
    }

    // ── 레이 캐스팅: 벽 + 장애물 + 타 랙 ──
    function rayIntersectAll(px, py, dx, dy, selfRack) {
        let minDist = Infinity;
        let hitType = 0; // 0=wall, 1=obstacle, 2=rack

        // 1) 벽
        for (let i = 0; i < points.length; i++) {
            let p1 = points[i], p2 = points[(i + 1) % points.length];
            if (i === points.length - 1 && !isPolygonClosed()) continue;
            let t = segIntersect(px, py, dx, dy, p1.x, p1.y, p2.x, p2.y);
            if (t < minDist) { minDist = t; hitType = 0; }
        }

        // 2) 장애물
        if (typeof obstacles !== 'undefined') {
            obstacles.forEach(obs => {
                const isDoorLike = obs.type === 'door' || obs.type === 'shutter';
                let t;
                if (!isDoorLike) {
                    let ow = (obs.width || 500) * currentScale, oh = (obs.height || 500) * currentScale;
                    t = aabbIntersect(px, py, dx, dy, obs.x - ow/2, obs.y - oh/2, ow, oh);
                } else {
                    let obsLen = (obs.length || 2000) * currentScale;
                    let cos = Math.cos(obs.angle), sin = Math.sin(obs.angle);
                    t = segIntersect(px, py, dx, dy,
                        obs.x - cos * obsLen/2, obs.y - sin * obsLen/2,
                        obs.x + cos * obsLen/2, obs.y + sin * obsLen/2);
                }
                if (t < minDist) { minDist = t; hitType = 1; }
            });
        }

        // 3) 다른 랙 (회전 각도 정밀 반영)
        if (typeof racks !== 'undefined') {
            racks.forEach(otherR => {
                if (otherR === selfRack) return;
                let t = rackIntersect(px, py, dx, dy, otherR);
                if (t < minDist) { minDist = t; hitType = 2; }
            });
        }

        if (minDist === Infinity || minDist > 80000) return null;
        return {
            x: px + dx * minDist, y: py + dy * minDist,
            dist: minDist,
            hitType
        };
    }

    let racksToCheck = [...racks];
    if ((window.isDrawingRack || isMovingRack) && currentRackPreview) {
        racksToCheck.push(currentRackPreview);
    }

    // ── 1단계: 세그먼트 수집 (회전 각도 지원) ──
    let dimSegments = [];

    racksToCheck.forEach(r => {
        let isFocused = (r === window.selectedRack || r === window.hoveredRack || r === currentRackPreview || (isMovingRack && r === currentRackPreview));
        
        let singleDepth = r.rackDepth || 1000;
        let holderSize = r.holderSize || 200;
        let totalDepthMm = r.isDouble ? (singleDepth * 2 + holderSize) : singleDepth;
        let dep = totalDepthMm * currentScale;
        
        let len = r.totalLengthPx;
        let angle = getRackAngle(r);

        // 길이 방향 단위벡터 & 법선(깊이) 단위벡터
        let lx = Math.cos(angle), ly = Math.sin(angle);
        let nx = -Math.sin(angle), ny = Math.cos(angle);

        // 랙 중심점
        let rcx = r.x + lx * (len / 2);
        let rcy = r.y + ly * (len / 2);

        // 4방향 발사 원점 및 방향 (회전된 랙 면에서 수직/평행하게 발사)
        const rays = [
            // 1. 위쪽 면 (법선 - 방향)
            { px: rcx - nx * (dep / 2), py: rcy - ny * (dep / 2), dx: -nx, dy: -ny, dir: 'N1' },
            // 2. 아래쪽 면 (법선 + 방향)
            { px: rcx + nx * (dep / 2), py: rcy + ny * (dep / 2), dx: nx,  dy: ny,  dir: 'N2' },
            // 3. 머리쪽 면 (길이 - 방향)
            { px: r.x, py: r.y, dx: -lx, dy: -ly, dir: 'L1' },
            // 4. 꼬리쪽 면 (길이 + 방향)
            { px: r.x + lx * len, py: r.y + ly * len, dx: lx,  dy: ly,  dir: 'L2' }
        ];

        // 🌟 이동/선택 중인 랙은 양 끝단(머리/꼬리)에서도 법선 레이 발사하여 장애물/벽면과의 정렬 치수선 다중 표시
        if (isFocused && len > 50) {
            rays.push({ px: r.x - nx * (dep / 2), py: r.y - ny * (dep / 2), dx: -nx, dy: -ny, dir: 'N1_head' });
            rays.push({ px: r.x + lx * len - nx * (dep / 2), py: r.y + ly * len - nx * (dep / 2), dx: -nx, dy: -ny, dir: 'N1_tail' });
            rays.push({ px: r.x + nx * (dep / 2), py: r.y + ny * (dep / 2), dx: nx, dy: ny, dir: 'N2_head' });
            rays.push({ px: r.x + lx * len + nx * (dep / 2), py: r.y + ly * len + nx * (dep / 2), dx: nx, dy: ny, dir: 'N2_tail' });
        }

        rays.forEach(ray => {
            let hit = rayIntersectAll(ray.px, ray.py, ray.dx, ray.dy, r);
            if (!hit) return;

            let distMm = Math.round(hit.dist / currentScale);
            let isObstacleHit = (hit.hitType > 0);

            // 표시 조건: 포커스 또는 2000mm 초과 (너무 먼 18m 초과는 도면 난잡 방지)
            if (!isFocused && !isObstacleHit && (distMm <= 2000 || distMm > 18000)) return;

            // 선분 정규화: 양 끝점을 정렬하여 마주보는 반대 방향 레이(A->B vs B->A)를 동일하게 병합
            let minX = Math.min(ray.px, hit.x), maxX = Math.max(ray.px, hit.x);
            let minY = Math.min(ray.py, hit.y), maxY = Math.max(ray.py, hit.y);
            let midX = (ray.px + hit.x) / 2, midY = (ray.py + hit.y) / 2;

            let isVert = Math.abs(ray.dy) > Math.abs(ray.dx) * 1.5;
            let isHoriz = Math.abs(ray.dx) > Math.abs(ray.dy) * 1.5;

            let groupKey;
            if (isVert) {
                // 수직선: X좌표와 Y범위로 그룹화
                groupKey = `V_${Math.round(midX / 60)}_${Math.round(minY / 60)}_${Math.round(maxY / 60)}`;
            } else if (isHoriz) {
                // 수평선: Y좌표와 X범위로 그룹화
                groupKey = `H_${Math.round(midY / 60)}_${Math.round(minX / 60)}_${Math.round(maxX / 60)}`;
            } else {
                // 사선: 중심 좌표와 기울기 버킷으로 그룹화
                let slope = Math.round(Math.atan2(hit.y - ray.py, hit.x - ray.px) * 180 / Math.PI / 10);
                groupKey = `S_${Math.round(midX / 60)}_${Math.round(midY / 60)}_${slope}`;
            }

            dimSegments.push({
                x1: ray.px, y1: ray.py,
                x2: hit.x,  y2: hit.y,
                distMm,
                isFocused,
                isObstacleHit,
                isVert,
                isHoriz,
                angle: Math.atan2(hit.y - ray.py, hit.x - ray.px),
                groupKey,
                hitType: hit.hitType,
                rayDx: ray.dx,
                rayDy: ray.dy,
                hitX: hit.x,
                hitY: hit.y
            });
        });
    });

    // ── 2단계: 그룹별 중복 제거 ──
    let groups = {};
    dimSegments.forEach(seg => {
        if (!groups[seg.groupKey]) groups[seg.groupKey] = [];
        groups[seg.groupKey].push(seg);
    });

    let finalSegments = [];
    Object.values(groups).forEach(group => {
        // 우선순위 정렬: 포커스 > 장애물/랙 > 벽
        group.sort((a, b) => {
            let pa = (a.isFocused ? 4 : 0) + (a.isObstacleHit ? 2 : 0);
            let pb = (b.isFocused ? 4 : 0) + (b.isObstacleHit ? 2 : 0);
            return pb - pa;
        });
        finalSegments.push(group[0]);
    });

    // ── 3단계: 렌더링 (통합 충돌 감지 & 회전 텍스트) ──
    ctx.save();
    ctx.font = 'bold 12px Inter';
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    ctx.setLineDash([4, 4]);

    finalSegments.sort((a, b) => {
        let pa = (a.isFocused ? 4 : 0) + (a.isObstacleHit ? 2 : 0);
        let pb = (b.isFocused ? 4 : 0) + (b.isObstacleHit ? 2 : 0);
        return pb - pa;
    });

    // 기존 출입문 치수선 등의 라벨 위치와도 충돌 방지
    let placedLabels = [...(window._placedDimensionLabels || [])];

    finalSegments.forEach(seg => {
        let label = `${seg.distMm}mm`;
        let textW = ctx.measureText(label).width + 10;
        let textH = 20;

        // 벽면에 닿는 흰색 치수선(외곽 여백)은 벽면 선분과 평행하게 회전 & 외곽 배치
        let isWallMargin = (seg.hitType === 0 && !seg.isFocused && !seg.isObstacleHit);
        let cx, cy, textRot;

        if (isWallMargin && seg.rayDx !== undefined && seg.rayDy !== undefined) {
            // 벽면 선분 방향에 맞춰 텍스트 회전:
            // - 가로 벽면 (수직 레이): 텍스트 가로 표기 (0도)
            // - 세로 벽면 (수평 레이): 텍스트 세로 표기 (-90도)
            if (seg.isVert) {
                textRot = 0;
                let offsetDist = textH / 2 + 6;
                cx = seg.hitX;
                cy = seg.hitY + seg.rayDy * offsetDist;
            } else if (seg.isHoriz) {
                textRot = -Math.PI / 2;
                let offsetDist = textH / 2 + 6;
                cx = seg.hitX + seg.rayDx * offsetDist;
                cy = seg.hitY;
            } else {
                textRot = seg.angle + Math.PI / 2;
                if (Math.abs(textRot) > Math.PI / 2) textRot += Math.PI;
                let offsetDist = textH / 2 + 6;
                cx = seg.hitX + seg.rayDx * offsetDist;
                cy = seg.hitY + seg.rayDy * offsetDist;
            }
        } else {
            cx = (seg.x1 + seg.x2) / 2;
            cy = (seg.y1 + seg.y2) / 2;
            textRot = 0;
            if (seg.isVert) {
                textRot = -Math.PI / 2;
            } else if (!seg.isHoriz) {
                textRot = seg.angle;
                if (Math.abs(textRot) > Math.PI / 2) textRot += Math.PI; // 뒤집힘 방지
            }
        }

        // 충돌 검사용 바운딩 박스 크기
        let isTextVertical = Math.abs(textRot) > 0.1;
        let hw = isTextVertical ? textH / 2 : textW / 2;
        let hh = isTextVertical ? textW / 2 : textH / 2;

        // 충돌 검사 (여유 간격 확보)
        let overlaps = placedLabels.some(pl =>
            Math.abs(cx - pl.cx) < (hw + pl.hw + 10) &&
            Math.abs(cy - pl.cy) < (hh + pl.hh + 8)
        );
        if (overlaps) return;

        placedLabels.push({ cx, cy, hw, hh });

        // 치수선 그리기
        ctx.beginPath();
        ctx.moveTo(seg.x1, seg.y1);
        ctx.lineTo(seg.x2, seg.y2);
        ctx.strokeStyle = seg.isFocused
            ? 'rgba(251, 146, 60, 0.95)'
            : seg.isObstacleHit
                ? 'rgba(251, 191, 36, 0.7)'
                : 'rgba(156, 163, 175, 0.5)';
        ctx.lineWidth = seg.isFocused ? 1.5 : 1;
        ctx.stroke();

        // 텍스트 그리기 (각도에 맞춰 회전)
        let textColor = seg.isFocused ? (isCanvasLightMode() ? '#ea580c' : '#fb923c')
                      : seg.isObstacleHit ? '#d97706'
                      : (isCanvasLightMode() ? '#1e293b' : '#e5e7eb');

        ctx.save();
        ctx.translate(cx, cy);
        ctx.rotate(textRot);

        ctx.fillStyle = isCanvasLightMode() ? 'rgba(255, 255, 255, 0.95)' : 'rgba(15, 23, 42, 0.85)';
        ctx.fillRect(-textW / 2, -textH / 2, textW, textH);
        ctx.fillStyle = textColor;
        ctx.fillText(label, 0, 0);

        ctx.restore();
    });

    ctx.restore();
}

// 랙 수량을 폼 및 상단 대시보드에 실시간 종합 업데이트
window.updateRackFormCounts = function() {
    let totalTieHolders = 0;
    let totalBays = 0;
    
    // 설치 단수 (기본 3단)
    const levelsInput = document.getElementById('rack-levels');
    const levels = levelsInput ? (parseInt(levelsInput.value) || 3) : 3;

    // 기본 높이 산출 로직
    const palletH = parseInt(document.getElementById('pallet-h')?.value) || 1000;
    let globalRackH = parseInt(document.getElementById('rack-height')?.value) || 0;
    if (globalRackH <= 0) {
        const rawH = (palletH * levels) + (levels * 200) + 300;
        globalRackH = Math.ceil(rawH / 500) * 500;
    }

    // 단수별 분류 카운트 맵 (커스텀 단수 분리용)
    const levelCounts = {}; // { ["3_4500"]: { level: 3, height: 4500, indep: 0, conn: 0, small: 0 } }
    const addCount = (lvl, hgt, type) => {
        let key = `${lvl}_${hgt}`;
        if (!levelCounts[key]) levelCounts[key] = { level: lvl, height: hgt, indep: 0, conn: 0, small: 0 };
        levelCounts[key][type]++;
    };

    let bypassRegularBays = 0;
    let bypassSmallBays = 0;
    
    let totalPalletsAcc = 0;

    racks.forEach(r => {
        let reg = (r.independent || 0) + (r.connected || 0);
        let sm = r.smallConnected || 0;
        let spans = reg + sm;
        let rowCount = r.isDouble ? 2 : 1;

        // bypassBays 2차원 배열 동기화 및 방어 코드
        if (!r.bypassBays || !Array.isArray(r.bypassBays) || r.bypassBays.length < rowCount) {
            let oldBays = r.bypassBays || [];
            r.bypassBays = [ [], [] ];
            if (Array.isArray(oldBays[0])) r.bypassBays[0] = oldBays[0];
            if (Array.isArray(oldBays[1])) r.bypassBays[1] = oldBays[1];
        }

        // 각 row 별 spans 크기 동기화 및 개별 바이패스 분류 카운트
        for (let row = 0; row < rowCount; row++) {
            if (r.bypassBays[row].length !== spans) {
                let oldRowBays = r.bypassBays[row];
                r.bypassBays[row] = new Array(spans).fill(false);
                for(let k = 0; k < Math.min(oldRowBays.length, spans); k++) {
                    r.bypassBays[row][k] = oldRowBays[k];
                }
            }
            
            let hasBayLevels = r.bayLevels && Array.isArray(r.bayLevels) && r.bayLevels.length > row && Array.isArray(r.bayLevels[row]);
            let hasBayHeights = r.bayHeights && Array.isArray(r.bayHeights) && r.bayHeights.length > row && Array.isArray(r.bayHeights[row]);

            for (let j = 0; j < reg; j++) {
                let isBypass = r.bypassBays[row][j];
                let bayLevel = (hasBayLevels && r.bayLevels[row][j] !== undefined) ? r.bayLevels[row][j] : (r.levels || levels);
                let bayHeight = (hasBayHeights && r.bayHeights[row][j]) ? r.bayHeights[row][j] : globalRackH;
                let bayType = (j < (r.independent || 0)) ? 'indep' : 'conn';

                if (isBypass) {
                    bypassRegularBays += 1;
                    totalPalletsAcc += 2 * Math.max(1, bayLevel - 1);
                } else {
                    totalPalletsAcc += 2 * bayLevel;
                    addCount(bayLevel, bayHeight, bayType);
                }
            }
            for (let j = reg; j < reg + sm; j++) {
                let isBypass = r.bypassBays[row][j];
                let bayLevel = (hasBayLevels && r.bayLevels[row][j] !== undefined) ? r.bayLevels[row][j] : (r.levels || levels);
                let bayHeight = (hasBayHeights && r.bayHeights[row][j]) ? r.bayHeights[row][j] : globalRackH;
                
                if (isBypass) {
                    bypassSmallBays += 1;
                    totalPalletsAcc += 1 * Math.max(1, bayLevel - 1);
                } else {
                    totalPalletsAcc += 1 * bayLevel;
                    addCount(bayLevel, bayHeight, 'small');
                }
            }
        }

        if (r.isDouble) {
            // 복렬(복수) 랙 1세트: 단열 2라인 결합
            totalBays += spans * 2;
            // 고정 홀더(Spacer): 기둥 열 수(spans + 1) * 2개 (상/하 2개씩)
            if (spans > 0) {
                totalTieHolders += (spans + 1) * 2;
            }
        } else {
            // 단열 랙
            totalBays += spans;
        }
    });

    let totalBypass = bypassRegularBays + bypassSmallBays;
    
    // 기본 단수/높이의 수량 추출
    let baseKey = `${levels}_${globalRackH}`;
    let baseCounts = levelCounts[baseKey] || { indep: 0, conn: 0, small: 0 };
    let totalIndep = baseCounts.indep;
    let totalConn = baseCounts.conn;
    let totalSmallConn = baseCounts.small;
    
    // 글로벌에 커스텀 뱃지 정보 공유 (HTMX 렌더링용)
    window.rackCustomLevels = {};
    for (const [key, counts] of Object.entries(levelCounts)) {
        if (key !== baseKey) {
            window.rackCustomLevels[key] = counts;
        }
    }
    
    // 총 적재 파렛트 수량: 랙 단위로 누적된 수량 사용
    const totalPallets = totalPalletsAcc;
    
    // 1. 숨김 input 필드 업데이트 (폼 제출용) 및 HTMX 연동 실시간 이벤트 트리거
    const indepInput = document.getElementById('rack-independent');
    const connInput = document.getElementById('rack-connected');
    const smallConnInput = document.getElementById('rack-small-connected');
    const bypassInput = document.getElementById('rack-bypass');
    const tieHolderInput = document.getElementById('rack-tie-holders');
    const palletsInput = document.getElementById('rack-total-pallets');
    
    let changeTriggered = false;
    
    const updateVal = (el, val) => {
        if (!el) return;
        const oldVal = el.value;
        const newVal = val > 0 ? String(val) : '';
        if (oldVal !== newVal) {
            el.value = newVal;
            el.dispatchEvent(new Event('change', { bubbles: true }));
            el.dispatchEvent(new Event('input', { bubbles: true }));
            changeTriggered = true;
        }
    };
    
    updateVal(indepInput, totalIndep);
    updateVal(connInput, totalConn);
    updateVal(smallConnInput, totalSmallConn);
    updateVal(bypassInput, totalBypass);
    updateVal(tieHolderInput, totalTieHolders);
    updateVal(palletsInput, totalPallets);

    // 2. 좌측 4단계 카드 실시간 숫자 텍스트 표시
    const dIndep = document.getElementById('display-indep');
    const dConn = document.getElementById('display-conn');
    const dSmallConn = document.getElementById('display-small-conn');
    const dBypass = document.getElementById('display-bypass');
    const dHolders = document.getElementById('display-holders');
    const dPallets = document.getElementById('display-pallets');
    const dBaysBadge = document.getElementById('rack-total-bays-badge');
    
    if(dIndep) dIndep.innerHTML = `${totalIndep}<span class="small text-muted fs-7">대</span>`;
    if(dConn) dConn.innerHTML = `${totalConn}<span class="small text-muted fs-7">대</span>`;
    if(dSmallConn) dSmallConn.innerHTML = `${totalSmallConn}<span class="small text-muted fs-7">대</span>`;
    if(dBypass) dBypass.innerHTML = `${totalBypass}<span class="small text-muted fs-7">대</span>`;
    if(dHolders) dHolders.innerHTML = `${totalTieHolders}<span class="small text-muted fs-7">개</span>`;
    if(dPallets) dPallets.innerHTML = `${totalPallets.toLocaleString()}<span class="small text-muted fs-7">PLT</span>`;
    
    // 표준 파렛트랙 규격 산출: [빔길이(W) × 깊이(D) × 높이(H) , (단수-1)S 단수단]
    const currentPalletW = parseInt(document.getElementById('pallet-w')?.value) || 1100;
    const currentPalletD = parseInt(document.getElementById('pallet-d')?.value) || 1100;
    const beamLen = (racks.length > 0 && racks[0].beamLength) || (window.rackSpecs && window.rackSpecs.beamLength) || parseInt(document.getElementById('rack-beam-length')?.value) || (currentPalletW * 2) + 385;
    const rackD = (racks.length > 0 && racks[0].rackDepth) || (window.rackSpecs && window.rackSpecs.rackDepth) || parseInt(document.getElementById('rack-depth')?.value) || 1000;
    let rackH = globalRackH;
    const spanS = Math.max(1, levels - 1); 
    const specTagText = `${beamLen}×${rackD}×${rackH} (${spanS}S ${levels}단)`;
    
    let specDetailText = `규격: ${beamLen}(W) × ${rackD}(D) × ${rackH}(H) , ${spanS}S ${levels}단`;
    if (totalBypass > 0) {
        const bpS = Math.max(1, spanS - 1);
        const bpLevels = Math.max(1, levels - 1);
        specDetailText += `<br>규격: ${beamLen}(W) × ${rackD}(D) × ${rackH}(H) , ${bpS}S ${bpLevels}단 (바이패스 ${totalBypass}대)`;
    }
    if (window.rackCustomLevels) {
        for (const [key, counts] of Object.entries(window.rackCustomLevels)) {
            let clvl = counts.level;
            let cHeight = counts.height;
            let cSpanS = Math.max(1, clvl - 1);
            let totalCustom = counts.indep + counts.conn + counts.small;
            if (totalCustom > 0) {
                specDetailText += `<br>규격: ${beamLen}(W) × ${rackD}(D) × ${cHeight}(H) , ${cSpanS}S ${clvl}단 (커스텀 ${totalCustom}대)`;
            }
        }
    }

    if(dBaysBadge) dBaysBadge.innerText = `총 ${totalBays + totalBypass}칸 (${spanS}S ${levels}단)`;
    const dSpecDetail = document.getElementById('rack-spec-detail-badge');
    if(dSpecDetail) dSpecDetail.innerHTML = specDetailText;

    // 3. 캔버스 상단 실시간 대시보드 뱃지 바 업데이트 (줄바꿈 두 줄 포맷 지원)
    const summaryBadge = document.getElementById('canvas-summary-badge');
    if (summaryBadge) {
        // 현재 활성화된 층 구역명 표시
        const curFloor = (window.canvasFloors && window.canvasFloors[window.currentFloorIndex]) ? window.canvasFloors[window.currentFloorIndex] : null;
        const floorNameTag = curFloor ? `<span class="badge bg-info text-dark shadow-sm me-1" style="font-size:0.75rem; font-weight:700;"><i class="fa-solid fa-layer-group me-1"></i>${curFloor.name}</span>` : '';

        // 첫 번째 줄 빌드
        let html = `<div class="d-flex align-items-center gap-2 flex-wrap">
            ${floorNameTag}
            <span id="top-badge-spec" class="badge bg-primary text-white shadow-sm" style="font-size:0.75rem; font-weight:600; padding:4px 10px; letter-spacing:0.02em; cursor:pointer;" onclick="if(window.openRackSpecModal) window.openRackSpecModal()" title="클릭하여 랙 규격(가로빔/깊이/높이) 실시간 변경">
                <i class="fa-solid fa-pen-to-square me-1 opacity-75"></i>${specTagText}
            </span>
            <span class="text-secondary">|</span>
            <span>독립 <strong id="top-badge-indep" class="text-primary">${totalIndep}</strong>대</span>
            <span class="text-secondary">|</span>
            <span>연결 <strong id="top-badge-conn" class="text-primary">${totalConn}</strong>대</span>`;
        
        if (totalSmallConn > 0) {
            html += ` <span class="text-secondary">|</span> <span class="text-info">작은연결 <strong id="top-badge-small-conn" class="text-info">${totalSmallConn}</strong>대</span>`;
        }
        
        html += ` <span class="text-secondary">|</span> <span class="text-warning">🔗 <strong id="top-badge-holders" class="text-warning">${totalTieHolders}</strong>홀더</span>
            <span class="text-secondary">|</span>
            <span class="text-success">📦 <strong id="top-badge-pallets" class="text-success">${totalPallets.toLocaleString()}</strong> PLT</span>`;
            
        // 멀티 플로어인 경우 전체 층 종합 집계 미니 뱃지 표시
        if (window.canvasFloors && window.canvasFloors.length > 1 && typeof window.calculateFloorStats === 'function') {
            let grandBays = 0, grandPallets = 0;
            window.canvasFloors.forEach(f => {
                let st = window.calculateFloorStats(f);
                grandBays += st.bays;
                grandPallets += st.pallets;
            });
            html += ` <span class="text-secondary">|</span> <span class="badge bg-dark border border-secondary text-warning" style="font-size:0.72rem; padding:3px 8px;" title="1층~${window.canvasFloors.length}층 전체 합산 총계">
                <i class="fa-solid fa-building me-1"></i>전체 ${window.canvasFloors.length}개층 합계: 총 ${grandBays}대 / 📦 ${grandPallets.toLocaleString()} PLT
            </span>`;
        }
        html += `</div>`;
        
        // 두 번째 줄 빌드 (바이패스가 있을 때만 혹은 바이패스 모드 활성화 시)
        if (totalBypass > 0 || window.activeInteractMode === 'bypass') {
            const bpS = Math.max(1, spanS - 1);
            const bpLevels = Math.max(1, levels - 1);
            const bpSpec = `${beamLen}×${rackD}×${rackH} (${bpS}S ${bpLevels}단)`;
            
            let bypassText = totalBypass > 0 
                ? `연결 <strong id="top-badge-bypass" class="text-danger">${totalBypass}</strong>대 (바이패스)` 
                : `<span class="text-danger" style="font-size:0.7rem; font-weight:normal; letter-spacing:-0.5px;">바이패스 모드 (도면에서 랙 클릭)</span>`;
                
            html += `<div class="d-flex align-items-center gap-2 flex-wrap mt-1 pt-1 border-top border-secondary w-100" style="font-size:0.78rem;">
                <span class="badge bg-danger text-white" style="font-size:0.7rem; font-weight:600; padding:3px 6px;">${bpSpec}</span>
                <span class="text-danger fw-bold ms-2">${bypassText}</span>
            </div>`;
        }
        
        // 커스텀 단수 뱃지 추가
        if (window.rackCustomLevels) {
            for (const [key, counts] of Object.entries(window.rackCustomLevels)) {
                let clvl = counts.level;
                let cHeight = counts.height;
                let cSpanS = Math.max(1, clvl - 1);
                let cSpec = `${beamLen}×${rackD}×${cHeight} (${cSpanS}S ${clvl}단)`;
                let badgeParts = [];
                if (counts.indep > 0) badgeParts.push(`독립 <strong class="text-danger">${counts.indep}</strong>대`);
                if (counts.conn > 0) badgeParts.push(`연결 <strong class="text-danger">${counts.conn}</strong>대`);
                if (counts.small > 0) badgeParts.push(`작은연결 <strong class="text-danger">${counts.small}</strong>대`);
                
                if (badgeParts.length > 0) {
                    html += `<div class="d-flex align-items-center gap-2 flex-wrap mt-1 pt-1 border-top border-secondary w-100" style="font-size:0.78rem;">
                        <span class="badge bg-danger text-white" style="font-size:0.7rem; font-weight:600; padding:3px 6px;">${cSpec}</span>
                        <span class="text-danger fw-bold ms-2">${badgeParts.join(' <span class="text-secondary fw-normal">|</span> ')}</span>
                    </div>`;
                }
            }
        }
        
        summaryBadge.innerHTML = html;
        summaryBadge.classList.remove('d-none');
        summaryBadge.style.display = 'flex';
        summaryBadge.style.flexDirection = 'column';
        summaryBadge.style.alignItems = 'flex-start';
    }
};
window.updateRackCounts = window.updateRackFormCounts;

// 좌측 폼 영역에 장애물 설정 UI 동적 렌더링
window.renderObstacleInputs = function() {
    const container = document.getElementById('obstacle-inputs-container');
    if (!container) return;
    
    container.innerHTML = ''; 

    obstacles.forEach((obs, index) => {
        const itemDiv = document.createElement('div');
        itemDiv.className = 'd-flex flex-column p-2 rounded-2 mt-2 bg-white border shadow-sm';
        itemDiv.style.borderColor = '#cbd5e1';

        const isDoorLike = obs.type === 'door' || obs.type === 'shutter';
        const nameColor = isDoorLike ? 'text-primary' : 'text-danger';

        const headerHtml = `
            <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="${nameColor} fw-bold small">${obs.name}</span>
                <button class="btn btn-sm text-secondary border-0 p-0 px-1 fw-bold" style="font-size: 0.85rem;" onclick="deleteObstacle(${index})" title="삭제">✕</button>
            </div>
        `;

        if (isDoorLike) {
            let distVal = 10000;
            if (obs.edgeIndex !== undefined && obs.edgeIndex !== -1 && typeof edgeLengths !== 'undefined' && edgeLengths[obs.edgeIndex]) {
                const p1 = points[obs.edgeIndex];
                const p2 = points[obs.edgeIndex + 1];
                const realWallLength = edgeLengths[obs.edgeIndex];
                const isHorizWall = Math.abs(p1.y - p2.y) < Math.abs(p1.x - p2.x);
                if (isHorizWall) {
                    const leftCornerX = Math.min(p1.x, p2.x);
                    const edgePixelLen = Math.abs(p1.x - p2.x) || 1;
                    distVal = Math.round((Math.abs(obs.x - leftCornerX) / edgePixelLen) * realWallLength);
                } else {
                    const topCornerY = Math.min(p1.y, p2.y);
                    const edgePixelLen = Math.abs(p1.y - p2.y) || 1;
                    distVal = Math.round((Math.abs(obs.y - topCornerY) / edgePixelLen) * realWallLength);
                }
            } else if (obs.visualDistFromStart !== undefined) {
                distVal = obs.visualDistFromStart;
            }

            itemDiv.innerHTML = headerHtml + `
                <div class="d-flex gap-1 mt-1">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light text-dark fw-bold" style="font-size:0.7rem; padding: 2px 4px;">길이</span>
                        <input type="text" inputmode="numeric" pattern="[0-9]*" class="form-control bg-white text-dark small px-1" style="font-size:0.75rem;" value="${obs.length || ''}" placeholder="크기(mm)" onclick="this.select()" oninput="this.value=this.value.replace(/[^0-9]/g, ''); updateObstacleData(${index}, 'length', this.value)">
                    </div>
                    <div class="input-group input-group-sm" title="좌측 또는 상단 기준 띄움 거리(mm)">
                        <span class="input-group-text bg-light text-dark fw-bold" style="font-size:0.7rem; padding: 2px 4px;">위치</span>
                        <input type="text" inputmode="numeric" pattern="[0-9]*" class="form-control bg-white text-dark small px-1" style="font-size:0.75rem;" value="${distVal}" placeholder="여백(mm)" onclick="this.select()" oninput="this.value=this.value.replace(/[^0-9]/g, ''); updateObstaclePosition(${index}, this.value)">
                    </div>
                </div>
            `;
        } else {
            itemDiv.innerHTML = headerHtml + `
                <div class="d-flex gap-2">
                    <input type="text" inputmode="numeric" pattern="[0-9]*" class="form-control form-control-sm bg-white text-dark border-secondary small" value="${obs.width || ''}" placeholder="가로(mm)" onclick="this.select()" oninput="this.value=this.value.replace(/[^0-9]/g, ''); updateObstacleData(${index}, 'width', this.value)">
                    <input type="text" inputmode="numeric" pattern="[0-9]*" class="form-control form-control-sm bg-white text-dark border-secondary small" value="${obs.height || ''}" placeholder="세로(mm)" onclick="this.select()" oninput="this.value=this.value.replace(/[^0-9]/g, ''); updateObstacleData(${index}, 'height', this.value)">
                </div>
            `;
        }
        container.appendChild(itemDiv);
    });
};

window.updateObstacleData = function(index, field, value) {
    if (index >= 0 && index < obstacles.length) {
        obstacles[index][field] = parseInt(value) || 0;
        draw(); 
    }
};

window.updateObstaclePosition = function(index, distMm) {
    if (index < 0 || index >= obstacles.length) return;
    const obs = obstacles[index];
    if (obs.edgeIndex === undefined || obs.edgeIndex === -1) return;
    
    const p1 = points[obs.edgeIndex];
    const p2 = points[obs.edgeIndex + 1];
    if (!p1 || !p2) return;

    const realWallLength = typeof edgeLengths !== 'undefined' ? edgeLengths[obs.edgeIndex] : 0;
    if (!realWallLength) return;

    let targetMm = parseInt(distMm);
    if (isNaN(targetMm)) return;
    
    const isHorizWall = Math.abs(p1.y - p2.y) < Math.abs(p1.x - p2.x);
    let isP1Start = isHorizWall ? (p1.x < p2.x) : (p1.y < p2.y);
    
    let distFromP1 = isP1Start ? targetMm : (realWallLength - targetMm);
    
    let param = distFromP1 / realWallLength;
    if (param < 0) param = 0;
    if (param > 1) param = 1;
    
    obs.ratioOnEdge = param;
    obs.visualDistFromStart = param * realWallLength;
    
    obs.x = p1.x + param * (p2.x - p1.x);
    obs.y = p1.y + param * (p2.y - p1.y);
    
    draw();
};

window.deleteObstacle = function(index) {
    if (index >= 0 && index < obstacles.length) {
        obstacles.splice(index, 1);
        draw();
        renderObstacleInputs();
    }
};

resizeCanvas();

// AI가 전달한 rackSpecs를 바탕으로 racks 배열 자동 생성 (다중 배치 지원)
function applyAiRackSpecs() {
    if (!window.rackSpecs || currentScale <= 0 || points.length < 3) return;
    
    const spec = window.rackSpecs;
    window.rackSpecs = null; // 중복 실행 방지
    
    const layoutRacks = spec.layoutRacks || [];
    if (layoutRacks.length === 0) return;
    
    // 기존 랙 비우기
    racks = [];
    
    // 로드빔 두께 바(var) UI 정보 업데이트용 전역 변수 저장
    window.currentBeamThicknessBar = spec.beamThicknessBar || 125;
    
    layoutRacks.forEach(layout => {
        const edgeIndex = layout.edgeIndex;
        if (edgeIndex < 0 || edgeIndex >= points.length - 1) return;
        
        const p1 = points[edgeIndex];
        const p2 = points[edgeIndex + 1];
        
        // 벽면의 길이 및 각도 계산
        const wallLenPx = Math.hypot(p2.x - p1.x, p2.y - p1.y);
        const wallLenMm = wallLenPx / currentScale;
        const angle = Math.atan2(p2.y - p1.y, p2.x - p1.x);

        // 🌟 사선(대각선) 벽면 필터링: 수평/수직과 15도 이상 벗어난 사선 벽면은 가로/세로 랙 배치 시 벽을 뚫고 나가므로 제외
        const isPureHoriz = Math.abs(Math.sin(angle)) < 0.25; // 수평 벽면
        const isPureVert = Math.abs(Math.cos(angle)) < 0.25;  // 수직 벽면
        if (!isPureHoriz && !isPureVert) {
            return;
        }

        const isHoriz = isPureHoriz;
        let dir = 1;
        if (isHoriz) {
            dir = p2.x > p1.x ? 1 : -1;
        } else {
            dir = p2.y > p1.y ? 1 : -1;
        }
        
        const beamLength = spec.beamLength || 2585;
        const rackDepth = spec.rackDepth || 1000;
        const pillarClearance = getEdgeClearance(p1, p2); // 기둥/벽 기준 이격거리(px)
        const rackHalfDepthPx = (rackDepth / 2) * currentScale;
        
        // ── 벽 안쪽 방향(targetX/Y) 결정 ──
        const midX = (p1.x + p2.x) / 2;
        const midY = (p1.y + p2.y) / 2;
        let targetX, targetY;
        
        if (isHoriz) {
            const offsetPx = pillarClearance + rackHalfDepthPx;
            const plusY = midY + offsetPx;
            const minusY = midY - offsetPx;
            targetY = isPointInPolygon([midX, plusY], points) ? plusY : minusY;
            targetX = null;
        } else {
            const offsetPx = pillarClearance + rackHalfDepthPx;
            const plusX = midX + offsetPx;
            const minusX = midX - offsetPx;
            targetX = isPointInPolygon([plusX, midY], points) ? plusX : minusX;
            targetY = null;
        }
        
        // ── 코너 Dead Zone 적용 (모서리 겹침 방지) ──
        const cornerOffsetMm = rackDepth + 200; 
        const usableWallMm = wallLenMm - cornerOffsetMm * 2;
        
        let maxBays = layout.bays || 0;
        if (maxBays <= 0) {
            maxBays = Math.floor(usableWallMm / beamLength);
        } else {
            maxBays = Math.min(maxBays, Math.floor(usableWallMm / beamLength));
        }
        
        if (maxBays <= 0) return;
        
        const isDouble = layout.isDouble || false;
        const rackDepthPx = rackDepth * (isDouble ? 2 : 1) * currentScale;

        // 벽면에 장애물이 있는지 확인
        let edgeObs = [];
        obstacles.forEach(obs => {
            if (obs.type === 'door' || obs.type === 'shutter') {
                if (obs.edgeIndex === edgeIndex) edgeObs.push(obs);
            }
        });

        if (edgeObs.length > 0) {
            // ── 출입문 등 장애물이 있는 벽면: 양쪽 코너에서 시작 ──
            const obs = edgeObs[0];
            const obsDistFromP1 = obs.visualDistFromStart ? (obs.visualDistFromStart / currentScale) : (wallLenMm / 2);
            const obsDistFromP2 = wallLenMm - obsDistFromP1;
            const obsLen = obs.length || 2000;
            const marginMm = 200;

            // 1구간: p1 코너 -> 장애물
            const span1Mm = Math.max(0, obsDistFromP1 - obsLen/2 - marginMm - cornerOffsetMm);
            const bays1 = Math.floor(span1Mm / beamLength);
            if (bays1 > 0) {
                const groupLenMm = (bays1 * beamLength) + 85;
                const startMm = cornerOffsetMm;
                const rackX = isHoriz ? (p1.x + dir * startMm * currentScale) : targetX;
                const rackY = isHoriz ? targetY : (p1.y + dir * startMm * currentScale);
                const r1 = {
                    x: rackX, y: rackY,
                    isHoriz: isHoriz, dir: dir,
                    independent: 1, connected: bays1 - 1, smallConnected: 0,
                    beamLength: beamLength, smallBeamLength: getSmallBeamLength(beamLength),
                    totalLengthPx: groupLenMm * currentScale,
                    rackDepth: rackDepth, isDouble: isDouble, isValid: true
                };
                if (checkRackValidPlacement(r1)) {
                    racks.push(r1);
                }
            }

            // 2구간: p2 코너 -> 장애물
            const span2Mm = Math.max(0, obsDistFromP2 - obsLen/2 - marginMm - cornerOffsetMm);
            const bays2 = Math.floor(span2Mm / beamLength);
            if (bays2 > 0) {
                const groupLenMm = (bays2 * beamLength) + 85;
                const startMm = cornerOffsetMm;
                const oppDir = -dir;
                const rackX = isHoriz ? (p2.x + oppDir * startMm * currentScale) : targetX;
                const rackY = isHoriz ? targetY : (p2.y + oppDir * startMm * currentScale);
                const r2 = {
                    x: rackX, y: rackY,
                    isHoriz: isHoriz, dir: oppDir,
                    independent: 1, connected: bays2 - 1, smallConnected: 0,
                    beamLength: beamLength, smallBeamLength: getSmallBeamLength(beamLength),
                    totalLengthPx: groupLenMm * currentScale,
                    rackDepth: rackDepth, isDouble: isDouble, isValid: true
                };
                if (checkRackValidPlacement(r2)) {
                    racks.push(r2);
                }
            }

        } else {
            // ── 일반 벽면 (단일 연속 배치) ──
            let currentBaysInGroup = 0;
            let groupStartX = 0;
            let groupStartY = 0;
            
            for (let b = 0; b < maxBays; b++) {
                const startMm = cornerOffsetMm + b * beamLength;
                const endMm = startMm + beamLength + 85;
                
                let bayMinX, bayMaxX, bayMinY, bayMaxY;
                let centerOffsetX = 0;
                let centerOffsetY = 0;
                
                if (isHoriz) {
                    const bayStartX = p1.x + dir * (startMm * currentScale);
                    const bayEndX = p1.x + dir * (endMm * currentScale);
                    bayMinX = Math.min(bayStartX, bayEndX);
                    bayMaxX = Math.max(bayStartX, bayEndX);
                    bayMinY = targetY - rackDepthPx / 2;
                    bayMaxY = targetY + rackDepthPx / 2;
                    centerOffsetX = bayStartX;
                    centerOffsetY = targetY;
                } else {
                    const bayStartY = p1.y + dir * (startMm * currentScale);
                    const bayEndY = p1.y + dir * (endMm * currentScale);
                    bayMinX = targetX - rackDepthPx / 2;
                    bayMaxX = targetX + rackDepthPx / 2;
                    bayMinY = Math.min(bayStartY, bayEndY);
                    bayMaxY = Math.max(bayStartY, bayEndY);
                    centerOffsetX = targetX;
                    centerOffsetY = bayStartY;
                }
                
                // 4개 코너 전체 다각형 내부 포함 검사
                const margin = 20 * currentScale;
                const corners = [
                    [bayMinX + margin, bayMinY + margin],
                    [bayMaxX - margin, bayMinY + margin],
                    [bayMinX + margin, bayMaxY - margin],
                    [bayMaxX - margin, bayMaxY - margin]
                ];
                let allCornersInside = corners.every(pt => isPointInPolygon(pt, points));

                if (!allCornersInside) {
                    if (currentBaysInGroup > 0) {
                        const groupLenMm = (currentBaysInGroup * beamLength) + 85;
                        const newRack = {
                            x: groupStartX, y: groupStartY,
                            isHoriz: isHoriz, dir: dir,
                            independent: 1, connected: currentBaysInGroup - 1, smallConnected: 0,
                            beamLength: beamLength, smallBeamLength: getSmallBeamLength(beamLength),
                            totalLengthPx: groupLenMm * currentScale,
                            rackDepth: rackDepth, isDouble: isDouble, isValid: true
                        };
                        if (checkRackValidPlacement(newRack)) {
                            racks.push(newRack);
                        }
                        currentBaysInGroup = 0;
                    }
                    continue;
                }
                
                // 장애물 충돌 판정
                let isColliding = false;
                for (let obs of obstacles) {
                    if (obs.type === 'door' || obs.type === 'shutter') {
                        const obsLenPx = (obs.length || 2000) * currentScale;
                        const margin = 100 * currentScale;
                        if (isHoriz) {
                            if (!(bayMaxX < obs.x - obsLenPx/2 - margin || bayMinX > obs.x + obsLenPx/2 + margin)) {
                                isColliding = true; break;
                            }
                        } else {
                            if (!(bayMaxY < obs.y - obsLenPx/2 - margin || bayMinY > obs.y + obsLenPx/2 + margin)) {
                                isColliding = true; break;
                            }
                        }
                    } else {
                        const obsWPx = (obs.width || 500) * currentScale;
                        const obsHPx = (obs.height || 500) * currentScale;
                        const margin = 50 * currentScale;
                        if (!(bayMaxX - margin < obs.x - obsWPx/2 || bayMinX + margin > obs.x + obsWPx/2 || bayMaxY - margin < obs.y - obsHPx/2 || bayMinY + margin > obs.y + obsHPx/2)) {
                            isColliding = true; break;
                        }
                    }
                }
                
                if (!isColliding) {
                    if (currentBaysInGroup === 0) {
                        groupStartX = centerOffsetX;
                        groupStartY = centerOffsetY;
                    }
                    currentBaysInGroup++;
                } else {
                    if (currentBaysInGroup > 0) {
                        const groupLenMm = (currentBaysInGroup * beamLength) + 85;
                        const newRack = {
                            x: groupStartX, y: groupStartY,
                            isHoriz: isHoriz, dir: dir,
                            independent: 1, connected: currentBaysInGroup - 1, smallConnected: 0,
                            beamLength: beamLength, smallBeamLength: getSmallBeamLength(beamLength),
                            totalLengthPx: groupLenMm * currentScale,
                            rackDepth: rackDepth, isDouble: isDouble, isValid: true
                        };
                        if (checkRackValidPlacement(newRack)) {
                            racks.push(newRack);
                        }
                        currentBaysInGroup = 0;
                    }
                }
            }
            
            // 남은 공간 작은 연결 검사
            const smallBeamLength = getSmallBeamLength(beamLength);
            const remainingMm = usableWallMm - (maxBays * beamLength);
            let hasSmallBay = false;

            if (remainingMm >= smallBeamLength + 85) {
                const sStartMm = cornerOffsetMm + maxBays * beamLength;
                const sEndMm = sStartMm + smallBeamLength + 85;

                let sBayMinX, sBayMaxX, sBayMinY, sBayMaxY;
                if (isHoriz) {
                    const sStartX = p1.x + dir * (sStartMm * currentScale);
                    const sEndX = p1.x + dir * (sEndMm * currentScale);
                    sBayMinX = Math.min(sStartX, sEndX);
                    sBayMaxX = Math.max(sStartX, sEndX);
                    sBayMinY = targetY - rackDepthPx / 2;
                    sBayMaxY = targetY + rackDepthPx / 2;
                } else {
                    const sStartY = p1.y + dir * (sStartMm * currentScale);
                    const sEndY = p1.y + dir * (sEndMm * currentScale);
                    sBayMinX = targetX - rackDepthPx / 2;
                    sBayMaxX = targetX + rackDepthPx / 2;
                    sBayMinY = Math.min(sStartY, sEndY);
                    sBayMaxY = Math.max(sStartY, sEndY);
                }

                const sInside = isPointInPolygon([(sBayMinX + sBayMaxX) / 2, (sBayMinY + sBayMaxY) / 2], points);
                if (sInside) {
                    hasSmallBay = true;
                }
            }

            if (currentBaysInGroup > 0 || hasSmallBay) {
                const indep = 1;
                const conn = Math.max(0, currentBaysInGroup - 1);
                const smallConn = hasSmallBay ? 1 : 0;
                const groupLenMm = (currentBaysInGroup * beamLength) + (hasSmallBay ? smallBeamLength : 0) + 85;

                const newRack = {
                    x: groupStartX, y: groupStartY,
                    isHoriz: isHoriz, dir: dir,
                    independent: indep, connected: conn, smallConnected: smallConn,
                    beamLength: beamLength, smallBeamLength: smallBeamLength,
                    totalLengthPx: groupLenMm * currentScale,
                    rackDepth: rackDepth, isDouble: isDouble, isValid: true
                };
                if (checkRackValidPlacement(newRack)) {
                    racks.push(newRack);
                }
            }
        }
    });

    // ─────────────────────────────────────────────────────────────
    // 🌟 2단계: 중앙 공간 복렬(Double Row) 랙 자동 다중 열(Multi-Column) 배치 엔진 (경계 엄격 검사)
    // ─────────────────────────────────────────────────────────────
    if (spec.isCenterDouble) {
        const isVert = (spec.centerDirection === 'vert');
        const beamLength = spec.beamLength || 2585;
        const smallBeamLength = getSmallBeamLength(beamLength);
        const rackDepth = spec.rackDepth || 1000;
        const holderSizeMm = spec.holderSize || 200; // 가운데 홀더 기본 200mm (300, 500 등 가변 가능)
        const astMm = spec.ast || 2800; // 작업 통로폭 (기본 2800mm)
        const doubleDepthMm = (rackDepth * 2) + holderSizeMm; // 복렬 깊이 (기본 1000*2 + 200 = 2200mm)
        const doubleDepthPx = doubleDepthMm * currentScale;
        
        let polyMinX = Infinity, polyMaxX = -Infinity, polyMinY = Infinity, polyMaxY = -Infinity;
        points.forEach(p => {
            if (p.x < polyMinX) polyMinX = p.x;
            if (p.x > polyMaxX) polyMaxX = p.x;
            if (p.y < polyMinY) polyMinY = p.y;
            if (p.y > polyMaxY) polyMaxY = p.y;
        });

        const wallClearanceMm = rackDepth + 100 + astMm; // 벽 이격 + 랙깊이 + AST 통로폭
        const innerMinX = polyMinX + wallClearanceMm * currentScale;
        const innerMaxX = polyMaxX - wallClearanceMm * currentScale;
        const innerMinY = polyMinY + wallClearanceMm * currentScale;
        const innerMaxY = polyMaxY - wallClearanceMm * currentScale;

        const centerWidthMm = (innerMaxX - innerMinX) / currentScale;
        const centerHeightMm = (innerMaxY - innerMinY) / currentScale;

        if (isVert) {
            // ── 세로(Vertical) 복렬 랙 다중 열(Multi-Column) 수학적 완벽 배치 ──
            if (centerHeightMm >= beamLength + 85 && centerWidthMm >= doubleDepthMm) {
                const maxCenterBays = Math.floor((centerHeightMm - 100) / beamLength);
                
                // 가용 폭에 안전하게 들어갈 수 있는 최대 복렬 랙 열 수 (좌/우 및 내부 통로 최소 2800mm 보장)
                const colPitchMm = doubleDepthMm + astMm; // 예: 2200 + 2800 = 5000mm
                let numCols = Math.max(1, Math.floor((centerWidthMm + astMm) / colPitchMm));

                // 내부 통로폭 균등 분배
                const totalInteriorAisleSpaceMm = centerWidthMm - (numCols * doubleDepthMm);
                const interiorAisleMm = numCols > 1 ? (totalInteriorAisleSpaceMm / (numCols - 1)) : 0;

                for (let colIdx = 0; colIdx < numCols; colIdx++) {
                    const colLeftX = numCols > 1 
                        ? (innerMinX + (colIdx * (doubleDepthMm + interiorAisleMm)) * currentScale)
                        : (innerMinX + (centerWidthMm - doubleDepthMm) / 2 * currentScale);
                    const colCenterX = colLeftX + doubleDepthPx / 2;
                    const startY = innerMinY + 50 * currentScale;
                    
                    let centerBaysInGroup = 0;
                    let cGroupStartX = colCenterX;
                    let cGroupStartY = startY;

                    for (let b = 0; b < maxCenterBays; b++) {
                        const bayStartMm = b * beamLength;
                        const bayEndMm = bayStartMm + beamLength + 85;

                        const bayStartY = startY + bayStartMm * currentScale;
                        const bayEndY = startY + bayEndMm * currentScale;

                        const bayMinX = colCenterX - doubleDepthPx / 2;
                        const bayMaxX = colCenterX + doubleDepthPx / 2;
                        const bayMinY = Math.min(bayStartY, bayEndY);
                        const bayMaxY = Math.max(bayStartY, bayEndY);

                        // 4개 꼭짓점 전체가 창고 다각형 내부에 완전히 들어있는지 엄격 검사
                        const margin = 20 * currentScale;
                        const corners = [
                            [bayMinX + margin, bayMinY + margin],
                            [bayMaxX - margin, bayMinY + margin],
                            [bayMinX + margin, bayMaxY - margin],
                            [bayMaxX - margin, bayMaxY - margin],
                            [colCenterX, (bayMinY + bayMaxY) / 2]
                        ];
                        let allInside = corners.every(pt => isPointInPolygon(pt, points));

                        if (!allInside) {
                            if (centerBaysInGroup > 0) {
                                const groupLenMm = (centerBaysInGroup * beamLength) + 85;
                                const centerRack = {
                                    x: cGroupStartX, y: cGroupStartY,
                                    isHoriz: false, dir: 1,
                                    independent: 1, connected: centerBaysInGroup - 1, smallConnected: 0,
                                    beamLength: beamLength, smallBeamLength: smallBeamLength,
                                    totalLengthPx: groupLenMm * currentScale,
                                    rackDepth: rackDepth, holderSize: holderSizeMm, isDouble: true, isValid: true
                                };
                                let testRack = Object.assign({}, centerRack);
                                  testRack.smallConnected = 1;
                                  testRack.totalLengthPx = (centerRack.totalLengthPx / currentScale + smallBeamLength) * currentScale;
                                  if (checkRackValidPlacement(testRack)) {
                                      racks.push(testRack);
                                  } else if (checkRackValidPlacement(centerRack)) {
                                      racks.push(centerRack);
                                  }
                                centerBaysInGroup = 0;
                            }
                            continue;
                        }

                        let isColliding = false;
                        for (let obs of obstacles) {
                            const obsWPx = (obs.width || 500) * currentScale;
                            const obsHPx = (obs.height || 500) * currentScale;
                            const obsMinX = obs.x - obsWPx / 2;
                            const obsMaxX = obs.x + obsWPx / 2;
                            const obsMinY = obs.y - obsHPx / 2;
                            const obsMaxY = obs.y + obsHPx / 2;
                            const margin = 50 * currentScale;

                            if (!(bayMaxX - margin < obsMinX || bayMinX + margin > obsMaxX || bayMaxY - margin < obsMinY || bayMinY + margin > obsMaxY)) {
                                isColliding = true; break;
                            }
                        }

                        if (!isColliding) {
                            if (centerBaysInGroup === 0) {
                                cGroupStartX = colCenterX;
                                cGroupStartY = bayStartY;
                            }
                            centerBaysInGroup++;
                        } else {
                            if (centerBaysInGroup > 0) {
                                const groupLenMm = (centerBaysInGroup * beamLength) + 85;
                                const centerRack = {
                                    x: cGroupStartX, y: cGroupStartY,
                                    isHoriz: false, dir: 1,
                                    independent: 1, connected: centerBaysInGroup - 1, smallConnected: 0,
                                    beamLength: beamLength, smallBeamLength: smallBeamLength,
                                    totalLengthPx: groupLenMm * currentScale,
                                    rackDepth: rackDepth, holderSize: holderSizeMm, isDouble: true, isValid: true
                                };
                                let testRack = Object.assign({}, centerRack);
                                  testRack.smallConnected = 1;
                                  testRack.totalLengthPx = (centerRack.totalLengthPx / currentScale + smallBeamLength) * currentScale;
                                  if (checkRackValidPlacement(testRack)) {
                                      racks.push(testRack);
                                  } else if (checkRackValidPlacement(centerRack)) {
                                      racks.push(centerRack);
                                  }
                                centerBaysInGroup = 0;
                            }
                        }
                    }

                    if (centerBaysInGroup > 0) {
                        const groupLenMm = (centerBaysInGroup * beamLength) + 85;
                        const centerRack = {
                            x: cGroupStartX, y: cGroupStartY,
                            isHoriz: false, dir: 1,
                            independent: 1, connected: centerBaysInGroup - 1, smallConnected: 0,
                            beamLength: beamLength, smallBeamLength: smallBeamLength,
                            totalLengthPx: groupLenMm * currentScale,
                            rackDepth: rackDepth, holderSize: holderSizeMm, isDouble: true, isValid: true
                        };
                        let testRack = Object.assign({}, centerRack);
                                  testRack.smallConnected = 1;
                                  testRack.totalLengthPx = (centerRack.totalLengthPx / currentScale + smallBeamLength) * currentScale;
                                  if (checkRackValidPlacement(testRack)) {
                                      racks.push(testRack);
                                  } else if (checkRackValidPlacement(centerRack)) {
                                      racks.push(centerRack);
                                  }
                    }
                }
            }
        } else {
            // ── 가로(Horizontal) 복렬 랙 배치 ──
            if (centerWidthMm >= beamLength + 85 && centerHeightMm >= doubleDepthMm) {
                const maxCenterBays = Math.floor((centerWidthMm - 100) / beamLength);
                const rowPitchMm = doubleDepthMm + astMm;
                let numRows = Math.max(1, Math.floor((centerHeightMm + astMm) / rowPitchMm));

                const totalInteriorAisleSpaceMm = centerHeightMm - (numRows * doubleDepthMm);
                const interiorAisleMm = numRows > 1 ? (totalInteriorAisleSpaceMm / (numRows - 1)) : 0;

                for (let rowIdx = 0; rowIdx < numRows; rowIdx++) {
                    const rowTopY = numRows > 1 
                        ? (innerMinY + (rowIdx * (doubleDepthMm + interiorAisleMm)) * currentScale)
                        : (innerMinY + (centerHeightMm - doubleDepthMm) / 2 * currentScale);
                    const centerY = rowTopY + doubleDepthPx / 2;
                    const startX = innerMinX + 50 * currentScale;
                    
                    let centerBaysInGroup = 0;
                    let cGroupStartX = 0;
                    let cGroupStartY = centerY;

                    for (let b = 0; b < maxCenterBays; b++) {
                        const bayStartMm = b * beamLength;
                        const bayEndMm = bayStartMm + beamLength + 85;

                        const bayStartX = startX + bayStartMm * currentScale;
                        const bayEndX = startX + bayEndMm * currentScale;

                        const bayMinX = Math.min(bayStartX, bayEndX);
                        const bayMaxX = Math.max(bayStartX, bayEndX);
                        const bayMinY = centerY - doubleDepthPx / 2;
                        const bayMaxY = centerY + doubleDepthPx / 2;

                        const margin = 20 * currentScale;
                        const corners = [
                            [bayMinX + margin, bayMinY + margin],
                            [bayMaxX - margin, bayMinY + margin],
                            [bayMinX + margin, bayMaxY - margin],
                            [bayMaxX - margin, bayMaxY - margin],
                            [(bayMinX + bayMaxX) / 2, centerY]
                        ];
                        let allInside = corners.every(pt => isPointInPolygon(pt, points));

                        if (!allInside) {
                            if (centerBaysInGroup > 0) {
                                const groupLenMm = (centerBaysInGroup * beamLength) + 85;
                                const centerRack = {
                                    x: cGroupStartX, y: cGroupStartY,
                                    isHoriz: true, dir: 1,
                                    independent: 1, connected: centerBaysInGroup - 1, smallConnected: 0,
                                    beamLength: beamLength, smallBeamLength: smallBeamLength,
                                    totalLengthPx: groupLenMm * currentScale,
                                    rackDepth: rackDepth, holderSize: holderSizeMm, isDouble: true, isValid: true
                                };
                                let testRack = Object.assign({}, centerRack);
                                  testRack.smallConnected = 1;
                                  testRack.totalLengthPx = (centerRack.totalLengthPx / currentScale + smallBeamLength) * currentScale;
                                  if (checkRackValidPlacement(testRack)) {
                                      racks.push(testRack);
                                  } else if (checkRackValidPlacement(centerRack)) {
                                      racks.push(centerRack);
                                  }
                                centerBaysInGroup = 0;
                            }
                            continue;
                        }

                        let isColliding = false;
                        for (let obs of obstacles) {
                            const obsWPx = (obs.width || 500) * currentScale;
                            const obsHPx = (obs.height || 500) * currentScale;
                            const obsMinX = obs.x - obsWPx / 2;
                            const obsMaxX = obs.x + obsWPx / 2;
                            const obsMinY = obs.y - obsHPx / 2;
                            const obsMaxY = obs.y + obsHPx / 2;
                            const obsMargin = 50 * currentScale;

                            if (!(bayMaxX - obsMargin < obsMinX || bayMinX + obsMargin > obsMaxX || bayMaxY - obsMargin < obsMinY || bayMinY + obsMargin > obsMaxY)) {
                                isColliding = true; break;
                            }
                        }

                        if (!isColliding) {
                            if (centerBaysInGroup === 0) {
                                cGroupStartX = bayStartX;
                                cGroupStartY = centerY;
                            }
                            centerBaysInGroup++;
                        } else {
                            if (centerBaysInGroup > 0) {
                                const groupLenMm = (centerBaysInGroup * beamLength) + 85;
                                const centerRack = {
                                    x: cGroupStartX, y: cGroupStartY,
                                    isHoriz: true, dir: 1,
                                    independent: 1, connected: centerBaysInGroup - 1, smallConnected: 0,
                                    beamLength: beamLength, smallBeamLength: smallBeamLength,
                                    totalLengthPx: groupLenMm * currentScale,
                                    rackDepth: rackDepth, holderSize: holderSizeMm, isDouble: true, isValid: true
                                };
                                let testRack = Object.assign({}, centerRack);
                                  testRack.smallConnected = 1;
                                  testRack.totalLengthPx = (centerRack.totalLengthPx / currentScale + smallBeamLength) * currentScale;
                                  if (checkRackValidPlacement(testRack)) {
                                      racks.push(testRack);
                                  } else if (checkRackValidPlacement(centerRack)) {
                                      racks.push(centerRack);
                                  }
                                centerBaysInGroup = 0;
                            }
                        }
                    }

                    if (centerBaysInGroup > 0) {
                        const groupLenMm = (centerBaysInGroup * beamLength) + ((centerBaysInGroup + 1) * 85);
                        const centerRack = {
                            x: cGroupStartX, y: cGroupStartY,
                            isHoriz: true, dir: 1,
                            independent: 1, connected: centerBaysInGroup - 1, smallConnected: 0,
                            beamLength: beamLength, smallBeamLength: smallBeamLength,
                            totalLengthPx: groupLenMm * currentScale,
                            rackDepth: rackDepth, holderSize: holderSizeMm, isDouble: true, isValid: true
                        };
                        let testRack = Object.assign({}, centerRack);
                                  testRack.smallConnected = 1;
                                  testRack.totalLengthPx = (centerRack.totalLengthPx / currentScale + smallBeamLength + 85) * currentScale;
                                  if (checkRackValidPlacement(testRack)) {
                                      racks.push(testRack);
                                  } else if (checkRackValidPlacement(centerRack)) {
                                      racks.push(centerRack);
                                  }
                    }
                }
            }
        }
    }
    
    if (typeof updateRackFormCounts === 'function') {
        updateRackFormCounts();
    }
}

window.spawnInitialRacks = function() {
    if (currentScale <= 0) {
        alert("도면을 먼저 그려주세요!");
        return;
    }
    
    // 가져올 폼 값
    const palletW = parseInt(document.getElementById('pallet-w')?.value) || 1100;
    const palletD = parseInt(document.getElementById('pallet-d')?.value) || 1100;
    const beamLength = parseInt(document.getElementById('rack-beam-length')?.value) || (window.rackSpecs && window.rackSpecs.beamLength) || (palletW * 2) + 385;
    const rackDepth = parseInt(document.getElementById('rack-depth')?.value) || (window.rackSpecs && window.rackSpecs.rackDepth) || 1000;
    const smallBeamLength = (typeof getSmallBeamLength === 'function') ? getSmallBeamLength(beamLength) : 1385;
    
    // 독립 1칸 길이 (기둥 2개(85x2) + 빔 1개 = 2,755mm)
    const totalLenMm = (2 * 85) + (1 * beamLength);
    const totalLenPx = totalLenMm * currentScale;
    const rackDepthPx = rackDepth * currentScale;
    
    // 생성 위치 (캔버스 중앙)
    const centerX = canvas.width / 2;
    const centerY = canvas.height / 2;
    
    // 1. 독립 파랫트 (단식) 1개
    let singleRack = {
        x: centerX - (totalLenPx / 2),
        y: centerY - rackDepthPx - 30, // 살짝 위로
        isHoriz: true,
        dir: 1,
        independent: 1,
        connected: 0,
        smallConnected: 0,
        beamLength: beamLength,
        smallBeamLength: smallBeamLength,
        totalLengthPx: totalLenPx,
        rackDepth: rackDepth,
        isDouble: false,
        isValid: true,
        angle: 0
    };
    
    // 2. 독립 복수 1개
    let doubleRack = {
        x: centerX - (totalLenPx / 2),
        y: centerY + 30, // 살짝 아래로
        isHoriz: true,
        dir: 1,
        independent: 1,
        connected: 0,
        smallConnected: 0,
        beamLength: beamLength,
        smallBeamLength: smallBeamLength,
        totalLengthPx: totalLenPx,
        rackDepth: rackDepth,
        isDouble: true,
        isValid: true,
        angle: 0
    };
    
    racks.push(singleRack);
    racks.push(doubleRack);
    
    draw();
};


window.autoAlignRacks = function() {
    if (racks.length === 0 || points.length === 0) return;
    let pMinX = Infinity, pMinY = Infinity, pMaxX = -Infinity, pMaxY = -Infinity;
    points.forEach(p => {
        if (p.x < pMinX) pMinX = p.x;
        if (p.y < pMinY) pMinY = p.y;
        if (p.x > pMaxX) pMaxX = p.x;
        if (p.y > pMaxY) pMaxY = p.y;
    });

    // 🌟 장애물(출입문, 셔터, 기둥, 사용불가구역 등) 분류
    const allObs = (typeof obstacles !== 'undefined' && Array.isArray(obstacles)) ? obstacles : [];
    const doors = allObs.filter(o => o.type === 'door' || o.type === 'shutter');
    const pillars = allObs.filter(o => o.type !== 'door' && o.type !== 'shutter');

    const DOOR_CLEARANCE_MM = 3000; // 출입문 앞 확보할 지게차/통행 안전통로 (3.0m)
    const DOOR_SIDE_MARGIN_MM = 300; // 출입문 양옆 안전 마진 (300mm)
    const WALL_MARGIN_MM = 200; // 벽면 최소 이격 여백 (200mm)

    let horizCount = 0;
    racks.forEach(r => { if(r.isHoriz) horizCount++; });
    const alignHoriz = horizCount >= racks.length / 2;

    // 🌟 가로/세로 랙 공존 시 코너 데드존(모서리 깊이×깊이 설치불가) 오프셋
    const hasHorizRack = racks.some(r => r.isHoriz);
    const hasVertRack = racks.some(r => !r.isHoriz);
    const baseDepthMm = (racks.length > 0 && racks[0].rackDepth) ? racks[0].rackDepth : 1000;
    const cornerDeadOffsetPx = (hasHorizRack && hasVertRack) ? (baseDepthMm * currentScale) : 0;

    let singleRacks = racks.filter(r => !r.isDouble && r.isHoriz === alignHoriz);
    let topY = pMinY + (alignHoriz && hasVertRack ? cornerDeadOffsetPx : 0);
    let bottomY = pMaxY - (alignHoriz && hasVertRack ? cornerDeadOffsetPx : 0);
    let leftX = pMinX + (!alignHoriz && hasHorizRack ? cornerDeadOffsetPx : 0);
    let rightX = pMaxX - (!alignHoriz && hasHorizRack ? cornerDeadOffsetPx : 0);

    if (alignHoriz) {
        singleRacks.forEach(r => {
            const depth = (r.rackDepth || 1000) * currentScale;
            if (r.y < (pMinY + pMaxY) / 2) {
                if (r.y + depth / 2 > topY) topY = r.y + depth / 2;
            } else {
                if (r.y - depth / 2 < bottomY) bottomY = r.y - depth / 2;
            }
        });
    } else {
        singleRacks.forEach(r => {
            const depth = (r.rackDepth || 1000) * currentScale;
            if (r.x < (pMinX + pMaxX) / 2) {
                if (r.x + depth / 2 > leftX) leftX = r.x + depth / 2;
            } else {
                if (r.x - depth / 2 < rightX) rightX = r.x - depth / 2;
            }
        });
    }

    let doubleRacks = racks.filter(r => r.isDouble && r.isHoriz === alignHoriz);
    const AISLE_CLEARANCE_PX = (typeof AISLE_DIST_PX !== 'undefined' ? AISLE_DIST_PX : 2800 * currentScale);

    if (doubleRacks.length > 0) {
        if (alignHoriz) {
            // ─────────────────────────────────────────────
            // 1. 가로 배치 랙 (Horizontal Racks)
            // ─────────────────────────────────────────────
            doubleRacks.sort((a, b) => a.y - b.y);
            let rows = [];
            let currentRow = [doubleRacks[0]];
            for(let i = 1; i < doubleRacks.length; i++) {
                if (Math.abs(doubleRacks[i].y - currentRow[0].y) < 200 * currentScale) {
                    currentRow.push(doubleRacks[i]);
                } else {
                    rows.push(currentRow);
                    currentRow = [doubleRacks[i]];
                }
            }
            rows.push(currentRow);

            let totalRowsDepth = 0;
            let rowDepths = [];
            rows.forEach(row => {
                let d = 0;
                row.forEach(r => d = Math.max(d, ((r.rackDepth||1000) * 2 + (r.holderSize||200)) * currentScale));
                rowDepths.push(d);
                totalRowsDepth += d;
            });
            const gap = (bottomY - topY - totalRowsDepth) / (rows.length + 1);

            // 세로 방향(직교) 랙들 추출
            const vertRacks = racks.filter(r => !r.isHoriz);

            rows.forEach((row, idx) => {
                let rowTop = topY + gap * (idx + 1);
                for (let i = 0; i < idx; i++) {
                    rowTop += rowDepths[i];
                }
                const targetY = rowTop + rowDepths[idx] / 2; // 행 폭의 정확한 중심 배치
                const rowMinY = targetY - rowDepths[idx] / 2;
                const rowMaxY = targetY + rowDepths[idx] / 2;

                // 🌟 가로 복수랙의 좌우 사용 가능 경계(availMinX, availMaxX)를 정밀 산출
                let minLeftX = pMinX + WALL_MARGIN_MM * currentScale;
                let maxRightX = pMaxX - WALL_MARGIN_MM * currentScale;

                // 1) 좌측/우측 벽면 출입문 회피 (지게차 통로 3.0m)
                doors.forEach(d => {
                    const dLenPx = (d.length || 3500) * currentScale;
                    const dMinY = d.y - dLenPx / 2 - DOOR_SIDE_MARGIN_MM * currentScale;
                    const dMaxY = d.y + dLenPx / 2 + DOOR_SIDE_MARGIN_MM * currentScale;
                    if (rowMaxY > dMinY && rowMinY < dMaxY) {
                        if (Math.abs(d.x - pMinX) < 1500 * currentScale) {
                            minLeftX = Math.max(minLeftX, pMinX + DOOR_CLEARANCE_MM * currentScale);
                        }
                        if (Math.abs(d.x - pMaxX) < 1500 * currentScale) {
                            maxRightX = Math.min(maxRightX, pMaxX - DOOR_CLEARANCE_MM * currentScale);
                        }
                    }
                });

                // 2) 🌟 좌측 또는 우측에 위치한 세로 랙(직교 랙)과의 지게차 통로(2,800mm) 간격 확보
                vertRacks.forEach(vr => {
                    const vBoxes = (typeof getRackBoxes === 'function') ? getRackBoxes(vr) : null;
                    const vMinY = vBoxes ? vBoxes.physical.minY : vr.y;
                    const vMaxY = vBoxes ? vBoxes.physical.maxY : (vr.y + (vr.totalLengthPx || 0));
                    const vMinX = vBoxes ? vBoxes.physical.minX : vr.x;
                    const vMaxX = vBoxes ? vBoxes.physical.maxX : (vr.x + 1000 * currentScale);

                    // 해당 가로 행과 Y축 높이가 겹치는지 검사
                    if (rowMaxY > vMinY - 10 && rowMinY < vMaxY + 10) {
                        // 세로 랙이 왼쪽에 있는 경우
                        if (vMaxX < (pMinX + pMaxX) / 2) {
                            minLeftX = Math.max(minLeftX, vMaxX + AISLE_CLEARANCE_PX);
                        }
                        // 세로 랙이 오른쪽에 있는 경우
                        else {
                            maxRightX = Math.min(maxRightX, vMinX - AISLE_CLEARANCE_PX);
                        }
                    }
                });

                // 🌟 남은 유효 가로 공간의 중앙 산출
                const availWidthPx = Math.max(50, maxRightX - minLeftX);
                const availCenterX = (minLeftX + maxRightX) / 2;

                row.forEach(r => {
                    r.y = targetY;
                    const isFlipped = Math.cos(getRackAngle(r)) < -0.1;

                    // 만약 복수랙의 길이가 남은 유효 공간보다 크다면 지게차 통로 확보를 위해 베이 수 자동 조절
                    if (r.totalLengthPx > availWidthPx) {
                        const availWidthMm = availWidthPx / currentScale;
                        const beam = r.beamLength || 2585;
                        let maxBays = Math.floor((availWidthMm - 85) / beam);
                        if (maxBays >= 1) {
                            r.independent = 1;
                            r.connected = Math.max(0, maxBays - 1);
                            r.smallConnected = 0;
                            const newLenMm = (r.independent + r.connected) * beam + 85;
                            r.totalLengthPx = newLenMm * currentScale;
                            if (r.bypassBays && Array.isArray(r.bypassBays)) {
                                r.bypassBays = r.bypassBays.map(sub => (sub || []).slice(0, (r.independent + r.connected)));
                            }
                        }
                    }

                    // 창고 전체 중심이 아닌, 세로랙 및 출입문을 고려한 '유효 여유 공간의 정중앙'에 정렬!
                    let targetX = isFlipped ? (availCenterX + r.totalLengthPx / 2) : (availCenterX - r.totalLengthPx / 2);
                    
                    // 경계 안전 보정
                    const leftEdge = isFlipped ? (targetX - r.totalLengthPx) : targetX;
                    const rightEdge = isFlipped ? targetX : (targetX + r.totalLengthPx);
                    if (leftEdge < minLeftX) {
                        targetX += (minLeftX - leftEdge);
                    } else if (rightEdge > maxRightX) {
                        targetX -= (rightEdge - maxRightX);
                    }

                    r.x = targetX;
                });
            });
        } else {
            // ─────────────────────────────────────────────
            // 2. 세로 배치 랙 (Vertical Racks)
            // ─────────────────────────────────────────────
            doubleRacks.sort((a, b) => a.x - b.x);
            let cols = [];
            let currentCol = [doubleRacks[0]];
            for(let i = 1; i < doubleRacks.length; i++) {
                if (Math.abs(doubleRacks[i].x - currentCol[0].x) < 200 * currentScale) {
                    currentCol.push(doubleRacks[i]);
                } else {
                    cols.push(currentCol);
                    currentCol = [doubleRacks[i]];
                }
            }
            cols.push(currentCol);

            let totalColsDepth = 0;
            let colDepths = [];
            cols.forEach(col => {
                let d = 0;
                col.forEach(r => d = Math.max(d, ((r.rackDepth||1000) * 2 + (r.holderSize||200)) * currentScale));
                colDepths.push(d);
                totalColsDepth += d;
            });
            const gap = (rightX - leftX - totalColsDepth) / (cols.length + 1);

            // 가로 방향(직교) 랙들 추출
            const horizRacks = racks.filter(r => r.isHoriz);

            cols.forEach((col, idx) => {
                let colLeft = leftX + gap * (idx + 1);
                for (let i = 0; i < idx; i++) {
                    colLeft += colDepths[i];
                }
                const targetX = colLeft + colDepths[idx] / 2; // 열 폭의 정확한 중심 배치
                const colMinX = targetX - colDepths[idx] / 2;
                const colMaxX = targetX + colDepths[idx] / 2;

                let minTopY = pMinY + WALL_MARGIN_MM * currentScale;
                let maxBottomY = pMaxY - WALL_MARGIN_MM * currentScale;

                // 1) 상단/하단 벽면 출입문 충돌 검사
                doors.forEach(d => {
                    const dLenPx = (d.length || 3500) * currentScale;
                    const dMinX = d.x - dLenPx / 2 - DOOR_SIDE_MARGIN_MM * currentScale;
                    const dMaxX = d.x + dLenPx / 2 + DOOR_SIDE_MARGIN_MM * currentScale;
                    if (colMaxX > dMinX && colMinX < dMaxX) {
                        if (Math.abs(d.y - pMinY) < 1500 * currentScale) {
                            minTopY = Math.max(minTopY, pMinY + DOOR_CLEARANCE_MM * currentScale);
                        }
                        if (Math.abs(d.y - pMaxY) < 1500 * currentScale) {
                            maxBottomY = Math.min(maxBottomY, pMaxY - DOOR_CLEARANCE_MM * currentScale);
                        }
                    }
                });

                // 2) 🌟 상단 또는 하단에 위치한 가로 랙(직교 랙)과의 지게차 통로(2,800mm) 간격 확보
                horizRacks.forEach(hr => {
                    const hBoxes = (typeof getRackBoxes === 'function') ? getRackBoxes(hr) : null;
                    const hMinX = hBoxes ? hBoxes.physical.minX : hr.x;
                    const hMaxX = hBoxes ? hBoxes.physical.maxX : (hr.x + (hr.totalLengthPx || 0));
                    const hMinY = hBoxes ? hBoxes.physical.minY : hr.y;
                    const hMaxY = hBoxes ? hBoxes.physical.maxY : (hr.y + 1000 * currentScale);

                    if (colMaxX > hMinX - 10 && colMinX < hMaxX + 10) {
                        if (hMaxY < (pMinY + pMaxY) / 2) {
                            minTopY = Math.max(minTopY, hMaxY + AISLE_CLEARANCE_PX);
                        } else {
                            maxBottomY = Math.min(maxBottomY, hMinY - AISLE_CLEARANCE_PX);
                        }
                    }
                });

                const availHeightPx = Math.max(50, maxBottomY - minTopY);
                const availCenterY = (minTopY + maxBottomY) / 2;

                col.forEach(r => {
                    r.x = targetX;
                    const isFlippedVert = Math.sin(getRackAngle(r)) < -0.1;

                    if (r.totalLengthPx > availHeightPx) {
                        const availHeightMm = availHeightPx / currentScale;
                        const beam = r.beamLength || 2585;
                        let maxBays = Math.floor((availHeightMm - 85) / beam);
                        if (maxBays >= 1) {
                            r.independent = 1;
                            r.connected = Math.max(0, maxBays - 1);
                            r.smallConnected = 0;
                            const newLenMm = (r.independent + r.connected) * beam + 85;
                            r.totalLengthPx = newLenMm * currentScale;
                            if (r.bypassBays && Array.isArray(r.bypassBays)) {
                                r.bypassBays = r.bypassBays.map(sub => (sub || []).slice(0, (r.independent + r.connected)));
                            }
                        }
                    }

                    let targetY = isFlippedVert ? (availCenterY + r.totalLengthPx / 2) : (availCenterY - r.totalLengthPx / 2);
                    const topEdge = isFlippedVert ? (targetY - r.totalLengthPx) : targetY;
                    const botEdge = isFlippedVert ? targetY : (targetY + r.totalLengthPx);
                    if (topEdge < minTopY) {
                        targetY += (minTopY - topEdge);
                    } else if (botEdge > maxBottomY) {
                        targetY -= (botEdge - maxBottomY);
                    }

                    r.y = targetY;
                });
            });
        }
    }

    // 🌟 모든 랙의 유효성 검사 최종 재평가
    racks.forEach(r => {
        if (typeof checkRackValidPlacement === 'function') {
            r.isValid = checkRackValidPlacement(r);
        }
    });

    // ─────────────────────────────────────────────
    // 3. 내부 기둥 및 장애물(Pillar / Forbidden Area) 간섭 자동 바이패스(Bypass) 감지
    // ─────────────────────────────────────────────
    if (pillars.length > 0) {
        racks.forEach(r => {
            const beam = r.beamLength || 2585;
            const indep = r.independent || 1;
            const conn = r.connected || 0;
            const totalBays = indep + conn;
            if (!r.bypassBays || !Array.isArray(r.bypassBays) || r.bypassBays.length < 2) {
                r.bypassBays = [
                    new Array(totalBays).fill(false),
                    new Array(totalBays).fill(false)
                ];
            }

            const isHoriz = r.isHoriz;
            const angle = getRackAngle(r);
            const isFlipped = isHoriz ? (Math.cos(angle) < -0.1) : (Math.sin(angle) < -0.1);
            const depthPx = ((r.rackDepth || 1000) * (r.isDouble ? 2 : 1) + (r.isDouble ? (r.holderSize || 200) : 0)) * currentScale;

            pillars.forEach(pil => {
                const pWPx = (pil.width || 500) * currentScale;
                const pHPx = (pil.height || 500) * currentScale;
                const pilMinX = pil.x - pWPx / 2;
                const pilMaxX = pil.x + pWPx / 2;
                const pilMinY = pil.y - pHPx / 2;
                const pilMaxY = pil.y + pHPx / 2;

                for (let b = 0; b < totalBays; b++) {
                    const bayOffsetMm = b * beam;
                    const bayStartPx = bayOffsetMm * currentScale;
                    const bayEndPx = (bayOffsetMm + beam) * currentScale;

                    let bMinX, bMaxX, bMinY, bMaxY;
                    if (isHoriz) {
                        const startX = isFlipped ? (r.x - bayEndPx) : (r.x + bayStartPx);
                        const endX = isFlipped ? (r.x - bayStartPx) : (r.x + bayEndPx);
                        bMinX = Math.min(startX, endX);
                        bMaxX = Math.max(startX, endX);
                        bMinY = r.y - depthPx / 2;
                        bMaxY = r.y + depthPx / 2;
                    } else {
                        const startY = isFlipped ? (r.y - bayEndPx) : (r.y + bayStartPx);
                        const endY = isFlipped ? (r.y - bayStartPx) : (r.y + bayEndPx);
                        bMinX = r.x - depthPx / 2;
                        bMaxX = r.x + depthPx / 2;
                        bMinY = Math.min(startY, endY);
                        bMaxY = Math.max(startY, endY);
                    }

                    // 겹침 판정
                    if (!(bMaxX < pilMinX || bMinX > pilMaxX || bMaxY < pilMinY || bMinY > pilMaxY)) {
                        // 기둥과 겹치는 베이에 자동으로 바이패스(터널 통로) 활성화!
                        if (r.bypassBays[0]) r.bypassBays[0][b] = true;
                        if (r.bypassBays[1]) r.bypassBays[1][b] = true;
                    }
                }
            });
        });
    }

    // 변경된 랙 정보 렌더링 및 동기화
    if (typeof window.renderFloorTabs === 'function') window.renderFloorTabs();
    if (typeof renderObstacleInputs === 'function') renderObstacleInputs();
    draw();
};


window.getGridSnapPx = function() {
    if (currentScale <= 0) return 1;
    const snapMm = 10; // 장애물 배치 시 10mm 단위로 정밀하게 이동
    return snapMm * currentScale;
};

canvas.addEventListener('mousemove', e => {
    window.lastMouseX = e.offsetX;
    window.lastMouseY = e.offsetY;
});

// FIXED by Heidi - global exposure (항상 최신 랙 배열을 반환하도록 getter 제공)
try {
    Object.defineProperty(window, 'racks', {
        get: function() { return typeof racks !== 'undefined' ? racks : []; },
        configurable: true
    });
} catch(err) {
    window.racks = typeof racks !== 'undefined' ? racks : [];
}
window.getRacks = function() { return typeof racks !== 'undefined' ? racks : []; };
window.currentScale = typeof currentScale !== 'undefined' ? currentScale : 0;
window.cameraZoom = typeof cameraZoom !== 'undefined' ? cameraZoom : 1;
window.checkRackValidPlacement = typeof checkRackValidPlacement !== 'undefined' ? checkRackValidPlacement : null;

// ===================================================================
// [멀티 플로어 / 다중 층 캔버스 탭 상태 관리자]
// 엑셀 시트 탭 방식으로 1층/2층/다중 창고 구역을 독립 관리 & 통합 견적
// ===================================================================
window.canvasFloors = [
    {
        id: 1,
        name: '1층 (기본 창고)',
        points: [],
        obstacles: [],
        edgeLengths: [],
        originalAngles: [],
        originalVisualLengths: [],
        userEnteredEdges: [],
        racks: [],
        currentScale: 0,
        isDrawingMode: true,
        cameraZoom: 1,
        rackCustomLevels: null,
        capturedImage: null,
        palletSpec: null
    }
];
window.currentFloorIndex = 0;

// 🌟 창고 및 랙 크기에 딱 맞춘 스마트 크롭 캔버스 생성 함수 (공백 제거)
window.getCroppedCanvas = function(options = {}) {
    if (!canvas || typeof points === 'undefined' || !points || points.length < 2) return null;
    
    const padding = options.padding !== undefined ? options.padding : 65; // 넉넉한 치수선/외곽 여백
    const isPrintMode = options.isPrintMode !== undefined ? options.isPrintMode : true; // CAD 반전 인쇄 모드
    
    let minX = Infinity, maxX = -Infinity;
    let minY = Infinity, maxY = -Infinity;
    
    // 1) 창고 외곽선 점들
    points.forEach(p => {
        if (p && typeof p.x === 'number' && typeof p.y === 'number') {
            minX = Math.min(minX, p.x);
            maxX = Math.max(maxX, p.x);
            minY = Math.min(minY, p.y);
            maxY = Math.max(maxY, p.y);
        }
    });
    
    // 2) 배치된 랙들
    if (typeof racks !== 'undefined' && Array.isArray(racks)) {
        racks.forEach(r => {
            if (typeof getRackBoxes === 'function') {
                try {
                    const boxes = getRackBoxes(r);
                    if (boxes && boxes.physical) {
                        minX = Math.min(minX, boxes.physical.minX);
                        maxX = Math.max(maxX, boxes.physical.maxX);
                        minY = Math.min(minY, boxes.physical.minY);
                        maxY = Math.max(maxY, boxes.physical.maxY);
                        return;
                    }
                } catch(e) {}
            }
            const depth = ((r.rackDepth || 1000) * 2 + 500) * (window.currentScale || currentScale || 1);
            const len = r.totalLengthPx || 0;
            minX = Math.min(minX, r.x - depth);
            maxX = Math.max(maxX, r.x + len + depth);
            minY = Math.min(minY, r.y - depth);
            maxY = Math.max(maxY, r.y + len + depth);
        });
    }
    
    // 3) 장애물 (문, 기둥, 셔터 등)
    if (typeof obstacles !== 'undefined' && Array.isArray(obstacles)) {
        obstacles.forEach(obs => {
            const size = Math.max(obs.width || 0, obs.height || 0, obs.length || 0) * (window.currentScale || currentScale || 1) + 30;
            minX = Math.min(minX, obs.x - size);
            maxX = Math.max(maxX, obs.x + size);
            minY = Math.min(minY, obs.y - size);
            maxY = Math.max(maxY, obs.y + size);
        });
    }
    
    if (!isFinite(minX) || !isFinite(maxX) || !isFinite(minY) || !isFinite(maxY)) {
        return null;
    }
    
    // 4) 외곽 치수선과 벽 번호 뱃지(1, 2, 3, 4)를 감안한 여백(padding) 부여
    const pad = Math.max(45, padding);
    minX = Math.max(0, minX - pad);
    minY = Math.max(0, minY - pad);
    
    const zoom = (typeof cameraZoom !== 'undefined' && cameraZoom > 0) ? cameraZoom : 1;
    maxX = Math.min(canvas.width / zoom, maxX + pad);
    maxY = Math.min(canvas.height / zoom, maxY + pad);
    
    let cropX = Math.floor(minX * zoom);
    let cropY = Math.floor(minY * zoom);
    let cropW = Math.ceil((maxX - minX) * zoom);
    let cropH = Math.ceil((maxY - minY) * zoom);
    
    cropX = Math.max(0, Math.min(cropX, canvas.width - 20));
    cropY = Math.max(0, Math.min(cropY, canvas.height - 20));
    cropW = Math.min(cropW, canvas.width - cropX);
    cropH = Math.min(cropH, canvas.height - cropY);
    
    if (cropW < 50 || cropH < 50) return null;
    
    // 5) 테마 및 렌더링 스타일 처리
    const prevTheme = window.CANVAS_THEME;
    const isCurrentlyLight = document.body.classList.contains('theme-light') || window.CANVAS_THEME === 'light';
    
    if (isPrintMode && isCurrentlyLight) {
        window.CANVAS_THEME = 'dark';
        document.body.classList.remove('theme-light');
        if (typeof draw === 'function') draw();
    }
    
    const cropCanvas = document.createElement('canvas');
    cropCanvas.width = cropW;
    cropCanvas.height = cropH;
    const cCtx = cropCanvas.getContext('2d');
    
    if (isPrintMode) {
        cCtx.fillStyle = '#ffffff';
        cCtx.fillRect(0, 0, cropW, cropH);
        cCtx.filter = 'invert(1)';
        cCtx.drawImage(canvas, cropX, cropY, cropW, cropH, 0, 0, cropW, cropH);
        cCtx.filter = 'none';
    } else {
        cCtx.fillStyle = isCurrentlyLight ? '#f8fafc' : '#0f172a';
        cCtx.fillRect(0, 0, cropW, cropH);
        cCtx.drawImage(canvas, cropX, cropY, cropW, cropH, 0, 0, cropW, cropH);
    }
    
    if (isPrintMode && isCurrentlyLight) {
        window.CANVAS_THEME = prevTheme;
        document.body.classList.add('theme-light');
        if (typeof draw === 'function') draw();
    }
    
    return cropCanvas;
};

function captureFloorSnapshot() {
    try {
        const c = document.getElementById('drawingCanvas');
        if (!c || c.style.display === 'none' || c.width <= 0) return null;
        
        // 🌟 창고 크기에 맞춘 스마트 크롭 적용
        if (typeof window.getCroppedCanvas === 'function') {
            const cropped = window.getCroppedCanvas({ padding: 65, isPrintMode: true });
            if (cropped) {
                return cropped.toDataURL('image/jpeg', 0.88);
            }
        }

        const tempCanvas = document.createElement('canvas');
        tempCanvas.width = c.width;
        tempCanvas.height = c.height;
        const tCtx = tempCanvas.getContext('2d');
        tCtx.fillStyle = '#ffffff';
        tCtx.fillRect(0, 0, tempCanvas.width, tempCanvas.height);
        
        // 다크모드/라이트모드 판별하여 반전 여부 결정 (다크모드일 때만 반전하여 CAD 인쇄 스타일 적용)
        const isLight = document.body.classList.contains('theme-light') || (typeof window.CANVAS_THEME !== 'undefined' && window.CANVAS_THEME === 'light');
        if (!isLight) {
            tCtx.filter = 'invert(1)';
        }
        tCtx.drawImage(c, 0, 0);
        tCtx.filter = 'none';
        return tempCanvas.toDataURL('image/jpeg', 0.85);
    } catch(e) {
        return null;
    }
}

function saveCurrentFloorState() {
    if (!window.canvasFloors || !window.canvasFloors[window.currentFloorIndex]) return;
    const cur = window.canvasFloors[window.currentFloorIndex];
    cur.points = JSON.parse(JSON.stringify(points || []));
    cur.obstacles = JSON.parse(JSON.stringify(obstacles || []));
    cur.edgeLengths = [...(edgeLengths || [])];
    cur.originalAngles = [...(originalAngles || [])];
    cur.originalVisualLengths = [...(originalVisualLengths || [])];
    cur.userEnteredEdges = [...(userEnteredEdges || [])];
    cur.racks = JSON.parse(JSON.stringify(racks || []));
    cur.currentScale = currentScale;
    cur.canvasOriginX = (window.canvasOriginX !== undefined) ? window.canvasOriginX : 0;
    cur.canvasOriginY = (window.canvasOriginY !== undefined) ? window.canvasOriginY : 0;
    cur.isDrawingMode = isDrawingMode;
    cur.cameraZoom = cameraZoom;
    cur.rackCustomLevels = window.rackCustomLevels ? JSON.parse(JSON.stringify(window.rackCustomLevels)) : null;

    // 🌟 선분 입력값(edgeLengths / input.value) 실시간 캡처 및 DOM attribute 동기화
    const savedEdgeValues = [];
    document.querySelectorAll('input[id^="edge-input-"]').forEach(inp => {
        const m = inp.id.match(/^edge-input-(\d+)$/);
        if (m) {
            const idx = parseInt(m[1]);
            savedEdgeValues[idx] = inp.value;
            inp.setAttribute('value', inp.value);
            // 만약 사용자가 입력했는데 edgeLengths에 아직 미반영된 경우 보정
            if (inp.value && (!cur.edgeLengths[idx] || cur.edgeLengths[idx] <= 0)) {
                const valInt = parseInt(inp.value) || 0;
                if (valInt > 0) cur.edgeLengths[idx] = valInt;
            }
        }
    });
    cur.savedEdgeValues = savedEdgeValues;

    // ChatWizard 상태 저장 (각 층별 독립 대화 보존)
    if (typeof ChatWizard !== 'undefined') {
        if (ChatWizard.body) {
            ChatWizard.body.querySelectorAll('input, select, textarea').forEach(el => {
                if (el.type === 'checkbox' || el.type === 'radio') {
                    if (el.checked) el.setAttribute('checked', 'checked');
                    else el.removeAttribute('checked');
                } else {
                    el.setAttribute('value', el.value);
                }
            });
        }
        cur.chatStep = ChatWizard.currentStep;
        cur.chatHtml = (ChatWizard.body) ? ChatWizard.body.innerHTML : '';
        cur.step2Html = ChatWizard.step2Html || '';
    }

    // Save pallet specs from form inputs
    cur.palletSpec = {
        pw: document.getElementById('pallet-w') ? document.getElementById('pallet-w').value : '1100',
        pd: document.getElementById('pallet-d') ? document.getElementById('pallet-d').value : '1100',
        ph: document.getElementById('pallet-h') ? document.getElementById('pallet-h').value : '1500',
        pWeight: document.getElementById('pallet-weight') ? document.getElementById('pallet-weight').value : '1000',
        levels: document.getElementById('rack-levels') ? document.getElementById('rack-levels').value : '3',
        rowType: (document.querySelector('input[name="rowType"]:checked') || {}).value || 'single',
        rackDepth: document.getElementById('rack-depth') ? document.getElementById('rack-depth').value : '1000',
        rackHeight: document.getElementById('rack-height') ? document.getElementById('rack-height').value : '4000',
        rackLoad: document.getElementById('rack-load') ? document.getElementById('rack-load').value : '2000',
        forkDir: (document.getElementById('forkD') && document.getElementById('forkD').checked) ? 'D' : 'W',
        forkType: document.getElementById('forklift-type') ? document.getElementById('forklift-type').value : '',
        forkLiftH: document.getElementById('forklift-lift-height') ? document.getElementById('forklift-lift-height').value : '',
        forkAst: document.getElementById('forklift-ast') ? document.getElementById('forklift-ast').value : ''
    };

    // Capture snapshot for instant quotation printing
    const snap = captureFloorSnapshot();
    if (snap) cur.capturedImage = snap;
}

function loadFloorState(index) {
    if (!window.canvasFloors || !window.canvasFloors[index]) return;
    window.currentFloorIndex = index;
    const target = window.canvasFloors[index];

    points = JSON.parse(JSON.stringify(target.points || []));
    obstacles = JSON.parse(JSON.stringify(target.obstacles || []));
    edgeLengths = [...(target.edgeLengths || [])];
    originalAngles = [...(target.originalAngles || [])];
    originalVisualLengths = [...(target.originalVisualLengths || [])];
    userEnteredEdges = [...(target.userEnteredEdges || [])];
    racks = JSON.parse(JSON.stringify(target.racks || []));
    currentScale = target.currentScale || 0;
    window.currentScale = currentScale;
    window.canvasOriginX = (target.canvasOriginX !== undefined) ? target.canvasOriginX : 0;
    window.canvasOriginY = (target.canvasOriginY !== undefined) ? target.canvasOriginY : 0;
    cameraZoom = target.cameraZoom || 1;

    // 폐합되지 않은 상태라면 점 찍기 모드(isDrawingMode = true) 유지
    const isClosed = points.length >= 4 && points[0].x === points[points.length - 1].x && points[0].y === points[points.length - 1].y;
    if (!isClosed) {
        isDrawingMode = true;
    } else {
        isDrawingMode = target.isDrawingMode || false;
    }
    window.rackCustomLevels = target.rackCustomLevels ? JSON.parse(JSON.stringify(target.rackCustomLevels)) : null;

    // Restore pallet form if saved
    if (target.palletSpec) {
        const s = target.palletSpec;
        if (document.getElementById('pallet-w') && s.pw) document.getElementById('pallet-w').value = s.pw;
        if (document.getElementById('pallet-d') && s.pd) document.getElementById('pallet-d').value = s.pd;
        if (document.getElementById('pallet-h') && s.ph) document.getElementById('pallet-h').value = s.ph;
        if (document.getElementById('pallet-weight') && s.pWeight) document.getElementById('pallet-weight').value = s.pWeight;
        if (document.getElementById('rack-levels') && s.levels) document.getElementById('rack-levels').value = s.levels;
        if (document.getElementById('rack-depth') && s.rackDepth) document.getElementById('rack-depth').value = s.rackDepth;
        if (document.getElementById('rack-height') && s.rackHeight) document.getElementById('rack-height').value = s.rackHeight;
        if (document.getElementById('rack-load') && s.rackLoad) document.getElementById('rack-load').value = s.rackLoad;
        if (s.rowType) {
            const r = document.querySelector(`input[name="rowType"][value="${s.rowType}"]`);
            if (r) r.checked = true;
        }
        if (s.forkDir) {
            if (s.forkDir === 'D' && document.getElementById('forkD')) document.getElementById('forkD').checked = true;
            else if (document.getElementById('forkW')) document.getElementById('forkW').checked = true;
        }
        if (s.forkType && document.getElementById('forklift-type')) document.getElementById('forklift-type').value = s.forkType;
        if (s.forkLiftH && document.getElementById('forklift-lift-height')) document.getElementById('forklift-lift-height').value = s.forkLiftH;
        if (s.forkAst && document.getElementById('forklift-ast')) document.getElementById('forklift-ast').value = s.forkAst;
    }

    // 🌟 ChatWizard 층별 대화 상태 완벽 복원 또는 새 구역 시작
    if (typeof ChatWizard !== 'undefined') {
        if (target.chatHtml) {
            ChatWizard.currentStep = target.chatStep || 1;
            ChatWizard.step2Html = target.step2Html || '';
            if (ChatWizard.body) {
                ChatWizard.body.innerHTML = target.chatHtml;
                ChatWizard.scrollToBottom();
            }
        } else {
            // 새로 추가된 층이거나 아직 대화가 없는 층
            if (typeof ChatWizard.resetForNewFloor === 'function') {
                ChatWizard.resetForNewFloor(target.name);
            }
            // 만약 이미 도면이 폐합되어 점이 찍혀 있다면 즉시 Step 2로 연결!
            if (isClosed && typeof window.generateCustomInputs === 'function') {
                setTimeout(() => {
                    window.generateCustomInputs(points.length - 1, true);
                }, 200);
            }
        }
    }

    // 🌟 복원된 도면의 모든 선분 입력창(edge-input-*)에 저장된 치수값 확실하게 재주입
    const restoreEdgeInputs = () => {
        document.querySelectorAll('input[id^="edge-input-"]').forEach(inputEl => {
            const m = inputEl.id.match(/^edge-input-(\d+)$/);
            if (m) {
                const idx = parseInt(m[1]);
                let val = '';
                if (target.savedEdgeValues && target.savedEdgeValues[idx] !== undefined && target.savedEdgeValues[idx] !== '') {
                    val = target.savedEdgeValues[idx];
                } else if (target.edgeLengths && target.edgeLengths[idx] > 0) {
                    val = target.edgeLengths[idx];
                }
                if (val !== '') {
                    inputEl.value = val;
                    inputEl.setAttribute('value', val);
                }
            }
        });
    };
    restoreEdgeInputs();
    setTimeout(restoreEdgeInputs, 100);
    setTimeout(restoreEdgeInputs, 300);

    // 캔버스는 항상 화면에 보여야 마우스 클릭으로 점을 찍을 수 있음
    const guideEl = document.getElementById('canvas-guide');
    if (guideEl) guideEl.style.display = 'none';
    if (canvas) canvas.style.display = 'block';

    // 캔버스 크기(width/height)만 안전하게 세팅하고, 도면/랙 위치 재정렬(alignAndScalePolygon)은 건너뜀!
    resizeCanvas(false);
    
    // 만약 한 번도 스케일링되지 않은 초기 층인 경우에만 최초 정렬 실행
    if (typeof alignAndScalePolygon === 'function' && points.length >= 4 && edgeLengths.some(v => v > 0) && (!target.currentScale || target.currentScale <= 0)) {
        alignAndScalePolygon();
    }
    activeRack = null;
    selectedRackIndices = [];
    if (typeof draw === 'function') draw();
    if (typeof updateRackFormCounts === 'function') updateRackFormCounts();
    else if (typeof updateRackCounts === 'function') updateRackCounts();
    renderFloorTabs();
}

window.addNewFloor = function(customName = '') {
    saveCurrentFloorState();
    const nextNum = window.canvasFloors.length + 1;
    const newFloor = {
        id: Date.now(),
        name: customName || `${nextNum}층 (제${nextNum}창고)`,
        points: [],
        obstacles: [],
        edgeLengths: [],
        originalAngles: [],
        originalVisualLengths: [],
        userEnteredEdges: [],
        racks: [],
        currentScale: 0,
        isDrawingMode: true,
        cameraZoom: 1,
        rackCustomLevels: null,
        capturedImage: null,
        palletSpec: null,
        chatStep: 1,
        chatHtml: null,
        step2Html: ''
    };
    window.canvasFloors.push(newFloor);
    loadFloorState(window.canvasFloors.length - 1);

    if (typeof window.onFloorAdded === 'function') {
        window.onFloorAdded(newFloor.name);
    }
};

window.switchFloor = function(index) {
    if (index === window.currentFloorIndex) return;
    saveCurrentFloorState();
    loadFloorState(index);
    if (typeof window.onFloorSwitched === 'function') {
        window.onFloorSwitched(window.canvasFloors[index].name);
    }
};

window.removeFloor = function(index, event) {
    if (event) event.stopPropagation();
    if (window.canvasFloors.length <= 1) {
        alert('최소 1개의 층(창고)은 유지되어야 합니다.');
        return;
    }
    const targetName = window.canvasFloors[index].name;
    if (!confirm(`'${targetName}' 탭을 삭제하시겠습니까?\n해당 층에 배치된 도면과 랙 정보가 삭제됩니다.`)) {
        return;
    }

    window.canvasFloors.splice(index, 1);
    if (window.currentFloorIndex >= window.canvasFloors.length) {
        window.currentFloorIndex = window.canvasFloors.length - 1;
    }
    loadFloorState(window.currentFloorIndex);
};

window.renameFloor = function(index, event) {
    if (event) event.stopPropagation();
    const currentName = window.canvasFloors[index].name;
    const newName = prompt('층 또는 창고 구역 이름을 입력하세요:', currentName);
    if (newName && newName.trim() !== '') {
        window.canvasFloors[index].name = newName.trim();
        renderFloorTabs();
    }
};

// 🌟 개별 층(Floor)의 랙 통계(독립, 연결, 바이패스, 파렛트 등) 정밀 계산 함수
function calculateFloorStats(f) {
    if (!f) return { bays: 0, indep: 0, conn: 0, smallConn: 0, bypass: 0, holders: 0, pallets: 0, spec: '' };
    const fRacks = f.racks || [];
    const pSpec = f.palletSpec || {};

    const palletH = parseInt(pSpec.ph) || parseInt(document.getElementById('pallet-h')?.value) || 1000;
    const palletW = parseInt(pSpec.pw) || parseInt(document.getElementById('pallet-w')?.value) || 1100;
    const palletD = parseInt(pSpec.pd) || parseInt(document.getElementById('pallet-d')?.value) || 1100;
    const levels = parseInt(pSpec.levels) || parseInt(document.getElementById('rack-levels')?.value) || 3;

    let rackH = parseInt(pSpec.rackHeight) || parseInt(document.getElementById('rack-height')?.value) || 0;
    if (rackH <= 0) {
        const rawH = (palletH * levels) + (levels * 200) + 300;
        rackH = Math.ceil(rawH / 500) * 500;
    }
    const rackD = parseInt(pSpec.rackDepth) || parseInt(document.getElementById('rack-depth')?.value) || 1000;
    const beamLen = (fRacks.length > 0 && fRacks[0].beamLength) || (palletW * 2) + 385;
    const spanS = Math.max(1, levels - 1);

    let indep = 0;
    let conn = 0;
    let smallConn = 0;
    let bypass = 0;
    let pallets = 0;
    let bays = 0;

    fRacks.forEach(r => {
        let reg = (r.independent || 0) + (r.connected || 0);
        let sm = r.smallConnected || 0;
        let spans = reg + sm;
        let rowCount = r.isDouble ? 2 : 1;

        for (let row = 0; row < rowCount; row++) {
            let rowBypass = (r.bypassBays && Array.isArray(r.bypassBays) && Array.isArray(r.bypassBays[row])) ? r.bypassBays[row] : [];
            let hasBayLevels = r.bayLevels && Array.isArray(r.bayLevels) && r.bayLevels.length > row && Array.isArray(r.bayLevels[row]);

            for (let j = 0; j < reg; j++) {
                let isBp = rowBypass[j] === true;
                let bLvl = (hasBayLevels && r.bayLevels[row][j] !== undefined) ? r.bayLevels[row][j] : (r.levels || levels);
                bays++;

                if (isBp) {
                    bypass += 1;
                    pallets += 2 * Math.max(1, bLvl - 1);
                } else {
                    if (j < (r.independent || 0)) {
                        indep += 1;
                    } else {
                        conn += 1;
                    }
                    pallets += 2 * bLvl;
                }
            }

            for (let j = reg; j < reg + sm; j++) {
                let isBp = rowBypass[j] === true;
                let bLvl = (hasBayLevels && r.bayLevels[row][j] !== undefined) ? r.bayLevels[row][j] : (r.levels || levels);
                bays++;

                if (isBp) {
                    bypass += 1;
                    pallets += 2 * Math.max(1, bLvl - 1);
                } else {
                    smallConn += 1;
                    pallets += 2 * bLvl;
                }
            }
        }
    });

    let holders = Math.floor(pallets / 4);
    let specText = `${beamLen}×${rackD}×${rackH} (${spanS}S ${levels}단)`;

    return {
        bays,
        indep,
        conn,
        smallConn,
        bypass,
        holders,
        pallets,
        beamLen,
        rackD,
        rackH,
        levels,
        spanS,
        spec: specText
    };
}
window.calculateFloorStats = calculateFloorStats;

function renderFloorTabs() {
    const container = document.getElementById('floor-tabs-container');
    if (!container) return;

    let html = '';
    window.canvasFloors.forEach((f, idx) => {
        const isActive = idx === window.currentFloorIndex;
        const activeClass = isActive ? 'active' : '';
        const stats = calculateFloorStats(f);
        const rowCount = f.racks ? f.racks.length : 0;
        
        // 탭 뱃지: 실제 설치되는 총 베이 수 및 파렛트 수량 표시
        const badgeTitle = `${f.name}: 랙 ${rowCount}개 열, 총 ${stats.bays}대 (독립 ${stats.indep}, 연결 ${stats.conn}, 바이패스 ${stats.bypass}) / ${stats.pallets.toLocaleString()} PLT`;
        const badgeHtml = stats.bays > 0 
            ? `<span class="excel-tab-badge ms-1" title="${badgeTitle}">${stats.bays}대</span>` 
            : (rowCount > 0 ? `<span class="excel-tab-badge ms-1">${rowCount}열</span>` : '');

        html += `
            <div class="excel-tab-group ${activeClass}" 
                 onclick="switchFloor(${idx})" 
                 title="클릭하여 '${f.name}' 선택 (더블클릭 시 이름 변경)"
                 ondblclick="renameFloor(${idx}, event)">
                <div class="excel-tab-shape">
                    <div class="excel-tab-inner">
                        <i class="fa-solid fa-layer-group me-1 opacity-75"></i>
                        <span class="excel-tab-name">${f.name}</span>
                        ${badgeHtml}
                        <button type="button" class="excel-tab-btn-icon ms-1" 
                                onclick="renameFloor(${idx}, event)" title="이름 수정">
                            <i class="fa-solid fa-pen"></i>
                        </button>
                        ${window.canvasFloors.length > 1 ? `
                        <button type="button" class="excel-tab-btn-icon excel-tab-btn-del ms-1" 
                                onclick="removeFloor(${idx}, event)" title="층 삭제">
                            <i class="fa-solid fa-xmark"></i>
                        </button>` : ''}
                    </div>
                </div>
            </div>
        `;
    });

    html += `
        <button type="button" class="excel-tab-add-btn" 
                onclick="addNewFloor()" title="새로운 층이나 분리된 창고 구역 추가">
            <i class="fa-solid fa-plus me-1"></i> 층/창고 추가
        </button>
    `;

    // 전체 다중 층 통합 뱃지 요약
    if (window.canvasFloors.length > 1) {
        let totalAllBays = 0;
        let totalAllPallets = 0;
        window.canvasFloors.forEach(f => {
            let st = calculateFloorStats(f);
            totalAllBays += st.bays;
            totalAllPallets += st.pallets;
        });
        html += `
            <div class="ms-auto d-flex align-items-center gap-2">
                <div class="excel-tab-summary shadow-sm">
                    <i class="fa-solid fa-building me-1"></i> 총 <strong class="text-white">${window.canvasFloors.length}개 층</strong> 통합: 
                    <span class="text-white">랙 ${totalAllBays}대</span> / <span class="text-success fw-bold">${totalAllPallets.toLocaleString()} PLT</span>
                </div>
            </div>
        `;
    }

    container.innerHTML = html;
}

window.renderFloorTabs = renderFloorTabs;
window.saveCurrentFloorState = saveCurrentFloorState;
window.loadFloorState = loadFloorState;

// 전체 층 통합 요약 데이터 산출 함수 (견적 제출 및 모달용)
window.getCombinedFloorsSummary = function() {
    saveCurrentFloorState();

    // 🌟 모든 층의 도면 스냅샷(capturedImage)이 빠짐없이 생성되어 있는지 전수 검사 및 보강
    if (window.canvasFloors && window.canvasFloors.length > 1) {
        const originalFloorIdx = window.currentFloorIndex;
        window.canvasFloors.forEach((f, idx) => {
            if (!f.capturedImage && f.points && f.points.length > 0) {
                try {
                    loadFloorState(idx);
                    if (typeof draw === 'function') draw();
                    const snap = captureFloorSnapshot();
                    if (snap) f.capturedImage = snap;
                } catch(e) {
                    console.error("Floor snapshot capture error:", e);
                }
            }
        });
        // 원래 작업 중이던 층으로 복귀
        if (window.currentFloorIndex !== originalFloorIdx) {
            loadFloorState(originalFloorIdx);
            if (typeof draw === 'function') draw();
        }
    }

    let grandSummary = {
        totalFloors: window.canvasFloors.length,
        floors: [],
        grandIndep: 0,
        grandConn: 0,
        grandSmallConn: 0,
        grandBypass: 0,
        grandTieHolders: 0,
        grandPallets: 0,
        grandBays: 0
    };

    window.canvasFloors.forEach((f, idx) => {
        const stats = calculateFloorStats(f);

        grandSummary.grandIndep += stats.indep;
        grandSummary.grandConn += stats.conn;
        grandSummary.grandSmallConn += stats.smallConn;
        grandSummary.grandBypass += stats.bypass;
        grandSummary.grandTieHolders += stats.holders;
        grandSummary.grandPallets += stats.pallets;
        grandSummary.grandBays += stats.bays;

        grandSummary.floors.push({
            id: f.id,
            index: idx,
            name: f.name,
            bays: stats.bays,
            indep: stats.indep,
            conn: stats.conn,
            smallConn: stats.smallConn,
            bypass: stats.bypass,
            holders: stats.holders,
            pallets: stats.pallets,
            spec: stats.spec,
            edgeLengths: f.edgeLengths || [],
            userEnteredEdges: f.userEnteredEdges || [],
            palletSpec: f.palletSpec || {},
            capturedImage: f.capturedImage
        });
    });

    grandSummary.totalIndep = grandSummary.grandIndep;
    grandSummary.totalConn = grandSummary.grandConn;
    grandSummary.totalSmallConn = grandSummary.grandSmallConn;
    grandSummary.totalBypass = grandSummary.grandBypass;
    grandSummary.totalHolders = grandSummary.grandTieHolders;
    grandSummary.totalPallets = grandSummary.grandPallets;
    grandSummary.totalBays = grandSummary.grandBays;

    return grandSummary;
};

// DOM 로드 시 탭 바 자동 렌더링
document.addEventListener('DOMContentLoaded', () => {
    setTimeout(() => {
        if (typeof renderFloorTabs === 'function') renderFloorTabs();
    }, 100);
});

// ==========================================
// 📥 도면 다중 포맷 내보내기 (JPG, PDF, DXF)
// ==========================================
window.exportCanvas = function(format) {
    if (!points || points.length === 0) {
        alert("도면이 비어있습니다. 도면을 먼저 그려주세요.");
        return;
    }

    if (format === 'jpg') {
        let targetCanvas = null;
        if (typeof window.getCroppedCanvas === 'function') {
            targetCanvas = window.getCroppedCanvas({ padding: 60, isPrintMode: false, quality: 1.0 });
        }
        if (!targetCanvas) {
            targetCanvas = document.createElement('canvas');
            targetCanvas.width = canvas.width;
            targetCanvas.height = canvas.height;
            const tCtx = targetCanvas.getContext('2d');
            tCtx.fillStyle = isCanvasLightMode() ? '#f8fafc' : '#0f172a';
            tCtx.fillRect(0, 0, targetCanvas.width, targetCanvas.height);
            tCtx.drawImage(canvas, 0, 0);
        }

        const dataURL = targetCanvas.toDataURL("image/jpeg", 1.0);
        const link = document.createElement('a');
        link.download = `스마트도면_${Date.now()}.jpg`;
        link.href = dataURL;
        link.click();
    } 
    else if (format === 'pdf') {
        if (typeof window.jspdf === 'undefined') {
            alert("PDF 라이브러리를 불러오는 중입니다. 잠시 후 다시 시도해주세요.");
            return;
        }
        
        let targetCanvas = null;
        if (typeof window.getCroppedCanvas === 'function') {
            targetCanvas = window.getCroppedCanvas({ padding: 60, isPrintMode: true, quality: 1.0 });
        }
        if (!targetCanvas) {
            targetCanvas = document.createElement('canvas');
            targetCanvas.width = canvas.width;
            targetCanvas.height = canvas.height;
            const tCtx = targetCanvas.getContext('2d');
            tCtx.fillStyle = isCanvasLightMode() ? '#ffffff' : '#0f172a';
            tCtx.fillRect(0, 0, targetCanvas.width, targetCanvas.height);
            tCtx.drawImage(canvas, 0, 0);
        }

        const imgData = targetCanvas.toDataURL("image/jpeg", 1.0);
        
        const { jsPDF } = window.jspdf;
        // A4 Landscape
        const pdf = new jsPDF({
            orientation: 'landscape',
            unit: 'mm',
            format: 'a4'
        });

        const pdfWidth = pdf.internal.pageSize.getWidth();
        const pdfHeight = pdf.internal.pageSize.getHeight();
        const imgProps = pdf.getImageProperties(imgData);
        
        const margin = 10;
        const maxWidth = pdfWidth - margin * 2;
        const maxHeight = pdfHeight - margin * 2 - 20; // 제목 여백

        let renderWidth = maxWidth;
        let renderHeight = (imgProps.height * renderWidth) / imgProps.width;

        if (renderHeight > maxHeight) {
            renderHeight = maxHeight;
            renderWidth = (imgProps.width * renderHeight) / imgProps.height;
        }

        const x = margin + (maxWidth - renderWidth) / 2;
        const y = margin + 15 + (maxHeight - renderHeight) / 2;

        pdf.setFont("helvetica", "bold");
        pdf.setTextColor(isCanvasLightMode() ? 0 : 255);
        if (!isCanvasLightMode()) {
            pdf.setFillColor(15, 23, 42);
            pdf.rect(0, 0, pdfWidth, pdfHeight, "F");
        }

        pdf.setFontSize(16);
        pdf.text("Warehouse Rack Layout Plan", pdfWidth / 2, margin + 5, { align: "center" });

        pdf.addImage(imgData, 'JPEG', x, y, renderWidth, renderHeight);
        pdf.save(`스마트도면_${Date.now()}.pdf`);
    }
    else if (format === 'dxf') {
        const dxfString = generateDXF();
        const blob = new Blob([dxfString], { type: 'text/plain' });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.download = `스마트도면_${Date.now()}.dxf`;
        link.href = url;
        link.click();
        URL.revokeObjectURL(url);
    }
};

// DXF 문자열 생성기 (경량 내장 엔진)
function generateDXF() {
    let out = [];
    
    // --- Header ---
    out.push("  0", "SECTION", "  2", "HEADER");
    out.push("  9", "$ACADVER", "  1", "AC1009");
    out.push("  0", "ENDSEC");
    
    // --- Tables ---
    out.push("  0", "SECTION", "  2", "TABLES");
    
    // Layers
    out.push("  0", "TABLE", "  2", "LAYER", " 70", "3");
    
    out.push("  0", "LAYER", "  2", "WALLS", " 70", "0", " 62", "7", "  6", "CONTINUOUS"); // 7=White/Black
    out.push("  0", "LAYER", "  2", "RACKS", " 70", "0", " 62", "5", "  6", "CONTINUOUS"); // 5=Blue
    out.push("  0", "LAYER", "  2", "TEXT", " 70", "0", " 62", "2", "  6", "CONTINUOUS"); // 2=Yellow
    out.push("  0", "LAYER", "  2", "OBSTACLES", " 70", "0", " 62", "1", "  6", "CONTINUOUS"); // 1=Red
    
    out.push("  0", "ENDTAB");
    out.push("  0", "ENDSEC");

    // --- Entities ---
    out.push("  0", "SECTION", "  2", "ENTITIES");

    // DXF는 Y축이 위로 증가하지만, Canvas는 Y축이 아래로 증가하므로 -y 변환 적용
    function addLine(layer, x1, y1, x2, y2) {
        out.push("  0", "LINE", "  8", layer);
        out.push(" 10", x1.toFixed(2), " 20", (-y1).toFixed(2), " 30", "0.0");
        out.push(" 11", x2.toFixed(2), " 21", (-y2).toFixed(2), " 31", "0.0");
    }

    function addText(layer, x, y, textStr, height) {
        out.push("  0", "TEXT", "  8", layer);
        out.push(" 10", x.toFixed(2), " 20", (-y).toFixed(2), " 30", "0.0");
        out.push(" 40", height.toFixed(2));
        out.push("  1", textStr);
        out.push(" 50", "0.0");
    }

    // 1. 벽면 (WALLS)
    if (points && points.length > 0) {
        for (let i = 0; i < points.length - 1; i++) {
            addLine("WALLS", points[i].x, points[i].y, points[i+1].x, points[i+1].y);
        }
        if (isPolygonClosed && isPolygonClosed()) {
            addLine("WALLS", points[points.length-1].x, points[points.length-1].y, points[0].x, points[0].y);
        }
    }

    // 2. 파렛트랙 (RACKS)
    if (racks && racks.length > 0) {
        racks.forEach(r => {
            const w = r.totalLengthPx || 100;
            const singleDepthPx = (r.rackDepth || 1000) * currentScale;
            const h = r.isDouble ? (singleDepthPx * 2 + ((r.holderSize||200) * currentScale)) : singleDepthPx;
            
            const angle = r.angle || 0;
            const cos = Math.cos(angle);
            const sin = Math.sin(angle);
            
            const pts = [
                { dx: 0, dy: -h/2 },
                { dx: w, dy: -h/2 },
                { dx: w, dy: h/2 },
                { dx: 0, dy: h/2 }
            ];

            const rotatedPts = pts.map(p => ({
                x: r.x + p.dx * cos - p.dy * sin,
                y: r.y + p.dx * sin + p.dy * cos
            }));

            for(let i=0; i<4; i++) {
                addLine("RACKS", rotatedPts[i].x, rotatedPts[i].y, rotatedPts[(i+1)%4].x, rotatedPts[(i+1)%4].y);
            }

            const reg = (r.independent || 0) + (r.connected || 0);
            const sm = r.smallConnected || 0;
            
            // 텍스트는 랙의 시작 지점 중심 부근에 배치
            const textX = r.x + (20 * cos);
            const textY = r.y + (20 * sin);
            addText("TEXT", textX, textY, `${reg+sm}Bays`, 12);
        });
    }

    // 3. 내부 기둥 및 장애물 (OBSTACLES)
    if (pillars && pillars.length > 0) {
        pillars.forEach(p => {
            const w = p.width * currentScale;
            const h = p.height * currentScale;
            const pts = [
                {x: p.x, y: p.y},
                {x: p.x + w, y: p.y},
                {x: p.x + w, y: p.y + h},
                {x: p.x, y: p.y + h}
            ];
            for(let i=0; i<4; i++) {
                addLine("OBSTACLES", pts[i].x, pts[i].y, pts[(i+1)%4].x, pts[(i+1)%4].y);
            }
            addText("TEXT", p.x + w/2 - 15, p.y + h/2, p.type || 'Pillar', 10);
        });
    }

    out.push("  0", "ENDSEC");
    out.push("  0", "EOF");

    return out.join("\n");
}

// 🔄 도면 데이터 전체 복원 전역 함수 (외부 canvas-restore.js 등에서 호출)
window.restoreCanvasData = function(raw) {
    if (!raw) return false;
    try {
        console.log("🔄 canvas2d 내부 도면 복원 실행 시작...", raw);

        // 1. 도면 점 (points)
        if (raw.points && Array.isArray(raw.points) && raw.points.length > 0) {
            points = JSON.parse(JSON.stringify(raw.points));
        }

        // 2. 장애물 (obstacles)
        if (raw.obstacles && Array.isArray(raw.obstacles)) {
            obstacles = JSON.parse(JSON.stringify(raw.obstacles));
        }

        // 3. 랙 데이터 (racks)
        if (raw.racks && Array.isArray(raw.racks)) {
            racks = JSON.parse(JSON.stringify(raw.racks));
        }

        // 4. 스케일 복원
        if (raw.currentScale && parseFloat(raw.currentScale) > 0) {
            currentScale = parseFloat(raw.currentScale);
            window.currentScale = currentScale;
        }

        // 5. 각도 및 시각적 길이 계산 (points 기반)
        if (points.length >= 4) {
            originalAngles = [];
            originalVisualLengths = [];
            const numEdges = points.length - 1;
            for (let i = 0; i < numEdges; i++) {
                const p1 = points[i];
                const p2 = points[i + 1];
                const rawAngle = Math.atan2(p2.y - p1.y, p2.x - p1.x);
                let finalAngle = rawAngle;
                const snap45 = Math.PI / 4;
                const nearest45 = Math.round(rawAngle / snap45) * snap45;
                let diff45 = Math.abs(rawAngle - nearest45);
                while (diff45 > Math.PI) diff45 = Math.abs(diff45 - Math.PI * 2);
                if (diff45 <= 6 * (Math.PI / 180)) {
                    finalAngle = nearest45;
                }
                originalAngles.push(finalAngle);
                originalVisualLengths.push(Math.hypot(p2.x - p1.x, p2.y - p1.y));
            }
        }

        // 6. 선분 길이 (edgeLengths)
        if (raw.edgeLengths && Array.isArray(raw.edgeLengths) && raw.edgeLengths.length > 0) {
            edgeLengths = [...raw.edgeLengths];
        } else if (raw.edge_lengths_str) {
            edgeLengths = raw.edge_lengths_str.split(',').map(v => parseInt(v.trim()) || 0);
        }

        // 7. 모드 플래그 정상화 (그리기 모드 해제, 랙 이동 및 마우스 이벤트 활성화)
        isDrawingMode = false;
        isMovingRack = false;
        isExtendingRack = false;
        window.activeInteractMode = null;

        // 8. 캔버스 화면 노출 및 리사이즈
        const guideEl = document.getElementById('canvas-guide');
        if (guideEl) guideEl.style.display = 'none';
        if (canvas) canvas.style.display = 'block';
        resizeCanvas(false);

        // 8-1. 🌟 창고 원점(0, 0) 동기화 및 뷰포트 중앙 쾌적 정렬 (눈금자 0점 일치 & 사방 여유 공간 확보)
        if (points && points.length >= 2) {
            let pMinX = Infinity, pMinY = Infinity, pMaxX = -Infinity, pMaxY = -Infinity;
            points.forEach(p => {
                if (p && typeof p.x === 'number') {
                    if (p.x < pMinX) pMinX = p.x;
                    if (p.x > pMaxX) pMaxX = p.x;
                }
                if (p && typeof p.y === 'number') {
                    if (p.y < pMinY) pMinY = p.y;
                    if (p.y > pMaxY) pMaxY = p.y;
                }
            });

            if (isFinite(pMinX) && isFinite(pMinY) && isFinite(pMaxX) && isFinite(pMaxY)) {
                const parent = canvas.parentElement;
                const containerW = parent ? parent.clientWidth : (baseWidth || window.innerWidth);
                const containerH = parent ? parent.clientHeight : (baseHeight || window.innerHeight);

                const chatEl = document.getElementById('chat-wizard-container');
                const isChatVisible = chatEl && chatEl.style.display !== 'none' && !chatEl.classList.contains('d-none');
                const rightMargin = isChatVisible ? 420 : 100;
                const leftMargin = 90;
                const topMargin = 75;
                const bottomMargin = 220;

                const safeWidth = Math.max(300, containerW - leftMargin - rightMargin);
                const safeHeight = Math.max(300, containerH - topMargin - bottomMargin);

                const polyWidth = Math.max(10, pMaxX - pMinX);
                const polyHeight = Math.max(10, pMaxY - pMinY);

                // 이상적인 창고 좌상단 위치 (상단 35% : 하단 65% 비율 배치)
                const targetOriginX = leftMargin + Math.max(20, (safeWidth - polyWidth) / 2);
                const targetOriginY = topMargin + Math.max(20, (safeHeight - polyHeight) * 0.35);

                const deltaX = targetOriginX - pMinX;
                const deltaY = targetOriginY - pMinY;

                // 창고 점들, 랙, 장애물 전체를 화면 보기 좋은 위치로 이동
                if (Math.abs(deltaX) > 0.01 || Math.abs(deltaY) > 0.01) {
                    points.forEach(p => {
                        p.x += deltaX;
                        p.y += deltaY;
                    });
                    if (racks && Array.isArray(racks)) {
                        racks.forEach(r => {
                            r.x += deltaX;
                            r.y += deltaY;
                        });
                    }
                    if (obstacles && Array.isArray(obstacles)) {
                        obstacles.forEach(obs => {
                            obs.x += deltaX;
                            obs.y += deltaY;
                        });
                    }
                }

                // 🌟 눈금자의 원점(0, 0)을 창고 좌상단 꼭짓점 위치와 100% 일치시킴!
                window.canvasOriginX = targetOriginX;
                window.canvasOriginY = targetOriginY;
            }
        }

        // 스케일 재확인 및 보정
        if ((!currentScale || currentScale <= 0) && typeof alignAndScalePolygon === 'function' && points.length >= 4 && edgeLengths.some(v => v > 0)) {
            alignAndScalePolygon();
        } else if (!currentScale || currentScale <= 0) {
            currentScale = 0.05;
            window.currentScale = 0.05;
        }

        // 랙 객체별 totalLengthPx 및 각도 보정
        if (racks && racks.length > 0) {
            racks.forEach(r => {
                if (typeof getRackTotalLengthPx === 'function') {
                    r.totalLengthPx = getRackTotalLengthPx(r);
                }
            });
        }

        // 9. 캔버스 및 뱃지 다시 그리기
        if (typeof draw === 'function') draw();
        if (typeof updateRackFormCounts === 'function') updateRackFormCounts();

        console.log("✅ canvas2d 도면 복원 완료! currentScale =", currentScale, "racks =", racks.length);
        return true;
    } catch(err) {
        console.error("❌ restoreCanvasData 실패:", err);
        return false;
    }
};
