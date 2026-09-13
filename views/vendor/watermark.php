<?php
/**
 * B2B 보안 무기: 스텔스 워터마크 (Stealth Screen Watermark)
 * 설계: 아사미야 & 하이디
 * 투명도: 3.5% (스텔스 은닉형 - 평소에는 눈 피로도 0, 캡처/대비 조절 시 선명하게 검출)
 */
$wm_user = $user['username'] ?? ($_SESSION['user_id'] ?? 'USER_GUEST');
$wm_ip   = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
$wm_time = date('Y-m-d H:i');
$wm_text = "CONFIDENTIAL • {$wm_user} • {$wm_ip} • {$wm_time}";

// SVG 인라인 생성 (사선 타일 패턴)
$svg_pattern = '<svg xmlns="http://www.w3.org/2000/svg" width="340" height="180">' .
    '<text x="20" y="100" fill="#ffffff" font-size="13" font-family="sans-serif" font-weight="600" ' .
    'transform="rotate(-25 170 90)" opacity="0.9">' .
    htmlspecialchars($wm_text, ENT_QUOTES, 'UTF-8') .
    '</text></svg>';
$svg_base64 = base64_encode($svg_pattern);
?>
<!-- 🛡️ B2B Stealth Security Watermark Overlay -->
<div id="asamiya-stealth-watermark" style="
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    pointer-events: none !important;
    z-index: 999999 !important;
    background-image: url('data:image/svg+xml;base64,<?= $svg_base64 ?>');
    background-repeat: repeat;
    opacity: 0.035;
    mix-blend-mode: difference;
    user-select: none;
"></div>
