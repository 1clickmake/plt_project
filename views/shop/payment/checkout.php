<?php include CM_VIEWS_PATH . '/layout/header.php'; ?>

<div class="container py-5 mt-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="text-center mb-5">
                <h1 class="h2 fw-bold text-dark mb-2">결제 확인</h1>
                <p class="text-muted">선택하신 서비스 결제를 진행합니다.</p>
            </div>

            <div class="card shadow-lg border-0 rounded-4 overflow-hidden">
                <div class="card-body p-4 p-md-5">
                    <div class="row align-items-center">
                        <div class="col-md-7 mb-4 mb-md-0 border-end-md">
                            <h4 class="fw-bold mb-4">주문 요약</h4>
                            <div class="list-group list-group-flush border rounded-3 overflow-hidden shadow-sm mb-0">
                                <div class="list-group-item d-flex justify-content-between align-items-center py-3">
                                    <span class="text-secondary small">상품명</span>
                                    <span class="fw-bold text-dark"><?php echo htmlspecialchars($product['name']); ?></span>
                                </div>
                                <div class="list-group-item d-flex justify-content-between align-items-center py-3 bg-light text-primary">
                                    <span class="small font-weight-bold">결제 금액</span>
                                    <span class="h4 fw-bold mb-0 text-primary">$<?php echo number_format($product['price'], 2); ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-5 ps-md-4 text-center">
                            <button id="paddle-checkout-btn" 
                                    class="btn btn-primary btn-lg w-100 py-3 fw-bold rounded-3 shadow-lg mb-3">
                                    <i class="fa-solid fa-credit-card me-2"></i> 결제하기
                            </button>
                            <div class="text-center">
                                <span class="text-muted small"><i class="fa-solid fa-lock me-1"></i> 안전한 보안 결제</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card-footer bg-light p-3 text-center border-0 small text-muted">
                    결제 시 Paddle의 이용 약관에 동의하게 됩니다.
                </div>
            </div>
            
            <div class="text-center mt-4">
                <a href="/payment" class="text-decoration-none text-muted small">
                    <i class="fa-solid fa-arrow-left me-1"></i> 이전 페이지로 돌아가기
                </a>
            </div>
        </div>
    </div>
</div>

<style>
@media (min-width: 768px) {
    .border-end-md { border-right: 1px solid #dee2e6 !important; }
}
</style>

<!-- Paddle.js Setup -->
<script src="https://cdn.paddle.com/paddle/v2/paddle.js"></script>
<script type="text/javascript">
  const paddlePriceId = '<?php echo $product['paddle_price_id']; ?>';
  const paddleToken = '<?php echo $paddle_client_token; ?>';
  const paddleEnv = '<?php echo $paddle_env; ?>';
  const successUrl = '<?php echo CM_SITE_URL; ?>/payment/success';

  console.log('Paddle Debug:', { env: paddleEnv, priceId: paddlePriceId, successUrl: successUrl });

  // Paddle v2 uses Initialize instead of Setup
  if (typeof Paddle !== 'undefined') {
    initializePaddle();
  } else {
    window.addEventListener('load', function() {
        if (typeof Paddle !== 'undefined') initializePaddle();
        else console.error('Paddle script failed to load.');
    });
  }

  function initializePaddle() {
    Paddle.Environment.set(paddleEnv);
    Paddle.Initialize({ 
        token: paddleToken,
        eventCallback: function(data) {
            console.log('Paddle Global Event:', data);
        }
    });
    console.log('Paddle v2 Initialized');
  }

  document.getElementById('paddle-checkout-btn').addEventListener('click', function() {
    console.log('Checkout button clicked. Target URL:', successUrl);
    
    if (!paddlePriceId) {
        alert('상품의 Paddle Price ID가 등록되지 않았습니다. 관리자 페이지에서 확인해 주세요.');
        return;
    }

    if (typeof Paddle === 'undefined' || !Paddle.Checkout) {
        alert('Paddle 결제 라이브러리가 로드되지 않았습니다. 잠시 후 다시 시도해 주세요.');
        return;
    }

    Paddle.Checkout.open({
      items: [
        {
          priceId: paddlePriceId,
          quantity: 1
        }
      ],
      customData: {
        product_id: '<?php echo $product['id']; ?>',
        user_id: '<?php echo $buyer_id; ?>'
      },
      settings: {
        successUrl: successUrl,
        displayMode: 'overlay',
        theme: 'light'
      }
    });
  });
</script>

<?php include CM_VIEWS_PATH . '/layout/footer.php'; ?>
