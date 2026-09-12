<?php

namespace App\Controllers;

use App\Core\Database;
use PDO;

class HomeController extends BaseController {
    public function index() {
        $db = Database::getInstance();
        
        // Get site configuration (including template)
        $config = $db->query("SELECT * FROM config WHERE id = 1")->fetch();
        $template = $config['template'] ?? 'basic';
        
        // Get board groups
        $groups = $db->query("SELECT * FROM board_groups")->fetchAll();
        
        foreach ($groups as &$group) {
            $stmt = $db->prepare("SELECT * FROM boards WHERE group_id = :id");
            $stmt->execute(['id' => $group['id']]);
            $group['boards'] = $stmt->fetchAll();
        }

        // Load template-specific main page
        $templatePath = "templates/{$template}/main";
        $this->view($templatePath, ['groups' => $groups, 'config' => $config]);
    }

    public function about() {
        $db = Database::getInstance();
        $config = $db->query("SELECT * FROM config WHERE id = 1")->fetch();
        $template = $config['template'] ?? 'basic';
        
        $templatePath = "templates/{$template}/about";
        $this->view($templatePath, ['config' => $config]);
    }

    public function website() {
        $db = Database::getInstance();
        $config = $db->query("SELECT * FROM config WHERE id = 1")->fetch();
        $template = $config['template'] ?? 'basic';
        
        // Ensure website_portfolios table exists
        try {
            $db->exec("CREATE TABLE IF NOT EXISTS `website_portfolios` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `title` varchar(255) NOT NULL COMMENT '프로젝트/사이트명',
                `url` varchar(500) NOT NULL COMMENT '사이트 URL',
                `category` varchar(50) DEFAULT '회사홈페이지' COMMENT '카테고리',
                `description` text DEFAULT NULL COMMENT '설명',
                `created_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT '등록일시',
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='제작 웹사이트 포트폴리오 테이블'");
            
            $portfolios = $db->query("SELECT * FROM website_portfolios ORDER BY id DESC")->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $portfolios = [];
        }

        $templatePath = "templates/{$template}/website";
        if (!file_exists(CM_VIEWS_PATH . '/' . $templatePath . '.php')) {
            $templatePath = "website";
        }
        $this->view($templatePath, [
            'config' => $config,
            'portfolios' => $portfolios
        ]);
    }

    public function addPortfolio() {
        $this->checkAdmin();

        $title       = trim($_POST['title'] ?? '');
        $url         = trim($_POST['url'] ?? '');
        $category    = trim($_POST['category'] ?? '회사홈페이지');
        $description = trim($_POST['description'] ?? '');

        if (!$url) {
            $this->redirect('/website');
            return;
        }

        if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
            $url = "http://" . $url;
        }

        if (!$title) {
            $parsed = parse_url($url, PHP_URL_HOST);
            $title = $parsed ?: $url;
        }

        try {
            $db = Database::getInstance();
            $stmt = $db->prepare("INSERT INTO website_portfolios (title, url, category, description) VALUES (?, ?, ?, ?)");
            $stmt->execute([$title, $url, $category, $description]);
        } catch (\Exception $e) {
            // Log or ignore
        }

        $this->redirect('/website#portfolio');
    }

    public function deletePortfolio() {
        $this->checkAdmin();

        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            try {
                $db = Database::getInstance();
                $stmt = $db->prepare("DELETE FROM website_portfolios WHERE id = ?");
                $stmt->execute([$id]);
            } catch (\Exception $e) {
                // Log or ignore
            }
        }

        $this->redirect('/website#portfolio');
    }

    public function page($vars) {
        $db = Database::getInstance();
        $slug = $vars['slug'];
        
        $stmt = $db->prepare("SELECT * FROM pages WHERE slug = ?");
        $stmt->execute([$slug]);
        $page = $stmt->fetch();

        if (!$page) {
            http_response_code(404);
            $this->view('404'); // Or just echo for now
            return;
        }

        $this->view('page', ['page' => $page]);
    }

    public function sendContact() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Invalid request method']);
        }

        if (!\App\Core\Csrf::verify($_POST['csrf_token'] ?? '')) {
            $this->json(['success' => false, 'message' => 'CSRF validation failed']);
        }

        $name        = $_POST['name'] ?? '';
        $phone       = $_POST['phone'] ?? '';
        $email       = $_POST['email'] ?? '';
        $subject     = $_POST['subject'] ?? '';
        $message     = $_POST['message'] ?? '';
        $target_type = $_POST['target_type'] ?? 'contact';

        if (!$name || !$email || !$subject || !$message) {
            $this->json(['success' => false, 'message' => 'Please fill in all fields']);
        }

        // Prepare email content
        $content = "
            <h3>New Contact Message</h3>
            <p><strong>Name:</strong> {$name}</p>
            <p><strong>Phone:</strong> {$phone}</p>
            <p><strong>Email:</strong> {$email}</p>
            <p><strong>Subject:</strong> {$subject}</p>
            <p><strong>Message:</strong></p>
            <p>" . nl2br(htmlspecialchars($message)) . "</p>
        ";
        
        require_once CM_LIB_PATH . '/mailer.lib.php';

        // Get site config for recipient email
        $db = Database::getInstance();
        $siteConfig = $db->query("SELECT company_email FROM config WHERE id = 1")->fetch();
        
        $to = !empty($siteConfig['company_email']) ? $siteConfig['company_email'] : ($_ENV['SMTP_USER'] ?? '');
        
        // Pass extra logging data
        $extraLogData = [
            'sender_name'  => $name,
            'sender_phone' => $phone,
            'sender_email' => $email,
            'target_info'  => 'contact',
            'log_content'  => nl2br(htmlspecialchars($message))
        ];

        $result = \Mailer::send($to, $subject, $content, [], true, $extraLogData);

        if ($result['success']) {
            $this->json(['success' => true, 'message' => 'Message sent successfully']);
        } else {
            $this->json(['success' => false, 'message' => $result['message']]);
        }
    }

    public function faq($vars) {
        $db = Database::getInstance();
        
        // 1. Get Categories
        $config = $db->query("SELECT faq_category FROM config WHERE id = 1")->fetch();
        $categories = isset($config['faq_category']) ? explode('|', $config['faq_category']) : [];

        // 2. Filter by Category & Search
        $currentCategory = isset($_GET['category']) ? trim($_GET['category']) : '';
        $searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';
        
        // 3. Get FAQs with Pagination
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        if ($page < 1) $page = 1;
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $faqs = [];
        $totalItems = 0;
        $totalPages = 0;

        try {
            $whereParts = [];
            if ($currentCategory) {
                $whereParts[] = "category = :category";
            }
            if ($searchTerm) {
                $whereParts[] = "(question LIKE :search OR answer LIKE :search)";
            }

            $whereSql = "";
            if (!empty($whereParts)) {
                $whereSql = " WHERE " . implode(" AND ", $whereParts);
            }

            // Get Total Items
            $stmt = $db->prepare("SELECT COUNT(*) FROM faq" . $whereSql);
            if ($currentCategory) {
                $stmt->bindValue(':category', $currentCategory);
            }
            if ($searchTerm) {
                $stmt->bindValue(':search', '%' . $searchTerm . '%');
            }
            $stmt->execute();
            $totalItems = $stmt->fetchColumn();
            $totalPages = ceil($totalItems / $limit);

            // Get Paginated Data
            $stmt = $db->prepare("SELECT * FROM faq " . $whereSql . " ORDER BY display_order ASC, created_at DESC LIMIT :limit OFFSET :offset");
            if ($currentCategory) {
                $stmt->bindValue(':category', $currentCategory);
            }
            if ($searchTerm) {
                $stmt->bindValue(':search', '%' . $searchTerm . '%');
            }
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            $faqs = $stmt->fetchAll();

        } catch (\PDOException $e) { 
             // Table not found or other error
        }

        $this->view('faq', [
            'categories' => $categories,
            'faqs' => $faqs,
            'page' => $page,
            'totalPages' => $totalPages,
            'currentCategory' => $currentCategory,
            'searchTerm' => $searchTerm,
            'siteConfig' => $config
        ]);
    }
}
