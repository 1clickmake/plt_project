<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>공급사 관리 센터 - 견적 요청 상세</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- External Vendor Dashboard CSS -->
    <link href="/css/vendor_dashboard.css" rel="stylesheet">
    <style>
        @media print {
            .sidebar, .top-navbar, .btn, button, #floorDetailTabs, .btn-group, .user-profile, .no-print {
                display: none !important;
            }
            body {
                background: #ffffff !important;
                color: #000000 !important;
                font-size: 11pt;
            }
            .main-content {
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
            }
            .content-body {
                padding: 0 !important;
            }
            .glass-panel {
                background: #ffffff !important;
                border: 1px solid #cbd5e1 !important;
                color: #000000 !important;
                box-shadow: none !important;
                margin-bottom: 20px !important;
            }
            .text-light, .text-secondary {
                color: #334155 !important;
            }
            .border-secondary {
                border-color: #cbd5e1 !important;
            }
            /* 📑 멀티 플로어: 탭 구조를 해제하고 모든 층의 도면을 순서대로 전체 출력 */
            #floorDetailTabContent > .tab-pane {
                display: block !important;
                opacity: 1 !important;
                visibility: visible !important;
                margin-bottom: 30px !important;
                page-break-inside: avoid !important;
            }
            .floor-print-header {
                display: block !important;
            }
            .floor-cad-img {
                background: #ffffff !important;
                border: 1px solid #94a3b8 !important;
                max-width: 100% !important;
                height: auto !important;
            }
            .badge {
                border: 1px solid #64748b !important;
                color: #0f172a !important;
                background: transparent !important;
            }
        }
    </style>
