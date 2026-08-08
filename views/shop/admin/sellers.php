<?php $title = '판매자 관리'; include_admin_header($title); ?>

<div class="glass-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>판매자 관리</h1>
    </div>

    <div class="table-responsive">
        <table class="table admin-table">
            <thead>
                <tr>
                    <th>판매자 ID</th>
                    <th>등록 상품 수</th>
                    <th>총 판매액</th>
                    <th>상태</th>
                    <th>관리</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($sellers)): ?>
                    <tr>
                        <td colspan="5" class="text-center py-5">등록된 판매자가 없습니다.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($sellers as $s): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($s['seller_id']) ?></strong></td>
                            <td>-</td>
                            <td>-</td>
                            <td><span class="badge bg-success">활성</span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary">상세보기</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    $('#link-sellers').addClass('active');
</script>

<?php include_admin_footer(); ?>
