<?php
require_once CM_PATH . '/config/config.php';
include_header('요금제 및 구독 결제');
?>
<style>
.pricing-card {
    background: rgba(255, 255, 255, 0.7);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.5);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.pricing-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 30px rgba(0,0,0,0.1);
}
.pricing-card.popular {
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
    border: 2px solid #667eea;
}
.price-display { min-height: 80px; }
.vat-text { font-size: 0.8rem; color: #6c757d; }
</style>

<div class="container py-5">
    <div class="text-center mb-5">
        <h2 class="fw-bold mb-3">비즈니스에 딱 맞는 요금제를 선택하세요 💎</h2>
        <p class="text-muted">약정 기간이 길어질수록 할인 혜택은 커집니다.</p>

        <!-- 결제 주기 토글 -->
        <div class="d-inline-flex bg-light rounded-pill p-1 mt-3 shadow-sm border">
            <input type="radio" class="btn-check" name="duration" id="dur_1m" value="1" autocomplete="off" checked>
            <label class="btn btn-outline-primary border-0 rounded-pill px-4" for="dur_1m">1개월</label>

            <input type="radio" class="btn-check" name="duration" id="dur_3m" value="3" autocomplete="off">
            <label class="btn btn-outline-primary border-0 rounded-pill px-4" for="dur_3m">3개월 (10% 할인)</label>

            <input type="radio" class="btn-check" name="duration" id="dur_6m" value="6" autocomplete="off">
            <label class="btn btn-outline-primary border-0 rounded-pill px-4" for="dur_6m">6개월 (20% 할인)</label>

            <input type="radio" class="btn-check" name="duration" id="dur_12m" value="12" autocomplete="off">
            <label class="btn btn-outline-primary border-0 rounded-pill px-4" for="dur_12m">12개월 (30% 할인)</label>
        </div>
    </div>

    <div class="row g-4 justify-content-center">
        <!-- FREE -->
        <div class="col-lg-4 col-md-6">
            <div class="card pricing-card h-100 rounded-4 p-4 text-center">
                <h4 class="fw-bold">FREE</h4>
                <p class="text-muted small">솔루션 체험용</p>
                <div class="price-display d-flex flex-column justify-content-center my-4">
                    <h2 class="fw-bold mb-0">무료</h2>
                </div>
                <button class="btn btn-light rounded-pill fw-bold w-100 mb-4" disabled>현재 이용중</button>
                <ul class="list-unstyled text-start small mb-0">
                    <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>모든 기능 100% 동일 제공</li>
                    <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>총 10건 견적 발행 (리셋 없음)</li>
                    <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>직원 등록 1명</li>
                    <li><i class="bi bi-check-circle-fill text-success me-2"></i>이메일 지원</li>
                </ul>
            </div>
        </div>

        <!-- STARTER -->
        <div class="col-lg-4 col-md-6">
            <div class="card pricing-card popular h-100 rounded-4 p-4 text-center position-relative">
                <span class="position-absolute top-0 start-50 translate-middle badge rounded-pill bg-danger px-3 py-2">가장 인기</span>
                <h4 class="fw-bold" style="color:#667eea;">STARTER</h4>
                <p class="text-muted small">소규모 업체용</p>
                <div class="price-display d-flex flex-column justify-content-center my-4">
                    <div id="price_starter" class="fw-bold fs-2">₩290,000</div>
                    <div id="vat_starter" class="vat-text">+ VAT ₩29,000</div>
                </div>
                <button class="btn btn-primary rounded-pill fw-bold w-100 mb-4 btn-checkout" data-plan="starter" data-base-price="290000" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">STARTER 구독하기</button>
                <ul class="list-unstyled text-start small mb-0">
                    <li class="mb-2"><i class="bi bi-check-circle-fill" style="color:#667eea; margin-right:8px;"></i>모든 기능 100% 동일 제공</li>
                    <li class="mb-2"><i class="bi bi-check-circle-fill" style="color:#667eea; margin-right:8px;"></i>월 30건 견적 발행 (30일 리셋)</li>
                    <li class="mb-2"><i class="bi bi-check-circle-fill" style="color:#667eea; margin-right:8px;"></i>직원 등록 무제한</li>
                    <li><i class="bi bi-check-circle-fill" style="color:#667eea; margin-right:8px;"></i>우선 지원 & 온보딩</li>
                </ul>
            </div>
        </div>

        <!-- PRO -->
        <div class="col-lg-4 col-md-6">
            <div class="card pricing-card h-100 rounded-4 p-4 text-center">
                <h4 class="fw-bold text-dark">PRO</h4>
                <p class="text-muted small">일반 업체용</p>
                <div class="price-display d-flex flex-column justify-content-center my-4">
                    <div id="price_pro" class="fw-bold fs-2">₩490,000</div>
                    <div id="vat_pro" class="vat-text">+ VAT ₩49,000</div>
                </div>
                <button class="btn btn-dark rounded-pill fw-bold w-100 mb-4 btn-checkout" data-plan="pro" data-base-price="490000">PRO 구독하기</button>
                <ul class="list-unstyled text-start small mb-0">
                    <li class="mb-2"><i class="bi bi-check-circle-fill text-dark me-2"></i>모든 기능 100% 동일 제공</li>
                    <li class="mb-2"><i class="bi bi-check-circle-fill text-dark me-2"></i><strong>견적 발행 무제한</strong></li>
                    <li class="mb-2"><i class="bi bi-check-circle-fill text-dark me-2"></i>직원 등록 무제한</li>
                    <li><i class="bi bi-check-circle-fill text-dark me-2"></i>24/7 전담 지원</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- 부트페이 JS -->
<script src="https://js.bootpay.co.kr/bootpay-5.3.0.min.js" type="application/javascript"></script>
<script>
const formatPrice = (num) => '₩' + num.toLocaleString();

// 할인율 설정
const discounts = {
    '1': 0,
    '3': 0.10, // 10%
    '6': 0.20, // 20%
    '12': 0.30 // 30%
};

let currentDuration = 1;

function updatePrices() {
    currentDuration = parseInt(document.querySelector('input[name="duration"]:checked').value);
    const discountRate = discounts[currentDuration];

    // STARTER
    const baseStarter = 290000;
    const finalStarter = baseStarter * currentDuration * (1 - discountRate);
    const vatStarter = finalStarter * 0.1;
    document.getElementById('price_starter').innerText = formatPrice(finalStarter);
    document.getElementById('vat_starter').innerText = '+ VAT ' + formatPrice(vatStarter);

    // PRO
    const basePro = 490000;
    const finalPro = basePro * currentDuration * (1 - discountRate);
    const vatPro = finalPro * 0.1;
    document.getElementById('price_pro').innerText = formatPrice(finalPro);
    document.getElementById('vat_pro').innerText = '+ VAT ' + formatPrice(vatPro);
}

document.querySelectorAll('input[name="duration"]').forEach(radio => {
    radio.addEventListener('change', updatePrices);
});

// 결제 버튼 이벤트
document.querySelectorAll('.btn-checkout').forEach(btn => {
    btn.addEventListener('click', async function() {
        const plan = this.dataset.plan; // 'starter' or 'pro'
        const basePrice = parseInt(this.dataset.basePrice);
        
        const discountRate = discounts[currentDuration];
        const finalPrice = basePrice * currentDuration * (1 - discountRate);
        const vat = finalPrice * 0.1;
        let totalPrice = finalPrice + vat;
        
        // [테스트용 임시 코드] 두목님의 요청으로 무조건 1004원 결제되게 세팅! (테스트 끝나면 지울게요!)
        totalPrice = 1004;
        
        let planName = plan === 'starter' ? 'STARTER' : 'PRO';
        let orderName = `${planName} 플랜 (${currentDuration}개월)`;

        // 유저 확인용 메시지 (부가세 포함)
        if (!confirm(`${orderName}\n결제 금액: ${formatPrice(finalPrice)} + VAT ${formatPrice(vat)} = 총 ${formatPrice(totalPrice)}\n결제를 진행하시겠습니까?`)) {
            return;
        }

        try {
            // 정기결제 시에는 빌링키만 따고 0원 결제 후 서버에서 실제 결제하는 방식이 안전하지만,
            // Bootpay v2 requestSubscription 은 PG사에 따라 가격을 넣으면 최초 결제 + 빌링키 발급을 동시에 해주기도 함.
            const response = await Bootpay.requestSubscription({
                client_key: '<?php echo $_ENV['BOOTPAY_CLIENT_KEY'] ?? $_SERVER['BOOTPAY_CLIENT_KEY'] ?? getenv('BOOTPAY_CLIENT_KEY'); ?>', 
                price: totalPrice,
                order_name: orderName,
                order_id: 'SUB_' + new Date().getTime(),
                subscription_id: plan + '_' + currentDuration + 'm_' + new Date().getTime(),
                pg: '나이스페이', // 실서비스 시 토스페이먼츠 등으로 변경
                user: {
                    id: '<?php echo $_SESSION['user']['id'] ?? 'guest'; ?>',
                    username: '<?php echo $_SESSION['user']['name'] ?? 'Guest'; ?>'
                },
                extra: {
                    separately_confirmed: true,
                    open_type: 'iframe',
                    redirect_url: 'https://cmake.work/vendor/payments'
                }
            });

            // 서버 승인 모델: 결제창에서 인증 완료 후 'confirm' 이벤트가 발생함
            if (response.event === 'confirm' || response.event === 'done') {
                // billing_key로 되어 있던 receipt_id를 백엔드로 전송
                // 백엔드에서 bootpayService->confirmPayment() 를 호출하여 실제 결제 확정
                const receiptId = response.receipt_id || (response.data && response.data.receipt_id) || response.billing_key;
                
                const res = await fetch('<?php echo CM_BASE_URL; ?>/api/bootpay/save-billing', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        billing_key: receiptId, 
                        plan_type: plan + '_' + currentDuration + 'm',
                        amount: totalPrice
                    })
                });
                const result = await res.json();
                if(result.success) {
                    alert('결제 및 정기구독이 성공적으로 등록되었습니다!\n다음 결제일: ' + result.next_payment_date);
                    window.location.href = '<?php echo CM_BASE_URL; ?>/vendor/payments';
                } else {
                    alert('서버 승인 실패: ' + result.message);
                }
            }
        } catch (e) {
            console.error(e);
            if (e.event === 'cancel') {
                console.log('사용자가 결제를 취소했습니다.');
            } else {
                alert('결제창 호출 중 오류가 발생했습니다: ' + (e.message || JSON.stringify(e)));
            }
        }
    });
});
</script>
<?php include_footer(); ?>
