<?php include CM_VIEWS_PATH . '/layout/header.php'; ?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-cart-check me-2"></i>판매 주문 관리</h2>
        <a href="/mypage" class="btn btn-outline-secondary btn-sm">대시보드로 돌아가기</a>
    </div>

    <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_GET['msg']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm border-0 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="px-4 py-3">주문번호 / 일시</th>
                        <th class="py-3">상품명 / 유형</th>
                        <th class="py-3">구매자</th>
                        <th class="py-3 text-end">금액</th>
                        <th class="py-3 text-center">상태</th>
                        <th class="px-4 py-3 text-center">관리</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($orders)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">주문 내역이 없습니다.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td class="px-4">
                                    <div class="fw-bold text-primary small"><?= $order['order_no'] ?></div>
                                    <div class="text-muted extra-small"><?= date('Y-m-d H:i', strtotime($order['created_at'])) ?></div>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($order['product_name']) ?></div>
                                    <div class="badge <?= $order['product_type'] === 'digital' ? 'bg-info' : 'bg-warning text-dark' ?> extra-small uppercase">
                                        <?= $order['product_type'] ?>
                                    </div>
                                </td>
                                <td><span class="text-secondary small"><?= htmlspecialchars($order['buyer_id']) ?></span></td>
                                <td class="text-end fw-bold text-success px-3"><?= number_format($order['amount']) ?>원</td>
                                <td class="text-center">
                                    <?php 
                                        $badgeClass = 'bg-secondary';
                                        $statusText = $order['status'];
                                        switch($order['status']) {
                                            case 'paid': $badgeClass = 'bg-primary'; $statusText = '결제완료'; break;
                                            case 'shipping': $badgeClass = 'bg-info'; $statusText = '배송중'; break;
                                            case 'delivered': $badgeClass = 'bg-success'; $statusText = '배송완료'; break;
                                            case 'completed': $badgeClass = 'bg-dark'; $statusText = '구매확정'; break;
                                            case 'cancelled': $badgeClass = 'bg-danger'; $statusText = '취소됨'; break;
                                        }
                                    ?>
                                    <span class="badge <?= $badgeClass ?> rounded-pill px-3"><?= $statusText ?></span>
                                </td>
                                <td class="px-4 text-center">
                                    <?php if ($order['status'] !== 'completed' && $order['status'] !== 'cancelled'): ?>
                                        <form action="/seller/orders/update-status" method="POST" class="d-inline-flex gap-1">
                                            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                                            <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                            <select name="status" class="form-select form-select-sm" style="width: auto;">
                                                <option value="paid" <?= $order['status'] === 'paid' ? 'selected' : '' ?>>결제완료</option>
                                                <option value="shipping" <?= $order['status'] === 'shipping' ? 'selected' : '' ?>>배송중</option>
                                                <option value="delivered" <?= $order['status'] === 'delivered' ? 'selected' : '' ?>>배송완료</option>
                                                <option value="completed" <?= $order['status'] === 'completed' ? 'selected' : '' ?>>구매확정</option>
                                            </select>
                                            <button type="submit" class="btn btn-primary btn-sm">변경</button>
                                        </form>
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
    </div>
</div>

<style>
.extra-small { font-size: 0.75rem; }
.uppercase { text-transform: uppercase; }
</style>

<?php include CM_VIEWS_PATH . '/layout/footer.php'; ?>
