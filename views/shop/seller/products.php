<?php $title = '내 상품 관리'; include_header($title, $siteConfig); ?>

<div class="container my-5">
    <div class="glass-card">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><i class="fa-solid fa-boxes-stacked me-2"></i>내 상품 관리</h1>
            <button class="btn btn-primary" onclick="showCreateModal()">
                <i class="fa-solid fa-plus"></i> 새 상품 등록 요청
            </button>
        </div>

        <?php if (isset($_GET['msg'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_GET['msg']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table mypage-table">
                <thead>
                    <tr>
                        <th>유형</th>
                        <th>상품명</th>
                        <th>가격</th>
                        <th>상태</th>
                        <th>등록일</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($products)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-5">등록된 상품이 없습니다. 첫 상품을 등록해 보세요!</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($products as $p): ?>
                            <tr>
                                <td>
                                    <?php if ($p['type'] === 'digital'): ?>
                                        <span class="badge bg-info text-dark">디지털</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark">실물</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($p['name']) ?></strong>
                                </td>
                                <td>$<?= number_format($p['price'], 2) ?></td>
                                <td>
                                    <?php 
                                        $statusBadge = 'bg-secondary';
                                        if ($p['status'] === 'active') $statusBadge = 'bg-success';
                                        elseif ($p['status'] === 'pending') $statusBadge = 'bg-warning text-dark';
                                        elseif ($p['status'] === 'suspended') $statusBadge = 'bg-danger';
                                    ?>
                                    <span class="badge <?= $statusBadge ?>"><?= $p['status'] ?></span>
                                </td>
                                <td><?= date('Y-m-d', strtotime($p['created_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <div class="mt-4">
            <a href="/mypage" class="btn btn-outline-secondary">
                <i class="fa-solid fa-arrow-left me-2"></i> 마이페이지로 돌아가기
            </a>
        </div>
    </div>
</div>

<!-- Create Modal -->
<div class="modal fade" id="productModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content glass-card p-0 overflow-hidden">
            <form id="productForm" action="/seller/products/create" method="POST">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                
                <div class="modal-header border-0 bg-primary text-white">
                    <h5 class="modal-title">판매 상품 등록 요청</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-info py-2 small">
                        <i class="fa-solid fa-circle-info me-1"></i> 모든 상품은 관리자의 승인 후에 판매가 시작됩니다.
                    </div>
                    
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label">상품명</label>
                            <input type="text" name="name" class="form-control" required placeholder="예: 디자인 템플릿, 수제 굿즈 등">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">상품 유형</label>
                            <select name="type" id="prod_type" class="form-select" onchange="toggleTypeFields()">
                                <option value="digital">디지털 상품 (파일/링크)</option>
                                <option value="physical">실물 배송 상품</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">상품 설명</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="상품에 대한 상세 설명을 입력하세요."></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">판매 가격 (USD)</label>
                            <input type="number" step="0.01" name="price" class="form-control" required value="0.00">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">지급 포인트 (구매 시)</label>
                            <input type="number" name="point_reward" class="form-control" value="0">
                        </div>
                    </div>

                    <!-- Digital Only -->
                    <div id="type_digital" class="type-section border-start border-4 border-info ps-3 bg-light py-3 rounded">
                        <div class="mb-3">
                            <label class="form-label">다운로드 링크 / 파일 경로</label>
                            <input type="text" name="digital_link" class="form-control" placeholder="https://...">
                            <div class="form-text">결제 완료 후 구매자에게 안전하게 전달될 링크입니다.</div>
                        </div>
                        <div class="mb-0">
                            <label class="form-label">Paddle Price ID (선택사항)</label>
                            <input type="text" name="paddle_price_id" class="form-control" placeholder="pri_...">
                            <div class="form-text">글로벌 결제 연동이 필요한 경우 입력하세요.</div>
                        </div>
                    </div>

                    <!-- Physical Only -->
                    <div id="type_physical" class="type-section border-start border-4 border-warning ps-3 bg-light py-3 rounded" style="display: none;">
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <label class="form-label">재고 수량</label>
                                <input type="number" name="stock" class="form-control" value="0">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label">배송비 (USD)</label>
                                <input type="number" step="0.01" name="shipping_fee" class="form-control" value="0.00">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">취소</button>
                    <button type="submit" class="btn btn-primary">등록 요청하기</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    let productModal;
    $(function() {
        productModal = new bootstrap.Modal(document.getElementById('productModal'));
    });

    function toggleTypeFields() {
        const type = $('#prod_type').val();
        if (type === 'digital') {
            $('#type_digital').show();
            $('#type_physical').hide();
        } else {
            $('#type_digital').hide();
            $('#type_physical').show();
        }
    }

    function showCreateModal() {
        productModal.show();
    }
</script>

<?php include_footer($siteConfig); ?>
