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
            $headquartersAddress = $_POST['headquarters_address'] ?? '';
            $faxNumber = $_POST['fax_number'] ?? '';
            $managerName = $_POST['manager_name'] ?? '';
            $managerEmail = $_POST['manager_email'] ?? '';
            $factoryAddress = $_POST['factory_address'] ?? '';
            $factoryContact = $_POST['factory_contact'] ?? '';

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
                    $updateStmt = $db->prepare("UPDATE vendor_settings SET company_name = :cn, url_slug = :us, contact_number = :cnm, company_logo = :logo, price_excel_path = :pep, prices_data = :pd, headquarters_address = :hq, fax_number = :fax, manager_name = :mgr, manager_email = :email, factory_address = :faddr, factory_contact = :fcont WHERE user_id = :uid");
                    $updateStmt->execute([
                        'cn' => $companyName,
                        'us' => $urlSlug,
                        'cnm' => $contactNumber,
                        'logo' => $companyLogo,
                        'pep' => $priceExcelPath,
                        'pd' => $pricesData,
                        'hq' => $headquartersAddress,
                        'fax' => $faxNumber,
                        'mgr' => $managerName,
                        'email' => $managerEmail,
                        'faddr' => $factoryAddress,
                        'fcont' => $factoryContact,
                        'uid' => $userId
                    ]);
                } else {
                    $insertStmt = $db->prepare("INSERT INTO vendor_settings (user_id, company_name, url_slug, contact_number, company_logo, price_excel_path, prices_data, headquarters_address, fax_number, manager_name, manager_email, factory_address, factory_contact) VALUES (:uid, :cn, :us, :cnm, :logo, :pep, :pd, :hq, :fax, :mgr, :email, :faddr, :fcont)");
                    $insertStmt->execute([
                        'uid' => $userId,
                        'cn' => $companyName,
                        'us' => $urlSlug,
                        'cnm' => $contactNumber,
                        'logo' => $companyLogo,
                        'pep' => $priceExcelPath,
                        'pd' => $pricesData,
                        'hq' => $headquartersAddress,
                        'fax' => $faxNumber,
                        'mgr' => $managerName,
                        'email' => $managerEmail,
                        'faddr' => $factoryAddress,
                        'fcont' => $factoryContact
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

    public function quotePrice($vars) {
        if (!isset($_SESSION['user']) || empty($_SESSION['user'])) {
            $this->redirect('/login');
            return;
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

        $stmt = $db->prepare("SELECT * FROM vendor_settings WHERE user_id = :uid");
        $stmt->execute(['uid' => $userId]);
        $settings = $stmt->fetch() ?: [];

        $this->view('vendor/quote_price', ['quote' => $quote, 'settings' => $settings]);
    }

    public function quoteDocument($vars) {
        if (!isset($_SESSION['user']) || empty($_SESSION['user'])) {
            $this->redirect('/login');
            return;
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

        $stmt = $db->prepare("SELECT * FROM vendor_settings WHERE user_id = :uid");
        $stmt->execute(['uid' => $userId]);
        $settings = $stmt->fetch() ?: [];

        $this->view('vendor/quote_document', ['quote' => $quote, 'settings' => $settings]);
    }
}
