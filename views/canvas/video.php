<!DOCTYPE html>
<html lang="ko" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>파렛트랙 자동 견적 시스템 - 메뉴얼 영상</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: #f8fafc;
            height: 100vh;
            overflow: hidden;
        }
        .glass-panel {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 1rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }
        .canvas-container {
            background: #000;
            border: 1px dashed #334155;
            border-radius: 0.5rem;
            min-height: 500px;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        video {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
    </style>
</head>
<body>
<div class="container-fluid pt-3 px-4 d-flex flex-column h-100">
    <div class="text-center mb-3 flex-shrink-0 position-relative">
        <?php if (!empty($vendor)): ?>
            <h4 class="fw-bold text-light mb-1"><?= htmlspecialchars($vendor['company_name']) ?></h4>
        <?php endif; ?>
        <h2 class="fw-bold" style="background: -webkit-linear-gradient(#38bdf8, #818cf8); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
            사용 매뉴얼 영상
        </h2>
        <p class="text-muted small m-0">스마트 창고 배치 견적 시스템 사용 방법을 영상으로 확인하세요.</p>
    </div>

    <div class="row g-4 flex-grow-1" style="min-height: 0;">
        <!-- 왼쪽: 빈 공간 -->
        <div class="col-xl-3 col-lg-4 h-100">
            <div class="glass-panel p-4 h-100 d-flex flex-column position-relative">
                <div class="mb-auto">
                    <a href="javascript:void(0);" onclick="if(document.referrer) { history.back(); } else { location.href='/quote/<?= htmlspecialchars($vendor['url_slug'] ?? 'asamiya') ?>'; }" class="btn btn-outline-secondary w-100"><i class="fas fa-arrow-left me-2"></i> 도면으로 돌아가기</a>
                </div>
                <div class="text-center mt-auto mb-auto">
                    <i class="fas fa-video fa-3x mb-3 text-muted"></i>
                    <h5 class="text-light mt-3">영상 시청 안내</h5>
                    <p class="text-muted small mt-2">우측 화면에서 플레이 버튼을 눌러 재생하세요. 전체 화면으로도 시청 가능합니다.</p>
                </div>
            </div>
        </div>
        
        <!-- 오른쪽: 비디오 영역 -->
        <div class="col-xl-9 col-lg-8 h-100 d-flex flex-column">
            <div class="glass-panel p-3 h-100 d-flex flex-column">
                <div class="canvas-container flex-grow-1">
                    <video controls autoplay>
                        <source src="/assets/rack설명.mp4" type="video/mp4">
                        브라우저가 동영상을 지원하지 않습니다.
                    </video>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
