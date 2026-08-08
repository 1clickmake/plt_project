<?php

namespace Plugins\adminai\Controllers;

use App\Core\Database;
use App\Core\View;
use Plugins\aimanager\Services\AiService;
use PDO;

class AdminAiController
{
    /**
     * Handle AI queries from the admin dashboard
     */
    public function query()
    {
        header('Content-Type: application/json');
        
        try {
            $prompt = $_POST['prompt'] ?? '';
            if (!$prompt) {
                throw new \Exception("질문을 입력해주세요.");
            }

            $db = Database::getInstance();
            $aiService = new AiService();

            // Prepare system instructions for administrative tasks
            $systemInstruction = "You are a professional Admin AI Assistant for a PHP management system. 
            You must provide administrative insights, data visualizations, and facilitate data management tasks.

            DATABASE CONTEXT (You can use this data for your analysis):
            1. 'users' table: id, username, email, level, created_at.
            2. 'visitor_logs' table: ip_address, visit_date, last_active_at.

            CAPABILITIES:
            1. CHART: Create line, bar, or pie charts for trends.
            2. DOWNLOAD: Provide a link to download data in Excel (.xls) format. 
               - For users: /admin/ai/download?type=users
               - Filter users by level: /admin/ai/download?type=users&level={number}
            3. LIST: Display small sets of data in a table on the dashboard.
            4. TEXT: Provide general help or explanations in HTML.

            RESPONSE FORMAT (Must be valid JSON):
            {
                \"type\": \"CHART\" | \"DOWNLOAD\" | \"LIST\" | \"TEXT\",
                \"title\": \"Clear title of the result\",
                \"chartType\": \"line\" | \"bar\" | \"pie\", (only for CHART)
                \"labels\": [\"label1\", ...], (for CHART)
                \"data\": [val1, ...], (for CHART)
                \"headers\": [\"Col1\", ...], (for LIST)
                \"rows\": [ [\"cell1\", ...], ... ], (for LIST)
                \"downloadUrl\": \"/admin/ai/download?...\", (only for DOWNLOAD)
                \"message\": \"Instructional message for the user\", (for DOWNLOAD/TEXT)
                \"content\": \"HTML formatted answer\", (for TEXT)
                \"explanation\": \"Brief technical explanation in Korean\"
            }

            STRICT RULES:
            - ALWAYS respond in Korean.
            - If data is requested and available in the provided 'Context Stats', use it for charts or lists.
            - For Excel downloads, use the exact URL formats provided in CAPABILITIES.
            - Do not invent data; use the context provided below.
            - IMPORTANT: Do not use nested double quotes inside JSON values. Use single quotes or no quotes for emphasis (e.g., Use '다운로드' instead of \"다운로드\").
            - Output ONLY the JSON object, no markdown blocks.";

            // Fetch some context data to help the AI
            $stats = $this->getContextData($db);
            
            $fullPrompt = $systemInstruction . "\n\nContext Stats:\n" . json_encode($stats, JSON_UNESCAPED_UNICODE) . "\n\nUser Request: " . $prompt . "\n\nReturn ONLY the JSON object.";

            $content = $aiService->generate($fullPrompt);
            
            // Clean up the response more robustly
            // 1. Remove markdown blocks if present
            $content = preg_replace('/```json\s*|\s*```/i', '', $content);
            $content = trim($content);
            
            // 2. Fix common nested quote issues in AI generated JSON if possible
            // This is a defensive check
            $result = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                // If parsing failed, try to find the JSON object part
                if (preg_match('/\{.*\}/s', $content, $matches)) {
                    $result = json_decode($matches[0], true);
                }
            }

            if (!$result) {
                // Return original content as text if it's still not valid JSON
                $result = [
                    'type' => 'TEXT',
                    'content' => $content,
                    'explanation' => 'AI 응답 형식이 요건에 맞지 않아 텍스트로 표시합니다.'
                ];
            }

            echo json_encode(['success' => true, 'data' => $result]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Fetch context summaries for the AI
     */
    private function getContextData($db)
    {
        $stats = [];
        
        // Join trends (past 7 days)
        $stats['join_trends'] = $db->query("SELECT DATE(created_at) as date, COUNT(*) as count FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 14 DAY) GROUP BY DATE(created_at) ORDER BY date ASC")->fetchAll();
        
        // Visitor trends (past 7 days)
        $stats['visitor_trends'] = $db->query("SELECT visit_date, COUNT(*) as count FROM visitor_logs WHERE visit_date >= DATE_SUB(NOW(), INTERVAL 14 DAY) GROUP BY visit_date ORDER BY visit_date ASC")->fetchAll();
        
        // User levels count
        $stats['user_levels'] = $db->query("SELECT level, COUNT(*) as count FROM users GROUP BY level")->fetchAll();

        return $stats;
    }

    /**
     * Handle CSV/Excel downloads
     */
    public function download()
    {
        $type = $_GET['type'] ?? '';
        $level = $_GET['level'] ?? null;
        
        $db = Database::getInstance();
        
        if ($type === 'users') {
            $sql = "SELECT username, email, level, point, country, created_at FROM users";
            $params = [];
            if ($level !== null) {
                $sql .= " WHERE level = ?";
                $params[] = (int)$level;
            }
            $sql .= " ORDER BY created_at DESC";
            
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Clear any previous output buffers to prevent corruption
            if (ob_get_length()) ob_end_clean();

            // Correct Headers for .xls (HTML-based Excel)
            header('Content-Type: application/vnd.ms-excel; charset=utf-8');
            header('Content-Disposition: attachment; filename=users_export_' . date('Ymd') . '.xls');
            header('Cache-Control: max-age=0');

            // Output the Excel-compatible HTML structure
            echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
            echo '<head>';
            echo '<meta http-equiv="Content-type" content="text/html; charset=utf-8" />';
            echo '<!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>Sheet1</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]-->';
            echo '<style>
                table { border-collapse: collapse; }
                th { background-color: #f2f2f2; font-weight: bold; border: 0.5pt solid #000000; }
                td { border: 0.5pt solid #000000; mso-number-format:"\@"; }
            </style>';
            echo '</head><body>';
            echo '<table>';
            
            // Header Row
            $headers = ['아이디', '이메일', '레벨', '포인트', '국가', '가입일'];
            echo '<tr>';
            foreach ($headers as $h) {
                echo '<th>' . $h . '</th>';
            }
            echo '</tr>';

            if (!empty($users)) {
                foreach ($users as $user) {
                    echo '<tr>';
                    echo '<td>' . htmlspecialchars($user['username']) . '</td>';
                    echo '<td>' . htmlspecialchars($user['email']) . '</td>';
                    echo '<td>' . htmlspecialchars($user['level']) . '</td>';
                    echo '<td>' . htmlspecialchars($user['point']) . '</td>';
                    echo '<td>' . htmlspecialchars($user['country'] ?? '-') . '</td>';
                    echo '<td>' . htmlspecialchars($user['created_at']) . '</td>';
                    echo '</tr>';
                }
            } else {
                echo '<tr><td colspan="' . count($headers) . '" style="text-align:center;">데이터가 없습니다.</td></tr>';
            }

            echo '</table>';
            echo '</body></html>';
            exit;
        }

        die("Invalid download type");
    }
}
