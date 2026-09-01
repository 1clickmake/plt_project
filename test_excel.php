<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$filePath = 'G:\\내 드라이브\\세화_단가자료_자재별단가_및_원가마진분석.xlsx';
try {
    $spreadsheet = IOFactory::load($filePath);
    $sheetNames = $spreadsheet->getSheetNames();
    echo "Sheets: " . implode(", ", $sheetNames) . "\n";
    
    // Read the first sheet to see its structure
    $worksheet = $spreadsheet->getSheet(0);
    echo "First sheet (" . $sheetNames[0] . ") rows:\n";
    $i = 0;
    foreach ($worksheet->getRowIterator() as $row) {
        $cellIterator = $row->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(FALSE); 
        $rowData = [];
        foreach ($cellIterator as $cell) {
            $rowData[] = $cell->getCalculatedValue();
        }
        echo implode(" | ", $rowData) . "\n";
        if (++$i >= 15) break;
    }
} catch (\Exception $e) {
    echo "Error reading file: " . $e->getMessage();
}
