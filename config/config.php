<?php
/**
 * Global Configuration & Constants
 */

// Root Path (Absolute path to the project root)
define('CM_PATH', dirname(__DIR__));

// Directory Names
define('CM_ADMIN_DIR',   'admin');
define('CM_APP_DIR',     'app');
define('CM_CONFIG_DIR',  'config');
define('CM_LIB_DIR',     'lib');
define('CM_PUBLIC_DIR',  'public');
define('CM_VIEWS_DIR',   'views');
define('CM_DATA_DIR',    'data');
define('CM_PLUGINS_DIR', 'plugins');

// Absolute Path Constants
define('CM_APP_PATH',       CM_PATH . '/' . CM_APP_DIR);
define('CM_LIB_PATH',       CM_PATH . '/' . CM_LIB_DIR);
define('CM_VIEWS_PATH',     CM_PATH . '/' . CM_VIEWS_DIR);
define('CM_PUBLIC_PATH',    CM_PATH . '/' . CM_PUBLIC_DIR);
define('CM_CONFIG_PATH',    CM_PATH . '/' . CM_CONFIG_DIR);
define('CM_DATA_PATH',      CM_PUBLIC_PATH . '/' . CM_DATA_DIR);
define('CM_ASSET_PATH',     CM_PUBLIC_PATH . '/assets');
define('CM_PLUGINS_PATH',   CM_PATH . '/' . CM_PLUGINS_DIR);

// Layout & Template Paths
define('CM_LAYOUT_PATH',      CM_VIEWS_PATH . '/layout');
define('CM_TEMPLATE_PATH',    CM_VIEWS_PATH . '/templates');
define('CM_BOARD_SKINS_PATH', CM_VIEWS_PATH . '/board/skins');

// URL Constants
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$baseUrl = str_replace('\\', '/', dirname($scriptName));
if ($baseUrl === '/' || $baseUrl === '.') $baseUrl = '';

define('CM_BASE_URL', $baseUrl);
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443 || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')) ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
define('CM_SITE_URL', $protocol . '://' . $host . CM_BASE_URL);

define('CM_ADMIN_URL',  CM_BASE_URL . '/admin');
define('CM_DATA_URL',   CM_BASE_URL . '/data'); // Removed /public as .htaccess handles it
define('CM_ASSET_URL',  CM_BASE_URL . '/assets'); // Removed /public as .htaccess handles it
define('CM_BOARD_SKINS_URL', CM_BASE_URL . '/views/board/skins');

/**
 * 템플릿 전용 에셋(CSS/JS) 경로를 가져옵니다.
 * @param string $file 파일명 (예: style.css, script.js)
 * @param string $template 템플릿명 (생략시 현재 설정된 템플릿)
 * @return string URL 주소
 */
function get_template_asset($file, $config = []) {
    if (is_array($config)) {
        $template = $config['template'] ?? 'basic';
    } else {
        $template = $config ?: 'basic';
    }
    return CM_ASSET_URL . '/templates/' . $template . '/' . $file;
}

/**
 * 템플릿 전용 에셋 파일이 실제로 존재하는지 확인하고 HTML 태그를 반환합니다.
 */
function load_template_assets($config = []) {
    if (is_array($config)) {
        $template = $config['template'] ?? 'basic';
    } else {
        $template = $config ?: 'basic';
    }

    $html = '';
    $asset_path = CM_ASSET_PATH . '/templates/' . $template;
    $asset_url = CM_ASSET_URL . '/templates/' . $template;

    // 1. 루트 폴더의 style.css 체크 (기본 파일)
    if (file_exists($asset_path . '/style.css')) {
        $html .= '<link rel="stylesheet" href="' . $asset_url . '/style.css?v=' . filemtime($asset_path . '/style.css') . '">' . PHP_EOL;
    }

    // 2. /css 폴더 내의 모든 .css 파일 자동 로드
    $css_dir = $asset_path . '/css';
    if (is_dir($css_dir)) {
        $files = scandir($css_dir);
        foreach ($files as $file) {
            // style.css가 루트에 이미 있다면 중복 로드 방지 (원할 경우 제외 가능)
            if (pathinfo($file, PATHINFO_EXTENSION) === 'css') {
                $html .= '<link rel="stylesheet" href="' . $asset_url . '/css/' . $file . '?v=' . filemtime($css_dir . '/' . $file) . '">' . PHP_EOL;
            }
        }
    }

    return $html;
}

/**
 * 템플릿 전용 JS 파일을 로드합니다.
 */
function load_template_scripts($config = []) {
    if (is_array($config)) {
        $template = $config['template'] ?? 'basic';
    } else {
        $template = $config ?: 'basic';
    }

    $html = '';
    $asset_path = CM_ASSET_PATH . '/templates/' . $template;
    $asset_url = CM_ASSET_URL . '/templates/' . $template;

    // 1. 루트 폴더의 script.js 체크
    if (file_exists($asset_path . '/script.js')) {
        $html .= '<script src="' . $asset_url . '/script.js?v=' . filemtime($asset_path . '/script.js') . '"></script>' . PHP_EOL;
    }

    // 2. /js 폴더 내의 모든 .js 파일 자동 로드
    $js_dir = $asset_path . '/js';
    if (is_dir($js_dir)) {
        $files = scandir($js_dir);
        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'js') {
                $html .= '<script src="' . $asset_url . '/js/' . $file . '?v=' . filemtime($js_dir . '/' . $file) . '"></script>' . PHP_EOL;
            }
        }
    }

    return $html;
}

// Helper for including Header/Footer based on template
function include_header($title = '', $siteConfig = []) {
    global $is_member, $is_guest, $is_super, $is_admin, $user, $csrf_token;
    $template = $siteConfig['template'] ?? 'basic';
    $headerPath = CM_TEMPLATE_PATH . '/' . $template . '/header.php';
    if (!file_exists($headerPath)) {
        $headerPath = CM_LAYOUT_PATH . '/header.php';
    }
    include $headerPath;
}

function include_footer($siteConfig = []) {
    global $is_member, $is_guest, $is_super, $is_admin, $user;
    $template = $siteConfig['template'] ?? 'basic';
    $footerPath = CM_TEMPLATE_PATH . '/' . $template . '/footer.php';
    if (!file_exists($footerPath)) {
        $footerPath = CM_LAYOUT_PATH . '/footer.php';
    }
    include $footerPath;
}

function include_admin_header($title = '') {
    global $is_member, $is_guest, $is_super, $is_admin, $user, $csrf_token;
    include CM_LAYOUT_PATH . '/admin_header.php';
}

function include_admin_footer() {
    global $is_member, $is_guest, $is_super, $is_admin, $user;
    include CM_LAYOUT_PATH . '/admin_footer.php';
}
