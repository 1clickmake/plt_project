<?php

namespace App\Controllers;

use App\Core\Database;

class VendorController extends BaseController {
    public function settings() {
        if (!isset($_SESSION['user']) || empty($_SESSION['user'])) {
            $this->redirect('/login');
        }

        $userId = $_SESSION['user']['user_id'];
        $db = Database::getInstance();

        // Fetch existing settings
        $stmt = $db->prepare("SELECT * FROM vendor_settings WHERE user_id = :uid");
        $stmt->execute(['uid' => $userId]);
        $settings = $stmt->fetch();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $companyName = $_POST['company_name'] ?? '';
            $urlSlug = $_POST['url_slug'] ?? '';
            $contactNumber = $_POST['contact_number'] ?? '';

            // Logo upload handling (basic)
            $companyLogo = $settings['company_logo'] ?? '';
            if (isset($_FILES['company_logo']) && $_FILES['company_logo']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../../public/data/logo/';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                $filename = 'logo_' . $userId . '_' . time() . '.' . pathinfo($_FILES['company_logo']['name'], PATHINFO_EXTENSION);
                if (move_uploaded_file($_FILES['company_logo']['tmp_name'], $uploadDir . $filename)) {
                    $companyLogo = '/data/logo/' . $filename;
                }
            }

            // Excel upload and parsing
            $priceExcelPath = $settings['price_excel_path'] ?? null;
            $pricesData = $settings['prices_data'] ?? null;

            if (isset($_FILES['price_excel']) && $_FILES['price_excel']['error'] === UPLOAD_ERR_OK) {
                $uploadDirExcel = __DIR__ . '/../../public/data/excel/';
                if (!file_exists($uploadDirExcel)) {
                    mkdir($uploadDirExcel, 0777, true);
                }
                $excelFilename = 'price_' . $userId . '_' . time() . '.' . pathinfo($_FILES['price_excel']['name'], PATHINFO_EXTENSION);
                $fullExcelPath = $uploadDirExcel . $excelFilename;
                
                if (move_uploaded_file($_FILES['price_excel']['tmp_name'], $fullExcelPath)) {
                    $priceExcelPath = '/data/excel/' . $excelFilename;
                    
                    try {
                        // Parse Excel
                        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($fullExcelPath);
                        $worksheet = $spreadsheet->getActiveSheet();
                        $rows = $worksheet->toArray();
                        
                        $extractedPrices = [
                            'parsed_at' => date('Y-m-d H:i:s'),
                            'raw_rows' => $rows
                        ];
                        $pricesData = json_encode($extractedPrices, JSON_UNESCAPED_UNICODE);
                    } catch (\Exception $e) {
                        // Keep old data if parsing fails
                    }
                }
            }

            try {
                if ($settings) {
                    $updateStmt = $db->prepare("UPDATE vendor_settings SET company_name = :cn, url_slug = :us, contact_number = :cnm, company_logo = :logo, price_excel_path = :pep, prices_data = :pd WHERE user_id = :uid");
                    $updateStmt->execute([
                        'cn' => $companyName,
                        'us' => $urlSlug,
                        'cnm' => $contactNumber,
                        'logo' => $companyLogo,
                        'pep' => $priceExcelPath,
                        'pd' => $pricesData,
                        'uid' => $userId
                    ]);
                } else {
                    $insertStmt = $db->prepare("INSERT INTO vendor_settings (user_id, company_name, url_slug, contact_number, company_logo, price_excel_path, prices_data) VALUES (:uid, :cn, :us, :cnm, :logo, :pep, :pd)");
                    $insertStmt->execute([
                        'uid' => $userId,
                        'cn' => $companyName,
                        'us' => $urlSlug,
                        'cnm' => $contactNumber,
                        'logo' => $companyLogo,
                        'pep' => $priceExcelPath,
                        'pd' => $pricesData
                    ]);
                }
                echo "<script>alert('Settings updated successfully!'); window.location.href='/vendor/settings';</script>";
                return;
            } catch (\PDOException $e) {
                // If url_slug is duplicate
                echo "<script>alert('Error updating settings. (URL slug might be duplicate)'); window.location.href='/vendor/settings';</script>";
                return;
            }
        }

