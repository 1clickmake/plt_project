<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>공급사 관리 센터 - 설정 관리</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- External Vendor Dashboard CSS -->
    <link href="/css/vendor_dashboard.css" rel="stylesheet">

    <style>
        .form-control::placeholder {
            color: #adb5bd !important;
            opacity: 0.7;
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
                SaaS Dashboard &gt; 공급사 정보 설정
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
            <div class="glass-panel p-4" style="max-width: 850px; margin: 0 auto;">
                <h4 class="fw-bold mb-4 pb-2 border-bottom border-secondary d-flex align-items-center gap-2" style="color: #c084fc;">
                    <span>🔧</span> 공급사 정보 및 단가 설정
                </h4>
                
                <form action="/vendor/settings" method="POST" enctype="multipart/form-data">
                    <div class="row g-4">
                        <!-- 회사명 -->
                        <div class="col-md-6">
                            <label class="form-label text-light small fw-bold mb-1">Company Name (회사명)</label>
                            <input type="text" name="company_name" class="form-control bg-dark bg-opacity-50 text-light border-secondary" placeholder="회사명" value="<?= htmlspecialchars($settings['company_name'] ?? '') ?>" required style="border-radius: 8px; padding: 10px;">
                        </div>
                        
                        <!-- 연락처 -->
                        <div class="col-md-6">
                            <label class="form-label text-light small fw-bold mb-1">Contact Number (전화번호)</label>
                            <input type="text" name="contact_number" class="form-control bg-dark bg-opacity-50 text-light border-secondary" placeholder="예: 02-1234-5678" value="<?= htmlspecialchars($settings['contact_number'] ?? '') ?>" style="border-radius: 8px; padding: 10px;">
                        </div>
                        
                        <!-- 팩스 -->
                        <div class="col-md-6">
                            <label class="form-label text-light small fw-bold mb-1">Fax Number (팩스)</label>
                            <input type="text" name="fax_number" class="form-control bg-dark bg-opacity-50 text-light border-secondary" placeholder="예: 02-1234-5678" value="<?= htmlspecialchars($settings['fax_number'] ?? '') ?>" style="border-radius: 8px; padding: 10px;">
                        </div>

                        <!-- 본사 주소 -->
                        <div class="col-12">
                            <label class="form-label text-light small fw-bold mb-1">Headquarters Address (본사 주소)</label>
                            <input type="text" name="headquarters_address" class="form-control bg-dark bg-opacity-50 text-light border-secondary" placeholder="예: 서울 강님구 역삼동 11-11" value="<?= htmlspecialchars($settings['headquarters_address'] ?? '') ?>" style="border-radius: 8px; padding: 10px;">
                        </div>

                        <!-- 담당자 -->
                        <div class="col-md-6">
                            <label class="form-label text-light small fw-bold mb-1">대표자명</label>
                            <input type="text" name="manager_name" class="form-control bg-dark bg-opacity-50 text-light border-secondary" placeholder="홍길동" value="<?= htmlspecialchars($settings['manager_name'] ?? '') ?>" style="border-radius: 8px; padding: 10px;">
                        </div>

                        <!-- 담당자 이메일 -->
                        <div class="col-md-6">
                            <label class="form-label text-light small fw-bold mb-1">Manager Email (이메일)</label>
                            <input type="email" name="manager_email" class="form-control bg-dark bg-opacity-50 text-light border-secondary" placeholder="예: email@email.net" value="<?= htmlspecialchars($settings['manager_email'] ?? '') ?>" style="border-radius: 8px; padding: 10px;">
                        </div>

                        <!-- 공장 주소 -->
                        <div class="col-12">
                            <label class="form-label text-light small fw-bold mb-1">Factory Address (공장 주소)</label>
                            <input type="text" name="factory_address" class="form-control bg-dark bg-opacity-50 text-light border-secondary" placeholder="" value="<?= htmlspecialchars($settings['factory_address'] ?? '') ?>" style="border-radius: 8px; padding: 10px;">
                        </div>

                        <!-- 공장 연락처 -->
                        <div class="col-md-6">
                            <label class="form-label text-light small fw-bold mb-1">Factory Contact (공장 연락처)</label>
                            <input type="text" name="factory_contact" class="form-control bg-dark bg-opacity-50 text-light border-secondary" placeholder="" value="<?= htmlspecialchars($settings['factory_contact'] ?? '') ?>" style="border-radius: 8px; padding: 10px;">
                        </div>
                        
                        <!-- 계좌 정보 -->
                        <div class="col-md-6 mt-3">
                            <label class="form-label text-light small fw-bold mb-1">Account Info (계좌 정보)</label>
                            <input type="text" name="bank_account" class="form-control bg-dark bg-opacity-50 text-light border-secondary" placeholder="예: 국민 123456-789 홍길동" value="<?= htmlspecialchars($settings['bank_account'] ?? '') ?>" style="border-radius: 8px; padding: 10px;">
                        </div>
                        
                        <!-- URL 슬러그 설정 -->
                        <div class="col-12">
                            <label class="form-label text-light small fw-bold mb-1">Custom URL Slug (고유 접속 주소)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-dark border-secondary text-light small" style="border-top-left-radius: 8px; border-bottom-left-radius: 8px;"><?= $_ENV['APP_URL'] ?? 'http://localhost:8001' ?>/quote/</span>
                                <input type="text" name="url_slug" class="form-control bg-dark bg-opacity-50 text-light border-secondary fw-semibold" value="<?= htmlspecialchars($settings['url_slug'] ?? $user['user_id']) ?>" required style="border-top-right-radius: 8px; border-bottom-right-radius: 8px; padding: 10px;">
                            </div>
                            <small class="text-info mt-1 d-block">💡 이 주소를 통해 고객들이 직접 견적용 캔버스 도면에 접속하게 됩니다.</small>
                        </div>
                        
                        <!-- 로고 이미지 -->
                        <div class="col-12">
                            <label class="form-label text-light small fw-bold mb-1">Company Logo (로고 이미지)</label>
                            <?php if(!empty($settings['company_logo'])): ?>
                                <div class="mb-3 p-2 bg-dark bg-opacity-25 rounded d-inline-block border border-secondary">
                                    <img src="<?= htmlspecialchars($settings['company_logo']) ?>" alt="Current Logo" style="max-height: 60px; padding: 5px; object-fit: contain;">
                                    <div class="text-center mt-1"><span class="badge bg-secondary font-monospace small">현재 등록됨</span></div>
                                </div>
                            <?php endif; ?>
                            <input type="file" name="company_logo" class="form-control bg-dark bg-opacity-50 text-light border-secondary" accept="image/*" style="border-radius: 8px;">
                        </div>


                    </div>

                    <div class="d-grid mt-5">
                        <button type="submit" class="btn btn-lg fw-bold" style="
                            background: linear-gradient(135deg, #a855f7 0%, #7c3aed 100%);
                            color: white; border: none; border-radius: 10px; padding: 12px;
                            transition: all 0.2s;
                        " onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 8px 20px rgba(168, 85, 247, 0.45)';" onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='none';">
                            💾 설정 정보 저장하기
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
