<?php

return function(FastRoute\RouteCollector $r) {
    $r->addRoute('GET', '/admin/ai/config', ['Plugins\aimanager\Controllers\AiAdminController', 'index']);
    $r->addRoute('POST', '/admin/ai/config', ['Plugins\aimanager\Controllers\AiAdminController', 'update']);
    $r->addRoute('POST', '/admin/ai/generate', ['Plugins\aimanager\Controllers\AiAdminController', 'generate']);
};
