<?php

namespace Plugins\chatbot\Services;

use App\Core\Database;
use Exception;
use NeuronAI\Agent;
use NeuronAI\Providers\Gemini\Gemini;
use NeuronAI\Providers\OpenAI\OpenAI;
use NeuronAI\Providers\Anthropic\Anthropic;
use NeuronAI\Chat\Messages\UserMessage;
use NeuronAI\Chat\Enums\MessageRole;
use NeuronAI\Chat\Messages\Message;

class AiService
{
    private $config;

    public function __construct()
    {
        $db = Database::getInstance();
        $this->config = $db->query("SELECT * FROM `ai_config` WHERE `id` = 1")->fetch();
    }

    public function generate($prompt, $model = null)
    {
        $model = $model ?? ($this->config['default_model'] ?? 'gemini-1.5-flash');

        try {
            if (stripos($model, 'gemini') !== false) {
                return $this->useNeuronGemini($prompt, $model);
            } elseif (preg_match('/(gpt|o1|o3)/i', $model)) {
                return $this->useNeuronOpenAI($prompt, $model);
            } elseif (stripos($model, 'claude') !== false) {
                return $this->useNeuronAnthropic($prompt, $model);
            } else {
                return $this->callGroq($prompt, $model);
            }
        } catch (Exception $e) {
            // Log or detailed message
            throw new Exception("AI Logic Error: " . $e->getMessage());
        }
    }

    private function useNeuronGemini($prompt, $model)
    {
        $apiKey = $this->config['gemini_key'] ?? '';
        if (!$apiKey) throw new Exception("Gemini API Key가 설정되지 않았습니다.");

        $maxRetries = 3;
        $retryDelay = 2; // seconds

        for ($i = 0; $i < $maxRetries; $i++) {
            try {
                $provider = new Gemini($apiKey, $model);

                $reflection = new \ReflectionClass($provider);
                $baseUriProp = $reflection->getProperty('baseUri');
                $baseUriProp->setAccessible(true);
                $baseUriProp->setValue($provider, 'https://generativelanguage.googleapis.com/v1beta/models');

                $fullPrompt = "Instructions: You are a professional web content writer. ALWAYS output clean HTML (h2, p, ul, li) without markdown blocks.\n\nUser Request: " . $prompt;
                $messages = [\NeuronAI\Chat\Messages\UserMessage::make($fullPrompt)];

                $response = $provider->chat($messages);
                return $response->getContent();

            } catch (Exception $e) {
                // Check if it's a 429 error
                if (strpos($e->getMessage(), '429') !== false) {
                    if ($i === $maxRetries - 1) {
                        throw new Exception("구글 AI의 무료 사용 할당량을 모두 사용했습니다. 약 1~5분 후 다시 시도해주세요.");
                    }
                    // Exponential backoff
                    sleep($retryDelay * ($i + 1));
                    continue;
                }
                throw $e;
            }
        }
    }

    private function useNeuronOpenAI($prompt, $model)
    {
        $apiKey = $this->config['openai_key'] ?? '';
        if (!$apiKey) throw new Exception("OpenAI API Key가 설정되지 않았습니다.");

        $provider = new OpenAI($apiKey, $model);
        $agent = (new Agent())
            ->setAiProvider($provider)
            ->withInstructions("You are a professional web content writer. ALWAYS output clean HTML (h2, p, ul, li) without markdown blocks.");

        $response = $agent->chat(\NeuronAI\Chat\Messages\UserMessage::make($prompt));
        return $response->getContent();
    }

    private function useNeuronAnthropic($prompt, $model)
    {
        $apiKey = $this->config['claude_key'] ?? '';
        if (!$apiKey) throw new Exception("Claude API Key가 설정되지 않았습니다.");

        $provider = new Anthropic($apiKey, $model);
        $agent = (new Agent())
            ->setAiProvider($provider)
            ->withInstructions("You are a professional web content writer. ALWAYS output clean HTML (h2, p, ul, li) without markdown blocks.");

        $response = $agent->chat(\NeuronAI\Chat\Messages\UserMessage::make($prompt));
        return $response->getContent();
    }

    private function callGroq($prompt, $model)
    {
        $apiKey = $this->config['groq_key'] ?? '';
        if (!$apiKey) throw new Exception("Groq API Key가 설정되지 않았습니다.");

        $ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
        $data = [
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => 'You are a professional copywriter. Output MUST be in HTML format without ```html blocks.'],
                ['role' => 'user', 'content' => $prompt]
            ],
            'temperature' => 0.7
        ];

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey
        ]);

        $response = curl_exec($ch);
        $result = json_decode($response, true);
        curl_close($ch);

        if (isset($result['choices'][0]['message']['content'])) {
            return $result['choices'][0]['message']['content'];
        }

        throw new Exception("Groq Error ($model): " . ($result['error']['message'] ?? 'Unknown error'));
    }
}
