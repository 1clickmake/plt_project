<?php
require_once CM_PATH . '/config/config.php';
require_once CM_PATH . '/app/Core/Database.php';

use App\Core\Database;

// 로그인 체크는 생략 (실제로는 해야함)
$userId = $user['id'] ?? 1; // 임시: 1번 유저라고 가정

$db = Database::getInstance();
if ($db) {
    // 1. 현재 구독 정보 가져오기
    $subStmt = $db->prepare("SELECT * FROM payment_subscriptions WHERE user_id = ? ORDER BY id DESC LIMIT 1");
    $subStmt->execute([$userId]);
    $subscription = $subStmt->fetch();

    // 페이징 처리
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    if ($page < 1) $page = 1;
    $limit = 10;
    $offset = ($page - 1) * $limit;

    $totalStmt = $db->prepare("SELECT COUNT(*) FROM payment_logs WHERE user_id = ?");
    $totalStmt->execute([$userId]);
    $totalItems = $totalStmt->fetchColumn();
    $totalPages = ceil($totalItems / $limit);

    // 2. 결제 내역(영수증) 가져오기
    $logStmt = $db->prepare("SELECT * FROM payment_logs WHERE user_id = ? ORDER BY created_at DESC LIMIT $limit OFFSET $offset");
    $logStmt->execute([$userId]);
    $paymentLogs = $logStmt->fetchAll();
} else {
    $subscription = null;
    $paymentLogs = [];
}
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>공급사 관리 센터 - 결제 및 구독 관리</title>
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
    <?php include __DIR__ . '/sidebar.php'; ?>

    <!-- 💻 우측 메인 대시보드 영역 -->
    <main class="main-content">
        <div class="top-navbar">
            <div class="navbar-title fw-bold text-light" style="font-size: 1.1rem;">
                SaaS Dashboard &gt; 결제 및 구독 관리
            </div>
