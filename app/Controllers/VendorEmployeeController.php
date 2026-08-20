<?php

namespace App\Controllers;

use App\Core\Database;
use Exception;

class VendorEmployeeController {
    
    private function checkVendorAuth() {
        if (!isset($_SESSION['user']) || empty($_SESSION['user'])) {
            header("Location: /login");
            exit;
        }
    }

    public function profiles() {
        $this->checkVendorAuth();
        $db = Database::getInstance();
        
        $stmt = $db->prepare("SELECT id FROM users WHERE user_id = ?");
        $stmt->execute([$_SESSION['user']['user_id']]);
        $vendor_int_id = $stmt->fetchColumn();

        $stmt = $db->prepare("SELECT * FROM vendor_employees WHERE vendor_id = ? ORDER BY id ASC");
        $stmt->execute([$vendor_int_id]);
        $employees = $stmt->fetchAll();

        // If no employees exist, just log them in as a default vendor profile and redirect
        if (empty($employees)) {
            $_SESSION['employee_id'] = 0;
            $_SESSION['employee_name'] = '대표 관리자';
            $_SESSION['employee_color'] = '#333333';
            header("Location: /vendor/quotes");
            exit;
        }

        require __DIR__ . '/../../views/vendor/profiles.php';
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
                $stmt = $db->prepare("INSERT INTO employee_logs (vendor_id, employee_id) VALUES (?, ?)");
                $stmt->execute([$vendor_int_id, $employee['id']]);

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
            header("Location: /login");
            exit;
        }
        $vendor_int_id = $user['id'];

        // Get employees
        $stmt = $db->prepare("SELECT * FROM vendor_employees WHERE vendor_id = ? ORDER BY id DESC");
        $stmt->execute([$vendor_int_id]);
        $employees = $stmt->fetchAll();

        require __DIR__ . '/../../views/vendor/employees.php';
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
}
