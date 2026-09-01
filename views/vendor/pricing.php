<?php
// Layout Variables
$pageTitle = "단가표(엑셀) 관리";
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>공급사 관리 센터 - 단가표 관리</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- External Vendor Dashboard CSS -->
    <link href="/css/vendor_dashboard.css" rel="stylesheet">
    <style>
        .table-dark-custom {
            background-color: rgba(30, 41, 59, 0.45);
            color: #f8fafc;
        }
        .table-dark-custom th {
            background-color: rgba(15, 23, 42, 0.6);
            color: #fbbf24;
            border-bottom: 2px solid rgba(255, 255, 255, 0.1);
        }
        .table-dark-custom td {
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            vertical-align: middle;
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
                SaaS Dashboard &gt; 단가표(엑셀) 관리
            </div>
<div class="user-profile d-flex align-items-center gap-2">
                <?php
                    $dbBtn = \App\Core\Database::getInstance();
                    $stmtBtn = $dbBtn->prepare("SELECT plan FROM users WHERE user_id = ?");
                    $stmtBtn->execute([$_SESSION['user']['user_id']]);
                    $btnPlan = $stmtBtn->fetchColumn();
                    if ($btnPlan !== 'pro'):
                ?>
                <a href="/vendor/addon_payment" class="btn btn-outline-warning btn-sm fw-bold px-3 py-1 me-3" style="border-radius: 10px;">
                    <i class="fa-solid fa-bolt"></i> 횟수 충전
                </a>
                <?php endif; ?>
                <i class="fa-solid fa-circle-user text-info fs-5"></i>
                <span class="small font-monospace text-light"><?= htmlspecialchars($_SESSION['user']['username'] ?? 'User') ?>님</span>
            </div>
        </div>

        <div class="content-body">
            <h3 class="mb-4 fw-bold text-light border-bottom border-secondary pb-2" style="font-size: 1.5rem;">
                <i class="fa-solid fa-file-excel text-success me-2"></i> 단가표 업로드 및 관리
            </h3>

            <div class="row g-4">
                <div class="col-lg-12">
                    <div class="glass-panel p-4">
                        <form id="pricingUploadForm" action="/vendor/pricing" method="POST" enctype="multipart/form-data">
                            <h5 class="fw-bold text-warning mb-3">새 단가표 업로드</h5>
                            <div class="mb-3">
                                <?php if(!empty($settings['price_excel_path'])): ?>
                                    <div class="mb-3 p-3 bg-dark bg-opacity-25 rounded border border-secondary d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center gap-2">
                                            <span style="font-size:1.5rem;">📊</span>
                                            <div>
                                                <span class="badge bg-success small">현재 적용중인 파일</span>
                                                <div class="text-light small mt-1 font-monospace"><?= htmlspecialchars(basename($settings['price_excel_path'])) ?></div>
                                            </div>
                                        </div>
                                        <a href="/vendor/pricing/download" class="btn btn-outline-secondary btn-sm rounded">보안 다운로드</a>
                                    </div>
                                <?php endif; ?>
                                
                                <label class="form-label text-light small fw-bold mb-1">단가표 엑셀 파일 (.xlsx, .xls)</label>
                                <input type="file" name="price_excel" class="form-control bg-dark bg-opacity-50 text-light border-secondary" accept=".xlsx,.xls" style="border-radius: 8px;" required>
                                <small class="text-info mt-1 d-block">💡 업로드된 엑셀에서 단가 데이터를 추출하여 파렛트랙 견적 산출에 활용합니다. (AI 분석 진행)</small>
                            </div>
                            <div class="text-end mt-3">
                                <button type="submit" class="btn btn-success fw-bold px-4">
                                    <i class="fa-solid fa-upload me-1"></i> 파일 업로드 및 적용
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="col-lg-12">
                    <div class="glass-panel p-4 mt-2">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold text-warning mb-0">과거 업로드 이력</h5>
                            <div>
                                <button type="button" class="btn btn-sm btn-outline-danger me-2" id="btnSelectDelete">선택 삭제</button>
                                <button type="button" class="btn btn-sm btn-danger" id="btnDeleteAll">전체 삭제</button>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-dark table-hover align-middle mb-0" style="--bs-table-bg: transparent; --bs-table-hover-bg: rgba(255,255,255,0.03);">
                                <thead>
                                    <tr class="text-light opacity-75 small uppercase" style="border-bottom: 1px solid rgba(255,255,255,0.12); font-weight: 600;">
                                        <th class="py-3 ps-3 text-center" width="40"><input type="checkbox" class="form-check-input" id="chkAll"></th>
                                        <th class="py-3 text-center" width="80">번호</th>
                                        <th class="py-3">적용 월</th>
                                        <th class="py-3">데이터 요약</th>
                                        <th class="py-3 text-center" width="180">업로드 일시</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(empty($rules)): ?>
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-muted">업로드된 이력이 없습니다.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach($rules as $index => $rule): ?>
                                            <tr style="border-bottom: 1px solid rgba(255,255,255,0.06);">
                                                <td class="py-3 ps-3 text-center"><input type="checkbox" class="form-check-input chk-item" value="<?= $rule['id'] ?>"></td>
                                                <td class="py-3 font-monospace text-light opacity-50 text-center"><?= $rule['id'] ?></td>
                                                <td class="py-3 fw-bold text-light"><?= htmlspecialchars($rule['applied_month']) ?></td>
                                                <td class="py-3 text-light">
                                                    <?php 
                                                        $sourceFile = !empty($rule['source_file']) ? htmlspecialchars($rule['source_file']) : '엑셀 단가표 AI 파싱 완료';
                                                        $pData = json_decode($rule['pricing_data'], true);
                                                        
                                                        if (is_array($pData) && isset($pData['meta'])) {
                                                            $extractedAt = $pData['meta']['extracted_at'] ?? '알 수 없음';
                                                            echo "<span class='badge bg-info text-dark mb-1'><i class='fa-solid fa-calendar-check'></i> 데이터 기준일: {$extractedAt}</span><br>";
                                                            echo "<span class='text-muted small'><i class='fa-solid fa-file-excel'></i> {$sourceFile}</span>";
                                                        } else {
                                                            echo "<span class='badge bg-secondary mb-1'><i class='fa-solid fa-file-excel'></i> {$sourceFile}</span><br>";
                                                            echo "<span class='text-light small'>파싱 데이터 적용 완료</span>";
                                                        }
                                                    ?>
                                                </td>
                                                <td class="py-3 text-light opacity-75 small font-monospace text-center">
                                                    <?= htmlspecialchars($rule['created_at'] ?? 'N/A') ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- 페이징 -->
                        <?php if ($totalPages > 1): ?>
                        <nav class="mt-4">
                            <ul class="pagination justify-content-center pagination-sm">
                                <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                                    <a class="page-link bg-dark text-light border-secondary" href="?page=<?= $page - 1 ?>">이전</a>
                                </li>
                                <?php for($i = 1; $i <= $totalPages; $i++): ?>
                                    <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                                        <a class="page-link <?= ($page == $i) ? 'bg-warning text-dark border-warning' : 'bg-dark text-light border-secondary' ?>" href="?page=<?= $i ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>
                                <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                                    <a class="page-link bg-dark text-light border-secondary" href="?page=<?= $page + 1 ?>">다음</a>
                                </li>
                            </ul>
                        </nav>
                        <?php endif; ?>

                    </div>
                </div>
            </div>

        </div>
    </main>

    <!-- 로딩 오버레이 (숨김 상태) -->
    <div id="loadingOverlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.85); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); z-index: 9999; flex-direction: column; align-items: center; justify-content: center;">
        <div class="spinner-border text-info" role="status" style="width: 4rem; height: 4rem; border-width: 0.35em;">
            <span class="visually-hidden">Loading...</span>
        </div>
        <h4 class="text-white mt-4 fw-bold">잠시만 기다려 주세요...</h4>
        <p class="text-info opacity-75">AI가 단가표 데이터를 분석하고 있습니다.</p>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.getElementById('pricingUploadForm').addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            
            // 폼 제출이 정상적으로 실행되도록 약간의 지연 후 버튼 비활성화
            setTimeout(() => {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> 업로드 중...';
            }, 50);
            
            // 로딩 오버레이 표시
            const overlay = document.getElementById('loadingOverlay');
            overlay.style.display = 'flex';
        });

        // 체크박스 전체 선택/해제 로직
        const chkAll = document.getElementById('chkAll');
        const chkItems = document.querySelectorAll('.chk-item');
        
        if (chkAll) {
            chkAll.addEventListener('change', function() {
                chkItems.forEach(chk => {
                    chk.checked = chkAll.checked;
                });
            });
        }
        
        chkItems.forEach(chk => {
            chk.addEventListener('change', function() {
                if (!chk.checked) {
                    chkAll.checked = false;
                } else {
                    const allChecked = Array.from(chkItems).every(c => c.checked);
                    chkAll.checked = allChecked;
                }
            });
        });

        // 삭제 처리 공통 함수
        function processDelete(action, ids = []) {
            let titleText = action === 'all' ? "모든 단가표 이력을 삭제하시겠습니까?" : `${ids.length}개의 단가표 이력을 삭제하시겠습니까?`;
            let warnText = "삭제 시 과거 견적서 중 일부가 기본값으로 초기화될 수 있습니다.";
            
            Swal.fire({
                title: titleText,
                text: warnText,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
                confirmButtonText: '네, 삭제합니다',
                cancelButtonText: '취소'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('/vendor/pricing/delete', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            action: action,
                            ids: ids
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                title: '삭제 완료!',
                                text: data.message,
                                icon: 'success',
                                confirmButtonColor: '#10b981'
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire('오류', data.message, 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire('오류', '삭제 요청 중 문제가 발생했습니다.', 'error');
                    });
                }
            });
        }

        // 전체 삭제 버튼
        const btnDeleteAll = document.getElementById('btnDeleteAll');
        if (btnDeleteAll) {
            btnDeleteAll.addEventListener('click', function() {
                processDelete('all');
            });
        }

        // 선택 삭제 버튼
        const btnSelectDelete = document.getElementById('btnSelectDelete');
        if (btnSelectDelete) {
            btnSelectDelete.addEventListener('click', function() {
                const checkedIds = Array.from(document.querySelectorAll('.chk-item:checked')).map(chk => chk.value);
                if (checkedIds.length === 0) {
                    Swal.fire('알림', '삭제할 항목을 선택해 주세요.', 'info');
                    return;
                }
                processDelete('select', checkedIds);
            });
        }
    </script>
</body>
</html>
