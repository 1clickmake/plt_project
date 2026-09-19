<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="google-site-verification" content="U8T-jVznB6hBqYLpVXh6N1cHvigp88gurGVoqTXPxSA" />
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="캐드(CAD) 없이 5분 만에 파렛트랙 도면 설계와 견적을 완성하세요. 중소형 창고에 최적화된 랙 원클릭 자동 배치 및 단가 계산 자동화 솔루션, 씨메이크(cmake)">
    <meta name="keywords" content="파렛트랙, 파레트랙, 물류창고 랙, 랙 설계, 자동 견적, 씨메이크, cmake, 랙 도면, 랙 CAD, 창고 도면">
    
    <!-- Open Graph (카카오톡, 페이스북 등 공유용) -->
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="씨메이크(cmake)">
    <meta property="og:title" content="씨메이크(cmake) | AI 파렛트랙 설계 & 견적 자동화">
    <meta property="og:description" content="캐드(CAD) 없이 5분 만에 파렛트랙 도면 설계와 견적을 완성하세요. 중소형 창고에 최적화된 AI 원클릭 배치 솔루션.">
    <meta property="og:url" content="https://cmake.work">
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="씨메이크(cmake) | AI 파렛트랙 설계 & 견적 자동화">
    <meta name="twitter:description" content="캐드(CAD) 없이 5분 만에 파렛트랙 도면 설계와 견적을 완성하세요.">
    <title><?= htmlspecialchars($siteConfig['site_name'] ?? 'Neuron AI PHP') ?> - <?= $title ?? 'Welcome' ?></title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-4.0.0-beta.min.js"></script>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/css/latest.css" rel="stylesheet"> <!-- 최신글 위젯 스타일 -->
    <link href="/css/formmail.css" rel="stylesheet"> <!-- 폼메일 위젯 스타일 -->
    <link href="/css/style.css" rel="stylesheet"> <!-- 통합 스타일 -->

	<!-- 템플릿 전용 CSS 동적 로드 -->
    <?= load_template_assets($siteConfig ?? []) ?>
    <?php do_action('public_head'); ?>
</head>
<body>
