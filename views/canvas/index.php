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
            height: 100vh;
            overflow: hidden;
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
        .file-drop-zone {
            border: 2px dashed rgba(56, 189, 248, 0.4);
            border-radius: 0.75rem;
            padding: 1.2rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s ease;
            background: rgba(56, 189, 248, 0.03);
        }
        .file-drop-zone:hover, .file-drop-zone.dragover {
            border-color: #38bdf8;
            background: rgba(56, 189, 248, 0.1);
        }
        #file-preview-area {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 8px;
        }
        .file-preview-thumb {
            position: relative;
            width: 70px;
            height: 70px;
            border-radius: 0.5rem;
            overflow: hidden;
            border: 1px solid rgba(255,255,255,0.2);
            background: #1e293b;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            font-size: 0.65rem;
            color: #94a3b8;
        }
        .file-preview-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .file-preview-thumb .del-btn {
            position: absolute;
            top: 2px;
            right: 2px;
            background: rgba(239,68,68,0.85);
            color: white;
            border: none;
            border-radius: 50%;
            width: 16px;
            height: 16px;
            font-size: 10px;
            line-height: 16px;
            cursor: pointer;
            padding: 0;
            text-align: center;
        }
        .section-required-note { font-size: 0.72rem; color: #94a3b8; font-weight: 400; }
        .required-star { color: #f87171; font-size: 0.7rem; margin-left: 3px; }
        .quote-submit-btn {
            background: linear-gradient(135deg, #10b981, #059669);
            border: none;
            color: white;
            font-weight: 700;
            transition: all 0.3s ease;
        }
        .quote-submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(16, 185, 129, 0.4);
            color: white;
        }

    </style>
</head>
<body>
<div class="container-fluid pt-3 px-4 d-flex flex-column h-100">
    <div class="text-center mb-3 flex-shrink-0">
        <?php if (!empty($vendor)): ?>
            <?php if (!empty($vendor['company_logo'])): ?>
                <img src="<?= htmlspecialchars($vendor['company_logo']) ?>" alt="Logo" style="max-height: 50px; margin-bottom: 5px;">
            <?php endif; ?>
            <h4 class="fw-bold text-light mb-1"><?= htmlspecialchars($vendor['company_name']) ?></h4>
        <?php endif; ?>
        <h2 class="fw-bold" style="background: -webkit-linear-gradient(#38bdf8, #818cf8); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
            스마트 창고 배치 견적
        </h2>
        <p class="text-muted small m-0">복잡한 창고 형태도 드래그 앤 드롭으로 1분 만에 완성! <?php if(!empty($vendor['contact_number'])) echo " (문의: " . htmlspecialchars($vendor['contact_number']) . ")"; ?></p>
    </div>

    <div class="row g-4 flex-grow-1" style="min-height: 0;">
        <!-- 왼쪽: 4단계 입력 폼 -->
        <div class="col-xl-3 col-lg-4 h-100">
            <div class="glass-panel p-4 h-100" style="overflow-y: auto; scrollbar-width: thin;">
                
                <!-- 1단계: 창고 모양 그리기 -->
                <div class="mb-4">
                    <h5 class="fw-semibold mb-3">
                        <span class="step-badge">1단계</span> 창고 평면도 그리기
                        <button type="button" class="btn btn-link btn-sm p-0 ms-1 text-info text-decoration-none" data-bs-toggle="offcanvas" data-bs-target="#helpOffcanvas" onclick="scrollToHelp('help-step1')">❓</button>
                    </h5>
                    <p class="text-muted small">우측 도화지(캔버스)에 점을 찍어 창고 외곽선을 직접 그려주세요.</p>
                </div>

                <!-- 2단계: 벽면 길이 입력 -->
                <div class="mb-4">
                    <h5 class="fw-semibold mb-3">
                        <span class="step-badge">2단계</span> 벽면 길이 입력 (mm)
                        <button type="button" class="btn btn-link btn-sm p-0 ms-1 text-info text-decoration-none" data-bs-toggle="offcanvas" data-bs-target="#helpOffcanvas" onclick="scrollToHelp('help-step2')">❓</button>
                    </h5>
                    
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
                        <h5 class="fw-semibold m-0">
                            <span class="step-badge">3단계</span> 장애물 배치
                            <button type="button" class="btn btn-link btn-sm p-0 ms-1 text-info text-decoration-none" data-bs-toggle="offcanvas" data-bs-target="#helpOffcanvas" onclick="scrollToHelp('help-step3')">❓</button>
                        </h5>
                        <button class="btn btn-sm btn-outline-danger" onclick="resetCanvas()">🔄 초기화</button>
                    </div>
                    <p class="text-muted small mb-2">우측 도면에 나타난 창고 위로 아이콘을 끌어다 놓으세요!</p>
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <div class="drag-item bg-dark text-white p-2 rounded" id="drag-door" draggable="true" ondragstart="handleDragStart(event, 'door')">🚪 출입문</div>
                        <div class="drag-item bg-dark text-white p-2 rounded" id="drag-pillar" draggable="true" ondragstart="handleDragStart(event, 'pillar')">◼️ 기둥</div>
                        <div class="drag-item bg-dark text-white p-2 rounded" id="drag-shutter" draggable="true" ondragstart="handleDragStart(event, 'shutter')">🪟 셔터</div>
                        <div class="drag-item bg-dark text-white p-2 rounded" id="drag-machine" draggable="true" ondragstart="handleDragStart(event, 'machine')">⚙️ 기계</div>
                        <div class="drag-item bg-dark text-white p-2 rounded" id="drag-hydrant" draggable="true" ondragstart="handleDragStart(event, 'hydrant')">🧯 소화전</div>
                        <div class="drag-item bg-dark text-white p-2 rounded" id="drag-panel" draggable="true" ondragstart="handleDragStart(event, 'panel')">⚡ 전기판넬</div>
                        <div class="drag-item bg-dark text-white p-2 rounded" id="drag-forbidden" draggable="true" ondragstart="handleDragStart(event, 'forbidden')">🚫 사용불가</div>
                    </div>
                    <div id="obstacle-inputs-container" class="mt-3 d-flex flex-column gap-2"></div>
                </div>

                <!-- 4단계: 파렛트 및 지게차 제원 설정 -->
                <div class="mb-4 pt-3 border-top border-secondary">
                    <h5 class="fw-semibold mb-3"><span class="step-badge">4단계</span> 파렛트 및 지게차 제원</h5>
                    
                    <h6 class="text-info small fw-bold mb-2">📦 적재 파렛트 제원</h6>
                    
                    <div class="col-12 mb-3 px-2 py-2 rounded" style="background: rgba(255,255,255,0.05);">
                        <label class="form-label text-info small mb-1">
                            포크 진입 방향 (파랫트 방향)
                            <button type="button" class="btn btn-link btn-sm p-0 ms-1 text-info text-decoration-none" data-bs-toggle="offcanvas" data-bs-target="#helpOffcanvas" onclick="scrollToHelp('help-pallet-direction')">❓</button>
                        </label>
                        <div class="d-flex gap-3 mt-1">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="forkDirection" id="forkW" value="W" checked>
                                <label class="form-check-label text-white small" for="forkW">가로(W) 면으로 진입</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="forkDirection" id="forkD" value="D">
                                <label class="form-check-label text-white small" for="forkD">세로(D) 면으로 진입</label>
                            </div>
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label text-muted small mb-1">가로 (W) mm</label>
                            <input type="number" class="form-control form-control-sm bg-transparent text-white border-secondary" id="pallet-w" value="1100" placeholder="예: 1100">
                        </div>
                        <div class="col-6">
                            <label class="form-label text-muted small mb-1">세로/깊이 (D) mm</label>
                            <input type="number" class="form-control form-control-sm bg-transparent text-white border-secondary" id="pallet-d" value="1100" placeholder="예: 1100">
                        </div>
                        <div class="col-6">
                            <label class="form-label text-muted small mb-1">
                                적재 높이 (H) mm
                                <button type="button" class="btn btn-link btn-sm p-0 ms-1 text-info text-decoration-none" data-bs-toggle="offcanvas" data-bs-target="#helpOffcanvas" onclick="scrollToHelp('help-pallet-height')">❓</button>
                            </label>
                            <input type="number" class="form-control form-control-sm bg-transparent text-white border-secondary" id="pallet-h" value="1500" placeholder="화물 포함">
                        </div>
                        <div class="col-6">
                            <label class="form-label text-muted small mb-1">총 중량 (kg)</label>
                            <input type="number" class="form-control form-control-sm bg-transparent text-white border-secondary" id="pallet-weight" value="1000" placeholder="파렛트당 중량">
                        </div>
                    </div>

                    <h6 class="text-warning small fw-bold mb-2">
                        🚜 지게차 제원
                        <button type="button" class="btn btn-link btn-sm p-0 ms-1 text-info text-decoration-none" data-bs-toggle="offcanvas" data-bs-target="#helpOffcanvas" onclick="scrollToHelp('help-forklift')">❓</button>
                    </h6>
                    <div class="row g-2">
                        <div class="col-12">
                            <label class="form-label text-muted small mb-1">지게차 종류</label>
                            <select class="form-select form-select-sm bg-transparent text-white border-secondary" id="forklift-type">
                                <option value="reach" class="text-dark">입승식 (리치형) - 좁은 통로용</option>
                                <option value="counter" class="text-dark">좌승식 (카운터발란스) - 일반용</option>
                                <option value="vna" class="text-dark">삼방향 지게차 (VNA) - 초소형 통로</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label text-muted small mb-1">최대 인상높이 (mm)</label>
                            <input type="number" class="form-control form-control-sm bg-transparent text-white border-secondary" id="forklift-lift-height" value="4500" placeholder="마스트 한계">
                        </div>
                        <div class="col-6">
                            <label class="form-label text-muted small mb-1">직각교차 통로폭(AST)</label>
                            <input type="number" class="form-control form-control-sm bg-transparent text-white border-secondary" id="forklift-ast" value="2800" placeholder="작업 통로 폭">
                        </div>
                    </div>

                    <h6 class="text-info small fw-bold mb-2 mt-3">
                        📋 랙 설치 희망 제원
                        <button type="button" class="btn btn-link btn-sm p-0 ms-1 text-info text-decoration-none" data-bs-toggle="offcanvas" data-bs-target="#helpOffcanvas" onclick="scrollToHelp('help-rack-specs')">❓</button>
                    </h6>
                    <div class="row g-2">
                        <div class="col-4">
                            <label class="form-label text-muted small mb-1">설치 단수 <span class="text-danger">*</span></label>
                            <input type="number" class="form-control form-control-sm bg-transparent text-white border-secondary" id="rack-levels" value="3" placeholder="예: 3단">
                        </div>
                        <div class="col-4">
                            <label class="form-label text-muted small mb-1">설치 칸수</label>
                            <input type="number" class="form-control form-control-sm bg-transparent text-white border-secondary" id="rack-bays" placeholder="최대설치">
                        </div>
                        <div class="col-4">
                            <label class="form-label text-muted small mb-1">설치 높이(mm)</label>
                            <input type="number" class="form-control form-control-sm bg-transparent text-white border-secondary" id="rack-height" placeholder="공란시 계산">
                        </div>
                    </div>
                </div>

                <!-- 5단계: 추가 자료 및 요청사항 -->
                <div class="mb-4 pt-3 border-top border-secondary">
                    <h5 class="fw-semibold mb-3">
                        <span class="step-badge">5단계</span> 추가 자료 및 요청사항
                        <span class="section-required-note">(필수 아님)</span>
                    </h5>

                    <div class="mb-3">
                        <label class="form-label text-muted small mb-2">📎 도면/배치 참고 파일 첨부</label>
                        <div class="file-drop-zone" id="file-drop-zone" onclick="document.getElementById('file-input').click()">
                            <div style="font-size:1.5rem;">📁</div>
                            <div class="text-muted small mt-1">JPG, PNG, PDF 클릭하여 업로드<br><span style="font-size:0.7rem; color: #475569;">손으로 그린 스케치 도면도 환영합니다</span></div>
                        </div>
                        <input type="file" id="file-input" multiple accept="image/*,.pdf" style="display:none;" onchange="handleFileSelect(this.files)">
                        <div id="file-preview-area"></div>
                    </div>

                    <div>
                        <label class="form-label text-muted small mb-1">💬 AI에게 남길 요청사항</label>
                        <textarea class="form-control bg-transparent text-white border-secondary" id="ai-request" rows="4"
                            placeholder="예) '입구 5m 좌측부터 3열로 배치해주세요', '기둥 주변 1m는 설치 제외해주세요' 등 자유롭게 작성하세요."></textarea>
                    </div>
                </div>

                <!-- 6단계: 실행 -->
                <div class="pt-3 border-top border-secondary pb-2">
                    <h5 class="fw-semibold mb-2"><span class="step-badge">6단계</span> 자동 배치 실행</h5>
                    <p class="text-muted small mb-3">모든 필수 정보가 입력되면 뿅!</p>
                    <button id="run-layout-btn" class="btn btn-primary-gradient w-100 py-3 rounded-3 shadow-lg" onclick="runAutoLayout()">
                        🚀 파렛트랙 자동 배치 실행
                    </button>
                </div>
            </div>
        </div>


        <!-- 우측: 캔버스 및 시각화 -->
        <div class="col-xl-9 col-lg-8 h-100">
            <div class="glass-panel p-4 h-100 d-flex flex-column">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-semibold text-info m-0">실시간 2D 배치 도면</h5>
                    <div class="d-flex gap-2 align-items-center flex-wrap">
                        <button class="btn btn-sm btn-outline-info" onclick="zoomIn()">➕ 줌인</button>
                        <button class="btn btn-sm btn-outline-info" onclick="zoomOut()">➖ 줌아웃</button>
                        <button class="btn btn-sm btn-outline-info" onclick="resetZoom()">🔄 원위치</button>
                        <span class="badge bg-secondary">평면도</span>
                        <button id="request-quote-btn" class="btn quote-submit-btn px-3 py-1 rounded-3 d-none" data-bs-toggle="modal" data-bs-target="#quoteRequestModal">
                            ✅ 배치도 수락 &mdash; 견적 요청하기
                        </button>
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

<!-- 입력 가이드라인 오프캔버스 -->
<div class="offcanvas offcanvas-end text-bg-dark" tabindex="-1" id="helpOffcanvas" aria-labelledby="helpOffcanvasLabel" style="width: 400px; border-left: 1px solid rgba(255,255,255,0.1);">
  <div class="offcanvas-header border-bottom border-secondary">
    <h5 class="offcanvas-title text-info fw-bold" id="helpOffcanvasLabel">📘 스마트 견적 입력 가이드</h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body" style="font-size: 0.95rem;">
    <p class="text-muted mb-4">각 단계별 입력란에 대한 상세 설명입니다. 헷갈리시는 부분을 확인해주세요!</p>

    <div id="help-step1" class="mb-4 p-3 rounded" style="transition: background-color 1.5s ease;">
        <h6 class="text-white fw-bold">📍 1단계: 창고 평면도 그리기</h6>
        <p class="text-secondary mb-0">캔버스에 점을 찍어 창고의 대략적인 형태를 그립니다. 마지막에 처음 찍었던 점 근처를 클릭하면 도형이 닫히며 도면이 완성됩니다.<br>
        <strong class="text-warning">주의: 반드시 도형을 닫아야 다음 단계를 진행할 수 있습니다!</strong></p>
    </div>

    <div id="help-step2" class="mb-4 p-3 rounded" style="transition: background-color 1.5s ease;">
        <h6 class="text-white fw-bold">📏 2단계: 벽면 길이 입력</h6>
        <p class="text-secondary mb-0">도면이 완성되면 각 벽면의 실제 길이(mm 단위)를 입력하는 칸이 나타납니다. <strong>첫 번째 길이 하나만 정확하게 입력하시면</strong> 나머지 길이들은 그린 도면의 비율에 맞춰 시스템이 똑똑하게 자동으로 채워줍니다! (물론 직접 수정도 가능합니다)</p>
    </div>

    <div id="help-step3" class="mb-4 p-3 rounded" style="transition: background-color 1.5s ease;">
        <h6 class="text-white fw-bold">🚧 3단계: 장애물 배치</h6>
        <p class="text-secondary mb-0">출입문, 기둥 등의 아이콘을 도면 위로 끌어다 놓으세요. <strong class="text-info">출입문과 셔터</strong>는 벽면에 자동으로 스냅(달라붙음)되며, <strong class="text-danger">기둥과 장애물</strong>은 도면 내부 어디든 자유롭게 배치할 수 있습니다. 캔버스에 배치한 후 좌측에서 상세 길이나 크기를 정밀하게 조절할 수 있습니다.</p>
    </div>

    <div id="help-pallet-direction" class="mb-4 p-3 rounded" style="transition: background-color 1.5s ease;">
        <h6 class="text-white fw-bold">📦 파랫트 진입 방향 (포크 방향)</h6>
        <p class="text-secondary mb-0">지게차의 포크(발)가 파랫트의 어느 쪽으로 들어가는지 선택합니다.<br>
        예를 들어 <strong>1100(W) x 1200(D)</strong> 파랫트일 때, <br>
        1) <strong>1100면으로 포크가 진입</strong>하면 랙 깊이는 1200쪽을 기준으로 깊게 설계되며,<br>
        2) <strong>1200면으로 진입</strong>하면 랙 깊이는 1100쪽을 기준으로 설계됩니다.<br>
        이는 로드빔 길이와 전체 랙 면적 계산에 매우 중요합니다.</p>
    </div>

    <div id="help-pallet-height" class="mb-4 p-3 rounded" style="transition: background-color 1.5s ease;">
        <h6 class="text-white fw-bold">📦 적재 높이 (H)</h6>
        <p class="text-secondary mb-0"><strong class="text-warning">순수 나무/플라스틱 파랫트의 두께 + 그 위에 실제로 쌓인 화물의 높이</strong>를 모두 합친 총 높이입니다.<br>이 높이를 기준으로 단(Level) 사이의 간격(피치)이 결정되며, 리프트업을 위한 필수 여유 공간(클리어런스 100~200mm)은 시스템이 규격에 맞게 <strong>알아서 추가로 계산</strong>해 드립니다.</p>
    </div>

    <div id="help-forklift" class="mb-4 p-3 rounded" style="transition: background-color 1.5s ease;">
        <h6 class="text-white fw-bold">🚜 지게차 제원 (AST & 인상높이)</h6>
        <ul class="text-secondary ps-3 mb-0">
            <li class="mb-2"><strong>최대 인상높이:</strong> 지게차가 최대로 들어 올릴 수 있는 한계 높이입니다. 창고 층고가 아무리 높아도 이 수치 이상으로는 랙을 높게 설계할 수 없습니다.</li>
            <li><strong>직각교차 통로폭(AST):</strong> 지게차가 파랫트를 들고 직각으로 회전하여 랙에 적재하기 위해 필요한 <strong>최소 작업 통로 폭</strong>입니다. 사용하시는 지게차 카탈로그에 기재된 AST 값을 입력해주세요. (통상 리치형 2800mm, 카운터발란스형 3300mm~ 이상)</li>
        </ul>
    </div>

    <div id="help-rack-specs" class="mb-4 p-3 rounded" style="transition: background-color 1.5s ease;">
        <h6 class="text-white fw-bold">📋 랙 설치 희망 제원 (단수, 칸수, 높이)</h6>
        <ul class="text-secondary ps-3 mb-0">
            <li class="mb-2"><strong>설치 단수 (필수):</strong> 설치하고자 하는 파렛트랙의 층수(적재단수)를 입력합니다. (예: 3단이면 바닥 포함 총 3개 층에 적재)</li>
            <li class="mb-2"><strong>설치 칸수 (선택):</strong> 설치하고자 하는 가로 칸(Bay) 수를 입력합니다. <strong>만약 입력하지 않고 빈칸으로 두시면</strong>, 지정하신 벽면이나 라인에 물리적으로 들어갈 수 있는 최대 칸수를 자동으로 꽉 채워서 설계해 드립니다.</li>
            <li><strong>설치 높이 (선택):</strong> 희망하는 랙 기둥(Upright Frame)의 총 높이(mm)입니다. 비워두시면 적재 높이(H)와 단수를 바탕으로 시스템이 가장 이상적인 기둥 높이를 **자동 계산**해 드립니다.</li>
        </ul>
    </div>
  </div>
