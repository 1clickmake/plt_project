<?php

namespace App\Controllers;

use App\Core\Database;

class VendorController extends BaseController {
    public function index() {
        global $user;
        if (!isset($_SESSION['user']) || empty($_SESSION['user'])) {
            $this->redirect('/login');
            return;
        }
        if (empty($user)) {
            $user = $_SESSION['user'];
        }
        
        $vendorUserId = $user['id'] ?? null;
        $vendorUserStrId = $user['user_id'] ?? '';
        $db = \App\Core\Database::getInstance();
        
        // 오늘 접수된 견적 건수
        $stmt = $db->prepare("SELECT COUNT(*) FROM quote_requests WHERE (vendor_user_id = :vuid1 OR vendor_user_id = :vuid2) AND DATE(created_at) = CURDATE()");
        $stmt->execute(['vuid1' => $vendorUserId, 'vuid2' => $vendorUserStrId]);
        $todayQuotesCount = intval($stmt->fetchColumn());
        
        // 발송 한도 정보
        $balanceInfo = $this->getQuoteBalance($vendorUserId ?: $vendorUserStrId);

        // 금일 메일 발송 횟수
        $stmt = $db->prepare("SELECT COUNT(*) FROM quote_requests WHERE (vendor_user_id = :vuid1 OR vendor_user_id = :vuid2) AND is_mailed = 1 AND DATE(mailed_at) = CURDATE()");
        $stmt->execute(['vuid1' => $vendorUserId, 'vuid2' => $vendorUserStrId]);
        $todayMailedCount = intval($stmt->fetchColumn());
        
        // 공급사 설정 상태 확인
        $stmtSettings = $db->prepare("SELECT * FROM vendor_settings WHERE user_id = :vuid1 OR user_id = :vuid2");
        $stmtSettings->execute(['vuid1' => $vendorUserId, 'vuid2' => $vendorUserStrId]);
        $vendorSettings = $stmtSettings->fetch(\PDO::FETCH_ASSOC);
        $isSettingsComplete = $vendorSettings && !empty($vendorSettings['url_slug']);

        // 금일 폼 접속 횟수
        $stmtVisit = $db->prepare("SELECT COUNT(*) FROM vendor_page_visits WHERE (vendor_user_id = :vuid1 OR vendor_user_id = :vuid2) AND DATE(visited_at) = CURDATE()");
        $stmtVisit->execute(['vuid1' => $vendorUserId, 'vuid2' => $vendorUserStrId]);
        $todayVisitCount = intval($stmtVisit->fetchColumn());

        // 최근 7일 접속 통계 (일자별)
        $sevenDaysAgo = date('Y-m-d 00:00:00', strtotime('-6 days'));
        $stmtStats = $db->prepare("
            SELECT DATE(visited_at) as visit_date, COUNT(*) as cnt 
            FROM vendor_page_visits 
            WHERE (vendor_user_id = :vuid1 OR vendor_user_id = :vuid2) AND visited_at >= :sdago 
            GROUP BY visit_date 
            ORDER BY visit_date ASC
        ");
        $stmtStats->execute(['vuid1' => $vendorUserId, 'vuid2' => $vendorUserStrId, 'sdago' => $sevenDaysAgo]);
        $visitStatsRaw = $stmtStats->fetchAll(\PDO::FETCH_ASSOC);
        
        // 7일치 빈 날짜 배열 채우기
        $visitStats = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("-$i days"));
            $visitStats[$d] = 0;
        }
        foreach ($visitStatsRaw as $row) {
            $visitStats[$row['visit_date']] = intval($row['cnt']);
        }

