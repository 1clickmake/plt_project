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
}