</div>

<!-- 견적 요청 모달 -->
<div class="modal fade" id="quoteRequestModal" tabindex="-1" aria-labelledby="quoteModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content text-bg-dark" style="border: 1px solid rgba(56,189,248,0.3); border-radius: 1rem;">
      <div class="modal-header border-bottom border-secondary">
        <h5 class="modal-title text-info fw-bold" id="quoteModalLabel">📝 견적 요청 정보 입력</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p class="text-muted small mb-4">배치도가 마음에 드시나요? 연락처를 남겨주시면 담당자가 빠르게 안내드리겠습니다! <span class="text-warning">(★ 는 필수 입력사항)</span></p>
        <div class="row g-3">
          <div class="col-12">
            <label class="form-label text-muted small mb-1">회사명 <span class="text-danger">★</span></label>
            <input type="text" class="form-control bg-transparent text-white border-secondary" id="modal-company" placeholder="예: 주식회사 파로퀘스">
          </div>
          <div class="col-md-6">
            <label class="form-label text-muted small mb-1">담당자 이름 <span class="text-danger">★</span></label>
            <input type="text" class="form-control bg-transparent text-white border-secondary" id="modal-name" placeholder="예: 홍길동">
          </div>
          <div class="col-md-6">
            <label class="form-label text-muted small mb-1">연락처 (카카오톡 수신용) <span class="text-danger">★</span></label>
            <input type="tel" class="form-control bg-transparent text-white border-secondary" id="modal-phone" placeholder="010-0000-0000">
          </div>
          <div class="col-12">
            <label class="form-label text-muted small mb-1">시공 현장 주소 <span class="text-danger">★</span> <span class="text-secondary" style="font-size:0.7rem;">(최소 시/군/구 수준)</span></label>
            <input type="text" class="form-control bg-transparent text-white border-secondary" id="modal-address" placeholder="예: 경기도 성남시 분당구">
          </div>
        </div>
      </div>
      <div class="modal-footer border-top border-secondary">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">취소</button>
        <button type="button" class="btn quote-submit-btn px-4" onclick="submitQuoteRequest()">🚀 견적 요청 제출</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="/assets/js/canvas2d.js?v=<?= time() ?>"></script>
