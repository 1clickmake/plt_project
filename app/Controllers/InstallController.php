<?php

namespace App\Controllers;

use PDO;
use PDOException;

class InstallController extends BaseController {
    public function index() {
        if (file_exists(__DIR__ . '/../../.env')) {
            $this->redirect('/');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->install();
            return;
        }

        $this->view('install/index');
    }

    private function install() {
        $dbHost = $_POST['db_host'] ?? 'localhost';
        $dbName = $_POST['db_name'] ?? '';
        $dbUser = $_POST['db_user'] ?? '';
        $dbPass = $_POST['db_pass'] ?? '';
        $appUrl = $_POST['app_url'] ?? 'http://localhost';
        
        $adminUserid = $_POST['admin_user_id'] ?? 'admin';
        $adminUsername = $_POST['admin_username'] ?? 'Admin';
        $adminPassword = $_POST['admin_password'] ?? '';
        $adminEmail = $_POST['admin_email'] ?? '';

        try {
            // 1. Try to connect and create DB if it doesn't exist
            try {
                $pdo = new PDO("mysql:host=$dbHost", $dbUser, $dbPass);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            } catch (PDOException $e) {
                // Ignore if CREATE DATABASE fails (shared hosting)
            }

            // Reconnect to the created/existing database
            $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->exec("SET SESSION sql_mode = ''");
            // 2. Run setup.sql
            if (file_exists(__DIR__ . '/../../views/install/setup.sql')) {
                $sql = file_get_contents(__DIR__ . '/../../views/install/setup.sql');
                // Remove USE statement from setup.sql if present to avoid conflicts
                $sql = preg_replace('/USE `?ai_php`?;/i', '', $sql);
                $pdo->exec($sql);
            }

            // 3. Insert Admin User from Form
            if (!empty($adminPassword)) {
                $hashed = password_hash($adminPassword, PASSWORD_BCRYPT);
                $stmt = $pdo->prepare("INSERT INTO users (user_id, username, password, email, role, level) VALUES (:user_id, :username, :password, :email, :role, :level)");
                $stmt->execute([
                    'user_id' => $adminUserid,
                    'username' => $adminUsername,
                    'password' => $hashed,
                    'email' => $adminEmail,
                    'role' => 'admin',
                    'level' => 10
                ]);
            }

            // 4. Create .env file
            $envContent = "DB_HOST=$dbHost\n";
            $envContent .= "DB_NAME=$dbName\n";
            $envContent .= "DB_USER=$dbUser\n";
            $envContent .= "DB_PASS=$dbPass\n";
            $envContent .= "APP_URL=" . rtrim($appUrl, '/') . "\n";

            file_put_contents(__DIR__ . '/../../.env', $envContent);
            
            // 5. Create data directory and essential sub-directories
            $baseDataPath = __DIR__ . '/../../public/data';
            $subDirs = ['', '/page', '/logo'];
            
            foreach ($subDirs as $sub) {
                $path = $baseDataPath . $sub;
                if (!file_exists($path)) {
                    if (!mkdir($path, 0707, true)) {
                        // If mkdir fails, we still try to proceed
                    }
                }
                @chmod($path, 0707);
            }

            echo "<script>alert('Installation successful!'); window.location.href='/';</script>";
            exit;

        } catch (PDOException $e) {
            $this->view('install/index', ['error' => "Connection failed: " . $e->getMessage()]);
        }
    }
}
