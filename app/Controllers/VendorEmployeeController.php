<?php

namespace App\Controllers;

use App\Core\Database;
use Exception;

class VendorEmployeeController extends BaseController {
    
    private function checkVendorAuth() {
        if (!isset($_SESSION['user']) || empty($_SESSION['user'])) {
            header("Location: /login");
            exit;
        }
    }

    public function profiles() {
        $this->checkVendorAuth();
        $this->requireVendorEmployees();
        $db = Database::getInstance();
        
        $stmt = $db->prepare("SELECT id FROM users WHERE user_id = ?");
        $stmt->execute([$_SESSION['user']['user_id']]);
        $vendor_int_id = $stmt->fetchColumn();

        $stmt = $db->prepare("SELECT * FROM vendor_employees WHERE vendor_id = ? ORDER BY id ASC");
        $stmt->execute([$vendor_int_id]);
        $employees = $stmt->fetchAll();



        $this->view('vendor/profiles', ['employees' => $employees]);
    }

    public function profileLogin() {
        $this->checkVendorAuth();
        $db = Database::getInstance();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $employee_id = (int)($_POST['employee_id'] ?? 0);
            
            $stmt = $db->prepare("SELECT id FROM users WHERE user_id = ?");
            $stmt->execute([$_SESSION['user']['user_id']]);
            $vendor_int_id = $stmt->fetchColumn();

            $stmt = $db->prepare("SELECT * FROM vendor_employees WHERE id = ? AND vendor_id = ?");
            $stmt->execute([$employee_id, $vendor_int_id]);
            $employee = $stmt->fetch();

            if ($employee) {
                $_SESSION['employee_id'] = $employee['id'];
                $_SESSION['employee_name'] = $employee['name'];
                $_SESSION['employee_color'] = $employee['color_code'];
                $_SESSION['employee_phone'] = $employee['phone'];
                $_SESSION['employee_title'] = $employee['title'];

                // Insert into employee_logs
                $ip = $_SERVER['REMOTE_ADDR'] ?? null;
                $ua = $_SERVER['HTTP_USER_AGENT'] ?? null;
                $stmt = $db->prepare("INSERT INTO employee_logs (vendor_id, employee_id, ip_address, user_agent) VALUES (?, ?, ?, ?)");
                $stmt->execute([$vendor_int_id, $employee['id'], $ip, $ua]);

                header("Location: /vendor/quotes");
                exit;
            }
        }
        header("Location: /vendor/profiles");
        exit;
    }

    public function index() {
        $this->checkVendorAuth();
        $db = Database::getInstance();
        
        $vendor_id = $_SESSION['user']['user_id']; // This is actually the string user_id, but the table uses the integer ID of the user.
        // Let's get the integer ID from users table.
        $stmt = $db->prepare("SELECT id FROM users WHERE user_id = ?");
        $stmt->execute([$vendor_id]);
        $user = $stmt->fetch();
        if (!$user) {
            // Ghost session detected: User exists in session but not in DB
            unset($_SESSION['user']);
            echo "<script>alert('로그인 세션이 만료되었거나 존재하지 않는 계정입니다. 다시 로그인해주세요.'); location.href='/login';</script>";
            exit;
        }
        $vendor_int_id = $user['id'];

        // Get employees
        try {
            $stmt = $db->prepare("SELECT * FROM vendor_employees WHERE vendor_id = ? ORDER BY id DESC");
            $stmt->execute([$vendor_int_id]);
            $employees = $stmt->fetchAll();
        } catch (\PDOException $e) {
            die("DB Error in employees: " . $e->getMessage() . "<br>Please run the CREATE TABLE vendor_employees query.");
        }

        $this->view('vendor/employees', ['employees' => $employees, 'vendor_id' => $vendor_id]);
    }

    public function create() {
        $this->checkVendorAuth();
        $db = Database::getInstance();
        
        // get integer id
        $stmt = $db->prepare("SELECT id FROM users WHERE user_id = ?");
        $stmt->execute([$_SESSION['user']['user_id']]);
        $vendor_int_id = $stmt->fetchColumn();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'] ?? '';
            $title = $_POST['title'] ?? '';
            $phone = $_POST['phone'] ?? '';
            $color_code = $_POST['color_code'] ?? '#c0392b';

            if ($name) {
                $stmt = $db->prepare("INSERT INTO vendor_employees (vendor_id, name, title, phone, color_code) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$vendor_int_id, $name, $title, $phone, $color_code]);
            }
        }
        header("Location: /vendor/employees");
        exit;
    }

    public function update() {
        $this->checkVendorAuth();
        $db = Database::getInstance();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? 0;
            $name = $_POST['name'] ?? '';
            $title = $_POST['title'] ?? '';
            $phone = $_POST['phone'] ?? '';
            $color_code = $_POST['color_code'] ?? '#c0392b';
            
            // Check ownership
            $stmt = $db->prepare("SELECT vendor_id FROM vendor_employees WHERE id = ?");
            $stmt->execute([$id]);
            $owner_id = $stmt->fetchColumn();

            $stmt2 = $db->prepare("SELECT id FROM users WHERE user_id = ?");
            $stmt2->execute([$_SESSION['user']['user_id']]);
            $vendor_int_id = $stmt2->fetchColumn();

            if ($owner_id == $vendor_int_id && $name) {
                $stmt = $db->prepare("UPDATE vendor_employees SET name=?, title=?, phone=?, color_code=? WHERE id=?");
                $stmt->execute([$name, $title, $phone, $color_code, $id]);
            }
        }
        header("Location: /vendor/employees");
        exit;
    }

    public function delete() {
        $this->checkVendorAuth();
        $db = Database::getInstance();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? 0;
            
            $stmt = $db->prepare("SELECT vendor_id FROM vendor_employees WHERE id = ?");
            $stmt->execute([$id]);
            $owner_id = $stmt->fetchColumn();

            $stmt2 = $db->prepare("SELECT id FROM users WHERE user_id = ?");
            $stmt2->execute([$_SESSION['user']['user_id']]);
            $vendor_int_id = $stmt2->fetchColumn();

            if ($owner_id == $vendor_int_id) {
                $stmt = $db->prepare("DELETE FROM vendor_employees WHERE id=?");
                $stmt->execute([$id]);
            }
        }
        header("Location: /vendor/employees");
        exit;
    }

    public function logs($vars) {
        $this->checkVendorAuth();
        
        $employee_id = (int)($vars['id'] ?? 0);
        $db = Database::getInstance();
        
        // Get Vendor ID
        $stmt = $db->prepare("SELECT id FROM users WHERE user_id = ?");
        $stmt->execute([$_SESSION['user']['user_id']]);
        $vendor_int_id = $stmt->fetchColumn();

        // Check if employee belongs to this vendor
        $stmt = $db->prepare("SELECT * FROM vendor_employees WHERE id = ? AND vendor_id = ?");
        $stmt->execute([$employee_id, $vendor_int_id]);
        $employee = $stmt->fetch();
        
        if (!$employee) {
            echo "<script>alert('접근 권한이 없거나 존재하지 않는 직원입니다.'); window.history.back();</script>";
            return;
        }

        // Get Logs
        $stmt = $db->prepare("SELECT * FROM employee_logs WHERE employee_id = ? AND vendor_id = ? ORDER BY login_time DESC LIMIT 50");
        $stmt->execute([$employee_id, $vendor_int_id]);
        $logs = $stmt->fetchAll();

        $this->view('vendor/employee_logs', ['employee' => $employee, 'logs' => $logs]);
    }
}
