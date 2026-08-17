<?php

namespace App\Controllers;

use App\Core\Database;

class CanvasController extends BaseController {

    public function showVendorCanvas($vars) {
        $slug = $vars['slug'] ?? '';
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM vendor_settings WHERE url_slug = :slug");
        $stmt->execute(['slug' => $slug]);
        $vendor = $stmt->fetch();

        if (!$vendor) {
            echo "<script>alert('Invalid Vendor URL!'); window.location.href='/';</script>";
            return;
        }

        $this->view('canvas/index', ['vendor' => $vendor]);
    }

    /**
     * POST /api/canvas/analyze
     * 캔버스 도면 + 파렛트/지게차 제원을 Gemini AI로 분석하여 배치 제안 반환
     */
    public function analyzeLayout() {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $body = json_decode(file_get_contents('php://input'), true);

            $palletW      = intval($body['pallet_w'] ?? 0);
            $palletD      = intval($body['pallet_d'] ?? 0);
            $palletH      = intval($body['pallet_h'] ?? 0);
            $palletWeight = intval($body['pallet_weight'] ?? 0);
            $forkDir      = $body['fork_direction'] ?? 'W';
            $liftHeight   = intval($body['lift_height'] ?? 0);
            $ast          = intval($body['ast'] ?? 0);
            $forkliftType = $body['forklift_type'] ?? 'reach';
            $edgeLengths  = $body['edge_lengths'] ?? [];
            $obstacles    = $body['obstacles'] ?? [];
            $rackLevels   = intval($body['rack_levels'] ?? 3);
            $rackBays     = intval($body['rack_bays'] ?? 0);
            $rackHeight   = intval($body['rack_height'] ?? 0);
            $userRequest  = trim($body['user_request'] ?? '');

            if (!$palletW || !$palletD || !$palletH || !$liftHeight || !$ast) {
                throw new \Exception("필수 파렛트/지게차 정보가 누락되었습니다.");
            }

            // 포크 진입 방향에 따른 랙 규격 연산 공식 (깊이 = 미진입방향 파렛트 크기 - 100, 빔길이 = 진입폭 * 2 + 385)
            $nonEntryWidth = ($forkDir === 'W') ? $palletD : $palletW;
            $rackDepth  = $nonEntryWidth - 100;
            $entryWidth = ($forkDir === 'W') ? $palletW : $palletD;
            $beamLength = ($entryWidth * 2) + 385;

            // 로드빔 두께 바(var) 결정 (단당 중량 = 파렛트 무게 * 2)
            $totalLevelWeight = $palletWeight * 2;
            $beamThicknessBar = ($totalLevelWeight >= 2800) ? 150 : 125;

            // 단 높이 및 층수 계산 (클리어런스 150mm 적용)
            $clearance     = 150;
            $pitchPerLevel = $palletH + $clearance;
            $maxLevels     = max(1, floor($liftHeight / $pitchPerLevel));

            // 창고 벽면 정보 요약
            $wallSummary = '';
            if (!empty($edgeLengths)) {
                foreach ($edgeLengths as $i => $len) {
                    $wallSummary .= ($i + 1) . "번 벽면: {$len}mm\n";
                }
            } else {
                $wallSummary = "벽면 길이 미입력\n";
            }

            // 장애물 요약
            $obstacleSummary = '';
            if (!empty($obstacles)) {
                foreach ($obstacles as $obs) {
                    $type = $obs['type'] ?? '';
                    $name = $obs['name'] ?? '';
                    $obstacleSummary .= "- {$name} (타입: {$type})\n";
                }
            } else {
                $obstacleSummary = "장애물 없음\n";
            }

            $forkliftLabel = match($forkliftType) {
                'reach'   => '입승식(리치형)',
                'counter' => '좌승식(카운터발란스)',
                'vna'     => '삼방향(VNA)',
                default   => $forkliftType
            };

            $prompt = "당신은 파렛트랙(Pallet Rack) 전문 설계 AI입니다. 아래 정보로 설치 계획을 수립하세요.\n\n"
                . "## 창고 벽면\n{$wallSummary}\n"
                . "## 파렛트 제원\n"
                . "- 가로(W): {$palletW}mm, 세로(D): {$palletD}mm\n"
                . "- 적재 높이(H): {$palletH}mm (화물 포함)\n"
                . "- 중량: {$palletWeight}kg\n"
                . "- 포크 진입: {$forkDir}면 → 랙깊이 {$rackDepth}mm, 빔길이 {$beamLength}mm 기준\n\n"
                . "## 지게차 제원\n"
                . "- 종류: {$forkliftLabel}\n"
                . "- 최대 인상높이: {$liftHeight}mm\n"
                . "- AST(통로폭): {$ast}mm\n\n"
                . "## 설치 희망 제원\n"
                . "- 설치 단수: {$rackLevels}단 (최대 가능 단수: {$maxLevels}단)\n"
                . "- 설치 칸수: " . ($rackBays > 0 ? "{$rackBays}칸" : "최대한 많이 설치") . "\n"
                . "- 설치 높이: " . ($rackHeight > 0 ? "{$rackHeight}mm" : "적재 높이와 단수에 맞춰 자동 계산") . "\n\n"
                . "## 장애물\n{$obstacleSummary}\n"
                . "## 의뢰자 요청\n" . ($userRequest ?: "없음") . "\n\n"
                . "## 설계 및 계산 참고 가이드 (중요 학습 데이터)\n"
                . "1. 로드빔의 길이(Beam Length) 및 표준 규격:\n"
                . "   - 1100(W) x 1100(D) 파랫트 2대를 한 칸(Bay)에 올릴 때, 빔 길이는 (1100 * 2) + 여유공간 385mm = 2585mm로 설계하는 것이 표준 규격입니다. (한 칸 외측 폭은 대략 2670mm)\n"
                . "   - 파랫트와 기둥 사이 여유 마진 100mm, 파랫트 간 간격 100~150mm를 보장해야 합니다.\n"
                . "2. 코너(모서리) 기준 시공 순서 및 수량 공식 (필수 시공 원칙):\n"
                . "   - 현장 시공 편의를 위해, 모든 랙 라인은 벽면의 **코너(모서리 끝)에서부터 독립형(Starter, 1대)을 시작으로 차례대로 연결형(Add-on)이 이어지는 순서**로 설치 계획을 수립해야 합니다.\n"
                . "   - 연속된 한 라인의 수량: 독립형 1대 + 연결형 (전체 칸수 - 1)대.\n"
                . "3. 출입문 및 장애물 분할 배치 원칙 (2단계 분할 시공):\n"
                . "   - 벽면 중간에 출입문(door, shutter)이나 통행 장애물이 있어 라인이 나뉠 경우, **[좌측 코너 → 출입문 시작점]까지 1개 구간**, **[출입문 끝점 → 우측 코너]까지 1개 구간**으로 2단계에 걸쳐 각각 랙을 생성해야 합니다.\n"
                . "   - 각 분할 구간마다 각각 독립형 1대 + 연결형 (구간 칸수 - 1)대로 정확히 자재를 산출하십시오.\n"
                . "4. 장애물이 없는 벽면의 연속 배치 (임의 단절 금지):\n"
                . "   - 출입문이나 장애물이 없는 정상 벽면은 중간에 랙을 끊지 말고, 해당 벽면 전체 가용 길이에 맞추어 한 라인으로 꽉 채워 연속 배치하십시오.\n"
                . "5. 코너 기둥(Dead Space) 스마트 이격 규칙:\n"
                . "   - 모서리 코너 영역(랙 깊이 1000mm 이내의 데드스페이스)에 위치한 기둥은 랙의 길이 방향 통행이나 설치에 직접 간섭하지 않으므로, 불필요하게 600mm 이상 과도하게 벽면에서 띄우지 마십시오. (기본 벽면 이격 100mm 적용)\n"
                . "   - 랙 라인의 중간을 직접 침범하는 장애물에 대해서만 100~200mm 안전 마진을 적용해 분할 배치합니다.\n"
                . "6. 자투리 공간 '작은연결(Small Add-on)' 적용:\n"
                . "   - 2열 표준 빔(2585mm)이 들어가지 못하지만 1열용 공간이 남는 경우, 1열용 작은연결 빔(예: 1385mm 등)을 적용하여 공간 활용도를 극대화하고 작은연결 수량과 길이를 산출하십시오.\n"
                . "7. 중앙 복렬(Double Row) 배치 및 가로/세로 방향성(Direction) 인식:\n"
                . "   - 의뢰자가 '가운데/중앙 복수(복렬)' 배치를 요청하거나 '세로로 세팅'을 요청한 경우, direction을 'vert'(세로)로 설정하고 중앙 가용 영역에 상하/좌우 AST 통로폭을 확보한 세로형 복렬 랙을 설계하십시오. '가로로 세팅' 요청 시 direction은 'horiz'(가로)입니다.\n"
                . "   - 복렬 1라인(N칸) 자재 산출: 독립형 2대, 연결형 (N - 1) * 2대, 고정 홀더 (N + 1) * 2개.\n"
                . "8. 바이패스(Bypass) 설계 개념:\n"
                . "   - 통로 확보를 위해 특정 칸의 1~2단 로드빔을 비워 터널을 만들고 3단만 적재하는 경우, 자재 견적 시 하단 로드빔 자재가 제외되어 반영되어야 합니다.\n"
                . "9. 상세 AI 시공 리포트 작성 (summary):\n"
                . "   - summary 항목에는 창고 레이아웃, 벽면별 랙 배치 내역, 지게차 통로 확보 현황, 자재 총 수량 분석, 현장 시공 시 필수 팁 등을 줄글로 매우 풍부하고 상세하게 작성하십시오 (화면에서 스크롤로 전문이 모두 표시되므로 분량 축약 금지).\n"
                . "10. 사선(대각선/비직각) 벽면 랙 배치 금지 및 직각 기준 정렬 원칙 (다각형 창고 필수 규칙):\n"
                . "   - 5각형, 6각형 등 다각형 창고에서 사선(대각선) 벽면에는 가로/세로 랙을 억지로 배치하지 마십시오. (사선 벽에 랙을 붙이면 벽을 뚫고 나가거나 중앙 랙과 90도로 겹쳐 충돌합니다).\n"
                . "   - 파렛트랙은 수평/수직인 직각 벽면에만 벽면 랙을 설치하고, 중앙 공간에는 직각 축 기준(세로 또는 가로)으로 복렬 랙을 배치하여 사선 벽면과 충돌 없이 안전한 통로를 확보하십시오.\n\n"
                . "아래 JSON 형식으로만 응답하세요 (다른 텍스트 없이 순수 JSON):\n"
                . '{"summary":"전체 설계 요약 및 상세 시공 분석(한국어로 분량 축약 없이 충분하고 친절하게 상세히 작성)","recommended_levels":단수숫자,"rack_depth_mm":숫자,"beam_length_mm":숫자,"small_beam_length_mm":작은연결빔길이(숫자,기본1385),"aisle_width_mm":숫자,"beam_thickness_bar":로드빔두께(125또는150),"notes":["주의사항1","주의사항2"],"estimated_independent":숫자,"estimated_connected":숫자,"estimated_small_connected":작은연결수량,"estimated_holders":홀더총수량,"layout_racks":[{"edgeIndex":벽면인덱스(0부터시작),"bays":설치할칸수(숫자,0이면자동),"isDouble":복렬여부(true/false)}],"center_double_racks":[{"direction":"horiz 또는 vert","bays":칸수,"rows":열수}]}';

            // Gemini API 직접 호출 (SSL 인증서 문제 우회 - 로컬 개발환경 대응)
            $db = Database::getInstance();
            $aiConfig = $db->query("SELECT gemini_key FROM ai_config WHERE id = 1")->fetch();
            $geminiKey = $aiConfig['gemini_key'] ?? '';
            if (!$geminiKey) {
                throw new \Exception("Gemini API Key가 설정되지 않았습니다. /admin/ai/config 에서 설정해주세요.");
            }

            $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=' . $geminiKey;
            $requestBody = json_encode([
                'contents' => [
                    ['parts' => [['text' => $prompt]]]
                ],
                'generationConfig' => [
                    'temperature'     => 0.3,
                    'maxOutputTokens' => 4096,
                ]
            ]);

            $ch = curl_init($apiUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST,           true);
            curl_setopt($ch, CURLOPT_POSTFIELDS,     $requestBody);
            curl_setopt($ch, CURLOPT_HTTPHEADER,     ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Windows 로컬 SSL 인증서 우회
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_TIMEOUT,        30);

            $curlResponse = curl_exec($ch);
            $curlError    = curl_error($ch);
            curl_close($ch);

            if ($curlError) {
                throw new \Exception("Gemini API 연결 오류: " . $curlError);
            }

            $geminiJson  = json_decode($curlResponse, true);
            if (isset($geminiJson['error'])) {
                throw new \Exception("Gemini API 오류: " . ($geminiJson['error']['message'] ?? '알 수 없는 오류'));
            }

            $rawResponse = $geminiJson['candidates'][0]['content']['parts'][0]['text'] ?? '';
            if (!$rawResponse) {
                throw new \Exception("Gemini API 응답이 비어있습니다. 잠시 후 다시 시도해주세요.");
            }

            // 마크다운 코드펜스 제거
            $rawCleaned = preg_replace('/^```(?:json)?\s*/i', '', trim($rawResponse));
            $rawCleaned = preg_replace('/\s*```\s*$/i', '', $rawCleaned);

            // ──────────────────────────────────────────────────────────────
            // [STEP 1] summary 값을 rawResponse 에서 직접 추출 (잘림 방지)
            //  - AI가 summary 안에 이스케이프 안된 따옴표/특수문자를 쓰면
            //    json_decode가 중간에 끊기므로, 먼저 rawCleaned 에서
            //    "summary":"..." 부분을 직접 잘라내어 보관.
            // ──────────────────────────────────────────────────────────────
            $extractedSummary = '';

            // 패턴1: 표준 이스케이프 JSON (\" 포함)
            if (preg_match('/"summary"\s*:\s*"((?:[^"\\\\]|\\\\.)*)"/su', $rawCleaned, $sm)) {
                $extractedSummary = stripcslashes($sm[1]);
            }
            // 패턴2: summary 값이 다음 키 직전까지
            elseif (preg_match('/"summary"\s*:\s*"(.*?)"\s*,\s*"[a-z_]+"/su', $rawCleaned, $sm)) {
                $extractedSummary = stripcslashes($sm[1]);
            }
            // 패턴3: summary 값이 JSON 끝 } 직전까지
            elseif (preg_match('/"summary"\s*:\s*"(.*?)"\s*\}/su', $rawCleaned, $sm)) {
                $extractedSummary = stripcslashes($sm[1]);
            }

            // 추출된 summary에서 \n 리터럴을 실제 줄바꿈으로 복원
            if ($extractedSummary !== '') {
                $extractedSummary = str_replace(['\\r\\n','\\n','\\r'], "\n", $extractedSummary);
                $extractedSummary = stripslashes($extractedSummary);
            }

            // ──────────────────────────────────────────────────────────────
            // [STEP 2] summary 를 임시로 플레이스홀더로 교체 후 JSON 파싱
            //  - 긴 summary 가 json_decode 실패 원인이 되지 않도록
            // ──────────────────────────────────────────────────────────────
            $jsonForParse = preg_replace(
                '/"summary"\s*:\s*"(?:[^"\\\\]|\\\\.)*"/su',
                '"summary":"__SUMMARY_PLACEHOLDER__"',
                $rawCleaned
            );

            // { } 범위 추출
            if (preg_match('/\{.*\}/s', $jsonForParse, $match)) {
                $jsonStr = $match[0];
            } else {
                $jsonStr = $jsonForParse;
            }

            $result = json_decode($jsonStr, true);

            // JSON 파싱 실패 시 기본값으로
            if (json_last_error() !== JSON_ERROR_NONE || !is_array($result)) {
                $result = [
                    'recommended_levels'   => $maxLevels,
                    'rack_depth_mm'        => $rackDepth,
                    'beam_length_mm'       => $beamLength,
                    'aisle_width_mm'       => $ast,
                    'beam_thickness_bar'   => $beamThicknessBar,
                    'notes'                => [],
                    'estimated_independent'=> 0,
                    'estimated_connected'  => 0,
                ];
            }

            // ──────────────────────────────────────────────────────────────
            // [STEP 3] summary 원본 복원 (플레이스홀더 → 실제 내용)
            // ──────────────────────────────────────────────────────────────
            $result['summary'] = $extractedSummary ?: ($result['summary'] ?? '설치 계획이 정상적으로 수립되었습니다.');
            if ($result['summary'] === '__SUMMARY_PLACEHOLDER__') {
                $result['summary'] = '설치 계획이 정상적으로 수립되었습니다.';
            }
            // 혹시 남아있는 \n 리터럴 한번 더 복원
            $result['summary'] = str_replace(['\\r\\n','\\n','\\r'], "\n", $result['summary']);


            if (!isset($result['beam_thickness_bar'])) {
                $result['beam_thickness_bar'] = $beamThicknessBar;
            }

            // 독립형/연결형 수량 계산 보정 (AI가 0을 반환했을 경우 자동 계산)
            if (isset($result['estimated_independent']) && intval($result['estimated_independent']) === 0) {
                $targetLine = 1;
                if (preg_match('/(\d+)\s*번\s*(라인|벽면|벽)/', $userRequest, $m)) {
                    $targetLine = intval($m[1]);
                }
                $edgeIndex = $targetLine - 1;
                $wallLen = intval($edgeLengths[$edgeIndex] ?? 12000);
                if ($wallLen <= 0) $wallLen = 12000;
                
                $bayWidth = $beamLength + 150;
                $bays = $rackBays > 0 ? $rackBays : max(1, floor(($wallLen - 100) / $bayWidth));
                
                $result['estimated_independent'] = 1;
                $result['estimated_connected'] = max(0, $bays - 1);
            }

            echo json_encode(['success' => true, 'data' => $result]);

        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    
    /**
     * POST /quote/submit
     * 캔버스 견적 요청 저장
     */
    public function submitQuote() {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $body = json_decode(file_get_contents('php://input'), true);
            $vendorUserId = intval($body['vendor_user_id'] ?? 0);
            $company      = trim($body['company'] ?? '');
            $name         = trim($body['name'] ?? '');
            $phone        = trim($body['phone'] ?? '');
            $email        = trim($body['email'] ?? '');
            $address      = trim($body['address'] ?? '');
            $canvasData   = $body['canvas_data'] ?? '';
            $summary      = $body['summary'] ?? '';
            $edge_lengths = $body['edge_lengths'] ?? '';
            $pallet_w     = intval($body['pallet_w'] ?? 0);
            $pallet_d     = intval($body['pallet_d'] ?? 0);
            $pallet_h     = intval($body['pallet_h'] ?? 0);
            $pallet_weight= intval($body['pallet_weight'] ?? 0);
            $fork_dir     = $body['fork_direction'] ?? '';
            $fork_type    = $body['forklift_type'] ?? '';
            $fork_lift_h  = intval($body['forklift_lift_height'] ?? 0);
            $fork_ast     = intval($body['forklift_ast'] ?? 0);
            $rack_levels  = intval($body['rack_levels'] ?? 0);
            $rack_height  = $body['rack_height'] ?? '';
            $rack_spec    = $body['rack_spec'] ?? '';
            $rack_type    = $body['rack_type'] ?? '';
            $rack_indep   = intval($body['rack_indep'] ?? 0);
            $rack_conn    = intval($body['rack_conn'] ?? 0);
            $rack_small   = intval($body['rack_small_conn'] ?? 0);
            $rack_bypass  = intval($body['rack_bypass'] ?? 0);
            $rack_bypass_type = $body['rack_bypass_type'] ?? '';
            $rack_holders = intval($body['rack_holders'] ?? 0);
            $rack_pallets = intval($body['rack_pallets'] ?? 0);
            $condition_type = $body['condition_type'] ?? 'new';
            $self_install = intval($body['self_install'] ?? 0);

            if ($company === '' || $name === '' || $phone === '' || $address === '') {
                throw new \Exception("필수 입력 정보가 누락되었습니다.");
            }

            $imageData = $body['image_data'] ?? '';
            $imagePath = '';
            
            if (!empty($imageData) && preg_match('/^data:image\/(\w+);base64,/', $imageData, $type)) {
                $imageData = substr($imageData, strpos($imageData, ',') + 1);
                $type = strtolower($type[1]);
                
                if (in_array($type, ['jpg', 'jpeg', 'png', 'gif'])) {
                    $imageData = base64_decode($imageData);
                    if ($imageData !== false) {
                        $dir = __DIR__ . '/../../public/uploads/quotes';
                        if (!is_dir($dir)) {
                            mkdir($dir, 0755, true);
                        }
                        $filename = 'quote_' . time() . '_' . rand(1000, 9999) . '.' . $type;
                        $filepath = $dir . '/' . $filename;
                        if (file_put_contents($filepath, $imageData)) {
                            $imagePath = '/uploads/quotes/' . $filename;
                        }
                    }
                }
            }

            $db = Database::getInstance();
            $sql = "INSERT INTO quote_requests (
                        vendor_user_id, company, name, phone, email, address, canvas_data, image_path, summary,
                        edge_lengths, pallet_w, pallet_d, pallet_h, pallet_weight, fork_direction,
                        forklift_type, forklift_lift_height, forklift_ast, rack_levels, rack_height,
                        rack_spec, rack_type, rack_indep, rack_conn, rack_small_conn, rack_bypass, rack_bypass_type, rack_holders, rack_pallets,
                        condition_type, self_install
                    ) VALUES (
                        :vuid, :company, :name, :phone, :email, :address, :cdata, :imgpath, :summary,
                        :edge_lengths, :pallet_w, :pallet_d, :pallet_h, :pallet_weight, :fork_dir,
                        :fork_type, :fork_lift_h, :fork_ast, :rack_levels, :rack_height,
                        :rack_spec, :rack_type, :rack_indep, :rack_conn, :rack_small, :rack_bypass, :rack_bypass_type, :rack_holders, :rack_pallets,
                        :condition_type, :self_install
                    )";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([
                'vuid'    => $vendorUserId,
                'company' => $company,
                'name'    => $name,
                'phone'   => $phone,
                'email'   => $email,
                'address' => $address,
                'cdata'   => $canvasData,
                'imgpath' => $imagePath,
                'summary' => $summary,
                'edge_lengths' => $edge_lengths,
                'pallet_w' => $pallet_w,
                'pallet_d' => $pallet_d,
                'pallet_h' => $pallet_h,
                'pallet_weight' => $pallet_weight,
                'fork_dir' => $fork_dir,
                'fork_type' => $fork_type,
                'fork_lift_h' => $fork_lift_h,
                'fork_ast' => $fork_ast,
                'rack_levels' => $rack_levels,
                'rack_height' => $rack_height,
                'rack_spec' => $rack_spec,
                'rack_type' => $rack_type,
                'rack_indep' => $rack_indep,
                'rack_conn' => $rack_conn,
                'rack_small' => $rack_small,
                'rack_bypass' => $rack_bypass,
                'rack_bypass_type' => $rack_bypass_type,
                'rack_holders' => $rack_holders,
                'rack_pallets' => $rack_pallets,
                'condition_type' => $condition_type,
                'self_install' => $self_install
            ]);

            echo json_encode(['success' => true, 'message' => '견적 요청이 성공적으로 저장되었습니다.']);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
