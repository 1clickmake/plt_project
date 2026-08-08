<!DOCTYPE html>
<html lang="ko" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>파렛트랙 자동 견적 시스템 - B2B SaaS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: #f8fafc;
            min-height: 100vh;
        }
        .glass-panel {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 1rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }
        .step-badge {
            background: rgba(56, 189, 248, 0.2);
            color: #38bdf8;
            font-weight: 800;
            padding: 0.3rem 0.6rem;
            border-radius: 0.5rem;
            margin-right: 0.5rem;
        }
        .shape-btn {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #94a3b8;
            transition: all 0.2s ease;
        }
        .shape-btn:hover {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
        }
        .shape-btn.active {
            background: rgba(56, 189, 248, 0.2);
            border-color: #38bdf8;
            color: #38bdf8;
        }
        .btn-primary-gradient {
            background: linear-gradient(to right, #0ea5e9, #3b82f6);
            border: none;
            color: white;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-primary-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(14, 165, 233, 0.4);
        }
        .canvas-container {
            background: #0f172a;
            border: 1px dashed #334155;
            border-radius: 0.5rem;
            min-height: 500px;
            position: relative;
            overflow: auto;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        #drawingCanvas {
            position: absolute;
            top: 0;
            left: 0;
            cursor: crosshair;
        }
        .drag-item {
            cursor: grab;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 0.3rem;
            padding: 0.5rem;
            display: inline-block;
            margin-right: 0.5rem;
            color: #e2e8f0;
            font-size: 0.9rem;
        }
        .drag-item:active {
            cursor: grabbing;
        }
        .shape-diagram {
            background: rgba(0,0,0,0.2);
            border-radius: 0.5rem;
            padding: 1rem;
            text-align: center;
        }
        .shape-diagram svg {
            max-width: 100%;
            height: 120px;
        }
        .shape-diagram text {
            fill: #38bdf8;
            font-size: 14px;
            font-weight: bold;
        }
        .shape-diagram path, .shape-diagram rect, .shape-diagram polygon {
            stroke: #94a3b8;
            stroke-width: 3;
            fill: rgba(56, 189, 248, 0.1);
        }
    </style>
</head>
<body>
<div class="container-fluid py-4 px-4">
    <div class="text-center mb-4">
        <h1 class="fw-bold" style="background: -webkit-linear-gradient(#38bdf8, #818cf8); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
            스마트 창고 배치 견적
        </h1>
        <p class="text-muted">복잡한 창고 형태도 드래그 앤 드롭으로 1분 만에 완성!</p>
    </div>

    <div class="row g-4">
        <!-- 왼쪽: 4단계 입력 폼 -->
        <div class="col-xl-3 col-lg-4">
            <div class="glass-panel p-4 h-100">
                
                <!-- 1단계: 창고 모양 그리기 -->
                <div class="mb-4">
                    <h5 class="fw-semibold mb-3"><span class="step-badge">1단계</span> 창고 평면도 그리기</h5>
                    <p class="text-muted small">우측 도화지(캔버스)에 점을 찍어 창고 외곽선을 직접 그려주세요.</p>
                </div>

                <!-- 2단계: 벽면 길이 입력 -->
                <div class="mb-4">
                    <h5 class="fw-semibold mb-3"><span class="step-badge">2단계</span> 벽면 길이 입력 (mm)</h5>
                    
                    <!-- 그림 안내 영역 -->
                    <div class="shape-diagram mb-3" id="diagram-container">
                        <!-- SVG 다이어그램이 JS에 의해 여기에 들어갑니다. -->
                    </div>

                    <!-- 동적 입력 폼 영역 -->
                    <div class="row g-2" id="inputs-container">
                        <!-- 입력 폼이 JS에 의해 여기에 생성됩니다. -->
                    </div>
                </div>

                <!-- 3단계: 입구/기둥 드래그 안내 -->
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-semibold m-0"><span class="step-badge">3단계</span> 장애물 드래그 앤 드롭</h5>
                        <button class="btn btn-sm btn-outline-danger" onclick="resetCanvas()">🔄 초기화</button>
                    </div>
                    <p class="text-muted small mb-2">우측 도면에 나타난 창고 위로 아이콘을 끌어다 놓으세요!</p>
                    <div class="d-flex align-items-center">
                        <div class="drag-item" id="drag-door" draggable="true" ondragstart="handleDragStart(event, 'door')">🚪 출입문</div>
                        <div class="drag-item" id="drag-pillar" draggable="true" ondragstart="handleDragStart(event, 'pillar')">◼️ 기둥</div>
                    </div>
                    <!-- 동적 생성되는 장애물 길이/크기 입력 영역 -->
                    <div id="obstacle-inputs-container" class="mt-3 d-flex flex-column gap-2"></div>
                </div>

                <!-- 4단계: 파렛트랙 규격 설정 -->
                <div class="mb-4 pt-3 border-top border-secondary">
                    <h5 class="fw-semibold mb-3"><span class="step-badge">4단계</span> 파렛트랙 규격 및 수량 설정</h5>
                    
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label text-info small mb-1">파렛트 크기</label>
                            <select class="form-select form-select-sm bg-transparent text-white border-secondary" id="pallet-size">
                                <option value="1100" class="text-dark">1100 x 1100</option>
                                <option value="1200" class="text-dark">1200 x 1200</option>
                                <option value="1300" class="text-dark">1300 x 1300</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-info small mb-1">파렛트 하중</label>
                            <select class="form-select form-select-sm bg-transparent text-white border-secondary" id="pallet-weight">
                                <option value="1.0" class="text-dark">1톤 이하</option>
                                <option value="1.5" class="text-dark">1.5톤 이하</option>
                                <option value="2.0" class="text-dark">1.5톤 이상</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-info small mb-1">파렛트랙 깊이 (mm)</label>
                            <select class="form-select form-select-sm bg-transparent text-white border-secondary" id="rack-depth">
                                <option value="1000" class="text-dark">1000 mm</option>
                                <option value="1100" class="text-dark">1100 mm</option>
                                <option value="1200" class="text-dark">1200 mm</option>
                                <option value="1300" class="text-dark">1300 mm</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-info small mb-1">로드빔 길이 (mm)</label>
                            <select class="form-select form-select-sm bg-transparent text-white border-secondary" id="beam-length">
                                <option value="2585" class="text-dark">2585 mm</option>
                                <option value="2785" class="text-dark">2785 mm</option>
                                <option value="2985" class="text-dark">2985 mm</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-info small mb-1">로드빔 규격</label>
                            <select class="form-select form-select-sm bg-transparent text-white border-secondary" id="beam-type">
                                <option value="100" class="text-dark">100 바</option>
                                <option value="125" class="text-dark">125 바</option>
                                <option value="150" class="text-dark">150 바</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label text-info small mb-1">설치 형태 (단수/복수)</label>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="rowType" id="rowSingle" value="single" checked>
                                    <label class="form-check-label text-white small" for="rowSingle">단수 (1줄)</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="rowType" id="rowDouble" value="double">
                                    <label class="form-check-label text-white small" for="rowDouble">복수 (2줄, 타이홀더 적용)</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mt-2">
                        <div class="col-md-4">
                            <label class="form-label text-info small mb-1">독립형</label>
                            <input type="number" class="form-control form-control-sm bg-transparent text-white border-secondary" id="rack-independent" placeholder="수량">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-info small mb-1">연결형</label>
                            <input type="number" class="form-control form-control-sm bg-transparent text-white border-secondary" id="rack-connected" placeholder="수량">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-info small mb-1">작은 연결형</label>
                            <input type="number" class="form-control form-control-sm bg-transparent text-white border-secondary" id="rack-small-connected" placeholder="수량" readonly>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label text-warning small mb-1">타이 홀더 (복수 설치용)</label>
                            <input type="number" class="form-control form-control-sm bg-transparent text-warning border-secondary" id="rack-tie-holders" placeholder="수량" readonly>
                        </div>
                        <div class="col-12 mt-1">
                            <p class="text-secondary m-0" style="font-size: 0.75rem;">* 드래그 시 수량이 자동 계산됩니다.</p>
                        </div>
                    </div>
                </div>

                <!-- 5단계: 견적서 생성 버튼 -->
                <div class="mt-4 pt-3 border-top border-secondary">
                    <h5 class="fw-semibold mb-3"><span class="step-badge">5단계</span> 완성 및 견적 확인</h5>
                    <button class="btn btn-primary-gradient w-100 py-3 rounded-3 shadow-lg">
                        🚀 파렛트랙 자동 배치 및 견적서 보기
                    </button>
                </div>
            </div>
        </div>

        <!-- 오른쪽: 실시간 2D 도면 프리뷰 -->
        <div class="col-xl-9 col-lg-8">
            <div class="glass-panel p-4 h-100 d-flex flex-column">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-semibold text-info m-0">실시간 2D 배치 도면</h5>
                    <div>
                        <button class="btn btn-sm btn-outline-info me-1" onclick="zoomIn()">➕ 줌인</button>
                        <button class="btn btn-sm btn-outline-info me-1" onclick="zoomOut()">➖ 줌아웃</button>
                        <button class="btn btn-sm btn-outline-info me-1" onclick="resetZoom()">🔄 원위치</button>
                        <span class="badge bg-secondary">평면도</span>
                    </div>
                </div>
                <!-- 캔버스 영역 -->
                <div class="canvas-container flex-grow-1" id="canvas-wrapper">
                    <!-- 기본 가이드 텍스트 -->
                    <div class="text-center text-secondary" id="canvas-guide">
                        <div style="font-size: 3rem; margin-bottom: 1rem;">✏️</div>
                        <p>좌측 치수를 기반으로<br>창고 평면도가 여기에 실시간으로 그려집니다.</p>
                    </div>
                    <!-- 커스텀 드로잉용 캔버스 (평소엔 숨김) -->
                    <canvas id="drawingCanvas" width="800" height="600" style="display:none;"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="/assets/js/canvas2d.js"></script>
<script>
window.generateCustomInputs = function(numEdges) {
    const diagramContainer = document.getElementById('diagram-container');
    const inputsContainer = document.getElementById('inputs-container');
    
    diagramContainer.innerHTML = `<p class="text-success fw-bold py-3 m-0">🎉 총 ${numEdges}각형 도면이 확정되었습니다!</p>`;
    
    // 엣지 길이 배열 초기화
    if(typeof clearEdgeLengths === 'function') clearEdgeLengths();

    let inputsHtml = '';
    for(let i=1; i<=numEdges; i++) {
        inputsHtml += `
        <div class="col-6">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-transparent text-info border-secondary">${i}번 선분</span>
                <input type="number" class="form-control bg-transparent text-white border-secondary" placeholder="길이(mm)" oninput="updateEdgeLength(${i-1}, this.value)">
            </div>
        </div>`;
    }
    inputsContainer.innerHTML = inputsHtml;
};

// 초기 로딩 시 커스텀 직접 그리기 렌더링
window.addEventListener('DOMContentLoaded', () => {
    const diagramContainer = document.getElementById('diagram-container');
    const inputsContainer = document.getElementById('inputs-container');
    const canvasGuide = document.getElementById('canvas-guide');
    const drawingCanvas = document.getElementById('drawingCanvas');

    if(diagramContainer) diagramContainer.innerHTML = '<p class="text-info fw-bold py-3 m-0">우측 캔버스에 점을 찍어 창고 모양을 완성해주세요!</p>';
    if(inputsContainer) inputsContainer.innerHTML = '';
    if(canvasGuide) canvasGuide.style.display = 'none';
    if(drawingCanvas) drawingCanvas.style.display = 'block';
    
    if(typeof startCustomDrawing === 'function') startCustomDrawing();
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>