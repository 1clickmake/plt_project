<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>공급사 관리 센터 - 발송 한도 충전</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- External Vendor Dashboard CSS -->
    <link href="/css/vendor_dashboard.css" rel="stylesheet">
</head>
<body>

    <!-- 좌측 네비게이션 사이드바 -->
    <?php include __DIR__ . '/sidebar.php'; ?>

    <!-- 우측 메인 대시보드 영역 -->
    <main class="main-content">
        <div class="top-navbar">
            <div class="navbar-title fw-bold text-light" style="font-size: 1.1rem;">
                SaaS Dashboard &gt; 발송 한도 충전
            </div>
            <div class="d-flex align-items-center gap-3">
                <a href="/vendor" class="btn btn-outline-secondary btn-sm rounded px-3" style="font-size:0.8rem; border-color: rgba(255,255,255,0.15); color:#cbd5e1;">
                    ◀ 대시보드로 돌아가기
                </a>
    <div class="user-profile d-flex align-items-center gap-2">
                <?php
                    $dbBtn = \App\Core\Database::getInstance();
                    $stmtBtn = $dbBtn->prepare("SELECT plan FROM users WHERE user_id = ?");
                    $stmtBtn->execute([$user['user_id']]);
                    $btnPlan = $stmtBtn->fetchColumn();
                    if ($btnPlan !== 'pro'):
                ?>
                <a href="/vendor/addon_payment" class="btn btn-outline-warning btn-sm fw-bold px-3 py-1 me-3" style="border-radius: 10px;">
                    <i class="fa-solid fa-bolt"></i> 횟수 충전
                </a>
                <?php endif; ?>
                    <i class="fa-solid fa-circle-user text-info fs-5"></i>
                    <span class="small font-monospace text-light"><?= htmlspecialchars($user['username'] ?? 'User') ?>님</span>
                </div>
            </div>
        </div>

        <div class="content-body">
            <div class="row justify-content-center">
                <div class="col-lg-6 mt-4">
                    <div class="card pricing-card h-100 rounded-4 p-4 text-center border-warning" style="border-width: 2px; background: rgba(15, 23, 42, 0.9);">
                        <div class="mb-3">
                            <i class="fa-solid fa-bolt text-warning" style="font-size: 3rem;"></i>
                        </div>
                        <h4 class="fw-bold text-white">견적 발송 10건 충전</h4>
                        <p class="text-light small">기본 제공량을 모두 소진하셨나요?<br>추가로 결제하신 건수는 <strong>영구적으로 누적(이월)</strong>됩니다.</p>
                        
                        <div class="price-display d-flex flex-column justify-content-center my-4">
                            <div id="price_addon" class="fw-bold fs-2 text-warning">₩150,000</div>
                            <div class="vat-text text-light">+ VAT ₩15,000</div>
                        </div>
                        
                        <button class="btn btn-warning rounded-pill fw-bold w-100 mb-4 btn-checkout text-dark fs-5 py-2">결제하기 (총 ₩165,000)</button>
                        
                        <ul class="list-unstyled text-start small mb-0 px-3 text-light">
                            <li class="mb-2"><i class="fa-solid fa-check text-warning me-2"></i>발송 한도 10건 즉시 추가</li>
                            <li class="mb-2"><i class="fa-solid fa-check text-warning me-2"></i>사용 기한 제한 없음 (평생 이월)</li>
                            <li><i class="fa-solid fa-check text-warning me-2"></i>기본 한도(무료/스타터) 소진 후 자동 차감</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </main>

<script src="https://js.bootpay.co.kr/bootpay-5.3.0.min.js" type="application/javascript"></script>
<script>
document.querySelector('.btn-checkout').addEventListener('click', async function() {
    const finalPrice = 150000;
    const vat = 15000;
    let totalPrice = finalPrice + vat;
    
    // 테스트 결제 금액
    totalPrice = 1004;

    const orderName = "견적 발송 10건 추가 충전";

    if (!confirm(`${orderName}\n결제 금액: ₩${finalPrice.toLocaleString()} + VAT ₩${vat.toLocaleString()} = 총 ₩${(finalPrice + vat).toLocaleString()}\n결제를 진행하시겠습니까?`)) {
        return;
    }

    try {
        const response = await Bootpay.requestPayment({
            client_key: '<?php echo $_ENV['BOOTPAY_CLIENT_KEY'] ?? $_SERVER['BOOTPAY_CLIENT_KEY'] ?? getenv('BOOTPAY_CLIENT_KEY') ?: 'AO3zL7T88bz6VHObNnnK1g'; ?>',
            price: totalPrice,
            order_name: orderName,
            order_id: 'ADDON_' + new Date().getTime(),
            pg: '나이스페이',
            method: '카드',
            tax_free: 0,
            user: {
                id: '<?php echo $user['user_id'] ?? $user['id'] ?? 'guest'; ?>',
                username: '<?php echo $user['username'] ?? $user['name'] ?? 'Guest'; ?>',
                phone: '<?php echo $user['phone'] ?? ''; ?>',
                email: '<?php echo $user['email'] ?? ''; ?>'
            },
            items: [
                {
                    id: 'addon_10_quotes',
                    name: '견적 발송 10건 추가',
                    qty: 1,
                    price: totalPrice
                }
            ],
            extra: {
                open_type: 'iframe',
                escrow: false
            }
        });

        if (response.event === 'done') {
            const receiptId = response.receipt_id || (response.data && response.data.receipt_id);
            console.log('Bootpay done - receiptId:', receiptId, 'amount:', totalPrice);
            
            const res = await fetch('/api/bootpay/verify-addon', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    receipt_id: receiptId,
                    amount: totalPrice
                })
            });
            const result = await res.json();
            if (result.success) {
                alert('결제가 성공적으로 완료되어 발송 한도 10건이 충전되었습니다!');
                window.location.href = '/vendor';
            } else {
                alert('결제 검증 실패: ' + result.message);
            }
        }
    } catch (e) {
        if (e.event === 'cancel') {
            console.log('사용자가 결제를 취소했습니다.');
        } else {
            console.error(e);
            alert('결제창 호출 중 오류가 발생했습니다.\n에러 내용: ' + (e.message || JSON.stringify(e)));
        }
    }
});
</script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
