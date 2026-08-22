<?php
require_once CM_PATH . '/config/config.php';
require_once CM_PATH . '/app/Core/Database.php';

use App\Core\Database;

// 관리자 권한 체크 생략
$db = Database::getInstance();
if ($db) {
    // 페이징 처리
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    if ($page < 1) $page = 1;
    $limit = 20;
    $offset = ($page - 1) * $limit;

    $totalStmt = $db->query("SELECT COUNT(*) FROM payment_logs");
    $totalItems = $totalStmt->fetchColumn();
    $totalPages = ceil($totalItems / $limit);

    // 모든 유저의 결제 내역(영수증) 가져오기 (가장 최근 결제 순)
    $logStmt = $db->query("
        SELECT p.*, u.user_id as string_id, u.username 
        FROM payment_logs p 
        LEFT JOIN users u ON p.user_id = u.id 
        ORDER BY p.created_at DESC 
        LIMIT $limit OFFSET $offset
    ");
    $paymentLogs = $logStmt->fetchAll();

    // 통계용
    $statsStmt = $db->query("
        SELECT 
            COUNT(CASE WHEN status = 'success' THEN 1 END) as success_cnt,
            COUNT(CASE WHEN status = 'failed' THEN 1 END) as failed_cnt,
            SUM(CASE WHEN status = 'success' THEN amount ELSE 0 END) as total_sales
        FROM payment_logs
    ");
    $stats = $statsStmt->fetch();
} else {
    $paymentLogs = [];
    $stats = ['success_cnt' => 0, 'failed_cnt' => 0, 'total_sales' => 0];
}

include_admin_header('관리자 - 결제 내역 관리');
?>

<div class="glass-card">
    <div class="admin-header-flex">
        <h1>전체 결제(매출) 내역</h1>
    </div>

    <!-- 통계 카드 -->
    <div class="admin-grid" style="grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
        <div class="glass-card" style="background: rgba(42, 91, 218, 0.2); border: 1px solid rgba(42, 91, 218, 0.3); padding: 1.5rem;">
            <div style="font-size: 0.9rem; color: rgba(255,255,255,0.7); margin-bottom: 0.5rem;"><i class="fa-solid fa-coins me-2"></i> 총 누적 매출</div>
            <div style="font-size: 1.8rem; font-weight: 700; color: #fff;">₩<?php echo number_format($stats['total_sales'] ?? 0); ?></div>
        </div>
        <div class="glass-card" style="background: rgba(39, 201, 63, 0.2); border: 1px solid rgba(39, 201, 63, 0.3); padding: 1.5rem;">
            <div style="font-size: 0.9rem; color: rgba(255,255,255,0.7); margin-bottom: 0.5rem;"><i class="fa-solid fa-check-circle me-2"></i> 성공 결제건</div>
            <div style="font-size: 1.8rem; font-weight: 700; color: #fff;"><?php echo number_format($stats['success_cnt'] ?? 0); ?>건</div>
        </div>
        <div class="glass-card" style="background: rgba(255, 95, 86, 0.2); border: 1px solid rgba(255, 95, 86, 0.3); padding: 1.5rem;">
            <div style="font-size: 0.9rem; color: rgba(255,255,255,0.7); margin-bottom: 0.5rem;"><i class="fa-solid fa-triangle-exclamation me-2"></i> 실패 결제건 (한도초과 등)</div>
            <div style="font-size: 1.8rem; font-weight: 700; color: #fff;"><?php echo number_format($stats['failed_cnt'] ?? 0); ?>건</div>
        </div>
    </div>

    <!-- 결제 내역 테이블 -->
    <div class="table-responsive">
        <table class="table table-dark table-hover table-striped text-center align-middle">
            <thead>
                <tr>
                    <th class="py-3">결제일시</th>
                    <th class="py-3">유저 ID</th>
                    <th class="py-3">금액</th>
                    <th class="py-3">상태</th>
                    <th class="py-3">영수증</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($paymentLogs)): ?>
                <tr>
                    <td colspan="5" style="padding: 3rem; color: rgba(255,255,255,0.5);">결제 내역이 없습니다.</td>
                </tr>
                <?php else: ?>
                    <?php foreach($paymentLogs as $log): ?>
                    <tr>
                        <td style="font-family: monospace; color: rgba(255,255,255,0.7);"><?php echo date('Y.m.d H:i:s', strtotime($log['created_at'])); ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($log['string_id'] ?? $log['user_id']); ?></strong>
                            <?php if(!empty($log['username'])): ?>
                                <span style="color: rgba(255,255,255,0.5); font-size: 0.85em;">(<?php echo htmlspecialchars($log['username']); ?>)</span>
                            <?php endif; ?>
                        </td>
                        <td style="font-weight: bold; color: #62D6FF;">₩<?php echo number_format($log['amount']); ?></td>
                        <td>
                            <?php if($log['status'] === 'success'): ?>
                                <span class="badge" style="background: rgba(39,201,63,0.2); color: #27C93F; border: 1px solid rgba(39,201,63,0.3); padding: 5px 10px;">결제 성공</span>
                            <?php else: ?>
                                <span class="badge" style="background: rgba(255,95,86,0.2); color: #FF5F56; border: 1px solid rgba(255,95,86,0.3); padding: 5px 10px;">결제 실패</span>
                                <?php if (!empty($log['error_msg'])): ?>
                                    <div style="font-size: 0.75rem; color: #FF5F56; margin-top: 4px;"><?php echo htmlspecialchars($log['error_msg']); ?></div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if($log['receipt_url']): ?>
                                <a href="<?php echo htmlspecialchars($log['receipt_url']); ?>" target="_blank" class="btn btn-sm" style="background: rgba(255,255,255,0.1); color: #fff; border: 1px solid rgba(255,255,255,0.2);">영수증 보기</a>
                            <?php else: ?>
                                <span style="color: rgba(255,255,255,0.3);">-</span>
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
                    <a class="page-link <?= ($page == $i) ? 'bg-primary text-light border-primary' : 'bg-dark text-light border-secondary' ?>" href="?page=<?= $i ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
            <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                <a class="page-link bg-dark text-light border-secondary" href="?page=<?= $page + 1 ?>">다음</a>
            </li>
        </ul>
    </nav>
    <?php endif; ?>
</div>

<?php include_admin_footer(); ?>
