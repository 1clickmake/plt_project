<?php
// Layout Variables
$pageTitle = "견적 바로가기";
$currentUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CMAKE SAAS - <?= $pageTitle ?></title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #0f172a;
            color: #f8fafc;
            font-family: 'Noto Sans KR', sans-serif;
            display: flex;
            height: 100vh;
            margin: 0;
            overflow: hidden;
        }

        /* Sidebar Base Styles */
        .sidebar {
            width: 260px;
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(20px);
            border-right: 1px solid rgba(255,255,255,0.08);
            display: flex;
            flex-direction: column;
            padding: 1.5rem 0;
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .sidebar-brand {
            padding: 0 1.5rem 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.05);
            font-size: 1.25rem;
            font-weight: 700;
            color: #fff;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
            letter-spacing: 1px;
        }

        .sidebar-menu {
            flex: 1;
            overflow-y: auto;
            padding: 1rem 0;
        }

        .menu-item {
            padding: 0.8rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 12px;
            color: #94a3b8;
            text-decoration: none;
            transition: all 0.2s;
            position: relative;
            font-weight: 500;
            margin: 0.2rem 1rem;
            border-radius: 8px;
        }

        .menu-item:hover, .menu-item.active {
            color: #fff;
            background: rgba(255,255,255,0.05);
        }

        .menu-item.active {
            background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%);
            box-shadow: 0 4px 15px rgba(79, 70, 229, 0.3);
        }

        .menu-item i {
            width: 20px;
            text-align: center;
            font-size: 1.1rem;
        }

        .main-content {
            flex: 1;
            padding: 2rem;
            overflow-y: auto;
            background: radial-gradient(circle at top right, #1e293b 0%, #0f172a 100%);
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }

        .page-title {
            font-size: 1.75rem;
            font-weight: 700;
            margin: 0;
            background: linear-gradient(135deg, #fff 0%, #94a3b8 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 60vh;
            text-align: center;
            color: #94a3b8;
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .empty-state h3 {
            font-weight: 600;
            color: #fff;
            margin-bottom: 0.5rem;
        }

        .empty-state p {
            max-width: 400px;
            line-height: 1.6;
        }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.2); }
    </style>
</head>
<body>

    <?php require __DIR__ . '/sidebar.php'; ?>

    <main class="main-content">
        <div class="page-header">
            <h1 class="page-title"><i class="fa-solid fa-chart-pie me-2 text-indigo-400"></i> 대시보드</h1>
        </div>
        
        <div class="row g-4 mb-4">
            <!-- 금일 접수 건수 위젯 -->
            <div class="col-md-4">
                <div class="card bg-dark bg-opacity-50 border-secondary h-100 shadow-sm" style="border-radius: 15px;">
                    <div class="card-body p-4 d-flex align-items-center">
                        <div class="rounded-circle bg-primary bg-opacity-25 d-flex align-items-center justify-content-center me-4" style="width: 60px; height: 60px;">
                            <i class="fa-solid fa-inbox fs-3 text-primary"></i>
                        </div>
                        <div>
                            <h6 class="text-secondary mb-1">금일 신규 견적 접수</h6>
                            <h2 class="text-white mb-0 fw-bold"><?= number_format($todayQuotesCount ?? 0) ?> <span class="fs-6 text-muted fw-normal">건</span></h2>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 금일 메일 발송 횟수 위젯 -->
            <div class="col-md-4">
                <div class="card bg-dark bg-opacity-50 border-secondary h-100 shadow-sm" style="border-radius: 15px;">
                    <div class="card-body p-4 d-flex align-items-center">
                        <div class="rounded-circle bg-info bg-opacity-25 d-flex align-items-center justify-content-center me-4" style="width: 60px; height: 60px;">
                            <i class="fa-solid fa-envelope-circle-check fs-3 text-info"></i>
                        </div>
                        <div>
                            <h6 class="text-secondary mb-1">금일 메일 발송</h6>
                            <h2 class="text-white mb-0 fw-bold"><?= number_format($todayMailedCount ?? 0) ?> <span class="fs-6 text-muted fw-normal">건</span></h2>
                            <div class="fs-8 text-secondary mt-1">오늘 0시 기준</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 발송 한도 위젯 -->
            <?php
                $plan      = $balanceInfo['plan'] ?? 'free';
                $isPro     = ($plan === 'pro');
                $isStarter = ($plan === 'starter');
                $isLow     = !$isPro && isset($balanceInfo) && $balanceInfo['remaining'] <= 0;
                $planLabel = ['free' => ['FREE', 'secondary'], 'starter' => ['STARTER', 'info'], 'pro' => ['PRO', 'warning']];
                [$planText, $planColor] = $planLabel[$plan] ?? ['FREE', 'secondary'];
            ?>
            <div class="col-md-4">
                <div class="card bg-dark bg-opacity-50 border-secondary h-100 shadow-sm" style="border-radius: 15px; <?= $isLow ? 'border: 1px solid #dc3545 !important;' : '' ?>">
                    <div class="card-body p-4 d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-success bg-opacity-25 d-flex align-items-center justify-content-center me-4" style="width: 60px; height: 60px; <?= $isLow ? 'background-color: rgba(220,53,69,0.25) !important;' : '' ?>">
                                <i class="fa-regular fa-paper-plane fs-3 <?= $isLow ? 'text-danger' : ($isPro ? 'text-warning' : 'text-success') ?>"></i>
                            </div>
                            <div>
                                <h6 class="text-secondary mb-1">
                                    메일 발송 가능 횟수
                                    <span class="badge bg-<?= $planColor ?> ms-2" style="font-size:0.65rem;"><?= $planText ?></span>
                                </h6>
                                <h2 class="text-white mb-0 fw-bold">
                                    <?php if ($isPro): ?>
                                        <span class="text-warning">무제한</span>
                                    <?php else: ?>
                                        <?= number_format($balanceInfo['remaining'] ?? 0) ?> <span class="fs-6 text-muted fw-normal">건 남음</span>
                                    <?php endif; ?>
                                </h2>
                                <?php if (!$isPro): ?>
                                    <div class="fs-8 text-secondary mt-1">
                                        기본 <?= number_format(max(0, $balanceInfo['total_limit'] - $balanceInfo['used'])) ?>건 + 이월 <?= number_format($balanceInfo['addon_balance'] ?? 0) ?>건
                                    </div>
                                <?php else: ?>
                                    <div class="fs-8 text-warning mt-1">PRO 플랜 · 발송 한도 없음</div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php if (!$isPro): ?>
                        <div>
                            <a href="/vendor/addon_payment" class="btn btn-outline-warning btn-sm fw-bold px-3 py-2" style="border-radius: 10px;">
                                <i class="fa-solid fa-bolt"></i> 횟수 충전
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

    </main>

</body>
</html>
