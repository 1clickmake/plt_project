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
                        // AI-driven Excel Parsing
                        $geminiService = new \App\Services\GeminiService();
                        $excelText = $geminiService->extractTextFromExcel($fullExcelPath);
                        $extractedPricing = $geminiService->extractPricingFromJson($excelText);
                        
                        $pricesData = json_encode($extractedPricing, JSON_UNESCAPED_UNICODE);

                        // Save to vendor_pricing_rules (Versioning)
                        $insertRuleStmt = $db->prepare("INSERT INTO vendor_pricing_rules (vendor_id, applied_month, pricing_data) VALUES (:vid, :am, :pd)");
                        $insertRuleStmt->execute([
                            'vid' => $userId,
                            'am' => $extractedPricing['meta']['base_month'] ?? date('Y-m'),
                            'pd' => $pricesData
                        ]);

                        // Redirect to settings page with success message
                        // For now just continue...
                    } catch (\Exception $e) {
                        // Keep old data if parsing fails
                        error_log("Gemini AI Parsing Failed: " . $e->getMessage());
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

        // BOM 계산 로직 (동적 산출)
        require_once __DIR__ . '/../Services/SehwaPriceCalculator.php';
        
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

        // 수량 계산
        $totalFrames = ($indep * 2) + $conn + $small; // 프레임 단위
        $totalColumns = $totalFrames * 2;
        $totalBeams = ($indep + $conn) * $beamLevels * 2;
        $totalSmallBeams = $small * $beamLevels * 2;
        $tieCountPerFrame = ($rackH >= 3000) ? 4 : 2;
        
        $bom = [];
        $totalWeight = 0;
        
        if ($totalColumns > 0) {
            $col = \App\Services\SehwaPriceCalculator::calcColumn([
                'type' => '85바', 'height' => $rackH, 'thickness' => 1.8, 'qty' => $totalColumns
            ]);
            $bom[] = $col;
            $totalWeight += $col['weight'] * $totalColumns;

            $base = \App\Services\SehwaPriceCalculator::calcColumnBase([
                'w' => 173, 'd' => 101, 'thickness' => 4, 'qty' => $totalColumns
            ]);
            $bom[] = $base;
            $totalWeight += $base['weight'] * $totalColumns;

            // 타이빔(서포트바) 수량 계산: 프레임 기준이 아니라 로드빔 한 쌍당 W길이에 따라 결정 (일반적으로 2585면 단당 4개)
            $tiePerLevel = ($rackW ?? 2585) >= 2785 ? 4 : (($rackW ?? 2585) >= 2585 ? 4 : 2); // 기본 2S면 단당 4개
            // 타이빔은 로드빔 쌍(Level)의 수에 비례. totalBeams / 2 가 로드빔 쌍의 수.
            $totalTieBeams = ($totalBeams > 0) ? ($totalBeams / 2) * $tiePerLevel : 0;
            // 작은 로드빔도 고려
            $totalSmallTieBeams = ($totalSmallBeams > 0) ? ($totalSmallBeams / 2) * 2 : 0; // 1385는 단당 2개
            
            $tie = \App\Services\SehwaPriceCalculator::calcTieBeam([
                'size' => '75*30', 'length' => $depth + 42, 'thickness' => 1.5, 'material' => '아연도', 'depth' => $depth, 'qty' => $totalTieBeams + $totalSmallTieBeams
            ]);
            $bom[] = $tie;
            $totalWeight += $tie['weight'] * ($totalTieBeams + $totalSmallTieBeams);

            // 브레싱 계산
            $straightCount = $totalFrames * 2; // 프레임당 직선 2개
            $diagonalCount = $totalFrames * 6; // 프레임당 경사 대략 6개 (단수에 따라 다름)
            $strB = \App\Services\SehwaPriceCalculator::calcBracing([
                'type' => '직선', 'length' => 925, 'thickness' => 1.5, 'qty' => $straightCount
            ]);
            $bom[] = $strB;
            $totalWeight += $strB['weight'] * $straightCount;

            $diaB = \App\Services\SehwaPriceCalculator::calcBracing([
                'type' => '경사', 'length' => 1202, 'thickness' => 1.5, 'qty' => $diagonalCount
            ]);
            $bom[] = $diaB;
            $totalWeight += $diaB['weight'] * $diagonalCount;

            // 부자재
            $bom[] = \App\Services\SehwaPriceCalculator::getFixedPart('FIX-001', $totalColumns); // 부싱
            $bom[] = \App\Services\SehwaPriceCalculator::getFixedPart('FIX-002', $totalColumns * 2); // 라이너 2ea/col
            $bom[] = \App\Services\SehwaPriceCalculator::getFixedPart('FIX-004', $totalColumns * 2); // 볼트너트 25L
            $bom[] = \App\Services\SehwaPriceCalculator::getFixedPart('FIX-005', $totalColumns * 4.5); // 볼트너트 65L (대략)
            $bom[] = \App\Services\SehwaPriceCalculator::getFixedPart('FIX-006', $totalColumns); // 앙카 (기둥당 1개)
        }
        if ($totalBeams > 0) {
            $beam = \App\Services\SehwaPriceCalculator::calcLoadBeam([
                'length' => $beamL, 'bar_type' => $barType, 'thickness' => $beamThick, 'qty' => $totalBeams
            ]);
            $bom[] = $beam;
            $totalWeight += $beam['weight'] * $totalBeams;

            $bkt = \App\Services\SehwaPriceCalculator::calcLoadBeamBracket([
                'w' => 101, 'd' => 200, 'thickness' => 4.0, 'qty' => $totalBeams * 2
            ]);
            $bom[] = $bkt;
            $totalWeight += $bkt['weight'] * ($totalBeams * 2);

            $bom[] = \App\Services\SehwaPriceCalculator::getFixedPart('FIX-003', $totalBeams * 2); // 안전핀
        }
        if ($totalSmallBeams > 0) {
            $sBeam = \App\Services\SehwaPriceCalculator::calcLoadBeam([
                'length' => 1385, 'bar_type' => $barType, 'thickness' => $beamThick, 'qty' => $totalSmallBeams
            ]);
            $bom[] = $sBeam;
            $totalWeight += $sBeam['weight'] * $totalSmallBeams;

            $sBkt = \App\Services\SehwaPriceCalculator::calcLoadBeamBracket([
                'w' => 101, 'd' => 200, 'thickness' => 4.0, 'qty' => $totalSmallBeams * 2
            ]);
            $bom[] = $sBkt;
            $totalWeight += $sBkt['weight'] * ($totalSmallBeams * 2);

            $bom[] = \App\Services\SehwaPriceCalculator::getFixedPart('FIX-003', $totalSmallBeams * 2); // 안전핀
        }

        // 복렬 홀더(Holder) 추가
        $holders = intval($quote['rack_holders'] ?? 0);
        if ($holders > 0) {
            $h = \App\Services\SehwaPriceCalculator::calcHolder([
                'length' => 200, 'thickness' => 2.0, 'qty' => $holders
            ]);
            $bom[] = $h;
            $totalWeight += $h['weight'] * $holders;

            // 홀더용 볼트너트 (홀더 1개당 4개)
            $bom[] = \App\Services\SehwaPriceCalculator::getFixedPart('FIX-004', $holders * 4);
        }

        // Loss (동적 엔진 연동: DB의 loss_rate 및 base_steel_price 적용)
        $lossTotal = \App\Services\SehwaPriceCalculator::calcLoss($totalWeight);
        if ($lossTotal > 0) {
            $bom[] = [
                'name' => 'Loss',
                'spec' => '-',
                'qty' => '',
                'unit_amount' => '',
                'total' => $lossTotal
            ];
        }

        $this->view('vendor/quote_price', ['quote' => $quote, 'settings' => $settings, 'bom' => $bom]);
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