<div class="user-profile d-flex align-items-center gap-2">
                <?php
                    $dbBtn = \App\Core\Database::getInstance();
                    $stmtBtn = $dbBtn->prepare("SELECT plan FROM users WHERE user_id = ?");
                    $stmtBtn->execute([$user['user_id']]);
                    $btnPlan = $stmtBtn->fetchColumn();
                    if ($btnPlan !== 'pro'):
                ?>
                <a href="/vendor/addon_payment" class="btn btn-outline-warning btn-sm fw-bold px-3 py-1 me-3" style="border-radius: 10px;">
                    <i class="fa-solid fa-bolt"></i> 횟수 충전
                </a>
                <?php endif; ?>
                <i class="fa-solid fa-circle-user text-info fs-5"></i>
                <span class="small font-monospace text-light"><?= htmlspecialchars($user['username'] ?? 'User') ?>님</span>
            </div>
        </div>

        <div class="content-body">
            <h3 class="mb-4 fw-bold text-light border-bottom border-secondary pb-2" style="font-size: 1.5rem;">
                <i class="fa-solid fa-credit-card text-success me-2"></i> 결제 및 구독 관리
            </h3>

            <!-- 현재 구독 상태 -->
            <div class="glass-panel p-4 mb-4">
                <h5 class="fw-bold text-warning mb-3">내 구독권 상태</h5>
                <?php if ($subscription && $subscription['status'] === 'active'): ?>
                    <div class="d-flex align-items-center mb-3">
                        <div class="me-3">
                            <span class="badge bg-success fs-6 px-3 py-2">사용 중</span>
                        </div>
                        <div>
                            <h4 class="fw-bold text-light mb-1"><?php echo htmlspecialchars(strtoupper(str_replace('_', ' ', $subscription['plan_type']))); ?></h4>
                            <p class="text-light opacity-75 mb-0">다음 결제 예정일: <?php echo htmlspecialchars($subscription['next_payment_date']); ?></p>
                        </div>
                    </div>
                    <div class="mt-4 pt-3 border-top border-secondary text-end">
                        <button class="btn btn-outline-danger fw-bold" onclick="cancelSubscription()">구독 해지 예약</button>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fa-solid fa-box-open fa-3x text-muted mb-3"></i>
                        <h5 class="fw-bold text-light">현재 이용 중인 구독권이 없습니다.</h5>
                        <p class="text-light opacity-50 mb-4">서비스를 이용하시려면 구독권을 결제해주세요.</p>
                        <a href="<?php echo CM_BASE_URL; ?>/subscribe" class="btn btn-warning fw-bold px-4 py-2">구독권 구매하러 가기</a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- 결제 내역 -->
            <div class="glass-panel p-4">
                <h5 class="fw-bold text-warning mb-3">결제 내역</h5>
                <div class="table-responsive">
                    <table class="table table-dark table-hover align-middle mb-0" style="--bs-table-bg: transparent; --bs-table-hover-bg: rgba(255,255,255,0.03);">
                        <thead>
                            <tr class="text-light opacity-75 small uppercase" style="border-bottom: 1px solid rgba(255,255,255,0.12); font-weight: 600;">
                                <th class="py-3 ps-3">결제일시</th>
                                <th class="py-3">결제금액</th>
                                <th class="py-3 text-center">상태</th>
                                <th class="py-3 text-center">영수증</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($paymentLogs)): ?>
                            <tr>
                                <td colspan="4" class="text-center py-5 text-muted">
                                    결제 내역이 없습니다.
                                </td>
                            </tr>
                            <?php else: ?>
                                <?php foreach ($paymentLogs as $log): ?>
                                <tr style="border-bottom: 1px solid rgba(255,255,255,0.06);">
                                    <td class="py-3 ps-3 text-light font-monospace opacity-75 small">
                                        <?php echo date('Y.m.d H:i', strtotime($log['created_at'])); ?>
                                    </td>
                                    <td class="py-3 fw-bold text-light">
                                        <?php echo number_format($log['amount']); ?>원
                                    </td>
                                    <td class="py-3 text-center">
                                        <?php if ($log['status'] === 'success'): ?>
                                            <span class="badge bg-success bg-opacity-25 text-success border border-success">결제완료</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary bg-opacity-25 text-light border border-secondary"><?php echo htmlspecialchars($log['status']); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3 text-center">
                                        <?php if (!empty($log['receipt_url'])): ?>
                                            <a href="<?php echo htmlspecialchars($log['receipt_url']); ?>" target="_blank" class="btn btn-sm btn-outline-info rounded-pill px-3">영수증 확인</a>
                                        <?php else: ?>
                                            <span class="text-muted small">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- 페이징 버튼 -->
                <?php if (isset($totalPages) && $totalPages > 1): ?>
                <nav class="mt-4">
                    <ul class="pagination justify-content-center pagination-sm">
                        <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                            <a class="page-link bg-dark text-light border-secondary" href="?page=<?= $page - 1 ?>">이전</a>
                        </li>
                        <?php for($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                                <a class="page-link <?= ($page == $i) ? 'bg-warning text-dark border-warning' : 'bg-dark text-light border-secondary' ?>" href="?page=<?= $i ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                            <a class="page-link bg-dark text-light border-secondary" href="?page=<?= $page + 1 ?>">다음</a>
                        </li>
                    </ul>
                </nav>
                <?php endif; ?>
            </div>
        </div>
    </main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
async function cancelSubscription() {
    if (!confirm('정말 구독을 해지하시겠습니까?\n해지하시면 다음 결제일부터 요금이 청구되지 않습니다.')) {
        return;
    }
    
    try {
        const res = await fetch('<?php echo CM_BASE_URL; ?>/api/bootpay/cancel', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' }
        });
        const result = await res.json();
        
        if (result.success) {
            alert(result.message);
            location.reload();
        } else {
            alert('구독 해지 실패: ' + result.message);
        }
    } catch (e) {
        console.error(e);
        alert('서버와 통신 중 오류가 발생했습니다.');
    }
}
</script>
</body>
</html>
