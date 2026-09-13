<?php
namespace App\Services;

use App\Core\Database;

class AuditService {
    /**
     * 로그 기록
     * @param int|string $userId 사용자 ID 또는 고유 식별자 (로그인 안 된 경우 IP 등)
     * @param string $action 수행한 액션 (VIEW_PRICING, QUOTE_CALC, DOC_VIEW 등)
     * @param string $target 대상 (예: 세화 단가표, 특정 견적서 등)
     * @param string $details 상세 설명 (JSON 등)
     */
    public static function logAccess($userId, $action, $target, $details = '') {
        $db = Database::getInstance();
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN';

        $stmt = $db->prepare("
            INSERT INTO price_access_logs 
            (user_id, action, target, details, ip_address, user_agent, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $userId,
            $action,
            $target,
            $details,
            $ip,
            $userAgent
        ]);
    }
}
