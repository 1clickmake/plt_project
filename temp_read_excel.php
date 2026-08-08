<?php
require __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$filePath = 'C:\Users\hades\OneDrive\Desktop\파랫트랙 자료\2026년 7월 세화 단가자료 (성진시스템) (성진 규격, 영진분체 도장비 포함).xlsx';

try {
    $spreadsheet = IOFactory::load($filePath);
    $worksheet = $spreadsheet->getActiveSheet();
    $rows = $worksheet->toArray();
    
    // Print first 20 rows
    for ($i = 0; $i < min(20, count($rows)); $i++) {
        echo "Row " . str_pad($i + 1, 2, ' ', STR_PAD_LEFT) . ": | ";
        foreach ($rows[$i] as $cell) {
            echo str_pad(mb_substr((string)$cell, 0, 20), 20) . " | ";
        }
        echo "\n";
    }
} catch (Exception $e) {
    echo "Error loading file: " . $e->getMessage();
}
