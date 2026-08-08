<?php

namespace App\Core;

class View {
    public static function render($path, $data = []) {
        // CSRF Token auto-injection
        $csrf_html = Csrf::getToken();
        
        // Escape data recursively for XSS protection
        $escapedData = self::escape($data);

        // Extract variables to local scope
        extract($escapedData);
        
        // Ensure $csrf_token and global $csrf_token are available as raw HTML
        global $csrf_token;
        $csrf_token = $csrf_html; // This updates both the global and the local variable in this scope
        
        // Provide raw data for special cases (WYSIWYG, etc)
        $_raw = $data;

        // Define path
        $viewFile = __DIR__ . '/../../views/' . $path . '.php'; // Adjust path relative to App/Core

        if (file_exists($viewFile)) {
            require $viewFile;
        } else {
            // Check in plugins directory
            $pluginViewFile = CM_PATH . '/' . $path . '.php';
            if (file_exists($pluginViewFile)) {
                require $pluginViewFile;
            } else {
                echo "View file not found: $path";
            }
        }
    }

    private static function escape($data) {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = self::escape($value);
            }
        } elseif (is_string($data)) {
            return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        }
        return $data;
    }
}