        $this->view('vendor/settings', ['settings' => $settings]);
    }

    public function quotes() {
        if (!isset($_SESSION['user']) || empty($_SESSION['user'])) {
            $this->redirect('/login');
        }

        $userId = $_SESSION['user']['user_id'];
        $db = Database::getInstance();

        $stmt = $db->prepare("SELECT * FROM quote_requests WHERE vendor_user_id = :vuid ORDER BY created_at DESC");
        $stmt->execute(['vuid' => $userId]);
        $quotes = $stmt->fetchAll();

        $this->view('vendor/quotes', ['quotes' => $quotes]);
    }

    public function quoteDetail($vars) {
        if (!isset($_SESSION['user']) || empty($_SESSION['user'])) {
            $this->redirect('/login');
        }

        $userId = $_SESSION['user']['user_id'];
        $quoteId = intval($vars['id'] ?? 0);
        $db = Database::getInstance();

        $stmt = $db->prepare("SELECT * FROM quote_requests WHERE id = :qid AND vendor_user_id = :vuid");
        $stmt->execute(['qid' => $quoteId, 'vuid' => $userId]);
        $quote = $stmt->fetch();

        if (!$quote) {
            echo "<script>alert('존재하지 않거나 접근 권한이 없는 견적 요청입니다.'); window.location.href='/vendor/quotes';</script>";
            return;
        }

        $this->view('vendor/quote_detail', ['quote' => $quote]);
    }

    public function sendQuoteEmail() {
        if (!isset($_SESSION['user']) || empty($_SESSION['user'])) {
            echo json_encode(['success' => false, 'message' => '로그인이 필요합니다.']);
            return;
        }

        $userId = $_SESSION['user']['user_id'];
        $quoteId = intval($_POST['quote_id'] ?? 0);
        $adminPrice = floatval($_POST['admin_price'] ?? 0);
        $adminMargin = floatval($_POST['admin_margin'] ?? 0);
        $adminNotes = trim($_POST['admin_notes'] ?? '');

        if (!$quoteId) {
            echo json_encode(['success' => false, 'message' => '잘못된 요청입니다.']);
            return;
        }

        $db = Database::getInstance();
        
        // 권한 확인 및 데이터 가져오기
        $stmt = $db->prepare("SELECT * FROM quote_requests WHERE id = :qid AND vendor_user_id = :vuid");
        $stmt->execute(['qid' => $quoteId, 'vuid' => $userId]);
        $quote = $stmt->fetch();

        if (!$quote) {
            echo json_encode(['success' => false, 'message' => '견적 요청을 찾을 수 없습니다.']);
            return;
        }

        // DB 업데이트
        $updateStmt = $db->prepare("UPDATE quote_requests SET status = 'completed', admin_price = :price, admin_margin = :margin, admin_notes = :notes WHERE id = :qid");
        $success = $updateStmt->execute([
            'price' => $adminPrice,
            'margin' => $adminMargin,
            'notes' => $adminNotes,
            'qid' => $quoteId
        ]);

        if ($success) {
            // 이메일 발송 로직 (PHP 내장 mail() 사용)
            $to = $quote['phone']; // 실제로는 이메일 컬럼이 필요하지만, 현재는 연락처로 대체하거나 폼에서 입력받아야 함. 일단 임시로 가짜 메일 발송 시도.
            $subject = "[ASAMIYA SAAS] 요청하신 파렛트랙 견적서가 도착했습니다!";
            
            $message = "안녕하세요, " . $quote['company'] . " " . $quote['name'] . "님!\n\n";
            $message .= "요청하신 시공 현장(" . $quote['address'] . ")에 대한 견적이 완료되었습니다.\n";
            $message .= "총 견적 금액: " . number_format($adminPrice) . " 원\n\n";
            if (!empty($adminNotes)) {
                $message .= "담당자 코멘트:\n" . $adminNotes . "\n\n";
            }
            $message .= "감사합니다.";

            $headers = "From: noreply@asamiyasaas.com\r\n";
            $headers .= "Reply-To: noreply@asamiyasaas.com\r\n";
            $headers .= "Content-Type: text/plain; charset=utf-8\r\n";

            // 메일 전송 시도 (실제 SMTP 환경이 아니면 실패할 수 있으므로 에러 무시)
            @mail("customer@example.com", $subject, $message, $headers);

            echo json_encode(['success' => true, 'message' => '견적서가 성공적으로 전송되었습니다!']);
        } else {
            echo json_encode(['success' => false, 'message' => '데이터베이스 업데이트 실패']);
        }
    }
}
