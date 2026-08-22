<?php
$data = ['siteConfig' => ['logo_text' => 'Hello']];
function test($data) {
    global $siteConfig;
    extract($data);
    var_dump($siteConfig);
}
test($data);
