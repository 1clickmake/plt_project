<?php

namespace Plugins\chatbot;

use App\Core\Database;

class Schema
{
    public function install()
    {
        $db = Database::getInstance();

        // 1. Create AI config table if it doesn't exist (Standalone Mode)
        $sql = "CREATE TABLE IF NOT EXISTS `ai_config` (
            `id` INT(11) PRIMARY KEY DEFAULT 1,
            `openai_key` VARCHAR(255) DEFAULT '',
            `claude_key` VARCHAR(255) DEFAULT '',
            `gemini_key` VARCHAR(255) DEFAULT '',
            `groq_key` VARCHAR(255) DEFAULT '',
            `default_model` VARCHAR(50) DEFAULT 'gpt-4o',
            `use_chatbot` TINYINT(1) DEFAULT 0,
            `chatbot_limit_guest` INT DEFAULT 5,
            `chatbot_limit_member` INT DEFAULT 20,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='AI Configuration Table';";
        
        $db->exec($sql);

        // 2. If table existed but missing columns (e.g. installed by ai-manager first), add them
        try { $db->exec("ALTER TABLE `ai_config` ADD COLUMN `use_chatbot` TINYINT(1) DEFAULT 0 AFTER `default_model` "); } catch (\Exception $e) { }
        try { $db->exec("ALTER TABLE `ai_config` ADD COLUMN `chatbot_limit_guest` INT DEFAULT 5 AFTER `use_chatbot` "); } catch (\Exception $e) { }
        try { $db->exec("ALTER TABLE `ai_config` ADD COLUMN `chatbot_limit_member` INT DEFAULT 20 AFTER `chatbot_limit_guest` "); } catch (\Exception $e) { }

        // Ensure default row exists
        $stmt = $db->query("SELECT COUNT(*) FROM `ai_config` WHERE `id` = 1");
        if ($stmt->fetchColumn() == 0) {
            $db->exec("INSERT INTO `ai_config` (`id`) VALUES (1)");
        }

        // 3. Create chatbot_logs table
        $sqlLogs = "CREATE TABLE IF NOT EXISTS `chatbot_logs` (
            `id` INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `session_id` VARCHAR(100) NOT NULL,
            `user_id` VARCHAR(50) DEFAULT NULL,
            `question` TEXT NOT NULL,
            `answer` TEXT NOT NULL,
            `ip_address` VARCHAR(45) NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX (`session_id`),
            INDEX (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        
        $db->exec($sqlLogs);
    }
}
