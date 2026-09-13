<?php
namespace App\Services;

use App\Core\Database;

class AuditService {
    /**
     * 디지털 감사 검증 토큰 생성 (HMAC-SHA256)
     * 위변조 방지 및 1:1 대조용 지문
     */
    public static function generateToken($quoteId, $userId) {
        $salt = $_ENV['AUDIT_SALT'] ?? 'fallback_salt_asamiya';
        $raw = hash_hmac('sha256', "{$quoteId}|{$userId}", $salt);
        return strtoupper(substr($raw, 0, 4) . '-' . substr($raw, 4, 4) . '-' . substr($raw, 8, 4));
    }

    /**
     * 단가 접근 및 견적 열람 감사 로그 기록
     * @param int|string $userId 사용자 ID 또는 사번
     * @param string $action 수행 액션
     * @param string $target 대상 (공급사명 또는 견적서 ID)
     * @param string $details 상세 내역
     * @param string|null $auditToken 법적 감사 검증 토큰
     */
    public static function logAccess($userId, $action, $target, $details = '', $auditToken = null) {
        $db = Database::getInstance();
        if (!$db) return;

        $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN';

        $stmt = $db->prepare("
            INSERT INTO price_access_logs 
            (user_id, action, target, details, audit_token, ip_address, user_agent, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $userId,
            $action,
            $target,
            $details,
            $auditToken,
            $ip,
            $userAgent
        ]);
    }
}
