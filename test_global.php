<?php
$data = ['siteConfig' => ['logo_text' => 'Hello']];
function test($data) {
    extract($data);
    global $siteConfig;
    var_dump($siteConfig);
}
test($data);
