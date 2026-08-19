<?php

namespace App\Services\AI;

use App\Core\Database;
use Exception;

class AIExtractorFactory
{
    /**
     * DB의 default_model 설정을 기반으로 적절한 AI Extractor 인스턴스를 반환
     * 
     * @return AIExtractorInterface
     * @throws Exception
     */
    public static function create(): AIExtractorInterface
    {
        $db = Database::getInstance();
        $stmt = $db->query("SELECT default_model FROM ai_config LIMIT 1");
        $config = $stmt->fetch();
        
        $model = strtolower($config['default_model'] ?? 'gemini');

        if (strpos($model, 'gemini') !== false) {
            return new GeminiExtractor();
        } elseif (
            strpos($model, 'gpt') !== false || 
            strpos($model, 'openai') !== false ||
            strpos($model, 'llama') !== false ||
            strpos($model, 'groq') !== false ||
            strpos($model, 'meta') !== false ||
            strpos($model, 'mixtral') !== false
        ) {
            return new OpenAICompatibleExtractor();
        } elseif (strpos($model, 'claude') !== false || strpos($model, 'anthropic') !== false) {
            // return new ClaudeExtractor(); // 추후 구현
            throw new Exception("Claude Extractor is not implemented yet.");
        } else {
            // Fallback default
            return new GeminiExtractor();
        }
    }
}
