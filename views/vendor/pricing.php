<?php
$pageTitle = "다공급사 단가표 관리 v2";
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Bootstrap 5 CSS (for sidebar layout compatibility) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- External Vendor Dashboard CSS -->
    <link href="/css/vendor_dashboard.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            corePlugins: {
                preflight: false // Disable Tailwind's CSS reset to avoid breaking Bootstrap sidebar
            }
        }
    </script>
    <style>
        body { font-family: 'Outfit', sans-serif; background-color: #0b0f19; color: #fff; }
        .mono { font-family: 'JetBrains Mono', monospace; }
        .scrollbar-none::-webkit-scrollbar { display: none; }
        .scrollbar-none { -ms-overflow-style: none; scrollbar-width: none; }
        
        .modal-overlay {
            display: none;
            position: fixed; inset: 0; z-index: 50;
            background: rgba(0,0,0,0.6); backdrop-filter: blur(12px);
            align-items: center; justify-content: center;
        }
        .modal-overlay.active { display: flex; }

        .glass-panel { background:rgba(30,41,59,0.45); border:1px solid rgba(255,255,255,0.08); border-radius:16px; backdrop-filter:blur(12px); }
        .flow-step { background:rgba(255,255,255,0.03); border:1px dashed rgba(253,224,71,0.3); border-radius:12px; padding:16px; text-align:center; }
        .arrow { color:#fde047; font-size:1.5rem; }
    </style>
</head>
<body class="selection:bg-[#fde047]/30">

    <!-- 🧭 좌측 네비게이션 사이드바 (Bootstrap layout) -->
    <?php include __DIR__ . '/sidebar.php'; ?>

    <!-- 💻 우측 메인 대시보드 영역 -->
    <main class="main-content">
        <div class="top-navbar">
            <div class="navbar-title fw-bold text-light" style="font-size: 1.1rem;">
                SaaS Dashboard &gt; 다공급사 단가표 관리
            </div>
            <div class="user-profile d-flex align-items-center gap-2">
                <i class="fa-solid fa-circle-user text-info fs-5"></i>
                <span class="small font-monospace text-light"><?= htmlspecialchars($user['username'] ?? 'User') ?>님</span>
            </div>
        </div>

        <!-- 꼬리표 배경 효과 -->
        <div class="pointer-events-none fixed inset-0 z-0">
            <div class="absolute -top-32 -left-32 w-[600px] h-[600px] bg-[#fde047]/10 blur-[120px] rounded-full"></div>
            <div class="absolute top-1/2 -right-48 w-[500px] h-[500px] bg-blue-500/10 blur-[120px] rounded-full"></div>
        </div>

        <div class="relative z-10 max-w-[1280px] mx-auto px-4 md:px-8 py-6 md:py-8">
        <!-- 상단 헤더 -->
        <!-- 상단 헤더 -->
        <!-- 상단 헤더 -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold text-light mb-0"><i class="fa-solid fa-file-excel text-success me-2"></i> 단가표 관리 <span class="text-[#fde047] fs-6 ms-2">v2 - 다공급사 지원</span></h3>
            <div class="d-flex align-items-center gap-3">
                <button type="button" class="btn btn-outline-warning btn-sm rounded px-3 fw-bold" style="font-size:0.85rem;" data-bs-toggle="modal" data-bs-target="#pricingUsageModal">
                    ℹ️ 이용안내
                </button>
                <span class="small text-white/50 d-none d-md-inline">SaaS Dashboard > 공급사 단가표 관리</span>
            </div>
        </div>

        <!-- FLOW -->
        <div class="glass-panel p-3 mb-5">
            <div class="row g-2 align-items-center text-center small m-0">
                <div class="col-md-2"><div class="flow-step" style="border-color:#10b981;"><i class="fa-solid fa-industry text-success mb-1 d-block fs-5"></i><b class="text-white">1. 공급사 선택</b><br><span class="text-white/50">세화/기타 추가공급사</span></div></div>
                <div class="col-md-1 d-none d-md-block"><span class="arrow">→</span></div>
                <div class="col-md-2"><div class="flow-step"><i class="fa-solid fa-cube text-info mb-1 d-block fs-5"></i><b class="text-white">2. 랙 도면 생성</b><br><span class="text-white/50">고객이 직접 그리기</span></div></div>
                <div class="col-md-1 d-none d-md-block"><span class="arrow">→</span></div>
                <div class="col-md-3"><div class="flow-step" style="border-color:#3b82f6; background:rgba(59,130,246,0.08);"><i class="fa-solid fa-coins text-[#fde047] mb-1 d-block fs-5"></i><b class="text-white">3. 단가 자동 매칭</b><br><span class="text-white/70">COALESCE(엑셀, 수동, 직접입력)</span></div></div>
                <div class="col-md-1 d-none d-md-block"><span class="arrow">→</span></div>
                <div class="col-md-2"><div class="flow-step"><i class="fa-solid fa-file-invoice-dollar text-[#fde047] mb-1 d-block fs-5"></i><b class="text-white">4. 견적서 완성</b><br><span class="text-white/50">실시간 자동 계산</span></div></div>
            </div>
        </div>

        <!-- 공급사 탭 (다중 공급사 기능 임시 숨김) -->
        <div class="flex items-center gap-2 overflow-x-auto pb-3 mb-4 scrollbar-none" id="supplierTabs" style="display: none;">
            <?php 
            $statusStyles = [
                'excel' => ['text' => '엑셀 적용중', 'cls' => 'bg-[#fde047] text-black'],
                'manual' => ['text' => '수동 단가', 'cls' => 'bg-[#1e293b] text-[#fde047] border border-[#fde047]/30'],
                'none' => ['text' => '미설정', 'cls' => 'bg-white/10 text-white/50 border border-white/10']
            ];
            
            $activeSupplierId = $suppliers[0]['id'] ?? 0;
            if (isset($_GET['sid'])) $activeSupplierId = intval($_GET['sid']);

            foreach ($suppliers as $sup): 
                $isActive = ($sup['id'] == $activeSupplierId);
                $st = $statusStyles[$sup['status'] ?? 'none'];
            ?>
            <a href="?sid=<?= $sup['id'] ?>" class="shrink-0 h-[44px] px-4 rounded-full border flex items-center gap-3 transition-all <?= $isActive ? 'bg-white text-black border-white' : 'bg-white/[0.05] border-white/10 text-white/70 hover:bg-white/[0.08] hover:text-white' ?>">
                <div class="w-2 h-2 rounded-full" style="background: <?= $sup['color'] ?>"></div>
                <span class="text-[13px] font-semibold"><?= htmlspecialchars($sup['name']) ?></span>
                <span class="text-[10px] px-2 py-0.5 rounded-full font-medium <?= $st['cls'] ?>"><?= $st['text'] ?></span>
            </a>
            <?php endforeach; ?>
            <!-- [TEMP] 주석 처리: 당분간 사용 안 함
            <button onclick="openAddSupplierModal()" class="bg-transparent shrink-0 h-[44px] px-4 rounded-full border border-dashed border-white/20 text-white/50 hover:text-white/80 hover:border-white/30 flex items-center gap-2 text-[13px] transition">
                <i class="fa-solid fa-plus"></i> 공급사 추가
            </button>
            -->
        </div>

        <?php 
        $activeSupplier = null;
        foreach($suppliers as $s) if($s['id'] == $activeSupplierId) $activeSupplier = $s;
        if($activeSupplier):
            $prices = $manualPrices[$activeSupplierId] ?? [];
            $activeSt = $statusStyles[$activeSupplier['status'] ?? 'none'];
        ?>
        
        <!-- 공급사 대시보드 -->
        <div class="rounded-[24px] border border-white/10 bg-white/[0.04] backdrop-blur-xl overflow-hidden">
            <!-- 탭 헤더 -->
            <div class="px-6 md:px-8 py-5 border-b border-white/10 flex flex-col md:flex-row md:items-center justify-between gap-3 bg-gradient-to-r from-white/[0.04] to-transparent">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-white text-black flex items-center justify-center font-bold text-[14px]">
                        <?= mb_substr($activeSupplier['name'], 0, 2) ?>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-[18px] font-semibold"><?= htmlspecialchars($activeSupplier['name']) ?></span>
                            <span class="text-[11px] text-white/40"><?= htmlspecialchars($activeSupplier['factory_name']) ?></span>
                        </div>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="text-[10px] px-2.5 py-1 rounded-full font-semibold <?= $activeSt['cls'] ?>"><?= $activeSt['text'] ?></span>
                            <?php if($activeSupplier['status'] == 'excel' && !empty($activeSupplier['excel_file'])): ?>
                            <span class="text-[11px] text-white/40 mono"><?= htmlspecialchars($activeSupplier['excel_file']) ?> · AI 파싱 완료</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-[1.1fr_1.2fr] gap-0">
                <!-- 엑셀 업로드 및 양식 다운로드 영역 -->
                <div class="p-6 md:p-8 border-b lg:border-b-0 lg:border-r border-white/10">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-[14px] font-bold tracking-wide flex items-center gap-2 text-white">
                            <i class="fa-solid fa-file-excel text-[#fde047]"></i> 1. 엑셀 대량 일괄 등록
                        </h3>
                        <span class="text-[11px] px-2 py-0.5 rounded-full bg-white/10 text-white/60 mono">Option B 표준</span>
                    </div>

                    <p class="text-[12px] text-white/50 mb-5 leading-relaxed">
                        우리가 제공하는 표준 엑셀 양식을 다운로드하여 단가를 입력한 뒤 업로드하세요. 기둥, 로드빔, 타이빔 3개 시트의 총 83개 단가가 즉시 자동 파싱됩니다.
                    </p>

                    <!-- 다운로드 & 업로드 카드 -->
                    <div class="rounded-[20px] border border-white/10 bg-white/[0.02] p-5 mb-6">
                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 pb-4 border-b border-white/5">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-amber-400/10 border border-amber-400/20 flex items-center justify-center text-amber-300">
                                    <i class="fa-solid fa-download"></i>
                                </div>
                                <div>
                                    <div class="text-[13px] font-semibold text-white">표준 단가표 양식 다운로드</div>
                                    <div class="text-[11px] text-white/40">pallet_rack_price_option_b.xlsx</div>
                                </div>
                            </div>
                            <a href="/vendor/pricing/template/download" class="h-9 px-4 rounded-xl bg-white/10 text-white text-[12px] font-semibold hover:bg-white/20 transition flex items-center justify-center gap-1.5 text-decoration-none">
                                <i class="fa-solid fa-file-arrow-down text-warning"></i> 양식 받기
                            </a>
                        </div>

                        <div class="pt-4 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-emerald-400/10 border border-emerald-400/20 flex items-center justify-center text-emerald-300">
                                    <i class="fa-solid fa-cloud-arrow-up"></i>
                                </div>
                                <div>
                                    <div class="text-[13px] font-semibold text-white">작성한 엑셀 업로드</div>
                                    <div class="text-[11px] text-white/40">
                                        <?= !empty($activeSupplier['excel_file']) ? htmlspecialchars($activeSupplier['excel_file']) . ' (등록됨)' : '아직 등록된 엑셀이 없습니다' ?>
                                    </div>
                                </div>
                            </div>
                            <button type="button" onclick="openExcelUploadModal()" class="h-9 px-4 rounded-xl bg-[#fde047] text-black text-[12px] font-bold hover:bg-[#fde047]/90 transition shadow flex items-center justify-center gap-1.5">
                                <i class="fa-solid fa-upload"></i> 엑셀 업로드
                            </button>
                        </div>
                    </div>

                    <!-- 단가표 업로드 내역 (History) -->
                    <div>
                        <h4 class="text-[12px] font-semibold tracking-wide flex items-center gap-2 mb-3 text-white/70">
                            <i class="fa-solid fa-clock-rotate-left text-white/40"></i> 최근 업로드 이력
                        </h4>
                        <div class="bg-black/30 border border-white/5 rounded-xl overflow-hidden">
                            <table class="w-full text-[12px] text-left">
                                <thead>
                                    <tr class="border-b border-white/5 bg-white/[0.02]">
                                        <th class="px-4 py-2.5 font-semibold text-white/50">공급사</th>
                                        <th class="px-4 py-2.5 font-semibold text-white/50">파일명</th>
                                        <th class="px-4 py-2.5 font-semibold text-white/50">등록일시</th>
                                        <th class="px-4 py-2.5 font-semibold text-white/50 text-center">관리</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-white/5">
                                    <?php if(!empty($rules)): foreach($rules as $rule): ?>
                                    <tr class="hover:bg-white/[0.02] transition">
                                        <td class="px-4 py-2 text-white/80 font-medium"><?= htmlspecialchars($rule['supplier_name'] ?? '기본') ?></td>
                                        <td class="px-4 py-2 text-white/70 truncate max-w-[180px]" title="<?= htmlspecialchars($rule['source_file'] ?? '단가표') ?>">
                                            <i class="fa-solid fa-file-excel text-emerald-400/60 mr-1"></i>
                                            <?= htmlspecialchars($rule['source_file'] ?? '단가표') ?>
                                        </td>
                                        <td class="px-4 py-2 text-white/40 font-mono text-[11px]">
                                            <?= date('Y.m.d H:i', strtotime($rule['created_at'])) ?>
                                        </td>
                                        <td class="px-4 py-2 text-center">
                                            <button type="button" onclick="deleteHistory(<?= $rule['id'] ?>)" 
                                                    class="w-7 h-7 rounded-lg text-red-400 hover:text-red-300 hover:bg-red-500/20 transition flex items-center justify-center mx-auto"
                                                    style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.25); cursor: pointer;" 
                                                    title="단가표 삭제">
                                                <i class="fa-solid fa-trash-can text-[11px]"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; else: ?>
                                    <tr>
                                        <td colspan="4" class="px-4 py-6 text-center text-white/30 text-[12px]">업로드 이력이 없습니다.</td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- 온라인 단가 워크시트 (2차 보안 금고 & 개별 수정) -->
                <div class="p-6 md:p-8 bg-gradient-to-b from-white/[0.03] to-transparent flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-[14px] font-bold tracking-wide flex items-center gap-2 text-white">
                                <i class="fa-solid fa-shield-halved text-amber-400"></i> 2. 온라인 단가 워크시트 & 개별 수정
                            </h3>
                            <span class="text-[10px] px-2 py-0.5 rounded-full bg-amber-400/10 text-amber-300 border border-amber-400/20 font-bold">2차 비밀번호 보호</span>
                        </div>

                        <p class="text-[12px] text-white/50 mb-6 leading-relaxed">
                            엑셀을 다시 열 필요 없이 웹에서 엑셀 시트처럼 탭을 넘겨가며 단가를 바로 확인하고, 필요한 규격만 <strong>숫자 하나씩 즉시 수정</strong>할 수 있습니다.
                        </p>

                        <!-- 워크시트 주요 구성 안내 카드 -->
                        <div class="space-y-3 mb-6">
                            <div class="p-3.5 rounded-2xl bg-white/[0.03] border border-white/5 flex items-center gap-3.5">
                                <div class="w-9 h-9 rounded-xl bg-amber-400/10 text-amber-300 flex items-center justify-center shrink-0">
                                    <i class="fa-solid fa-table-columns text-sm"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="text-[13px] font-semibold text-white/90">기둥세트단가 워크시트</div>
                                    <div class="text-[11px] text-white/40">1,500H~7,000H (12종) × 900~1,300D (5종) = 총 60개 규격</div>
                                </div>
                            </div>

                            <div class="p-3.5 rounded-2xl bg-white/[0.03] border border-white/5 flex items-center gap-3.5">
                                <div class="w-9 h-9 rounded-xl bg-blue-400/10 text-blue-300 flex items-center justify-center shrink-0">
                                    <i class="fa-solid fa-bars text-sm"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="text-[13px] font-semibold text-white/90">로드빔세트단가 워크시트</div>
                                    <div class="text-[11px] text-white/40">1,385L~2,985L (6종) × 100/125/150바 = 총 18개 순수 빔 세트</div>
                                </div>
                            </div>

                            <div class="p-3.5 rounded-2xl bg-white/[0.03] border border-white/5 flex items-center gap-3.5">
                                <div class="w-9 h-9 rounded-xl bg-emerald-400/10 text-emerald-300 flex items-center justify-center shrink-0">
                                    <i class="fa-solid fa-grip-lines text-sm"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="text-[13px] font-semibold text-white/90">타이빔 개당 단가 & 세트 자동합산</div>
                                    <div class="text-[11px] text-white/40">깊이별 5개 단가 + 순수빔 합산 완제품 단가 실시간 시뮬레이션</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 단가 워크시트 입장 버튼 -->
                    <div class="pt-4 border-t border-white/10">
                        <a href="/vendor/pricing/sheet?sid=<?= $activeSupplierId ?>" 
                           class="w-full h-12 rounded-2xl bg-gradient-to-r from-amber-400 to-yellow-300 text-black font-bold text-[13px] flex items-center justify-center gap-2 hover:opacity-95 transition shadow-lg text-decoration-none">
                            <i class="fa-solid fa-arrow-up-right-from-square"></i> 단가표 워크시트 열기 (열람 및 개별 수정)
                        </a>
                        <div class="text-center text-[11px] text-white/35 mt-2.5">
                            ※ 영업 비밀 보호를 위해 2차 보안 비밀번호 입력 후 입장됩니다.
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- 기존 와이드 히스토리 삭제됨 (왼쪽 단으로 이동) -->

        <?php endif; ?>
    </div>
    </main>

    <!-- 공급사 추가 모달 -->
    <!-- [TEMP] 주석 처리: 당분간 사용 안 함
    <div id="addSupModal" class="modal-overlay">
        <div class="w-full max-w-[420px] rounded-[20px] bg-[#151a27] border border-white/10 p-6 shadow-2xl">
            <h4 class="text-[16px] font-semibold mb-1">새 공급사 추가</h4>
            <p class="text-[12px] text-white/40 mb-5">추가 후 엑셀 또는 수동 단가를 설정하면 견적에 바로 반영됩니다.</p>
            <input type="text" id="newSupName" placeholder="예: 부산스틸랙" class="w-full h-11 rounded-xl bg-white/[0.06] border border-white/10 px-4 text-[13px] focus:outline-none focus:border-[#fde047]/50 text-white placeholder:text-white/20">
            <div class="mt-5 flex gap-2">
                <button onclick="closeAddSupplierModal()" class="flex-1 h-11 rounded-full bg-white/10 border border-white/10 text-[13px] hover:bg-white/20">취소</button>
                <button onclick="saveNewSupplier()" class="flex-1 h-11 rounded-full bg-[#fde047] text-black text-[13px] font-semibold hover:bg-[#fde047]/90">추가하기</button>
            </div>
        </div>
    </div>
    -->

    <!-- 📤 엑셀 일괄 업로드 모달 -->
    <div id="excelUploadModal" class="modal-overlay">
        <div class="w-full max-w-[460px] rounded-[24px] bg-[#121826] border border-white/10 p-6 md:p-8 shadow-2xl">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="text-base font-bold text-white mb-0 flex items-center gap-2">
                    <i class="fa-solid fa-file-excel text-[#fde047]"></i> 엑셀 단가표 일괄 업로드
                </h4>
                <button type="button" onclick="closeExcelUploadModal()" class="text-white/40 hover:text-white transition">
                    <i class="fa-solid fa-xmark fs-5"></i>
                </button>
            </div>

            <p class="text-xs text-white/60 mb-5 leading-relaxed">
                다운로드받은 표준 Option B 양식(<code>pallet_rack_price_option_b.xlsx</code>)에 단가를 입력한 뒤 첨부해주세요. 기둥 60개, 로드빔 18개, 타이빔 5개 단가가 즉시 자동 분석되어 등록됩니다.
            </p>

            <form action="/vendor/pricing/upload_excel" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="supplier_id" value="<?= $activeSupplierId ?>">

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
    <div id="loadingOverlay" style="display: none; position: fixed; inset: 0; background: rgba(11,15,25,0.85); backdrop-filter: blur(8px); z-index: 9999; flex-direction: column; align-items: center; justify-content: center;">
        <i class="fa-solid fa-spinner fa-spin text-[#fde047] text-4xl mb-4"></i>
        <h4 class="text-white mt-2 fw-bold text-lg">단가표 처리 중...</h4>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function openAddSupplierModal() { document.getElementById('addSupModal').classList.add('active'); document.getElementById('newSupName').focus(); }
        function closeAddSupplierModal() { document.getElementById('addSupModal').classList.remove('active'); }
        function openExcelUploadModal() { document.getElementById('excelUploadModal').classList.add('active'); }
        function closeExcelUploadModal() { document.getElementById('excelUploadModal').classList.remove('active'); }
        
        async function saveNewSupplier() {
            const name = document.getElementById('newSupName').value;
            if(!name) { alert('이름을 입력해주세요!'); return; }
            
            try {
                const res = await fetch('/vendor/pricing/supplier/add', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({name})
                });
                const data = await res.json();
                if(data.success) {
                    window.location.href = '?sid=' + data.id;
                } else {
                    alert(data.message);
                }
            } catch(e) {
                alert('오류가 발생했습니다.');
            }
        }

        function formatComma(input) {
            let val = input.value.replace(/[^0-9]/g, '');
            if(val === '') { input.value = ''; return; }
            input.value = Number(val).toLocaleString('ko-KR');
        }

        function submitExcelForm() {
            document.getElementById('loadingOverlay').style.display = 'flex';
            document.getElementById('excelForm').submit();
        }

        async function saveManualPrices(e) {
            e.preventDefault();
            const sid = document.getElementById('manual_supplier_id').value;
            const inputs = document.querySelectorAll('#manualPriceForm .price-input, #manualPriceForm input[name="prices[install]"]');
            
            const prices = {};
            inputs.forEach(input => {
                const code = input.name.match(/prices\[(.*?)\]/)[1];
                prices[code] = input.value;
            });

            document.getElementById('loadingOverlay').style.display = 'flex';
            try {
                const res = await fetch('/vendor/pricing/manual_save', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({supplier_id: sid, prices: prices})
                });
                const data = await res.json();
                document.getElementById('loadingOverlay').style.display = 'none';
                
                if(data.success) {
                    Swal.fire({title: '완료!', text: data.message, icon: 'success'}).then(() => window.location.reload());
                } else {
                    Swal.fire({title: '오류', text: data.message, icon: 'error'});
                }
            } catch(e) {
                document.getElementById('loadingOverlay').style.display = 'none';
                alert('저장 중 오류가 발생했습니다.');
            }
        }
        async function deleteHistory(id) {
            if(!confirm('이 단가표 이력을 삭제하시면 현재 적용 중인 모든 단가 데이터와 비밀번호 잠금까지 완전히 초기화(삭제)됩니다. 그래도 삭제하시겠습니까?')) return;
            
            document.getElementById('loadingOverlay').style.display = 'flex';
            try {
                const res = await fetch('/vendor/pricing/delete', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({ action: 'select', ids: [id] })
                });
                const data = await res.json();
                document.getElementById('loadingOverlay').style.display = 'none';
                if(data.success) {
                    window.location.reload();
                } else {
                    alert(data.message || '삭제 실패');
                }
            } catch(e) {
                document.getElementById('loadingOverlay').style.display = 'none';
                alert('오류가 발생했습니다.');
            }
        }
    </script>
    <!-- 단가표 이용안내 모달 -->
    <div class="modal fade" id="pricingUsageModal" tabindex="-1" aria-labelledby="pricingUsageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="background-color: #1e293b; border: 1px solid rgba(255,255,255,0.1); color: #f8fafc; border-radius: 12px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold" id="pricingUsageModalLabel" style="color: #fde047;">
                        <i class="fa-solid fa-file-excel me-2"></i> 단가표 관리 이용 안내
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-secondary mb-4" style="font-size: 0.9rem;">
                        한 번만 세팅해 두시면 평생 편안해지는 단가표 관리! 아래 순서대로 진행해 보세요.
                    </p>
                    <div class="d-flex flex-column gap-3">
                        <div class="d-flex gap-3 align-items-start p-3 rounded" style="background-color: rgba(255, 255, 255, 0.03);">
                            <div class="fs-4 text-success">📥</div>
                            <div>
                                <h6 class="fw-bold mb-1 text-light">1. 최초 양식 다운로드 및 업로드</h6>
                                <p class="mb-0 text-secondary" style="font-size: 0.85rem;">[단가표 양식 다운로드] 버튼을 눌러 엑셀 파일을 받으신 후, 귀사의 단가를 기입하여 업로드해 주세요.</p>
                            </div>
                        </div>
                        <div class="d-flex gap-3 align-items-start p-3 rounded" style="background-color: rgba(255, 255, 255, 0.03);">
                            <div class="fs-4 text-info">💻</div>
                            <div>
                                <h6 class="fw-bold mb-1 text-light">2. PC에서 간편한 단가 수정</h6>
                                <p class="mb-0 text-secondary" style="font-size: 0.85rem;">한 번 업로드가 완료되면, 이후부터는 매번 엑셀을 올릴 필요 없이 이 대시보드 화면(PC)에서 언제든 편하게 단가를 직접 수정하실 수 있습니다.</p>
                            </div>
                        </div>
                        <div class="d-flex gap-3 align-items-start p-3 rounded" style="background-color: rgba(255, 255, 255, 0.03);">
                            <div class="fs-4 text-warning">🤖</div>
                            <div>
                                <h6 class="fw-bold mb-1 text-light">3. 전용 단가표 AI 맞춤 연동 지원</h6>
                                <p class="mb-0 text-secondary" style="font-size: 0.85rem;">혹시 귀사에서 이미 사용 중이신 전용 단가표(엑셀 등)가 따로 있으신가요? 저희에게 보내주시면 최신 AI 기술을 활용하여 귀사의 단가 구조를 정밀 분석 후 시스템에 찰떡같이 연동해 드립니다. 고민하지 말고 편하게 문의해 주세요!</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-warning btn-sm px-4 fw-bold w-100 rounded-pill" data-bs-dismiss="modal">확인했습니다</button>
                </div>
            </div>
        </div>
    </div>

    <!-- 🛡️ B2B Stealth Security Watermark Overlay -->
    <?php include __DIR__ . '/watermark.php'; ?>
    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
