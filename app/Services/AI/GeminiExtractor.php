<?php

namespace App\Services\AI;

use App\Core\Database;
use PDO;
use Exception;
use PhpOffice\PhpSpreadsheet\IOFactory;

class GeminiExtractor extends AbstractAIExtractor
{
    private $apiKey;
    private $modelName;

    public function __construct()
    {
        $db = Database::getInstance();
        $stmt = $db->query("SELECT gemini_key, default_model FROM ai_config LIMIT 1");
        $config = $stmt->fetch();
        if (!$config || empty($config['gemini_key'])) {
            throw new Exception("Gemini API Key is not configured in the database.");
        }
        $this->apiKey = $config['gemini_key'];
        
        // If default_model is generic 'gemini', use a sensible default.
        // If it's a specific version like 'gemini-2.5-flash', use it.
        $model = $config['default_model'] ?? 'gemini-2.5-flash';
        if ($model === 'gemini') {
            $model = 'gemini-2.5-flash';
        }
        $this->modelName = $model;
    }



    /**
     * Gemini API 호출하여 JSON 파라미터 추출
     */
    public function extractPricingFromJson(string $excelText): array
    {
        return $this->callGeminiApiWithFallback($excelText);
    }

    private function callGeminiApiWithFallback(string $excelText, bool $isRetry = false): array
    {
        $promptPath = __DIR__ . '/../Prompts/sehwa_price_extract_prompt.txt';
        if (!file_exists($promptPath)) {
            throw new Exception("System prompt file not found.");
        }
        $systemPrompt = file_get_contents($promptPath);

        $userPrompt = "아래는 업로드된 단가 엑셀 데이터입니다. 반드시 JSON 형식으로만 추출해주세요.\n\n" . $excelText;

        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$this->modelName}:generateContent?key=" . $this->apiKey;

        $data = [
            "contents" => [
                [
                    "role" => "user",
                    "parts" => [
                        ["text" => $systemPrompt . "\n\n" . $userPrompt]
                    ]
                ]
            ],
            "generationConfig" => [
                "temperature" => 0.1,
                "responseMimeType" => "application/json"
            ]
        ];

        set_time_limit(180);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 180);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Fallback Logic: If model is not found (400 or 404) and we haven't retried yet
        if (($httpCode === 400 || $httpCode === 404) && !$isRetry) {
            $latestModel = $this->discoverLatestFlashModel();
            if ($latestModel && $latestModel !== $this->modelName) {
                // Update DB so we don't have to discover next time
                $db = Database::getInstance();
                $stmt = $db->prepare("UPDATE ai_config SET default_model = :model");
                $stmt->execute(['model' => $latestModel]);
                
                // Retry with new model
                $this->modelName = $latestModel;
                return $this->callGeminiApiWithFallback($excelText, true);
            }
        }

        if ($httpCode !== 200) {
            throw new Exception("Gemini API Error ({$this->modelName}): HTTP " . $httpCode . " - " . $response);
        }

        $resData = json_decode($response, true);
        
        if (isset($resData['candidates'][0]['content']['parts'][0]['text'])) {
            $jsonString = $resData['candidates'][0]['content']['parts'][0]['text'];
            
            $jsonString = preg_replace('/^```json\s*/i', '', $jsonString);
            $jsonString = preg_replace('/```\s*$/i', '', $jsonString);
            $jsonString = trim($jsonString);

            $extracted = json_decode($jsonString, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception("Failed to parse Gemini response as JSON: " . json_last_error_msg() . "\n" . $jsonString);
            }
            return $extracted;
        }

        throw new Exception("Unexpected API response structure.");
    }

    /**
     * 동적으로 최신 모델 탐색
     */
    private function discoverLatestFlashModel(): ?string
    {
        $url = "https://generativelanguage.googleapis.com/v1beta/models?key=" . $this->apiKey;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            $data = json_decode($response, true);
            $models = $data['models'] ?? [];
            $candidates = [];
            
            foreach ($models as $model) {
                $name = str_replace('models/', '', $model['name']);
                // Only consider 'gemini' and 'flash' models (fastest/cheapest)
                if (strpos($name, 'gemini') !== false && strpos($name, 'flash') !== false && strpos($name, 'vision') === false) {
                    $candidates[] = $name;
                }
            }
            
            if (!empty($candidates)) {
                // Sort descending to get the highest version number
                rsort($candidates);
                return $candidates[0]; // e.g., 'gemini-2.5-flash' or 'gemini-3.0-flash'
            }
        }
        return null;
    }
}
