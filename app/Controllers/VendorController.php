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
                        $aiService = \App\Services\AI\AIExtractorFactory::create();
                        $excelText = $aiService->extractTextFromExcel($fullExcelPath);
                        $extractedPricing = $aiService->extractPricingFromJson($excelText);
                        
                        // 검증 로직 추가 (AI가 일부 값을 찾지 못했거나 실패한 경우 방어)
                        if (isset($extractedPricing['validation']['is_complete']) && $extractedPricing['validation']['is_complete'] !== true) {
                            $missing = implode(", ", $extractedPricing['validation']['missing_fields'] ?? ['Unknown']);
                            throw new \Exception("AI 파싱이 불완전합니다. 누락된 필드: " . $missing);
                        }

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

        // 모듈별 단위 BOM 계산 클로저
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
                $sumRaw += $item['total'];
            }
            $finalPrice = \App\Services\SehwaPriceCalculator::finalAmount($sumRaw);

            return [
                'bom' => $bom,
                'weight' => $totalWeight,
                'raw_price' => $sumRaw,
                'final_price' => $finalPrice
            ];
        };

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
                'total_price' => $unit['final_price'] * $bypass,
                'bom' => $unit['bom']
            ];
            $overallTotal += $unit['final_price'] * $bypass;
        }

        // 복렬 홀더(Holder)
        $holders = intval($quote['rack_holders'] ?? 0);
        if ($holders > 0) {
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
            foreach ($hBom as $item) { $sumRaw += $item['total']; }
            $hFinal = \App\Services\SehwaPriceCalculator::finalAmount($sumRaw);

            $modules[] = [
                'type' => '홀더',
                'name' => '복렬 홀더',
                'spec' => '200L',
                'remark' => '복식/상하체결',
                'qty' => $holders,
                'unit_price' => $hFinal,
                'total_price' => $hFinal * $holders,
                'bom' => $hBom
            ];
            $overallTotal += $hFinal * $holders;
        }

        $this->view('vendor/quote_price', [
            'quote' => $quote, 
            'settings' => $settings, 
            'modules' => $modules,
            'overallTotal' => $overallTotal
        ]);
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
