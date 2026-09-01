<?php
$isEmbed = isset($_GET['embed']) && in_array(strtolower((string)$_GET['embed']), ['1', 'true', 'yes']);
$theme = strtolower((string)($_GET['theme'] ?? 'dark'));
$isLightTheme = in_array($theme, ['light', 'white']);
?>
<!DOCTYPE html>
<html lang="ko" <?= $isLightTheme ? 'data-bs-theme="light"' : 'data-bs-theme="dark"' ?>>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= !empty($vendor['company_name']) ? htmlspecialchars($vendor['company_name']) . ' - ' : '' ?>파렛트랙 자동 견적 시스템 - B2B SaaS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
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

        /* ☀️ 화이트 모드 (Theme Light) 스타일 오버라이드 */
        html[data-bs-theme="light"] body,
        body.theme-light {
            background: #f1f5f9 !important;
            color: #1e293b !important;
        }
        body.theme-light .glass-panel {
            background: #ffffff !important;
            border: 1px solid #e2e8f0 !important;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.03) !important;
        }
        body.theme-light .canvas-container {
            background: #ffffff !important;
            border: 1px dashed #cbd5e1 !important;
        }
        body.theme-light .text-light,
        body.theme-light .text-white {
            color: #0f172a !important;
        }
        body.theme-light .text-secondary,
        body.theme-light .text-muted {
            color: #64748b !important;
        }
        body.theme-light .border-secondary {
            border-color: #e2e8f0 !important;
        }
        body.theme-light .form-control,
        body.theme-light .form-select {
            background-color: #ffffff !important;
            color: #0f172a !important;
            border-color: #cbd5e1 !important;
        }
        body.theme-light .form-control:focus,
        body.theme-light .form-select:focus {
            border-color: #0ea5e9 !important;
            box-shadow: 0 0 0 0.25rem rgba(14, 165, 233, 0.15) !important;
        }
        body.theme-light .form-control::placeholder {
            color: #94a3b8 !important;
        }
        body.theme-light .drag-item {
            background: #f8fafc !important;
            border: 1px solid #cbd5e1 !important;
            color: #1e293b !important;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05) !important;
        }
        body.theme-light .shape-diagram {
            background: #f8fafc !important;
            border: 1px solid #e2e8f0 !important;
        }
        body.theme-light .shape-btn {
            background: #f8fafc !important;
            border: 1px solid #cbd5e1 !important;
            color: #475569 !important;
        }
        body.theme-light .shape-btn:hover {
            background: #e2e8f0 !important;
            color: #0f172a !important;
        }
        body.theme-light .file-drop-zone {
            background: rgba(14, 165, 233, 0.04) !important;
            border-color: rgba(14, 165, 233, 0.4) !important;
        }
        body.theme-light .file-preview-thumb {
            background: #f8fafc !important;
            border-color: #cbd5e1 !important;
            color: #64748b !important;
        }
        body.theme-light #canvas-summary-badge {
            background: #ffffff !important;
            border-color: #bae6fd !important;
            color: #1e293b !important;
            box-shadow: 0 2px 6px rgba(0,0,0,0.05) !important;
        }
        body.theme-light #canvas-remote-ctrl > div {
            background: #ffffff !important;
            border: 1px solid #bae6fd !important;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1), 0 2px 8px rgba(0,0,0,0.05) !important;
        }
        body.theme-light #remote-header {
            color: #0284c7 !important;
        }
        body.theme-light .modal-content {
            background: #ffffff !important;
            color: #1e293b !important;
            border: 1px solid #cbd5e1 !important;
            box-shadow: 0 20px 40px rgba(0,0,0,0.15) !important;
        }
        body.theme-light .modal-content .text-white {
            color: #1e293b !important;
        }
        body.theme-light .modal-content .text-bg-dark {
            background: #ffffff !important;
            color: #1e293b !important;
        }
        body.theme-light .offcanvas {
            background: #ffffff !important;
            color: #1e293b !important;
            border-left-color: #e2e8f0 !important;
        }
        body.theme-light .offcanvas .text-white {
            color: #0f172a !important;
        }
        body.theme-light .offcanvas .btn-close,
        body.theme-light .modal .btn-close {
            filter: none !important;
        }

        /* 📱 아이프레임(iframe) 임베드 시 컴팩트 레이아웃 */
        body.is-embed {
            height: 100vh;
            padding: 0 !important;
            margin: 0 !important;
        }
        body.is-embed .container-fluid {
            padding-top: 8px !important;
            padding-bottom: 8px !important;
            padding-left: 10px !important;
            padding-right: 10px !important;
        }
    </style>
