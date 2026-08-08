<?php $title = 'My Page'; include_header($title, $siteConfig); ?>

<div class="container mypage-container">
    <div class="mypage-header-mb">
        <h1 class="mypage-header-title">My Page</h1>
        <div class="mypage-header-divider"></div>
    </div>

    <?php if (isset($_GET['updated'])): ?>
        <div class="alert alert-success mypage-alert-success">
            Profile updated successfully.
        </div>
    <?php endif; ?>

    <div class="mypage-grid">
        <!-- Left: User Info -->
        <div class="glass-card glass-card-start">
            <div class="mypage-profile-header">
                <img src="https://ui-avatars.com/api/?name=<?= urlencode($user['username']) ?>&background=6366f1&color=fff&size=128" 
                     class="mypage-profile-img">
                <h3 class="mypage-profile-name"><?= htmlspecialchars($user['username'] ?? '') ?></h3>
                <span class="mypage-profile-role"><?= strtoupper($user['role'] ?? 'USER') ?></span>
            </div>

            <form action="/profile/update" method="POST">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <div class="mypage-form-group">
                    <label>User ID</label>
                    <input type="text" class="form-control mypage-input-disabled" value="<?= htmlspecialchars($user['user_id'] ?? '') ?>" disabled>
                </div>
                <div class="mypage-form-group">
                    <label>Username</label>
                    <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($user['username'] ?? '') ?>" required>
                </div>
                <div class="mypage-form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
                </div>
                <div class="mypage-form-group">
                    <label>Current Password (Required for changes)</label>
                    <input type="password" name="current_password" class="form-control" required>
                </div>
                <div class="mypage-form-group">
                    <label>New Password (Leave blank to keep current)</label>
                    <input type="password" name="password" class="form-control" placeholder="Optional">
                </div>
                <div class="mypage-form-group">
                    <label>Country (Auto Detected)</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($user['country'] ?? 'Unknown') ?>" disabled style="opacity: 0.6;">
                </div>
                
                <div class="mypage-btn-group">
                    <button type="submit" class="btn btn-primary btn-w-100">Update Profile</button>
                    <a href="/logout" class="btn btn-logout">Logout</a>
                </div>
            </form>

            <hr class="mypage-divider">

            <form action="/profile/delete" method="POST" onsubmit="return confirm('WARNING: Are you sure you want to delete your account? This action cannot be undone.')">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <button type="submit" class="btn-delete-account">
                    Delete my account
                </button>
            </form>
        </div>

        <!-- Right: My Posts & Seller Dashboard -->
        <div class="glass-card-container">
            <!-- Tabs for Right Column -->
            <ul class="nav nav-pills mb-4" id="mypage-tabs" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tab-posts">내가 쓴 글</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-seller">판매자 센터</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-orders">주문 내역</button>
                </li>
            </ul>

            <div class="tab-content">
                <!-- Tab: My Posts -->
                <div class="tab-pane fade show active" id="tab-posts">
                    <h3 class="mypage-section-title">최근 작성 글 (최대 10개)</h3>
                    <div class="table-responsive">
                        <table class="table table-hover mypage-table">
                            <thead>
                                <tr>
                                    <th>게시판</th>
                                    <th>제목</th>
                                    <th>날짜</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($posts as $post): ?>
                                <tr>
                                    <td><span class="mypage-badge"><?= htmlspecialchars($post['board_title']) ?></span></td>
                                    <td>
                                        <a href="/board/view/<?= $post['id'] ?>" class="mypage-post-link">
                                            <?= htmlspecialchars($post['title']) ?>
                                        </a>
                                    </td>
                                    <td class="mypage-post-date"><?= date('Y-m-d', strtotime($post['created_at'])) ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($posts)): ?>
                                <tr>
                                    <td colspan="3" class="mypage-empty-state">
                                        <i class="fa-solid fa-pen-nib mypage-empty-icon"></i>
                                        작성한 게시글이 없습니다.
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tab: Seller Dashboard -->
                <div class="tab-pane fade" id="tab-seller">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3 class="mypage-section-title mb-0">판매자 대시보드</h3>
                        <a href="/seller/products" class="btn btn-sm btn-primary">
                            <i class="fa-solid fa-plus"></i> 상품 등록/관리
                        </a>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="glass-card bg-primary text-white p-3 border-0">
                                <div class="small opacity-75">출금 가능 금액</div>
                                <div class="h3 mb-0">$<?= number_format($sellerInfo['balance'] ?? 0, 2) ?></div>
                                <a href="/seller/settlement" class="btn btn-sm btn-light mt-2">정산 신청</a>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="glass-card p-3 border-0 bg-light">
                                <div class="small text-muted">이번 달 판매 건수</div>
                                <div class="h3 mb-0 text-dark"><?= number_format($sellerInfo['monthly_sales'] ?? 0) ?>건</div>
                                <div class="small text-success mt-2">활성 상품: <?= number_format($sellerInfo['active_products'] ?? 0) ?>개</div>
                            </div>
                        </div>
                    </div>

                    <h4 class="h6 mb-3">최근 판매 알림</h4>
                    <div class="table-responsive">
                        <table class="table table-sm mypage-table">
                            <tbody>
                                <?php if (empty($recentSales)): ?>
                                    <tr><td class="text-center py-4 text-muted">판매 내역이 아직 없습니다.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($recentSales as $sale): ?>
                                        <tr>
                                            <td>
                                                <div class="small text-muted"><?= date('m-d H:i', strtotime($sale['created_at'])) ?></div>
                                                <div class="fw-bold"><?= htmlspecialchars($sale['product_name']) ?></div>
                                            </td>
                                            <td class="text-end align-middle">
                                                <span class="text-primary fw-bold">$<?= number_format($sale['amount'], 2) ?></span>
                                                <div class="badge bg-light text-dark border"><?= $sale['status'] ?></div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tab: My Orders -->
                <div class="tab-pane fade" id="tab-orders">
                    <h3 class="mypage-section-title">내가 구매한 내역</h3>
                    <div class="table-responsive">
                        <table class="table mypage-table">
                            <thead>
                                <tr>
                                    <th>주문정보</th>
                                    <th>금액</th>
                                    <th>상태</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($myOrders)): ?>
                                    <tr><td colspan="3" class="text-center py-4 text-muted">구매한 내역이 없습니다.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($myOrders as $order): ?>
                                        <tr>
                                            <td>
                                                <div class="fw-bold"><?= htmlspecialchars($order['product_name']) ?></div>
                                                <div class="small text-muted">주문번호: <?= $order['order_no'] ?></div>
                                            </td>
                                            <td class="align-middle">
                                                $<?= number_format($order['amount'], 2) ?>
                                            </td>
                                            <td class="align-middle text-end">
                                                <?php if ($order['status'] === 'completed'): ?>
                                                    <span class="badge bg-success">구매확정</span>
                                                <?php else: ?>
                                                    <span class="badge bg-info mb-1 d-block"><?= $order['status'] ?></span>
                                                    <button class="btn btn-sm btn-primary w-100" onclick="confirmOrder(<?= $order['id'] ?>)">확정하기</button>
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
    </div>
</div>

<script>
function confirmOrder(orderId) {
    if (!confirm('물건을 잘 받으셨나요? 구매 확정 후에는 취소가 불가하며 판매자에게 정산금이 지급됩니다.')) return;

    fetch('/payment/confirm-order', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'order_id=' + orderId + '&csrf_token=<?= $csrf_token ?>'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('구매 확정이 완료되었습니다. 감사합니다!');
            location.reload();
        } else {
            alert('오류: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('처리 중 오류가 발생했습니다.');
    });
}
</script>
<?php include_footer($siteConfig); ?>