</head>
<body>

    <!-- 🧭 좌측 네비게이션 사이드바 -->
    <?php include __DIR__ . '/sidebar.php'; ?>

    <!-- 💻 우측 메인 대시보드 영역 -->
    <main class="main-content">
        <div class="top-navbar">
            <div class="navbar-title fw-bold text-light" style="font-size: 1.1rem;">
                SaaS Dashboard &gt; 견적 요청 상세 보기
            </div>
            <div class="d-flex align-items-center gap-3">
                <div class="btn-group" role="group">
                    <a href="/vendor/quotes/<?= $quote['id'] ?>" class="btn btn-info btn-sm px-3 fw-bold text-dark" style="font-size:0.85rem;">견적상세보기</a>
                    <a href="/vendor/quotes/<?= $quote['id'] ?>/price" class="btn btn-outline-info btn-sm px-3 text-light" style="font-size:0.85rem; border-color: rgba(255,255,255,0.15);">단가확인</a>
                    <a href="/vendor/quotes/<?= $quote['id'] ?>/document" class="btn btn-outline-info btn-sm px-3 text-light" style="font-size:0.85rem; border-color: rgba(255,255,255,0.15);">견적서</a>
                </div>
                <a href="/vendor/quotes" class="btn btn-outline-secondary btn-sm rounded px-3" style="font-size:0.8rem; border-color: rgba(255,255,255,0.15); color:#cbd5e1;">
                    ◀ 목록으로 돌아가기
                </a>
    <div class="user-profile d-flex align-items-center gap-2">
                <?php
                    $dbBtn = \App\Core\Database::getInstance();
                    $stmtBtn = $dbBtn->prepare("SELECT plan FROM users WHERE user_id = ?");
                    $stmtBtn->execute([$user['user_id']]);
                    $btnPlan = $stmtBtn->fetchColumn();
                    if ($btnPlan !== 'pro'):
                ?>
                <!-- <!-- <a href="/vendor/addon_payment" class="btn btn-outline-warning btn-sm fw-bold px-3 py-1 me-3" style="border-radius: 10px;">
                    <i class="fa-solid fa-bolt"></i> 횟수 충전
                </a> --> -->
                <?php endif; ?>
                    <i class="fa-solid fa-circle-user text-info fs-5"></i>
                    <span class="small font-monospace text-light"><?= htmlspecialchars($user['username'] ?? 'User') ?>님</span>
                </div>
            </div>
        </div>

        <div class="content-body">
            <div class="row g-4">
                <!-- 1. 의뢰고객 인적사항 & CAD 도면 시각화 (좌측) -->
                <div class="col-lg-6">
                    <!-- 인적사항 -->
                    <div class="glass-panel p-4 mb-4">
                        <h5 class="fw-bold text-info mb-3 pb-2 border-bottom border-secondary customer-info-title">
                            <span><span>👤</span> 의뢰 고객 정보</span>
                            <span class="fs-6 fw-normal text-muted print-only-date" style="display: none;">(접수일 : <?= date('Y-m-d', strtotime($quote['created_at'])) ?>)</span>
                        </h5>
                        
                        <!-- 화면 표시용 (프린트시 숨김) -->
                        <div class="row g-3 d-print-none position-relative">
                            <?php if (isset($balanceInfo) && $balanceInfo['remaining'] <= 0): ?>
                            <!-- 마스킹 오버레이 -->
                            <div class="position-absolute top-0 start-0 w-100 h-100 d-flex flex-column align-items-center justify-content-center" style="background: rgba(15, 23, 42, 0.85); z-index: 10; border-radius: 8px; backdrop-filter: blur(4px);">
                                <i class="fa-solid fa-lock text-warning fs-3 mb-2"></i>
                                <span class="text-white fw-bold mb-2 text-center" style="font-size: 0.9rem;">메일 발송 횟수를 모두 소진하여<br>고객 정보를 확인할 수 없습니다.</span>
                                <a href="/vendor/addon_payment" class="btn btn-warning btn-sm fw-bold px-3 py-1 text-dark rounded-pill">추가 결제하기</a>
                            </div>
                            <!-- 블러 처리된 내용 -->
                            <div class="row g-3 m-0 w-100" style="filter: blur(6px) grayscale(50%); opacity: 0.3; user-select: none;">
                            <?php else: ?>
                            <div class="row g-3 m-0 w-100">
                            <?php endif; ?>
                            
                                <div class="col-6">
                                    <span class="text-light opacity-75 small d-block">회사명</span>
                                    <span class="text-light fw-bold fs-5"><?= htmlspecialchars($quote['company']) ?></span>
                                </div>
                                <div class="col-6">
                                    <span class="text-light opacity-75 small d-block">담당자</span>
                                    <span class="text-light fw-bold fs-5"><?= htmlspecialchars($quote['name']) ?></span>
                                </div>
                                <div class="col-6">
                                    <span class="text-light opacity-75 small d-block">연락처</span>
                                    <a href="tel:<?= htmlspecialchars($quote['phone']) ?>" class="text-info fw-semibold font-monospace fs-5"><?= htmlspecialchars($quote['phone']) ?></a>
                                </div>
                                <div class="col-6">
                                    <span class="text-light opacity-75 small d-block">접수일시</span>
                                    <span class="text-light font-monospace fs-6"><?= date('Y-m-d H:i:s', strtotime($quote['created_at'])) ?></span>
                                </div>
                                <div class="col-12">
                                    <span class="text-light opacity-75 small d-block">시공 현장 주소</span>
                                    <span class="text-light fw-semibold"><?= htmlspecialchars($quote['address']) ?></span>
                                </div>
                            </div>
                        </div>

                        <!-- 프린트 전용 레이아웃 -->
                        <div class="d-none d-print-block print-customer-info" style="font-size: 80%; line-height: 1.6;">
                            <div class="d-flex">
                                <div style="flex: 1;"><strong>회사명 :</strong> <?= htmlspecialchars($quote['company']) ?></div>
                                <div style="flex: 1;"><strong>담당자 :</strong> <?= htmlspecialchars($quote['name']) ?></div>
                            </div>
                            <div class="d-flex mt-1">
                                <div style="flex: 1;"><strong>연락처 :</strong> <?= htmlspecialchars($quote['phone']) ?></div>
                                <div style="flex: 1;"><strong>시공현장주소 :</strong> <?= htmlspecialchars($quote['address']) ?></div>
                            </div>
                        </div>
                    </div>

                    <!-- CAD 도면 시각화 캔버스 / 이미지 (멀티 플로어 지원) -->
                    <div class="glass-panel p-4">
                        <?php
                            $cData = json_decode($quote['canvas_data'] ?? '{}', true);
                            $quoteFloors = $cData['floors'] ?? [];
                            $hasMultiFloors = is_array($quoteFloors) && count($quoteFloors) > 1;
                        ?>
                        <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom border-secondary">
                            <h5 class="m-0 fw-bold text-success d-flex align-items-center gap-2">
                                <span>📐</span> 배치 설계 도면 <?= $hasMultiFloors ? '(총 ' . count($quoteFloors) . '개 구역)' : '캡쳐' ?>
                            </h5>
                            <div>
                                <button onclick="window.print()" class="btn btn-sm btn-outline-primary rounded px-2 py-1 me-2" style="font-size: 0.75rem;">
                                    <i class="fa-solid fa-print"></i> 프린트 출력
                                </button>
                                <?php if (!empty($quote['image_path'])): ?>
                                    <a href="<?= htmlspecialchars($quote['image_path']) ?>" target="_blank" class="btn btn-sm btn-outline-info rounded px-2 py-1 me-2" style="font-size: 0.75rem;">
                                        <i class="fa-solid fa-magnifying-glass-plus"></i> 전체이미지 보기
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if ($hasMultiFloors): ?>
                            <!-- 📑 멀티 플로어 탭 네비게이션 -->
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <ul class="nav nav-pills gap-1 bg-dark p-1 rounded border border-secondary flex-grow-1" id="floorDetailTabs" role="tablist">
                                    <?php foreach ($quoteFloors as $idx => $f): ?>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link <?= $idx === 0 ? 'active' : '' ?> py-1 px-3 fw-bold" style="font-size:0.85rem;" id="floor-tab-<?= $idx ?>" data-bs-toggle="pill" data-bs-target="#floor-pane-<?= $idx ?>" type="button" role="tab">
                                                🏢 <?= htmlspecialchars($f['name'] ?? ('구역 ' . ($idx + 1))) ?>
                                            </button>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            
                            <div class="tab-content" id="floorDetailTabContent">
                                <?php foreach ($quoteFloors as $idx => $f): ?>
                                    <div class="tab-pane fade <?= $idx === 0 ? 'show active' : '' ?>" id="floor-pane-<?= $idx ?>" role="tabpanel">
                                        <!-- 프린트 전용 구역 타이틀 (인쇄 시 각 층 상단에 자동 출력) -->
                                        <div class="floor-print-header d-none mb-2 pb-1 border-bottom border-dark">
                                            <h5 class="fw-bold m-0 text-dark">🏢 <?= htmlspecialchars($f['name'] ?? ('구역 ' . ($idx + 1))) ?> 설계 도면</h5>
                                        </div>

                                        <!-- 해당 구역 스펙 요약 뱃지 -->
                                        <div class="d-flex flex-wrap gap-2 mb-2 p-2 rounded bg-dark border border-secondary small text-light align-items-center">
                                            <span class="badge bg-primary">독립 <?= intval($f['indep'] ?? 0) ?>대</span>
                                            <span class="badge bg-info text-dark">연결 <?= intval($f['conn'] ?? 0) ?>대</span>
                                            <?php if (!empty($f['bypass'])): ?>
                                                <span class="badge bg-danger">바이패스 <?= intval($f['bypass']) ?>대</span>
                                            <?php endif; ?>
                                            <span class="badge bg-success">📦 <?= intval($f['pallets'] ?? 0) ?> PLT</span>
                                            <?php if (!empty($f['spec'])): ?>
                                                <span class="text-secondary ms-auto small">규격: <?= htmlspecialchars($f['spec']) ?></span>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <!-- 도면 이미지 -->
                                        <div class="text-center position-relative mb-3">
                                            <?php 
                                                $fImg = !empty($f['image_path']) ? $f['image_path'] : (!empty($f['capturedImage']) ? $f['capturedImage'] : '');
                                                // fallback: 층별 이미지가 없고 대표 이미지가 존재할 때
                                                if (empty($fImg)) {
                                                    if ($idx === 0 || count($quoteFloors) === 1) {
                                                        $fImg = $quote['image_path'] ?? '';
                                                    }
                                                }
                                            ?>
                                            <?php if (!empty($fImg)): ?>
                                                <a href="<?= htmlspecialchars($fImg) ?>" target="_blank">
                                                    <img src="<?= htmlspecialchars($fImg) ?>" alt="<?= htmlspecialchars($f['name'] ?? '도면') ?>" class="floor-cad-img" style="
                                                        background: #090d16;
                                                        border: 1px solid rgba(255,255,255,0.08);
                                                        border-radius: 12px;
                                                        width: 100%;
                                                        height: auto;
                                                    ">
                                                </a>
                                            <?php else: ?>
                                                <div class="p-4 text-secondary border border-secondary rounded text-center">
                                                    <i class="fa-solid fa-image-slash fa-2x mb-2 opacity-50"></i><br>
                                                    해당 구역의 캡처 이미지가 없습니다.
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <!-- 기존 단일 도면 이미지 표시 -->
                            <div class="text-center">
                                <?php if (!empty($quote['image_path'])): ?>
                                    <img src="<?= htmlspecialchars($quote['image_path']) ?>" alt="도면 캡쳐" class="floor-cad-img" style="
                                        background: #090d16;
                                        border: 1px solid rgba(255,255,255,0.08);
                                        border-radius: 12px;
                                        width: 100%;
                                        height: auto;
                                    ">
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <!-- 첨부파일 (이미지는 출력, PDF/기타는 다운로드) -->
                        <?php if (!empty($quote['extra_files'])): ?>
                            <?php 
                                $extraFiles = htmlspecialchars_decode($quote['extra_files'] ?? '', ENT_QUOTES);
                                $decodeLimit = 5;
                                while (is_string($extraFiles) && $decodeLimit > 0) {
                                    $decoded = json_decode($extraFiles, true);
                                    if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
                                        break;
                                    }
                                    $extraFiles = $decoded;
                                    $decodeLimit--;
                                }
                                if (!is_array($extraFiles)) {
                                    $extraFiles = [];
                                }
                            ?>
                            <!-- DEBUG_EXTRA_FILES: 원본=<?= htmlspecialchars($quote['extra_files']) ?> 파싱결과=<?= htmlspecialchars(json_encode($extraFiles, JSON_UNESCAPED_UNICODE)) ?> -->
                            <?php if (!empty($extraFiles)): ?>
                                <div class="mt-4 pt-3 <?php if (!empty($quote['image_path'])): ?>border-top border-secondary<?php endif; ?> text-start">
                                    <h6 class="text-white fw-bold mb-3">📎 고객 추가 첨부파일</h6>
                                    
                                    <?php foreach ($extraFiles as $file): ?>
                                        <?php 
                                            $ext = strtolower(pathinfo($file['original_name'], PATHINFO_EXTENSION));
                                            $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif']);
                                        ?>
                                        
                                        <?php if ($isImage): ?>
                                            <!-- 이미지는 직접 렌더링 -->
                                            <div class="mb-3 text-center">
                                                <div class="text-light text-start ps-4 small mb-1"><i class="fa-regular fa-image"></i> <?= htmlspecialchars($file['original_name']) ?></div>
                                                <a href="<?= htmlspecialchars($file['path']) ?>" target="_blank">
                                                    <img src="<?= htmlspecialchars($file['path']) ?>" alt="첨부 이미지" style="max-width: 100%; border: 1px solid rgba(255,255,255,0.08); border-radius: 8px;">
                                                </a>
                                            </div>
                                        <?php else: ?>
                                            <!-- 이미지 외 파일(PDF 등)은 다운로드 버튼 -->
                                            <div class="mb-2">
                                                <a href="<?= htmlspecialchars($file['path']) ?>" download="<?= htmlspecialchars($file['original_name']) ?>" class="btn btn-sm btn-outline-info rounded px-3 py-2" style="font-size: 0.85rem;">
                                                    <i class="fa-solid fa-file-pdf"></i> <?= htmlspecialchars($file['original_name']) ?> 다운로드
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- 2. 시공리포트 전문 (우측) -->
                <div class="col-lg-6">
                    <div class="glass-panel p-4 h-100 d-flex flex-column print-scroll-reset" style="min-height: 500px;">
                        <h5 class="fw-bold text-warning pb-2 border-bottom border-secondary d-flex align-items-center gap-2">
                            <span>💡</span> 시공리포트
                        </h5>
                        
                        <div class="flex-grow-1 print-scroll-reset" style="line-height: 1.4; font-size: 0.88rem; overflow-y: auto; max-height: 700px; word-break: keep-all; color: #e2e8f0;">
                            <?php if (($quote['source_mode'] ?? '') === 'board'): ?>
                                <div>
                                    <h6 class="text-white fw-bold mb-2">[게시판 문의 내용]</h6>
                                    
                                    <?php if (!empty($quote['condition_type']) || !empty($quote['self_install'])): ?>
                                        <div class="mb-3 p-2 rounded" style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1);">
                                            <div class="text-light fw-bold mb-1" style="font-size: 0.85rem; color: #a0aec0;"><i class="fa-solid fa-tags me-2"></i>  견적 희망 옵션</div>
                                            <div class="text-light" style="font-size: 0.9rem;">
                                                <?php if (!empty($quote['condition_type'])): ?>
                                                    <?php 
                                                        $cond_text = '중고 자재';
                                                        if ($quote['condition_type'] === 'new') $cond_text = '신규 자재';
                                                        if ($quote['condition_type'] === 'both') $cond_text = '모두(신규+중고 비교 견적)';
                                                    ?>
                                                    <div class="ps-3">자재 상태: <?= htmlspecialchars($cond_text) ?></div>
                                                <?php endif; ?>
                                                <?php if (($quote['self_install'] ?? 0) == 1): ?>
                                                    <div class="ps-3">직접 설치</div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <div class="ps-2 text-light fw-bold" style="font-size: 1.05rem; color: #fff;">
                                        <?= htmlspecialchars($quote['title'] ?? '제목 없음') ?>
                                    </div>
                                    <?php 
                                        $cleanSummaryBoard = trim(preg_replace('/\[(신청 모드|고객 요청사항|문의 제목)[^\]]*\]/u', '', $quote['summary'] ?? ''));
                                    ?>
                                    <div class="p-2 rounded" style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); white-space: pre-line;">
                                        <?= htmlspecialchars($cleanSummaryBoard) ?>
                                    </div>
                                </div>
                            <?php else: ?>
                                <?php
                                    // 창고 벽면 길이 포맷팅
                                    $edgeText = '';
                                    if (!empty($quote['edge_lengths'])) {
                                        $edges = explode(',', $quote['edge_lengths']);
                                        $edgeArr = [];
                                        foreach ($edges as $idx => $len) {
                                            $edgeArr[] = ($idx + 1) . "번 길이: " . trim($len) . "mm";
                                        }
                                        $edgeText = implode(', ', $edgeArr);
                                    }
                                ?>

                                <?php if ($edgeText): ?>
                                    <div class="mb-3">
                                        <h6 class="text-white fw-bold mb-1">[창고 벽면 길이]</h6>
                                        <div class="ps-2 text-light"><?= htmlspecialchars($edgeText) ?></div>
                                    </div>
                                <?php endif; ?>

                                <div class="mb-3">
                                    <h6 class="text-white fw-bold mb-1">[랙 및 적재물 제원]</h6>
                                    <div class="ps-2 text-light">
                                        <div>파렛트 규격: <?= htmlspecialchars($quote['pallet_w'] ?? 0) ?>(W) x <?= htmlspecialchars($quote['pallet_d'] ?? 0) ?>(D) x <?= htmlspecialchars($quote['pallet_h'] ?? 0) ?>(H) mm</div>
                                        <div>포크 진입 방향: <?= htmlspecialchars($quote['fork_direction'] ?? '') ?></div>
                                        <div>총 중량: <?= htmlspecialchars($quote['pallet_weight'] ?? 0) ?> kg / PLT</div>
                                        <div class="mt-1">지게차 종류: <?= htmlspecialchars($quote['forklift_type'] ?? '') ?></div>
                                        <div>최대 인상높이: <?= htmlspecialchars($quote['forklift_lift_height'] ?? 0) ?> mm</div>
                                        <div>직각교차 통로폭(AST): <?= htmlspecialchars($quote['forklift_ast'] ?? 0) ?> mm</div>
                                        <div class="mt-1">설치 단수: <?= htmlspecialchars($quote['rack_levels'] ?? 0) ?>단</div>
                                        <div>설치 높이: <?= htmlspecialchars($quote['rack_height'] ?? '') ?></div>
                                    </div>
                                </div>

                                <?php if (!empty($quote['rack_spec'])): ?>
                                    <div class="mt-3">
                                        <h6 class="text-white fw-bold mb-1"><?= htmlspecialchars($quote['rack_spec']) ?><?php if (!empty($quote['rack_type'])): ?> (<?= htmlspecialchars($quote['rack_type']) ?>)<?php endif; ?></h6>
                                        <div class="ps-2 text-light">독립 <?= $quote['rack_indep'] ?? 0 ?>대 | 연결 <?= $quote['rack_conn'] ?? 0 ?>대 <?php if (!empty($quote['rack_small_conn'])): ?>| 작은연결 <?= $quote['rack_small_conn'] ?>대<?php endif; ?> | 🔗 <?= $quote['rack_holders'] ?? 0 ?>홀더 | 📦 <?= $quote['rack_pallets'] ?? 0 ?> PLT</div>
                                        <?php if (!empty($quote['rack_bypass'])): ?>
                                            <div class="mt-2">
                                                <?php if (!empty($quote['rack_bypass_type'])): ?>
                                                    <h6 class="text-white fw-bold mb-1"><?= htmlspecialchars($quote['rack_spec'] . ' (' . $quote['rack_bypass_type'] . ')') ?></h6>
                                                <?php endif; ?>
                                                <div class="ps-2 text-light">연결 <?= htmlspecialchars($quote['rack_bypass']) ?>대 (바이패스)</div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>

                                <?php 
                                    $rawSummary = $quote['summary'] ?? '';
                                    $cleanSummary = trim(preg_replace('/\[(신청 모드|고객 요청사항|문의 제목)[^\]]*\]/u', '', $rawSummary));
                                ?>
                                <?php if (!empty($cleanSummary)): ?>
                                    <div class="mt-3 pt-3 border-top border-secondary">
                                        <h6 class="text-white fw-bold mb-0 pb-0">고객 요청 사항</h6>
                                        <div class="ps-2 text-light" style="white-space: pre-line;">
                                            <?= htmlspecialchars($cleanSummary) ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>


                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- 📐 CAD 시각화 렌더러 스크립트 -->
    <script>
    window.addEventListener('DOMContentLoaded', () => {
        const rawData = <?= json_encode($quote['canvas_data']) ?>;
        const hasImagePath = <?= !empty($quote['image_path']) ? 'true' : 'false' ?>;
        
        if (hasImagePath) return; // 이미지가 있으면 캔버스 그릴 필요 없음
        if (!rawData) return;
        
        let data;
        try {
            data = typeof rawData === 'string' ? JSON.parse(rawData) : rawData;
        } catch(e) {
            console.error("Failed to parse canvas JSON:", e);
            return;
        }

        const canvas = document.getElementById('quoteCanvas');
        const ctx = canvas.getContext('2d');

        const points = data.points || [];
        const racks = data.racks || [];
        const obstacles = data.obstacles || [];
        const scale = data.currentScale || 0.12;

        if (points.length === 0) {
            ctx.fillStyle = '#475569';
            ctx.font = '14px Arial';
            ctx.fillText("표시할 도면 좌표가 없습니다.", 50, 50);
            return;
        }

        // 1. 다각형 외곽 영역을 캔버스 중심에 맞추기 위한 바운딩 구하기
        let minX = Infinity, maxX = -Infinity, minY = Infinity, maxY = -Infinity;
        points.forEach(p => {
            if (p.x < minX) minX = p.x;
            if (p.x > maxX) maxX = p.x;
            if (p.y < minY) minY = p.y;
            if (p.y > maxY) maxY = p.y;
        });

        const centerX = (minX + maxX) / 2;
        const centerY = (minY + maxY) / 2;

        // 스케일 보정 (캔버스 크기에 맞춰 비율 산출)
        const boundsWidth = maxX - minX;
        const boundsHeight = maxY - minY;
        const margin = 60;
        const scaleX = (canvas.width - margin * 2) / boundsWidth;
        const scaleY = (canvas.height - margin * 2) / boundsHeight;
        const finalZoom = Math.min(scaleX, scaleY, 1);

        ctx.save();
        // 캔버스 중심 정렬
        ctx.translate(canvas.width / 2, canvas.height / 2);
        ctx.scale(finalZoom, finalZoom);
        ctx.translate(-centerX, -centerY);

        // 2. 창고 외곽 벽면 그리기
        ctx.strokeStyle = '#475569';
        ctx.lineWidth = 6 / finalZoom;
        ctx.fillStyle = 'rgba(30, 58, 138, 0.15)';
        ctx.beginPath();
        ctx.moveTo(points[0].x, points[0].y);
        for (let i = 1; i < points.length; i++) {
            ctx.lineTo(points[i].x, points[i].y);
        }
        ctx.closePath();
        ctx.fill();
        ctx.stroke();

        // 3. 장애물(기둥 등) 그리기
        obstacles.forEach(obs => {
            ctx.fillStyle = 'rgba(239, 68, 68, 0.3)';
            ctx.strokeStyle = '#ef4444';
            ctx.lineWidth = 2 / finalZoom;
            const w = (obs.width || 500) * scale;
            const h = (obs.height || 500) * scale;
            ctx.fillRect(obs.x - w / 2, obs.y - h / 2, w, h);
            ctx.strokeRect(obs.x - w / 2, obs.y - h / 2, w, h);
        });

        // 4. 파렛트랙 그룹 그리기
        racks.forEach(r => {
            ctx.save();
            ctx.translate(r.x, r.y);

            // 회전 각도 계산
            let angle = r.angle || 0;
            if (r.angle === undefined) {
                if (r.isHoriz) {
                    angle = r.dir < 0 ? Math.PI : 0;
                } else {
                    angle = r.dir < 0 ? -Math.PI / 2 : Math.PI / 2;
                }
            }
            ctx.rotate(angle);

            const singleDepth = r.rackDepth || 1000;
            const holderSize = r.holderSize || 200;
            const isDouble = r.isDouble || false;
            
            const singleDepthPx = singleDepth * scale;
            const holderPx = (isDouble ? holderSize : 0) * scale;
            const depthPx = isDouble ? (singleDepthPx * 2 + holderPx) : singleDepthPx;

            ctx.fillStyle = 'rgba(15, 23, 42, 0.85)';
            ctx.strokeStyle = '#0ea5e9';
            ctx.lineWidth = 3 / finalZoom;
            
            if (isDouble) {
                const topRackY = -depthPx / 2;
                const botRackY = depthPx / 2 - singleDepthPx;
                ctx.fillRect(0, topRackY, r.totalLengthPx, singleDepthPx);
                ctx.strokeRect(0, topRackY, r.totalLengthPx, singleDepthPx);
                ctx.fillRect(0, botRackY, r.totalLengthPx, singleDepthPx);
                ctx.strokeRect(0, botRackY, r.totalLengthPx, singleDepthPx);
            } else {
                ctx.fillRect(0, -depthPx / 2, r.totalLengthPx, depthPx);
                ctx.strokeRect(0, -depthPx / 2, r.totalLengthPx, depthPx);
            }

            ctx.restore();
        });

        ctx.restore();
    });
    </script>
    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
