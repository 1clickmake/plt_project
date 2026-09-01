<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$filePath = 'G:\\내 드라이브\\세화_단가자료_자재별단가_및_원가마진분석.xlsx';
try {
    $spreadsheet = IOFactory::load($filePath);
    $worksheet = $spreadsheet->getSheetByName('자재별_단가_리스트');
    echo "Sheet (자재별_단가_리스트) rows:\n";
    $i = 0;
    foreach ($worksheet->getRowIterator() as $row) {
        $cellIterator = $row->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(FALSE); 
        $rowData = [];
        foreach ($cellIterator as $cell) {
            $val = $cell->getCalculatedValue();
            if ($val !== null && $val !== '') {
                $rowData[] = $val;
            } else {
                $rowData[] = "";
            }
        }
        if (implode("", $rowData) !== "") {
            echo implode(" | ", $rowData) . "\n";
            if (++$i >= 20) break;
        }
    }
} catch (\Exception $e) {
    echo "Error reading file: " . $e->getMessage();
}
