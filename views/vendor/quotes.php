<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>공급사 관리 센터 - 견적요청 수신함</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- External Vendor Dashboard CSS -->
    <link href="/css/vendor_dashboard.css" rel="stylesheet">
</head>
<body>

    <!-- 🧭 좌측 네비게이션 사이드바 -->
    <?php include __DIR__ . '/sidebar.php'; ?>

    <!-- 💻 우측 메인 대시보드 영역 -->
    <main class="main-content">
        <div class="top-navbar">
            <div class="navbar-title fw-bold text-light" style="font-size: 1.1rem;">
                SaaS Dashboard &gt; 견적요청 수신함
            </div>
<div class="user-profile d-flex align-items-center gap-2">
                <?php
                    $dbBtn = \App\Core\Database::getInstance();
                    $stmtBtn = $dbBtn->prepare("SELECT plan FROM users WHERE user_id = ?");
                    $stmtBtn->execute([$user['user_id']]);
                    $btnPlan = $stmtBtn->fetchColumn();
                    if ($btnPlan !== 'pro'):
                ?>
                <!-- <a href="/vendor/addon_payment" class="btn btn-outline-warning btn-sm fw-bold px-3 py-1 me-3" style="border-radius: 10px;">
                    <i class="fa-solid fa-bolt"></i> 횟수 충전
                </a> -->
                <?php endif; ?>
                <i class="fa-solid fa-circle-user text-info fs-5"></i>
                <span class="small font-monospace text-light"><?= htmlspecialchars($user['username'] ?? 'User') ?>님</span>
            </div>
        </div>

        <div class="content-body">
            <div class="glass-panel p-0">
                <div class="p-4 border-bottom border-secondary d-flex justify-content-between align-items-center" style="background: rgba(255,255,255,0.01); border-radius: 16px 16px 0 0;">
                    <h4 class="m-0 fw-bold d-flex align-items-center gap-2" style="color: #fde047;">
                        <span>📬</span> 견적요청 목록 수신함
                    </h4>
                    <span class="badge bg-warning text-dark font-monospace fw-bold">총 <?= count($quotes) ?>건</span>
                </div>
                
