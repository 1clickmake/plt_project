// 창고 도면 직접 그리기 (Custom Canvas Drawing) 엔진

const canvas = document.getElementById('drawingCanvas');
const ctx = canvas.getContext('2d');
let isDrawingMode = false;
let points = [];
let obstacles = []; // 기둥과 문을 저장하는 배열
let edgeLengths = []; // 사용자가 입력한 실제 선분 길이 (mm) 배열
let originalAngles = []; // 처음에 그려진 스냅된 각도 보관
let mousePos = null;

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
    return {
        x: e.offsetX / cameraZoom,
        y: e.offsetY / cameraZoom
    };
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
    resizeCanvas();
    renderObstacleInputs();
    updateRackFormCounts();
};

window.stopCustomDrawing = function() {
    isDrawingMode = false;
};


// 🔄 초기화 함수
window.resetCanvas = function() {
    points = [];
    obstacles = [];
    racks = [];
    edgeLengths = [];
    originalAngles = [];
    currentScale = 0;
    isDrawingMode = true;
    document.getElementById('inputs-container').innerHTML = '';
    const diagramContainer = document.getElementById('diagram-container');
    diagramContainer.innerHTML = '<p class="text-info fw-bold py-3 m-0">우측 캔버스에 점을 찍어 창고 모양을 완성해주세요!</p>';
    draw();
    renderObstacleInputs();
    updateRackFormCounts();
};

// 엣지(선분) 길이 저장
window.clearEdgeLengths = function() {
    edgeLengths = [];
};

window.updateEdgeLength = function(index, value) {
    edgeLengths[index] = parseInt(value) || 0;
    alignAndScalePolygon(); 
    draw();
};