<script>

// --- 도움말 오프캔버스 스크롤 ---
function scrollToHelp(id) {
    const el = document.getElementById(id);
    if (el) {
        setTimeout(() => {
            el.scrollIntoView({ behavior: 'smooth', block: 'start' });
            el.style.backgroundColor = 'rgba(56, 189, 248, 0.2)';
            setTimeout(() => { el.style.backgroundColor = 'transparent'; }, 1500);
        }, 350);
    }
}

// --- 파일 업로드 미리보기 ---
let uploadedFiles = [];

function handleFileSelect(files) {
    Array.from(files).forEach(file => uploadedFiles.push(file));
    renderPreviews();
}

function renderPreviews() {
    const area = document.getElementById('file-preview-area');
    if (!area) return;
    area.innerHTML = '';
    uploadedFiles.forEach((file, i) => {
        const thumb = document.createElement('div');
        thumb.className = 'file-preview-thumb';
        const delBtn = `<button class="del-btn" onclick="removeFile(${i})">✕</button>`;
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = e => {
                thumb.innerHTML = `<img src="${e.target.result}" alt="미리보기">${delBtn}`;
            };
            reader.readAsDataURL(file);
        } else {
            const shortName = file.name.length > 12 ? file.name.substring(0, 10) + '…' : file.name;
            thumb.innerHTML = `<div style="font-size:1.5rem;">📄</div><div style="padding:2px 4px;text-align:center;word-break:break-all;">${shortName}</div>${delBtn}`;
        }
        area.appendChild(thumb);
    });
}

