<?php

namespace App\Services;

use Bootpay\ServerPhp\BootpayApi;
use Exception;

class BootpayService
{
    public function __construct()
    {
        $clientId = $_ENV['BOOTPAY_CLIENT_KEY'] ?? $_SERVER['BOOTPAY_CLIENT_KEY'] ?? getenv('BOOTPAY_CLIENT_KEY');
        $clientSecret = $_ENV['BOOTPAY_SECRET_KEY'] ?? $_SERVER['BOOTPAY_SECRET_KEY'] ?? getenv('BOOTPAY_SECRET_KEY');
        
        BootpayApi::setClientKeyConfiguration($clientId, $clientSecret);
    }

    /**
     * 부트페이 액세스 토큰 발급
     */
    public function getAccessToken()
    {
        $response = BootpayApi::getAccessToken();
        if (isset($response->status) && $response->status === 200) {
            return $response->access_token ?? '';
        }
        // $response is a stdClass from API
        return $response;
    }

    /**
     * 빌링키를 이용한 정기결제(자동연장) 요청
     */
    public function requestSubscribe($billingKey, $orderName, $price, $orderId, $user)
    {
        $this->getAccessToken();

        return BootpayApi::requestSubscribePayment([
            'billing_key' => $billingKey,
            'order_name'  => $orderName,
            'order_id'    => $orderId,
            'price'       => $price,
            'user_info'   => [
                'id'       => $user['id'] ?? '',
                'username' => $user['username'] ?? 'User',
                'email'    => $user['email'] ?? '',
            ]
        ]);
    }

    /**
     * 빌링키 삭제 (구독 해지 시)
     */
    public function destroyBillingKey($billingKey)
    {
        $this->getAccessToken();
        return BootpayApi::destroyBillingKey($billingKey);
    }

    /**
     * 단건 결제 검증
     */
    public function verifyPayment($receiptId)
    {
        $this->getAccessToken();
        return BootpayApi::receiptPayment($receiptId);
    }

    /**
     * 서버 승인 (클라이언트에서 confirm 이벤트 발생 시 서버에서 승인 처리)
     */
    public function confirmPayment($receiptId)
    {
        $this->getAccessToken();
        return BootpayApi::confirmPayment($receiptId);
    }

    /**
     * 정기결제 빌링키 조회 (receipt_id 기반)
     */
    public function lookupSubscribeBillingKey($receiptId)
    {
        $this->getAccessToken();
        return BootpayApi::lookupSubscribeBillingKey($receiptId);
    }

    /**
     * 발급된 빌링키 조회 (billing_key 기반)
     */
    public function lookupBillingKey($billingKey)
    {
        $this->getAccessToken();
        return BootpayApi::lookupBillingKey($billingKey);
    }
}
