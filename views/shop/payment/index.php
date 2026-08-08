<?php include CM_VIEWS_PATH . '/layout/header.php'; ?>

<div class="container py-5 mt-5">
    <div class="text-center mb-5">
        <h1 class="display-4 fw-bold text-dark mb-3">Pricing Plans</h1>
        <p class="lead text-muted">당신에게 필요한 최적의 플랜을 선택하세요.</p>
    </div>

    <div class="row g-4 justify-content-center">
        <?php if (empty($products)): ?>
            <div class="col-12 text-center py-5 border rounded bg-light">
                <p class="text-muted mb-0">현재 준비된 요금제가 없습니다.</p>
            </div>
        <?php else: ?>
            <?php foreach ($products as $index => $p): ?>
                <?php 
                    $isPopular = ($p['display_order'] >= 99); 
                ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 shadow-sm border-0 <?= $isPopular ? 'border-primary border-top border-4' : '' ?> rounded-4 position-relative overflow-hidden">
                        <?php if ($isPopular): ?>
                            <div class="position-absolute top-0 end-0 bg-primary text-white px-3 py-1 small fw-bold rounded-bl-3" style="z-index: 10;">Popular</div>
                        <?php endif; ?>
                        
                        <div class="card-body p-4 d-flex flex-column">
                            <h3 class="card-title h4 fw-bold mb-3"><?= htmlspecialchars($p['name']) ?></h3>
                            <div class="d-flex align-items-baseline mb-4">
                                <span class="display-5 fw-bold text-dark">$<?= number_format($p['price'], 0) ?></span>
                                <span class="text-muted ms-1">/mo</span>
                            </div>
                            
                            <?php if ($p['description']): ?>
                                <p class="card-text text-secondary mb-4"><?= nl2br(htmlspecialchars($p['description'])) ?></p>
                            <?php endif; ?>

                            <ul class="list-unstyled mb-auto">
                                <li class="d-flex align-items-center mb-3">
                                    <i class="fa-solid fa-circle-check text-primary me-2"></i>
                                    <span class="text-dark"><?= number_format($p['point_reward']) ?> 포인트 지급</span>
                                </li>
                                <li class="d-flex align-items-center mb-3">
                                    <i class="fa-solid fa-circle-check text-primary me-2"></i>
                                    <span class="text-dark">프리미엄 기능 활성화</span>
                                </li>
                                <li class="d-flex align-items-center mb-3">
                                    <i class="fa-solid fa-circle-check text-primary me-2"></i>
                                    <span class="text-dark">평생 업데이트 포함</span>
                                </li>
                            </ul>
                            
                            <a href="/payment/checkout/<?= htmlspecialchars($p['id']) ?>" 
                               class="btn <?= $isPopular ? 'btn-primary' : 'btn-outline-primary' ?> btn-lg w-100 py-3 fw-bold mt-4 shadow-sm">
                                지금 시작하기
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php include CM_VIEWS_PATH . '/layout/footer.php'; ?>
