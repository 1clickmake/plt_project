<?php

namespace App\Core;

class PluginManager
{
    private static $instance = null;
    private $plugins = [];

    private function __construct() {
        $this->registerAutoloader();
        $this->loadPlugins();
    }

    private function registerAutoloader() {
        spl_autoload_register(function ($class) {
            $prefix = 'Plugins\\';
            $base_dir = CM_PLUGINS_PATH . '/';

            $len = strlen($prefix);
            if (strncmp($prefix, $class, $len) !== 0) {
                return;
            }

            $relative_class = substr($class, $len);
            
            // Map the first segment (plugin folder) and keep the rest
            $parts = explode('\\', $relative_class);
            if (count($parts) < 2) return;
            
            $pluginFolderRaw = $parts[0];
            // We need to find the actual folder name since $id might have had dashes removed in namespace
            // but for simplicity, let's assume we use lowercase folder names or handle it
            
            // Let's iterate plugins to find the match
            $foundPath = '';
            foreach (scandir(CM_PLUGINS_PATH) as $dir) {
                if ($dir === '.' || $dir === '..') continue;
                if (strtolower(str_replace('-', '', $dir)) === strtolower($pluginFolderRaw)) {
                    $foundPath = CM_PLUGINS_PATH . '/' . $dir;
                    break;
                }
            }
            
            if (!$foundPath) return;
            
            array_shift($parts);
            $file = $foundPath . '/' . implode('/', $parts) . '.php';

            if (file_exists($file)) {
                require $file;
            }
        });
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function loadPlugins() {
        if (!is_dir(CM_PLUGINS_PATH)) return;

        $dirs = scandir(CM_PLUGINS_PATH);
        foreach ($dirs as $dir) {
            if ($dir === '.' || $dir === '..') continue;
            
            $pluginPath = CM_PLUGINS_PATH . '/' . $dir;
            if (is_dir($pluginPath) && file_exists($pluginPath . '/plugin.json')) {
                $config = json_decode(file_get_contents($pluginPath . '/plugin.json'), true);
                $config['path'] = $pluginPath;
                $config['id'] = $dir;
                $this->plugins[$dir] = $config;

                // Auto-run install if Schema.php exists
                $this->checkInstallation($dir);

                // Load Hooks if hooks.php exists
                $hooksFile = $pluginPath . '/hooks.php';
                if (file_exists($hooksFile)) {
                    require_once $hooksFile;
                }
            }
        }
    }

    private function checkInstallation($id) {
        $db = Database::getInstance();
        if (!$db) return; // Skip if database is not ready

        $schemaFile = $this->plugins[$id]['path'] . '/Schema.php';
        if (file_exists($schemaFile)) {
            require_once $schemaFile;
            $className = "Plugins\\" . str_replace('-', '', $id) . "\\Schema";
            if (class_exists($className)) {
                $schema = new $className();
                $schema->install();
            }
        }
    }

    public function registerRoutes($r) {
        foreach ($this->plugins as $id => $plugin) {
            $routeFile = $plugin['path'] . '/routes.php';
            if (file_exists($routeFile)) {
                $pluginRoutes = require $routeFile;
                if (is_callable($pluginRoutes)) {
                    $pluginRoutes($r);
                }
            }
        }
    }

    public function getAdminMenuItems() {
        $items = [];
        foreach ($this->plugins as $id => $plugin) {
            // Plugins can have an AdminMenu.php that returns an array of items
            $menuFile = $plugin['path'] . '/AdminMenu.php';
            if (file_exists($menuFile)) {
                $pluginMenu = require $menuFile;
                if (is_array($pluginMenu)) {
                    $items = array_merge($items, $pluginMenu);
                }
            }
        }
        return $items;
    }

    public function getPlugins() {
        return $this->plugins;
    }
}
