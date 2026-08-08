<?php $title = '전체 주문 내역'; include_admin_header($title); ?>

<div class="glass-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>전체 주문 내역</h1>
    </div>

    <div class="table-responsive">
        <table class="table admin-table">
            <thead>
                <tr>
                    <th>주문번호</th>
                    <th>구매자</th>
                    <th>판매자</th>
                    <th>금액</th>
                    <th>상태</th>
                    <th>주문일시</th>
                    <th>관리</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($orders)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5">주문 내역이 없습니다.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($orders as $o): ?>
                        <tr>
                            <td><code><?= htmlspecialchars($o['order_no']) ?></code></td>
                            <td><?= htmlspecialchars($o['buyer_id']) ?></td>
                            <td><?= htmlspecialchars($o['seller_id']) ?></td>
                            <td>$<?= number_format($o['amount'], 2) ?></td>
                            <td>
                                <span class="badge bg-primary"><?= $o['status'] ?></span>
                            </td>
                            <td><?= $o['created_at'] ?></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary">상세</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    $('#link-orders').addClass('active');
</script>

<?php include_admin_footer(); ?>