</head>
<body class="<?= $isLightTheme ? 'theme-light' : '' ?> <?= $isEmbed ? 'is-embed' : '' ?>">
<script>
window.vendorUserId = <?= json_encode($vendor['url_slug'] ?? 0) ?>;
window.CANVAS_THEME = <?= json_encode($isLightTheme ? 'light' : 'dark') ?>;
window.IS_EMBED = <?= json_encode($isEmbed) ?>;
</script>
<div class="container-fluid pt-3 px-4 d-flex flex-column h-100">
    <?php if (!$isEmbed): ?>
    <div class="text-center mb-3 flex-shrink-0 position-relative">
        <?php if (!empty($vendor)): ?>
            <h4 class="fw-bold text-light mb-1"><?= htmlspecialchars($vendor['company_name']) ?></h4>
        <?php endif; ?>
        <h2 class="fw-bold" style="background: -webkit-linear-gradient(#38bdf8, #818cf8); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
            스마트 창고 배치 견적
        </h2>
        <p class="text-muted small m-0">복잡한 창고 형태도 드래그 앤 드롭으로 1분 만에 완성! <?php if(!empty($vendor['contact_number'])) echo " (문의: " . htmlspecialchars($vendor['contact_number']) . ")"; ?></p>
        
    </div>
    <?php endif; ?>

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
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="window.location.reload();">🔄 초기화</button>
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

                <!-- 4단계: 랙 설치 희망 제원 -->
                <div class="mb-4 pt-3 border-top border-secondary">
                    <h5 class="fw-semibold mb-3">
                        <span class="step-badge">4단계</span> 랙 설치 희망 제원
                        <button type="button" class="btn btn-link btn-sm p-0 ms-1 text-info text-decoration-none" data-bs-toggle="offcanvas" data-bs-target="#helpOffcanvas" onclick="scrollToHelp('help-rack-specs')">❓</button>
                    </h5>
                    
                    <!-- 이지 모드 자동 계산용 표준값 hidden inputs -->
                    <input type="hidden" id="pallet-w" value="1100">
                    <input type="hidden" id="pallet-d" value="1100">
                    <input type="hidden" id="pallet-h" value="1000">
                    <input type="hidden" id="pallet-weight" value="1000">
                    <input type="hidden" name="forkDirection" id="forkW" value="W">
                    <input type="hidden" id="forklift-type" value="reach">
                    <input type="hidden" id="forklift-lift-height" value="4500">
                    <input type="hidden" id="forklift-ast" value="2800">

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label text-muted small mb-1">설치 단수 <span class="text-danger">*</span></label>
                            <input type="number" onclick="this.select()" class="form-control form-control-sm bg-transparent text-white border-secondary" id="rack-levels" value="3" placeholder="예: 3단">
                        </div>
                        <div class="col-6">
                            <label class="form-label text-muted small mb-1">설치 높이(mm)</label>
                            <input type="number" onclick="this.select()" class="form-control form-control-sm bg-transparent text-white border-secondary" id="rack-height" placeholder="공란시 자동계산">
                        </div>
                    </div>

                    <div class="p-2 rounded mt-2" style="background: rgba(56, 189, 248, 0.08); border: 1px dashed rgba(56, 189, 248, 0.3);">
                        <p class="text-info small m-0" style="font-size: 0.78rem;">
                            💡 <strong>이지 모드 안내:</strong> 가장 범용적인 표준 파렛트(1100×1100) 및 입승식 리치 지게차 제원이 기본 적용되어 자동으로 도면이 꽉 차게 계산됩니다!
                        </p>
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

                    <input type="hidden" id="ai-request" value="">
                </div>

                <!-- 6단계: 실행 -->
                <div class="pt-3 border-top border-secondary pb-2">
                    <h5 class="fw-semibold mb-2"><span class="step-badge">6단계</span> 배치 실행</h5>
                    <p class="text-muted small mb-3">모든 필수 정보가 입력되면 뿅!</p>
                    <button id="run-layout-btn" class="btn btn-primary-gradient w-100 py-3 rounded-3 shadow-lg" onclick="runAutoLayout()">
                        🚀 파렛트랙 배치 실행
                    </button>
                </div>

                <!-- 하단 회사 정보 -->
                <?php if (!empty($vendor)): ?>
                <div class="mt-4 pt-3 border-top border-secondary text-center" style="opacity: 0.8;">
                    <?php if (!empty($vendor['company_logo'])): ?>
                        <img src="<?= htmlspecialchars($vendor['company_logo']) ?>" alt="Logo" class="mb-2" style="max-height: 40px; border-radius: 4px;">
                    <?php endif; ?>
                    <h6 class="text-info fw-bold mb-1"><?= htmlspecialchars($vendor['company_name']) ?></h6>
                    <?php if(!empty($vendor['contact_number'])): ?>
                        <p class="text-muted small mb-1">
                            <i class="fa-solid fa-phone me-1"></i> <?= htmlspecialchars($vendor['contact_number']) ?>
                        </p>
                    <?php endif; ?>
                    <?php if(!empty($vendor['manager_name']) || !empty($vendor['manager_email'])): ?>
                        <p class="text-muted small mb-0" style="font-size: 0.75rem;">
                            <?= htmlspecialchars($vendor['manager_name'] ?? '') ?> 
                            <?= !empty($vendor['manager_email']) ? '(' . htmlspecialchars($vendor['manager_email']) . ')' : '' ?>
                        </p>
                    <?php endif; ?>
                    <div class="mt-3 text-secondary" style="font-size: 0.65rem; letter-spacing: 1px;">
                        POWERED BY ASAMIYA SAAS
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>


        <!-- 우측: 캔버스 및 시각화 -->
        <div class="col-xl-9 col-lg-8 h-100">
            <div class="glass-panel p-4 h-100 d-flex flex-column">
                <!-- 상단 배지 바 (한 줄 컴팩트) -->
                <div class="d-flex align-items-center justify-content-between mb-3 gap-2 flex-wrap">
                    <div class="d-flex align-items-center gap-2 flex-wrap flex-grow-1">
                        <h5 class="fw-semibold text-info m-0 me-1">실시간 2D 배치 도면</h5>
                        <div id="canvas-summary-badge" class="d-none d-md-flex align-items-center gap-2 px-3 py-1 rounded" style="background: rgba(15, 23, 42, 0.85); border: 1px solid rgba(56, 189, 248, 0.4); font-size: 0.82rem;">
                            <span id="top-badge-spec" class="badge bg-primary text-white" style="font-size:0.75rem; font-weight:600; padding:4px 8px; letter-spacing:0.02em;">2585×1000×4500 (2S 3단)</span>
                            <span class="text-secondary">|</span>
                            <span>독립 <strong id="top-badge-indep" class="text-primary">0</strong>대</span>
                            <span class="text-secondary">|</span>
                            <span>연결 <strong id="top-badge-conn" class="text-primary">0</strong>대</span>
                            <span id="top-badge-small-wrap" class="d-none"><span class="text-secondary">|</span> <span class="text-info">작은연결 <strong id="top-badge-small-conn" class="text-info">0</strong>대</span></span>
                            <span id="top-badge-bypass-wrap" class="d-none"><span class="text-secondary">|</span> <span class="text-danger">연결 <strong id="top-badge-bypass" class="text-danger">0</strong>대 (바이패스)</span></span>
                            <span class="text-secondary">|</span>
                            <span class="text-warning">🔗 <strong id="top-badge-holders" class="text-warning">0</strong>홀더</span>
                            <span class="text-secondary">|</span>
                            <span class="text-success">📦 <strong id="top-badge-pallets" class="text-success">0</strong> PLT</span>
                        </div>
                    </div>
                    <div class="flex-shrink-0 d-flex align-items-center gap-2 flex-wrap">
                        <div class="btn-group btn-group-sm" role="group">
                            <a href="/quote/<?= htmlspecialchars($vendor['url_slug'] ?? '') ?>" class="btn btn-outline-info px-3">👨‍💻 전문가 모드</a>
                            <a href="/quote/<?= htmlspecialchars($vendor['url_slug'] ?? '') ?>/easy" class="btn btn-primary-gradient px-3 fw-bold">🟢 이지 모드</a>
                            <a href="/quote/<?= htmlspecialchars($vendor['url_slug'] ?? '') ?>/board" class="btn btn-outline-light px-3">📝 게시판 문의</a>
                        </div>
                        <a href="/quote/<?= htmlspecialchars($vendor['url_slug'] ?? 'asamiya') ?>/video" class="btn btn-sm btn-outline-info">🎥 동영상메뉴얼</a>
                    </div>
                </div>

                <!-- 캔버스 영역 (리모컨 패널 포함, position:relative) -->
                <div class="canvas-container flex-grow-1 position-relative" id="canvas-wrapper" style="overflow: auto; scrollbar-width: thin; min-height: 0;">


                    <!-- 기본 가이드 텍스트 -->
                    <div class="text-center text-secondary" id="canvas-guide">
                        <div style="font-size: 3rem; margin-bottom: 1rem;">✏️</div>
                        <p>좌측 치수를 기반으로<br>창고 평면도가 여기에 실시간으로 그려집니다.</p>
                    </div>
                    <!-- 커스텀 드로잉용 캔버스 (평소엔 숨김) -->
                    <canvas id="drawingCanvas" width="800" height="600" style="display:none;"></canvas>

                    <!-- 🎮 TV 리모컨 스타일 플로팅 컨트롤 패널 (fixed: 캔버스 바깥 우측 상단 고정) -->
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
          
          <!-- 자재 상태 선택 -->
          <div class="col-12">
            <label class="form-label text-muted small mb-2 d-block">자재 상태 선택 <span class="text-danger">★</span></label>
            <div class="form-check form-check-inline">
              <input class="form-check-input" type="radio" name="condition_type" id="cond_new" value="new" checked>
              <label class="form-check-label text-white small" for="cond_new">신규</label>
            </div>
            <div class="form-check form-check-inline">
              <input class="form-check-input" type="radio" name="condition_type" id="cond_used" value="used">
              <label class="form-check-label text-white small" for="cond_used">중고</label>
            </div>
            <div class="form-check form-check-inline">
              <input class="form-check-input" type="radio" name="condition_type" id="cond_both" value="both">
              <label class="form-check-label text-white small" for="cond_both">모두</label>
            </div>
          </div>

          <!-- 직접설치 체크박스 -->
          <div class="col-12">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="modal-self-install" value="1">
              <label class="form-check-label text-white small" for="modal-self-install">
                직접 설치 (자재만 납품받기)
              </label>
            </div>
          </div>

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
            <label class="form-label text-muted small mb-1">이메일 (견적서 수신용) <span class="text-danger">★</span></label>
            <input type="email" class="form-control bg-transparent text-white border-secondary" id="modal-email" placeholder="example@email.com">
          </div>
          
          <div class="col-12">
            <label class="form-label text-muted small mb-1">시공 현장 주소 <span class="text-danger">★</span> <span class="text-secondary" style="font-size:0.7rem;">(최소 시/군/구 수준)</span></label>
            <input type="text" class="form-control bg-transparent text-white border-secondary" id="modal-address" placeholder="예: 경기도 성남시 분당구">
          </div>
          <div class="col-12">
            <label class="form-label text-muted small mb-1">상세내용 입력</label>
            <textarea class="form-control bg-transparent text-white border-secondary" id="modal-details" rows="2" placeholder="추가적인 요청사항이나 현장 특이사항을 적어주세요."></textarea>
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
<script src="/assets/js/canvas2d-easy.js?v=<?= time() ?>"></script>
<script src="/assets/js/canvas-interactions-easy.js?v=<?= time() ?>"></script>
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

