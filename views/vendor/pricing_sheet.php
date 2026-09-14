<?php
$pageTitle = htmlspecialchars($supplier['name'] ?? '공급사') . " 단가표 워크시트";
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - B2B 파렛트랙 관리</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/css/vendor_dashboard.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            corePlugins: { preflight: false }
        }
    </script>
    <style>
        body { font-family: 'Outfit', sans-serif; background-color: #080c14; color: #fff; min-height: 100vh; }
        .mono { font-family: 'JetBrains Mono', monospace; }
        .glass-sheet { background: rgba(18, 24, 38, 0.7); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 20px; backdrop-filter: blur(16px); }
        
        /* 📑 탭 네비게이션 고품격 세그먼트 스타일 */
        .sheet-tab-nav {
            background: rgba(15, 23, 42, 0.85);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 14px;
            padding: 5px;
            display: inline-flex;
            gap: 6px;
        }
        .sheet-tab-btn {
            background: transparent !important;
            border: 1px solid transparent !important;
            border-radius: 10px !important;
            color: rgba(255, 255, 255, 0.6) !important;
            padding: 9px 18px !important;
            font-size: 13px !important;
            font-weight: 500 !important;
            cursor: pointer;
            outline: none !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 8px !important;
            transition: all 0.2s ease !important;
        }
        .sheet-tab-btn:hover {
            background: rgba(255, 255, 255, 0.06) !important;
            color: #ffffff !important;
        }
        .sheet-tab-btn.active {
            background: #1e293b !important;
            color: #fde047 !important;
            border: 1px solid rgba(253, 224, 71, 0.4) !important;
            font-weight: 700 !important;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.35) !important;
        }
        .sheet-tab-btn .tab-badge {
            font-family: 'JetBrains Mono', monospace;
            font-size: 10px;
            padding: 2px 8px;
            border-radius: 9999px;
            background: rgba(255, 255, 255, 0.1);
            color: rgba(255, 255, 255, 0.7);
        }
        .sheet-tab-btn.active .tab-badge {
            background: rgba(253, 224, 71, 0.2);
            color: #fde047;
            border: 1px solid rgba(253, 224, 71, 0.3);
        }

        /* 📊 고정 가로 사이즈 테이블 스타일 */
        .fixed-sheet-table {
            table-layout: fixed !important;
            border-collapse: collapse;
            margin: 0 auto;
        }
        .sheet-input {
            width: 100%;
            height: 38px;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            padding: 0 12px;
            color: #fff;
            text-align: right;
            font-family: 'JetBrains Mono', monospace;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.15s;
        }
        .sheet-input:focus {
            outline: none;
            border-color: #fde047;
            background: rgba(253, 224, 71, 0.06);
            box-shadow: 0 0 12px rgba(253, 224, 71, 0.25);
        }
        .sheet-table th {
            background: #0f172a;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.03em;
            color: rgba(255, 255, 255, 0.7);
            border-bottom: 1px solid rgba(255, 255, 255, 0.12);
            padding: 11px 12px;
            text-align: center;
        }
        .sheet-table td {
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            padding: 6px 10px;
            vertical-align: middle;
        }
        .sheet-table tr:hover td {
            background: rgba(255, 255, 255, 0.02);
        }
        .preview-cell {
            font-family: 'JetBrains Mono', monospace;
            font-size: 13px;
            text-align: right;
            color: #93c5fd;
            font-weight: 500;
        }
        .modal-overlay {
            display: none; position: fixed; inset: 0; z-index: 9999;
            background: rgba(4, 7, 13, 0.85); backdrop-filter: blur(12px);
            align-items: center; justify-content: center;
        }
        .modal-overlay.active { display: flex; }
    </style>