// --- 도면 자동 정렬 (Parametric Alignment) ---
function alignAndScalePolygon() {
    if (points.length < 4 || originalAngles.length === 0) return;
    const numEdges = points.length - 1;

    let hasEnteredLength = edgeLengths.some(l => l > 0);
    if (!hasEnteredLength) return;

    let ratio = 100; 
    for(let i = 0; i < numEdges; i++) {
        if(edgeLengths[i] > 0) {
            const visualLen = Math.hypot(points[i+1].x - points[i].x, points[i+1].y - points[i].y);
            ratio = edgeLengths[i] / (visualLen || 1);
            break;
        }
    }

    let mathPoints = [{x: 0, y: 0}];
    for (let i = 0; i < numEdges; i++) {
        let len = edgeLengths[i] > 0 ? edgeLengths[i] : (Math.hypot(points[i+1].x - points[i].x, points[i+1].y - points[i].y) * ratio);
        let angle = originalAngles[i];
        
        let nx = mathPoints[i].x + len * Math.cos(angle);
        let ny = mathPoints[i].y + len * Math.sin(angle);
        
        if (i === numEdges - 1) {
            nx = 0;
            ny = 0;
            
            const calculatedLen = Math.round(Math.hypot(mathPoints[i].x, mathPoints[i].y));
            if (!edgeLengths[i] || edgeLengths[i] <= 0) {
                edgeLengths[i] = calculatedLen;
                const inputs = document.querySelectorAll('#inputs-container input[type="number"]');
                if (inputs[i]) inputs[i].value = calculatedLen;
            }
        } else {
            if (!edgeLengths[i] || edgeLengths[i] <= 0) {
                const estimatedLen = Math.round(len);
                edgeLengths[i] = estimatedLen;
                const inputs = document.querySelectorAll('#inputs-container input[type="number"]');
                if (inputs[i]) inputs[i].value = estimatedLen;
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

    const centerX = baseWidth / 2;
    const centerY = baseHeight / 2;
    const offsetX = centerX - ((minX + maxX) / 2) * currentScale;
    const offsetY = centerY - ((minY + maxY) / 2) * currentScale;

    for(let i = 0; i <= numEdges; i++) {
        points[i].x = mathPoints[i].x * currentScale + offsetX;
        points[i].y = mathPoints[i].y * currentScale + offsetY;
    }

    obstacles.forEach(obs => {
        if (obs.type === 'door' && obs.edgeIndex !== undefined && obs.edgeIndex >= 0 && obs.edgeIndex < numEdges) {
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
            totalLengthPx: totalLenMm * currentScale
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
        let newX = r.x + (r.isHoriz ? r.dir * offsetPx : 0);
        let newY = r.y + (!r.isHoriz ? r.dir * offsetPx : 0);
        
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
            totalLengthPx: totalLenMm * currentScale
        };
        newBack.isValid = checkRackValidPlacement(newBack);
        racks.push(newBack);
    }
    
    updateRackFormCounts();
    draw();
}

canvas.addEventListener('mousedown', (e) => {
    const {x: clickX, y: clickY} = getLogicalPos(e);

    // 1. 이미 배치된 랙 그룹 삭제 확인
    if (!isDrawingMode && currentScale > 0) {
        for (let i = racks.length - 1; i >= 0; i--) {
            const r = racks[i];
            const depthPx = (r.rackDepth || 1000) * currentScale;
            
            // 바운딩 박스 판별
            let minX = r.isHoriz ? (r.dir > 0 ? r.x : r.x - r.totalLengthPx) : (r.x - depthPx/2);
            let maxX = r.isHoriz ? (r.dir > 0 ? r.x + r.totalLengthPx : r.x) : (r.x + depthPx/2);
            let minY = r.isHoriz ? (r.y - depthPx/2) : (r.dir > 0 ? r.y : r.y - r.totalLengthPx);
            let maxY = r.isHoriz ? (r.y + depthPx/2) : (r.dir > 0 ? r.y + r.totalLengthPx : r.y);
            
            // 약간의 터치 여유(padding)
            const pad = 5;
            if (clickX >= minX - pad && clickX <= maxX + pad && clickY >= minY - pad && clickY <= maxY + pad) {
                // 랙 드래그(이동) 모드 진입
                isMovingRack = true;
                rackDragOffsetX = r.x - clickX;
                rackDragOffsetY = r.y - clickY;
                
                // 기존 배열에서 빼내서 currentRackPreview로 띄움
                currentRackPreview = racks.splice(i, 1)[0];
                updateRackFormCounts();
                draw(); // 버그 픽스: 즉시 화면 갱신
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

    // 4. 랙 배치 모드 시작 (도면 완성 후 빈 공간 드래그)
    if (!isDrawingMode && currentScale > 0) {
        isDrawingRack = true;
        rackStartX = clickX;
        rackStartY = clickY;
        currentRackPreview = null;
    } else if (!isDrawingMode && currentScale === 0) {
        alert("좌측 폼에 선분 길이(mm)를 입력하여 도면을 정렬한 후 랙을 배치해주세요!");
    }
});

canvas.addEventListener('mousemove', (e) => {
    const {x: currentX, y: currentY} = getLogicalPos(e);

    // 장애물 드래그 중
    if (draggingObstacleIndex !== -1) {
        const obs = obstacles[draggingObstacleIndex];
        const targetX = currentX + dragOffsetX;
        const targetY = currentY + dragOffsetY;
        
        if (obs.type === 'pillar') {
            let snappedPos = calculatePillarSnap(targetX, targetY, obs);
            obs.x = snappedPos.x;
            obs.y = snappedPos.y;
        } else if (obs.type === 'door') {
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

    // 랙 새로 그리기 모드
    if (isDrawingRack && currentScale > 0) {
        const dx = currentX - rackStartX;
        const dy = currentY - rackStartY;
        
        const isHoriz = Math.abs(dx) > Math.abs(dy);
        const dragLengthPx = isHoriz ? Math.abs(dx) : Math.abs(dy);
        const dragLengthMm = dragLengthPx / currentScale;
        
        const beamInput = document.getElementById('beam-length');
        const beamLength = beamInput ? parseInt(beamInput.value) : 2585;
        const smallBeamLength = beamLength - 1200;

        let indepCount = 0;
        let connCount = 0;
        let smallConnCount = 0;
        let totalLengthPx = 0;

        // 최소 요구 길이는 정규빔 1개 + 85mm
        if (dragLengthMm >= beamLength + 85) {
            indepCount = 1;
            
            let N = Math.floor((dragLengthMm - 85) / beamLength);
            connCount = N - 1;
            
            let remaining = dragLengthMm - ((N * beamLength) + 85);
            if (remaining >= smallBeamLength) {
                smallConnCount = 1;
            }

            const totalLengthMm = (N * beamLength) + (smallConnCount * smallBeamLength) + 85;
            totalLengthPx = totalLengthMm * currentScale;
        }

        if (indepCount > 0) {
            currentRackPreview = {
                x: rackStartX,
                y: rackStartY,
                isHoriz: isHoriz,
                dir: isHoriz ? Math.sign(dx) : Math.sign(dy),
                independent: indepCount,
                connected: connCount,
                smallConnected: smallConnCount,
                beamLength: beamLength,
                smallBeamLength: smallBeamLength,
                totalLengthPx: totalLengthPx,
                rackDepth: getRackDepth(),
                isDouble: isDoubleRow(),
                isValid: true
            };
            currentRackPreview.isValid = checkRackValidPlacement(currentRackPreview);
        } else {
            currentRackPreview = null;
        }
        draw();
        return;
    }

    // 도면 그리는 중 가이드 선
    if (isDrawingMode) {
        mousePos = { x: currentX, y: currentY };
        draw();
    }
});

canvas.addEventListener('mouseup', (e) => {
    if (draggingObstacleIndex !== -1) {
        draggingObstacleIndex = -1;
    }
    
    if (isDrawingRack) {
        isDrawingRack = false;
        if (currentRackPreview && currentRackPreview.independent > 0) {
            // 위치가 불량(빨간색)이어도 일단 배치는 허용하도록 수정 (사용자 요청)
            racks.push(currentRackPreview);
            updateRackFormCounts();
            
            if (!currentRackPreview.isValid) {
                console.log("경고: 유효하지 않은 위치에 배치되었습니다.");
            }
        }
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
    for (let i = 0; i < racks.length; i++) {
        let placedRack = racks[i];
        
        if (movingRack.isHoriz !== placedRack.isHoriz) continue;
        if (movingRack.dir !== placedRack.dir) continue;
        if (movingRack.beamLength !== placedRack.beamLength) continue;
        
        // 일직선상 여부
        if (movingRack.isHoriz && Math.abs(movingRack.y - placedRack.y) > 5) continue;
        if (!movingRack.isHoriz && Math.abs(movingRack.x - placedRack.x) > 5) continue;
        
        // placedRack 꼬리
        let placedTailX = placedRack.x + (placedRack.isHoriz ? placedRack.dir * placedRack.totalLengthPx : 0);
        let placedTailY = placedRack.y + (!placedRack.isHoriz ? placedRack.dir * placedRack.totalLengthPx : 0);
        
        // movingRack 머리가 placedRack 꼬리에 닿을 때
        if (Math.hypot(placedTailX - movingRack.x, placedTailY - movingRack.y) < 15) {
            placedRack.connected += movingRack.independent + movingRack.connected;
            placedRack.smallConnected += movingRack.smallConnected;
            
            let spans = placedRack.independent + placedRack.connected;
            let lenMm = 85 + (spans * placedRack.beamLength) + (placedRack.smallConnected * placedRack.smallBeamLength);
            placedRack.totalLengthPx = lenMm * currentScale;
            return true;
        }
        
        // movingRack 꼬리가 placedRack 머리에 닿을 때
        let movingTailX = movingRack.x + (movingRack.isHoriz ? movingRack.dir * movingRack.totalLengthPx : 0);
        let movingTailY = movingRack.y + (!movingRack.isHoriz ? movingRack.dir * movingRack.totalLengthPx : 0);
        
        if (Math.hypot(movingTailX - placedRack.x, movingTailY - placedRack.y) < 15) {
            placedRack.x = movingRack.x; 
            placedRack.y = movingRack.y;
            placedRack.connected += movingRack.independent + movingRack.connected;
            placedRack.smallConnected += movingRack.smallConnected;
            
            let spans = placedRack.independent + placedRack.connected;
            let lenMm = 85 + (spans * placedRack.beamLength) + (placedRack.smallConnected * placedRack.smallBeamLength);
            placedRack.totalLengthPx = lenMm * currentScale;
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

    if (draggedItemType === 'pillar') {
        const pCount = obstacles.filter(o => o.type === 'pillar').length + 1;
        obstacles.push({ type: 'pillar', name: '기둥' + pCount, x: dropX, y: dropY, width: 500, height: 500 });
    } else if (draggedItemType === 'door') {
        const dCount = obstacles.filter(o => o.type === 'door').length + 1;
        let closestLine = getClosestLineSegment(dropX, dropY);
        if (closestLine) {
            obstacles.push({ 
                type: 'door', name: '출입문' + dCount, 
                x: closestLine.x, y: closestLine.y, 
                angle: closestLine.angle, length: 2000,
                edgeIndex: closestLine.edgeIndex,
                ratioOnEdge: closestLine.ratioOnEdge,
                visualDistFromStart: closestLine.visualDistFromStart,
                visualEdgeLength: closestLine.visualEdgeLength
            });
        } else {
            obstacles.push({ type: 'door', name: '출입문' + dCount, x: dropX, y: dropY, angle: 0, length: 2000, edgeIndex: -1 });
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
    const snapRadian = Math.PI / 4; 
    for (let i = 0; i < numEdges; i++) {
        let angle = Math.atan2(points[i+1].y - points[i].y, points[i+1].x - points[i].x);
        angle = Math.round(angle / snapRadian) * snapRadian;
        originalAngles.push(angle);
    }
    
    if (typeof window.generateCustomInputs === 'function') {
        window.generateCustomInputs(numEdges);
    }
}

function getEdgeClearance(p1, p2) {
    let maxPillarDepth = 0;
    obstacles.forEach(obs => {
        if (obs.type === 'pillar') {
            let w = (obs.width || 500) * currentScale;
            let h = (obs.height || 500) * currentScale;
            
            if (Math.abs(p1.x - p2.x) < 5) { // 수직 벽면
                let wallX = p1.x;
                if (Math.abs(obs.x - w/2 - wallX) < 5 || Math.abs(obs.x + w/2 - wallX) < 5) {
                    let minY = Math.min(p1.y, p2.y) - h/2 - 5;
                    let maxY = Math.max(p1.y, p2.y) + h/2 + 5;
                    if (obs.y >= minY && obs.y <= maxY) {
                        maxPillarDepth = Math.max(maxPillarDepth, w);
                    }
                }
            } else if (Math.abs(p1.y - p2.y) < 5) { // 수평 벽면
                let wallY = p1.y;
                if (Math.abs(obs.y - h/2 - wallY) < 5 || Math.abs(obs.y + h/2 - wallY) < 5) {
                    let minX = Math.min(p1.x, p2.x) - w/2 - 5;
                    let maxX = Math.max(p1.x, p2.x) + w/2 + 5;
                    if (obs.x >= minX && obs.x <= maxX) {
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
    const depthPx = (r.rackDepth || 1000) * (r.isDouble ? 2 : 1) * currentScale;

    // 현재 좌표 기준 랙 바운딩 박스
    let minX = r.isHoriz ? (r.dir > 0 ? targetX : targetX - r.totalLengthPx) : (targetX - depthPx/2);
    let maxX = r.isHoriz ? (r.dir > 0 ? targetX + r.totalLengthPx : targetX) : (targetX + depthPx/2);
    let minY = r.isHoriz ? (targetY - depthPx/2) : (r.dir > 0 ? targetY : targetY - r.totalLengthPx);
    let maxY = r.isHoriz ? (targetY + depthPx/2) : (r.dir > 0 ? targetY + r.totalLengthPx : targetY);

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
        
        // --- 머리-꼬리 합체 스냅 ---
        if (placed.isHoriz === r.isHoriz && placed.dir === r.dir) {
            let placedTailX = placed.x + (placed.isHoriz ? placed.dir * placed.totalLengthPx : 0);
            let placedTailY = placed.y + (!placed.isHoriz ? placed.dir * placed.totalLengthPx : 0);
            
            // 내 머리가 상대방 꼬리에 스냅
            if (Math.hypot(targetX - placedTailX, targetY - placedTailY) < SNAP_DIST) {
                snappedX = placedTailX;
                snappedY = placedTailY;
                break;
            }
            
            // 내 꼬리가 상대방 머리에 스냅
            let myTailX = targetX + (r.isHoriz ? r.dir * r.totalLengthPx : 0);
            let myTailY = targetY + (!r.isHoriz ? r.dir * r.totalLengthPx : 0);
            if (Math.hypot(myTailX - placed.x, myTailY - placed.y) < SNAP_DIST) {
                snappedX = placed.x - (r.isHoriz ? r.dir * r.totalLengthPx : 0);
                snappedY = placed.y - (!r.isHoriz ? r.dir * r.totalLengthPx : 0);
                break;
            }
        }
        
        // --- 통로 간격(Aisle) 2800mm 스냅 ---
        if (placed.isHoriz === r.isHoriz) {
            const placedOuterDepth = placed.rackDepth || 1100;
            const placedDepthPx = placedOuterDepth * (placed.isDouble ? 2 : 1) * currentScale;
            
            if (r.isHoriz) {
                // X 구간 겹침 확인
                let myMinX = r.dir > 0 ? targetX : targetX - r.totalLengthPx;
                let myMaxX = r.dir > 0 ? targetX + r.totalLengthPx : targetX;
                let pMinX = placed.dir > 0 ? placed.x : placed.x - placed.totalLengthPx;
                let pMaxX = placed.dir > 0 ? placed.x + placed.totalLengthPx : placed.x;
                
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
                let myMinY = r.dir > 0 ? targetY : targetY - r.totalLengthPx;
                let myMaxY = r.dir > 0 ? targetY + r.totalLengthPx : targetY;
                let pMinY = placed.dir > 0 ? placed.y : placed.y - placed.totalLengthPx;
                let pMaxY = placed.dir > 0 ? placed.y + placed.totalLengthPx : placed.y;
                
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
        let myMinX = r.dir > 0 ? targetX : targetX - r.totalLengthPx;
        let myMaxX = r.dir > 0 ? targetX + r.totalLengthPx : targetX;
        
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
            let pMinX = placed.dir > 0 ? placed.x : placed.x - placed.totalLengthPx;
            let pMaxX = placed.dir > 0 ? placed.x + placed.totalLengthPx : placed.x;
            if (!(myMaxX < pMinX || myMinX > pMaxX)) {
                let placedOuterDepth = placed.rackDepth || 1100;
                let placedDepthPx = placedOuterDepth * (placed.isDouble ? 2 : 1) * currentScale;
                
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
        let myMinY = r.dir > 0 ? targetY : targetY - r.totalLengthPx;
        let myMaxY = r.dir > 0 ? targetY + r.totalLengthPx : targetY;
        
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
            let pMinY = placed.dir > 0 ? placed.y : placed.y - placed.totalLengthPx;
            let pMaxY = placed.dir > 0 ? placed.y + placed.totalLengthPx : placed.y;
            if (!(myMaxY < pMinY || myMinY > pMaxY)) {
                let placedOuterDepth = placed.rackDepth || 1100;
                let placedDepthPx = placedOuterDepth * (placed.isDouble ? 2 : 1) * currentScale;
                
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

function getRackBoxes(r) {
    const depthPx = (r.rackDepth || 1000) * currentScale;
    const cornerClearancePx = depthPx + (100 * currentScale);
    
    // 물리적 바운딩 박스
    let pxMinX = r.isHoriz ? (r.dir > 0 ? r.x : r.x - r.totalLengthPx) : (r.x - depthPx/2);
    let pxMaxX = r.isHoriz ? (r.dir > 0 ? r.x + r.totalLengthPx : r.x) : (r.x + depthPx/2);
    let pxMinY = r.isHoriz ? (r.y - depthPx/2) : (r.dir > 0 ? r.y : r.y - r.totalLengthPx);
    let pxMaxY = r.isHoriz ? (r.y + depthPx/2) : (r.dir > 0 ? r.y + r.totalLengthPx : r.y);
    
    let physicalBox = { minX: pxMinX, maxX: pxMaxX, minY: pxMinY, maxY: pxMaxY };
    
    // 클리어런스 박스 (코너 죽은 공간을 위해 양 끝단 확장)
    let cxMinX = pxMinX - (r.isHoriz ? cornerClearancePx : 0);
    let cxMaxX = pxMaxX + (r.isHoriz ? cornerClearancePx : 0);
    let cxMinY = pxMinY - (!r.isHoriz ? cornerClearancePx : 0);
    let cxMaxY = pxMaxY + (!r.isHoriz ? cornerClearancePx : 0);
    
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

function checkRackValidPlacement(newRack) {
    let boxes = getRackBoxes(newRack);
    
    // 조건 A: 겹침 및 코너 여유공간 (Dead Space), 그리고 통로(Aisle) 간격 검사
    const AISLE_DIST_PX = 2800 * currentScale;
    
    for(let r of racks) {
        let existBoxes = getRackBoxes(r);
        // 새로운 랙의 물리적 박스가 기존 랙의 클리어런스 박스를 침범하거나,
        // 새로운 랙의 클리어런스 박스가 기존 랙의 물리적 박스를 침범하면 불합격!
        if (checkBoxesOverlap(boxes.physical, existBoxes.clearance) || checkBoxesOverlap(boxes.clearance, existBoxes.physical)) {
            return false;
        }
        
        // 통로 간격 검사 (2800mm 룰)
        if (newRack.isHoriz === r.isHoriz) {
            let placedOuterDepth = r.rackDepth || 1100;
            let placedDepthPx = placedOuterDepth * (r.isDouble ? 2 : 1) * currentScale;
            let newOuterDepth = newRack.rackDepth || 1100;
            let newDepthPx = newOuterDepth * (newRack.isDouble ? 2 : 1) * currentScale;
            
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
    
    // 조건 C: 장애물(기둥) 100mm 직접 이격 검사 (단, 벽 스냅에 의해 자동 조절되므로 여기선 물리적 충돌만 방지)
    for (let obs of obstacles) {
        if (obs.type === 'pillar') {
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
        } else if (obs.type === 'door') {
            // 출입구 앞쪽 2800mm 확보
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
    
    // 조건 D: 창고 벽면 동적 이격(기둥 연동) 및 바깥으로 벗어남 방지
    // 1. 다각형 내부 포함 여부 (최소 1px 패딩)
    let padPx = 1 * currentScale;
    let wBox = {
        minX: boxes.physical.minX + padPx,
        maxX: boxes.physical.maxX - padPx,
        minY: boxes.physical.minY + padPx,
        maxY: boxes.physical.maxY - padPx
    };
    
    let corners = [
        [wBox.minX, wBox.minY], [wBox.maxX, wBox.minY],
        [wBox.maxX, wBox.maxY], [wBox.minX, wBox.maxY]
    ];
    
    for(let c of corners) {
        if(!isPointInPolygon(c, points)) return false;
    }
    
    // 2. 동적 벽면 이격 거리(Clearance) 검사
    for (let i = 0; i < points.length - 1; i++) {
        let p1 = points[i];
        let p2 = points[i+1];
        let clearance = getEdgeClearance(p1, p2) - 2; // -2 for float error
        
        if (Math.abs(p1.x - p2.x) < 5) { // Vertical wall
            let wallX = p1.x;
            let minY = Math.min(p1.y, p2.y) - 5;
            let maxY = Math.max(p1.y, p2.y) + 5;
            if (!(boxes.physical.maxY < minY || boxes.physical.minY > maxY)) {
                let dist = 0;
                if (wallX <= boxes.physical.minX) dist = boxes.physical.minX - wallX;
                else if (wallX >= boxes.physical.maxX) dist = wallX - boxes.physical.maxX;
                else dist = -1;
                
                if (dist < clearance) return false;
            }
        } else if (Math.abs(p1.y - p2.y) < 5) { // Horizontal wall
            let wallY = p1.y;
            let minX = Math.min(p1.x, p2.x) - 5;
            let maxX = Math.max(p1.x, p2.x) + 5;
            if (!(boxes.physical.maxX < minX || boxes.physical.minX > maxX)) {
                let dist = 0;
                if (wallY <= boxes.physical.minY) dist = boxes.physical.minY - wallY;
                else if (wallY >= boxes.physical.maxY) dist = wallY - boxes.physical.maxY;
                else dist = -1;
                
                if (dist < clearance) return false;
            }
        }
    }
    
    return true;
}

// 랙 그룹 렌더링 헬퍼 함수
function drawRackGroup(r, isPreview = false) {
    if(!r || currentScale <= 0) return;
    
    const outerDepth = r.rackDepth || 1100;
    const depthPx = outerDepth * (r.isDouble ? 2 : 1) * currentScale;
    const framePx = RACK_FRAME_WIDTH * currentScale;
    const beamPx = r.beamLength * currentScale;
    
    ctx.save();
    ctx.translate(r.x, r.y);
    
    // 회전 처리
    if (!r.isHoriz) {
        ctx.rotate(Math.PI / 2);
    }
    if (r.dir < 0) {
        ctx.rotate(Math.PI);
    }

    // 색상 결정: 에러 상태면 빨간색 반투명
    if (isPreview) {
        ctx.fillStyle = r.isValid ? 'rgba(56, 189, 248, 0.4)' : 'rgba(239, 68, 68, 0.4)';
    } else {
        ctx.fillStyle = 'rgba(14, 165, 233, 0.7)';
    }
    
    ctx.strokeStyle = '#fff';
    ctx.lineWidth = 1;

    // Y축 중앙 정렬을 위해 Y좌표를 -depthPx/2 로 시작
    // 랙 전체 바운딩 박스 바탕색 및 테두리
    ctx.fillRect(0, -depthPx/2, r.totalLengthPx, depthPx);
    
    // 랙 분할(Split) 기능 시각화: 독립형 첫 스팬은 주황색 계열로 칠함
    if (r.independent > 0) {
        let spans = r.independent + r.connected + r.smallConnected;
        let frameMarginPx = (85 * currentScale) / (spans + 1); 
        let firstSpanWidth = frameMarginPx * 2 + beamPx; // 첫 칸 (양쪽 기둥 포함)
        
        ctx.fillStyle = isPreview ? (r.isValid ? 'rgba(249, 115, 22, 0.5)' : 'rgba(239, 68, 68, 0.4)') : 'rgba(249, 115, 22, 0.8)';
        ctx.fillRect(0, -depthPx/2, firstSpanWidth, depthPx);
    }
    
    ctx.strokeRect(0, -depthPx/2, r.totalLengthPx, depthPx);

    // 복수형(Double Row)일 경우 가운데 분할선과 타이 홀더(가상의 선) 그리기
    if (r.isDouble) {
        ctx.beginPath();
        ctx.moveTo(0, 0); // Y 중앙선
        ctx.lineTo(r.totalLengthPx, 0);
        ctx.strokeStyle = 'rgba(255, 255, 255, 0.5)';
        ctx.lineWidth = 1;
        ctx.stroke();
    }

    // 각 프레임 구간 나누기 선 그리기
    ctx.beginPath();
    let currentOffsetX = 0;
    let totalSpans = r.independent + r.connected + r.smallConnected;
    // 85mm의 전체 마진을 칸수만큼 대략적으로 나누어 프레임처럼 보이게 함
    let frameMarginPx = (85 * currentScale) / (totalSpans + 1); 
    
    currentOffsetX += frameMarginPx;
    
    for(let i=0; i<r.independent + r.connected; i++) {
        currentOffsetX += beamPx;
        ctx.moveTo(currentOffsetX, -depthPx/2);
        ctx.lineTo(currentOffsetX, depthPx/2);
        currentOffsetX += frameMarginPx;
    }
    
    if (r.smallConnected > 0) {
        currentOffsetX += (r.smallBeamLength * currentScale);
        ctx.moveTo(currentOffsetX, -depthPx/2);
        ctx.lineTo(currentOffsetX, depthPx/2);
    }
    
    ctx.strokeStyle = isPreview && !r.isValid ? 'rgba(220, 38, 38, 0.8)' : '#0ea5e9';
    ctx.lineWidth = 2;
    ctx.stroke();

    // 텍스트 라벨 (몇 독립/몇 연결)
    ctx.fillStyle = '#fff';
    ctx.font = 'bold 12px Inter';
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    
    // 글씨가 돌아가지 않게 캔버스 변환 복구 후 그리기 위해 좌표 변환
    ctx.restore();
    
    ctx.fillStyle = '#fff';
    ctx.font = 'bold 12px Inter';
    ctx.textAlign = 'center';
    
    // 라벨 출력 위치 계산 (랙의 중앙)
    let cx = r.isHoriz ? r.x + (r.dir > 0 ? r.totalLengthPx/2 : -r.totalLengthPx/2) : r.x;
    let cy = r.isHoriz ? r.y : r.y + (r.dir > 0 ? r.totalLengthPx/2 : -r.totalLengthPx/2);
    
    let label = `독${r.independent} 연${r.connected}`;
    if (r.smallConnected > 0) label += ` 짝${r.smallConnected}`;
    
    ctx.fillText(label, cx, cy + (isPreview ? -20 : 0));
}

// 전체 화면 그리기 루프
function draw() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
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

    // 4. 파렛트랙 그룹들 그리기
    racks.forEach(r => drawRackGroup(r));

    // 5. 드래그 중인 파렛트랙 미리보기 그리기
    if ((isDrawingRack || isMovingRack) && currentRackPreview) {
        drawRackGroup(currentRackPreview, true);
    }

    // 6. 장애물 그리기
    obstacles.forEach(obs => {
        if (obs.type === 'pillar') {
            const size = 15;
            const px = obs.x - size / 2;
            const py = obs.y - size / 2;
            
            ctx.fillStyle = 'rgba(239, 68, 68, 0.5)';
            ctx.fillRect(px, py, size, size);
            
            ctx.beginPath();
            ctx.moveTo(px, py);
            ctx.lineTo(px + size, py + size);
            ctx.moveTo(px + size, py);
            ctx.lineTo(px, py + size);
            ctx.strokeStyle = '#ef4444';
            ctx.lineWidth = 2;
            ctx.stroke();
            
            ctx.fillStyle = '#fca5a5';
            ctx.font = '10px Inter';
            ctx.textAlign = 'center';
            ctx.fillText(obs.name, obs.x, obs.y - size / 2 - 5);
            ctx.fillText(`${obs.width}x${obs.height}`, obs.x, obs.y + size + 10);
            
        } else if (obs.type === 'door') {
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
            
            ctx.fillStyle = '#f59e0b';
            ctx.fillRect(-visualLength / 2, -thickness / 2, visualLength, thickness);
            ctx.restore();
            
            ctx.fillStyle = '#fcd34d';
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
}

// 랙 수량을 폼에 자동 업데이트
window.updateRackFormCounts = function() {
    let totalIndep = 0;
    let totalConn = 0;
    let totalSmallConn = 0;
    let totalTieHolders = 0;
    racks.forEach(r => {
        if (r.isDouble) {
            totalIndep += (r.independent || 0) * 2;
            totalConn += (r.connected || 0) * 2;
            totalSmallConn += (r.smallConnected || 0) * 2;
            
            let totalSpans = (r.independent || 0) + (r.connected || 0) + (r.smallConnected || 0);
            if (totalSpans > 0) {
                totalTieHolders += (totalSpans + 1) * 2;
            }
        } else {
            totalIndep += r.independent || 0;
            totalConn += r.connected || 0;
            totalSmallConn += r.smallConnected || 0;
        }
    });
    
    const indepInput = document.getElementById('rack-independent');
    const connInput = document.getElementById('rack-connected');
    const smallConnInput = document.getElementById('rack-small-connected');
    const tieHolderInput = document.getElementById('rack-tie-holders');
    
    if(indepInput) indepInput.value = totalIndep > 0 ? totalIndep : '';
    if(connInput) connInput.value = totalConn > 0 ? totalConn : '';
    if(smallConnInput) smallConnInput.value = totalSmallConn > 0 ? totalSmallConn : '';
    if(tieHolderInput) tieHolderInput.value = totalTieHolders > 0 ? totalTieHolders : '';
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

        if (obs.type === 'door') {
            itemDiv.innerHTML = `
                <span class="text-warning fw-bold small" style="min-width:60px;">${obs.name}</span>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-transparent text-secondary border-secondary">길이</span>
                    <input type="number" class="form-control bg-transparent text-white border-secondary" value="${obs.length}" oninput="updateObstacleData(${index}, 'length', this.value)">
                </div>
                <button class="btn btn-sm btn-outline-danger px-2 py-1" onclick="deleteObstacle(${index})">✕</button>
            `;
        } else if (obs.type === 'pillar') {
            itemDiv.innerHTML = `
                <span class="text-danger fw-bold small" style="min-width:60px;">${obs.name}</span>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-transparent text-secondary border-secondary">가로</span>
                    <input type="number" class="form-control bg-transparent text-white border-secondary" value="${obs.width}" oninput="updateObstacleData(${index}, 'width', this.value)">
                </div>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-transparent text-secondary border-secondary">세로</span>
                    <input type="number" class="form-control bg-transparent text-white border-secondary" value="${obs.height}" oninput="updateObstacleData(${index}, 'height', this.value)">
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
