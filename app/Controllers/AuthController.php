<?php

namespace App\Controllers;

use App\Core\Database;
use App\Core\Csrf;
use PDO;

class AuthController extends BaseController {
    public function showLogin() {
        global $is_member;
        if ($is_member) {
            $this->redirect('/');
        }
        $this->view('auth/login', ['csrf_token' => Csrf::getToken()]);
    }

    public function login() {
        // Rate limiting: max 5 attempts per minute
        if (!check_rate_limit('login', 5, 60)) {
            $this->view('auth/login', ['error' => 'Too many login attempts. Please try again in 1 minute.']);
            return;
        }

        if (!Csrf::verify($_POST['csrf_token'] ?? '')) {
            $this->view('auth/login', ['error' => 'Invalid Security Token']);
            return;
        }

        $userId = $_POST['user_id'] ?? '';
        $password = $_POST['password'] ?? '';

        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM users WHERE user_id = :user_id");
        $stmt->execute(['user_id' => $userId]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Reset rate limit on successful login
            unset($_SESSION['rate_limit_login']);
            
            $_SESSION['user'] = $user;
            setup_user_variables(); // Update global variables immediately
            
            global $is_admin;
            $this->redirect($is_admin ? '/admin' : '/vendor/profiles');
        } else {
            $this->view('auth/login', ['error' => 'Invalid User ID or password']);
        }
    }

    public function showRegister() {
        $this->view('auth/register', ['csrf_token' => Csrf::getToken()]);
    }

