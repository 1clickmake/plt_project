<?php

namespace App\Controllers;

class BaseController {
    protected function view($path, $data = []) {
        // Fetch global site configuration
        try {
            $db = \App\Core\Database::getInstance();
            if ($db) {
                // Check if table exists query to avoid errors during clean install
                $stmt = $db->query("SHOW TABLES LIKE 'config'");
                if ($stmt->rowCount() > 0) {
                    $config = $db->query("SELECT * FROM config WHERE id = 1")->fetch();
                    $data['siteConfig'] = $config ?: [];

                    // Fetch pages for footer links
                    $stmtPages = $db->query("SHOW TABLES LIKE 'pages'");
                    if ($stmtPages->rowCount() > 0) {
                        $pages = $db->query("SELECT title, slug FROM pages ORDER BY id ASC")->fetchAll();
                        $data['footerPages'] = $pages ?: [];
                    }
                }
            }
        } catch (\Exception $e) {
            // Site config table might not exist yet
            $data['siteConfig'] = [];
            $data['footerPages'] = [];
        }

        // Automatically pass current session user to views
        if (isset($_SESSION['user'])) {
            $data['user'] = $_SESSION['user'];
        }

        // Always pass CSRF token to views
        $data['csrf_token'] = \App\Core\Csrf::getToken();

        // Inject global user variables to views
        global $is_member, $is_guest, $is_super, $is_admin, $user;
        $data['is_member'] = $is_member;
        $data['is_guest']  = $is_guest;
        $data['is_super']  = $is_super;
        $data['is_admin']  = $is_admin;
        $data['user']      = $user;
        
        \App\Core\View::render($path, $data);
    }

    protected function json($data) {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    protected function redirect($url) {
        header('Location: ' . $url);
        exit;
    }

    protected function checkAdmin() {
        global $is_admin;
        if (!$is_admin) {
            $this->redirect('/login');
        }
    }

    protected function requireVendorEmployees() {
        if (!isset($_SESSION['user']) || empty($_SESSION['user'])) {
            $this->redirect('/login');
        }
        
        $db = \App\Core\Database::getInstance();
        $stmt = $db->prepare("SELECT id FROM users WHERE user_id = ?");
        $stmt->execute([$_SESSION['user']['user_id']]);
        $vendor_int_id = $stmt->fetchColumn();

        if ($vendor_int_id) {
            $stmt2 = $db->prepare("SELECT COUNT(*) FROM vendor_employees WHERE vendor_id = ?");
            $stmt2->execute([$vendor_int_id]);
            $count = $stmt2->fetchColumn();
            
            if ($count == 0) {
                // 직원이 한 명도 없으면 직원 관리 페이지로 이동
                echo "<script>alert('견적 관리를 시작하려면 최소 1명의 대표 또는 직원을 등록해야 합니다.'); window.location.href='/vendor/employees';</script>";
                exit;
            }
        }
    }
}