// 창고 벽면 길이 입력 검증 헬퍼 함수
function checkWarehouseLengthsEntered(isSubmitting = false) {
    if (typeof isPolygonClosed === 'function' && !isPolygonClosed()) {
        alert('⚠️ 1단계: 우측 캔버스에 창고 평면도를 먼저 완성해주세요!\n(점을 찍어 다각형 외곽선을 닫아야 합니다)');
        return false;
    }

    const inputs = document.querySelectorAll('#inputs-container input[type="number"]');
    if (inputs.length === 0) {
        alert('⚠️ 1단계와 2단계: 우측 캔버스에 창고 평면도를 먼저 완성해주세요!');
        return false;
    }

    for (let i = 0; i < inputs.length; i++) {
        const val = parseInt(inputs[i].value);
        if (!val || val <= 0) {
            alert(`⚠️ 2단계: ${i + 1}번 선분(벽면)의 길이를 입력해주세요!\n모든 벽면의 실제 길이를 입력해야 AI가 정확한 치수로 랙을 배치할 수 있습니다.`);
            inputs[i].focus();
            return false;
        }
    }
    return true;
}

// 드래그앤드롭 파일 업로드 및 AI 요청창 클릭 감지
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

    // AI에게 남길 요청사항 클릭/포커스 시 창고 길이 미입력 체크
    const aiRequestInput = document.getElementById('ai-request');
    if (aiRequestInput) {
        let hasWarnedFocus = false;
        aiRequestInput.addEventListener('focus', () => {
            if (!hasWarnedFocus) {
                if (!checkWarehouseLengthsEntered()) {
                    hasWarnedFocus = true;
                    setTimeout(() => { hasWarnedFocus = false; }, 3000);
                }
            }
        });
        aiRequestInput.addEventListener('click', () => {
            if (!hasWarnedFocus) {
                if (!checkWarehouseLengthsEntered()) {
                    hasWarnedFocus = true;
                    setTimeout(() => { hasWarnedFocus = false; }, 3000);
                }
            }
        });
    }
});

