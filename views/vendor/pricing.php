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
    <script src="https://cdn.tailwindcss.com"></script>
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
    </style>
</head>
<body class="selection:bg-[#fde047]/30 relative overflow-x-hidden min-h-screen">
    <div class="pointer-events-none fixed inset-0 z-0">
        <div class="absolute -top-32 -left-32 w-[600px] h-[600px] bg-[#fde047]/10 blur-[120px] rounded-full"></div>
        <div class="absolute top-1/2 -right-48 w-[500px] h-[500px] bg-blue-500/10 blur-[120px] rounded-full"></div>
    </div>

    <!-- 좌측 네비게이션 포함 -->
    <div class="d-none">
        <?php include __DIR__ . '/sidebar.php'; ?>
    </div>

    <div class="relative z-10 max-w-[1280px] mx-auto px-4 md:px-8 py-6 md:py-8">
        <!-- 상단 헤더 -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-9 h-9 rounded-xl bg-[#fde047] flex items-center justify-center text-black">
                        <i class="fa-solid fa-layer-group"></i>
                    </div>
                    <span class="text-[11px] tracking-[0.2em] text-white/40 font-medium">CMAKE.WORK / VENDOR / PRICING</span>
                </div>
                <h1 class="text-[32px] md:text-[38px] font-[700] leading-none tracking-tight">다공급사 단가표 관리 <span class="text-[#fde047]">v2</span></h1>
                <p class="text-[13px] text-white/50 mt-3 max-w-[560px] leading-relaxed">
                    엑셀이 없으면 수동 단가로 자동 폴백되어 견적이 끊기지 않습니다. <br>
                    <span class="text-[#fde047]"><i class="fa-solid fa-check"></i> 두목님 질문 해결: "아무것도 올라가지 않았다면 직접 단가 입력"</span>
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="/vendor/settings" class="h-8 px-3 rounded-full bg-white text-black text-[12px] font-semibold flex items-center gap-1.5 hover:bg-white/90 transition">
                    <i class="fa-solid fa-gear"></i> 환경설정
                </a>
            </div>
        </div>

        <!-- 공급사 탭 -->
        <div class="flex items-center gap-2 overflow-x-auto pb-3 mb-4 scrollbar-none" id="supplierTabs">
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
            <button onclick="openAddSupplierModal()" class="shrink-0 h-[44px] px-4 rounded-full border border-dashed border-white/20 text-white/50 hover:text-white/80 hover:border-white/30 flex items-center gap-2 text-[13px] transition">
                <i class="fa-solid fa-plus"></i> 공급사 추가
            </button>
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
                <!-- 엑셀 업로드 영역 -->
                <div class="p-6 md:p-8 border-b lg:border-b-0 lg:border-r border-white/10">
                    <div class="flex items-center justify-between mb-5">
                        <h3 class="text-[13px] font-semibold tracking-wide flex items-center gap-2">
                            <i class="fa-solid fa-file-excel text-[#fde047]"></i> 엑셀 단가표 (최우선 순위)
                        </h3>
                    </div>

                    <form id="excelForm" action="/vendor/pricing" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="supplier_id" value="<?= $activeSupplierId ?>">
                        
                        <div class="rounded-[16px] border-2 border-dashed p-5 transition-all <?= ($activeSupplier['status']=='excel') ? 'border-[#fde047]/30 bg-[#fde047]/5' : 'border-white/15 bg-white/[0.02]' ?>">
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 rounded-xl bg-white flex items-center justify-center shrink-0">
                                    <i class="fa-solid fa-file-excel text-black fs-5"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <?php if($activeSupplier['status']=='excel' && !empty($activeSupplier['excel_file'])): ?>
                                        <div class="text-[13px] font-semibold truncate"><?= htmlspecialchars($activeSupplier['excel_file']) ?></div>
                                        <div class="text-[11px] text-white/40 mt-1 mono">정상 파싱됨</div>
                                    <?php else: ?>
                                        <div class="text-[13px] font-semibold text-white/60">업로드된 엑셀이 없습니다</div>
                                        <div class="text-[11px] text-white/35 mt-1">.xlsx / .xls · 최대 10MB</div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="mt-5 flex flex-wrap gap-2">
                                <label class="h-9 px-4 rounded-full bg-[#fde047] text-black text-[13px] font-semibold flex items-center gap-2 cursor-pointer hover:bg-[#fde047]/90">
                                    <i class="fa-solid fa-upload"></i> 엑셀 첨부하기
                                    <input type="file" name="price_excel" class="hidden" accept=".xlsx,.xls" onchange="submitExcelForm()">
                                </label>
                            </div>

                            <?php if($activeSupplier['status'] == 'excel'): ?>
                            <div class="mt-4 rounded-xl bg-black/40 border border-white/10 p-3 flex items-start gap-2.5">
                                <i class="fa-solid fa-check-circle text-emerald-400 mt-0.5"></i>
                                <div class="text-[11px] leading-relaxed text-white/60">
                                    <span class="text-white font-medium">파싱 완료:</span> 견적 생성 시 이 단가가 1순위로 적용됩니다.
                                </div>
                            </div>
                            <?php else: ?>
                            <div class="mt-4 rounded-xl bg-[#fde047]/10 border border-[#fde047]/20 p-3 flex items-start gap-2.5">
                                <i class="fa-solid fa-circle-exclamation text-[#fde047] mt-0.5"></i>
                                <div class="text-[11px] leading-relaxed text-[#fde047]/80">
                                    엑셀이 없으면 오른쪽 <span class="text-[#fde047] font-semibold">수동 단가</span>가 자동 사용됩니다.
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>

                <!-- 수동 단가 영역 -->
                <div class="p-6 md:p-8 bg-gradient-to-b from-white/[0.03] to-transparent">
                    <div class="flex items-center justify-between mb-5">
                        <h3 class="text-[13px] font-semibold tracking-wide flex items-center gap-2">
                            <i class="fa-solid fa-hammer text-[#fde047]"></i> 수동 단가 입력 (완제품 기준)
                            <span class="ml-2 text-[10px] px-2 py-0.5 rounded-full bg-[#fde047] text-black font-bold">FALLBACK</span>
                        </h3>
                    </div>

                    <form id="manualPriceForm" onsubmit="saveManualPrices(event)">
                        <input type="hidden" id="manual_supplier_id" value="<?= $activeSupplierId ?>">
                        <div class="rounded-[16px] bg-[#0b0f19] border border-white/10 p-4 md:p-5">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <!-- 기둥 -->
                                <div>
                                    <div class="text-[11px] font-semibold tracking-widest text-white/40 mb-3 flex items-center gap-2">
                                        <div class="w-1.5 h-1.5 rounded-full bg-[#fde047]"></div> 기둥 (완제품 단가)
                                    </div>
                                    <div class="space-y-3">
                                        <?php 
                                        $cols = [
                                            'column2000' => 'H-2000 (3,000kg)',
                                            'column2500' => 'H-2500 (3,500kg)',
                                            'column3000' => 'H-3000 (4,000kg)'
                                        ];
                                        foreach($cols as $code => $label): ?>
                                        <label class="block">
                                            <span class="text-[11px] text-white/50"><?= $label ?></span>
                                            <div class="mt-1.5 relative">
                                                <input type="text" name="prices[<?= $code ?>]" value="<?= number_format($prices[$code] ?? 0) ?>" class="price-input w-full h-10 rounded-xl bg-white/[0.06] border border-white/10 px-3 pr-12 text-[13px] focus:outline-none focus:border-[#fde047]/50 focus:bg-white/[0.08]" onkeyup="formatComma(this)">
                                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-[11px] text-white/30">원</span>
                                            </div>
                                        </label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                
                                <!-- 빔 -->
                                <div>
                                    <div class="text-[11px] font-semibold tracking-widest text-white/40 mb-3 flex items-center gap-2">
                                        <div class="w-1.5 h-1.5 rounded-full bg-blue-400"></div> 로드빔 (완제품 단가)
                                    </div>
                                    <div class="space-y-3">
                                        <?php 
                                        $beams = [
                                            'beam1t' => '1단당 1.0T (1,000kg)',
                                            'beam1_5t' => '1단당 1.5T (1,500kg)',
                                            'beam2t' => '1단당 2.0T (2,000kg)'
                                        ];
                                        foreach($beams as $code => $label): ?>
                                        <label class="block">
                                            <span class="text-[11px] text-white/50"><?= $label ?></span>
                                            <div class="mt-1.5 relative">
                                                <input type="text" name="prices[<?= $code ?>]" value="<?= number_format($prices[$code] ?? 0) ?>" class="price-input w-full h-10 rounded-xl bg-white/[0.06] border border-white/10 px-3 pr-12 text-[13px] focus:outline-none focus:border-[#fde047]/50 focus:bg-white/[0.08]" onkeyup="formatComma(this)">
                                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-[11px] text-white/30">원</span>
                                            </div>
                                        </label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- 부자재 -->
                            <div class="mt-6">
                                <div class="text-[11px] font-semibold tracking-widest text-white/40 mb-3 flex items-center gap-2">
                                    <div class="w-1.5 h-1.5 rounded-full bg-emerald-400"></div> 부자재 & 설치비율
                                </div>
                                <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                                    <?php 
                                    $accs = [
                                        'tiebar' => '타이바(원)',
                                        'brace' => '브레이스(원)',
                                        'boltSet' => '볼트세트(원)',
                                        'liner' => '라이너(원)',
                                        'install' => '설치비(%)'
                                    ];
                                    foreach($accs as $code => $label): ?>
                                    <label class="block">
                                        <span class="text-[11px] text-white/50"><?= $label ?></span>
                                        <input type="text" name="prices[<?= $code ?>]" value="<?= $code=='install' ? ($prices[$code] ?? 10) : number_format($prices[$code] ?? 0) ?>" class="<?= $code!='install' ? 'price-input' : '' ?> mt-1.5 w-full h-9 rounded-xl bg-white/[0.06] border border-white/10 px-3 text-[13px] focus:outline-none focus:border-[#fde047]/50" <?= $code!='install' ? 'onkeyup="formatComma(this)"' : '' ?>>
                                    </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                        <div class="mt-5 flex items-center gap-3">
                            <button type="submit" class="h-11 px-5 rounded-full bg-white text-black text-[13px] font-semibold flex items-center gap-2 hover:bg-white/90 transition">
                                <i class="fa-solid fa-save"></i> 수동 단가 저장
                            </button>
                            <div class="text-[11px] leading-snug text-white/40">
                                저장 시 해당 공급사의 단가는 수동 모드로 우선 전환됩니다.<br>
                                영업소(B2B) 등 완제품 단가 관리가 필요할 때 사용하세요!
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <?php endif; ?>
    </div>

    <!-- 공급사 추가 모달 -->
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

    <!-- 로딩 오버레이 -->
    <div id="loadingOverlay" style="display: none; position: fixed; inset: 0; background: rgba(11,15,25,0.85); backdrop-filter: blur(8px); z-index: 9999; flex-direction: column; align-items: center; justify-content: center;">
        <i class="fa-solid fa-spinner fa-spin text-[#fde047] text-4xl mb-4"></i>
        <h4 class="text-white mt-2 fw-bold text-lg">단가표 처리 중...</h4>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function openAddSupplierModal() { document.getElementById('addSupModal').classList.add('active'); document.getElementById('newSupName').focus(); }
        function closeAddSupplierModal() { document.getElementById('addSupModal').classList.remove('active'); }
        
        async function saveNewSupplier() {
            const name = document.getElementById('newSupName').value;
            if(!name) { alert('이름을 입력해주세요!'); return; }
            
            try {
                const res = await fetch('/vendor/addSupplier', {
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
                const res = await fetch('/vendor/saveManualPricing', {
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
    </script>
</body>
</html>