</head>
<body class="selection:bg-[#fde047]/30">

    <!-- 🧭 좌측 네비게이션 사이드바 -->
    <?php include __DIR__ . '/sidebar.php'; ?>

    <!-- 💻 우측 메인 대시보드 영역 -->
    <main class="main-content">
        <div class="top-navbar">
            <div class="navbar-title fw-bold text-light d-flex align-items-center gap-2" style="font-size: 1.05rem;">
                <a href="/vendor/pricing?sid=<?= $supplierId ?>" class="text-white/60 hover:text-white transition text-decoration-none">
                    <i class="fa-solid fa-arrow-left me-1"></i> 단가표 관리
                </a>
                <span class="text-white/30">&gt;</span>
                <span class="text-[#fde047]"><?= htmlspecialchars($supplier['name']) ?> 워크시트</span>
            </div>
            <div class="d-flex align-items-center gap-3">
                <a href="/vendor/pricing/template/download" class="btn btn-outline-light btn-sm rounded-pill px-3" style="font-size:0.8rem; border-color: rgba(255,255,255,0.2);">
                    <i class="fa-solid fa-file-arrow-down text-warning me-1"></i> 표준 엑셀 양식 다운로드
                </a>
                <button type="button" onclick="openExcelUploadModal()" class="btn btn-outline-warning btn-sm rounded-pill px-3" style="font-size:0.8rem;">
                    <i class="fa-solid fa-file-excel me-1"></i> 엑셀 일괄 업로드
                </button>
                <div class="user-profile d-flex align-items-center gap-2">
                    <i class="fa-solid fa-circle-user text-info fs-5"></i>
                    <span class="small font-monospace text-light"><?= htmlspecialchars($_SESSION['user']['username'] ?? 'User') ?>님</span>
                </div>
            </div>
        </div>

        <div class="p-4 md:p-6 max-w-[1400px] mx-auto">
            
            <?php if (!$isUnlocked): ?>
            <!-- 🔐 2차 비밀번호 잠금 화면 -->
            <div class="max-w-[460px] mx-auto my-16 glass-sheet p-8 text-center border border-white/10 shadow-2xl">
                <div class="w-16 h-16 rounded-2xl bg-amber-400/10 border border-amber-400/30 flex items-center justify-center mx-auto mb-5">
                    <i class="fa-solid fa-lock text-2xl text-amber-300"></i>
                </div>
                <h3 class="text-xl font-bold text-white mb-2">단가표 보안 인증 (2차 비밀번호)</h3>
                <p class="text-xs text-white/50 mb-6 leading-relaxed">
                    <span class="text-amber-300 font-semibold">[<?= htmlspecialchars($supplier['name']) ?>]</span>의 단가표는 민감한 영업 비밀로 안전하게 암호화되어 있습니다. 단가를 열람하거나 수정하려면 보안 비밀번호를 입력해주세요.
                </p>

                <form onsubmit="handleUnlock(event)" class="space-y-4">
                    <input type="hidden" id="unlock_supplier_id" value="<?= $supplierId ?>">
                    <div class="relative">
                        <input type="password" id="unlock_password" required placeholder="단가표 보안 비밀번호 입력" 
                               class="w-full h-12 bg-white/[0.06] border border-white/15 rounded-xl px-4 text-center text-base tracking-widest text-white focus:outline-none focus:border-[#fde047] focus:ring-1 focus:ring-[#fde047]">
                    </div>
                    <button type="submit" id="btnUnlockSubmit" class="w-full h-12 rounded-xl bg-[#fde047] text-black font-semibold text-sm hover:bg-[#fde047]/90 transition shadow-lg flex items-center justify-center gap-2">
                        <i class="fa-solid fa-key"></i> 잠금 해제 및 워크시트 열기
                    </button>
                </form>

                <div class="mt-6 pt-4 border-t border-white/5 text-[11px] text-white/40">
                    비밀번호를 아직 설정하지 않은 경우 입력하신 비밀번호가 1차 비밀번호로 자동 등록됩니다.
                </div>
            </div>

            <?php else: ?>
            <!-- 📑 잠금 해제된 엑셀 워크시트 에디터 -->
            
            <!-- 상단 바 -->
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-white text-black font-bold flex items-center justify-center text-sm shadow">
                        <?= mb_substr($supplier['name'], 0, 2) ?>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <h4 class="text-lg font-bold text-white mb-0"><?= htmlspecialchars($supplier['name']) ?> 단가표 워크시트</h4>
                            <span class="text-[11px] px-2.5 py-0.5 rounded-full bg-emerald-400/10 text-emerald-300 border border-emerald-400/30">
                                <i class="fa-solid fa-lock-open me-1"></i> 보안 인증됨
                            </span>
                        </div>
                        <span class="text-xs text-white/40">Option B 부품 분리형 단가 시스템 (기둥 60개 + 순수 로드빔 18개 + 타이빔 5개 = 총 83개)</span>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <button type="button" onclick="lockPricing()" class="btn btn-outline-secondary btn-sm rounded-pill px-3" style="font-size:0.8rem; color:#cbd5e1;">
                        <i class="fa-solid fa-lock me-1"></i> 다시 잠그기
                    </button>
                    <button type="button" onclick="saveSheetPrices()" id="btnSaveSheet" class="btn btn-warning btn-sm rounded-pill px-4 fw-bold shadow">
                        <i class="fa-solid fa-floppy-disk me-1"></i> 변경사항 즉시 저장
                    </button>
                </div>
            </div>

            <!-- 시트 탭 네비게이션 -->
            <div class="mb-4 overflow-x-auto pb-1">
                <div class="sheet-tab-nav">
                    <button type="button" onclick="switchTab('tab-columns')" id="btn-tab-columns" class="sheet-tab-btn active">
                        <i class="fa-solid fa-table-columns text-warning"></i> 기둥세트단가 <span class="tab-badge">60</span>
                    </button>
                    <button type="button" onclick="switchTab('tab-beams')" id="btn-tab-beams" class="sheet-tab-btn">
                        <i class="fa-solid fa-bars text-info"></i> 로드빔세트단가 <span class="tab-badge">18</span>
                    </button>
                    <button type="button" onclick="switchTab('tab-ties')" id="btn-tab-ties" class="sheet-tab-btn">
                        <i class="fa-solid fa-grip-lines text-emerald-400"></i> 타이빔단가 <span class="tab-badge">5</span>
                    </button>
                    <button type="button" onclick="switchTab('tab-preview')" id="btn-tab-preview" class="sheet-tab-btn">
                        <i class="fa-solid fa-calculator text-purple-400"></i> 자동 세트단가 시뮬레이션 <span class="tab-badge">미리보기</span>
                    </button>
                </div>
            </div>

            <form id="pricingSheetForm" onsubmit="saveSheetPrices(event)">
                <input type="hidden" name="supplier_id" id="sheet_supplier_id" value="<?= $supplierId ?>">

                <!-- 📑 [탭 1] 기둥세트단가 (12 높이 x 5 깊이) -->
                <div id="tab-columns" class="tab-content-panel">
                    <div class="glass-sheet p-4 md:p-6 mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h5 class="text-sm font-bold text-white mb-1 flex items-center gap-2">
                                    <i class="fa-solid fa-table-columns text-[#fde047]"></i> 기둥(포스트) 세트 단가표 (60개 규격)
                                </h5>
                                <p class="text-xs text-white/50 mb-0">
                                    기둥 2개 + 직선/경사 브레싱 일체 + 베이스플레이트 2개 + 앙카볼트 4개 포함 완제품 1세트(프레임) 금액
                                </p>
                            </div>
                            <span class="text-xs text-white/40 mono">단위: 원 (VAT 별도)</span>
                        </div>

                        <div class="overflow-x-auto rounded-xl border border-white/10 p-2 bg-black/20">
                            <table class="fixed-sheet-table sheet-table" style="width: 960px;">
                                <thead>
                                    <tr>
                                        <th style="width: 150px;" class="text-center">높이(H) \ 깊이(D)</th>
                                        <th style="width: 160px;">900D (원)</th>
                                        <th style="width: 170px;" class="text-amber-300 bg-amber-400/10">1000D [기본] (원)</th>
                                        <th style="width: 160px;">1100D (원)</th>
                                        <th style="width: 160px;">1200D (원)</th>
                                        <th style="width: 160px;">1300D (원)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $heights = [1500, 2000, 2500, 3000, 3500, 4000, 4500, 5000, 5500, 6000, 6500, 7000];
                                    $depths = [900, 1000, 1100, 1200, 1300];
                                    foreach ($heights as $h): 
                                    ?>
                                    <tr>
                                        <td class="text-center font-bold text-white/80 mono bg-white/[0.02]">
                                            <?= number_format($h) ?> H
                                        </td>
                                        <?php foreach ($depths as $d): 
                                            $code = "col_{$h}_{$d}";
                                            $val = $prices[$code] ?? 0;
                                        ?>
                                        <td>
                                            <input type="text" name="prices[<?= $code ?>]" value="<?= $val > 0 ? number_format($val) : '' ?>" 
                                                   placeholder="0" onkeyup="formatComma(this)" class="sheet-input">
                                        </td>
                                        <?php endforeach; ?>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- 📑 [탭 2] 로드빔세트단가 (18개 규격) -->
                <div id="tab-beams" class="tab-content-panel" style="display: none;">
                    <div class="glass-sheet p-4 md:p-6 mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h5 class="text-sm font-bold text-white mb-1 flex items-center gap-2">
                                    <i class="fa-solid fa-bars text-info"></i> 순수 로드빔 세트 단가표 (18개 규격)
                                </h5>
                                <p class="text-xs text-white/50 mb-0">
                                    로드빔 2개(좌/우 1단용) + 안전핀 4개 일체 단가 (타이빔 미포함 순수 빔 세트)
                                </p>
                            </div>
                            <span class="text-xs text-white/40 mono">단위: 원 (VAT 별도)</span>
                        </div>

                        <div class="overflow-x-auto rounded-xl border border-white/10 p-2 bg-black/20">
                            <table class="fixed-sheet-table sheet-table" style="width: 960px;">
                                <thead>
                                    <tr>
                                        <th style="width: 60px;">순번</th>
                                        <th style="width: 170px;">로드빔 길이(L)</th>
                                        <th style="width: 120px;">바 규격</th>
                                        <th style="width: 140px;">기본 소요 타이빔</th>
                                        <th style="width: 220px;">순수 로드빔 세트단가 (원)</th>
                                        <th style="width: 250px;">비고 (용도)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $beamList = [
                                        ['len' => 1385, 'bar' => 100, 'tie' => 2, 'note' => '1파렛트 적재 (소형)'],
                                        ['len' => 1385, 'bar' => 125, 'tie' => 2, 'note' => '1파렛트 적재 (소형)'],
                                        ['len' => 1385, 'bar' => 150, 'tie' => 2, 'note' => '1파렛트 적재 (소형)'],
                                        ['len' => 1485, 'bar' => 100, 'tie' => 2, 'note' => '1파렛트 적재 (특수)'],
                                        ['len' => 1485, 'bar' => 125, 'tie' => 2, 'note' => '1파렛트 적재 (특수)'],
                                        ['len' => 1485, 'bar' => 150, 'tie' => 2, 'note' => '1파렛트 적재 (특수)'],
                                        ['len' => 1585, 'bar' => 100, 'tie' => 2, 'note' => '1파렛트 적재 (중형)'],
                                        ['len' => 1585, 'bar' => 125, 'tie' => 2, 'note' => '1파렛트 적재 (중형)'],
                                        ['len' => 1585, 'bar' => 150, 'tie' => 2, 'note' => '1파렛트 적재 (중형)'],
                                        ['len' => 2585, 'bar' => 100, 'tie' => 4, 'note' => '2파렛트 적재 (표준형)'],
                                        ['len' => 2585, 'bar' => 125, 'tie' => 4, 'note' => '2파렛트 적재 (표준형)'],
                                        ['len' => 2585, 'bar' => 150, 'tie' => 4, 'note' => '2파렛트 적재 (표준형)'],
                                        ['len' => 2785, 'bar' => 100, 'tie' => 4, 'note' => '2파렛트 적재 (광폭형)'],
                                        ['len' => 2785, 'bar' => 125, 'tie' => 4, 'note' => '2파렛트 적재 (광폭형)'],
                                        ['len' => 2785, 'bar' => 150, 'tie' => 4, 'note' => '2파렛트 적재 (광폭형)'],
                                        ['len' => 2985, 'bar' => 100, 'tie' => 4, 'note' => '2파렛트 적재 (대형)'],
                                        ['len' => 2985, 'bar' => 125, 'tie' => 4, 'note' => '2파렛트 적재 (대형)'],
                                        ['len' => 2985, 'bar' => 150, 'tie' => 4, 'note' => '2파렛트 적재 (대형)'],
                                    ];
                                    foreach ($beamList as $idx => $b): 
                                        $code = "beam_{$b['bar']}_{$b['len']}";
                                        $val = $prices[$code] ?? 0;
                                    ?>
                                    <tr>
                                        <td class="text-center text-white/40 mono"><?= $idx + 1 ?></td>
                                        <td class="font-semibold text-white/90 mono"><?= number_format($b['len']) ?> mm</td>
                                        <td class="mono text-center">
                                            <span class="px-2 py-0.5 rounded bg-white/5 border border-white/10 text-xs"><?= $b['bar'] ?>바</span>
                                        </td>
                                        <td class="mono text-center text-white/60"><?= $b['tie'] ?> EA</td>
                                        <td>
                                            <input type="text" name="prices[<?= $code ?>]" value="<?= $val > 0 ? number_format($val) : '' ?>" 
                                                   id="input_beam_<?= $b['bar'] ?>_<?= $b['len'] ?>"
                                                   data-bar="<?= $b['bar'] ?>" data-len="<?= $b['len'] ?>" data-tie="<?= $b['tie'] ?>"
                                                   placeholder="0" onkeyup="formatComma(this); recalcPreview();" class="sheet-input beam-input">
                                        </td>
                                        <td class="text-xs text-white/40"><?= $b['note'] ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- 📑 [탭 3] 타이빔단가 (5개 규격) -->
                <div id="tab-ties" class="tab-content-panel" style="display: none;">
                    <div class="glass-sheet p-4 md:p-6 mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h5 class="text-sm font-bold text-white mb-1 flex items-center gap-2">
                                    <i class="fa-solid fa-grip-lines text-emerald-400"></i> 타이빔 개당 단가표 (5개 깊이 규격)
                                </h5>
                                <p class="text-xs text-white/50 mb-0">
                                    랙 깊이(D)에 따라 적용되는 타이빔 1개당 단가입니다.
                                </p>
                            </div>
                            <span class="text-xs text-white/40 mono">단위: 원 (VAT 별도)</span>
                        </div>

                        <div class="overflow-x-auto rounded-xl border border-white/10 p-2 bg-black/20">
                            <table class="fixed-sheet-table sheet-table" style="width: 960px;">
                                <thead>
                                    <tr>
                                        <th style="width: 60px;">순번</th>
                                        <th style="width: 180px;">랙 프레임 깊이(D)</th>
                                        <th style="width: 180px;">타이빔 규격</th>
                                        <th style="width: 110px;">단위</th>
                                        <th style="width: 220px;">개당 단가 (원)</th>
                                        <th style="width: 210px;">비고</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $tieList = [
                                        ['d' => 900, 'spec' => '900 mm용', 'note' => '소형 규격'],
                                        ['d' => 1000, 'spec' => '1000 mm용', 'note' => '가장 표준 규격 (T-11형 파렛트용)'],
                                        ['d' => 1100, 'spec' => '1100 mm용', 'note' => '수출/특수 파렛트용'],
                                        ['d' => 1200, 'spec' => '1200 mm용', 'note' => '수출/대형 파렛트용'],
                                        ['d' => 1300, 'spec' => '1300 mm용', 'note' => '수출/특수 파렛트용'],
                                    ];
                                    foreach ($tieList as $idx => $t): 
                                        $code = "tie_{$t['d']}";
                                        $val = $prices[$code] ?? 0;
                                    ?>
                                    <tr>
                                        <td class="text-center text-white/40 mono"><?= $idx + 1 ?></td>
                                        <td class="font-semibold text-white/90 mono"><?= $t['d'] ?>D</td>
                                        <td class="mono text-white/70"><?= $t['spec'] ?></td>
                                        <td class="text-center text-white/50 mono">1 EA</td>
                                        <td>
                                            <input type="text" name="prices[<?= $code ?>]" value="<?= $val > 0 ? number_format($val) : '' ?>" 
                                                   id="input_tie_<?= $t['d'] ?>" data-d="<?= $t['d'] ?>"
                                                   placeholder="0" onkeyup="formatComma(this); recalcPreview();" class="sheet-input tie-input">
                                        </td>
                                        <td class="text-xs text-white/40"><?= $t['note'] ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- 📑 [탭 4] 자동 세트단가 시뮬레이션 미리보기 -->
                <div id="tab-preview" class="tab-content-panel" style="display: none;">
                    <div class="glass-sheet p-4 md:p-6 mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h5 class="text-sm font-bold text-white mb-1 flex items-center gap-2">
                                    <i class="fa-solid fa-calculator text-purple-400"></i> [자동 계산] 로드빔 1단 완제품 세트단가 시뮬레이션
                                </h5>
                                <p class="text-xs text-white/50 mb-0">
                                    연산 공식: <span class="text-amber-300 font-mono font-semibold">순수 로드빔 세트 단가 + (타이빔 개당 단가 × 기본 소요수량)</span>
                                </p>
                            </div>
                            <span class="text-xs text-white/40 mono">단위: 원 (VAT 별도)</span>
                        </div>

                        <div class="overflow-x-auto rounded-xl border border-white/10 p-2 bg-black/20">
                            <table class="fixed-sheet-table sheet-table" style="width: 980px;">
                                <thead>
                                    <tr>
                                        <th style="width: 140px;">로드빔 길이(L)</th>
                                        <th style="width: 90px;">바 규격</th>
                                        <th style="width: 90px;">타이빔</th>
                                        <th style="width: 130px;">900D 세트가</th>
                                        <th style="width: 140px;" class="text-amber-300 bg-amber-400/10">1000D 세트가 [기본]</th>
                                        <th style="width: 130px;">1100D 세트가</th>
                                        <th style="width: 130px;">1200D 세트가</th>
                                        <th style="width: 130px;">1300D 세트가</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($beamList as $b): ?>
                                    <tr>
                                        <td class="font-semibold text-white/90 mono"><?= number_format($b['len']) ?> mm</td>
                                        <td class="mono text-center"><span class="px-2 py-0.5 rounded bg-white/5 text-xs"><?= $b['bar'] ?>바</span></td>
                                        <td class="mono text-center text-white/60"><?= $b['tie'] ?> EA</td>
                                        <?php foreach ([900, 1000, 1100, 1200, 1300] as $d): ?>
                                        <td id="prev_<?= $b['bar'] ?>_<?= $b['len'] ?>_<?= $d ?>" class="preview-cell <?= $d == 1000 ? 'bg-amber-400/5 text-amber-200 font-bold' : '' ?>">-</td>
                                        <?php endforeach; ?>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </form>

            <!-- 플로팅 저장 바 -->
            <div class="glass-sheet p-3 px-4 d-flex justify-content-between align-items-center mt-4">
                <div class="d-flex align-items-center gap-3">
                    <i class="fa-solid fa-circle-check text-emerald-400 fs-5"></i>
                    <span class="text-xs text-white/70">
                        수정한 단가는 상단의 <strong>[변경사항 즉시 저장]</strong> 또는 우측 버튼을 누르면 서버에 즉시 암호화 반영됩니다.
                    </span>
                </div>
                <button type="button" onclick="saveSheetPrices()" class="btn btn-warning btn-sm rounded-pill px-4 fw-bold shadow">
                    <i class="fa-solid fa-floppy-disk me-1"></i> 전체 단가 저장하기
                </button>
            </div>

            <?php endif; ?>

        </div>
    </main>

    <!-- 📤 엑셀 일괄 업로드 모달 -->
    <div id="excelUploadModal" class="modal-overlay">
        <div class="w-full max-w-[480px] rounded-[24px] bg-[#121826] border border-white/10 p-6 md:p-8 shadow-2xl">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="text-base font-bold text-white mb-0 flex items-center gap-2">
                    <i class="fa-solid fa-file-excel text-[#fde047]"></i> 엑셀 단가표 일괄 업로드
                </h4>
                <button type="button" onclick="closeExcelUploadModal()" class="text-white/40 hover:text-white transition">
                    <i class="fa-solid fa-xmark fs-5"></i>
                </button>
            </div>

            <p class="text-xs text-white/60 mb-5 leading-relaxed">
                다운로드받은 표준 Option B 양식(<code>pallet_rack_price_option_b.xlsx</code>)에 단가를 입력한 뒤 첨부해주세요. 3개 시트의 총 83개 부품 단가가 0.1초 만에 자동 분석됩니다.
            </p>

            <form action="/vendor/pricing/upload_excel" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="supplier_id" value="<?= $supplierId ?>">

                <div class="mb-4">
                    <label class="block text-xs font-semibold text-white/70 mb-2">1. 엑셀 파일 (.xlsx)</label>
                    <input type="file" name="price_excel" required accept=".xlsx,.xls" 
                           class="w-full text-xs text-white/70 file:mr-3 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-[#fde047] file:text-black hover:file:bg-[#fde047]/90 file:cursor-pointer cursor-pointer bg-white/[0.04] rounded-xl border border-white/10 p-2">
                </div>

                <div class="mb-6">
                    <label class="block text-xs font-semibold text-white/70 mb-2">2. 단가표 2차 보안 비밀번호</label>
                    <input type="password" name="pricing_password" required placeholder="비밀번호 입력 (최초 등록 또는 확인)" 
                           class="w-full h-11 bg-white/[0.06] border border-white/15 rounded-xl px-3 text-sm text-white focus:outline-none focus:border-[#fde047]">
                    <span class="text-[11px] text-white/40 mt-1 block">이 단가표를 열람/수정할 때 사용할 비밀번호입니다.</span>
                </div>

                <div class="d-flex gap-2">
                    <button type="button" onclick="closeExcelUploadModal()" class="flex-1 h-11 rounded-xl bg-white/10 text-white text-xs hover:bg-white/15 transition">취소</button>
                    <button type="submit" class="flex-1 h-11 rounded-xl bg-[#fde047] text-black font-bold text-xs hover:bg-[#fde047]/90 transition shadow">
                        <i class="fa-solid fa-cloud-arrow-up me-1"></i> 업로드 및 분석 시작
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- 로딩 오버레이 -->
    <div id="sheetLoadingOverlay" style="display: none; position: fixed; inset: 0; background: rgba(8,12,20,0.85); backdrop-filter: blur(8px); z-index: 99999; flex-direction: column; align-items: center; justify-content: center;">
        <i class="fa-solid fa-spinner fa-spin text-[#fde047] text-4xl mb-3"></i>
        <h5 class="text-white fw-bold" id="sheetLoadingText">처리 중입니다...</h5>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function openExcelUploadModal() {
            document.getElementById('excelUploadModal').classList.add('active');
        }
        function closeExcelUploadModal() {
            document.getElementById('excelUploadModal').classList.remove('active');
        }

        function switchTab(tabId) {
            document.querySelectorAll('.tab-content-panel').forEach(p => p.style.display = 'none');
            document.querySelectorAll('.sheet-tab-btn').forEach(b => b.classList.remove('active'));
            
            document.getElementById(tabId).style.display = 'block';
            document.getElementById('btn-' + tabId).classList.add('active');

            if(tabId === 'tab-preview') {
                recalcPreview();
            }
        }

        function formatComma(input) {
            let val = input.value.replace(/[^0-9]/g, '');
            if(val === '') { input.value = ''; return; }
            input.value = Number(val).toLocaleString('ko-KR');
        }

        function getNum(val) {
            if(!val) return 0;
            const n = parseFloat(String(val).replace(/[^0-9.]/g, ''));
            return isNaN(n) ? 0 : n;
        }

        function recalcPreview() {
            const depths = [900, 1000, 1100, 1200, 1300];
            const tiePrices = {};
            depths.forEach(d => {
                const el = document.getElementById('input_tie_' + d);
                tiePrices[d] = el ? getNum(el.value) : 0;
            });

            document.querySelectorAll('.beam-input').forEach(bInput => {
                const bar = bInput.dataset.bar;
                const len = bInput.dataset.len;
                const tieQty = parseInt(bInput.dataset.tie) || 0;
                const beamPrice = getNum(bInput.value);

                depths.forEach(d => {
                    const cellId = `prev_${bar}_${len}_${d}`;
                    const targetEl = document.getElementById(cellId);
                    if(targetEl) {
                        if(beamPrice > 0 && tiePrices[d] > 0) {
                            const setPrice = beamPrice + (tiePrices[d] * tieQty);
                            targetEl.innerText = Number(setPrice).toLocaleString('ko-KR') + '원';
                        } else if(beamPrice > 0) {
                            targetEl.innerText = Number(beamPrice).toLocaleString('ko-KR') + '원 (타이빔 미포함)';
                        } else {
                            targetEl.innerText = '-';
                        }
                    }
                });
            });
        }

        async function handleUnlock(e) {
            e.preventDefault();
            const sid = document.getElementById('unlock_supplier_id').value;
            const password = document.getElementById('unlock_password').value;
            const btn = document.getElementById('btnUnlockSubmit');

            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> 인증 확인 중...';

            try {
                const res = await fetch('/vendor/pricing/unlock', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({ supplier_id: sid, password: password })
                });
                const data = await res.json();
                if(data.success) {
                    window.location.reload();
                } else {
                    Swal.fire({
                        title: '인증 실패',
                        text: data.message || '비밀번호가 올바르지 않습니다.',
                        icon: 'error',
                        confirmButtonColor: '#fde047'
                    });
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fa-solid fa-key"></i> 잠금 해제 및 워크시트 열기';
                }
            } catch(err) {
                alert('서버 오류가 발생했습니다.');
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-key"></i> 잠금 해제 및 워크시트 열기';
            }
        }

        async function lockPricing() {
            // Unset cookie/session by reloading or calling simple post
            window.location.href = '/vendor/pricing?sid=<?= $supplierId ?>';
        }

        async function saveSheetPrices(e) {
            if(e) e.preventDefault();
            const sid = document.getElementById('sheet_supplier_id').value;
            const inputs = document.querySelectorAll('#pricingSheetForm .sheet-input');

            const prices = {};
            let count = 0;
            inputs.forEach(input => {
                const match = input.name.match(/prices\[(.*?)\]/);
                if(match && match[1]) {
                    const code = match[1];
                    const val = getNum(input.value);
                    if(val > 0) {
                        prices[code] = val;
                        count++;
                    }
                }
            });

            const overlay = document.getElementById('sheetLoadingOverlay');
            overlay.style.display = 'flex';
            document.getElementById('sheetLoadingText').innerText = `${count}개 단가 항목 암호화 저장 중...`;

            try {
                const res = await fetch('/vendor/pricing/sheet_save', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({ supplier_id: sid, prices: prices })
                });
                const data = await res.json();
                overlay.style.display = 'none';

                if(data.success) {
                    Swal.fire({
                        title: '저장 완료!',
                        text: data.message || '단가표가 성공적으로 저장되었습니다.',
                        icon: 'success',
                        confirmButtonColor: '#fde047'
                    });
                } else {
                    Swal.fire({
                        title: '저장 실패',
                        text: data.message,
                        icon: 'error'
                    });
                }
            } catch(err) {
                overlay.style.display = 'none';
                alert('저장 중 네트워크 오류가 발생했습니다.');
            }
        }

        // Initialize preview on page load
        window.addEventListener('DOMContentLoaded', () => {
            recalcPreview();
        });
    </script>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
