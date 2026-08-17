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
    <aside class="sidebar">
        <a href="/" class="sidebar-brand">
            <span>⚙️</span>
            <span>ASAMIYA SAAS</span>
        </a>
        <div class="sidebar-menu">
            <a href="/vendor/settings" class="menu-item">
                <i class="fa-solid fa-sliders"></i>
                <span>공급사 설정 관리</span>
            </a>
            <a href="/vendor/quotes" class="menu-item active">
                <i class="fa-solid fa-envelope-open-text"></i>
                <span>견적요청 수신함</span>
            </a>
            <hr style="border-color: rgba(255,255,255,0.08); margin: 15px 0;">
            <a href="/" class="menu-item">
                <i class="fa-solid fa-house"></i>
                <span>홈페이지 메인</span>
            </a>
            <a href="/logout" class="menu-item" style="color: #f87171;">
                <i class="fa-solid fa-power-off"></i>
                <span>로그아웃</span>
            </a>
        </div>
    </aside>

    <!-- 💻 우측 메인 대시보드 영역 -->
    <main class="main-content">
        <div class="top-navbar">
            <div class="navbar-title fw-bold text-light" style="font-size: 1.1rem;">
                SaaS Dashboard &gt; 견적요청 수신함
            </div>
            <div class="user-profile d-flex align-items-center gap-2">
                <i class="fa-solid fa-circle-user text-info fs-5"></i>
                <span class="small font-monospace text-light"><?= htmlspecialchars($_SESSION['user']['username'] ?? 'User') ?>님</span>
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
                    <?php if (empty($quotes)): ?>
                        <div class="text-center py-5">
                            <span style="font-size: 3rem;">📭</span>
                            <h5 class="text-light mt-3 fw-bold">아직 도착한 견적 요청이 없습니다.</h5>
                            <p class="text-light opacity-75 small">고객이 도면 배치 완료 후 '견적 요청' 버튼을 누르면 여기에 수신됩니다.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-dark table-hover align-middle mb-0" style="--bs-table-bg: transparent; --bs-table-hover-bg: rgba(255,255,255,0.03);">
                                <thead>
                                    <tr class="text-light opacity-75 small uppercase" style="border-bottom: 1px solid rgba(255,255,255,0.12); font-weight: 600;">
                                        <th class="py-3 ps-3" style="width: 80px;">번호</th>
                                        <th class="py-3">회사명</th>
                                        <th class="py-3">담당자</th>
                                        <th class="py-3">연락처</th>
                                        <th class="py-3">현장 주소</th>
                                        <th class="py-3">요청 일시</th>
                                        <th class="py-3 pe-3 text-end" style="width: 120px;">상세</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($quotes as $i => $q): ?>
                                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.06); cursor: pointer;" onclick="window.location.href='/vendor/quotes/<?= $q['id'] ?>'">
                                            <td class="py-3 ps-3 font-monospace text-light opacity-50"><?= count($quotes) - $i ?></td>
                                            <td class="py-3 fw-bold text-light"><?= htmlspecialchars($q['company']) ?></td>
                                            <td class="py-3 text-light"><?= htmlspecialchars($q['name']) ?></td>
                                            <td class="py-3 text-info font-monospace fw-semibold"><?= htmlspecialchars($q['phone']) ?></td>
                                            <td class="py-3 text-light opacity-75 small"><?= htmlspecialchars($q['address']) ?></td>
                                            <td class="py-3 text-light opacity-75 small font-monospace"><?= date('Y-m-d H:i', strtotime($q['created_at'])) ?></td>
                                            <td class="py-3 pe-3 text-end">
                                                <a href="/vendor/quotes/<?= $q['id'] ?>" class="btn btn-outline-warning btn-sm rounded-pill px-3" style="font-size: 0.72rem; font-weight:700;">보기 🔍</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
