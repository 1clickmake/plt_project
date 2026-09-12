<?php
if (empty($siteConfig)) {
    try {
        $db = \App\Core\Database::getInstance();
        if ($db) {
            $siteConfig = $db->query("SELECT * FROM config WHERE id = 1")->fetch() ?: [];
        }
    } catch (\Exception $e) {
        $siteConfig = [];
    }
}
$title = '요금제 및 구독 결제';
include_header($title, $siteConfig ?? []);
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
        <div class="col-lg-5 col-md-6">
            <div class="card pricing-card h-100 rounded-4 p-4 text-center">
                <h4 class="fw-bold">FREE</h4>
                <p class="text-muted small">솔루션 체험용</p>
                <div class="price-display d-flex flex-column justify-content-center my-4">
                    <h2 class="fw-bold mb-0">무료</h2>
                </div>
                <button class="btn btn-light rounded-pill fw-bold w-100 mb-4" disabled>현재 이용중</button>
                <ul class="list-unstyled text-start small mb-0">
                    <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>모든 기능 100% 동일 제공</li>
                    <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>총 200건 견적 발행</li>
                    <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>직원 등록 3명</li>
                    <li><i class="bi bi-check-circle-fill text-success me-2"></i>1달(30일)만 이용 가능</li>
                </ul>
            </div>
        </div>

        <!-- PRO -->
        <div class="col-lg-5 col-md-6">
            <div class="card pricing-card popular h-100 rounded-4 p-4 text-center position-relative">
                <span class="position-absolute top-0 start-50 translate-middle badge rounded-pill bg-danger px-3 py-2">가장 인기</span>
                <h4 class="fw-bold" style="color:#667eea;">PRO</h4>
                <p class="text-muted small">전문 업체용</p>
                <div class="price-display d-flex flex-column justify-content-center my-4">
                    <div id="price_pro" class="fw-bold fs-2">₩220,000</div>
                    <div id="vat_pro" class="vat-text">+ VAT ₩22,000</div>
                </div>
                <button class="btn btn-primary rounded-pill fw-bold w-100 mb-4 btn-checkout" data-plan="pro" data-base-price="220000" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">PRO 구독하기</button>
                <ul class="list-unstyled text-start small mb-0">
                    <li class="mb-2"><i class="bi bi-check-circle-fill" style="color:#667eea; margin-right:8px;"></i>모든 기능 100% 동일 제공</li>
                    <li class="mb-2"><i class="bi bi-check-circle-fill" style="color:#667eea; margin-right:8px;"></i><strong>견적 발행 무제한</strong></li>
                    <li class="mb-2"><i class="bi bi-check-circle-fill" style="color:#667eea; margin-right:8px;"></i>직원 등록 무제한</li>
                    <li><i class="bi bi-check-circle-fill" style="color:#667eea; margin-right:8px;"></i>24/7 전담 지원</li>
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
    const checkedRadio = document.querySelector('input[name="duration"]:checked');
    currentDuration = checkedRadio ? parseInt(checkedRadio.value) : 1;
    const discountRate = discounts[currentDuration] || 0;

    // PRO 플랜 (기본 월 220,000원)
    const basePro = 220000;
    const originalPro = basePro * currentDuration;
    const finalPro = Math.round(originalPro * (1 - discountRate));
    const vatPro = Math.round(finalPro * 0.1);
    const totalPro = finalPro + vatPro;

    const priceEl = document.getElementById('price_pro');
    const vatEl = document.getElementById('vat_pro');

    if (priceEl) {
        priceEl.innerText = formatPrice(finalPro);
    }
    if (vatEl) {
        let vatHtml = `+ VAT ${formatPrice(vatPro)} <span class="fw-bold text-dark">(총 ${formatPrice(totalPro)})</span>`;
        if (currentDuration > 1) {
            const monthlyPrice = Math.round(finalPro / currentDuration);
            const savedAmount = originalPro - finalPro;
            vatHtml += `<div class="mt-1 text-primary fw-bold" style="font-size: 0.85rem;">
                <i class="bi bi-tag-fill me-1"></i>월 ${formatPrice(monthlyPrice)} (${Math.round(discountRate * 100)}% 할인 / ${formatPrice(savedAmount)} 절약)
            </div>`;
        }
        vatEl.innerHTML = vatHtml;
    }
}

document.querySelectorAll('input[name="duration"]').forEach(radio => {
    radio.addEventListener('change', updatePrices);
});

// 페이지 로드 시 즉시 1회 계산 실행
document.addEventListener('DOMContentLoaded', updatePrices);
updatePrices();

// 결제 버튼 이벤트
document.querySelectorAll('.btn-checkout').forEach(btn => {
    btn.addEventListener('click', async function() {
        const plan = this.dataset.plan || 'pro';
        const basePrice = parseInt(this.dataset.basePrice) || 220000;
        
        const discountRate = discounts[currentDuration] || 0;
        const finalPrice = Math.round(basePrice * currentDuration * (1 - discountRate));
        const vat = Math.round(finalPrice * 0.1);
        const totalPrice = finalPrice + vat;
        
        let planName = 'PRO';
        let orderName = `${planName} 플랜 (${currentDuration}개월)`;

        // 유저 확인용 메시지 (부가세 포함 실제 결제 금액)
        const confirmMsg = `${orderName}\n` +
            `공급가액: ${formatPrice(finalPrice)}\n` +
            `부가세(VAT): ${formatPrice(vat)}\n` +
            `최종 결제 금액: ${formatPrice(totalPrice)}\n\n` +
            `결제를 진행하시겠습니까?`;

        if (!confirm(confirmMsg)) {
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
<?php include_footer($siteConfig ?? []); ?>
