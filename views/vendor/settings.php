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
    
    <style>
        :root {
            --bg-primary: #020617;
            --bg-secondary: #0f172a;
            --accent-purple: #a855f7;
            --accent-glow: rgba(168, 85, 247, 0.35);
            --text-light: #f8fafc;
            --text-muted: #94a3b8;
            --border-rgba: rgba(255, 255, 255, 0.08);
        }
        
        body {
            font-family: 'Outfit', sans-serif;
            background: radial-gradient(circle at top left, #0f172a 0%, #020617 100%);
            color: var(--text-light);
            min-height: 100vh;
            display: flex;
            overflow-x: hidden;
            margin: 0;
        }

        /* Sidebar Styling */
        .sidebar {
            width: 260px;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-right: 1px solid rgba(255, 255, 255, 0.06);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 1000;
        }
        
        .sidebar-brand {
            padding: 24px;
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--accent-purple);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
            border-bottom: 1px solid rgba(255,255,255,0.05);
            text-shadow: 0 0 15px rgba(168, 85, 247, 0.4);
        }

        .sidebar-menu {
            padding: 20px 14px;
            display: flex;
            flex-direction: column;
            gap: 6px;
            flex-grow: 1;
        }

        .menu-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            color: var(--text-muted);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.92rem;
            border-radius: 10px;
            transition: all 0.2s ease;
        }

        .menu-item:hover {
            background: rgba(255, 255, 255, 0.04);
            color: var(--text-light);
            transform: translateX(4px);
        }

        .menu-item.active {
            background: linear-gradient(135deg, rgba(168, 85, 247, 0.15) 0%, rgba(124, 58, 237, 0.15) 100%);
            border: 1px solid rgba(168, 85, 247, 0.4);
            color: #c084fc;
        }

        /* Main Content Styling */
        .main-content {
            margin-left: 260px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .top-navbar {
            height: 70px;
            background: rgba(15, 23, 42, 0.3);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 40px;
        }

        .content-body {
            padding: 40px;
            flex-grow: 1;
        }
        
        /* Glassmorphic panels */
        .glass-panel {
            background: rgba(30, 41, 59, 0.45);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.55);
        }
    </style>
</head>
<body>

    <!-- 🧭 좌측 네비게이션 사이드바 -->
    <aside class="sidebar">
        <a href="/" class="sidebar-brand">
            <span>⚙️</span>
            <span>ASAMIYA SAAS</span>
        </a>
        <div class="sidebar-menu">
            <a href="/vendor/settings" class="menu-item active">
                <i class="fa-solid fa-sliders"></i>
                <span>공급사 설정 관리</span>
            </a>
            <a href="/vendor/quotes" class="menu-item">
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
                SaaS Dashboard &gt; 공급사 정보 설정
            </div>
            <div class="user-profile d-flex align-items-center gap-2">
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
                            <input type="text" name="company_name" class="form-control bg-dark bg-opacity-50 text-light border-secondary" placeholder="예: 아사미야 랙 주식회사" value="<?= htmlspecialchars($settings['company_name'] ?? '') ?>" required style="border-radius: 8px; padding: 10px;">
                        </div>
                        
                        <!-- 연락처 -->
                        <div class="col-md-6">
                            <label class="form-label text-light small fw-bold mb-1">Contact Number (연락처)</label>
                            <input type="text" name="contact_number" class="form-control bg-dark bg-opacity-50 text-light border-secondary" placeholder="예: 02-1234-5678" value="<?= htmlspecialchars($settings['contact_number'] ?? '') ?>" style="border-radius: 8px; padding: 10px;">
                        </div>
                        
                        <!-- URL 슬러그 설정 -->
                        <div class="col-12">
                            <label class="form-label text-light small fw-bold mb-1">Custom URL Slug (고유 접속 주소)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-dark border-secondary text-light small" style="border-top-left-radius: 8px; border-bottom-left-radius: 8px;"><?= $_ENV['APP_URL'] ?? 'http://localhost:8001' ?>/quote/</span>
                                <input type="text" name="url_slug" class="form-control bg-dark bg-opacity-50 text-light border-secondary fw-semibold" placeholder="예: asamiya" value="<?= htmlspecialchars($settings['url_slug'] ?? '') ?>" required style="border-top-right-radius: 8px; border-bottom-right-radius: 8px; padding: 10px;">
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

                        <!-- 단가표 엑셀 업로드 -->
                        <div class="col-12">
                            <label class="form-label text-light small fw-bold mb-1">Price List Excel (단가표 엑셀 업로드)</label>
                            <?php if(!empty($settings['price_excel_path'])): ?>
                                <div class="mb-3 p-3 bg-dark bg-opacity-25 rounded border border-secondary d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-2">
                                        <span style="font-size:1.5rem;">📊</span>
                                        <div>
                                            <span class="badge bg-success small"><i class="fa-solid fa-file-excel"></i> 엑셀 파일 파싱 완료</span>
                                            <div class="text-light small mt-1 font-monospace"><?= htmlspecialchars(basename($settings['price_excel_path'])) ?></div>
                                        </div>
                                    </div>
                                    <a href="<?= htmlspecialchars($settings['price_excel_path']) ?>" class="btn btn-outline-secondary btn-sm rounded" download>다운로드</a>
                                </div>
                            <?php endif; ?>
                            <input type="file" name="price_excel" class="form-control bg-dark bg-opacity-50 text-light border-secondary" accept=".xlsx,.xls" style="border-radius: 8px;">
                            <small class="text-info mt-1 d-block">💡 업로드된 엑셀에서 단가 데이터를 추출하여 파렛트랙 견적 산출에 활용합니다.</small>
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