// --- 배치 실행 (수동 모드 진입) ---
function runAutoLayout() {
    // 1단계 & 2단계: 도면 및 벽면 길이 입력 확인
    if (!checkWarehouseLengthsEntered(true)) {
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
    
    // 지게차 인상높이 vs 랙 설치높이 검증
    const palletH = parseInt(document.getElementById('pallet-h')?.value) || 0;
    const levels = parseInt(document.getElementById('rack-levels')?.value) || 0;
    const maxLiftH = parseInt(document.getElementById('forklift-lift-height')?.value) || 0;
    let rackH = parseInt(document.getElementById('rack-height')?.value) || 0;
    
    if (rackH <= 0 && palletH > 0 && levels > 0) {
        const rawH = (palletH * levels) + (levels * 200) + 300;
        rackH = Math.ceil(rawH / 500) * 500;
    }
    
    if (rackH > maxLiftH) {
        alert(`⚠️ 계산된 랙 설치 높이(${rackH.toLocaleString()}mm)가 지게차 최대 인상높이(${maxLiftH.toLocaleString()}mm)를 초과합니다!\n단수를 낮추거나 지게차 제원을 확인해주세요.`);
        document.getElementById('rack-levels')?.focus();
        return;
    }

    const btn = document.getElementById('run-layout-btn');
    btn.innerHTML = '✅ 도면 활성화 완료';
    btn.classList.remove('btn-primary-gradient');
    btn.classList.add('btn-success');

    // 리모컨 패널 자동 표시
    const remoteCtrl = document.getElementById('canvas-remote-ctrl');
    if (remoteCtrl) remoteCtrl.classList.remove('d-none');
    
    // 리모컨 내 견적 버튼 표시
    const remoteQuoteBtn = document.getElementById('remote-quote-btn');
    if (remoteQuoteBtn) remoteQuoteBtn.classList.remove('d-none');

    // 이지 모드: 창고 안을 꽉 채우는 완전 자동 배치 실행!
    if (typeof window.spawnEasyFullLayout === 'function') {
        window.spawnEasyFullLayout();
    } else if (typeof window.spawnInitialRacks === 'function') {
        window.spawnInitialRacks();
    } else if (typeof draw === 'function') {
        draw();
    }
}

// --- AI 분석 결과 (fixed 위치, 좌측 하단, 전체 내용 스크롤) ---
function showAiResult(d) {
    const old = document.getElementById('ai-result-panel');
    if (old) old.remove();

    const panel = document.createElement('div');
    panel.id = 'ai-result-panel';
    // fixed 위치: 화면 우측 하단 고정 (캔버스 스크롤과 무관)
    panel.style.cssText = [
        'position:fixed',
        'bottom:20px',
        'right:20px',
        'width:420px',
        'max-height:65vh',
        'display:flex',
        'flex-direction:column',
        'background:rgba(10,15,30,0.97)',
        'border:1px solid rgba(56,189,248,0.5)',
        'border-radius:0.85rem',
        'padding:0',
        'font-size:0.85rem',
        'z-index:9999',
        'color:#e2e8f0',
        'line-height:1.6',
        'box-shadow:0 12px 40px rgba(0,0,0,0.75)',
        'backdrop-filter:blur(16px)'
    ].join(';');
    
    let text = d.summary || '설치 계획이 정상적으로 수립되었습니다.';
    
    // 1. 마크다운 JSON 블록 제거
    text = text.replace(/```json\s*/gi, '').replace(/```\s*/g, '');
    
    // 2. JSON 문자열 내 summary 값만 추출
    if (text.includes('"summary"')) {
        const match = text.match(/"summary"\s*:\s*"((?:[^"\\]|\\.)*)"/s);
        if (match && match[1]) {
            text = match[1];
        } else {
            text = text.replace(/^\s*\{?\s*"summary"\s*:\s*"?/i, '');
        }
    }
    
    // 3. 이스케이프된 줄바꿈 → 실제 줄바꿈
    text = text.replace(/\\n/g, '\n').replace(/\\r/g, '').replace(/\\t/g, ' ');
    text = text.replace(/\\"/g, '"');
    
    // 4. 마크다운 헤더 → 이모지 소제목
    text = text.replace(/###\s*(.*)/g, '📌 $1');
    text = text.replace(/##\s*(.*)/g, '📋 $1');
    text = text.replace(/#\s*(.*)/g,  '📍 $1');
    text = text.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
    
    // 5. 앞뒤 불필요한 따옴표/괄호 제거
    text = text.replace(/^[\{\s"'\\]+/, '').replace(/[\}\s"'\\]+$/, '').trim();
    
    // 정제된 순수 텍스트 리포트를 전역 변수에 임시 저장 (견적요청 제출 시 DB 저장용)
    window.lastAiSummary = text;
    
    // 6. 줄바꿈을 <br>로 변환 (innerHTML 출력용)
    const htmlText = text.replace(/\n/g, '<br>');

    panel.innerHTML = `
        <div style="padding:10px 14px 8px;border-bottom:1px solid rgba(56,189,248,0.2);display:flex;justify-content:space-between;align-items:center;background:rgba(56,189,248,0.08);border-top-left-radius:0.85rem;border-top-right-radius:0.85rem;flex-shrink:0;">
            <div class="text-info fw-bold d-flex align-items-center gap-1" style="font-size:0.86rem;">
                <span>💡</span>
                <span>AI 설계 요약 및 시공 리포트</span>
            </div>
            <button onclick="document.getElementById('ai-result-panel').remove()" style="background:none;border:none;color:#94a3b8;font-size:1.1rem;cursor:pointer;padding:0;line-height:1;" title="닫기">✕</button>
        </div>
        <div style="padding:12px 14px 14px;overflow-y:auto;flex:1 1 auto;white-space:normal;color:#e2e8f0;word-break:keep-all;line-height:1.7;font-size:0.83rem;">
            ${htmlText}
        </div>
    `;
    document.body.appendChild(panel);
}


// --- 견적 요청 모달 제출 ---
function submitQuoteRequest() {
    if (typeof updateRackFormCounts === 'function') {
        updateRackFormCounts();
    }
    const company = document.getElementById('modal-company').value.trim();
    const name    = document.getElementById('modal-name').value.trim();
    const phone   = document.getElementById('modal-phone').value.trim();
    const email   = document.getElementById('modal-email').value.trim();
    const address = document.getElementById('modal-address').value.trim();
    const details = document.getElementById('modal-details') ? document.getElementById('modal-details').value.trim() : '';

    const conditionTypeNode = document.querySelector('input[name="condition_type"]:checked');
    const conditionType = conditionTypeNode ? conditionTypeNode.value : 'new';
    
    const selfInstallNode = document.getElementById('modal-self-install');
    const selfInstall = (selfInstallNode && selfInstallNode.checked) ? 1 : 0;

    if (!company || !name || !phone || !email || !address) {
        alert('★ 표시된 필수 항목을 모두 입력해주세요.');
        return;
    }

    const submitBtn = document.querySelector('.quote-submit-btn');
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerText = '⏳ 제출 중...';
    }

    const canvas = document.getElementById('drawingCanvas');
    let imgData = '';
    if (canvas) {
        // 화이트모드/다크모드 여부와 관계없이 견적서 저장용 도면 이미지는
        // 항상 최고 가독성을 자랑하는 CAD 청사진(1번 다크모드 반전 스타일)으로 일관되게 캡처합니다.
        const prevTheme = window.CANVAS_THEME;
        const isCurrentlyLight = document.body.classList.contains('theme-light') || window.CANVAS_THEME === 'light';
        
        // 1. 임시로 다크 팔레트로 전환 후 캔버스 그리기
        if (isCurrentlyLight) {
            window.CANVAS_THEME = 'dark';
            document.body.classList.remove('theme-light');
            if (typeof draw === 'function') draw();
        }

        const tempCanvas = document.createElement('canvas');
        tempCanvas.width = canvas.width;
        tempCanvas.height = canvas.height;
        const ctx = tempCanvas.getContext('2d');
        ctx.fillStyle = '#ffffff'; // 프린트용 흰색 배경
        ctx.fillRect(0, 0, tempCanvas.width, tempCanvas.height);
        
        // 색상 반전: 다크모드 선을 고대비 CAD 도면 스타일(주황/갈색 랙, 또렷한 치수선)로 반전
        ctx.filter = 'invert(1)';
        ctx.drawImage(canvas, 0, 0);
        ctx.filter = 'none'; // 필터 초기화
        imgData = tempCanvas.toDataURL('image/jpeg', 0.85);

        // 2. 원래 테마로 즉시 복구
        if (isCurrentlyLight) {
            window.CANVAS_THEME = prevTheme;
            document.body.classList.add('theme-light');
            if (typeof draw === 'function') draw();
        }
    }

    let edge_lengths_array = [];
    const edgeInputs = document.querySelectorAll('#inputs-container input[type="number"]');
    if (edgeInputs.length > 0) {
        edgeInputs.forEach(input => {
            edge_lengths_array.push(input.value || 0);
        });
    } else if (window.edgeLengths && window.edgeLengths.length > 0) {
        window.edgeLengths.forEach(len => {
            edge_lengths_array.push(len);
        });
    }
    const edge_lengths_str = edge_lengths_array.join(', ');

    const pw = document.getElementById('pallet-w') ? document.getElementById('pallet-w').value : '';
    const pd = document.getElementById('pallet-d') ? document.getElementById('pallet-d').value : '';
    const ph = document.getElementById('pallet-h') ? document.getElementById('pallet-h').value : '';
    const pWeight = document.getElementById('pallet-weight') ? document.getElementById('pallet-weight').value : '';
    
    let forkDir = 'W';
    if (document.getElementById('forkD') && document.getElementById('forkD').checked) {
        forkDir = 'D';
    }

    let forkliftText = '입승식(리치) 지게차';
    const forkliftSelect = document.getElementById('forklift-type');
    if (forkliftSelect && forkliftSelect.tagName === 'SELECT') {
        forkliftText = forkliftSelect.options[forkliftSelect.selectedIndex].text;
    } else if (forkliftSelect && forkliftSelect.value) {
        forkliftText = forkliftSelect.value === 'reach' ? '입승식(리치) 지게차' : '좌승식(카운터) 지게차';
    }
    const forkliftLiftHeight = document.getElementById('forklift-lift-height') ? document.getElementById('forklift-lift-height').value : '';
    const forkliftAst = document.getElementById('forklift-ast') ? document.getElementById('forklift-ast').value : '';

    const levels = document.getElementById('rack-levels') ? document.getElementById('rack-levels').value : '3';
    let rHeight = document.getElementById('rack-height') ? document.getElementById('rack-height').value : '';

    const spec = document.getElementById('top-badge-spec') ? document.getElementById('top-badge-spec').innerText : '';
    let rackSpec = '';
    let rackType = '';
    if (spec) {
        const parts = spec.split('(');
        rackSpec = parts[0].trim();
        if (parts[1]) {
            rackType = parts[1].replace(')', '').trim();
        }
    }
    
    const indep = document.getElementById('top-badge-indep') ? document.getElementById('top-badge-indep').innerText : '0';
    const conn = document.getElementById('top-badge-conn') ? document.getElementById('top-badge-conn').innerText : '0';
    const small_conn = document.getElementById('top-badge-small-conn') ? document.getElementById('top-badge-small-conn').innerText : '0';
    const bypass = document.getElementById('top-badge-bypass') ? document.getElementById('top-badge-bypass').innerText : '0';
    const holders = document.getElementById('top-badge-holders') ? document.getElementById('top-badge-holders').innerText : '0';
    const pallets = document.getElementById('top-badge-pallets') ? document.getElementById('top-badge-pallets').innerText : '0';

    if (!rHeight && spec) {
        const match = spec.match(/×\s*\d+\s*×\s*(\d+)/);
        if (match) {
            rHeight = match[1] + ' (자동 계산)';
        }
    } else if (!rHeight) {
        const p_h = parseInt(ph) || 1000;
        const l = parseInt(levels) || 3;
        rHeight = ((p_h + 200) * l) + ' (자동 계산)';
    } else if (rHeight) {
        rHeight += ' mm';
    }

    if (indep === '0' && conn === '0') {
        alert('⚠️ 6단계 [배치 실행] 버튼을 눌러 도면에 랙을 배치한 후 견적을 제출해주세요!');
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerText = '🚀 견적 요청 제출';
        }
        return;
    }

    let rackBypassType = '';
    if (parseInt(bypass) > 0) {
        const l = parseInt(levels) || 3;
        const bpLevels = Math.max(1, l - 1);
        const spanS = Math.max(1, l - 1);
        const bpS = Math.max(1, spanS - 1);
        rackBypassType = bpS + 'S ' + bpLevels + '단';
    }

    const payload = {
        vendor_user_id: window.vendorUserId || 0,
        company: company,
        name: name,
        phone: phone,
        email: email,
        address: address,
        condition_type: conditionType,
        self_install: selfInstall,
        canvas_data: JSON.stringify({
            racks: typeof racks !== 'undefined' ? racks : [],
            points: typeof points !== 'undefined' ? points : [],
            obstacles: typeof obstacles !== 'undefined' ? obstacles : [],
            currentScale: typeof currentScale !== 'undefined' ? currentScale : 1
        }),
        summary: `[신청 모드: 이지 모드]\n` + (details ? `[고객 요청사항]\n${details}\n\n` : '') + (window.lastAiSummary || ''),
        source_mode: 'easy',
        edge_lengths: edge_lengths_str,
        pallet_w: pw,
        pallet_d: pd,
        pallet_h: ph,
        pallet_weight: pWeight,
        fork_direction: forkDir,
        forklift_type: forkliftText,
        forklift_lift_height: forkliftLiftHeight,
        forklift_ast: forkliftAst,
        rack_levels: levels,
        rack_height: rHeight,
        rack_spec: rackSpec,
        rack_type: rackType,
        rack_indep: indep,
        rack_conn: conn,
        rack_small_conn: small_conn,
        rack_bypass: bypass,
        rack_bypass_type: rackBypassType,
        rack_holders: holders,
        rack_pallets: pallets,
        image_data: imgData
    };

    const formData = new FormData();
    formData.append('json_payload', JSON.stringify(payload));
    
    // 첨부된 파일들 추가
    if (typeof uploadedFiles !== 'undefined' && uploadedFiles.length > 0) {
        uploadedFiles.forEach(file => {
            formData.append('extra_files[]', file);
        });
    }

    fetch('/quote/submit', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(res => {
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerText = '🚀 견적 요청 제출';
        }
        if (res.success) {
            alert(`✅ 견적 요청이 성공적으로 접수되었습니다!\n\n회사: ${company}\n담당자: ${name}\n연락처: ${phone}\n현장: ${address}\n\n공급사 담당자가 확인 후 빠른 시일 안에 연락드리겠습니다! 감사합니다 💕`);
            bootstrap.Modal.getInstance(document.getElementById('quoteRequestModal'))?.hide();
        } else {
            alert('❌ 오류: ' + res.message);
        }
    })
    .catch(err => {
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerText = '🚀 견적 요청 제출';
        }
        alert('❌ 서버 통신 오류가 발생했습니다: ' + err.message);
    });
}

// --- 도면 입력 생성 함수 ---
window.generateCustomInputs = function(numEdges) {
    const diagramContainer = document.getElementById('diagram-container');
    const inputsContainer  = document.getElementById('inputs-container');

    diagramContainer.innerHTML = `<p class="text-success fw-bold py-3 m-0">🎉 총 ${numEdges}각형 도면이 확정되었습니다!</p>`;

    if (typeof clearEdgeLengths === 'function') clearEdgeLengths();

    let html = '';
    
    for (let i = 1; i <= numEdges; i++) {
        // 내부 edgeLengths 배열을 초기 비움(0) 상태로 동기화
        if (typeof edgeLengths !== 'undefined') {
            edgeLengths[i-1] = 0;
        }

        html += `
        <div class="col-6">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-transparent text-info border-secondary">${i}번 선분</span>
                <input type="number" id="edge-input-${i-1}" onclick="this.select()" class="form-control bg-transparent text-white border-secondary" value="" placeholder="길이(mm)" oninput="updateEdgeLength(${i-1}, this.value)">
            </div>
        </div>`;
    }
    inputsContainer.innerHTML = html;
    
    // 초기 정렬 및 축적 렌더링 강제 트리거
    if (typeof alignAndScalePolygon === 'function') {
        alignAndScalePolygon();
    }
};

// --- 전역 인터랙션 모드 제어 ---
window.activeInteractMode = null; // 기본은 null (이동 모드)

window.setInteractMode = function(mode) {
    const btnRotate = document.getElementById('mode-btn-rotate');
    const btnExtend = document.getElementById('mode-btn-extend');
    const btnCopy = document.getElementById('mode-btn-copy');
    const btnBypass = document.getElementById('mode-btn-bypass');
    const btnDelete = document.getElementById('mode-btn-delete');
    
    // 이미 활성화된 모드를 다시 클릭 시 모드 해제(null)
    if (window.activeInteractMode === mode) {
        window.activeInteractMode = null;
    } else {
        window.activeInteractMode = mode;
    }
    
    // 항상 뱃지 정보 즉시 업데이트
    if (typeof updateRackFormCounts === 'function') {
        updateRackFormCounts();
    }
    
    // 버튼 스타일 리셋
    if (btnRotate) {
        btnRotate.style.background = 'rgba(251,191,36,0.06)';
        btnRotate.style.color = '#fbbf24';
    }
    if (btnExtend) {
        btnExtend.style.background = 'rgba(56,189,248,0.06)';
        btnExtend.style.color = '#38bdf8';
    }
    if (btnCopy) {
        btnCopy.style.background = 'rgba(52,211,153,0.06)';
        btnCopy.style.color = '#34d399';
    }
    if (btnBypass) {
        btnBypass.style.background = 'rgba(244,63,94,0.06)';
        btnBypass.style.color = '#f43f5e';
    }
    if (btnDelete) {
        btnDelete.style.background = 'rgba(239,68,68,0.06)';
        btnDelete.style.color = '#ef4444';
    }
    const btnLevels = document.getElementById('mode-btn-levels');
    if (btnLevels) {
        btnLevels.style.background = 'rgba(168,85,247,0.06)';
        btnLevels.style.color = '#a855f7';
    }
    
    // 활성화된 모드 버튼 하이라이트
    if (window.activeInteractMode === 'rotate' && btnRotate) {
        btnRotate.style.background = '#fbbf24';
        btnRotate.style.color = '#000';
    } else if (window.activeInteractMode === 'extend' && btnExtend) {
        btnExtend.style.background = '#38bdf8';
        btnExtend.style.color = '#000';
    } else if (window.activeInteractMode === 'copy' && btnCopy) {
        btnCopy.style.background = '#34d399';
        btnCopy.style.color = '#000';
    } else if (window.activeInteractMode === 'bypass' && btnBypass) {
        btnBypass.style.background = '#f43f5e';
        btnBypass.style.color = '#000';
    } else if (window.activeInteractMode === 'delete' && btnDelete) {
        btnDelete.style.background = '#ef4444';
        btnDelete.style.color = '#fff';
    } else if (window.activeInteractMode === 'levels' && btnLevels) {
        btnLevels.style.background = '#a855f7';
        btnLevels.style.color = '#fff';
    }
    
    // 캔버스 즉시 갱신 (핸들 렌더링 변경 반영)
    if (typeof updateRackFormCounts === 'function') {
        updateRackFormCounts();
    }
    if (typeof draw === 'function') {
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

<!-- 🎮 TV 리모컨 컨트롤 패널 (position:fixed - 항상 우측 상단 고정) -->
<div id="canvas-remote-ctrl" class="d-none" style="position:fixed; top:80px; right:20px; z-index:9998; user-select:none;">
    <div style="
        background: linear-gradient(160deg, rgba(10,15,28,0.98) 0%, rgba(22,33,52,0.98) 100%);
        border: 1px solid rgba(56,189,248,0.4);
        border-radius: 22px;
        padding: 14px 11px 14px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.7), inset 0 1px 0 rgba(255,255,255,0.06);
        width: 128px;
        backdrop-filter: blur(16px);
    ">
        <!-- 헤더 -->
        <div id="remote-header" class="text-center mb-2" style="font-size:0.63rem; color:rgba(148,163,184,0.75); letter-spacing:0.1em; font-weight:700; cursor:grab;">📐 CANVAS</div>

        <!-- 줌 버튼 행 -->
        <div class="d-flex justify-content-between gap-1 mb-2">
            <button onclick="zoomIn()" title="줌인" style="
                flex:1; padding:6px 0; border-radius:8px; border:none;
                background: rgba(56,189,248,0.13); color:#38bdf8; font-size:1.05rem;
                cursor:pointer; transition: background 0.15s;
            " onmouseover="this.style.background='rgba(56,189,248,0.3)'" onmouseout="this.style.background='rgba(56,189,248,0.13)'">＋</button>
            <button onclick="resetZoom()" title="원위치" style="
                flex:1; padding:6px 0; border-radius:8px; border:none;
                background: rgba(99,102,241,0.18); color:#a5b4fc; font-size:0.68rem; font-weight:700;
                cursor:pointer; transition: background 0.15s;
            " onmouseover="this.style.background='rgba(99,102,241,0.38)'" onmouseout="this.style.background='rgba(99,102,241,0.18)'">원위치</button>
            <button onclick="zoomOut()" title="줌아웃" style="
                flex:1; padding:6px 0; border-radius:8px; border:none;
                background: rgba(56,189,248,0.13); color:#38bdf8; font-size:1.05rem;
                cursor:pointer; transition: background 0.15s;
            " onmouseover="this.style.background='rgba(56,189,248,0.3)'" onmouseout="this.style.background='rgba(56,189,248,0.13)'">－</button>
        </div>

        <!-- 방향 십자 키패드 -->
        <div style="display:flex; justify-content:center; margin-bottom:4px;">
            <button onclick="panCanvas(0,-60)" title="위로" style="
                width:36px; height:30px; border-radius:7px; border:none;
                background: rgba(51,65,85,0.75); color:#94a3b8; font-size:0.9rem;
                cursor:pointer; transition: all 0.12s; display:flex; align-items:center; justify-content:center;
            " onmouseover="this.style.background='rgba(56,189,248,0.28)';this.style.color='#38bdf8'" onmouseout="this.style.background='rgba(51,65,85,0.75)';this.style.color='#94a3b8'">▲</button>
        </div>
        <div style="display:flex; justify-content:center; gap:4px; margin-bottom:4px;">
            <button onclick="panCanvas(-60,0)" title="왼쪽" style="
                width:36px; height:30px; border-radius:7px; border:none;
                background: rgba(51,65,85,0.75); color:#94a3b8; font-size:0.9rem;
                cursor:pointer; transition: all 0.12s; display:flex; align-items:center; justify-content:center;
            " onmouseover="this.style.background='rgba(56,189,248,0.28)';this.style.color='#38bdf8'" onmouseout="this.style.background='rgba(51,65,85,0.75)';this.style.color='#94a3b8'">◀</button>
            <div style="width:36px; height:30px; border-radius:7px; background:rgba(22,33,52,0.8); border:1px solid rgba(56,189,248,0.12);"></div>
            <button onclick="panCanvas(60,0)" title="오른쪽" style="
                width:36px; height:30px; border-radius:7px; border:none;
                background: rgba(51,65,85,0.75); color:#94a3b8; font-size:0.9rem;
                cursor:pointer; transition: all 0.12s; display:flex; align-items:center; justify-content:center;
            " onmouseover="this.style.background='rgba(56,189,248,0.28)';this.style.color='#38bdf8'" onmouseout="this.style.background='rgba(51,65,85,0.75)';this.style.color='#94a3b8'">▶</button>
        </div>
        <div style="display:flex; justify-content:center; margin-bottom:10px;">
            <button onclick="panCanvas(0,60)" title="아래로" style="
                width:36px; height:30px; border-radius:7px; border:none;
                background: rgba(51,65,85,0.75); color:#94a3b8; font-size:0.9rem;
                cursor:pointer; transition: all 0.12s; display:flex; align-items:center; justify-content:center;
            " onmouseover="this.style.background='rgba(56,189,248,0.28)';this.style.color='#38bdf8'" onmouseout="this.style.background='rgba(51,65,85,0.75)';this.style.color='#94a3b8'">▼</button>
        </div>

        <!-- 구분선 -->
        <div style="height:1px; background: rgba(56,189,248,0.18); margin: 6px 0 8px;"></div>

        <!-- 🛠️ 편집 모드 선택 패널 -->
        <div class="text-center mb-1" style="font-size:0.6rem; color:rgba(148,163,184,0.6); letter-spacing:0.05em; font-weight:700;">🛠️ EDIT MODE</div>
        <div style="display:flex; flex-direction:column; gap:4px; margin-bottom:8px;">
            <!-- 회전 모드 (Amber) -->
            <button id="mode-btn-rotate" onclick="setInteractMode('rotate')" style="
                width:100%; border: 1px solid rgba(251,191,36,0.3); border-radius:8px;
                background: rgba(251,191,36,0.06); color:#fbbf24; font-size:0.7rem; font-weight:700;
                padding:6px 0; cursor:pointer; transition: all 0.2s; display:flex; align-items:center; justify-content:center; gap:4px;
            " onmouseover="if(window.activeInteractMode!=='rotate') this.style.background='rgba(251,191,36,0.18)'" onmouseout="if(window.activeInteractMode!=='rotate') this.style.background='rgba(251,191,36,0.06)'">
                <span>↻</span> <span>회전 모드</span>
            </button>
            <!-- 연장 모드 (Sky Blue) -->
            <button id="mode-btn-extend" onclick="setInteractMode('extend')" style="
                width:100%; border: 1px solid rgba(56,189,248,0.3); border-radius:8px;
                background: rgba(56,189,248,0.06); color:#38bdf8; font-size:0.7rem; font-weight:700;
                padding:6px 0; cursor:pointer; transition: all 0.2s; display:flex; align-items:center; justify-content:center; gap:4px;
            " onmouseover="if(window.activeInteractMode!=='extend') this.style.background='rgba(56,189,248,0.18)'" onmouseout="if(window.activeInteractMode!=='extend') this.style.background='rgba(56,189,248,0.06)'">
                <span>⬌</span> <span>연장 편집</span>
            </button>
            <!-- 복사 모드 (Emerald) -->
            <button id="mode-btn-copy" onclick="setInteractMode('copy')" style="
                width:100%; border: 1px solid rgba(52,211,153,0.3); border-radius:8px;
                background: rgba(52,211,153,0.06); color:#34d399; font-size:0.7rem; font-weight:700;
                padding:6px 0; cursor:pointer; transition: all 0.2s; display:flex; align-items:center; justify-content:center; gap:4px;
            " onmouseover="if(window.activeInteractMode!=='copy') this.style.background='rgba(52,211,153,0.18)'" onmouseout="if(window.activeInteractMode!=='copy') this.style.background='rgba(52,211,153,0.06)'">
                <span>❐</span> <span>복사 편집</span>
            </button>
            <!-- 바이패스 모드 (Rose) - 텍스트만 표시 -->
            <button id="mode-btn-bypass" onclick="setInteractMode('bypass')" style="
                width:100%; border: 1px solid rgba(244,63,94,0.3); border-radius:8px;
                background: rgba(244,63,94,0.06); color:#f43f5e; font-size:0.7rem; font-weight:700;
                padding:6px 0; cursor:pointer; transition: all 0.2s; display:flex; align-items:center; justify-content:center;
            " onmouseover="if(window.activeInteractMode!=='bypass') this.style.background='rgba(244,63,94,0.18)'" onmouseout="if(window.activeInteractMode!=='bypass') this.style.background='rgba(244,63,94,0.06)'">
                <span>Bypass</span>
            </button>
            <!-- 삭제 모드 (Red) -->
            <button id="mode-btn-delete" onclick="setInteractMode('delete')" style="
                width:100%; border: 1px solid rgba(239,68,68,0.3); border-radius:8px;
                background: rgba(239,68,68,0.06); color:#ef4444; font-size:0.7rem; font-weight:700;
                padding:6px 0; cursor:pointer; transition: all 0.2s; display:flex; align-items:center; justify-content:center; gap:4px;
            " onmouseover="if(window.activeInteractMode!=='delete') this.style.background='rgba(239,68,68,0.18)'" onmouseout="if(window.activeInteractMode!=='delete') this.style.background='rgba(239,68,68,0.06)'">
                <span>✕</span> <span>삭제 모드</span>
            </button>
            
            <!-- 단수 편집 모드 (Purple) -->
            <button id="mode-btn-levels" onclick="setInteractMode('levels')" style="
                width:100%; border: 1px solid rgba(168,85,247,0.3); border-radius:8px;
                background: rgba(168,85,247,0.06); color:#a855f7; font-size:0.7rem; font-weight:700;
                padding:6px 0; cursor:pointer; transition: all 0.2s; display:flex; align-items:center; justify-content:center; gap:4px; margin-bottom:8px;
            " onmouseover="if(window.activeInteractMode!=='levels') this.style.background='rgba(168,85,247,0.18)'" onmouseout="if(window.activeInteractMode!=='levels') this.style.background='rgba(168,85,247,0.06)'">
                <span>☰</span> <span>단수 편집</span>
            </button>
            
            <!-- 도면자동정렬 버튼 -->
            <button id="remote-align-btn" onclick="if(window.autoAlignRacks) window.autoAlignRacks();" style="
                width:100%; border: 1px solid rgba(56,189,248,0.5); border-radius:8px;
                background: rgba(56,189,248,0.1); color:#38bdf8; font-size:0.7rem; font-weight:700;
                padding:6px 0; cursor:pointer; transition: all 0.2s; display:flex; align-items:center; justify-content:center;
            " onmouseover="this.style.background='rgba(56,189,248,0.25)'" onmouseout="this.style.background='rgba(56,189,248,0.1)'">
                <span>🎛️</span> <span style="margin-left:4px;">도면자동정렬</span>
            </button>
            
            <!-- 기본 랙 (드래그 스폰) -->
            <div id="remote-drag-rack" draggable="true" ondragstart="handleDragStart(event, 'rack')" style="
                width:100%; border: 1px solid rgba(139,92,246,0.5); border-radius:8px;
                background: rgba(139,92,246,0.15); color:#c4b5fd; font-size:0.7rem; font-weight:700;
                padding:6px 0; cursor:grab; transition: all 0.2s; display:flex; align-items:center; justify-content:center;
            " onmouseover="this.style.background='rgba(139,92,246,0.3)'" onmouseout="this.style.background='rgba(139,92,246,0.15)'">
                <span>🟦</span> <span style="margin-left:4px;">기본 랙 (드래그)</span>
            </div>
        </div>

        <!-- 구분선 -->
        <div style="height:1px; background: rgba(56,189,248,0.18); margin: 2px 0 8px;"></div>

        <!-- 견적 요청 버튼 (빨간색) -->
        <button id="remote-quote-btn" class="d-none" data-bs-toggle="modal" data-bs-target="#quoteRequestModal" style="
            width:100%; border: 1px solid rgba(239,68,68,0.7); border-radius:10px;
            background: rgba(220,38,38,0.2); color:#fca5a5;
            font-size:0.7rem; font-weight:700; padding:18px 4px;
            cursor:pointer; transition: all 0.2s; line-height:1.35; letter-spacing:0.01em;
        " onmouseover="this.style.background='rgba(220,38,38,0.42)';this.style.color='#fff'" onmouseout="this.style.background='rgba(220,38,38,0.2)';this.style.color='#fca5a5'">
            🔴 견적요청
        </button>
    </div>
</div>

<!-- 개별 칸 단수/높이 커스텀 설정 모달 -->
<div class="modal fade" id="customLevelModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
    <div class="modal-content" style="background: rgba(15,23,42,0.95); border: 1px solid rgba(168,85,247,0.3); border-radius: 12px; backdrop-filter: blur(10px);">
      <div class="modal-header border-bottom border-secondary">
        <h5 class="modal-title" style="color: #a855f7; font-weight: 700;">칸(베이) 설정 변경</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-white">
        <input type="hidden" id="modal-custom-rack-idx">
        <input type="hidden" id="modal-custom-row">
        <input type="hidden" id="modal-custom-span">
        
        <div class="mb-3">
            <label class="form-label text-muted small mb-1">변경할 단수 입력</label>
            <input type="number" class="form-control bg-transparent text-white border-secondary" id="modal-custom-level" placeholder="예: 2" min="2">
        </div>
        <div class="mb-2">
            <label class="form-label text-muted small mb-1">변경할 기둥 높이 (선택사항)</label>
            <input type="number" class="form-control bg-transparent text-white border-secondary" id="modal-custom-height" placeholder="예: 3500" step="500">
            <div class="form-text text-secondary" style="font-size:0.7rem; margin-top:4px;">
                ※ 500 단위 입력을 권장합니다.<br>
                ※ 비워두시면 단수에 맞춰 자동 계산됩니다.
            </div>
        </div>
      </div>
      <div class="modal-footer border-top border-secondary">
        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">취소</button>
        <button type="button" class="btn btn-sm" id="btn-save-custom-level" style="background: rgba(168,85,247,0.2); color:#c084fc; border:1px solid #a855f7; font-weight:600; padding: 4px 16px;">적용하기</button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. 바이패스 버튼 제어 로직
    const rackLevelsInput = document.getElementById('rack-levels');
    const bypassBtn = document.getElementById('mode-btn-bypass');
    
    function updateBypassBtnState() {
        if (!rackLevelsInput || !bypassBtn) return;
        const levels = parseInt(rackLevelsInput.value) || 0;
        if (levels < 3) {
            bypassBtn.style.display = 'none';
            
            if (window.activeInteractMode === 'bypass' && typeof window.setInteractMode === 'function') {
                window.setInteractMode('bypass'); 
            }
        } else {
            bypassBtn.style.display = 'flex';
        }
    }

    if (rackLevelsInput) {
        rackLevelsInput.addEventListener('input', updateBypassBtnState);
        rackLevelsInput.addEventListener('change', updateBypassBtnState);
    }
    setTimeout(updateBypassBtnState, 500);

    // 2. 리모컨 드래그 로직
    const remoteCtrl = document.getElementById('canvas-remote-ctrl');
    const remoteHeader = document.getElementById('remote-header');
    
    if (remoteCtrl && remoteHeader) {
        let isDraggingRemote = false;
        let remoteOffsetX = 0;
        let remoteOffsetY = 0;
        
        remoteHeader.addEventListener('mousedown', function(e) {
            isDraggingRemote = true;
            const rect = remoteCtrl.getBoundingClientRect();
            remoteOffsetX = e.clientX - rect.left;
            remoteOffsetY = e.clientY - rect.top;
            remoteHeader.style.cursor = 'grabbing';
            // 기존 bottom/right 기준을 top/left로 전환
            remoteCtrl.style.right = 'auto';
            remoteCtrl.style.bottom = 'auto';
            remoteCtrl.style.left = rect.left + 'px';
            remoteCtrl.style.top = rect.top + 'px';
        });
        
        document.addEventListener('mousemove', function(e) {
            if (!isDraggingRemote) return;
            e.preventDefault();
            let newX = e.clientX - remoteOffsetX;
            let newY = e.clientY - remoteOffsetY;
            
            // 화면 밖으로 안 나가게 방어
            const maxX = window.innerWidth - remoteCtrl.offsetWidth;
            const maxY = window.innerHeight - remoteCtrl.offsetHeight;
            newX = Math.max(0, Math.min(newX, maxX));
            newY = Math.max(0, Math.min(newY, maxY));
            
            remoteCtrl.style.left = newX + 'px';
            remoteCtrl.style.top = newY + 'px';
        });
        
        document.addEventListener('mouseup', function() {
            if (isDraggingRemote) {
                isDraggingRemote = false;
                remoteHeader.style.cursor = 'grab';
            }
        });
    }
});
</script>
</body>
</html>
