<?php

namespace Plugins\chatbot\Controllers;

use App\Core\Database;
use Plugins\chatbot\Services\AiService;

class ChatbotController
{
    public function ask()
    {
        // Prevent any stray output from breaking JSON
        while (ob_get_level()) ob_end_clean();
        ob_start();
        
        header('Content-Type: application/json');
        
        try {
            // Increase limits for AI processing
            set_time_limit(120);
            ini_set('memory_limit', '256M');

            $message = $_POST['message'] ?? '';
            if (!$message) {
                throw new \Exception("Message is required.");
            }

            $db = Database::getInstance();
            $ai_config = $db->query("SELECT chatbot_limit_guest, chatbot_limit_member FROM `ai_config` WHERE `id` = 1")->fetch();
            $limit_guest = intval($ai_config['chatbot_limit_guest'] ?? 5);
            $limit_member = intval($ai_config['chatbot_limit_member'] ?? 20);

            $sessionId = session_id();
            $userId = $_SESSION['user']['user_id'] ?? ($_SESSION['user']['id'] ?? null);
            $ipAddr = $_SERVER['REMOTE_ADDR'];

            // Limit Enforcement
            $current_limit = $userId ? $limit_member : $limit_guest;
            if ($current_limit > 0) {
                if ($userId) {
                    $stmt = $db->prepare("SELECT COUNT(*) FROM `chatbot_logs` WHERE (user_id = ? OR user_id = ?) AND created_at >= CURDATE()");
                    $stmt->execute([$userId, $_SESSION['user']['id'] ?? $userId]);
                } else {
                    $stmt = $db->prepare("SELECT COUNT(*) FROM `chatbot_logs` WHERE ip_address = ? AND user_id IS NULL AND created_at >= CURDATE()");
                    $stmt->execute([$ipAddr]);
                }
                $usage = $stmt->fetchColumn();
                if ($usage >= $current_limit) {
                    $user_label = $userId ? "회원" : "비회원";
                    throw new \Exception("오늘 허용된 질문 횟수({$current_limit}회)를 모두 사용하셨습니다. {$user_label}은 하루에 {$current_limit}번까지 질문 가능합니다.");
                }
            }

            // 1. Get site config
            $config = $db->query("SELECT * FROM `config` WHERE `id` = 1")->fetch();
            $site_name = $config['site_name'] ?? 'Neuron AI';
            
            $site_details = [
                "Company Name" => $config['company_name'] ?? '',
                "Owner" => $config['company_owner'] ?? '',
                "Business License" => $config['company_license_num'] ?? '',
                "Phone" => $config['company_tel'] ?? '',
                "Email" => $config['company_email'] ?? '',
                "Address" => $config['company_address'] ?? '',
                "Welcome Point" => ($config['join_point'] ?? 0) . " pts",
                "Default Join Level (For New Users)" => "Level " . ($config['join_level'] ?? 1),
                "Intro" => strip_tags($config['company_intro'] ?? '')
            ];
            
            $company_info = "";
            foreach ($site_details as $key => $val) {
                if ($val) $company_info .= "{$key}: {$val}\n";
            }
            
            // 2. Get recent board posts (titles and snippets)
            $posts_context = "Recent Board Posts:\n";
            try {
                $posts = $db->query("SELECT title, content FROM `posts` ORDER BY created_at DESC LIMIT 5")->fetchAll();
                foreach ($posts as $p) {
                    $snippet = mb_substr(strip_tags($p['content']), 0, 100);
                    $posts_context .= "- Title: {$p['title']} | Content: {$snippet}...\n";
                }
            } catch (\Exception $e) { $posts_context .= "None\n"; }

            // 3. Get all pages
            $pages_context = "Custom Pages:\n";
            try {
                $pages = $db->query("SELECT title, content FROM `pages`")->fetchAll();
                foreach ($pages as $pg) {
                    $snippet = mb_substr(strip_tags($pg['content']), 0, 150);
                    $pages_context .= "- Page: {$pg['title']} | Preview: {$snippet}...\n";
                }
            } catch (\Exception $e) { $pages_context .= "None\n"; }

            // 3.5. Get FAQs
            $faqs_context = "Frequently Asked Questions (FAQ):\n";
            try {
                $faqs = $db->query("SELECT category, question, answer FROM `faq` ORDER BY display_order ASC")->fetchAll();
                foreach ($faqs as $f) {
                    $faqs_context .= "[{$f['category']}] Q: {$f['question']} | A: {$f['answer']}\n";
                }
                if (empty($faqs)) $faqs_context .= "None\n";
            } catch (\Exception $e) { $faqs_context .= "None\n"; }

            // 4. Get key .md file contents
            $md_files = ['README.md', 'SECURITY.md', 'TEMPLATE_GUIDE.md'];
            $md_context = "Key Documents (.md):\n";
            foreach ($md_files as $file) {
                $path = CM_PATH . '/' . $file;
                if (file_exists($path)) {
                    $content = @file_get_contents($path);
                    if ($content) {
                        $content = mb_substr($content, 0, 500);
                        $md_context .= "--- File: {$file} ---\n{$content}\n\n";
                    }
                }
            }

            // 5. Get conversation history (Context Memory)
            $current_history = "";
            $historical_memory = "";
            $global_knowledge = "";
            try {
                error_log("Chatbot Debug - Detected UserId: " . print_r($userId, true));
                
                // 5a. Current Session (Immediate Context)
                $history = $db->prepare("SELECT question, answer FROM `chatbot_logs` WHERE session_id = ? ORDER BY created_at DESC LIMIT 5");
                $history->execute([$sessionId]);
                $history_rows = array_reverse($history->fetchAll());
                foreach ($history_rows as $row) {
                    $current_history .= "User: {$row['question']}\nAI: " . strip_tags($row['answer']) . "\n";
                }

                // 5b. Historical Memory (Cross-session memory for this specific user/IP)
                // We look for older logs from the same user ID (numeric or string) or IP
                if ($userId) {
                    $stmt = $db->prepare("SELECT question, answer, created_at FROM `chatbot_logs` WHERE (user_id = ? OR user_id = ?) AND session_id != ? ORDER BY created_at DESC LIMIT 5");
                    // We check both session's user_id (string) and id (int) if they differ
                    $stmt->execute([$userId, $_SESSION['user']['id'] ?? $userId, $sessionId]);
                } else {
                    $stmt = $db->prepare("SELECT question, answer, created_at FROM `chatbot_logs` WHERE ip_address = ? AND user_id IS NULL AND session_id != ? ORDER BY created_at DESC LIMIT 5");
                    $stmt->execute([$ipAddr, $sessionId]);
                }
                $historical_rows = array_reverse($stmt->fetchAll());
                foreach ($historical_rows as $row) {
                    $historical_memory .= "[{$row['created_at']}] User: {$row['question']}\nAI: " . strip_tags($row['answer']) . "\n";
                }

                // 5c. Global Recent Logs (General Knowledge from others)
                $global = $db->prepare("SELECT question, answer FROM `chatbot_logs` WHERE session_id != ? AND (user_id != ? OR user_id IS NULL) AND ip_address != ? ORDER BY created_at DESC LIMIT 5");
                $global->execute([$sessionId, $userId, $ipAddr]);
                $global_rows = $global->fetchAll();
                foreach ($global_rows as $row) {
                    $q = $this->maskPersonalData($row['question']);
                    $a = $this->maskPersonalData(strip_tags($row['answer']));
                    $global_knowledge .= "- Q: {$q} | A: {$a}\n";
                }

                // 5d. Get Current User's Profile (If logged in)
                $user_profile_context = "";
                if ($userId) {
                    // Try to match by string user_id first, then fallback to numeric id
                    $u = $db->prepare("SELECT user_id, username, point, level, role, created_at FROM `users` WHERE user_id = ? OR id = ?");
                    $u->execute([$userId, $_SESSION['user']['id'] ?? 0]);
                    $userData = $u->fetch();
                    if ($userData) {
                        $user_profile_context = "Current Logged-in User Profile:\n";
                        $user_profile_context .= "- ID: {$userData['user_id']}\n";
                        $user_profile_context .= "- Name: {$userData['username']}\n";
                        $user_profile_context .= "- Points: {$userData['point']}\n";
                        $user_profile_context .= "- AUTHENTICATED_CURRENT_LEVEL: Level {$userData['level']}\n";
                        $user_profile_context .= "- Role: {$userData['role']}\n";
                        $user_profile_context .= "- Joined: {$userData['created_at']}\n";
                        
                        error_log("Chatbot Debug - Fetched User Level: " . $userData['level']);
                    }
                }
            } catch (\Exception $e) { 
                error_log("Chatbot Debug - History Error: " . $e->getMessage());
            }
            
            // Build comprehensive context
            $context = "You are a friendly AI chatbot for '{$site_name}'.\n";
            
            if ($user_profile_context) {
                $context .= "--- [CRITICAL] CURRENT_LOGGED_IN_USER_DATA ---\n" . $user_profile_context . "\n";
                $context .= "INSTRUCTION: The user you are talking to has the level shown above. If they ask about their level or points, use the data in this section ONLY.\n\n";
            }

            $context .= "--- GENERAL_SITE_INFORMATION ---\n" . strip_tags($company_info) . "\n\n";
            
            if ($historical_memory) {
                $context .= "--- THIS USER'S PAST CONVERSATIONS (Prior to today) ---\n" . $historical_memory . "\n";
            }
            
            $context .= "--- CURRENT CONVERSATION (Memory) ---\n" . $current_history . "\n";
            $context .= "--- GENERAL KNOWLEDGE (Others' Q&A) ---\n" . $global_knowledge . "\n";
            $context .= "--- DOCUMENTS & POSTS ---\n" . $posts_context . $pages_context . $faqs_context . $md_context . "\n";
            
            $context .= "PERSONALIZATION RULES:\n";
            $context .= "PERSONALIZATION RULES (행동 지침):\n";
            $context .= "1. [기억하기] '과거 대화 이력(PAST CONVERSATIONS)'이 있다면 이를 활용해 사용자를 알아보세요. (예: '다시 뵙네요!', '지난번에 말씀하신...').\n";
            $context .= "2. [우선순위] 불특정 다수의 지식보다 사용자 개인의 기록을 최우선으로 참고하세요.\n";
            $context .= "3. [개인정보 보호] 타인의 개인정보(이메일, 전화번호 등)는 절대 발설하지 마세요.\n";
            $context .= "4. [중요: 사용자 정보] '현재 로그인한 사용자 정보(CURRENT_LOGGED_IN_USER_DATA)'가 제공된다면, 그 안의 레벨과 포인트가 유일한 정답입니다. 사이트 기본 설정값보다 우선시하세요.\n";
            $context .= "5. [만렙 인지] 레벨 10은 이 사이트의 **최대 등급(MAX)**입니다. 레벨 10인 사용자에게 '레벨업 하세요' 같은 조언은 하지 마세요.\n";
            $context .= "6. [최고관리자 예우] 'Role: admin'은 이 사이트의 **주인(최고관리자)**입니다. '최고관리자님'이라고 호칭하고 각별한 예우를 갖춰 정중하게 답변하세요.\n";
            $context .= "7. [언어] 답변은 **완벽하고 자연스러운 한국어**만 사용하세요. 문맥에 맞지 않는 외국어나 번역투를 절대 섞지 마세요.\n";
            $context .= "8. [간결성] 답변은 **최대한 짧게 쉽고 간결하게** 작성해주세요.\n";
            $context .= "9. [가독성 및 줄바꿈] 답변이 길어질 경우 반드시 적절한 줄바꿈을 사용하여 가독성을 높이세요. 나열식 정보는 반드시 한 줄에 하나씩 작성하세요.\n";
            $context .= "10. [목록 형식] 정보를 나열할 때는 반드시 숫자(1., 2.)나 글머리 기호(-)를 사용하여 줄을 나누어 작성하세요.\n";
            $context .= "11. [출력 형식] 서술형 답변이 길어질 경우 의미 단위로 끊어서 줄바꿈을 자주 해주세요.\n";
            
            $fullPrompt = "{$context}\n\nUser Question: {$message}\nAI Answer:";

            $aiService = new AiService();
            $answer = $aiService->generate($fullPrompt);

            // Clean markdown code blocks if present
            $answer = preg_replace('/^```(?:html|json)?\n?|```$/m', '', trim($answer));

            // Convert newlines to <br> tags for HTML display
            $answer = nl2br($answer);

            // 6. Store log in DB
            try {
                $stmt = $db->prepare("INSERT INTO `chatbot_logs` (session_id, user_id, question, answer, ip_address) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([
                    $sessionId,
                    $_SESSION['user']['user_id'] ?? null,
                    $message,
                    $answer,
                    $_SERVER['REMOTE_ADDR']
                ]);
            } catch (\Exception $e) { }

            ob_end_clean(); // Clear any warnings
            echo json_encode(['success' => true, 'answer' => $answer]);
        } catch (\Exception $e) {
            error_log("Chatbot Error: " . $e->getMessage());
            if (ob_get_level()) ob_end_clean();
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Mask sensitive information like phone numbers and emails
     */
    private function maskPersonalData($text)
    {
        // Mask Emails
        $text = preg_replace('/[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}/i', '[EMAIL]', $text);
        // Mask Phone numbers (Korean and Global styles)
        $text = preg_replace('/(\d{2,3})[- .]?(\d{3,4})[- .]?(\d{4})/', '$1-****-****', $text);
        return $text;
    }

    public function adminLogs()
    {
        $db = Database::getInstance();
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $search = $_GET['search'] ?? '';
        $date_start = $_GET['date_start'] ?? '';
        $date_end = $_GET['date_end'] ?? '';

        $where = "WHERE 1=1";
        $params = [];

        if ($search) {
            $where .= " AND (question LIKE ? OR answer LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        if ($date_start) {
            $where .= " AND created_at >= ?";
            $params[] = $date_start . " 00:00:00";
        }

        if ($date_end) {
            $where .= " AND created_at <= ?";
            $params[] = $date_end . " 23:59:59";
        }

        $totalItems = $db->prepare("SELECT COUNT(*) FROM chatbot_logs $where");
        $totalItems->execute($params);
        $totalCount = $totalItems->fetchColumn();
        
        $totalPages = ceil($totalCount / $limit);

        $stmt = $db->prepare("SELECT * FROM chatbot_logs $where ORDER BY created_at DESC LIMIT $limit OFFSET $offset");
        $stmt->execute($params);
        $logs = $stmt->fetchAll();

        \App\Core\View::render('plugins/chatbot/Views/admin_logs', [
            'logs' => $logs,
            'page' => $page,
            'totalPages' => $totalPages,
            'search' => $search,
            'date_start' => $date_start,
            'date_end' => $date_end
        ]);
    }

    public function cleanupLogs()
    {
        $db = Database::getInstance();
        $months = $_POST['months'] ?? '';
        
        if ($months === 'all') {
            $db->exec("TRUNCATE TABLE chatbot_logs");
            header("Location: /admin/chatbot/logs?success=cleanup&count=all");
        } elseif ((int)$months > 0) {
            $stmt = $db->prepare("DELETE FROM chatbot_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL ? MONTH)");
            $stmt->execute([(int)$months]);
            header("Location: /admin/chatbot/logs?success=cleanup&count=" . $stmt->rowCount());
        } else {
            header("Location: /admin/chatbot/logs?error=invalid_months");
        }
        exit;
    }

}
