<?php
$dir = 'c:/Users/hades/OneDrive/Desktop/작업폴더/plt_project/views/vendor/';
$files = glob($dir . '*.php');
foreach ($files as $file) {
    $content = file_get_contents($file);
    $newContent = str_replace(['<!-- <!--', '--> -->'], ['<!--', '-->'], $content);
    if ($content !== $newContent) {
        file_put_contents($file, $newContent);
        echo 'Fixed: ' . basename($file) . PHP_EOL;
    }
}
echo "Done\n";
