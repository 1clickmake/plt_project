<?php

namespace Plugins\aimanager;

use App\Core\Database;

class Schema
{
    public function install()
    {
        $db = Database::getInstance();
        
        // Create AI config table
        $sql = "CREATE TABLE IF NOT EXISTS `ai_config` (
            `id` INT(11) PRIMARY KEY DEFAULT 1,
            `openai_key` VARCHAR(255) DEFAULT '',
            `claude_key` VARCHAR(255) DEFAULT '',
            `gemini_key` VARCHAR(255) DEFAULT '',
            `groq_key` VARCHAR(255) DEFAULT '',
            `default_model` VARCHAR(50) DEFAULT 'gpt-4o',
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='AI Configuration Table';";
        
        $db->exec($sql);

        // Proactively add missing columns if table already existed
        try {
            $db->exec("ALTER TABLE `ai_config` ADD COLUMN `gemini_key` VARCHAR(255) DEFAULT '' AFTER `claude_key` ");
        } catch (\Exception $e) { /* Column might already exist */ }
        
        try {
            $db->exec("ALTER TABLE `ai_config` ADD COLUMN `groq_key` VARCHAR(255) DEFAULT '' AFTER `gemini_key` ");
        } catch (\Exception $e) { /* Column might already exist */ }

        // Insert default record if not exists
        $stmt = $db->query("SELECT COUNT(*) FROM `ai_config` WHERE `id` = 1");
        if ($stmt->fetchColumn() == 0) {
            $db->exec("INSERT INTO `ai_config` (`id`) VALUES (1)");
        }
    }
}
