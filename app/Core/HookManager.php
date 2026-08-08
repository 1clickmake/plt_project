<?php

namespace App\Core;

class HookManager
{
    private static $instance = null;
    private $actions = [];

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Register a callback for a hook
     */
    public function addAction($hookName, $callback, $priority = 10)
    {
        $this->actions[$hookName][$priority][] = $callback;
    }

    /**
     * Execute all callbacks for a hook
     */
    public function doAction($hookName, ...$params)
    {
        if (!isset($this->actions[$hookName])) {
            return;
        }

        // Sort by priority
        ksort($this->actions[$hookName]);

        foreach ($this->actions[$hookName] as $priority => $callbacks) {
            foreach ($callbacks as $callback) {
                if (is_callable($callback)) {
                    call_user_func_array($callback, $params);
                }
            }
        }
    }
}