function removeFile(index) {
    uploadedFiles.splice(index, 1);
    renderPreviews();
}

// 드래그앤드롭 파일 업로드
document.addEventListener('DOMContentLoaded', () => {
    const dropZone = document.getElementById('file-drop-zone');
    if (dropZone) {
        dropZone.addEventListener('dragover', e => { e.preventDefault(); dropZone.classList.add('dragover'); });
        dropZone.addEventListener('dragleave', () => dropZone.classList.remove('dragover'));
        dropZone.addEventListener('drop', e => {
            e.preventDefault();
            dropZone.classList.remove('dragover');
            handleFileSelect(e.dataTransfer.files);
        });
    }
});

// --- 자동 배치 실행 (Gemini AI 연동) ---
function runAutoLayout() {
    // 1단계: 도면 완성 여부 확인
    if (typeof isPolygonClosed === 'function' && !isPolygonClosed()) {
        alert('⚠️ 1단계: 우측 캔버스에 창고 평면도를 먼저 완성해주세요!\n(점을 찍어 도형을 닫아야 합니다)');
        return;
    }
    // 4단계: 필수값 확인
    const checks = [
        ['pallet-w',             '파렛트 가로(W)'],
        ['pallet-d',             '파렛트 세로/깊이(D)'],
        ['pallet-h',             '적재 높이(H)'],
        ['pallet-weight',        '총 중량'],
        ['forklift-lift-height', '최대 인상높이'],
        ['forklift-ast',         '직각교차 통로폭(AST)'],
        ['rack-levels',          '설치 단수'],
    ];
    for (const [id, label] of checks) {
        const el = document.getElementById(id);
        if (!el || !el.value || parseInt(el.value) <= 0) {
            alert(`⚠️ 4단계 [${label}]를 입력해주세요!`);
            el?.focus();
            return;
        }
    }

    const btn = document.getElementById('run-layout-btn');
    btn.disabled = true;
    btn.innerHTML = '⏳ Gemini AI 분석 중...';

    // DOM 입력에서 실제/자동계산된 벽면 치수 수집 (0으로 전송되는 것 방지)
    const edgeLengths = [];
    const inputs = document.querySelectorAll('#inputs-container input[type="number"]');
    inputs.forEach(input => {
        edgeLengths.push(parseInt(input.value) || 0);
    });

    const obstacles = (typeof window.obstacles !== 'undefined') ? window.obstacles : [];

    const payload = {
        pallet_w:       parseInt(document.getElementById('pallet-w').value),
        pallet_d:       parseInt(document.getElementById('pallet-d').value),
        pallet_h:       parseInt(document.getElementById('pallet-h').value),
        pallet_weight:  parseInt(document.getElementById('pallet-weight').value),
        fork_direction: document.querySelector('input[name="forkDirection"]:checked')?.value ?? 'W',
        lift_height:    parseInt(document.getElementById('forklift-lift-height').value),
        ast:            parseInt(document.getElementById('forklift-ast').value),
        forklift_type:  document.getElementById('forklift-type').value,
        rack_levels:    parseInt(document.getElementById('rack-levels').value),
        rack_bays:      parseInt(document.getElementById('rack-bays').value) || 0,
        rack_height:    parseInt(document.getElementById('rack-height').value) || 0,
        edge_lengths:   edgeLengths,
        obstacles:      obstacles.map(o => ({type: o.type, name: o.name})),
        user_request:   document.getElementById('ai-request')?.value ?? '',
    };


    fetch('/api/canvas/analyze', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(res => res.json())
    .then(json => {
        btn.disabled = false;
        btn.innerHTML = '🚀 파렛트랙 자동 배치 실행';

        if (!json.success) {
            alert('❌ AI 분석 중 오류가 발생했습니다:\n' + json.message);
            return;
        }

        const d = json.data;
        showAiResult(d);

        // --- 캔버스에 2D 파랫트랙 배치 시각화 트리거 ---
        const userReq = payload.user_request;
        let targetLine = 1; // 기본값 1번 라인
        const match = userReq.match(/(\d+)\s*번\s*(라인|벽면|벽)/);
        if (match) {
            targetLine = parseInt(match[1]);
        }
        
        // 엣지 인덱스 바운드 체크
        let edgeIndex = targetLine - 1;
        if (edgeIndex < 0 || edgeIndex >= edgeLengths.length) {
            edgeIndex = 0; // 범위를 벗어나면 기본 1번 벽면(index 0)에 배치
        }

        window.rackSpecs = {
            edgeIndex: edgeIndex,
            levels: payload.rack_levels,
            bays: payload.rack_bays,
            beamLength: d.beam_length_mm || payload.pallet_w,
            rackDepth: d.rack_depth_mm || payload.pallet_d
        };

        // 캔버스 다시 그리기
        if (typeof draw === 'function') {
            draw();
        }

        // 배치 완료 후 견적요청 버튼 표시
        const reqBtn = document.getElementById('request-quote-btn');
        if (reqBtn) reqBtn.classList.remove('d-none');
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = '🚀 파렛트랙 자동 배치 실행';
        alert('❌ 서버 통신 오류: ' + err.message);
    });
}

// --- AI 분석 결과 캔버스 우측 하단에 표시 (요청에 따라 summary 위주로 간소화) ---
function showAiResult(d) {
    const wrapper = document.getElementById('canvas-wrapper');
    const old = document.getElementById('ai-result-panel');
    if (old) old.remove();

    const notes = (d.notes || []).map(n => `<li>${n}</li>`).join('');
    const panel = document.createElement('div');
    panel.id = 'ai-result-panel';
    panel.style.cssText = 'position:absolute;bottom:12px;right:12px;width:320px;background:rgba(15,23,42,0.95);border:1px solid rgba(56,189,248,0.4);border-radius:0.75rem;padding:16px;font-size:0.85rem;z-index:100;color:#e2e8f0;line-height:1.4;box-shadow: 0 10px 30px rgba(0,0,0,0.5);';
    panel.innerHTML = `
        <div class="d-flex justify-content-between align-items-center mb-2">
            <strong class="text-info" style="font-size:0.9rem;">✨ Gemini AI 설계 분석 요약</strong>
            <button onclick="this.closest('#ai-result-panel').remove()" style="background:none;border:none;color:#94a3b8;font-size:1.1rem;cursor:pointer;padding:0;line-height:1;">✕</button>
        </div>
        <div style="color:#e2e8f0;margin-bottom:8px;word-break:break-all;">
            ${d.summary || '분석 결과를 불러올 수 없습니다.'}
        </div>
        ${notes ? `
        <div class="border-top border-secondary pt-2 mt-2">
            <small class="text-warning d-block mb-1">💡 설계 주의사항:</small>
            <ul style="padding-left:14px;margin:0;color:#94a3b8;font-size:0.78rem;">${notes}</ul>
        </div>` : ''}
    `;
    wrapper.appendChild(panel);
}


// --- 견적 요청 모달 제출 ---
function submitQuoteRequest() {
    const company = document.getElementById('modal-company').value.trim();
    const name    = document.getElementById('modal-name').value.trim();
    const phone   = document.getElementById('modal-phone').value.trim();
    const address = document.getElementById('modal-address').value.trim();

    if (!company || !name || !phone || !address) {
        alert('★ 표시된 필수 항목을 모두 입력해주세요.');
        return;
    }

    // TODO: 실제 백엔드 FormData 전송 (추후 구현)
    alert(`✅ 견적 요청이 접수되었습니다!\n\n회사: ${company}\n담당자: ${name}\n연락처: ${phone}\n현장: ${address}\n\n담당자가 빠른 시일 안에 연락드리겠습니다! 감사합니다 💕`);
    bootstrap.Modal.getInstance(document.getElementById('quoteRequestModal'))?.hide();
}

// --- 도면 입력 생성 함수 ---
window.generateCustomInputs = function(numEdges) {
    const diagramContainer = document.getElementById('diagram-container');
    const inputsContainer  = document.getElementById('inputs-container');

    diagramContainer.innerHTML = `<p class="text-success fw-bold py-3 m-0">🎉 총 ${numEdges}각형 도면이 확정되었습니다!</p>`;

    if (typeof clearEdgeLengths === 'function') clearEdgeLengths();

    let html = '';
    const initialRatio = 25; // 1px = 25mm 임시 스케일 매핑
    
    for (let i = 1; i <= numEdges; i++) {
        let calculatedVal = 0;
        if (typeof points !== 'undefined' && points[i-1] && points[i]) {
            const p1 = points[i-1];
            const p2 = points[i];
            const pxDist = Math.hypot(p2.x - p1.x, p2.y - p1.y);
            calculatedVal = Math.round(pxDist * initialRatio);
            
            // canvas2d.js 내부 edgeLengths 배열 동기화
            if (typeof edgeLengths !== 'undefined') {
                edgeLengths[i-1] = calculatedVal;
            }
        }

        html += `
        <div class="col-6">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-transparent text-info border-secondary">${i}번 선분</span>
                <input type="number" class="form-control bg-transparent text-white border-secondary" value="${calculatedVal > 0 ? calculatedVal : ''}" placeholder="길이(mm)" oninput="updateEdgeLength(${i-1}, this.value)">
            </div>
        </div>`;
    }
    inputsContainer.innerHTML = html;
    
    // 초기 정렬 및 축적 렌더링 강제 트리거
    if (typeof alignAndScalePolygon === 'function') {
        alignAndScalePolygon();
        draw();
    }
};

// --- 초기 로딩 ---
window.addEventListener('DOMContentLoaded', () => {
    const diagramContainer = document.getElementById('diagram-container');
    const inputsContainer  = document.getElementById('inputs-container');
    const canvasGuide      = document.getElementById('canvas-guide');
    const drawingCanvas    = document.getElementById('drawingCanvas');

    if (diagramContainer) diagramContainer.innerHTML = '<p class="text-info fw-bold py-3 m-0">우측 캔버스에 점을 찍어 창고 모양을 완성해주세요!</p>';
    if (inputsContainer) inputsContainer.innerHTML = '';
    if (canvasGuide) canvasGuide.style.display = 'none';
    if (drawingCanvas) drawingCanvas.style.display = 'block';

    if (typeof startCustomDrawing === 'function') startCustomDrawing();
});
</script>

</body>
</html>