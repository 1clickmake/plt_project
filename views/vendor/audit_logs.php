<?php
$pageTitle = "단가 유출 보안 추적 (감사 로그)";
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- External Vendor Dashboard CSS -->
    <link href="/css/vendor_dashboard.css" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', -apple-system, BlinkMacSystemFont, sans-serif; background-color: #0b0f19; color: #f8fafc; }
        .mono { font-family: 'JetBrains Mono', monospace; }
        .glass-card { background: rgba(30, 41, 59, 0.45); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 16px; backdrop-filter: blur(12px); }
        .stat-card { background: linear-gradient(135deg, rgba(30, 41, 59, 0.6) 0%, rgba(15, 23, 42, 0.8) 100%); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 14px; padding: 20px; transition: transform 0.2s ease; }
        .stat-card:hover { transform: translateY(-2px); }
        .table-dark-custom { background-color: transparent !important; color: #f1f5f9; }
        .table-dark-custom th { background-color: rgba(15, 23, 42, 0.7) !important; color: #94a3b8; font-weight: 600; font-size: 0.82rem; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid rgba(255,255,255,0.1); }
        .table-dark-custom td { background-color: rgba(30, 41, 59, 0.2) !important; border-bottom: 1px solid rgba(255,255,255,0.05); font-size: 0.88rem; vertical-align: middle; }
        .table-dark-custom tr:hover td { background-color: rgba(59, 130, 246, 0.08) !important; }
        .badge-action { font-size: 0.75rem; padding: 4px 8px; border-radius: 6px; font-weight: 600; }
    </style>
</head>
<body>

    <!-- 🧭 좌측 네비게이션 사이드바 -->
    <?php include __DIR__ . '/sidebar.php'; ?>

    <!-- 💻 우측 메인 대시보드 영역 -->
    <main class="main-content">
        <div class="top-navbar">
            <div class="navbar-title fw-bold text-light" style="font-size: 1.1rem;">
                <i class="fa-solid fa-shield-halved text-warning me-2"></i> B2B 보안 무기 &gt; 단가 유출 감사 추적기
            </div>
            <div class="user-profile d-flex align-items-center gap-2">
                <i class="fa-solid fa-circle-user text-info fs-5"></i>
                <span class="small font-monospace text-light"><?= htmlspecialchars($user['username'] ?? 'User') ?>님</span>
            </div>
        </div>

        <div class="p-4 max-w-[1400px] mx-auto">
            <!-- 헤더 타이틀 -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h3 class="fw-bold text-light mb-1">
                        <i class="fa-solid fa-fingerprint text-info me-2"></i> 단가 열람 추적 로그 (Forensic Audit)
                    </h3>
                    <p class="text-white-50 small mb-0">
                        영업사원 및 사용자의 모든 원단가 열람, 견적 계산, PDF 인쇄 이력을 0.01초 만에 추적합니다.
                    </p>
                </div>
                <div>
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2">
                        <i class="fa-solid fa-bolt me-1"></i> 복합 인덱스 가속화 (검색 3초 컷)
                    </span>
                </div>
            </div>

            <!-- 요약 통계 카드 3종 -->
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="stat-card d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-white-50 small mb-1">오늘 단가 조회 건수</div>
                            <h3 class="fw-bold mb-0 text-white"><?= number_format($todayCount ?? 0) ?><span class="fs-6 text-white-50 ms-1">건</span></h3>
                        </div>
                        <div class="bg-primary bg-opacity-10 p-3 rounded-circle text-primary fs-4">
                            <i class="fa-solid fa-eye"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-white-50 small mb-1">최근 1시간 집중 열람 (유출 리스크 감지)</div>
                            <h3 class="fw-bold mb-0 <?= ($recentLeakRisk > 20) ? 'text-danger' : 'text-warning' ?>">
                                <?= number_format($recentLeakRisk ?? 0) ?><span class="fs-6 text-white-50 ms-1">건</span>
                            </h3>
                        </div>
                        <div class="bg-warning bg-opacity-10 p-3 rounded-circle text-warning fs-4">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-white-50 small mb-1">최다 열람 사원/계정</div>
                            <h4 class="fw-bold mb-0 text-info font-monospace">
                                <?= htmlspecialchars($topUser['user_id'] ?? '기록 없음') ?>
                                <span class="fs-6 text-white-50 ms-1">(<?= number_format($topUser['cnt'] ?? 0) ?>회)</span>
                            </h4>
                        </div>
                        <div class="bg-info bg-opacity-10 p-3 rounded-circle text-info fs-4">
                            <i class="fa-solid fa-user-secret"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 필터 & 검색 카드 -->
            <div class="glass-card p-3 mb-4">
                <form method="GET" action="/vendor/audit_logs" class="row g-2 align-items-center">
                    <div class="col-md-2">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-dark border-secondary text-white-50"><i class="fa-solid fa-user"></i></span>
                            <input type="text" name="user_id" class="form-control bg-dark text-white border-secondary" placeholder="사번 / ID" value="<?= htmlspecialchars($searchUser) ?>">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-dark border-secondary text-info"><i class="fa-solid fa-fingerprint"></i></span>
                            <input type="text" name="audit_token" class="form-control bg-dark text-white border-secondary font-monospace" placeholder="감사토큰 (#8F3A...)" value="<?= htmlspecialchars($searchToken ?? '') ?>">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <select name="action" class="form-select form-select-sm bg-dark text-white border-secondary">
                            <option value="">전체 작업(Action)</option>
                            <option value="VIEW_PRICING_TABLE" <?= $searchAction === 'VIEW_PRICING_TABLE' ? 'selected' : '' ?>>단가표 열람</option>
                            <option value="VIEW_QUOTE_PRICE" <?= $searchAction === 'VIEW_QUOTE_PRICE' ? 'selected' : '' ?>>견적 단가 산출</option>
                            <option value="VIEW_QUOTE_DOCUMENT" <?= $searchAction === 'VIEW_QUOTE_DOCUMENT' ? 'selected' : '' ?>>견적서(인쇄) 열람</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-dark border-secondary text-white-50"><i class="fa-solid fa-network-wired"></i></span>
                            <input type="text" name="ip_address" class="form-control bg-dark text-white border-secondary" placeholder="IP 주소" value="<?= htmlspecialchars($searchIp) ?>">
                        </div>
                    </div>
                    <div class="col-md-2 d-flex align-items-center gap-1">
                        <input type="date" name="date_from" class="form-control form-control-sm bg-dark text-white border-secondary" value="<?= htmlspecialchars($dateFrom) ?>">
                        <span class="text-white-50">~</span>
                        <input type="date" name="date_to" class="form-control form-control-sm bg-dark text-white border-secondary" value="<?= htmlspecialchars($dateTo) ?>">
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm flex-fill fw-bold">
                            <i class="fa-solid fa-magnifying-glass me-1"></i> 검색
                        </button>
                        <a href="/vendor/audit_logs" class="btn btn-outline-secondary btn-sm" title="초기화">
                            <i class="fa-solid fa-rotate-left"></i>
                        </a>
                    </div>
                </form>
            </div>

            <!-- 감사 로그 테이블 -->
            <div class="glass-card overflow-hidden">
                <div class="d-flex justify-content-between align-items-center p-3 border-bottom border-secondary border-opacity-25">
                    <div class="fw-bold small text-white-50">
                        총 <b class="text-info"><?= number_format($totalCount) ?></b>건의 보안 접근 로그가 검색되었습니다.
                    </div>
                    <div class="small text-white-50">
                        <i class="fa-solid fa-lock text-success me-1"></i> 365일 보관 정책 및 위변조 방지 지문 적용 중
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-dark-custom mb-0">
                        <thead>
                            <tr>
                                <th style="width: 60px;" class="text-center">번호</th>
                                <th style="width: 130px;">열람자 (사번/ID)</th>
                                <th style="width: 140px;">열람 작업</th>
                                <th style="width: 150px;">감사 토큰 (지문)</th>
                                <th style="width: 180px;">대상 문서/공급사</th>
                                <th>상세 내역</th>
                                <th style="width: 120px;">접속 IP</th>
                                <th style="width: 150px;" class="text-end">열람 일시</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($logs)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-5 text-white-50">
                                    <i class="fa-solid fa-shield-cat fs-1 d-block mb-3 text-secondary"></i>
                                    조회된 보안 감사 로그가 없습니다.
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($logs as $row): 
                                $actionBadge = 'bg-secondary';
                                $actionName = $row['action'];
                                if ($row['action'] === 'VIEW_PRICING_TABLE') {
                                    $actionBadge = 'bg-primary';
                                    $actionName = '단가표 열람';
                                } else if ($row['action'] === 'VIEW_QUOTE_PRICE') {
                                    $actionBadge = 'bg-warning text-dark';
                                    $actionName = '원단가 산출';
                                } else if ($row['action'] === 'VIEW_QUOTE_DOCUMENT') {
                                    $actionBadge = 'bg-success';
                                    $actionName = '견적서(인쇄)';
                                }
                            ?>
                            <tr>
                                <td class="text-center text-white-50 mono"><?= $row['id'] ?></td>
                                <td>
                                    <span class="fw-bold text-light mono">
                                        <i class="fa-solid fa-user-tag text-info me-1"></i><?= htmlspecialchars($row['user_id'] ?? '익명') ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge <?= $actionBadge ?> badge-action">
                                        <?= $actionName ?>
                                    </span>
                                </td>
                                <td class="mono small">
                                    <?php if (!empty($row['audit_token'])): ?>
                                    <span class="badge bg-dark text-info border border-info border-opacity-50">
                                        <i class="fa-solid fa-key me-1"></i>#<?= htmlspecialchars($row['audit_token']) ?>
                                    </span>
                                    <?php else: ?>
                                    <span class="text-white-50">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-light">
                                    <i class="fa-regular fa-file-lines text-secondary me-1"></i>
                                    <?= htmlspecialchars($row['target'] ?? '-') ?>
                                </td>
                                <td class="text-white-50 small" style="max-width: 250px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?= htmlspecialchars($row['details'] ?? '') ?>">
                                    <?= htmlspecialchars($row['details'] ?? '-') ?>
                                </td>
                                <td class="mono small text-info">
                                    <?= htmlspecialchars($row['ip_address']) ?>
                                </td>
                                <td class="text-end mono small text-white-50">
                                    <?= $row['created_at'] ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- 페이지네이션 -->
                <?php if ($totalPages > 1): ?>
                <div class="d-flex justify-content-center p-3 border-top border-secondary border-opacity-25">
                    <nav>
                        <ul class="pagination pagination-sm mb-0">
                            <?php for ($p = 1; $p <= $totalPages; $p++): 
                                $query = $_GET;
                                $query['page'] = $p;
                                $pageUrl = '/vendor/audit_logs?' . http_build_query($query);
                            ?>
                            <li class="page-item <?= ($p == $currentPage) ? 'active' : '' ?>">
                                <a class="page-link bg-dark text-white border-secondary <?= ($p == $currentPage) ? 'bg-primary border-primary' : '' ?>" href="<?= $pageUrl ?>">
                                    <?= $p ?>
                                </a>
                            </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- 🛡️ B2B Stealth Security Watermark Overlay -->
    <?php include __DIR__ . '/watermark.php'; ?>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
