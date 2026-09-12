<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>공급사 관리 센터 - 도면 퍼가기 및 연동 가이드</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Noto+Sans+KR:wght@300;400;500;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- External Vendor Dashboard CSS -->
    <link href="/css/vendor_dashboard.css" rel="stylesheet">

    <style>
        body {
            font-family: 'Outfit', 'Noto Sans KR', sans-serif;
            background-color: #0b0f19;
            color: #f1f5f9;
        }
        .embed-card {
            background: rgba(15, 23, 42, 0.75);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 1rem;
            backdrop-filter: blur(12px);
            transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
        }
        .embed-card:hover {
            border-color: rgba(56, 189, 248, 0.3);
            box-shadow: 0 12px 30px -10px rgba(0, 0, 0, 0.5);
        }
        .code-box {
            background: #020617;
            border: 1px solid rgba(56, 189, 248, 0.2);
            border-radius: 0.75rem;
            padding: 1rem;
            font-family: 'JetBrains Mono', 'Fira Code', monospace;
            font-size: 0.85rem;
            color: #38bdf8;
            position: relative;
            word-break: break-all;
            white-space: pre-wrap;
        }
        .theme-selector-btn {
            border: 2px solid rgba(255, 255, 255, 0.1);
            background: rgba(15, 23, 42, 0.6);
            color: #94a3b8;
            border-radius: 0.75rem;
            padding: 0.75rem 1.25rem;
            cursor: pointer;
            transition: all 0.25s ease;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 600;
        }
        .theme-selector-btn:hover {
            border-color: rgba(56, 189, 248, 0.4);
            color: #f8fafc;
        }
        .theme-selector-btn.active {
            border-color: #38bdf8;
            background: rgba(56, 189, 248, 0.12);
            color: #38bdf8;
            box-shadow: 0 0 15px rgba(56, 189, 248, 0.25);
        }
        .copy-btn {
            background: linear-gradient(135deg, #0284c7, #2563eb);
            border: none;
            color: white;
            font-weight: 600;
            padding: 6px 14px;
            border-radius: 6px;
            font-size: 0.8rem;
            transition: all 0.2s;
        }
        .copy-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(2, 132, 199, 0.4);
            color: white;
        }
        .copy-toast {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: #10b981;
            color: white;
            padding: 12px 24px;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.4);
            font-weight: 600;
            z-index: 9999;
            display: none;
            align-items: center;
            gap: 8px;
            animation: fadeIn 0.3s ease;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
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
                SaaS Dashboard &gt; 도면 툴 퍼가기 / 공유 연동
            </div>
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

        <div class="content-body px-4 py-4">
            
            <?php 
            $slug = $vendorSettings['url_slug'] ?? '';
            $hasSlug = !empty($slug);
            ?>

            <?php if (!$hasSlug): ?>
                <!-- 슬러그 미설정 경고 -->
                <div class="alert alert-warning border-0 rounded-4 p-4 mb-4 d-flex align-items-center justify-content-between shadow-lg" style="background: rgba(245, 158, 11, 0.15); border: 1px solid rgba(245, 158, 11, 0.3) !important;">
                    <div class="d-flex align-items-center gap-3">
                        <i class="fa-solid fa-triangle-exclamation text-warning fs-2"></i>
                        <div>
                            <h5 class="fw-bold text-warning mb-1">고유 URL 슬러그가 아직 설정되지 않았습니다!</h5>
                            <p class="text-secondary small mb-0">퍼가기 및 견적 수신을 활성화하려면 먼저 공급사 설정에서 고유 영문 URL 슬러그를 등록해주세요.</p>
                        </div>
                    </div>
                    <a href="/vendor/settings" class="btn btn-warning fw-bold px-4 py-2 rounded-3 text-dark">
                        설정하러 가기 &rarr;
                    </a>
                </div>
            <?php endif; ?>

            <!-- 상단 안내 헤더 -->
            <div class="mb-4">
                <h3 class="fw-bold text-light mb-2">
                    <span style="background: -webkit-linear-gradient(#38bdf8, #818cf8); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                        🚀 2D 스마트 도면 툴 퍼가기 & 연동 센터
                    </span>
                </h3>
                <p class="text-secondary small mb-0">
                    회원님의 자사 홈페이지, 블로그, 인트라넷에 2D 파렛트랙 견적 시스템을 직접 심거나 링크로 연결할 수 있습니다.<br>
                    방문자가 도면을 그리고 견적을 요청하면 회원님의 <strong>[견적요청 수신함]</strong>으로 자동 접수됩니다.
                </p>
            </div>

            <!-- 🎨 [Option A] 테마 및 모드 선택 섹션 -->
            <div class="embed-card p-4 mb-4">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
                    <div>
                        <h5 class="fw-bold text-light mb-1"><i class="fa-solid fa-palette text-info me-2"></i>A. 캔버스 디자인 테마 선택</h5>
                        <p class="text-secondary small mb-0">삽입할 홈페이지의 디자인 분위기(다크/화이트)에 맞게 테마를 선택하세요. 코드가 실시간 자동 변경됩니다.</p>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="theme-selector-btn active" id="btn-theme-dark" onclick="selectTheme('dark')">
                            <i class="fa-solid fa-moon text-info fs-5"></i>
                            <div class="text-start">
                                <div style="font-size:0.9rem;">다크 모드 (Dark)</div>
                                <div style="font-size:0.7rem; color:#64748b;">고급스럽고 모던한 블랙 톤</div>
                            </div>
                        </button>
                        <button type="button" class="theme-selector-btn" id="btn-theme-light" onclick="selectTheme('light')">
                            <i class="fa-solid fa-sun text-warning fs-5"></i>
                            <div class="text-start">
                                <div style="font-size:0.9rem;">화이트 모드 (Light)</div>
                                <div style="font-size:0.7rem; color:#64748b;">깔끔하고 화사한 화이트 톤</div>
                            </div>
                        </button>
                    </div>
                </div>
            </div>

            <!-- 주요 연동 방식 3가지 그리드 -->
            <div class="row g-4 mb-4">

                <!-- 🔗 [Option B] URL 직접 링크 복사 -->
                <div class="col-lg-6">
                    <div class="embed-card p-4 h-100 d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <h5 class="fw-bold text-light mb-0">
                                    <i class="fa-solid fa-link text-primary me-2"></i>B. 단독 URL 주소 복사
                                </h5>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1" style="font-size:0.75rem;">새창 링크 / 버튼용</span>
                            </div>
                            <p class="text-secondary small mb-3">
                                홈페이지의 "견적문의" 버튼이나 메뉴에 링크를 연결할 때 사용합니다. 상단 타이틀과 메뉴얼이 함께 노출되는 독립형 페이지입니다.
                            </p>
                            <div class="code-box mb-3" id="box-direct-url">로딩 중...</div>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" class="copy-btn flex-grow-1" onclick="copyToClipboard('box-direct-url')">
                                <i class="fa-regular fa-copy me-1"></i> URL 링크 복사
                            </button>
                            <a href="#" id="link-direct-preview" target="_blank" class="btn btn-outline-info btn-sm px-3 d-flex align-items-center gap-1" style="border-radius:6px; font-size:0.8rem;">
                                <i class="fa-solid fa-arrow-up-right-from-square"></i> 열어보기
                            </a>
                        </div>
                    </div>
                </div>

                <!-- 💻 [Option C] Iframe 임베드 코드 복사 -->
                <div class="col-lg-6">
                    <div class="embed-card p-4 h-100 d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <h5 class="fw-bold text-light mb-0">
                                    <i class="fa-solid fa-code text-success me-2"></i>C. 아이프레임(iframe) 퍼가기
                                </h5>
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1" style="font-size:0.75rem;">웹사이트 매립용 (추천)</span>
                            </div>
                            <p class="text-secondary small mb-3">
                                홈페이지 내 원하는 페이지에 2D 도면 창을 그대로 쏙 집어넣습니다. <strong>상단 불필요한 헤더는 자동 제거</strong>되어 깔끔하게 매립됩니다.
                            </p>
                            <div class="code-box mb-3" id="box-iframe-code" style="max-height: 120px; overflow-y: auto;">로딩 중...</div>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" class="copy-btn flex-grow-1" style="background: linear-gradient(135deg, #059669, #10b981);" onclick="copyToClipboard('box-iframe-code')">
                                <i class="fa-regular fa-copy me-1"></i> Iframe 태그 복사
                            </button>
                            <button type="button" class="btn btn-outline-success btn-sm px-3 d-flex align-items-center gap-1" style="border-radius:6px; font-size:0.8rem;" onclick="toggleIframePreviewModal()">
                                <i class="fa-solid fa-eye"></i> 미리보기
                            </button>
                        </div>
                    </div>
                </div>

                <!-- ⚡ [Option D] REST API 연동 가이드 -->
                <div class="col-12">
                    <div class="embed-card p-4">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                            <h5 class="fw-bold text-light mb-0">
                                <i class="fa-solid fa-plug text-warning me-2"></i>D. REST API 데이터 직접 연동
                            </h5>
                            <span class="badge bg-warning text-dark fw-bold px-3 py-2" style="font-size:0.8rem; border-radius:8px;">
                                <i class="fa-solid fa-clock-rotate-left me-1"></i> 준비 중 (Coming Soon)
                            </span>
                        </div>
                        <p class="text-secondary small mb-3">
                            자체 ERP, CRM 또는 모바일 앱에서 고객의 2D 도면 배치 데이터(JSON)와 견적 산출 명세를 직접 실시간으로 송수신할 수 있는 RESTful API 엔드포인트가 곧 오픈됩니다!
                        </p>

                        <div class="p-3 rounded-3" style="background: rgba(0,0,0,0.3); border: 1px dashed rgba(255,255,255,0.1);">
                            <div class="d-flex align-items-center gap-3 text-muted small">
                                <div><strong class="text-info">API 엔드포인트 (예정):</strong> <code>POST /api/v1/vendor/quotes/generate</code></div>
                                <div><strong class="text-info">인증 방식:</strong> <code>Bearer Token (API Key)</code></div>
                                <div><strong class="text-info">응답 포맷:</strong> <code>JSON (BOM Parts &amp; Drawing Coordinates)</code></div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </main>

    <!-- 📺 실시간 Iframe 미리보기 모달 -->
    <div class="modal fade" id="previewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered" style="max-width: 95vw; height: 90vh;">
            <div class="modal-content text-bg-dark h-100 border-secondary" style="border-radius: 16px; overflow: hidden;">
                <div class="modal-header border-secondary py-2 px-3">
                    <h6 class="modal-title text-info fw-bold mb-0">
                        <i class="fa-solid fa-laptop-code me-2"></i>홈페이지 삽입 시 실제 화면 미리보기
                    </h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0" style="height: calc(100% - 50px); background: #000;">
                    <iframe id="previewIframe" src="" style="width: 100%; height: 100%; border: none;"></iframe>
                </div>
            </div>
        </div>
    </div>

    <!-- 📋 복사 완료 토스트 알림 -->
    <div id="copyToast" class="copy-toast">
        <i class="fa-solid fa-circle-check fs-5"></i>
        <span>클립보드에 깔끔하게 복사되었습니다!</span>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        const baseUrl = <?= json_encode($domainName) ?>;
        const slug = <?= json_encode($slug) ?>;
        let selectedTheme = 'dark';

        function updateCodeSnippets() {
            if (!slug) {
                document.getElementById('box-direct-url').innerText = '공급사 설정에서 URL 슬러그를 먼저 등록해주세요.';
                document.getElementById('box-iframe-code').innerText = '공급사 설정에서 URL 슬러그를 먼저 등록해주세요.';
                return;
            }

            // 1. 단독 URL 생성
            const themeParam = selectedTheme === 'light' ? '?theme=light' : '';
            const directUrl = `${baseUrl}/quote/${slug}${themeParam}`;
            document.getElementById('box-direct-url').innerText = directUrl;
            document.getElementById('link-direct-preview').href = directUrl;

            // 2. Iframe 코드 생성 (상단 헤더 숨김 embed=1 추가)
            const iframeThemeParam = selectedTheme === 'light' ? '&theme=light' : '';
            const iframeSrc = `${baseUrl}/quote/${slug}?embed=1${iframeThemeParam}`;
            const iframeHtml = `<!-- 파렛트랙 스마트 견적 2D 도면 위젯 -->
<div style="width: 100%; max-width: 1800px; margin: 0 auto; border-radius: 12px; overflow: hidden;">
    <iframe src="${iframeSrc}" width="100%" height="860" frameborder="0" allowfullscreen style="display: block; border: none;"></iframe>
</div>`;
            document.getElementById('box-iframe-code').innerText = iframeHtml;
        }

        function selectTheme(theme) {
            selectedTheme = theme;
            document.getElementById('btn-theme-dark').classList.toggle('active', theme === 'dark');
            document.getElementById('btn-theme-light').classList.toggle('active', theme === 'light');
            updateCodeSnippets();
        }

        function copyToClipboard(elementId) {
            const text = document.getElementById(elementId).innerText;
            if (!text || text.includes('공급사 설정에서')) return;

            navigator.clipboard.writeText(text).then(() => {
                showToast();
            }).catch(err => {
                // 구형 브라우저 폴백
                const textarea = document.createElement('textarea');
                textarea.value = text;
                document.body.appendChild(textarea);
                textarea.select();
                document.execCommand('copy');
                document.body.removeChild(textarea);
                showToast();
            });
        }

        function showToast() {
            const toast = document.getElementById('copyToast');
            toast.style.display = 'flex';
            setTimeout(() => {
                toast.style.display = 'none';
            }, 2500);
        }

        function toggleIframePreviewModal() {
            if (!slug) return;
            const iframeThemeParam = selectedTheme === 'light' ? '&theme=light' : '';
            const iframeSrc = `${baseUrl}/quote/${slug}?embed=1${iframeThemeParam}`;
            document.getElementById('previewIframe').src = iframeSrc;
            const modal = new bootstrap.Modal(document.getElementById('previewModal'));
            modal.show();
        }

        // 초기 실행
        document.addEventListener('DOMContentLoaded', () => {
            updateCodeSnippets();
        });
    </script>
</body>
</html>
