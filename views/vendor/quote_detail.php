<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>공급사 관리 센터 - 견적 요청 상세</title>
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
            --accent-amber: #fbbf24;
            --accent-glow: rgba(251, 191, 36, 0.35);
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
            color: #a855f7;
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
            background: linear-gradient(135deg, rgba(251, 191, 36, 0.12) 0%, rgba(217, 119, 6, 0.12) 100%);
            border: 1px solid rgba(251, 191, 36, 0.4);
            color: #fde047;
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
                SaaS Dashboard &gt; 견적 요청 상세 보기
            </div>
            <div class="d-flex align-items-center gap-3">
                <a href="/vendor/quotes" class="btn btn-outline-secondary btn-sm rounded px-3" style="font-size:0.8rem; border-color: rgba(255,255,255,0.15); color:#cbd5e1;">
                    ◀ 목록으로 돌아가기
                </a>
                <div class="user-profile d-flex align-items-center gap-2">
                    <i class="fa-solid fa-circle-user text-info fs-5"></i>
                    <span class="small font-monospace text-light"><?= htmlspecialchars($_SESSION['user']['username'] ?? 'User') ?>님</span>
                </div>
            </div>
        </div>

        <div class="content-body">
            <div class="row g-4">
                <!-- 1. 의뢰고객 인적사항 & CAD 도면 시각화 (좌측) -->
                <div class="col-lg-6">
                    <!-- 인적사항 -->
                    <div class="glass-panel p-4 mb-4">
                        <h5 class="fw-bold text-info mb-3 d-flex align-items-center gap-2 pb-2 border-bottom border-secondary">
                            <span>👤</span> 의뢰 고객 정보
                        </h5>
                        <div class="row g-3">
                            <div class="col-6">
                                <span class="text-light opacity-75 small d-block">회사명</span>
                                <span class="text-light fw-bold fs-5"><?= htmlspecialchars($quote['company']) ?></span>
                            </div>
                            <div class="col-6">
                                <span class="text-light opacity-75 small d-block">담당자</span>
                                <span class="text-light fw-bold fs-5"><?= htmlspecialchars($quote['name']) ?></span>
                            </div>
                            <div class="col-6">
                                <span class="text-light opacity-75 small d-block">연락처</span>
                                <a href="tel:<?= htmlspecialchars($quote['phone']) ?>" class="text-info fw-semibold font-monospace fs-5"><?= htmlspecialchars($quote['phone']) ?></a>
                            </div>
                            <div class="col-6">
                                <span class="text-light opacity-75 small d-block">접수일시</span>
                                <span class="text-light font-monospace fs-6"><?= date('Y-m-d H:i:s', strtotime($quote['created_at'])) ?></span>
                            </div>
                            <div class="col-12">
                                <span class="text-light opacity-75 small d-block">시공 현장 주소</span>
                                <span class="text-light fw-semibold"><?= htmlspecialchars($quote['address']) ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- CAD 도면 시각화 캔버스 -->
                    <div class="glass-panel p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom border-secondary">
                            <h5 class="m-0 fw-bold text-success d-flex align-items-center gap-2">
                                <span>📐</span> 배치 설계 도면 프리뷰
                            </h5>
                            <span class="badge bg-secondary font-monospace" style="font-size: 0.65rem;">Read-Only CAD</span>
                        </div>
                        <div class="text-center">
                            <canvas id="quoteCanvas" width="800" height="500" style="
                                background: #090d16;
                                border: 1px solid rgba(255,255,255,0.08);
                                border-radius: 12px;
                                width: 100%;
                                height: auto;
                            "></canvas>
                            <small class="text-info d-block mt-2">💡 고객이 배치 설계를 완료하고 전송한 시점의 실시간 도면 스냅샷입니다.</small>
                        </div>
                    </div>
                </div>

                <!-- 2. AI 분석 설계 리포트 전문 (우측) -->
                <div class="col-lg-6">
                    <div class="glass-panel p-4 mb-4 d-flex flex-column" style="max-height: 400px;">
                        <h5 class="fw-bold text-warning mb-3 pb-2 border-bottom border-secondary d-flex align-items-center gap-2">
                            <span>💡</span> AI 설계 요약 및 시공 리포트
                        </h5>
                        <div class="flex-grow-1" style="line-height: 1.8; font-size: 0.88rem; overflow-y: auto; white-space: pre-line; word-break: keep-all; color: #e2e8f0;">
                            <?php if (empty($quote['summary'])): ?>
                                <p class="text-muted">요청된 AI 리포트 본문이 비어있습니다.</p>
                            <?php else: ?>
                                <?= htmlspecialchars($quote['summary']) ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- 3. 관리자 견적 승인 및 이메일 전송 폼 -->
                    <div class="glass-panel p-4" style="background: rgba(15, 23, 42, 0.7); border: 1px solid rgba(251, 191, 36, 0.2);">
                        <h5 class="fw-bold text-amber mb-3 d-flex align-items-center gap-2 pb-2 border-bottom border-secondary" style="color: #f59e0b;">
                            <span>💰</span> 최종 견적 산출 및 고객 이메일 발송
                        </h5>
                        <?php if(isset($quote['status']) && $quote['status'] === 'completed'): ?>
                            <div class="alert alert-success bg-transparent border-success text-success d-flex align-items-center gap-2 p-3">
                                <i class="fa-solid fa-circle-check fs-4"></i>
                                <div>
                                    <strong>이미 전송이 완료된 견적서입니다.</strong><br>
                                    최종 단가: <?= number_format($quote['admin_price'] ?? 0) ?> 원 / 마진율: <?= floatval($quote['admin_margin'] ?? 0) ?>%
                                </div>
                            </div>
                        <?php endif; ?>
                        <form id="admin-quote-form" onsubmit="sendAdminQuote(event)">
                            <input type="hidden" id="quote_id" value="<?= $quote['id'] ?>">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label text-muted small mb-1">총 견적 단가 (원)</label>
                                    <input type="number" class="form-control bg-transparent text-white border-secondary fw-bold text-end" id="admin_price" value="<?= $quote['admin_price'] ?? '' ?>" placeholder="0" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted small mb-1">마진율 (%)</label>
                                    <input type="number" step="0.1" class="form-control bg-transparent text-white border-secondary text-end" id="admin_margin" value="<?= $quote['admin_margin'] ?? '' ?>" placeholder="0.0">
                                </div>
                                <div class="col-12">
                                    <label class="form-label text-muted small mb-1">고객 전달 코멘트</label>
                                    <textarea class="form-control bg-transparent text-white border-secondary" id="admin_notes" rows="3" placeholder="예: 물류비 포함 최종 견적입니다."><?= htmlspecialchars($quote['admin_notes'] ?? '') ?></textarea>
                                </div>
                                <div class="col-12 mt-4">
                                    <button type="submit" id="btn-send-quote" class="btn btn-warning w-100 py-3 fw-bold shadow-lg" style="color: #451a03; font-size:1.1rem; border-radius: 12px;">
                                        <i class="fa-solid fa-paper-plane me-2"></i> 최종 견적서 이메일 전송 🚀
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </main>

    <!-- 📐 CAD 시각화 렌더러 스크립트 -->
    <script>
    window.addEventListener('DOMContentLoaded', () => {
        const rawData = <?= json_encode($quote['canvas_data']) ?>;
        if (!rawData) return;
        
        let data;
        try {
            data = typeof rawData === 'string' ? JSON.parse(rawData) : rawData;
        } catch(e) {
            console.error("Failed to parse canvas JSON:", e);
            return;
        }

        const canvas = document.getElementById('quoteCanvas');
        const ctx = canvas.getContext('2d');

        const points = data.points || [];
        const racks = data.racks || [];
        const obstacles = data.obstacles || [];
        const scale = data.currentScale || 0.12;

        if (points.length === 0) {
            ctx.fillStyle = '#475569';
            ctx.font = '14px Arial';
            ctx.fillText("표시할 도면 좌표가 없습니다.", 50, 50);
            return;
        }

        // 1. 다각형 외곽 영역을 캔버스 중심에 맞추기 위한 바운딩 구하기
        let minX = Infinity, maxX = -Infinity, minY = Infinity, maxY = -Infinity;
        points.forEach(p => {
            if (p.x < minX) minX = p.x;
            if (p.x > maxX) maxX = p.x;
            if (p.y < minY) minY = p.y;
            if (p.y > maxY) maxY = p.y;
        });

        const centerX = (minX + maxX) / 2;
        const centerY = (minY + maxY) / 2;

        // 스케일 보정 (캔버스 크기에 맞춰 비율 산출)
        const boundsWidth = maxX - minX;
        const boundsHeight = maxY - minY;
        const margin = 60;
        const scaleX = (canvas.width - margin * 2) / boundsWidth;
        const scaleY = (canvas.height - margin * 2) / boundsHeight;
        const finalZoom = Math.min(scaleX, scaleY, 1);

        ctx.save();
        // 캔버스 중심 정렬
        ctx.translate(canvas.width / 2, canvas.height / 2);
        ctx.scale(finalZoom, finalZoom);
        ctx.translate(-centerX, -centerY);

        // 2. 창고 외곽 벽면 그리기
        ctx.strokeStyle = '#475569';
        ctx.lineWidth = 6 / finalZoom;
        ctx.fillStyle = 'rgba(30, 58, 138, 0.15)';
        ctx.beginPath();
        ctx.moveTo(points[0].x, points[0].y);
        for (let i = 1; i < points.length; i++) {
            ctx.lineTo(points[i].x, points[i].y);
        }
        ctx.closePath();
        ctx.fill();
        ctx.stroke();

        // 3. 장애물(기둥 등) 그리기
        obstacles.forEach(obs => {
            ctx.fillStyle = 'rgba(239, 68, 68, 0.3)';
            ctx.strokeStyle = '#ef4444';
            ctx.lineWidth = 2 / finalZoom;
            const w = (obs.width || 500) * scale;
            const h = (obs.height || 500) * scale;
            ctx.fillRect(obs.x - w / 2, obs.y - h / 2, w, h);
            ctx.strokeRect(obs.x - w / 2, obs.y - h / 2, w, h);
        });

        // 4. 파렛트랙 그룹 그리기
        racks.forEach(r => {
            ctx.save();
            ctx.translate(r.x, r.y);

            // 회전 각도 계산
            let angle = r.angle || 0;
            if (r.angle === undefined) {
                if (r.isHoriz) {
                    angle = r.dir < 0 ? Math.PI : 0;
                } else {
                    angle = r.dir < 0 ? -Math.PI / 2 : Math.PI / 2;
                }
            }
            ctx.rotate(angle);

            const singleDepth = r.rackDepth || 1000;
            const holderSize = r.holderSize || 200;
            const isDouble = r.isDouble || false;
            
            const singleDepthPx = singleDepth * scale;
            const holderPx = (isDouble ? holderSize : 0) * scale;
            const depthPx = isDouble ? (singleDepthPx * 2 + holderPx) : singleDepthPx;

            ctx.fillStyle = 'rgba(15, 23, 42, 0.85)';
            ctx.strokeStyle = '#0ea5e9';
            ctx.lineWidth = 3 / finalZoom;
            
            if (isDouble) {
                const topRackY = -depthPx / 2;
                const botRackY = depthPx / 2 - singleDepthPx;
                ctx.fillRect(0, topRackY, r.totalLengthPx, singleDepthPx);
                ctx.strokeRect(0, topRackY, r.totalLengthPx, singleDepthPx);
                ctx.fillRect(0, botRackY, r.totalLengthPx, singleDepthPx);
                ctx.strokeRect(0, botRackY, r.totalLengthPx, singleDepthPx);
            } else {
                ctx.fillRect(0, -depthPx / 2, r.totalLengthPx, depthPx);
                ctx.strokeRect(0, -depthPx / 2, r.totalLengthPx, depthPx);
            }

            ctx.restore();
        });

        ctx.restore();
    });

    async function sendAdminQuote(e) {
        e.preventDefault();
        const btn = document.getElementById('btn-send-quote');
        const quoteId = document.getElementById('quote_id').value;
        const price = document.getElementById('admin_price').value;
        const margin = document.getElementById('admin_margin').value;
        const notes = document.getElementById('admin_notes').value;

        if(!confirm('고객에게 견적서를 최종 전송하시겠습니까?')) return;

        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i> 전송 중...';

        const formData = new FormData();
        formData.append('quote_id', quoteId);
        formData.append('admin_price', price);
        formData.append('admin_margin', margin);
        formData.append('admin_notes', notes);

        try {
            const res = await fetch('/vendor/quotes/send-email', {
                method: 'POST',
                body: formData
            });
            const result = await res.json();
            if (result.success) {
                alert(result.message);
                location.reload();
            } else {
                alert('오류: ' + result.message);
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-paper-plane me-2"></i> 최종 견적서 이메일 전송 🚀';
            }
        } catch(err) {
            alert('서버 에러: ' + err.message);
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-paper-plane me-2"></i> 최종 견적서 이메일 전송 🚀';
        }
    }
    </script>
    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
