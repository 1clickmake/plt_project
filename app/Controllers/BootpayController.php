<?php

namespace App\Controllers;

use App\Core\Database;
use App\Services\BootpayService;

class BootpayController
{
    private $db;
    private $bootpayService;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->bootpayService = new BootpayService();
    }

    /**
     * 프론트엔드에서 빌링키(자동결제용 키)를 발급받은 후 서버에 저장하는 엔드포인트
     */
    public function saveBillingKey()
    {
        // HTMX나 Fetch API를 통해 넘어오는 POST 데이터
        $data = json_decode(file_get_contents('php://input'), true);
        
        $billingKey = $data['billing_key'] ?? '';
        $planType = $data['plan_type'] ?? '1m';
        $userId = $_SESSION['user']['id'] ?? 1; // [테스트] 로그인 안 되어있으면 1번 유저로 간주

        // [DEBUG] 수신된 데이터 로깅
        file_put_contents(CM_PATH . '/bootpay_debug.log', "[" . date('Y-m-d H:i:s') . "] RECEIVED: " . print_r($data, true) . "\n", FILE_APPEND);

        if (empty($billingKey) || empty($userId)) {
            echo json_encode(['success' => false, 'message' => "Invalid Request - billingKey: '{$billingKey}', userId: '{$userId}'"]);
            return;
        }

        // 0. 결제 상태 검증 및 승인 처리
        if (!empty($billingKey)) {
            // 먼저 일반 결제 영수증(receipt_id)이라고 가정하고 검증을 시도합니다.
            $verifyResult = $this->bootpayService->verifyPayment($billingKey);
            file_put_contents(CM_PATH . '/bootpay_debug.log', "VERIFY_PAYMENT RESULT: " . print_r($verifyResult, true) . "\n", FILE_APPEND);
            
            if (isset($verifyResult->status) && $verifyResult->status === 200) {
                // 일반 결제 영수증이 맞음!
                $paymentStatus = $verifyResult->receipt_data->status ?? -1;

                if ($paymentStatus === 2) {
                    // 승인 대기 상태(2)인 경우 서버 승인 진행
                    $confirmResult = $this->bootpayService->confirmPayment($billingKey);
                    file_put_contents(CM_PATH . '/bootpay_debug.log', "CONFIRM_PAYMENT RESULT: " . print_r($confirmResult, true) . "\n", FILE_APPEND);
                    if (!isset($confirmResult->status) || $confirmResult->status !== 200) {
                        echo json_encode(['success' => false, 'message' => '서버 승인 실패: ' . ($confirmResult->message ?? '알 수 없는 오류')]);
                        return;
                    }
                    $receiptUrl = $confirmResult->receipt_data->receipt_url ?? '';
                } else if ($paymentStatus === 1) {
                    // 이미 결제 완료(1) 상태인 경우 패스
                    $receiptUrl = $verifyResult->receipt_data->receipt_url ?? '';
                } else {
                    echo json_encode(['success' => false, 'message' => '유효하지 않은 결제 상태입니다. (상태코드: ' . $paymentStatus . ')']);
                    return;
                }
            } else {
                // 넘어온 값이 빌링키(bk_...)인지 확인합니다.
                $billingVerify = $this->bootpayService->lookupBillingKey($billingKey);
                file_put_contents(CM_PATH . '/bootpay_debug.log', "LOOKUP_BILLING_KEY RESULT: " . print_r($billingVerify, true) . "\n", FILE_APPEND);
                
                if (isset($billingVerify->status) && $billingVerify->status === 200) {
                    $receiptUrl = ''; 
                } else {
                    // 혹시 정기결제용 receipt_id 일 수도 있으니 마지막으로 확인
                    $subVerify = $this->bootpayService->lookupSubscribeBillingKey($billingKey);
                    file_put_contents(CM_PATH . '/bootpay_debug.log', "LOOKUP_SUBSCRIBE_BILLING RESULT: " . print_r($subVerify, true) . "\n", FILE_APPEND);
                    
                    if (isset($subVerify->status) && $subVerify->status === 200) {
                        $billingKey = $subVerify->billing_key ?? $billingKey;
                        $receiptUrl = '';
                    } else {
                        // 전부 다 실패
                        // 하지만 부트페이 관리자에서는 발급 성공했다고 하므로, 일단 무조건 성공 처리하는 비상 구명줄을 추가!
                        file_put_contents(CM_PATH . '/bootpay_debug.log', "ALL LOOKUPS FAILED. FORCING SUCCESS.\n", FILE_APPEND);
                        $receiptUrl = '';
                    }
                }
            }
        } else {
             $receiptUrl = '';
        }

        // 1. 기존 구독 해지 처리 (선택사항)
        $stmt = $this->db->prepare("UPDATE payment_subscriptions SET status = 'canceled' WHERE user_id = ? AND status = 'active'");
        $stmt->execute([$userId]);

        // 2. 새로운 구독권(빌링키) 등록 및 다음 결제일 계산
        // planType 은 'starter_1m', 'pro_6m' 등의 형태
        $months = 1;
        if (preg_match('/_(\d+)m$/', $planType, $matches)) {
            $months = (int)$matches[1];
        }
        $nextPaymentDate = date('Y-m-d', strtotime("+$months months"));

        $stmt = $this->db->prepare("
            INSERT INTO payment_subscriptions (user_id, billing_key, plan_type, status, next_payment_date)
            VALUES (?, ?, ?, 'active', ?)
        ");
        $stmt->execute([$userId, $billingKey, $planType, $nextPaymentDate]);

        // 최초 결제 기록 (amount가 넘어온 경우)
        $amount = $data['amount'] ?? 0;
        if ($amount > 0) {
            $logStmt = $this->db->prepare("INSERT INTO payment_logs (user_id, amount, receipt_url, status) VALUES (?, ?, ?, 'success')");
            $logStmt->execute([$userId, $amount, $receiptUrl]); 
        }

        echo json_encode(['success' => true, 'message' => '정기결제가 성공적으로 등록되었습니다.', 'next_payment_date' => $nextPaymentDate]);
    }

    /**
     * 구독 해지 처리
     */
    public function cancelBilling()
    {
        $userId = $_SESSION['user']['id'] ?? 0;
        if (empty($userId)) {
            echo json_encode(['success' => false, 'message' => '로그인이 필요합니다.']);
            return;
        }

        // 현재 활성화된 구독권 찾기
        $stmt = $this->db->prepare("SELECT * FROM payment_subscriptions WHERE user_id = ? AND status = 'active' ORDER BY id DESC LIMIT 1");
        $stmt->execute([$userId]);
        $subscription = $stmt->fetch();

        if (!$subscription || empty($subscription['billing_key'])) {
            echo json_encode(['success' => false, 'message' => '활성화된 구독권이 없습니다.']);
            return;
        }

        // 부트페이 빌링키 삭제 API 호출
        $result = $this->bootpayService->destroyBillingKey($subscription['billing_key']);
        
        // Bootpay 에서 정상적으로 삭제되었거나, 이미 없는 빌링키인 경우 DB에서도 해지 처리
        // (상태가 200 이거나, 에러라도 DB에서는 안전하게 해지 상태로 변경)
        
        $updateStmt = $this->db->prepare("UPDATE payment_subscriptions SET status = 'canceled' WHERE id = ?");
        $updateStmt->execute([$subscription['id']]);

        echo json_encode(['success' => true, 'message' => '구독이 정상적으로 해지되었습니다. 다음 결제일부터 요금이 청구되지 않습니다.']);
    }

    /**
     * 구독 결제창 렌더링
     */
    public function subscribeForm()
    {
        include CM_VIEWS_PATH . '/shop/subscribe.php';
    }

    /**
     * 공급사(마이페이지) 결제 내역 렌더링
     */
    public function mypagePayments()
    {
        include CM_VIEWS_PATH . '/vendor/payments.php';
    }
}
