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
