<?php

namespace App\Controllers;

use App\Core\Database;
use Plugins\aimanager\Services\AiService;

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

            // 포크 진입 방향에 따른 랙 깊이 & 빔 길이 결정
            $rackDepth  = ($forkDir === 'W') ? $palletD : $palletW;
            $beamLength = ($forkDir === 'W') ? $palletW : $palletD;

            // 층수 계산 (클리어런스 150mm 자동 적용)
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
                . "1. 로드빔의 길이(Beam Length)는 표준 파렛트 배치 기준에 맞춰야 합니다.\n"
                . "   - 예: 1100(W) x 1100(D) 파랫트 2대를 한 칸(Bay)에 올릴 때, 빔 길이는 (1100 * 2) + 여유공간 385mm = 2585mm로 설계하는 것이 표준 규격입니다. (이때 전체 한 칸 외측 폭은 기둥 두께 등을 포함해 대략 2670mm가 됩니다.)\n"
                . "   - 파랫트와 기둥 사이의 여유 마진은 100mm, 파랫트와 파랫트 사이는 100~150mm를 보장해야 합니다.\n"
                . "2. 바이패스(Bypass) 설계 개념:\n"
                . "   - 바이패스는 지게차나 작업 동선 확보를 위해 통로 중간의 특정 칸에서 하단 단수를 비우는 설계 방식입니다.\n"
                . "   - 예: 3단 랙 레이아웃에서 바이패스가 적용되는 칸은 아래 1~2단 로드빔과 파레트를 제거하여 지게차가 지나갈 수 있는 터널 통로를 만들고, 최상단인 3단만 적재 공간으로 남겨둡니다. (이 경우 독립/연결 자재 계산 시 하단 로드빔 자재가 제외되어 견적에 반영되어야 합니다.)\n"
                . "3. 수량 계산 공식 (독립형과 연결형):\n"
                . "   - 한 줄의 레이아웃(Row)은 반드시 1개의 독립형(Starter) 랙과 (전체 칸수 - 1)개의 연결형(Add-on) 랙으로 구성됩니다.\n"
                . "   - 예: 총 59칸을 한 라인에 연속 배치할 경우, 독립형 is 1대, 연결형은 58대가 됩니다. (단, 기둥이나 입구로 인해 라인이 나뉘면 독립형이 추가됩니다.)\n"
                . "4. 작업 통로폭(Aisle Width)은 지게차의 직각교차 통로폭(AST)에 작업 안전 마진 100~200mm를 더해 제안하십시오. (예: AST 2800mm일 때 통로폭은 2900~3000mm 권장)\n"
                . "5. 의뢰자가 '전체 공간'에 설치해달라고 하거나 넓은 배치를 원할 경우, 창고의 가용한 벽면(edgeIndex)들을 최대한 활용하여 여러 라인에 걸쳐 파렛트랙을 다수 배치하는 계획(layout_racks)을 설계하십시오. 단, 출입문(door, shutter)이나 장애물이 설치된 벽면은 안전 이격을 고려하여 배치를 피하거나 bays 수를 대폭 줄여 장애물과 간섭되지 않도록 정교하게 배치해야 합니다.\n\n"
                . "아래 JSON 형식으로만 응답하세요 (다른 텍스트 없이 순수 JSON):\n"
                . '{"summary":"전체 설계 요약 2~3문장(한국어)","recommended_levels":단수숫자,"rack_depth_mm":숫자,"beam_length_mm":숫자,"aisle_width_mm":숫자,"notes":["주의사항1","주의사항2"],"estimated_independent":숫자,"estimated_connected":숫자,"layout_racks":[{"edgeIndex":벽면인덱스(0부터시작),"bays":설치할칸수(숫자),"isDouble":복렬여부(true/false)}]}';



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
                    'maxOutputTokens' => 1024,
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

            // 중괄호 { } 사이의 내용만 추출하여 더욱 견고하게 JSON 파싱
            if (preg_match('/\{.*\}/s', $rawResponse, $match)) {
                $jsonStr = $match[0];
            } else {
                $jsonStr = $rawResponse;
            }

            $result = json_decode($jsonStr, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $result = [
                    'summary'              => $rawResponse,
                    'recommended_levels'   => $maxLevels,
                    'rack_depth_mm'        => $rackDepth,
                    'beam_length_mm'       => $beamLength,
                    'aisle_width_mm'       => $ast,
                    'notes'                => [],
                    'estimated_independent'=> 0,
                    'estimated_connected'  => 0,
                ];
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
}
