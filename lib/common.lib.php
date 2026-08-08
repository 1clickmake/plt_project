<?php
/**
 * Global Utility Functions
 */

// Global User Variables
$is_member = false;
$is_guest = true;
$is_super = false;
$is_admin = false;
$user = [];

/**
 * Initialize user-related global variables from session
 */
function setup_user_variables() {
    global $is_member, $is_guest, $is_super, $is_admin, $user;

    if (isset($_SESSION['user']) && !empty($_SESSION['user'])) {
        $user = $_SESSION['user'];
        $is_member = true;
        $is_guest = false;
        
        $level = isset($user['level']) ? (int)$user['level'] : 1;
        
        // 최고관리자 (Level 10)
        if ($level >= 10) {
            $is_super = true;
        }
        
        // 일반 관리자 (Role is admin or level >= 5)
        if ((isset($user['role']) && $user['role'] === 'admin') || $level >= 5) {
            $is_admin = true;
        }
    } else {
        $is_member = false;
        $is_guest = true;
        $is_super = false;
        $is_admin = false;
        $user = [];
    }
}

/**
 * Clean strings for safe output
 */
function get_text($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

/**
 * Alert and redirect
 */
function alert($msg, $url = '') {
    echo "<script>alert('" . addslashes($msg) . "');";
    if ($url) {
        echo "location.replace('" . $url . "');";
    } else {
        echo "history.back();";
    }
    echo "</script>";
    exit;
}


/**
 * Check if the current request is from a mobile device
 */
function is_mobile() {
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $mobile_agents = array(
        'iPhone', 'iPad', 'Android', 'Pocket PC', 'Mobile', 'Minimo', 'AvantGo', 
        'Opera Mobi', 'Opera Mini', 'Palm', 'Blazer', 'BlackBerry', 'Windows CE', 
        'IEMobile', 'Kindle', 'NetFront', 'Silk/', 'hpwOS', 'webOS', 
        'Fennec', 'bada', 'Tizen', 'SymbianOS', 'Brew'
    );

    foreach ($mobile_agents as $agent) {
        if (stripos($user_agent, $agent) !== false) {
            return true;
        }
    }

    return false;
}

/**
 * Log visitor information
 */
function log_visitor() {
    // Skip logging for admin or specific AJAX requests if needed
    // But usually we log everyone once a day
    
    $today = date('Y-m-d');
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $now = time();

    // To prevent heavy DB writes, update last_active_at only once every 60 seconds per session
    if (isset($_SESSION['last_activity_time']) && ($now - $_SESSION['last_activity_time'] < 60)) {
        // But we still need to check if the date changed
        // But we still need to check if the date changed
        if (isset($_SESSION['last_visit_date']) && $_SESSION['last_visit_date'] === $today) {
            return;
        }
    }

    global $is_member, $user;

    $db = \App\Core\Database::getInstance();
    if (!$db) return;

    // Check if record for today already exists
    $stmt = $db->prepare("SELECT id FROM visitor_logs WHERE ip_address = ? AND visit_date = ? LIMIT 1");
    $stmt->execute([$ip, $today]);
    $row = $stmt->fetch();

    if ($row) {
        // Update last active time
        $stmt = $db->prepare("UPDATE visitor_logs SET last_active_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->execute([$row['id']]);
    } else {
        // New visitor for today
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        $country = 'Unknown';

        if (!isset($_SESSION['visitor_country'])) {
            try {
                $ctx = stream_context_create(['http' => ['timeout' => 1]]);
                $json = @file_get_contents("http://ip-api.com/json/{$ip}?fields=countryCode", false, $ctx);
                if ($json) {
                    $data = json_decode($json, true);
                    $country = $data['countryCode'] ?? 'Unknown';
                }
            } catch (Exception $e) {
                $country = 'Unknown';
            }
            $_SESSION['visitor_country'] = $country;
        } else {
            $country = $_SESSION['visitor_country'];
        }

        $stmt = $db->prepare("INSERT INTO visitor_logs (ip_address, country, user_agent, referer, visit_date, visit_time) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([ $ip, $country, $userAgent, $referer, $today, date('H:i:s') ]);
    }

    $_SESSION['last_visit_date'] = $today;
    $_SESSION['last_activity_time'] = $now;
}

/**
 * Add or subtract user points
 */
function add_point($user_id, $point, $rel_msg = '') {
    if (!$user_id || $point == 0) return false;
    
    $db = \App\Core\Database::getInstance();
    if (!$db) return false;

    // Update user point
    $stmt = $db->prepare("UPDATE users SET point = point + :point WHERE user_id = :user_id");
    $stmt->execute(['point' => $point, 'user_id' => $user_id]);

    // Record point log
    $stmt = $db->prepare("INSERT INTO point_log (user_id, point, rel_msg) VALUES (:user_id, :point, :rel_msg)");
    $stmt->execute(['user_id' => $user_id, 'point' => $point, 'rel_msg' => $rel_msg]);

    // Update level based on new points (simple rule: level = floor(points / 1000) + 1)
    update_level($user_id);

    return true;
}

/**
 * Check if the current user has the required level
 * Admin always returns true
 */
function check_level($required_level) {
    global $is_admin, $is_member, $user;

    // If Admin, always pass
    if ($is_admin) return true;
    
    // Default level for guest is 1
    $user_level = $is_member ? (int)$user['level'] : 1;
    
    return $user_level >= (int)$required_level;
}

/**
 * Update user level based on points
 */
function update_level($user_id) {
    if (!$user_id) return false;

    $db = \App\Core\Database::getInstance();
    if (!$db) return false;

    $stmt = $db->prepare("SELECT point, level FROM users WHERE user_id = :user_id");
    $stmt->execute(['user_id' => $user_id]);
    $user = $stmt->fetch();

    if (!$user) return false;

    // level calculation logic (e.g., Level up every 500 points)
    $new_level = floor($user['point'] / 500) + 1;
    if ($new_level < 1) $new_level = 1;

    if ($new_level != $user['level']) {
        $stmt = $db->prepare("UPDATE users SET level = :level WHERE user_id = :user_id");
        $stmt->execute(['level' => $new_level, 'user_id' => $user_id]);
        return true;
    }

    return false;
}

/**
 * Generate pagination HTML
 */
/**
 * Generate pagination HTML
 */
function get_pagination($page, $totalPages, $extraQuery = '', $pageLimit = 5) {
    if ($totalPages <= 1) return '';

    // Use .pagination-container class instead of inline styles
    $html = '<div class="pagination-container">';
    
    $startPage = max(1, $page - floor($pageLimit / 2));
    $endPage = min($totalPages, $startPage + $pageLimit - 1);
    
    if ($endPage - $startPage + 1 < $pageLimit) {
        $startPage = max(1, $endPage - $pageLimit + 1);
    }
    
    $queryStr = '';
    if ($extraQuery) {
        // If it looks like a full query part (contains =), use it directly, otherwise prefix with search=
        if (strpos($extraQuery, '=') !== false) {
            $queryStr = '&' . $extraQuery;
        } else {
            $queryStr = '&search=' . urlencode($extraQuery);
        }
    }

    // First & Prev
    if ($page > 1) {
        $html .= '<a href="?page=1' . $queryStr . '" class="btn btn-page" title="First"><i class="fa-solid fa-angles-left"></i></a>';
        $html .= '<a href="?page=' . ($page - 1) . $queryStr . '" class="btn btn-page" title="Prev"><i class="fa-solid fa-angle-left"></i></a>';
    }

    // Page Numbers
    for ($i = $startPage; $i <= $endPage; $i++) {
        $activeClass = ($i == $page) ? 'btn-page-active' : 'btn-page';
        $html .= "<a href=\"?page={$i}{$queryStr}\" class=\"btn {$activeClass}\">{$i}</a>";
    }

    // Next & Last
    if ($page < $totalPages) {
        $html .= '<a href="?page=' . ($page + 1) . $queryStr . '" class="btn btn-page" title="Next"><i class="fa-solid fa-angle-right"></i></a>';
        $html .= '<a href="?page=' . $totalPages . $queryStr . '" class="btn btn-page" title="Last"><i class="fa-solid fa-angles-right"></i></a>';
    }

    $html .= '</div>';
    return $html;
}

/**
 * Enhanced Security Helper Functions
 */

/**
 * Escape HTML with default settings (alias for get_text)
 * Use this for all user-generated content output
 */
function e($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Sanitize URL for safe output in href attributes
 * Prevents javascript: and data: URI schemes
 */
function sanitize_url($url) {
    $url = trim($url);
    
    // Block dangerous protocols
    $dangerous_protocols = ['javascript:', 'data:', 'vbscript:', 'file:'];
    foreach ($dangerous_protocols as $protocol) {
        if (stripos($url, $protocol) === 0) {
            return '#';
        }
    }
    
    // Only allow http, https, mailto, tel, or relative URLs
    if (!preg_match('/^(https?:\/\/|mailto:|tel:|\/|#)/', $url)) {
        return '#';
    }
    
    return htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
}

/**
 * Safe array access with default value
 * Prevents undefined index warnings
 */
function array_get($array, $key, $default = null) {
    return $array[$key] ?? $default;
}

/**
 * Clean text from user input (removes tags, trims)
 */
function clean_input($str) {
    return trim(strip_tags($str ?? ''));
}

/**
 * Validate email format
 */
function is_valid_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Generate a secure random token
 */
function generate_token($length = 32) {
    return bin2hex(random_bytes($length));
}

/**
 * Check if request is AJAX
 */
function is_ajax_request() {
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Rate limiting helper (simple session-based)
 * Returns true if action is allowed, false if rate limit exceeded
 */
/**
 * Hook System Helpers
 */
function add_action($hookName, $callback, $priority = 10) {
    \App\Core\HookManager::getInstance()->addAction($hookName, $callback, $priority);
}

function do_action($hookName, ...$params) {
    \App\Core\HookManager::getInstance()->doAction($hookName, ...$params);
}

function check_rate_limit($action, $max_attempts = 5, $time_window = 60) {
    $key = 'rate_limit_' . $action;
    
    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = ['count' => 0, 'first_attempt' => time()];
    }
    
    $data = $_SESSION[$key];
    
    // Reset if time window has passed
    if (time() - $data['first_attempt'] > $time_window) {
        $_SESSION[$key] = ['count' => 1, 'first_attempt' => time()];
        return true;
    }
    
    // Check if limit exceeded
    if ($data['count'] >= $max_attempts) {
        return false;
    }
    
    // Increment counter
    $_SESSION[$key]['count']++;
    return true;
}

/**
 * Get thumbnail from post data or content
 */
function get_thumbnail($post, $default = null) {
    if (!$post) return $default;
    
    // 1. Check for attached file (first_file)
    $thumbnail = $post['first_file'] ?? null;
    
    // 2. If no attachment, extract from content
    if (!$thumbnail && !empty($post['content'])) {
        $content = htmlspecialchars_decode($post['content']);
        preg_match('/<img[^>]+src="([^">]+)"/i', $content, $matches);
        if (!empty($matches[1])) {
            $thumbnail = $matches[1];
        }
    }
    
    return $thumbnail ?: $default;
}
