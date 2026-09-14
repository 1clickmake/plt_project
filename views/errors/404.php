<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - 페이지를 찾을 수 없습니다 | 파렛트랙 자동 견적 SaaS</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700;800&family=Noto+Sans+KR:wght@300;400;500;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Outfit', 'Noto Sans KR', sans-serif;
            background: radial-gradient(circle at top center, #1e293b 0%, #0f172a 100%);
            color: #f8fafc;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
        }
        .error-card {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            padding: 48px 40px;
            max-width: 540px;
            width: 100%;
            text-align: center;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5), 0 0 30px rgba(253, 224, 71, 0.05);
        }
        .error-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 16px;
            border-radius: 100px;
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #f87171;
            font-weight: 700;
            font-size: 0.85rem;
            margin-bottom: 24px;
        }
        .error-code {
            font-size: 5rem;
            font-weight: 800;
            line-height: 1;
            background: linear-gradient(135deg, #fde047 0%, #f59e0b 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 16px;
            letter-spacing: -2px;
        }
        .error-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 12px;
        }
        .error-desc {
            color: #94a3b8;
            font-size: 0.95rem;
            line-height: 1.6;
            margin-bottom: 32px;
        }
        .btn-custom {
            padding: 12px 28px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }
        .btn-home {
            background: #fde047;
            color: #0f172a;
            border: none;
        }
        .btn-home:hover {
            background: #fef08a;
            color: #000;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px -5px rgba(253, 224, 71, 0.3);
        }
        .btn-back {
            background: rgba(255, 255, 255, 0.05);
            color: #cbd5e1;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .btn-back:hover {
            background: rgba(255, 255, 255, 0.1);
            color: #ffffff;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="error-badge">
            <i class="fa-solid fa-triangle-exclamation"></i> <?= htmlspecialchars($errorType ?? '404 NOT FOUND') ?>
        </div>
        <div class="error-code">404</div>
        <h1 class="error-title"><?= htmlspecialchars($errorMessage ?? '요청하신 페이지를 찾을 수 없습니다') ?></h1>
        <p class="error-desc">
            주소가 잘못 입력되었거나, 삭제 또는 이동된 페이지입니다.<br>
            공급사 견적 URL이나 게시판 주소를 다시 확인해 주세요.
        </p>
        <div class="d-flex justify-content-center gap-3">
            <button onclick="window.history.back()" class="btn-custom btn-back">
                <i class="fa-solid fa-arrow-left"></i> 이전 페이지
            </button>
            <a href="/" class="btn-custom btn-home">
                <i class="fa-solid fa-house"></i> 홈으로 이동
            </a>
        </div>
    </div>
</body>
</html>
