<?php

return function(FastRoute\RouteCollector $r) {
    $r->addRoute('POST', '/admin/ai/query', ['Plugins\adminai\Controllers\AdminAiController', 'query']);
    $r->addRoute('GET', '/admin/ai/download', ['Plugins\adminai\Controllers\AdminAiController', 'download']);
};
