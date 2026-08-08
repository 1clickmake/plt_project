<?php $title = '상품 관리 (Product Manager)'; include_admin_header($title); ?>

<div class="glass-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>상품 관리</h1>
        <button class="btn btn-primary" onclick="showCreateModal()">
            <i class="fa-solid fa-plus"></i> 새 상품 등록
        </button>
    </div>

    <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_GET['msg']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table admin-table">
            <thead>
                <tr>
                    <th>순서</th>
                    <th>유형</th>
                    <th>상품명</th>
                    <th>가격 (USD)</th>
                    <th>지급 포인트</th>
                    <th>Paddle/재고</th>
                    <th>상태</th>
                    <th>관리</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($products)): ?>
                    <tr>
                        <td colspan="8" class="text-center py-5">등록된 상품이 없습니다.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($products as $p): ?>
                        <tr>
                            <td><?= $p['display_order'] ?></td>
                            <td>
                                <?php if ($p['type'] === 'digital'): ?>
                                    <span class="badge bg-info text-dark">디지털</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark">실물</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($p['name']) ?></strong>
                                <?php if ($p['description']): ?>
                                    <div class="text-muted-small small"><?= htmlspecialchars($p['description']) ?></div>
                                <?php endif; ?>
                                <div class="text-muted-small small" style="font-size: 0.7rem;">ID: <?= $p['id'] ?> | 판매자: <?= $p['seller_id'] ?></div>
                            </td>
                            <td>$<?= number_format($p['price'], 2) ?></td>
                            <td><?= number_format($p['point_reward']) ?> P</td>
                            <td>
                                <?php if ($p['type'] === 'digital'): ?>
                                    <code><?= htmlspecialchars($p['paddle_price_id']) ?: 'N/A' ?></code>
                                <?php else: ?>
                                    재고: <?= number_format($p['stock']) ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php 
                                    $statusBadge = 'bg-secondary';
                                    $statusText = $p['status'];
                                    if ($p['status'] === 'active') { $statusBadge = 'bg-success'; $statusText = '활성'; }
                                    elseif ($p['status'] === 'pending') { $statusBadge = 'bg-warning text-dark'; $statusText = '대기'; }
                                    elseif ($p['status'] === 'suspended') { $statusBadge = 'bg-danger'; $statusText = '중지'; }
                                ?>
                                <span class="badge <?= $statusBadge ?>"><?= $statusText ?></span>
                                <?php if (!$p['is_active']): ?>
                                    <span class="badge bg-light text-dark border">숨김</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" onclick='showEditModal(<?= json_encode($p) ?>)'>수정</button>
                                <button class="btn btn-sm btn-outline-danger" onclick="deleteProduct(<?= $p['id'] ?>)">삭제</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Create/Edit Modal -->
<div class="modal fade" id="productModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content glass-card p-0 overflow-hidden">
            <form id="productForm" method="POST">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <input type="hidden" name="id" id="prod_id">
                
                <div class="modal-header border-0 bg-primary text-white">
                    <h5 class="modal-title" id="modalTitle">상품 등록</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label">상품명</label>
                            <input type="text" name="name" id="prod_name" class="form-control" required placeholder="예: Pro Plan 또는 프리미엄 티셔츠">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">상품 유형</label>
                            <select name="type" id="prod_type" class="form-select" onchange="toggleTypeFields()">
                                <option value="digital">디지털 상품 (Paddle)</option>
                                <option value="physical">일반 배송 상품</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">설명</label>
                        <textarea name="description" id="prod_desc" class="form-control" rows="2"></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">가격 (USD)</label>
                            <input type="number" step="0.01" name="price" id="prod_price" class="form-control" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">지급 포인트</label>
                            <input type="number" name="point_reward" id="prod_point" class="form-control" value="0">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">판매 상태</label>
                            <select name="status" id="prod_status" class="form-select">
                                <option value="active">판매 중 (Active)</option>
                                <option value="pending">승인 대기 (Pending)</option>
                                <option value="suspended">판매 중지 (Suspended)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Digital Only -->
                    <div id="type_digital" class="type-section">
                        <div class="mb-3 border-start border-4 border-info ps-3 bg-light py-2 rounded">
                            <label class="form-label">Paddle Price ID</label>
                            <input type="text" name="paddle_price_id" id="prod_paddle_id" class="form-control" placeholder="pri_...">
                            <div class="form-text">디지털 결제를 위해 Paddle Price ID를 입력하세요.</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">디지털 파일/링크</label>
                            <input type="text" name="digital_link" id="prod_link" class="form-control" placeholder="https://...">
                            <div class="form-text">결제 완료 후 구매자에게 보여줄 다운로드 링크입니다.</div>
                        </div>
                    </div>

                    <!-- Physical Only -->
                    <div id="type_physical" class="type-section" style="display: none;">
                        <div class="row border-start border-4 border-warning ps-3 bg-light py-2 rounded">
                            <div class="col-md-6 mb-2">
                                <label class="form-label">재고 수량</label>
                                <input type="number" name="stock" id="prod_stock" class="form-control" value="0">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label">배송비 (USD)</label>
                                <input type="number" step="0.01" name="shipping_fee" id="prod_shipping" class="form-control" value="0.00">
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">출력 순서</label>
                            <input type="number" name="display_order" id="prod_order" class="form-control" value="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">노출 여부</label>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="is_active" id="prod_active" checked>
                                <label class="form-check-label" for="prod_active">목록에 노출</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">취소</button>
                    <button type="submit" class="btn btn-primary">저장하기</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $('#link-products').addClass('active');

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
        $('#modalTitle').text('새 상품 등록');
        $('#productForm').attr('action', '/admin/products/create');
        $('#prod_id').val('');
        $('#prod_name').val('');
        $('#prod_desc').val('');
        $('#prod_price').val('0.00');
        $('#prod_point').val('0');
        $('#prod_paddle_id').val('');
        $('#prod_type').val('digital');
        $('#prod_stock').val('0');
        $('#prod_shipping').val('0.00');
        $('#prod_link').val('');
        $('#prod_status').val('active');
        $('#prod_order').val('0');
        $('#prod_active').prop('checked', true);
        toggleTypeFields();
        productModal.show();
    }

    function showEditModal(p) {
        $('#modalTitle').text('상품 수정');
        $('#productForm').attr('action', '/admin/products/update');
        $('#prod_id').val(p.id);
        $('#prod_name').val(p.name);
        $('#prod_desc').val(p.description);
        $('#prod_price').val(p.price);
        $('#prod_point').val(p.point_reward);
        $('#prod_paddle_id').val(p.paddle_price_id);
        $('#prod_type').val(p.type);
        $('#prod_stock').val(p.stock);
        $('#prod_shipping').val(p.shipping_fee);
        $('#prod_link').val(p.digital_link);
        $('#prod_status').val(p.status);
        $('#prod_order').val(p.display_order);
        $('#prod_active').prop('checked', p.is_active == 1);
        toggleTypeFields();
        productModal.show();
    }

    function deleteProduct(id) {
        if (confirm('정말 이 상품을 삭제하시겠습니까?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/admin/products/delete';
            
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = 'csrf_token';
            csrfInput.value = '<?= $csrf_token ?>';
            form.appendChild(csrfInput);
            
            const idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = 'id';
            idInput.value = id;
            form.appendChild(idInput);
            
            document.body.appendChild(form);
            form.submit();
        }
    }
</script>

<?php include_admin_footer(); ?>
