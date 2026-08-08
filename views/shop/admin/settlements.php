<?php $title = '정산 승인/관리'; include_admin_header($title); ?>

<div class="glass-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>정산 승인/관리</h1>
    </div>

    <div class="table-responsive">
        <table class="table admin-table">
            <thead>
                <tr>
                    <th>판매자</th>
                    <th>요청 금액</th>
                    <th>실제 지급액</th>
                    <th>상태</th>
                    <th>요청일</th>
                    <th>처리일</th>
                    <th>관리</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($settlements)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5">정산 요청 내역이 없습니다.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($settlements as $s): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($s['seller_id']) ?></strong></td>
                            <td>$<?= number_format($s['amount'], 2) ?></td>
                            <td class="text-primary fw-bold">$<?= number_format($s["amount"] * (1 - (($commission ?? 10) / 100)), 2) ?></td>
                            <td>
                                <?php 
                                    $badge = 'bg-warning text-dark';
                                    if ($s['status'] === 'approved') $badge = 'bg-info text-dark';
                                    elseif ($s['status'] === 'paid') $badge = 'bg-success';
                                    elseif ($s['status'] === 'rejected') $badge = 'bg-danger';
                                ?>
                                <span class="badge <?= $badge ?>"><?= $s['status'] ?></span>
                            </td>
                            <td><?= $s['request_date'] ?></td>
                            <td><?= $s['process_date'] ?: '-' ?></td>
                            <td>
                                <?php if ($s['status'] === 'request'): ?>
                                    <form action="/admin/settlements/approve" method="POST" style="display:inline;">
                                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                                        <input type="hidden" name="id" value="<?= $s['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-success">승인</button>
                                    </form>
                                <?php endif; ?>
                                <form action="/admin/settlements/delete" method="POST" style="display:inline;" onsubmit="return confirm('정말 삭제하시겠습니까?');">
                                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                                    <input type="hidden" name="id" value="<?= $s['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-danger"><i class="fa-solid fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    $('#link-settlements').addClass('active');
</script>

<?php include_admin_footer(); ?>
