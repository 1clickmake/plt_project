<?php

namespace App\Services\AI;

use App\Core\Database;
use Exception;

class OpenAICompatibleExtractor extends AbstractAIExtractor
{
    private $apiKey;
    private $modelName;
    private $endpointUrl;

    public function __construct()
    {
        $db = Database::getInstance();
        $stmt = $db->query("SELECT * FROM ai_config LIMIT 1");
        $config = $stmt->fetch();
        
        $model = strtolower($config['default_model'] ?? 'gpt-4o');
        
        if (strpos($model, 'gpt') !== false || strpos($model, 'openai') !== false) {
            $this->apiKey = $config['openai_key'] ?? '';
            $this->modelName = $model;
            $this->endpointUrl = 'https://api.openai.com/v1/chat/completions';
        } elseif (strpos($model, 'llama') !== false || strpos($model, 'mixtral') !== false || strpos($model, 'gemma') !== false) {
            // Assume Groq for open source models by default unless meta_key is specifically targeted
            if (!empty($config['groq_key'])) {
                $this->apiKey = $config['groq_key'];
                $this->modelName = $model;
                $this->endpointUrl = 'https://api.groq.com/openai/v1/chat/completions';
            } else {
                // Fallback to meta key if they configured Meta API
                $this->apiKey = $config['meta_key'] ?? '';
                $this->modelName = $model;
                // Place holder for Meta's hypothetical OpenAI-compatible endpoint
                $this->endpointUrl = 'https://api.meta.ai/v1/chat/completions'; 
            }
        } elseif (strpos($model, 'meta') !== false) {
            $this->apiKey = $config['meta_key'] ?? '';
            $this->modelName = str_replace('meta-', '', $model); // fallback model name
            $this->endpointUrl = 'https://api.meta.ai/v1/chat/completions';
        } else {
            // Default to Groq if unknown but Groq key exists
            if (!empty($config['groq_key'])) {
                $this->apiKey = $config['groq_key'];
                $this->modelName = 'llama3-70b-8192'; // Groq default fallback
                $this->endpointUrl = 'https://api.groq.com/openai/v1/chat/completions';
            } else {
                throw new Exception("No valid API key found for the selected model: {$model}");
            }
        }

        if (empty($this->apiKey)) {
            throw new Exception("API Key is missing for the selected model: {$model}");
        }
    }

    public function extractPricingFromJson(string $excelText): array
    {
        $promptPath = __DIR__ . '/../Prompts/sehwa_price_extract_prompt.txt';
        if (!file_exists($promptPath)) {
            throw new Exception("System prompt file not found.");
        }
        $systemPrompt = file_get_contents($promptPath);
        $userPrompt = "아래는 업로드된 단가 엑셀 데이터입니다. 반드시 JSON 형식으로만 추출해주세요.\n\n" . $excelText;

        $data = [
            "model" => $this->modelName,
            "messages" => [
                ["role" => "system", "content" => $systemPrompt],
                ["role" => "user", "content" => $userPrompt]
            ],
            "response_format" => ["type" => "json_object"],
            "temperature" => 0.1
        ];

        $ch = curl_init($this->endpointUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 180);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new Exception("API Error ({$this->modelName}): HTTP " . $httpCode . " - " . $response);
        }

        $resData = json_decode($response, true);
        
        if (isset($resData['choices'][0]['message']['content'])) {
            $jsonString = $resData['choices'][0]['message']['content'];
            $jsonString = preg_replace('/^```json\s*/i', '', $jsonString);
            $jsonString = preg_replace('/```\s*$/i', '', $jsonString);
            $jsonString = trim($jsonString);

            $extracted = json_decode($jsonString, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception("Failed to parse response as JSON: " . json_last_error_msg() . "\n" . $jsonString);
            }
            return $extracted;
        }

        throw new Exception("Unexpected API response structure.");
    }
}