        $this->view('vendor/index', [
            'todayQuotesCount' => $todayQuotesCount,
            'todayMailedCount' => $todayMailedCount,
            'balanceInfo'      => $balanceInfo,
            'vendorSettings'   => $vendorSettings,
            'isSettingsComplete'=> $isSettingsComplete,
            'todayVisitCount'  => $todayVisitCount,
            'visitStats'       => $visitStats
        ]);
    }
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
            $bankAccount = $_POST['bank_account'] ?? '';
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
                    @mkdir($uploadDirExcel, 0777, true);
                }
                $excelFilename = 'price_' . $userId . '_' . time() . '.' . pathinfo($_FILES['price_excel']['name'], PATHINFO_EXTENSION);
                $fullExcelPath = $uploadDirExcel . $excelFilename;
                
                if (@move_uploaded_file($_FILES['price_excel']['tmp_name'], $fullExcelPath)) {
                    $priceExcelPath = '/data/excel/' . $excelFilename;
                    
                    try {
                        // AI 파싱이 오래 걸릴 수 있으므로 PHP 실행 시간 무제한(또는 300초)으로 연장
                        set_time_limit(300);
                        
                        // AI-driven Excel Parsing
                        $aiService = \App\Services\AI\AIExtractorFactory::create();
                        $excelText = $aiService->extractTextFromExcel($fullExcelPath);
                        $extractedPricing = $aiService->extractPricingFromJson($excelText);
                        
                        // 검증 로직 추가 (AI가 일부 값을 찾지 못했거나 실패한 경우 방어)
                        if (isset($extractedPricing['validation']['is_complete']) && $extractedPricing['validation']['is_complete'] !== true) {
                            $missing = implode(", ", $extractedPricing['validation']['missing_fields'] ?? ['Unknown']);
                            throw new \Exception("AI 파싱이 불완전합니다. 누락된 필드: " . $missing);
                        }

                        // 원본 파일명 삽입
                        if (!isset($extractedPricing['meta'])) {
                            $extractedPricing['meta'] = [];
                        }
                        $extractedPricing['meta']['source_file'] = $_FILES['price_excel']['name'];

                        $pricesData = json_encode($extractedPricing, JSON_UNESCAPED_UNICODE);

                        // Save to vendor_pricing_rules (Versioning)
                        $insertRuleStmt = $db->prepare("INSERT INTO vendor_pricing_rules (vendor_id, applied_month, source_file, pricing_data) VALUES (:vid, :am, :sf, :pd)");
                        $insertRuleStmt->execute([
                            'vid' => $userId,
                            'am' => $extractedPricing['meta']['base_month'] ?? date('Y-m'),
                            'sf' => $_FILES['price_excel']['name'],
                            'pd' => $pricesData
                        ]);

                        // Redirect to settings page with success message
                        // For now just continue...
                    } catch (\Exception $e) {
                        // Keep old data if parsing fails
                        error_log("Gemini AI Parsing Failed: " . $e->getMessage());
                    }
                } else {
                    echo "<script>alert('파일 업로드 실패 (권한 문제). 서버의 public/data/excel 폴더 쓰기 권한을 확인해 주세요!'); window.history.back();</script>";
                    return;
                }
            }

            try {
                if ($settings) {
                    $updateStmt = $db->prepare("UPDATE vendor_settings SET company_name = :cn, url_slug = :us, contact_number = :cnm, company_logo = :logo, price_excel_path = :pep, prices_data = :pd, headquarters_address = :hq, fax_number = :fax, manager_name = :mgr, manager_email = :email, factory_address = :faddr, factory_contact = :fcont, bank_account = :bank WHERE user_id = :uid");
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
                        'bank' => $bankAccount,
                        'faddr' => $factoryAddress,
                        'fcont' => $factoryContact,
                        'uid' => $userId
                    ]);
                } else {
                    $insertStmt = $db->prepare("INSERT INTO vendor_settings (user_id, company_name, url_slug, contact_number, company_logo, price_excel_path, prices_data, headquarters_address, fax_number, manager_name, manager_email, factory_address, factory_contact, bank_account) VALUES (:uid, :cn, :us, :cnm, :logo, :pep, :pd, :hq, :fax, :mgr, :email, :faddr, :fcont, :bank)");
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
                        'bank' => $bankAccount,
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

    public function pricing() {
        $this->requireVendorEmployees();
        if (!isset($_SESSION['user']) || empty($_SESSION['user'])) {
            $this->redirect('/login');
            return;
        }
        if (!isset($_SESSION['employee_id'])) {
            $this->redirect('/vendor/profiles');
            return;
        }

        $userId = $_SESSION['user']['user_id'];
        $db = Database::getInstance();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $supplierId = intval($_POST['supplier_id'] ?? 0);
            if ($supplierId <= 0) {
                echo "<script>alert('공급사를 선택해 주세요.'); window.history.back();</script>";
                return;
            }

            if (isset($_FILES['price_excel']) && $_FILES['price_excel']['error'] === UPLOAD_ERR_OK) {
                $uploadDirExcel = __DIR__ . '/../../storage/excel/';
                if (!file_exists($uploadDirExcel)) {
                    @mkdir($uploadDirExcel, 0777, true);
                }
                $excelFilename = 'price_' . $userId . '_' . time() . '.' . pathinfo($_FILES['price_excel']['name'], PATHINFO_EXTENSION);
                $fullExcelPath = $uploadDirExcel . $excelFilename;
                
                if (@move_uploaded_file($_FILES['price_excel']['tmp_name'], $fullExcelPath)) {
                    $priceExcelPath = $excelFilename;
                    
                    try {
                        // AI 파싱이 오래 걸릴 수 있으므로 PHP 실행 시간 무제한(또는 300초)으로 연장
                        set_time_limit(300);
                        
                        // AI-driven Excel Parsing
                        $aiService = \App\Services\AI\AIExtractorFactory::create();
                        $excelText = $aiService->extractTextFromExcel($fullExcelPath);
                        $extractedPricing = $aiService->extractPricingFromJson($excelText);
                        
                        if (isset($extractedPricing['validation']['is_complete']) && $extractedPricing['validation']['is_complete'] !== true) {
                            $missing = implode(", ", $extractedPricing['validation']['missing_fields'] ?? ['Unknown']);
                            throw new \Exception("AI 파싱이 불완전합니다. 누락된 필드: " . $missing);
                        }

                        // 원본 파일명 삽입
                        if (!isset($extractedPricing['meta'])) {
                            $extractedPricing['meta'] = [];
                        }
                        $extractedPricing['meta']['source_file'] = $_FILES['price_excel']['name'];

                        $pricesData = json_encode($extractedPricing, JSON_UNESCAPED_UNICODE);

                        // Save to vendor_pricing_rules
                        $insertRuleStmt = $db->prepare("INSERT INTO vendor_pricing_rules (vendor_id, supplier_id, applied_month, source_file, pricing_data) VALUES (:vid, :sid, :am, :sf, :pd)");
                        $insertRuleStmt->execute([
                            'vid' => $userId,
                            'sid' => $supplierId,
                            'am' => $extractedPricing['meta']['base_month'] ?? date('Y-m'),
                            'sf' => $_FILES['price_excel']['name'],
                            'pd' => $pricesData
                        ]);
                        
                        // Update suppliers table status to excel
                        $updSup = $db->prepare("UPDATE suppliers SET status = 'excel', excel_file = :ef WHERE id = :sid AND vendor_user_id = :vuid");
                        $updSup->execute(['ef' => $_FILES['price_excel']['name'], 'sid' => $supplierId, 'vuid' => $userId]);

                        echo "<script>alert('단가표가 성공적으로 분석 및 적용되었습니다.'); window.location.href='/vendor/pricing';</script>";
                        $stmtCheck = $db->prepare("SELECT id FROM vendor_settings WHERE user_id = :uid");
                        $stmtCheck->execute(['uid' => $userId]);
                        if ($stmtCheck->fetchColumn()) {
                            $updStmt = $db->prepare("UPDATE vendor_settings SET price_excel_path = :pep, prices_data = :pd WHERE user_id = :uid");
                            $updStmt->execute(['pep' => $priceExcelPath, 'pd' => $pricesData, 'uid' => $userId]);
                        } else {
                            $insStmt = $db->prepare("INSERT INTO vendor_settings (user_id, price_excel_path, prices_data) VALUES (:uid, :pep, :pd)");
                            $insStmt->execute(['uid' => $userId, 'pep' => $priceExcelPath, 'pd' => $pricesData]);
                        }

                        echo "<script>alert('단가표가 성공적으로 분석 및 적용되었습니다.'); window.location.href='/vendor/pricing';</script>";
                        return;
                    } catch (\Exception $e) {
                        $errorMsg = addslashes("AI 분석 실패: " . $e->getMessage());
                        echo "<script>alert('{$errorMsg}'); window.history.back();</script>";
                        return;
                    }
                } else {
                    echo "<script>alert('파일 업로드 실패 (권한 문제). 서버의 public/data/excel 폴더 쓰기 권한을 확인해 주세요!'); window.history.back();</script>";
                    return;
                }
            }
        }
        
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $stmtTotal = $db->prepare("SELECT COUNT(*) FROM vendor_pricing_rules WHERE vendor_id = :vuid");
        $stmtTotal->execute(['vuid' => $userId]);
        $totalCount = $stmtTotal->fetchColumn();
        $totalPages = ceil($totalCount / $limit);

        $stmt = $db->prepare("SELECT r.*, s.name as supplier_name FROM vendor_pricing_rules r LEFT JOIN suppliers s ON r.supplier_id = s.id WHERE r.vendor_id = :vuid ORDER BY r.id DESC LIMIT :limit OFFSET :offset");
        $stmt->bindValue(':vuid', $userId, \PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        $rules = $stmt->fetchAll();

        // Fetch suppliers for the vendor
        $stmtSuppliers = $db->prepare("SELECT * FROM suppliers WHERE vendor_user_id = :vuid ORDER BY id ASC");
        $stmtSuppliers->execute(['vuid' => $userId]);
        $suppliers = $stmtSuppliers->fetchAll(\PDO::FETCH_ASSOC);

        // If no suppliers exist, auto-create default '세화 (기본)'
        if (empty($suppliers)) {
            // Check if user already has an existing pricing excel in vendor_settings
            $stmtSet = $db->prepare("SELECT price_excel_path FROM vendor_settings WHERE user_id = :vuid");
            $stmtSet->execute(['vuid' => $userId]);
            $existExcel = $stmtSet->fetchColumn();

            $initStatus = !empty($existExcel) ? 'excel' : 'none';
            $initExcelName = !empty($existExcel) ? basename($existExcel) : null;

            $stmtIns = $db->prepare("INSERT INTO suppliers (vendor_user_id, name, color, status, factory_name, excel_file) VALUES (:vuid, '세화 (기본)', '#fde047', :st, '세화스틸랙 본사/공장', :ef)");
            $stmtIns->execute([
                'vuid' => $userId,
                'st' => $initStatus,
                'ef' => $initExcelName
            ]);
            
            $stmtSuppliers->execute(['vuid' => $userId]);
            $suppliers = $stmtSuppliers->fetchAll(\PDO::FETCH_ASSOC);
        }

        // Fetch manual pricing for each supplier
        $manualPrices = [];
        if ($suppliers) {
            $sIds = array_column($suppliers, 'id');
            $placeholders = str_repeat('?,', count($sIds) - 1) . '?';
            $stmtManual = $db->prepare("SELECT supplier_id, item_code, unit_price FROM vendor_prices_manual WHERE supplier_id IN ($placeholders)");
            $stmtManual->execute($sIds);
            $manualData = $stmtManual->fetchAll(\PDO::FETCH_ASSOC);
            foreach ($manualData as $md) {
                $manualPrices[$md['supplier_id']][$md['item_code']] = $md['unit_price'];
            }
        }

        $this->view('vendor/pricing', [
            'suppliers' => $suppliers,
            'manualPrices' => $manualPrices,
            'rules' => $rules,
            'page' => $page,
            'totalPages' => $totalPages
        ]);
    }

    public function addSupplier() {
        $this->requireVendorEmployees();
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['user']) || empty($_SESSION['user'])) {
            echo json_encode(['success' => false, 'message' => '로그인이 필요합니다.']);
            return;
        }

        $userId = $_SESSION['user']['user_id'];
        $db = Database::getInstance();
        $input = json_decode(file_get_contents('php://input'), true);
        
        $name = trim($input['name'] ?? '');
        if (empty($name)) {
            echo json_encode(['success' => false, 'message' => '공급사 이름을 입력해주세요.']);
            return;
        }

        try {
            $stmt = $db->prepare("INSERT INTO suppliers (vendor_user_id, name, factory_name, status, color) VALUES (?, ?, ?, 'none', '#94a3b8')");
            $stmt->execute([$userId, $name, $name . ' 공장']);
            $newId = $db->lastInsertId();
            echo json_encode(['success' => true, 'id' => $newId]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => '저장 중 오류가 발생했습니다.']);
        }
    }

    public function saveManualPricing() {
        $this->requireVendorEmployees();
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['user']) || empty($_SESSION['user'])) {
            echo json_encode(['success' => false, 'message' => '로그인이 필요합니다.']);
            return;
        }

        $userId = $_SESSION['user']['user_id'];
        $db = Database::getInstance();
        $input = json_decode(file_get_contents('php://input'), true);
        
        $supplierId = intval($input['supplier_id'] ?? 0);
        $prices = $input['prices'] ?? [];
        
        if ($supplierId <= 0 || empty($prices)) {
            echo json_encode(['success' => false, 'message' => '유효하지 않은 요청입니다.']);
            return;
        }

        try {
            // Verify supplier ownership
            $stmt = $db->prepare("SELECT id FROM suppliers WHERE id = ? AND vendor_user_id = ?");
            $stmt->execute([$supplierId, $userId]);
            if (!$stmt->fetch()) {
                echo json_encode(['success' => false, 'message' => '권한이 없습니다.']);
                return;
            }

            $db->beginTransaction();
            $ins = $db->prepare("INSERT INTO vendor_prices_manual (supplier_id, item_code, unit_price) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE unit_price = VALUES(unit_price)");
            foreach ($prices as $code => $price) {
                // remove commas and convert to float/int
                $val = floatval(str_replace(',', '', $price));
                $ins->execute([$supplierId, $code, $val]);
            }
            
            $upd = $db->prepare("UPDATE suppliers SET status = 'manual' WHERE id = ? AND status = 'none'");
            $upd->execute([$supplierId]);
            
            $db->commit();
            echo json_encode(['success' => true, 'message' => '수동 단가가 저장되었습니다.']);
        } catch (\Exception $e) {
            $db->rollBack();
            echo json_encode(['success' => false, 'message' => '저장 중 오류가 발생했습니다.']);
        }
    }

    public function downloadPricingExcel() {
        $this->requireVendorEmployees();
        if (!isset($_SESSION['user']) || empty($_SESSION['user'])) {
            $this->redirect('/login');
            return;
        }
        
        $userId = $_SESSION['user']['user_id'];
        $db = Database::getInstance();
        $stmtSettings = $db->prepare("SELECT price_excel_path FROM vendor_settings WHERE user_id = :uid");
        $stmtSettings->execute(['uid' => $userId]);
        $settings = $stmtSettings->fetch();
        
        if (!$settings || empty($settings['price_excel_path'])) {
            echo "<script>alert('등록된 단가표 파일이 없습니다.'); window.history.back();</script>";
            return;
        }

        $basename = basename($settings['price_excel_path']);
        $filePath = __DIR__ . '/../../storage/excel/' . $basename;

        if (file_exists($filePath)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="pricing_table.xlsx"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($filePath));
            readfile($filePath);
            exit;
        } else {
            echo "<script>alert('파일을 찾을 수 없습니다.'); window.history.back();</script>";
            return;
        }
    }

    public function deletePricingRules() {
        $this->requireVendorEmployees();
        header('Content-Type: application/json');

        if (!isset($_SESSION['user']) || empty($_SESSION['user'])) {
            echo json_encode(['success' => false, 'message' => '로그인이 필요합니다.']);
            return;
        }

        $userId = $_SESSION['user']['user_id'];
        $db = Database::getInstance();

        $rawInput = file_get_contents('php://input');
        $input = json_decode($rawInput, true);
        if (!$input) {
            $input = $_POST;
        }

        $action = $input['action'] ?? '';
        $ids = $input['ids'] ?? [];

        try {
            if ($action === 'all') {
                $stmt = $db->prepare("DELETE FROM vendor_pricing_rules WHERE vendor_id = :vuid");
                $stmt->execute(['vuid' => $userId]);
                
                $upd = $db->prepare("UPDATE vendor_settings SET price_excel_path = NULL WHERE user_id = :vuid");
                $upd->execute(['vuid' => $userId]);

                echo json_encode(['success' => true, 'message' => '모든 업로드 이력이 삭제되었습니다.']);
                return;
            } else if ($action === 'select' && !empty($ids) && is_array($ids)) {
                $placeholders = str_repeat('?,', count($ids) - 1) . '?';
                $sql = "DELETE FROM vendor_pricing_rules WHERE vendor_id = ? AND id IN ($placeholders)";
                
                $params = array_merge([$userId], $ids);
                $stmt = $db->prepare($sql);
                $stmt->execute($params);
                
                $chk = $db->prepare("SELECT COUNT(*) FROM vendor_pricing_rules WHERE vendor_id = :vuid");
                $chk->execute(['vuid' => $userId]);
                if ($chk->fetchColumn() == 0) {
                    $upd = $db->prepare("UPDATE vendor_settings SET price_excel_path = NULL WHERE user_id = :vuid");
                    $upd->execute(['vuid' => $userId]);
                }

                echo json_encode(['success' => true, 'message' => count($ids) . '개의 이력이 삭제되었습니다.']);
                return;
            } else {
                echo json_encode(['success' => false, 'message' => '잘못된 요청이거나 선택된 항목이 없습니다.']);
                return;
            }
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => '삭제 처리 중 오류가 발생했습니다: ' . $e->getMessage()]);
            return;
        }
    }

    public function quotes() {
        $this->requireVendorEmployees();
        if (!isset($_SESSION['user']) || empty($_SESSION['user'])) {
            $this->redirect('/login');
            return;
        }
        if (!isset($_SESSION['employee_id'])) {
            $this->redirect('/vendor/profiles');
            return;
        }

        $userId = $_SESSION['user']['id'];
        $userStrId = $_SESSION['user']['user_id'] ?? '';
        $db = Database::getInstance();

        $stmt = $db->prepare("
            SELECT q.*, e.name as employee_name, e.color_code as employee_color 
            FROM quote_requests q 
            LEFT JOIN vendor_employees e ON q.processed_by = e.id 
            WHERE q.vendor_user_id = :vuid1 OR q.vendor_user_id = :vuid2
            ORDER BY q.created_at DESC
        ");
        $stmt->execute(['vuid1' => $userId, 'vuid2' => $userStrId]);
        $quotes = $stmt->fetchAll() ?: [];

        // 게시판 신청건과 일반(도면/캔버스) 견적건 분류
        $inquiries = array_values(array_filter($quotes, function($q) { 
            return ($q['source_mode'] ?? '') === 'board'; 
        }));

        // DB의 vendor_inquiries 테이블 내역도 통합
        try {
            $stmtInq = $db->prepare("SELECT * FROM vendor_inquiries WHERE vendor_user_id = :vuid1 OR vendor_user_id = :vuid2 ORDER BY created_at DESC");
            $stmtInq->execute(['vuid1' => $userId, 'vuid2' => $userStrId]);
            $extraInquiries = $stmtInq->fetchAll() ?: [];
            if (!empty($extraInquiries)) {
                $inquiries = array_merge($inquiries, $extraInquiries);
            }
        } catch (\Throwable $e) {}

        // 일반 도면 견적건 분류
        $regularQuotes = array_filter($quotes, function($q) { 
            return ($q['source_mode'] ?? '') !== 'board'; 
        });

        $completed_quotes = array_values(array_filter($regularQuotes, function($q) { return !empty($q['processed_by']); }));
        $pending_quotes = array_values(array_filter($regularQuotes, function($q) { return empty($q['processed_by']); }));

        // Sort completed quotes by mailed_at / processed_at DESC
        usort($completed_quotes, function($a, $b) {
            $timeA = strtotime($a['mailed_at'] ?? $a['processed_at'] ?? $a['created_at']);
            $timeB = strtotime($b['mailed_at'] ?? $b['processed_at'] ?? $b['created_at']);
            return $timeB <=> $timeA;
        });

        $pending_inquiries_count = 0;
        foreach ($inquiries as $inq) {
            if (empty($inq['processed_by']) && ($inq['status'] ?? 'pending') === 'pending') {
                $pending_inquiries_count++;
            }
        }

        $this->view('vendor/quotes', [
            'quotes' => $regularQuotes,
            'completed_quotes' => $completed_quotes,
            'pending_quotes' => $pending_quotes,
            'inquiries' => $inquiries,
            'pending_inquiries_count' => $pending_inquiries_count,
            'page' => 1,
            'totalPages' => 1
        ]);
    }

    public function quoteDetail($vars) {
        $this->requireVendorEmployees();
        if (!isset($_SESSION['user']) || empty($_SESSION['user'])) {
            $this->redirect('/login');
            return;
        }
        if (!isset($_SESSION['employee_id'])) {
            $this->redirect('/vendor/profiles');
            return;
        }

        $userId = $_SESSION['user']['id'];
        $userStrId = $_SESSION['user']['user_id'] ?? '';
        $quoteId = intval($vars['id'] ?? 0);
        $db = Database::getInstance();

        $stmt = $db->prepare("
            SELECT q.*, e.name as employee_name, e.color_code as employee_color, e.phone as employee_phone, e.title as employee_title
            FROM quote_requests q 
            LEFT JOIN vendor_employees e ON q.processed_by = e.id 
            WHERE q.id = :qid AND (q.vendor_user_id = :vuid1 OR q.vendor_user_id = :vuid2)
        ");
        $stmt->execute(['qid' => $quoteId, 'vuid1' => $userId, 'vuid2' => $userStrId]);
        $quote = $stmt->fetch();

        if (!$quote) {
            echo "<script>alert('존재하지 않거나 접근 권한이 없는 견적 요청입니다.'); window.location.href='/vendor/quotes';</script>";
            return;
        }

        $balanceInfo = $this->getQuoteBalance($userId);
        
        $stmtSettings = $db->prepare("SELECT url_slug FROM vendor_settings WHERE user_id = :uid");
        $stmtSettings->execute(['uid' => $userStrId]);
        $vendor = $stmtSettings->fetch(\PDO::FETCH_ASSOC);

        $this->view('vendor/quote_detail', ['quote' => $quote, 'balanceInfo' => $balanceInfo, 'vendor' => $vendor]);
    }

    public function quotePrice($vars) {
        $this->requireVendorEmployees();
        if (!isset($_SESSION['user']) || empty($_SESSION['user'])) {
            $this->redirect('/login');
            return;
        }
        if (!isset($_SESSION['employee_id'])) {
            $this->redirect('/vendor/profiles');
            return;
        }

        $userId = $_SESSION['user']['id'];
        $userStrId = $_SESSION['user']['user_id'] ?? '';
        $quoteId = intval($vars['id'] ?? 0);
        $db = Database::getInstance();

        $stmt = $db->prepare("
            SELECT q.*, e.name as employee_name, e.color_code as employee_color, e.phone as employee_phone, e.title as employee_title
            FROM quote_requests q 
            LEFT JOIN vendor_employees e ON q.processed_by = e.id 
            WHERE q.id = :qid AND (q.vendor_user_id = :vuid1 OR q.vendor_user_id = :vuid2)
        ");
        $stmt->execute(['qid' => $quoteId, 'vuid1' => $userId, 'vuid2' => $userStrId]);
        $quote = $stmt->fetch();

        if (!$quote) {
            echo "<script>alert('존재하지 않거나 접근 권한이 없는 견적 요청입니다.'); window.location.href='/vendor/quotes';</script>";
            return;
        }

        $stmt = $db->prepare("SELECT * FROM vendor_settings WHERE user_id = :uid");
        $stmt->execute(['uid' => $userStrId]);
        $settings = $stmt->fetch() ?: [];

        $result = $this->buildQuoteModules($quote);
        $modules = $result['modules'];
        $customItems = $result['custom_items'] ?? [];
        $overallTotal = $result['overallTotal'];
        $isCustomized = $result['is_customized'] ?? false;

        $balanceInfo = $this->getQuoteBalance($userId);

        $this->view('vendor/quote_price', [
            'quote' => $quote, 
            'settings' => $settings, 
            'modules' => $modules,
            'customItems' => $customItems,
            'balanceInfo' => $balanceInfo,
            'overallTotal' => $overallTotal,
            'isCustomized' => $isCustomized
        ]);
    }

    public function saveQuoteDetails($vars) {
        $this->requireVendorEmployees();
        header('Content-Type: application/json');

        if (!isset($_SESSION['user']) || empty($_SESSION['user'])) {
            echo json_encode(['success' => false, 'message' => '로그인이 필요합니다.']);
            return;
        }

        $userId = $_SESSION['user']['id'];
        $userStrId = $_SESSION['user']['user_id'] ?? '';
        $quoteId = intval($vars['id'] ?? 0);
        $db = Database::getInstance();

        $stmt = $db->prepare("SELECT id, is_mailed, processed_by, pricing_rule_id, source_mode FROM quote_requests WHERE id = :qid AND (vendor_user_id = :vuid1 OR vendor_user_id = :vuid2)");
        $stmt->execute(['qid' => $quoteId, 'vuid1' => $userId, 'vuid2' => $userStrId]);
        $row = $stmt->fetch();
        if (!$row) {
            echo json_encode(['success' => false, 'message' => '해당 견적을 찾을 수 없거나 권한이 없습니다.']);
            return;
        }

        if (!empty($row['is_mailed']) || !empty($row['processed_by'])) {
            echo json_encode(['success' => false, 'message' => '이미 고객에게 메일 발송이 완료된 견적서는 무결성 보호를 위해 단가를 수정할 수 없습니다.']);
            return;
        }

        $rawInput = file_get_contents('php://input');
        $input = json_decode($rawInput, true);
        if (!$input) {
            $input = $_POST;
        }

        $modules = $input['modules'] ?? [];
        $customItems = $input['custom_items'] ?? [];
        $hasPricingRule = !empty($row['pricing_rule_id']);

        // 🛡️ [보안 강화] 서버 측 가격/마진/BOM 무결성 재계산 및 위변조 방어
        $calcFinalAmount = function($rawAmount) use ($hasPricingRule) {
            if (!$hasPricingRule) {
                return max(0, intval($rawAmount));
            }
            return max(0, intval(round(($rawAmount * 1.1) / 100) * 100));
        };

        $verifiedOverallTotal = 0;

        // 1. 모듈별 BOM 데이터 검증 및 서버 사이드 총액 재계산
        if (is_array($modules)) {
            foreach ($modules as &$mod) {
                $modQty = max(0, intval($mod['rack_count'] ?? 1));
                $bomItems = $mod['bom'] ?? [];
                $bomRawSum = 0;

                if (is_array($bomItems)) {
                    foreach ($bomItems as &$bItem) {
                        $isLoss = !empty($bItem['is_loss']);
                        $bQty = max(0, floatval($bItem['quantity'] ?? 0));
                        $bUnit = max(0, intval($bItem['unit_amount'] ?? 0));

                        if ($isLoss) {
                            $itemTotal = max(0, intval($bItem['total'] ?? 0));
                            $bomRawSum += $itemTotal;
                        } else {
                            $itemTotal = intval(round($bQty * $bUnit));
                            $bItem['total'] = $itemTotal;
                            $bomRawSum += $itemTotal;
                        }
                    }
                    unset($bItem);
                }

                // 마진 규칙 적용 여부에 따른 모듈 단가 산출
                $verifiedModUnitPrice = $calcFinalAmount($bomRawSum);
                $mod['unit_price'] = $verifiedModUnitPrice;
                $modSubtotal = $modQty * $verifiedModUnitPrice;
                $mod['subtotal'] = $modSubtotal;

                $verifiedOverallTotal += $modSubtotal;
            }
            unset($mod);
        }

        // 2. 추가/부자재 품목 검증
        if (is_array($customItems)) {
            foreach ($customItems as &$cItem) {
                $cQty = max(0, floatval($cItem['qty'] ?? 0));
                $cUnit = max(0, intval($cItem['unit_price'] ?? 0));
                $cTotal = intval(round($cQty * $cUnit));
                $cItem['total'] = $cTotal;
                $verifiedOverallTotal += $cTotal;
            }
            unset($cItem);
        }

        $detailsData = [
            'modules' => $modules,
            'custom_items' => $customItems,
            'overallTotal' => $verifiedOverallTotal, // 클라이언트가 변조한 값 대신 서버 검증 합계로 안전 저장
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => $_SESSION['employee_name'] ?? $_SESSION['user']['username'] ?? 'User'
        ];

        $jsonStr = json_encode($detailsData, JSON_UNESCAPED_UNICODE);

        $upd = $db->prepare("UPDATE quote_requests SET admin_quote_details = :details WHERE id = :qid");
        $upd->execute(['details' => $jsonStr, 'qid' => $quoteId]);

        echo json_encode([
            'success' => true, 
            'message' => '단가 및 부품 변경 사항이 서버 무결성 검증을 거쳐 안전하게 저장되었습니다!',
            'verifiedOverallTotal' => $verifiedOverallTotal
        ]);
    }

    public function resetQuoteDetails($vars) {
        $this->requireVendorEmployees();
        header('Content-Type: application/json');

        if (!isset($_SESSION['user']) || empty($_SESSION['user'])) {
            echo json_encode(['success' => false, 'message' => '로그인이 필요합니다.']);
            return;
        }

        $userId = $_SESSION['user']['id'];
        $userStrId = $_SESSION['user']['user_id'] ?? '';
        $quoteId = intval($vars['id'] ?? 0);
        $db = Database::getInstance();

        $stmt = $db->prepare("SELECT id, is_mailed, processed_by FROM quote_requests WHERE id = :qid AND (vendor_user_id = :vuid1 OR vendor_user_id = :vuid2)");
        $stmt->execute(['qid' => $quoteId, 'vuid1' => $userId, 'vuid2' => $userStrId]);
        $row = $stmt->fetch();
        if (!$row) {
            echo json_encode(['success' => false, 'message' => '해당 견적을 찾을 수 없거나 권한이 없습니다.']);
            return;
        }

        if (!empty($row['is_mailed']) || !empty($row['processed_by'])) {
            echo json_encode(['success' => false, 'message' => '이미 발송 완료된 견적서는 초기화할 수 없습니다.']);
            return;
        }

        $upd = $db->prepare("UPDATE quote_requests SET admin_quote_details = NULL WHERE id = :qid AND (vendor_user_id = :vuid1 OR vendor_user_id = :vuid2)");
        $upd->execute(['qid' => $quoteId, 'vuid1' => $userId, 'vuid2' => $userStrId]);

        echo json_encode(['success' => true, 'message' => '기본 도면 산출 값으로 초기화되었습니다!']);
    }

    private function buildQuoteModules($quote) {
        // 🌟 이미 관리자가 수동 수정한 상세 내역이 DB에 저장되어 있다면 이를 우선 사용
        if (!empty($quote['admin_quote_details'])) {
            $savedDetails = json_decode($quote['admin_quote_details'], true);
            if (is_array($savedDetails) && isset($savedDetails['modules'])) {
                return [
                    'modules' => $savedDetails['modules'],
                    'custom_items' => $savedDetails['custom_items'] ?? [],
                    'overallTotal' => $savedDetails['overallTotal'] ?? 0,
                    'is_customized' => true
                ];
            }
        }

        // BOM 계산 로직 (동적 산출)
        require_once __DIR__ . '/../Services/SehwaPriceCalculator.php';
        
        // 견적 당시의 단가표(Rule)를 주입하여 과거 단가 고정
        $vendorId = $quote['vendor_user_id'] ?? 1;
        $ruleId = $quote['pricing_rule_id'] ?? null;
        \App\Services\SehwaPriceCalculator::setRuleContext($vendorId, $ruleId);

        $indep = intval($quote['rack_indep']);
        $conn = intval($quote['rack_conn']);
        $small = intval($quote['rack_small_conn']);
        
        // 단수 (levels) 계산: 엑셀에서 단수는 '로드빔 단수' (예: 2S 3단이면 로드빔 단수는 2단)
        // DB의 rack_levels가 3이면 실제 로드빔은 2단이 됨.
        $levels = intval($quote['rack_levels']) ?: 2;
        $beamLevels = max(1, $levels - 1); 
        
        // 빔 두께와 바(Bar) 타입 
        $barType = intval($quote['beam_thickness'] ?? 125); 
        $beamThick = 1.6; // 일반 파렛트랙 기본 1.6t
        
        $w = intval($quote['pallet_w']);
        $d = intval($quote['pallet_d']);
        if($w == 0) $w = 1100;
        if($d == 0) $d = 1100;

        $rackH = intval(preg_replace('/[^0-9]/', '', $quote['rack_height'] ?? ''));
        if ($rackH <= 0) $rackH = ($quote['pallet_h'] + 150) * $levels;
        
        $entryW = (strpos($quote['fork_direction'], 'W') !== false) ? $w : $d;
        $nonEntryW = (strpos($quote['fork_direction'], 'W') !== false) ? $d : $w;
        $beamL = ($entryW * 2) + 385;
        $depth = $nonEntryW - 100;

        // 모듈별 단위 BOM 계산 클로저
        if (empty($ruleId)) {
            // 엑셀 단가표가 없는 경우: 수동 완제품 단가(Fallback) 사용 (영업소/대리점 B2B용)
            $db = \App\Core\Database::getInstance();
            $stmt = $db->prepare("
                SELECT v.item_code, v.unit_price 
                FROM vendor_prices_manual v
                JOIN suppliers s ON v.supplier_id = s.id
                WHERE s.vendor_user_id = ? AND s.status = 'manual'
                ORDER BY s.id ASC LIMIT 100
            ");
            $stmt->execute([$vendorId]);
            $manualPrices = [];
            foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row) {
                $manualPrices[$row['item_code']] = floatval($row['unit_price']);
            }

            $buildUnitBom = function($frames, $beamLevels, $tiePerLevel, $rackH, $depth, $beamL, $barType, $beamThick) use ($manualPrices) {
                $bom = [];
                $totalPrice = 0;
                
                // 1. 기둥 단가 (높이에 가장 가까운 단가 매핑)
                $colPrice = 0;
                if ($rackH <= 2250) $colPrice = $manualPrices['column2000'] ?? 0;
                else if ($rackH <= 2750) $colPrice = $manualPrices['column2500'] ?? 0;
                else $colPrice = $manualPrices['column3000'] ?? 0;
                
                if ($frames > 0) {
                    $bom[] = [
                        'name' => '기둥 (프레임)',
                        'spec' => "H-{$rackH}",
                        'qty' => $frames,
                        'unit_amount' => $colPrice,
                        'total' => $colPrice * $frames
                    ];
                    $totalPrice += $colPrice * $frames;
                }

                // 2. 빔 단가 (두께에 따라 매핑)
                $beamPrice = 0;
                if ($beamThick <= 1.2) $beamPrice = $manualPrices['beam1t'] ?? 0;
                else if ($beamThick <= 1.7) $beamPrice = $manualPrices['beam1_5t'] ?? 0;
                else $beamPrice = $manualPrices['beam2t'] ?? 0;
                
                if ($beamLevels > 0) {
                    $bom[] = [
                        'name' => '로드빔 (단당)',
                        'spec' => "{$beamThick}T",
                        'qty' => $beamLevels,
                        'unit_amount' => $beamPrice,
                        'total' => $beamPrice * $beamLevels
                    ];
                    $totalPrice += $beamPrice * $beamLevels;
                }

                // 3. 부자재
                $tieCount = $beamLevels * $tiePerLevel;
                $tiePrice = $manualPrices['tiebar'] ?? 0;
                if ($tieCount > 0 && $tiePrice > 0) {
                    $bom[] = ['name' => '타이바', 'spec' => '-', 'qty' => $tieCount, 'unit_amount' => $tiePrice, 'total' => $tiePrice * $tieCount];
                    $totalPrice += $tiePrice * $tieCount;
                }

                $miscPrice = ($manualPrices['boltSet'] ?? 0) + ($manualPrices['liner'] ?? 0);
                if ($miscPrice > 0) {
                    $bom[] = ['name' => '부자재 세트 (볼트/라이너 등)', 'spec' => '-', 'qty' => 1, 'unit_amount' => $miscPrice, 'total' => $miscPrice];
                    $totalPrice += $miscPrice;
                }

                return [
                    'bom' => $bom,
                    'weight' => 0,
                    'raw_price' => $totalPrice,
                    'final_price' => $totalPrice // 마진 포함 완제품 단가이므로 raw_price = final_price
                ];
            };
        } else {
            // 공장 직거래 대형 업체를 위한 정밀 부품 단위 BOM
            $buildUnitBom = function($frames, $beamLevels, $tiePerLevel, $rackH, $depth, $beamL, $barType, $beamThick) {
                $bom = [];
                $totalWeight = 0;
                $columns = $frames * 2;
                $beams = $beamLevels * 2;
                $tieBeams = ($beams > 0) ? ($beams / 2) * $tiePerLevel : 0;
    
                if ($columns > 0) {
                    $col = \App\Services\SehwaPriceCalculator::calcColumn(['type' => '85바', 'height' => $rackH, 'thickness' => 1.8, 'qty' => $columns]);
                    $bom[] = $col;
                $totalWeight += $col['weight'] * $columns;

                $base = \App\Services\SehwaPriceCalculator::calcColumnBase(['w' => 173, 'd' => 101, 'thickness' => 4, 'qty' => $columns]);
                $bom[] = $base;
                $totalWeight += $base['weight'] * $columns;

                $tie = \App\Services\SehwaPriceCalculator::calcTieBeam(['size' => '75*30', 'length' => $depth + 42, 'thickness' => 1.5, 'material' => '아연도', 'depth' => $depth, 'qty' => $tieBeams]);
                $bom[] = $tie;
                $totalWeight += $tie['weight'] * $tieBeams;

                $x6 = floor(($rackH - 400) / 750);
                $straightCount = $frames * 2;
                $diagonalCount = $frames * $x6;

                $strB = \App\Services\SehwaPriceCalculator::calcBracing(['type' => '직선', 'length' => $depth - 75, 'thickness' => 1.5, 'qty' => $straightCount]);
                $bom[] = $strB;
                $totalWeight += $strB['weight'] * $straightCount;

                $diaLength = sqrt(pow($depth - 125, 2) + pow(750, 2)) + 50;
                $diaB = \App\Services\SehwaPriceCalculator::calcBracing(['type' => '경사', 'length' => $diaLength, 'thickness' => 1.5, 'qty' => $diagonalCount]);
                $bom[] = $diaB;
                $totalWeight += $diaB['weight'] * $diagonalCount;

                $bom[] = \App\Services\SehwaPriceCalculator::getFixedPart('FIX-001', $columns);
                $bom[] = \App\Services\SehwaPriceCalculator::getFixedPart('FIX-002', $columns * 2);
                $bom[] = \App\Services\SehwaPriceCalculator::getFixedPart('FIX-004', $columns * 2);
                
                $bolt65Qty = floor(($columns / 2) * ($x6 + 3));
                $bom[] = \App\Services\SehwaPriceCalculator::getFixedPart('FIX-005', $bolt65Qty);
                $bom[] = \App\Services\SehwaPriceCalculator::getFixedPart('FIX-006', $columns);
            }

            if ($beams > 0) {
                $beam = \App\Services\SehwaPriceCalculator::calcLoadBeam(['length' => $beamL, 'bar_type' => $barType, 'thickness' => $beamThick, 'qty' => $beams]);
                $bom[] = $beam;
                $totalWeight += $beam['weight'] * $beams;

                $bkt = \App\Services\SehwaPriceCalculator::calcLoadBeamBracket(['w' => 101, 'd' => 200, 'thickness' => 4.0, 'qty' => $beams * 2]);
                $bom[] = $bkt;
                $totalWeight += $bkt['weight'] * ($beams * 2);

                $bom[] = \App\Services\SehwaPriceCalculator::getFixedPart('FIX-003', $beams * 2);
            }

            $lossTotal = \App\Services\SehwaPriceCalculator::calcLoss($totalWeight);
            if ($lossTotal > 0) {
                $bom[] = ['name' => 'Loss', 'spec' => '철강 Loss 3%', 'qty' => '-', 'unit_amount' => '-', 'total' => $lossTotal];
            }

                $sumRaw = 0;
                foreach ($bom as $item) {
                    $sumRaw += is_numeric($item['total'] ?? null) ? $item['total'] : 0;
                }
                $finalPrice = \App\Services\SehwaPriceCalculator::finalAmount($sumRaw);
    
                return [
                    'bom' => $bom,
                    'weight' => $totalWeight,
                    'raw_price' => $sumRaw,
                    'final_price' => $finalPrice
                ];
            };
        }

        $tiePerLevel = intval($quote['rack_tie_per_level'] ?? 4);
        $modules = [];
        $overallTotal = 0;

        if ($indep > 0) {
            $unit = $buildUnitBom(2, $beamLevels, $tiePerLevel, $rackH, $depth, $beamL, $barType, $beamThick);
            $sName = ($beamL >= 2585 ? "2S" : "1S") . " {$levels}단 독립";
            $modules[] = [
                'type' => '독립',
                'name' => '파렛트랙',
                'spec' => "{$beamL}*{$depth}*{$rackH}",
                'remark' => $sName,
                'qty' => $indep,
                'unit_price' => $unit['final_price'],
                'raw_price' => $unit['raw_price'],
                'total_price' => $unit['final_price'] * $indep,
                'bom' => $unit['bom']
            ];
            $overallTotal += $unit['final_price'] * $indep;
        }

        if ($conn > 0) {
            $unit = $buildUnitBom(1, $beamLevels, $tiePerLevel, $rackH, $depth, $beamL, $barType, $beamThick);
            $sName = ($beamL >= 2585 ? "2S" : "1S") . " {$levels}단 연결";
            $modules[] = [
                'type' => '연결',
                'name' => '파렛트랙',
                'spec' => "{$beamL}*{$depth}*{$rackH}",
                'remark' => $sName,
                'qty' => $conn,
                'unit_price' => $unit['final_price'],
                'raw_price' => $unit['raw_price'],
                'total_price' => $unit['final_price'] * $conn,
                'bom' => $unit['bom']
            ];
            $overallTotal += $unit['final_price'] * $conn;
        }

        if ($small > 0) {
            $unit = $buildUnitBom(1, $beamLevels, 2, $rackH, $depth, 1385, $barType, $beamThick);
            $sNameSmall = ($beamL >= 2585 ? "2S" : "1S") . " {$levels}단 작은연결";
            $modules[] = [
                'type' => '작은연결',
                'name' => '파렛트랙',
                'spec' => "1385*{$depth}*{$rackH}",
                'remark' => $sNameSmall,
                'qty' => $small,
                'unit_price' => $unit['final_price'],
                'raw_price' => $unit['raw_price'],
                'total_price' => $unit['final_price'] * $small,
                'bom' => $unit['bom']
            ];
            $overallTotal += $unit['final_price'] * $small;
        }

        // 바이패스(Bypass)
        $bypass = intval($quote['rack_bypass'] ?? 0);
        if ($bypass > 0) {
            $bpLevels = max(1, $levels - 1);
            $bpBeamLevels = max(1, $beamLevels - 1);
            
            // 바이패스: 프레임 1개, 로드빔 쌍은 일반보다 1단 적음
            $unit = $buildUnitBom(1, $bpBeamLevels, $tiePerLevel, $rackH, $depth, $beamL, $barType, $beamThick);
            
            $bpType = $quote['rack_bypass_type'] ?? "1S {$bpLevels}단 연결";
            
            $modules[] = [
                'type' => '바이패스',
                'name' => '파렛트랙',
                'spec' => "{$beamL}*{$depth}*{$rackH}",
                'remark' => $bpType . " (바이패스)",
                'qty' => $bypass,
                'unit_price' => $unit['final_price'],
                'raw_price' => $unit['raw_price'],
                'total_price' => $unit['final_price'] * $bypass,
                'bom' => $unit['bom']
            ];
            $overallTotal += $unit['final_price'] * $bypass;
        }

        // 🌟 커스텀 단수 / 기둥 높이 변경 랙 (canvas_data 파싱)
        $canvasData = json_decode($quote['canvas_data'] ?? '', true);
        $racks = $canvasData['racks'] ?? [];
        $customLevelCounts = [];

        if (!empty($racks) && is_array($racks)) {
            foreach ($racks as $r) {
                $reg = intval($r['independent'] ?? 0) + intval($r['connected'] ?? 0);
                $sm = intval($r['smallConnected'] ?? 0);
                $rowCount = !empty($r['isDouble']) ? 2 : 1;
                $defaultRackLevel = intval($r['levels'] ?? 0) ?: $levels;
                $rBeamL = intval($r['beamLength'] ?? 0) ?: $beamL;
                $rSmallBeamL = intval($r['smallBeamLength'] ?? 0) ?: 1385;
                $rDepth = intval($r['rackDepth'] ?? 0) ?: $depth;

                $bypassBays = $r['bypassBays'] ?? [];
                $bayLevels = $r['bayLevels'] ?? [];
                $bayHeights = $r['bayHeights'] ?? [];

                for ($row = 0; $row < $rowCount; $row++) {
                    for ($j = 0; $j < $reg; $j++) {
                        $isBypass = !empty($bypassBays[$row][$j]);
                        if ($isBypass) continue;

                        $bayLvl = (isset($bayLevels[$row][$j]) && $bayLevels[$row][$j] !== null && intval($bayLevels[$row][$j]) > 0) ? intval($bayLevels[$row][$j]) : $defaultRackLevel;
                        $bayH = (isset($bayHeights[$row][$j]) && !empty($bayHeights[$row][$j]) && intval($bayHeights[$row][$j]) > 0) ? intval($bayHeights[$row][$j]) : $rackH;

                        if ($bayLvl !== $levels || $bayH !== $rackH) {
                            $key = "{$bayLvl}_{$bayH}_{$rBeamL}_{$rDepth}";
                            if (!isset($customLevelCounts[$key])) {
                                $customLevelCounts[$key] = ['level' => $bayLvl, 'height' => $bayH, 'beamLength' => $rBeamL, 'depth' => $rDepth, 'indep' => 0, 'conn' => 0, 'small' => 0];
                            }
                            $bayType = ($j < intval($r['independent'] ?? 0)) ? 'indep' : 'conn';
                            $customLevelCounts[$key][$bayType]++;
                        }
                    }

                    for ($j = $reg; $j < $reg + $sm; $j++) {
                        $isBypass = !empty($bypassBays[$row][$j]);
                        if ($isBypass) continue;

                        $bayLvl = (isset($bayLevels[$row][$j]) && $bayLevels[$row][$j] !== null && intval($bayLevels[$row][$j]) > 0) ? intval($bayLevels[$row][$j]) : $defaultRackLevel;
                        $bayH = (isset($bayHeights[$row][$j]) && !empty($bayHeights[$row][$j]) && intval($bayHeights[$row][$j]) > 0) ? intval($bayHeights[$row][$j]) : $rackH;

                        if ($bayLvl !== $levels || $bayH !== $rackH) {
                            $key = "{$bayLvl}_{$bayH}_{$rSmallBeamL}_{$rDepth}_sm";
                            if (!isset($customLevelCounts[$key])) {
                                $customLevelCounts[$key] = ['level' => $bayLvl, 'height' => $bayH, 'beamLength' => $rSmallBeamL, 'depth' => $rDepth, 'indep' => 0, 'conn' => 0, 'small' => 0, 'isSmall' => true];
                            }
                            $customLevelCounts[$key]['small']++;
                        }
                    }
                }
            }
        }

        foreach ($customLevelCounts as $cItem) {
            $cLvl = $cItem['level'];
            $cH = $cItem['height'];
            $cBeamL = $cItem['beamLength'];
            $cDepth = $cItem['depth'];
            $cBeamLevels = max(1, $cLvl - 1);
            $cSpanS = max(1, $cLvl - 1);

            if ($cItem['indep'] > 0) {
                $unit = $buildUnitBom(2, $cBeamLevels, $tiePerLevel, $cH, $cDepth, $cBeamL, $barType, $beamThick);
                $sName = "{$cSpanS}S {$cLvl}단 독립";
                $modules[] = [
                    'type' => '독립',
                    'name' => '파렛트랙',
                    'spec' => "{$cBeamL}*{$cDepth}*{$cH}",
                    'remark' => $sName,
                    'qty' => $cItem['indep'],
                    'unit_price' => $unit['final_price'],
                    'raw_price' => $unit['raw_price'],
                    'total_price' => $unit['final_price'] * $cItem['indep'],
                    'bom' => $unit['bom']
                ];
                $overallTotal += $unit['final_price'] * $cItem['indep'];
            }

            if ($cItem['conn'] > 0) {
                $unit = $buildUnitBom(1, $cBeamLevels, $tiePerLevel, $cH, $cDepth, $cBeamL, $barType, $beamThick);
                $sName = "{$cSpanS}S {$cLvl}단 연결";
                $modules[] = [
                    'type' => '연결',
                    'name' => '파렛트랙',
                    'spec' => "{$cBeamL}*{$cDepth}*{$cH}",
                    'remark' => $sName,
                    'qty' => $cItem['conn'],
                    'unit_price' => $unit['final_price'],
                    'raw_price' => $unit['raw_price'],
                    'total_price' => $unit['final_price'] * $cItem['conn'],
                    'bom' => $unit['bom']
                ];
                $overallTotal += $unit['final_price'] * $cItem['conn'];
            }

            if ($cItem['small'] > 0) {
                $unit = $buildUnitBom(1, $cBeamLevels, 2, $cH, $cDepth, $cBeamL, $barType, $beamThick);
                $sNameSmall = "{$cSpanS}S {$cLvl}단 작은연결";
                $modules[] = [
                    'type' => '작은연결',
                    'name' => '파렛트랙',
                    'spec' => "{$cBeamL}*{$cDepth}*{$cH}",
                    'remark' => $sNameSmall,
                    'qty' => $cItem['small'],
                    'unit_price' => $unit['final_price'],
                    'raw_price' => $unit['raw_price'],
                    'total_price' => $unit['final_price'] * $cItem['small'],
                    'bom' => $unit['bom']
                ];
                $overallTotal += $unit['final_price'] * $cItem['small'];
            }
        }

        // 복렬 홀더(Holder)
        $holders = intval($quote['rack_holders'] ?? 0);
        if ($holders > 0) {
            if (empty($ruleId)) {
                $hFinal = 0;
                $sumRaw = 0;
                $hBom = [
                    ['name' => '복렬 홀더 세트', 'spec' => '200L (복식/상하체결)', 'qty' => 1, 'unit_amount' => 0, 'total' => 0]
                ];
            } else {
                $hBom = [];
                $hWeight = 0;
                
                $h = \App\Services\SehwaPriceCalculator::calcHolder(['length' => 200, 'thickness' => 2.0, 'qty' => 1]);
                $hBom[] = $h;
                $hWeight += $h['weight'];

                $hBom[] = \App\Services\SehwaPriceCalculator::getFixedPart('FIX-004', 4);
                
                $lossTotal = \App\Services\SehwaPriceCalculator::calcLoss($hWeight);
                if ($lossTotal > 0) {
                    $hBom[] = ['name' => 'Loss', 'spec' => '철강 Loss 3%', 'qty' => '-', 'unit_amount' => '-', 'total' => $lossTotal];
                }
                
                $sumRaw = 0;
                foreach ($hBom as $item) { $sumRaw += is_numeric($item['total'] ?? null) ? $item['total'] : 0; }
                $hFinal = \App\Services\SehwaPriceCalculator::finalAmount($sumRaw);
            }

            $modules[] = [
                'type' => '홀더',
                'name' => '복렬 홀더',
                'spec' => '200L',
                'remark' => '복식/상하체결',
                'qty' => $holders,
                'unit_price' => $hFinal,
                'raw_price' => $sumRaw,
                'total_price' => $hFinal * $holders,
                'bom' => $hBom
            ];
            $overallTotal += $hFinal * $holders;
        }

        return [
            'modules' => $modules,
            'custom_items' => [],
            'overallTotal' => $overallTotal,
            'is_customized' => false
        ];
    }

    public function quoteDocument($vars) {
        $this->requireVendorEmployees();
        if (!isset($_SESSION['user']) || empty($_SESSION['user'])) {
            $this->redirect('/login');
            return;
        }
        if (!isset($_SESSION['employee_id'])) {
            $this->redirect('/vendor/profiles');
            return;
        }

        $userId = $_SESSION['user']['id'];
        $userIdStr = $_SESSION['user']['user_id'];
        $quoteId = intval($vars['id'] ?? 0);
        $db = Database::getInstance();

        $stmt = $db->prepare("
            SELECT q.*, e.name as employee_name, e.color_code as employee_color, e.phone as employee_phone, e.title as employee_title
            FROM quote_requests q 
            LEFT JOIN vendor_employees e ON q.processed_by = e.id 
            WHERE q.id = :qid AND q.vendor_user_id = :vuid
        ");
        $stmt->execute(['qid' => $quoteId, 'vuid' => $userId]);
        $quote = $stmt->fetch();

        if (!$quote) {
            echo "<script>alert('존재하지 않거나 접근 권한이 없는 견적 요청입니다.'); window.location.href='/vendor/quotes';</script>";
            return;
        }

        $stmt = $db->prepare("SELECT * FROM vendor_settings WHERE user_id = :uid");
        $stmt->execute(['uid' => $userIdStr]);
        $settings = $stmt->fetch() ?: [];

        $result = $this->buildQuoteModules($quote);
        $modules = $result['modules'];
        $customItems = $result['custom_items'] ?? [];
        $isCustomized = $result['is_customized'] ?? false;

        $balanceInfo = $this->getQuoteBalance($userId);
        $this->view('vendor/quote_document', [
            'quote' => $quote, 
            'settings' => $settings, 
            'modules' => $modules, 
            'customItems' => $customItems,
            'balanceInfo' => $balanceInfo,
            'isCustomized' => $isCustomized
        ]);
    }

    public function sendEmail(array $vars) {
        $id = $vars['id'];
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['user']) || empty($_SESSION['user'])) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }

        $userId = $_SESSION['user']['user_id'];
        
        $to = $_POST['to'] ?? '';
        $subject = $_POST['subject'] ?? '';
        $body = $_POST['body'] ?? '';
        
        if (empty($to) || empty($subject)) {
            echo json_encode(['success' => false, 'message' => '필수 항목이 누락되었습니다.']);
            return;
        }
        
        // Check Limit
        $balanceInfo = $this->getQuoteBalance($userId);
        if ($balanceInfo['remaining'] <= 0) {
            echo json_encode(['success' => false, 'message' => '발송 한도를 초과했습니다. 추가 결제가 필요합니다.']);
            return;
        }

        $attachments = [];
        
        // Handle PDF Quote
        if (isset($_FILES['quote_pdf']) && $_FILES['quote_pdf']['error'] === UPLOAD_ERR_OK) {
            $tmpPath = $_FILES['quote_pdf']['tmp_name'];
            $name = '견적서_' . date('Ymd_His') . '.pdf';
            $attachments[] = [$tmpPath, $name];
        } else {
            echo json_encode(['success' => false, 'message' => 'PDF 변환 파일이 수신되지 않았습니다.']);
            return;
        }
        
        // Handle Extra Files
        if (isset($_FILES['extra_files'])) {
            $files = $_FILES['extra_files'];
            for ($i = 0; $i < count($files['name']); $i++) {
                if ($files['error'][$i] === UPLOAD_ERR_OK) {
                    $attachments[] = [$files['tmp_name'][$i], $files['name'][$i]];
                }
            }
        }
        
        // Send Email using existing Mailer
        require_once __DIR__ . '/../../lib/mailer.lib.php';
        $result = \Mailer::send($to, $subject, $body, $attachments, false);
        
        if ($result['success']) {
            $db = \App\Core\Database::getInstance();
            
            // 차감 로직: 기본 한도를 초과하여 발송하는 경우 addon_balance 차감
            if ($balanceInfo['used'] >= $balanceInfo['total_limit'] && $balanceInfo['addon_balance'] > 0) {
                $db->prepare("UPDATE users SET addon_quotes_balance = addon_quotes_balance - 1 WHERE id = ?")->execute([$userId]);
            }
            
            $adminMargin = $_POST['admin_margin'] ?? 0;
            $adminPrice = $_POST['admin_price'] ?? 0;
            
            // 기존에 저장된 커스텀 BOM 상세(modules, custom_items 등)가 있다면 보존하고 견적서 입력값을 병합
            $currDetailsStmt = $db->prepare("SELECT admin_quote_details FROM quote_requests WHERE id = ?");
            $currDetailsStmt->execute([$id]);
            $currDetailsRaw = $currDetailsStmt->fetchColumn();
            
            $finalDetailsJson = $currDetailsRaw;
            if (!empty($_POST['admin_quote_details'])) {
                $postedDocInputs = json_decode($_POST['admin_quote_details'], true);
                if (is_array($postedDocInputs)) {
                    $currDetailsArr = json_decode($currDetailsRaw ?? '', true) ?: [];
                    $currDetailsArr['doc_inputs'] = $postedDocInputs;
                    $finalDetailsJson = json_encode($currDetailsArr, JSON_UNESCAPED_UNICODE);
                }
            }
            
            $employeeId = $_SESSION['employee_id'] ?? null;
            if ($employeeId) {
                $stmt = $db->prepare("UPDATE quote_requests SET processed_by = ?, processed_at = NOW(), is_mailed = 1, mailed_at = NOW(), admin_margin = ?, admin_price = ?, admin_quote_details = ? WHERE id = ? AND vendor_user_id = ?");
                $stmt->execute([$employeeId, $adminMargin, $adminPrice, $finalDetailsJson, $id, $userId]);
            } else {
                $stmt = $db->prepare("UPDATE quote_requests SET is_mailed = 1, mailed_at = NOW(), admin_margin = ?, admin_price = ?, admin_quote_details = ? WHERE id = ? AND vendor_user_id = ?");
                $stmt->execute([$adminMargin, $adminPrice, $finalDetailsJson, $id, $userId]);
            }
            echo json_encode(['success' => true, 'message' => '메일이 성공적으로 발송되었습니다.']);
        } else {
            echo json_encode(['success' => false, 'message' => '발송 실패: ' . $result['message']]);
        }
    }

    /**
     * 현재 업체의 남은 견적 발송 건수 조회
     * @return array [ 'total_limit' => 기본한도, 'addon_balance' => 추가결제 잔여분, 'used' => 현재 주기 사용량, 'remaining' => 총 남은 발송 횟수 ]
     */
    public function getQuoteBalance($vendorUserId) {
        $db = \App\Core\Database::getInstance();
        
        // 1. users 테이블에서 플랜 및 가입일 직접 조회
        $userStmt = $db->prepare("SELECT id, user_id, plan, created_at, addon_quotes_balance FROM users WHERE user_id = ? OR id = ? LIMIT 1");
        $userStmt->execute([$vendorUserId, $vendorUserId]);
        $userData = $userStmt->fetch();

        if (!$userData) {
            return ['total_limit' => 10, 'addon_balance' => 0, 'used' => 0, 'remaining' => 10, 'plan' => 'free'];
        }

        $plan = $userData['plan'] ?? 'free';
        $addonBalance = intval($userData['addon_quotes_balance'] ?? 0);

        // 2. 플랜별 기본 제공 건수
        if ($plan === 'pro') {
            // PRO: 무제한 → addon 충전 불필요
            return [
                'total_limit'   => 99999999,
                'addon_balance' => $addonBalance,
                'used'          => 0,
                'remaining'     => 99999999,
                'plan'          => 'pro'
            ];
        } elseif ($plan === 'starter') {
            $limit = 30;
        } else {
            $limit = 10; // free
        }

        // 3. 현재 주기 기준일 계산 (가입일 기준 매월 동일 일자)
        $created_at = strtotime($userData['created_at']);
        $day = date('d', $created_at);
        $currentMonthDay = strtotime(date("Y-m-{$day} 00:00:00"));
        if ($currentMonthDay > time()) {
            $baseDate = date("Y-m-{$day} 00:00:00", strtotime("-1 month", $currentMonthDay));
        } else {
            $baseDate = date("Y-m-{$day} 00:00:00", $currentMonthDay);
        }

        // 4. 현재 주기 발송 메일 수 카운트
        $userStrId = $userData['user_id'] ?? $userData['id'];
        $countStmt = $db->prepare("SELECT COUNT(*) FROM quote_requests WHERE (vendor_user_id = :v1 OR vendor_user_id = :v2) AND is_mailed = 1 AND mailed_at >= :bdate");
        $countStmt->execute(['v1' => $userData['id'], 'v2' => $userStrId, 'bdate' => $baseDate]);
        $usedCount = intval($countStmt->fetchColumn());

        // 5. 남은 횟수 계산 (기본 남은 건수 + addon 누적)
        $remainingBase = max(0, $limit - $usedCount);
        $totalRemaining = $remainingBase + $addonBalance;

        return [
            'total_limit'   => $limit,
            'addon_balance' => $addonBalance,
            'used'          => $usedCount,
            'remaining'     => $totalRemaining,
            'plan'          => $plan
        ];
    }

    public function addonPayment() {
        if (!isset($_SESSION['user']) || empty($_SESSION['user'])) {
            $this->redirect('/login');
            return;
        }

        // 부트페이 설정 가져오기
        $db = \App\Core\Database::getInstance();
        $configStmt = $db->query("SELECT * FROM config WHERE id = 1");
        $config = $configStmt->fetch();

        $this->view('vendor/addon_payment', [
            'config' => $config,
            'user' => clone (object)$_SESSION['user']
        ]);
    }

    public function embed() {
        if (!isset($_SESSION['user']) || empty($_SESSION['user'])) {
            $this->redirect('/login');
            return;
        }

        $userId = $_SESSION['user']['user_id'];
        $db = Database::getInstance();

        $stmt = $db->prepare("SELECT * FROM vendor_settings WHERE user_id = :uid");
        $stmt->execute(['uid' => $userId]);
        $settings = $stmt->fetch();

        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $domainName = rtrim($protocol . $host, '/');

        $this->view('vendor/embed', [
            'vendorSettings' => $settings,
            'domainName' => $domainName,
            'user' => $_SESSION['user']
        ]);
    }
}