<div class="p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <ul class="nav nav-tabs border-secondary mb-0 w-100" id="quoteTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active text-light bg-transparent border-0 border-bottom border-warning" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending" type="button" role="tab" onclick="window.location.href=window.location.pathname;">미완료 (<?= count($pending_quotes) ?>)</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link text-light bg-transparent border-0" id="completed-tab" data-bs-toggle="tab" data-bs-target="#completed" type="button" role="tab">완료됨 (<?= count($completed_quotes) ?>)</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link text-light bg-transparent border-0" id="inquiries-tab" data-bs-toggle="tab" data-bs-target="#inquiries" type="button" role="tab">
                                게시판 문의 
                                <?php if (!empty($pending_inquiries_count) && $pending_inquiries_count > 0): ?>
                                    <span class="badge bg-danger ms-1"><?= $pending_inquiries_count ?></span>
                                <?php else: ?>
                                    (<?= count($inquiries ?? []) ?>)
                                <?php endif; ?>
                            </button>
                        </li>
                    </ul>
                    
                    <div class="input-group ms-3" style="max-width: 300px;">
                        <span class="input-group-text bg-dark border-secondary text-secondary"><i class="fa-solid fa-search"></i></span>
                        <input type="text" id="quoteSearch" class="form-control form-control-sm bg-dark text-light border-secondary" placeholder="회사명, 연락처, 지역, 담당자 검색...">
                    </div>
                </div>

                <div class="tab-content" id="quoteTabsContent">
                    <!-- 미완료 탭 -->
                    <div class="tab-pane fade show active" id="pending" role="tabpanel">
                        <?php if (empty($pending_quotes)): ?>
                            <div class="text-center py-5">
                                <span style="font-size: 3rem;">📭</span>
                                <h5 class="text-light mt-3 fw-bold">미완료된 견적 요청이 없습니다.</h5>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-dark table-hover align-middle mb-0" style="--bs-table-bg: transparent; --bs-table-hover-bg: rgba(255,255,255,0.03);">
                                    <thead>
                                        <tr class="text-light opacity-75 small uppercase" style="border-bottom: 1px solid rgba(255,255,255,0.12); font-weight: 600;">
                                            <th class="py-3 ps-3">번호</th>
                                            <th class="py-3">회사명</th>
                                            <th class="py-3">담당자</th>
                                            <th class="py-3">연락처</th>
                                            <th class="py-3">현장 주소</th>
                                            <th class="py-3">작업 현황</th>
                                            <th class="py-3">요청 일시</th>
                                            <th class="py-3 pe-3 text-end">상세</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($pending_quotes as $i => $q): ?>
                                            <tr style="border-bottom: 1px solid rgba(255,255,255,0.06); cursor: pointer;" onclick="window.location.href='/vendor/quotes/<?= $q['id'] ?>'">
                                                <td class="py-3 ps-3 font-monospace text-light opacity-50"><?= $q['id'] ?></td>
                                                <td class="py-3 fw-bold text-light"><?= htmlspecialchars($q['company']) ?></td>
                                                <td class="py-3 text-light"><?= htmlspecialchars($q['name']) ?></td>
                                                <td class="py-3 text-info font-monospace fw-semibold"><?= htmlspecialchars($q['phone']) ?></td>
                                                <td class="py-3 text-light opacity-75 small"><?= htmlspecialchars($q['address']) ?></td>
                                                <td class="py-3">
                                                    <?php
                                                        $isActive = false;
                                                        if (isset($q['active_seconds_ago']) && $q['active_seconds_ago'] !== null) {
                                                            if ($q['active_seconds_ago'] >= 0 && $q['active_seconds_ago'] <= 12) {
                                                                $isActive = true;
                                                            }
                                                        }
                                                    ?>
                                                    <?php if ($isActive && !empty($q['active_employee_name'])): ?>
                                                        <span class="badge" style="background-color: <?= htmlspecialchars($q['active_employee_color'] ?? '#10b981') ?>; color: #fff;">
                                                            <span class="spinner-grow spinner-grow-sm me-1" role="status" style="width: 0.7rem; height: 0.7rem;"></span>
                                                            <?= htmlspecialchars($q['active_employee_name']) ?> 접속중
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary text-light opacity-50">
                                                            <i class="fa-solid fa-circle me-1" style="font-size: 0.6rem;"></i>대기중
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="py-3 text-light opacity-75 small font-monospace"><?= date('Y-m-d H:i', strtotime($q['created_at'])) ?></td>
                                                <td class="py-3 pe-3 text-end">
                                                    <a href="/vendor/quotes/<?= $q['id'] ?>" class="btn btn-outline-warning btn-sm rounded-pill px-3" style="font-size: 0.72rem; font-weight:700;">보기 🔍</a>
                                                    <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-3 ms-1" style="font-size: 0.72rem; font-weight:700;" onclick="deleteQuote(<?= $q['id'] ?>, event)">삭제 🗑️</button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- 완료됨 탭 -->
                    <div class="tab-pane fade" id="completed" role="tabpanel">
                        <?php if (empty($completed_quotes)): ?>
                            <div class="text-center py-5">
                                <span style="font-size: 3rem;">📭</span>
                                <h5 class="text-light mt-3 fw-bold">처리 완료된 견적 요청이 없습니다.</h5>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-dark table-hover align-middle mb-0" style="--bs-table-bg: transparent; --bs-table-hover-bg: rgba(255,255,255,0.03);">
                                    <thead>
                                        <tr class="text-light opacity-75 small uppercase" style="border-bottom: 1px solid rgba(255,255,255,0.12); font-weight: 600;">
                                            <th class="py-3 ps-3">번호</th>
                                            <th class="py-3">회사명</th>
                                            <th class="py-3">처리한 직원</th>
                                            <th class="py-3">현장 주소</th>
                                            <th class="py-3">요청 일시</th>
                                            <th class="py-3">처리 일시</th>
                                            <th class="py-3 pe-3 text-end">상세</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($completed_quotes as $i => $q): ?>
                                            <tr style="border-bottom: 1px solid rgba(255,255,255,0.06); cursor: pointer;" onclick="window.location.href='/vendor/quotes/<?= $q['id'] ?>'">
                                                <td class="py-3 ps-3 font-monospace text-light opacity-50"><?= $q['id'] ?></td>
                                                <td class="py-3 fw-bold text-light"><?= htmlspecialchars($q['company']) ?></td>
                                                <td class="py-3 text-light">
                                                    <span class="badge" style="background-color: <?= htmlspecialchars($q['employee_color'] ?? '#333') ?>;">
                                                        <?= htmlspecialchars($q['employee_name'] ?? '알 수 없음') ?>
                                                    </span>
                                                </td>
                                                <td class="py-3 text-light opacity-75 small"><?= htmlspecialchars($q['address']) ?></td>
                                                <td class="py-3 text-light opacity-75 small font-monospace"><?= date('Y-m-d H:i', strtotime($q['created_at'])) ?></td>
                                                <td class="py-3 text-light opacity-75 small font-monospace"><?= date('Y-m-d H:i', strtotime($q['processed_at'] ?? '')) ?></td>
                                                <td class="py-3 pe-3 text-end">
                                                    <a href="/vendor/quotes/<?= $q['id'] ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3" style="font-size: 0.72rem; font-weight:700;">보기 🔍</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- 게시판 문의 탭 -->
                    <div class="tab-pane fade" id="inquiries" role="tabpanel">
                        <?php if (empty($inquiries)): ?>
                            <div class="text-center py-5">
                                <span style="font-size: 3rem;">📝</span>
                                <h5 class="text-light mt-3 fw-bold">등록된 게시판 문의가 없습니다.</h5>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-dark table-hover align-middle mb-0" style="--bs-table-bg: transparent; --bs-table-hover-bg: rgba(255,255,255,0.03);">
                                    <thead>
                                        <tr class="text-light opacity-75 small uppercase" style="border-bottom: 1px solid rgba(255,255,255,0.12); font-weight: 600;">
                                            <th class="py-3 ps-3">번호</th>
                                            <th class="py-3">상태</th>
                                            <th class="py-3">제목</th>
                                            <th class="py-3">회사명(담당자)</th>
                                            <th class="py-3">작성 일시</th>
                                            <th class="py-3 pe-3 text-end">상세</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($inquiries as $i => $inq): ?>
                                            <tr style="border-bottom: 1px solid rgba(255,255,255,0.06); cursor: pointer;" onclick="window.location.href='/vendor/quotes/<?= $inq['id'] ?>'">
                                                <td class="py-3 ps-3 font-monospace text-light opacity-50"><?= $inq['id'] ?></td>
                                                <td class="py-3">
                                                    <?php if($inq['status'] === 'pending'): ?>
                                                        <span class="badge bg-danger">미답변</span>
                                                    <?php elseif($inq['status'] === 'answered'): ?>
                                                        <span class="badge bg-success">답변완료</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">종료</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="py-3 fw-bold text-light"><?= htmlspecialchars($inq['title']) ?></td>
                                                <td class="py-3 text-light">
                                                    <?= htmlspecialchars($inq['company'] ?: '개인') ?> 
                                                    <span class="opacity-75 small">(<?= htmlspecialchars($inq['name']) ?>)</span>
                                                </td>
                                                <td class="py-3 text-light opacity-75 small font-monospace"><?= date('Y-m-d H:i', strtotime($inq['created_at'])) ?></td>
                                                <td class="py-3 pe-3 text-end">
                                                    <a href="/vendor/quotes/<?= $inq['id'] ?>" class="btn btn-outline-info btn-sm rounded-pill px-3" style="font-size: 0.72rem; font-weight:700;">보기 🔍</a>
                                                    <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-3 ms-1" style="font-size: 0.72rem; font-weight:700;" onclick="deleteQuote(<?= $inq['id'] ?>, event)">삭제 🗑️</button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- 페이징 UI -->
                <?php if (isset($totalPages) && $totalPages > 1): ?>
                <nav class="mt-4">
                    <ul class="pagination justify-content-center pagination-sm">
                        <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                            <a class="page-link bg-dark text-light border-secondary" href="?page=<?= max(1, $page - 1) ?>">이전</a>
                        </li>
                        <?php for($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                                <a class="page-link <?= ($page == $i) ? 'bg-warning text-dark border-warning' : 'bg-dark text-light border-secondary' ?>" href="?page=<?= $i ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                            <a class="page-link bg-dark text-light border-secondary" href="?page=<?= min($totalPages, $page + 1) ?>">다음</a>
                        </li>
                    </ul>
                </nav>
                <?php endif; ?>

</div>
            </div>
        </div>
    </main>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Tab switching style
        document.querySelectorAll('#quoteTabs .nav-link').forEach(tab => {
            tab.addEventListener('click', function(e) {
                e.preventDefault();
                document.querySelectorAll('#quoteTabs .nav-link').forEach(t => t.classList.remove('border-bottom', 'border-warning'));
                this.classList.add('border-bottom', 'border-warning');
            });
        });

        // Search Filter
        const searchInput = document.getElementById('quoteSearch');
        if (searchInput) {
            searchInput.addEventListener('keyup', function(e) {
                const term = e.target.value.toLowerCase();
                const allRows = document.querySelectorAll('tbody tr');
                
                allRows.forEach(row => {
                    const text = row.innerText.toLowerCase();
                    if (text.includes(term)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        }

        // 견적/문의 삭제 함수
        function deleteQuote(id, event) {
            event.stopPropagation(); // 행 클릭 이벤트(보기 페이지 이동) 방지
            if (!confirm('정말 이 항목을 삭제하시겠습니까? 삭제 후에는 복구할 수 없습니다.')) {
                return;
            }
            
            fetch(`/vendor/quotes/${id}/delete`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' }
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    alert('성공적으로 삭제되었습니다.');
                    location.reload();
                } else {
                    alert('삭제 실패: ' + (data.message || '알 수 없는 오류'));
                }
            })
            .catch(err => {
                console.error(err);
                alert('서버 통신 중 오류가 발생했습니다.');
            });
        }
    </script>
</body>
</html>
