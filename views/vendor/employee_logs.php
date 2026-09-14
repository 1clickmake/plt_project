<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>공급사 관리 센터 - 직원 접속 로그</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- External Vendor Dashboard CSS -->
    <link href="/css/vendor_dashboard.css" rel="stylesheet">

    <style>
        .employee-info-card {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 12px;
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .emp-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: #fff;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3);
        }

        .emp-details h4 {
            margin: 0 0 0.25rem 0;
            font-size: 1.25rem;
            font-weight: 600;
            color: #f8fafc;
        }
        
        .emp-details p {
            margin: 0;
            color: #94a3b8;
            font-size: 0.9rem;
        }

        .table-dark-glass {
            color: #e2e8f0;
        }

        .table-dark-glass th {
            background: rgba(0, 0, 0, 0.2);
            color: #94a3b8;
            font-weight: 600;
            font-size: 0.85rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            padding: 1rem;
        }

        .table-dark-glass td {
            background: transparent;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            padding: 1rem;
            vertical-align: middle;
        }

        .log-time {
            font-weight: 500;
            color: #38bdf8;
        }

        .log-meta {
            font-family: monospace;
            color: #94a3b8;
            font-size: 0.85rem;
            background: rgba(0,0,0,0.3);
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
        }

        .empty-state {
            text-align: center;
            padding: 3rem 2rem;
            color: #94a3b8;
        }
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
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
                SaaS Dashboard &gt; 직원 관리 &gt; 접속 로그
            </div>
<div class="user-profile d-flex align-items-center gap-2">
                <?php
                    $dbBtn = \App\Core\Database::getInstance();
                    $stmtBtn = $dbBtn->prepare("SELECT plan FROM users WHERE user_id = ?");
                    $stmtBtn->execute([$user['user_id']]);
                    $btnPlan = $stmtBtn->fetchColumn();
                    if ($btnPlan !== 'pro'):
                ?>
                <!-- <a href="/vendor/addon_payment" class="btn btn-outline-warning btn-sm fw-bold px-3 py-1 me-3" style="border-radius: 10px;">
                    <i class="fa-solid fa-bolt"></i> 횟수 충전
                </a> -->
                <?php endif; ?>
                <i class="fa-solid fa-circle-user text-info fs-5"></i>
                <span class="small font-monospace text-light"><?= htmlspecialchars($user['username'] ?? 'User') ?>님</span>
            </div>
        </div>

        <div class="content-body">
            <div class="glass-panel p-0">
                <div class="p-4 border-bottom border-secondary d-flex justify-content-between align-items-center" style="background: rgba(255,255,255,0.01); border-radius: 16px 16px 0 0;">
                    <h4 class="m-0 fw-bold d-flex align-items-center gap-2" style="color: #38bdf8;">
                        <i class="fa-solid fa-clock-rotate-left"></i> 접속 로그 (출근부)
                    </h4>
                    <a href="/vendor/employees" class="btn btn-sm btn-outline-light rounded-pill px-3">
                        <i class="fa-solid fa-arrow-left me-1"></i> 목록으로
                    </a>
                </div>
                
                <div class="p-4">
                    <div class="employee-info-card">
                        <div class="emp-avatar" style="background-color: <?= htmlspecialchars($employee['color_code']) ?>;">
                            <i class="fa-solid fa-user-tie"></i>
                        </div>
                        <div class="emp-details">
                            <h4><?= htmlspecialchars($employee['name']) ?> <span class="badge bg-secondary ms-2" style="font-size: 0.75rem; vertical-align: middle;"><?= htmlspecialchars($employee['title'] ?: '직책 없음') ?></span></h4>
                            <p><i class="fa-solid fa-phone me-2"></i><?= htmlspecialchars($employee['phone'] ?: '연락처 없음') ?></p>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-borderless table-dark-glass">
                            <thead>
                                <tr>
                                    <th width="5%">No</th>
                                    <th width="20%">접속 일자</th>
                                    <th width="20%">접속 시간</th>
                                    <th width="25%">IP 주소</th>
                                    <th width="30%">접속 기기 (User Agent)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($logs)): ?>
                                    <tr>
                                        <td colspan="5">
                                            <div class="empty-state">
                                                <i class="fa-solid fa-ghost"></i>
                                                <h5>아직 접속 기록이 없습니다</h5>
                                                <p>이 직원이 프로필을 전환하여 접속하면 기록이 생성됩니다.</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($logs as $index => $log): 
                                        $dt = new DateTime($log['login_time']);
                                    ?>
                                        <tr>
                                            <td class="text-light opacity-75"><?= count($logs) - $index ?></td>
                                            <td class="fw-bold text-white"><?= $dt->format('Y년 m월 d일') ?></td>
                                            <td class="log-time" style="color: #7dd3fc; font-weight: 600;"><?= $dt->format('H:i:s') ?></td>
                                            <td>
                                                <?php if (!empty($log['ip_address'])): ?>
                                                    <span class="log-meta" style="color: #f8fafc; background: rgba(255,255,255,0.1);"><?= htmlspecialchars($log['ip_address']) ?></span>
                                                <?php else: ?>
                                                    <span class="text-light opacity-50 small">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($log['user_agent'])): ?>
                                                    <span class="text-light opacity-75 small" title="<?= htmlspecialchars($log['user_agent']) ?>">
                                                        <?= htmlspecialchars(mb_strimwidth($log['user_agent'], 0, 50, '...')) ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-light opacity-50 small">-</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
