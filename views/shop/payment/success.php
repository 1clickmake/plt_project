<?php include CM_VIEWS_PATH . '/layout/header.php'; ?>

<div class="container py-5 mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5 text-center">
            <div class="card shadow-lg border-0 rounded-4 p-5">
                <div class="mb-4">
                    <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center mx-auto shadow-sm" style="width: 80px; height: 80px;">
                        <i class="fa-solid fa-check fa-3x"></i>
                    </div>
                </div>
                
                <h1 class="h2 fw-bold text-dark mb-3">결제가 완료되었습니다!</h1>
                <p class="text-muted mb-5">주문이 성공적으로 처리되었습니다.<br>이용해 주셔서 감사합니다.</p>
                
                <div class="d-grid gap-3">
                    <a href="<?php echo CM_SITE_URL; ?>/mypage" class="btn btn-primary btn-lg fw-bold rounded-3 py-3 shadow-sm">
                        확인 (구매 내역 이동)
                    </a>
                    <a href="<?php echo CM_SITE_URL; ?>/" class="btn btn-link text-decoration-none text-muted">
                        메인으로 돌아가기
                    </a>
                </div>
            </div>
            <p class="mt-4 text-muted small">결제 관련 문의는 고객센터 채널을 이용해 주세요.</p>
        </div>
    </div>
</div>

<?php include CM_VIEWS_PATH . '/layout/footer.php'; ?>
