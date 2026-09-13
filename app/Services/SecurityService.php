<?php

namespace App\Services;

class SecurityService
{
    /**
     * 암호화에 사용할 256비트 바이너리 키 도출
     */
    private static function getKey(): string
    {
        $rawKey = $_ENV['ENCRYPTION_KEY'] ?? 'plt_secret_master_key_2026_asamiya_love';
        return hash('sha256', $rawKey, true);
    }

    /**
     * 평문(JSON 등)을 AES-256-CBC로 암호화하고 DB JSON 타입 제약조건에 안전한 JSON 래퍼로 반환
     */
    public static function encrypt(string $plainText): string
    {
        if ($plainText === '') {
            return '';
        }

        $key = self::getKey();
        $ivLength = openssl_cipher_iv_length('aes-256-cbc');
        $iv = openssl_random_pseudo_bytes($ivLength);

        $ciphertext = openssl_encrypt($plainText, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
        $payload = base64_encode($iv . $ciphertext);

        // MariaDB CHECK (json_valid(`pricing_data`)) 제약조건 100% 호환
        return json_encode([
            '_encrypted' => true,
            'algorithm'  => 'AES-256-CBC',
            'data'       => $payload,
            'timestamp'  => time()
        ], JSON_UNESCAPED_UNICODE);
    }

    /**
     * 암호화된 JSON 래퍼를 복호화. 평문(구버전 데이터)인 경우 그대로 반환 (하위 호환성)
     */
    public static function decrypt(string $data): string
    {
        if (empty($data)) {
            return '';
        }

        $decoded = json_decode($data, true);

        // 암호화된 형식인지 확인
        if (is_array($decoded) && !empty($decoded['_encrypted']) && !empty($decoded['data'])) {
            $key = self::getKey();
            $raw = base64_decode($decoded['data']);
            $ivLength = openssl_cipher_iv_length('aes-256-cbc');

            if (strlen($raw) > $ivLength) {
                $iv = substr($raw, 0, $ivLength);
                $ciphertext = substr($raw, $ivLength);
                $decrypted = openssl_decrypt($ciphertext, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
                if ($decrypted !== false) {
                    return $decrypted;
                }
            }
        }

        // 구버전 평문 JSON 또는 복호화 불필요 데이터
        return $data;
    }

    /**
     * 단일 값(단가 숫자 등)을 AES-256-CBC로 암호화하여 'ENC:base64' 포맷으로 반환
     */
    public static function encryptValue($value): string
    {
        if ($value === null || $value === '') {
            return '';
        }
        $plain = (string)$value;
        $key = self::getKey();
        $ivLength = openssl_cipher_iv_length('aes-256-cbc');
        $iv = openssl_random_pseudo_bytes($ivLength);
        $ciphertext = openssl_encrypt($plain, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
        return 'ENC:' . base64_encode($iv . $ciphertext);
    }

    /**
     * 'ENC:base64'로 암호화된 값을 복호화. 기존 평문 숫자/문자열이면 그대로 반환 (하위 호환성)
     */
    public static function decryptValue($value): string
    {
        if (empty($value) || !is_string($value)) {
            return (string)$value;
        }
        if (str_starts_with($value, 'ENC:')) {
            $raw = base64_decode(substr($value, 4));
            $key = self::getKey();
            $ivLength = openssl_cipher_iv_length('aes-256-cbc');
            if (strlen($raw) > $ivLength) {
                $iv = substr($raw, 0, $ivLength);
                $ciphertext = substr($raw, $ivLength);
                $decrypted = openssl_decrypt($ciphertext, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
                if ($decrypted !== false) {
                    return $decrypted;
                }
            }
        }
        return (string)$value;
    }
}

