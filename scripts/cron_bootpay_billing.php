<?php
/**
 * 매일 자정에 실행되는 정기결제 스케줄러 (Cronjob)
 * 실행 방법: php scripts/cron_bootpay_billing.php
 */
require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Services/BootpayService.php';

use App\Core\Database;
use App\Services\BootpayService;

$db = Database::getInstance();
if (!$db) {
    die("DB connection failed.\n");
}

$bootpayService = new BootpayService();

// 오늘 날짜가 다음 결제일(next_payment_date)이거나 지난 구독권 찾기
$today = date('Y-m-d');
$stmt = $db->prepare("SELECT * FROM payment_subscriptions WHERE status = 'active' AND next_payment_date <= ?");
$stmt->execute([$today]);
$subscriptions = $stmt->fetchAll();

echo "[" . date('Y-m-d H:i:s') . "] 부트페이 정기결제 크론 시작... (대상자: " . count($subscriptions) . "명)\n";

foreach ($subscriptions as $sub) {
    try {
        $userId = $sub['user_id'];
        $billingKey = $sub['billing_key'];
        $planType = $sub['plan_type']; // e.g., 'starter_3m', 'pro_12m'
        
        // 플랜 파싱
        $months = 1;
        $planName = 'STARTER';
        $basePrice = 290000;
        
        if (strpos($planType, 'pro') !== false) {
            $planName = 'PRO';
            $basePrice = 490000;
        }
        if (preg_match('/_(\d+)m$/', $planType, $matches)) {
            $months = (int)$matches[1];
        }

        // 할인율
        $discountRate = 0;
        if ($months == 3) $discountRate = 0.10;
        if ($months == 6) $discountRate = 0.20;
        if ($months == 12) $discountRate = 0.30;

        $finalPrice = $basePrice * $months * (1 - $discountRate);
        $vat = $finalPrice * 0.1;
        $totalPrice = $finalPrice + $vat;

        $orderName = "{$planName} 플랜 ({$months}개월 연장)";
        $orderId = 'SUB_' . $sub['id'] . '_' . time();
        
        // 결제 요청
        $response = $bootpayService->requestSubscribe($billingKey, $orderName, $totalPrice, $orderId, [
            'id' => $userId
        ]);

        if (isset($response->status) && $response->status === 200) {
            // 결제 성공
            $receiptUrl = $response->data->receipt_url ?? '';
            
            // 1. payment_logs 영수증 기록
            $logStmt = $db->prepare("INSERT INTO payment_logs (user_id, amount, receipt_url, status) VALUES (?, ?, ?, 'success')");
            $logStmt->execute([$userId, $totalPrice, $receiptUrl]);

            // 2. 만료일 연장
            $nextDate = date('Y-m-d', strtotime("+$months months", strtotime($sub['next_payment_date'])));

            $updateStmt = $db->prepare("UPDATE payment_subscriptions SET next_payment_date = ? WHERE id = ?");
            $updateStmt->execute([$nextDate, $sub['id']]);
            
            echo " - 유저 #$userId 결제 성공! 다음 결제일: $nextDate\n";
        } else {
            // 결제 실패 (잔액 부족 등)
            throw new Exception($response->message ?? '결제 요청 거절됨');
        }
    } catch (Exception $e) {
        $errorMsg = $e->getMessage();
        echo " - 유저 #{$sub['user_id']} 결제 실패: $errorMsg\n";

        // 실패 기록
        $logStmt = $db->prepare("INSERT INTO payment_logs (user_id, amount, status, error_msg) VALUES (?, ?, 'failed', ?)");
        $logStmt->execute([$sub['user_id'], $price ?? 0, $errorMsg]);

        // 두목님 정책: 결제 실패 시 즉시 구독 해지!
        $updateStmt = $db->prepare("UPDATE payment_subscriptions SET status = 'failed' WHERE id = ?");
        $updateStmt->execute([$sub['id']]);
    }
}

echo "[" . date('Y-m-d H:i:s') . "] 크론 종료.\n";
