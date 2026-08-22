
function drawGridBackground() {
    if (currentScale <= 0) return;
    const gridLineMm = cameraZoom >= 1.5 ? 100 : 500;
    const textStepMm = cameraZoom >= 1.5 ? 500 : 1000;
    const linePx = gridLineMm * currentScale;
    
    const parent = canvas.parentElement;
    const viewTop = parent.scrollTop / cameraZoom;
    const viewLeft = parent.scrollLeft / cameraZoom;
    
    let startX = (0 % linePx) - linePx;
    let startY = (0 % linePx) - linePx;
    
    ctx.save();
    ctx.scale(cameraZoom, cameraZoom);
    
    const maxW = canvas.width / cameraZoom;
    const maxH = canvas.height / cameraZoom;
    
    // 1. Grid Lines
    ctx.strokeStyle = 'rgba(255, 255, 255, 0.1)';
    ctx.lineWidth = 1 / cameraZoom;
    ctx.beginPath();
    for (let x = startX; x <= maxW + linePx; x += linePx) {
        ctx.moveTo(x, 0);
        ctx.lineTo(x, maxH);
    }
    for (let y = startY; y <= maxH + linePx; y += linePx) {
        ctx.moveTo(0, y);
        ctx.lineTo(maxW, y);
    }
    ctx.stroke();

    // 2. Ruler Background Panels
    const rulerThickTop = 22 / cameraZoom;
    const rulerThickLeft = 45 / cameraZoom;
    ctx.fillStyle = 'rgba(20, 25, 35, 0.9)'; 
    
    ctx.fillRect(viewLeft, viewTop, parent.clientWidth / cameraZoom, rulerThickTop);
    ctx.fillRect(viewLeft, viewTop, rulerThickLeft, parent.clientHeight / cameraZoom);

    // 3. Ruler Text
    ctx.fillStyle = 'rgba(255, 255, 255, 0.9)';
    ctx.font = (10 / cameraZoom) + 'px sans-serif';
    ctx.textAlign = 'left';
    ctx.textBaseline = 'top';
    
    for (let x = startX; x <= maxW + linePx; x += linePx) {
        let logicalMm = Math.round(x / currentScale) - 2000;
        if (Math.abs(logicalMm) % textStepMm === 0 && x > viewLeft + rulerThickLeft) {
            ctx.fillText(logicalMm, x + 4 / cameraZoom, viewTop + 6 / cameraZoom);
        }
    }
    for (let y = startY; y <= maxH + linePx; y += linePx) {
        let logicalMm = Math.round(y / currentScale) - 2000;
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
let isDrawingRack = false;
let rackStartX = 0;
let rackStartY = 0;
let currentRackPreview = null;
let currentScale = 0; // pixels per mm (도면이 정렬되어야 값이 생김)
window.currentScale = 0;

let isMovingRack = false;
let rackDragOffsetX = 0;
let rackDragOffsetY = 0;



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

function getLogicalPos(e) {
    const rawX = e.offsetX / cameraZoom;
    const rawY = e.offsetY / cameraZoom;
    if (typeof getGridSnapPx === 'function' && currentScale > 0) {
        const snapPx = getGridSnapPx();
        return {
            x: Math.round(rawX / snapPx) * snapPx,
            y: Math.round(rawY / snapPx) * snapPx
        };
    }
    return { x: rawX, y: rawY };
}

// 스냅 가이드 토글 함수
function toggleSnapGuide() {
    snapGuideEnabled = !snapGuideEnabled;
    draw();
}

// 레티나 디스플레이 및 사이즈 맞춤 설정
function resizeCanvas() {
    const parent = canvas.parentElement;
    baseWidth = parent.clientWidth;
    baseHeight = parent.clientHeight;
    applyZoom();
}
window.addEventListener('resize', resizeCanvas);

window.zoomIn = function() {
    cameraZoom = Math.min(cameraZoom * 1.2, 5);
    applyZoom();
};

window.zoomOut = function() {
    cameraZoom = Math.max(cameraZoom / 1.2, 0.5);
    applyZoom();
};

window.resetZoom = function() {
    cameraZoom = 1;
    applyZoom();
};

// 리모컨 방향 이동: panCanvas(dx, dy) - 스크롤 단위(px)
window.panCanvas = function(dx, dy) {
    const parent = canvas.parentElement;
    if (parent) {
        parent.scrollLeft = Math.max(0, parent.scrollLeft + dx);
        parent.scrollTop  = Math.max(0, parent.scrollTop  + dy);
    }
};

function applyZoom(oldZoom = null) {
    if (baseWidth === 0) return;
    const parent = canvas.parentElement;
    
    let viewCenterX = parent.scrollLeft + parent.clientWidth / 2;
    let viewCenterY = parent.scrollTop + parent.clientHeight / 2;
    
    if (window.lastMouseX !== undefined && window.lastMouseY !== undefined) {
        viewCenterX = window.lastMouseX;
        viewCenterY = window.lastMouseY;
    }

    // Remember screen position of the target point
    let screenMouseX = viewCenterX - parent.scrollLeft;
    let screenMouseY = viewCenterY - parent.scrollTop;

    canvas.width = baseWidth * cameraZoom;
    canvas.height = baseHeight * cameraZoom;
    
    if (points.length >= 3 && currentScale > 0) {
        alignAndScalePolygon();
    }
    draw();
    
    if (oldZoom && oldZoom > 0) {
        const zoomRatio = cameraZoom / oldZoom;
        parent.scrollLeft = (viewCenterX * zoomRatio) - screenMouseX;
        parent.scrollTop = (viewCenterY * zoomRatio) - screenMouseY;
    } else {
        if (cameraZoom > 1) {
            parent.scrollLeft = (canvas.width - parent.clientWidth) / 2;
            parent.scrollTop = (canvas.height - parent.clientHeight) / 2;
        } else {
            parent.scrollLeft = 0;
            parent.scrollTop = 0;
        }
    }
}

// 전역 함수로 노출
window.zoomIn = function() {
    const oldZoom = cameraZoom;
    cameraZoom = Math.min(cameraZoom * 1.2, 5);
    applyZoom(oldZoom);
};

window.zoomOut = function() {
    const oldZoom = cameraZoom;
    cameraZoom = Math.max(cameraZoom / 1.2, 0.5);
    applyZoom(oldZoom);
};

window.resetZoom = function() {
    cameraZoom = 1;
    applyZoom();
};

// 리모컨 방향 이동: panCanvas(dx, dy) - 스크롤 단위(px)
window.panCanvas = function(dx, dy) {
    const parent = canvas.parentElement;
    if (parent) {
        parent.scrollLeft = Math.max(0, parent.scrollLeft + dx);
        parent.scrollTop  = Math.max(0, parent.scrollTop  + dy);
    }
};

function applyZoom() {
    if (baseWidth === 0) return;
    canvas.width = baseWidth * cameraZoom;
    canvas.height = baseHeight * cameraZoom;
    if (points.length >= 3 && currentScale > 0) {
        alignAndScalePolygon();
    }
    draw();
    
    // 중앙 스크롤 유지
    const parent = canvas.parentElement;
    if (cameraZoom > 1) {
        parent.scrollLeft = (canvas.width - parent.clientWidth) / 2;
        parent.scrollTop = (canvas.height - parent.clientHeight) / 2;
    } else {
        parent.scrollLeft = 0;
        parent.scrollTop = 0;
    }
}

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
    const valInt = parseInt(value) || 0;
    
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

    const viewWidth = baseWidth - 200;
    const viewHeight = baseHeight - 200;
    currentScale = Math.min(viewWidth / (maxX - minX || 1), viewHeight / (maxY - minY || 1));
    window.currentScale = currentScale;

    const centerX = baseWidth / 2;
    const centerY = baseHeight / 2;
    let offsetX = (-minX + 2000) * currentScale;
    let offsetY = (-minY + 2000) * currentScale;
    window.globalPolyMinX = 2000;
    window.globalPolyMinY = 2000;

    for(let i = 0; i <= numEdges; i++) {
        points[i].x = mathPoints[i].x * currentScale + offsetX;
        points[i].y = mathPoints[i].y * currentScale + offsetY;
    }

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
        }
    });
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
    const lenPx = r.totalLengthPx;
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
    const tailX = r.x + Math.cos(angle) * r.totalLengthPx;
    const tailY = r.y + Math.sin(angle) * r.totalLengthPx;
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
                    // 1) 회전 핸들 (10도 회전 - 리모컨 모드에 맞게 작동하며 Shift 클릭 시 방향 전환)
                    if (handles.rotate && Math.hypot(handles.rotate.x - clickX, handles.rotate.y - clickY) <= 15) {
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
                    if (handles.extend && Math.hypot(handles.extend.x - clickX, handles.extend.y - clickY) <= 15) {
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
                    if (handles.copy && Math.hypot(handles.copy.x - clickX, handles.copy.y - clickY) <= 15) {
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
                if (Math.hypot(tail.x - clickX, tail.y - clickY) <= 20) {
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
        
        // 마그네틱 스냅 로직 적용
        let snappedPos = calculateRackSnap(targetX, targetY, currentRackPreview);
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
        isExtendingRack = false;
        extendingRackIndex = -1;
        updateRackFormCounts();
        draw();
    }

    if (draggingObstacleIndex !== -1) {
        draggingObstacleIndex = -1;
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
                angle: closestLine.angle, length: 2000,
                edgeIndex: closestLine.edgeIndex,
                ratioOnEdge: closestLine.ratioOnEdge,
                visualDistFromStart: closestLine.visualDistFromStart,
                visualEdgeLength: closestLine.visualEdgeLength
            });
        } else {
            obstacles.push({ type: draggedItemType, name: typeLabel + dCount, x: dropX, y: dropY, angle: 0, length: 2000, edgeIndex: -1 });
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

// 마그네틱 스냅 로직
function calculateRackSnap(targetX, targetY, r) {
    let snappedX = targetX;
    let snappedY = targetY;
    const SNAP_DIST = 25; // 스냅 발동 픽셀 반경
    const depthPx = getRackTotalDepthPx(r);

    // 현재 좌표 기준 임시 랙 및 회전 바운딩 박스
    const tempRack = { ...r, x: targetX, y: targetY };
    const tempBoxes = getRackBoxes(tempRack);
    let minX = tempBoxes.physical.minX;
    let maxX = tempBoxes.physical.maxX;
    let minY = tempBoxes.physical.minY;
    let maxY = tempBoxes.physical.maxY;

    for (let i = 0; i < points.length - 1; i++) {
        const p1 = points[i];
        const p2 = points[i+1];
        let CLEARANCE = getEdgeClearance(p1, p2);
        
        // 수직 벽면
        if (Math.abs(p1.x - p2.x) < 5) {
            let wallX = p1.x;
            if (Math.abs(minX - (wallX + CLEARANCE)) < SNAP_DIST) {
                snappedX += (wallX + CLEARANCE) - minX;
            } else if (Math.abs(maxX - (wallX - CLEARANCE)) < SNAP_DIST) {
                snappedX += (wallX - CLEARANCE) - maxX;
            }
            
            // Y축 정중앙 스냅
            let wallCenterY = (p1.y + p2.y) / 2;
            let rackCenterY = (minY + maxY) / 2;
            if (Math.abs(rackCenterY - wallCenterY) < SNAP_DIST) {
                snappedY += wallCenterY - rackCenterY;
            }
        }
        
        // 수평 벽면
        if (Math.abs(p1.y - p2.y) < 5) {
            let wallY = p1.y;
            if (Math.abs(minY - (wallY + CLEARANCE)) < SNAP_DIST) {
                snappedY += (wallY + CLEARANCE) - minY;
            } else if (Math.abs(maxY - (wallY - CLEARANCE)) < SNAP_DIST) {
                snappedY += (wallY - CLEARANCE) - maxY;
            }
            
            // X축 정중앙 스냅
            let wallCenterX = (p1.x + p2.x) / 2;
            let rackCenterX = (minX + maxX) / 2;
            if (Math.abs(rackCenterX - wallCenterX) < SNAP_DIST) {
                snappedX += wallCenterX - rackCenterX;
            }
        }
    }
    
    // 랙 간 합체 스냅(머리-꼬리 연결) 및 통로 간격 스냅(2800mm)
    const AISLE_DIST_PX = 2800 * currentScale;
    
    for (let i = 0; i < racks.length; i++) {
        let placed = racks[i];
        if (placed === r) continue;
        
        const placedBoxes = getRackBoxes(placed);
        
        // --- 머리-꼬리 합체 스냅 ---
        const placedAngle = getRackAngle(placed);
        const curAngle = getRackAngle(r);
        if (Math.abs(placedAngle - curAngle) < 0.05) {
            const placedTail = getRackTailPos(placed);
            
            // 내 머리가 상대방 꼬리에 스냅
            if (Math.hypot(targetX - placedTail.x, targetY - placedTail.y) < SNAP_DIST) {
                snappedX = placedTail.x;
                snappedY = placedTail.y;
                break;
            }
            
            // 내 꼬리가 상대방 머리에 스냅
            const myTail = getRackTailPos(tempRack);
            if (Math.hypot(myTail.x - placed.x, myTail.y - placed.y) < SNAP_DIST) {
                snappedX = placed.x - (myTail.x - targetX);
                snappedY = placed.y - (myTail.y - targetY);
                break;
            }
        }
        
        // --- 통로 간격(Aisle) 2800mm 스냅 ---
        if (placed.isHoriz === r.isHoriz) {
            const placedDepthPx = getRackTotalDepthPx(placed);
            
            if (r.isHoriz) {
                // X 구간 겹침 확인
                let myMinX = minX;
                let myMaxX = maxX;
                let pMinX = placedBoxes.physical.minX;
                let pMaxX = placedBoxes.physical.maxX;
                
                if (!(myMaxX < pMinX || myMinX > pMaxX)) {
                    let distTop = Math.abs((placed.y - placedDepthPx/2) - (targetY + depthPx/2));
                    let distBottom = Math.abs((placed.y + placedDepthPx/2) - (targetY - depthPx/2));
                    
                    if (Math.abs(distTop - AISLE_DIST_PX) < SNAP_DIST) {
                        snappedY = placed.y - placedDepthPx/2 - AISLE_DIST_PX - depthPx/2;
                    } else if (Math.abs(distBottom - AISLE_DIST_PX) < SNAP_DIST) {
                        snappedY = placed.y + placedDepthPx/2 + AISLE_DIST_PX + depthPx/2;
                    }
                }
            } else {
                // Y 구간 겹침 확인
                let myMinY = minY;
                let myMaxY = maxY;
                let pMinY = placedBoxes.physical.minY;
                let pMaxY = placedBoxes.physical.maxY;
                
                if (!(myMaxY < pMinY || myMinY > pMaxY)) {
                    let distLeft = Math.abs((placed.x - placedDepthPx/2) - (targetX + depthPx/2));
                    let distRight = Math.abs((placed.x + placedDepthPx/2) - (targetX - depthPx/2));
                    
                    if (Math.abs(distLeft - AISLE_DIST_PX) < SNAP_DIST) {
                        snappedX = placed.x - placedDepthPx/2 - AISLE_DIST_PX - depthPx/2;
                    } else if (Math.abs(distRight - AISLE_DIST_PX) < SNAP_DIST) {
                        snappedX = placed.x + placedDepthPx/2 + AISLE_DIST_PX + depthPx/2;
                    }
                }
            }
        }
    }
    
    // --- 통로 중앙 스냅 (평균 분배) ---
    if (r.isHoriz) {
        let myMinX = minX;
        let myMaxX = maxX;
        
        let boundTop = null; 
        let boundBottom = null;
        
        // 벽면 체크
        for (let i = 0; i < points.length - 1; i++) {
            let p1 = points[i]; let p2 = points[i+1];
            if (Math.abs(p1.y - p2.y) < 5) {
                let wallY = p1.y;
                let wallMinX = Math.min(p1.x, p2.x);
                let wallMaxX = Math.max(p1.x, p2.x);
                if (!(myMaxX < wallMinX || myMinX > wallMaxX)) {
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
        
        // 장애물(출입구) 체크
        obstacles.forEach(obs => {
            if (obs.type === 'door') {
                let w = (obs.length || 2000) * currentScale;
                let clearance = 2800 * currentScale;
                let isHorizDoor = Math.abs(Math.cos(obs.angle)) > 0.5;
                if (isHorizDoor) {
                    let obsMinX = obs.x - w/2;
                    let obsMaxX = obs.x + w/2;
                    if (!(myMaxX < obsMinX || myMinX > obsMaxX)) {
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
        
        // 다른 랙 체크
        for (let placed of racks) {
            if (placed === r) continue;
            const placedBoxes = getRackBoxes(placed);
            let pMinX = placedBoxes.physical.minX;
            let pMaxX = placedBoxes.physical.maxX;
            if (!(myMaxX < pMinX || myMinX > pMaxX)) {
                let placedDepthPx = getRackTotalDepthPx(placed);
                
                if (placed.y < targetY) {
                    let effY = placed.y + placedDepthPx/2;
                    if (boundTop === null || effY > boundTop) boundTop = effY;
                } else {
                    let effY = placed.y - placedDepthPx/2;
                    if (boundBottom === null || effY < boundBottom) boundBottom = effY;
                }
            }
        }
        
        if (boundTop !== null && boundBottom !== null) {
            let centerSpaceY = (boundTop + boundBottom) / 2;
            if (Math.abs(targetY - centerSpaceY) < SNAP_DIST) {
                snappedY = centerSpaceY;
            }
        }
    } else { // 수직 배치
        let myMinY = minY;
        let myMaxY = maxY;
        
        let boundLeft = null; 
        let boundRight = null;
        
        // 벽면 체크
        for (let i = 0; i < points.length - 1; i++) {
            let p1 = points[i]; let p2 = points[i+1];
            if (Math.abs(p1.x - p2.x) < 5) {
                let wallX = p1.x;
                let wallMinY = Math.min(p1.y, p2.y);
                let wallMaxY = Math.max(p1.y, p2.y);
                if (!(myMaxY < wallMinY || myMinY > wallMaxY)) {
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
        
        // 장애물(출입구) 체크
        obstacles.forEach(obs => {
            if (obs.type === 'door') {
                let w = (obs.length || 2000) * currentScale;
                let clearance = 2800 * currentScale;
                let isHorizDoor = Math.abs(Math.cos(obs.angle)) > 0.5;
                if (!isHorizDoor) {
                    let obsMinY = obs.y - w/2;
                    let obsMaxY = obs.y + w/2;
                    if (!(myMaxY < obsMinY || myMinY > obsMaxY)) {
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
        
        // 다른 랙 체크
        for (let placed of racks) {
            if (placed === r) continue;
            const placedBoxes = getRackBoxes(placed);
            let pMinY = placedBoxes.physical.minY;
            let pMaxY = placedBoxes.physical.maxY;
            if (!(myMaxY < pMinY || myMinY > pMaxY)) {
                let placedDepthPx = getRackTotalDepthPx(placed);
                
                if (placed.x < targetX) {
                    let effX = placed.x + placedDepthPx/2;
                    if (boundLeft === null || effX > boundLeft) boundLeft = effX;
                } else {
                    let effX = placed.x - placedDepthPx/2;
                    if (boundRight === null || effX < boundRight) boundRight = effX;
                }
            }
        }
        
        if (boundLeft !== null && boundRight !== null) {
            let centerSpaceX = (boundLeft + boundRight) / 2;
            if (Math.abs(targetX - centerSpaceX) < SNAP_DIST) {
                snappedX = centerSpaceX;
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
    
    // 조건 A: 겹침 및 코너 여유공간 (Dead Space), 그리고 통로(Aisle) 간격 검사
    const AISLE_DIST_PX = 2800 * currentScale;
    
    for(let r of racks) {
        if (r === newRack || r === ignoreRack) continue; // 자기 자신과의 비교 제외
        
        let existBoxes = getRackBoxes(r);
        // 새로운 랙의 물리적 박스가 기존 랙의 물리적 박스를 침범하면 불합격
        if (checkBoxesOverlap(boxes.physical, existBoxes.physical)) {
            return false;
        }
        
        // 통로 간격 검사 (2800mm 룰)
        if (newRack.isHoriz === r.isHoriz) {
            let placedDepthPx = getRackTotalDepthPx(r);
            let newDepthPx = getRackTotalDepthPx(newRack);
            
            if (newRack.isHoriz) {
                let myMinX = newRack.dir > 0 ? newRack.x : newRack.x - newRack.totalLengthPx;
                let myMaxX = newRack.dir > 0 ? newRack.x + newRack.totalLengthPx : newRack.x;
                let pMinX = r.dir > 0 ? r.x : r.x - r.totalLengthPx;
                let pMaxX = r.dir > 0 ? r.x + r.totalLengthPx : r.x;
                
                if (!(myMaxX < pMinX || myMinX > pMaxX)) {
                    let dist = Math.abs(newRack.y - r.y) - (newDepthPx/2 + placedDepthPx/2);
                    if (dist > -5 && dist < AISLE_DIST_PX - 5) {
                        return false;
                    }
                }
            } else {
                let myMinY = newRack.dir > 0 ? newRack.y : newRack.y - newRack.totalLengthPx;
                let myMaxY = newRack.dir > 0 ? newRack.y + newRack.totalLengthPx : newRack.y;
                let pMinY = r.dir > 0 ? r.y : r.y - r.totalLengthPx;
                let pMaxY = r.dir > 0 ? r.y + r.totalLengthPx : r.y;
                
                if (!(myMaxY < pMinY || myMinY > pMaxY)) {
                    let dist = Math.abs(newRack.x - r.x) - (newDepthPx/2 + placedDepthPx/2);
                    if (dist > -5 && dist < AISLE_DIST_PX - 5) {
                        return false;
                    }
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
    
    // 조건 D: 창고 다각형 경계 내부 검사
    const ptsToCheck = [
        [boxes.physical.minX, boxes.physical.minY],
        [boxes.physical.maxX, boxes.physical.minY],
        [boxes.physical.minX, boxes.physical.maxY],
        [boxes.physical.maxX, boxes.physical.maxY]
    ];
    for(let pt of ptsToCheck) {
        if (!isPointInPolygon(pt, points)) {
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
    
    ctx.save();
    ctx.translate(r.x, r.y);
    
    // 회전 처리
    ctx.rotate(getRackAngle(r));

    if (isPreview) {
        ctx.globalAlpha = 0.45; // 투명처리된 복사된 랙 스타일 적용
    }

    // 1. 배경 채우기
    if (isPreview) {
        ctx.fillStyle = r.isValid ? 'rgba(56, 189, 248, 0.25)' : 'rgba(239, 68, 68, 0.25)';
    } else {
        ctx.fillStyle = 'rgba(15, 23, 42, 0.6)'; // 어두운 반투명
    }
    ctx.fillRect(0, -depthPx/2, r.totalLengthPx, depthPx);

    // 2. 가로 로드빔 평행선 그리기 (이중 선)
    ctx.strokeStyle = isPreview && !r.isValid ? 'rgba(239, 68, 68, 0.8)' : '#0ea5e9';
    ctx.lineWidth = 2;
    
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
    ctx.fillStyle = isPreview ? 'rgba(56, 189, 248, 0.7)' : '#0ea5e9';
    ctx.strokeStyle = '#fff';
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
            ctx.fillStyle = isPreview ? 'rgba(56, 189, 248, 0.7)' : '#0ea5e9';
            ctx.strokeStyle = '#fff';
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
    if (!isPreview) {
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
            const smallStartPx = colSizePx + (regularSpans * beamPx);
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
                        ctx.fillStyle = 'rgba(15, 23, 42, 0.85)';
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
        
        ctx.fillStyle = 'rgba(15, 23, 42, 0.8)';
        ctx.fillRect(midX - 12, midY - 12, 24, 24);
        
        ctx.fillStyle = '#38bdf8';
        ctx.fillText(i + 1, midX, midY);
    }

    // 3.5. 코너 Dead Zone 표시 (랙이 배치된 경우에만 표시)
    if (isPolygonClosed() && racks.length > 0 && currentScale > 0) {
        const rackDepthMm = getRackDepth ? getRackDepth() : 1000;
        const cornerMm = rackDepthMm + 100; // 코너 Dead Zone = 랙깊이 + 100mm
        const cornerPx = cornerMm * currentScale;
        
        ctx.save();
        ctx.fillStyle = 'rgba(251, 191, 36, 0.08)';   // 연한 황금색 배경
        ctx.strokeStyle = 'rgba(251, 191, 36, 0.35)';
        ctx.setLineDash([4, 4]);
        ctx.lineWidth = 1;
        
        for (let i = 0; i < points.length - 1; i++) {
            const p1 = points[i];
            const p2 = points[i + 1];
            const wallLenPx = Math.hypot(p2.x - p1.x, p2.y - p1.y);
            if (wallLenPx < cornerPx * 2) continue; // 벽이 너무 짧으면 스킵
            
            const angle = Math.atan2(p2.y - p1.y, p2.x - p1.x);
            const isHoriz = Math.abs(Math.cos(angle)) > 0.5;
            const dir = isHoriz ? (p2.x > p1.x ? 1 : -1) : (p2.y > p1.y ? 1 : -1);
            
            // 안쪽 방향 판별
            const midX = (p1.x + p2.x) / 2;
            const midY = (p1.y + p2.y) / 2;
            const rackHalfPx = (rackDepthMm / 2) * currentScale;
            const pillarClear = 100 * currentScale;
            let insetDir = 0;
            if (isHoriz) {
                insetDir = isPointInPolygon([midX, midY + rackHalfPx + pillarClear], points) ? 1 : -1;
            } else {
                insetDir = isPointInPolygon([midX + rackHalfPx + pillarClear, midY], points) ? 1 : -1;
            }
            
            // 코너 Dead Zone 박스 2개 (시작점 쪽 + 끝점 쪽)
            const depthPx = (rackDepthMm) * currentScale; // Dead Zone 깊이 = 랙 깊이
            
            [-1, 1].forEach(side => {
                // side=-1: p1 코너, side=1: p2 코너
                const cornerX = side === -1 ? p1.x : p2.x;
                const cornerY = side === -1 ? p1.y : p2.y;
                
                let zx, zy, zw, zh;
                if (isHoriz) {
                    const dx = side === -1 ? dir : -dir;
                    zx = cornerX;
                    zy = cornerY - (insetDir > 0 ? depthPx : 0) - (insetDir > 0 ? 0 : -depthPx);
                    // 간략화: 코너에서 cornerPx 만큼 가로, depthPx 만큼 세로
                    if (dx * dir > 0) {
                        // 벽 방향으로 cornerPx 만큼
                        if (insetDir > 0) {
                            ctx.fillRect(cornerX, cornerY, dir * cornerPx, insetDir * depthPx);
                            ctx.strokeRect(cornerX, cornerY, dir * cornerPx, insetDir * depthPx);
                        }
                    }
                } else {
                    if (insetDir !== 0) {
                        ctx.fillRect(cornerX - (insetDir > 0 ? depthPx : 0), cornerY, insetDir * depthPx, dir * cornerPx * side);
                        ctx.strokeRect(cornerX - (insetDir > 0 ? depthPx : 0), cornerY, insetDir * depthPx, dir * cornerPx * side);
                    }
                }
            });
        }
        ctx.setLineDash([]);
        ctx.restore();
    }

    // 4. 파렛트랙 그룹들 그리기
    racks.forEach((r, idx) => drawRackGroup(r, false, idx));

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
            
            if (obs.edgeIndex !== undefined && obs.edgeIndex !== -1 && edgeLengths[obs.edgeIndex]) {
                const realWallLength = edgeLengths[obs.edgeIndex];
                if (realWallLength > 0) {
                    const ratio = obs.visualEdgeLength / realWallLength;
                    visualLength = Math.max(obs.length * ratio, 10); 
                    
                    const realOffset = Math.round(obs.visualDistFromStart / ratio);
                    realOffsetMsg = `(좌측 ${realOffset}mm)`;
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
    
    // 수평 배치된 랙들 Y축 정렬
    let horizRacks = racks.filter(r => r.isHoriz).sort((a,b) => a.y - b.y);
    for(let i=0; i<horizRacks.length-1; i++) {
        let r1 = horizRacks[i];
        let r2 = horizRacks[i+1];
        
        let r1MinX = r1.dir > 0 ? r1.x : r1.x - r1.totalLengthPx;
        let r1MaxX = r1.dir > 0 ? r1.x + r1.totalLengthPx : r1.x;
        let r2MinX = r2.dir > 0 ? r2.x : r2.x - r2.totalLengthPx;
        let r2MaxX = r2.dir > 0 ? r2.x + r2.totalLengthPx : r2.x;
        
        let overlapMinX = Math.max(r1MinX, r2MinX);
        let overlapMaxX = Math.min(r1MaxX, r2MaxX);
        
        if (overlapMaxX > overlapMinX) {
            let cx = (overlapMinX + overlapMaxX) / 2;
            
            let r1OuterDepth = r1.rackDepth || 1100;
            let r1DepthPx = r1OuterDepth * (r1.isDouble ? 2 : 1) * currentScale;
            let r2OuterDepth = r2.rackDepth || 1100;
            let r2DepthPx = r2OuterDepth * (r2.isDouble ? 2 : 1) * currentScale;
            
            let y1 = r1.y + r1DepthPx/2;
            let y2 = r2.y - r2DepthPx/2;
            
            if (y2 > y1) {
                let distPx = y2 - y1;
                let distMm = Math.round(distPx / currentScale);
                
                // 화살표 선
                ctx.beginPath();
                ctx.moveTo(cx, y1);
                ctx.lineTo(cx, y2);
                ctx.strokeStyle = 'rgba(251, 146, 60, 0.7)'; // 주황색 톤
                ctx.lineWidth = 1;
                ctx.stroke();
                
                // 화살표 머리
                ctx.beginPath(); ctx.moveTo(cx-3, y1+5); ctx.lineTo(cx, y1); ctx.lineTo(cx+3, y1+5); ctx.stroke();
                ctx.beginPath(); ctx.moveTo(cx-3, y2-5); ctx.lineTo(cx, y2); ctx.lineTo(cx+3, y2-5); ctx.stroke();
                
                // 텍스트 배경 및 출력
                ctx.fillStyle = 'rgba(15, 23, 42, 0.8)';
                let textW = ctx.measureText(`${distMm}mm`).width + 10;
                ctx.fillRect(cx - textW/2, (y1+y2)/2 - 10, textW, 20);
                
                ctx.fillStyle = '#fdba74';
                ctx.fillText(`${distMm}mm`, cx, (y1+y2)/2);
            }
        }
    }
    
    // 수직 배치된 랙들 X축 정렬
    let vertRacks = racks.filter(r => !r.isHoriz).sort((a,b) => a.x - b.x);
    for(let i=0; i<vertRacks.length-1; i++) {
        let r1 = vertRacks[i];
        let r2 = vertRacks[i+1];
        
        let r1MinY = r1.dir > 0 ? r1.y : r1.y - r1.totalLengthPx;
        let r1MaxY = r1.dir > 0 ? r1.y + r1.totalLengthPx : r1.y;
        let r2MinY = r2.dir > 0 ? r2.y : r2.y - r2.totalLengthPx;
        let r2MaxY = r2.dir > 0 ? r2.y + r2.totalLengthPx : r2.y;
        
        let overlapMinY = Math.max(r1MinY, r2MinY);
        let overlapMaxY = Math.min(r1MaxY, r2MaxY);
        
        if (overlapMaxY > overlapMinY) {
            let cy = (overlapMinY + overlapMaxY) / 2;
            
            let r1OuterDepth = r1.rackDepth || 1100;
            let r1DepthPx = r1OuterDepth * (r1.isDouble ? 2 : 1) * currentScale;
            let r2OuterDepth = r2.rackDepth || 1100;
            let r2DepthPx = r2OuterDepth * (r2.isDouble ? 2 : 1) * currentScale;
            
            let x1 = r1.x + r1DepthPx/2;
            let x2 = r2.x - r2DepthPx/2;
            
            if (x2 > x1) {
                let distPx = x2 - x1;
                let distMm = Math.round(distPx / currentScale);
                
                ctx.beginPath();
                ctx.moveTo(x1, cy);
                ctx.lineTo(x2, cy);
                ctx.strokeStyle = 'rgba(251, 146, 60, 0.7)';
                ctx.lineWidth = 1;
                ctx.stroke();
                
                ctx.beginPath(); ctx.moveTo(x1+5, cy-3); ctx.lineTo(x1, cy); ctx.lineTo(x1+5, cy+3); ctx.stroke();
                ctx.beginPath(); ctx.moveTo(x2-5, cy-3); ctx.lineTo(x2, cy); ctx.lineTo(x2-5, cy+3); ctx.stroke();
                
                ctx.fillStyle = 'rgba(15, 23, 42, 0.8)';
                let textW = ctx.measureText(`${distMm}mm`).width + 10;
                ctx.fillRect((x1+x2)/2 - textW/2, cy - 10, textW, 20);
                
                ctx.fillStyle = '#fdba74';
                ctx.fillText(`${distMm}mm`, (x1+x2)/2, cy);
            }
        }
    }
    
    // 출입문과 마주보는 랙 간의 거리 표시
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

            racks.forEach(r => {
                let rOuterDepth = r.rackDepth || 1100;
                let rDepthPx = rOuterDepth * (r.isDouble ? 2 : 1) * currentScale;
                
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
                        
                        ctx.fillStyle = 'rgba(15, 23, 42, 0.8)';
                        let textW = ctx.measureText(`${distMm}mm`).width + 10;
                        ctx.fillRect(cx - textW/2, (y1+y2)/2 - 10, textW, 20);
                        ctx.fillStyle = '#fdba74';
                        ctx.fillText(`${distMm}mm`, cx, (y1+y2)/2);
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
                        
                        ctx.fillStyle = 'rgba(15, 23, 42, 0.8)';
                        let textW = ctx.measureText(`${distMm}mm`).width + 10;
                        ctx.fillRect((x1+x2)/2 - textW/2, cy - 10, textW, 20);
                        ctx.fillStyle = '#fdba74';
                        ctx.fillText(`${distMm}mm`, (x1+x2)/2, cy);
                    }
                }
            }
        }
    });


    ctx.restore();
}

// 랙 수량을 폼 및 상단 대시보드에 실시간 종합 업데이트
window.updateRackFormCounts = function() {
    let totalIndep = 0;
    let totalConn = 0;
    let totalSmallConn = 0;
    let totalBypass = 0;
    let totalTieHolders = 0;
    let totalBays = 0;
    
    // 설치 단수 (기본 3단)
    const levelsInput = document.getElementById('rack-levels');
    const levels = levelsInput ? (parseInt(levelsInput.value) || 3) : 3;

    let totalRegularBays = 0;
    let totalSmallBays = 0;
    
    let bypassRegularBays = 0;
    let bypassSmallBays = 0;

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

            for (let j = 0; j < reg; j++) {
                if (r.bypassBays[row][j]) {
                    bypassRegularBays += 1;
                } else {
                    totalRegularBays += 1;
                }
            }
            for (let j = reg; j < reg + sm; j++) {
                if (r.bypassBays[row][j]) {
                    bypassSmallBays += 1;
                } else {
                    totalSmallBays += 1;
                }
            }
        }

        if (r.isDouble) {
            // 복렬(복수) 랙 1세트: 단열 2라인 결합
            totalIndep += (r.independent || 0) * 2;
            totalConn += (r.connected || 0) * 2;
            totalSmallConn += sm * 2;
            totalBays += spans * 2;
            
            // 고정 홀더(Spacer): 기둥 열 수(spans + 1) * 2개 (상/하 2개씩)
            if (spans > 0) {
                totalTieHolders += (spans + 1) * 2;
            }
        } else {
            // 단열 랙
            totalIndep += r.independent || 0;
            totalConn += r.connected || 0;
            totalSmallConn += sm;
            totalBays += spans;
        }
    });

    totalBypass = bypassRegularBays + bypassSmallBays;
    
    // 바이패스 수량만큼 연결 대수에서 차감 (DB 및 화면 표기용)
    if (bypassRegularBays > 0) {
        if (bypassRegularBays <= totalConn) {
            totalConn -= bypassRegularBays;
        } else {
            let rem = bypassRegularBays - totalConn;
            totalConn = 0;
            totalIndep = Math.max(0, totalIndep - rem);
        }
    }
    if (bypassSmallBays > 0) {
        totalSmallConn = Math.max(0, totalSmallConn - bypassSmallBays);
    }
    
    // 총 적재 파렛트 수량: (일반 정규베이 * 2 + 일반 작은연결베이 * 1) * levels + (바이패스 정규베이 * 2 + 바이패스 작은연결베이 * 1) * (levels - 1)
    const bypassLevels = Math.max(1, levels - 1);
    const totalPallets = ((totalRegularBays * 2 + totalSmallBays * 1) * levels) +
                         ((bypassRegularBays * 2 + bypassSmallBays * 1) * bypassLevels);
    
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
    const beamLen = (window.rackSpecs && window.rackSpecs.beamLength) || (currentPalletW * 2) + 385;
    const rackD = (window.rackSpecs && window.rackSpecs.rackDepth) || (currentPalletD - 100);
    const palletH = parseInt(document.getElementById('pallet-h')?.value) || 1200;
    let rackH = parseInt(document.getElementById('rack-height')?.value) || 0;
    if (rackH <= 0) {
        const rawH = (palletH * levels) + (levels * 200) + 300;
        rackH = Math.ceil(rawH / 500) * 500;
    }
    const spanS = Math.max(1, levels - 1); 
    const specTagText = `${beamLen}×${rackD}×${rackH} (${spanS}S ${levels}단)`;
    
    let specDetailText = `규격: ${beamLen}(W) × ${rackD}(D) × ${rackH}(H) , ${spanS}S ${levels}단`;
    if (totalBypass > 0) {
        const bpS = Math.max(1, spanS - 1);
        const bpLevels = Math.max(1, levels - 1);
        specDetailText += `<br>규격: ${beamLen}(W) × ${rackD}(D) × ${rackH}(H) , ${bpS}S ${bpLevels}단 (바이패스 ${totalBypass}대)`;
    }

    if(dBaysBadge) dBaysBadge.innerText = `총 ${totalBays + totalBypass}칸 (${spanS}S ${levels}단)`;
    const dSpecDetail = document.getElementById('rack-spec-detail-badge');
    if(dSpecDetail) dSpecDetail.innerHTML = specDetailText;

    // 3. 캔버스 상단 실시간 대시보드 뱃지 바 업데이트 (줄바꿈 두 줄 포맷 지원)
    const summaryBadge = document.getElementById('canvas-summary-badge');
    if (summaryBadge) {
        // 첫 번째 줄 빌드
        let html = `<div class="d-flex align-items-center gap-2 flex-wrap">
            <span id="top-badge-spec" class="badge bg-primary text-white" style="font-size:0.75rem; font-weight:600; padding:4px 8px; letter-spacing:0.02em;">${specTagText}</span>
            <span class="text-secondary">|</span>
            <span>독립 <strong id="top-badge-indep" class="text-primary">${totalIndep}</strong>대</span>
            <span class="text-secondary">|</span>
            <span>연결 <strong id="top-badge-conn" class="text-primary">${totalConn}</strong>대</span>`;
        
        if (totalSmallConn > 0) {
            html += ` <span class="text-secondary">|</span> <span class="text-info">작은연결 <strong id="top-badge-small-conn" class="text-info">${totalSmallConn}</strong>대</span>`;
        }
        
        html += ` <span class="text-secondary">|</span> <span class="text-warning">🔗 <strong id="top-badge-holders" class="text-warning">${totalTieHolders}</strong>홀더</span>
            <span class="text-secondary">|</span>
            <span class="text-success">📦 <strong id="top-badge-pallets" class="text-success">${totalPallets.toLocaleString()}</strong> PLT</span>
        </div>`;
        
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
        
        summaryBadge.innerHTML = html;
        summaryBadge.classList.remove('d-none');
        summaryBadge.style.display = 'flex';
        summaryBadge.style.flexDirection = 'column';
        summaryBadge.style.alignItems = 'flex-start';
    }
};

// 좌측 폼 영역에 장애물 설정 UI 동적 렌더링
window.renderObstacleInputs = function() {
    const container = document.getElementById('obstacle-inputs-container');
    if (!container) return;
    
    container.innerHTML = ''; 

    obstacles.forEach((obs, index) => {
        const itemDiv = document.createElement('div');
        itemDiv.className = 'd-flex align-items-center gap-2 p-2 rounded-2 mt-2';
        itemDiv.style.background = 'rgba(255, 255, 255, 0.05)';
        itemDiv.style.border = '1px solid rgba(255,255,255,0.1)';

        const isDoorLike = obs.type === 'door' || obs.type === 'shutter';
        const nameColor = isDoorLike ? 'text-warning' : 'text-danger';

        if (isDoorLike) {
            itemDiv.innerHTML = `
                <span class="${nameColor} fw-bold small" style="min-width:60px;">${obs.name}</span>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-transparent text-secondary border-secondary">길이</span>
                    <input type="number" class="form-control bg-transparent text-white border-secondary" value="${obs.length}" onclick="this.select()" oninput="updateObstacleData(${index}, 'length', this.value)">
                </div>
                <button class="btn btn-sm btn-outline-danger px-2 py-1" onclick="deleteObstacle(${index})">✕</button>
            `;
        } else {
            itemDiv.innerHTML = `
                <span class="${nameColor} fw-bold small" style="min-width:60px;">${obs.name}</span>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-transparent text-secondary border-secondary">가로</span>
                    <input type="number" class="form-control bg-transparent text-white border-secondary" value="${obs.width}" onclick="this.select()" oninput="updateObstacleData(${index}, 'width', this.value)">
                </div>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-transparent text-secondary border-secondary">세로</span>
                    <input type="number" class="form-control bg-transparent text-white border-secondary" value="${obs.height}" onclick="this.select()" oninput="updateObstacleData(${index}, 'height', this.value)">
                </div>
                <button class="btn btn-sm btn-outline-danger px-2 py-1" onclick="deleteObstacle(${index})">✕</button>
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
    const beamLength = (palletW * 2) + 385;
    const rackDepth = palletD - 100;
    const smallBeamLength = 1385;
    
    // 독립 1칸 길이
    const totalLenMm = 85 + (1 * beamLength);
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

    let horizCount = 0;
    racks.forEach(r => { if(r.isHoriz) horizCount++; });
    const alignHoriz = horizCount >= racks.length / 2;

    let singleRacks = racks.filter(r => !r.isDouble && r.isHoriz === alignHoriz);
    let topY = pMinY + 100 * currentScale;
    let bottomY = pMaxY - 100 * currentScale;
    let leftX = pMinX + 100 * currentScale;
    let rightX = pMaxX - 100 * currentScale;

    if (alignHoriz) {
        singleRacks.forEach(r => {
            const depth = (r.rackDepth || 1000) * currentScale;
            if (r.y < (pMinY + pMaxY) / 2) {
                if (r.y + depth > topY) topY = r.y + depth;
            } else {
                if (r.y < bottomY) bottomY = r.y;
            }
        });
    } else {
        singleRacks.forEach(r => {
            const depth = (r.rackDepth || 1000) * currentScale;
            if (r.x < (pMinX + pMaxX) / 2) {
                if (r.x + depth > leftX) leftX = r.x + depth;
            } else {
                if (r.x < rightX) rightX = r.x;
            }
        });
    }

    let doubleRacks = racks.filter(r => r.isDouble && r.isHoriz === alignHoriz);
    if (doubleRacks.length > 0) {
        if (alignHoriz) {
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

            let maxDepthBottom = 0;
            rows[rows.length-1].forEach(r => {
                const depth = ((r.rackDepth||1000) * 2 + (r.holderSize||200)) * currentScale;
                if(depth > maxDepthBottom) maxDepthBottom = depth;
            });
            bottomY -= maxDepthBottom;
            const wCenter = (pMinX + pMaxX)/2;

            if (rows.length === 1) {
                rows[0].forEach(r => {
                    r.y = (topY + bottomY) / 2;
                    const isFlipped = Math.cos(getRackAngle(r)) < -0.1;
                    r.x = isFlipped ? wCenter + r.totalLengthPx/2 : wCenter - r.totalLengthPx/2;
                });
            } else {
                const gap = (bottomY - topY) / (rows.length + 1);
                rows.forEach((row, idx) => {
                    let targetY = topY + gap * (idx + 1);
                    for(let i=0; i<idx; i++) {
                        let d = 0;
                        rows[i].forEach(r => d = Math.max(d, ((r.rackDepth||1000)*2 + (r.holderSize||200))*currentScale));
                        targetY += d;
                    }
                    row.forEach(r => {
                        r.y = targetY;
                        const isFlipped = Math.cos(getRackAngle(r)) < -0.1;
                        r.x = isFlipped ? wCenter + r.totalLengthPx/2 : wCenter - r.totalLengthPx/2;
                    });
                });
            }
        } else {
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

            let maxDepthRight = 0;
            cols[cols.length-1].forEach(r => {
                const depth = ((r.rackDepth||1000) * 2 + (r.holderSize||200)) * currentScale;
                if(depth > maxDepthRight) maxDepthRight = depth;
            });
            rightX -= maxDepthRight;
            const wCenterY = (pMinY + pMaxY)/2;

            if (cols.length === 1) {
                cols[0].forEach(r => {
                    r.x = (leftX + rightX) / 2;
                    const isFlippedVert = Math.sin(getRackAngle(r)) < -0.1;
                    r.y = isFlippedVert ? wCenterY + r.totalLengthPx/2 : wCenterY - r.totalLengthPx/2;
                });
            } else {
                const gap = (rightX - leftX) / (cols.length + 1);
                cols.forEach((col, idx) => {
                    let targetX = leftX + gap * (idx + 1);
                    for(let i=0; i<idx; i++) {
                        let d = 0;
                        cols[i].forEach(r => d = Math.max(d, ((r.rackDepth||1000)*2 + (r.holderSize||200))*currentScale));
                        targetX += d;
                    }
                    col.forEach(r => {
                        r.x = targetX;
                        const isFlippedVert = Math.sin(getRackAngle(r)) < -0.1;
                        r.y = isFlippedVert ? wCenterY + r.totalLengthPx/2 : wCenterY - r.totalLengthPx/2;
                    });
                });
            }
        }
    }
    
    draw();
};


window.getGridSnapPx = function() {
    if (currentScale <= 0) return 1;
    const snapMm = cameraZoom >= 1.5 ? 100 : 500;
    return snapMm * currentScale;
};

canvas.addEventListener('mousemove', e => {
    window.lastMouseX = e.offsetX;
    window.lastMouseY = e.offsetY;
});

// FIXED by Heidi - global exposure
window.racks = typeof racks !== 'undefined' ? racks : [];
window.currentScale = typeof currentScale !== 'undefined' ? currentScale : 0;
window.cameraZoom = typeof cameraZoom !== 'undefined' ? cameraZoom : 1;