    public function register() {
        if (!Csrf::verify($_POST['csrf_token'] ?? '')) {
            $this->view('auth/register', ['error' => 'Invalid Security Token']);
            return;
        }

        $userId = $_POST['user_id'] ?? '';
        $username = $_POST['username'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        $country = $_POST['country'] ?? 'Unknown';

        if ($password !== $confirm) {
            return $this->view('auth/register', ['error' => 'Passwords do not match']);
        }

        // Validate User ID
        $blacklistCheck = $this->validateUserId($userId);
        if ($blacklistCheck !== true) {
            return $this->view('auth/register', ['error' => $blacklistCheck]);
        }

        // Validate Username/Nickname
        $usernameCheck = $this->validateUsername($username);
        if ($usernameCheck !== true) {
            return $this->view('auth/register', ['error' => $usernameCheck]);
        }

        $db = Database::getInstance();
        $hashed = password_hash($password, PASSWORD_BCRYPT);

        // Fetch default signup settings
        $config = $db->query("SELECT join_point, join_level FROM config LIMIT 1")->fetch();
        $defaultPoint = $config['join_point'] ?? 0;
        $defaultLevel = $config['join_level'] ?? 1;

        try {
            $stmt = $db->prepare("INSERT INTO users (user_id, username, email, password, country, point, level) VALUES (:user_id, :username, :email, :password, :country, :point, :level)");
            $stmt->execute([
                'user_id' => $userId,
                'username' => $username,
                'email' => $email,
                'password' => $hashed,
                'country' => $country,
                'point' => $defaultPoint,
                'level' => $defaultLevel
            ]);

            // If signup points are given, record in point_log
            if ($defaultPoint > 0) {
                // We need to fetch the last insert ID if needed, but point_log uses user_id (the string ID)
                $stmt = $db->prepare("INSERT INTO point_log (user_id, point, rel_msg) VALUES (:user_id, :point, :rel_msg)");
                $stmt->execute([
                    'user_id' => $userId, 
                    'point' => $defaultPoint,
                    'rel_msg' => 'Signup Bonus'
                ]);
            }

            $this->redirect('/register?success=1');
        } catch (\PDOException $e) {
            $this->view('auth/register', ['error' => 'User ID or Email already exists']);
        }
    }

    public function logout() {
        session_destroy();
        $_SESSION = []; // Clear current session array
        setup_user_variables(); // Reset global variables
        $this->redirect('/login');
    }

    public function mypage() {
        global $is_member, $user;
        if (!$is_member) {
            $this->redirect('/login');
        }

        $db = Database::getInstance();
        $userId = $user['id'];
        $userStrId = $user['user_id'];

        // Get last 10 posts
        $stmt = $db->prepare("
            SELECT p.*, b.title as board_title, b.slug as board_slug 
            FROM posts p 
            JOIN boards b ON p.board_id = b.id 
            WHERE p.user_id = :user_id 
            ORDER BY p.created_at DESC 
            LIMIT 10
        ");
        $stmt->execute(['user_id' => $userId]);
        $posts = $stmt->fetchAll();

        $this->view('auth/mypage', [
            'user' => $user,
            'posts' => $posts
        ]);
    }

    public function updateProfile() {
        global $is_member, $user;
        if (!$is_member) $this->redirect('/login');

        if (!Csrf::verify($_POST['csrf_token'] ?? '')) {
            $this->redirect('/mypage?error=csrf');
            return;
        }

        $db = Database::getInstance();
        $userId = $user['id'];
        
        $currentPassword = $_POST['current_password'] ?? '';
        
        // Verify current password
        $stmt = $db->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $dbUser = $stmt->fetch();

        if (!$dbUser || !password_verify($currentPassword, $dbUser['password'])) {
            $this->redirect('/mypage?error=password');
            return;
        }

        $username = $_POST['username'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        if ($password) {
            $hashed = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $db->prepare("UPDATE users SET username = :username, email = :email, password = :password WHERE id = :id");
            $stmt->execute(['username' => $username, 'email' => $email, 'password' => $hashed, 'id' => $userId]);
        } else {
            $stmt = $db->prepare("UPDATE users SET username = :username, email = :email WHERE id = :id");
            $stmt->execute(['username' => $username, 'email' => $email, 'id' => $userId]);
        }

        // Update session
        $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $_SESSION['user'] = $stmt->fetch();
        setup_user_variables();

        $this->redirect('/mypage?updated=1');
    }

    public function deleteAccount() {
        global $is_member, $user;
        if (!$is_member) $this->redirect('/login');

        if (!Csrf::verify($_POST['csrf_token'] ?? '')) {
            $this->redirect('/mypage?error=csrf');
            return;
        }

        $db = Database::getInstance();
        $userId = $user['id'];

        $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$userId]);

        session_destroy();
        $_SESSION = [];
        setup_user_variables();
        $this->redirect('/?deleted=1');
    }

    public function checkDuplicate() {
        $field = $_GET['field'] ?? '';
        $value = $_GET['value'] ?? '';

        if (!in_array($field, ['user_id', 'email', 'username'])) {
            return $this->json(['error' => 'Invalid field']);
        }

        if ($field === 'user_id') {
            $validation = $this->validateUserId($value);
            if ($validation !== true) {
                return $this->json(['exists' => true, 'message' => $validation]); // Use 'exists' true to block it on frontend
            }
        }
        
        if ($field === 'username') {
            $validation = $this->validateUsername($value);
            if ($validation !== true) {
                 return $this->json(['exists' => true, 'message' => $validation]);
            }
        }

        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE $field = :value");
        $stmt->execute(['value' => $value]);
        $exists = $stmt->fetchColumn() > 0;

        return $this->json(['exists' => $exists]);
    }

    private function validateUserId($userId) {
        // 1. Minimum/Maximum length
        if (mb_strlen($userId, 'UTF-8') < 3 || mb_strlen($userId, 'UTF-8') > 20) {
            return 'ID must be between 3 and 20 characters.';
        }

        // 2. Character allowed: alphabets, numbers, hyphen, underscore
        // (Korean keys generally not used for system IDs, but if needed, add logic. Assuming English ID for now based on standard)
        if (!preg_match('/^[a-zA-Z0-9_\-]+$/', $userId)) {
            return 'ID can only contain letters, numbers, hyphens, and underscores.';
        }

        // 3. Start with number or hyphen check
        if (preg_match('/^[0-9\-]/', $userId)) {
            return 'ID cannot start with a number or hyphen.';
        }
        
        // 4. Forbidden Names Blacklist
        $forbiddenNames = $this->getForbiddenNames();

        if (in_array(strtolower($userId), $forbiddenNames)) {
            return 'This ID is not allowed.';
        }
        
        // Partial match check for 'admin' (e.g., 'super_admin', 'admin_123')
        if (strpos(strtolower($userId), 'admin') !== false) {
             return 'This ID contains forbidden words.';
        }

        return true;
    }

    private function validateUsername($username) {
        $forbiddenNames = $this->getForbiddenNames();

        // Exact match check
        if (in_array(strtolower($username), $forbiddenNames)) {
            return 'This name is not allowed.';
        }
        
        // Partial match check for specific keywords
        foreach ($forbiddenNames as $bad) {
             if (mb_strpos(strtolower($username), $bad) !== false) {
                 return 'This name contains forbidden words.';
             }
        }

        return true;
    }
    private function getForbiddenNames() {
        return [
            // System & Admin
            'admin', 'administrator', 'root', 'superadmin', 'sysadmin', 'master', 'webmaster', 'manager',
            '관리자', '어드민', '운영자', '담당자', '고객센터', '스태프', 'staff',
            'support', 'help', 'helpdesk', 'info', 'contact', 'official', 'notice', 'service',
            'system', 'noreply', 'mail', 'postmaster', 'hostmaster',
            
            // Tech & Infra
            'localhost', 'www', 'ftp', 'smtp', 'pop3', 'ns1', 'ns2', 'api', 'dev', 'test', 'git', 'tester',
            'index', 'home', 'public', 'static', 'assets', 'robots', 'css', 'js', 'images',
            'null', 'undefined', 'void', 'sql', 'mysql', 'database',
            
            // Service Menus
            'login', 'signup', 'join', 'logout', 'settings', 'profile', 'search', 'board', 'chat', 'blog', 'apps', 'shop', 'cart',
            
            // Misc
            '테스트', '임시', '익명', 'google', 'kakao', 'naver', 'facebook', 'apple', 'microsoft'
        ];
    }
}
