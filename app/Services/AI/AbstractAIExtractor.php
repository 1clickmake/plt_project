<?php

namespace App\Services\AI;

use PhpOffice\PhpSpreadsheet\IOFactory;

abstract class AbstractAIExtractor implements AIExtractorInterface
{
    /**
     * 엑셀 파일을 읽어 텍스트(CSV 형태)로 변환
     */
    public function extractTextFromExcel(string $filePath): string
    {
        $spreadsheet = IOFactory::load($filePath);
        $text = "";

        foreach ($spreadsheet->getAllSheets() as $sheet) {
            $sheetName = $sheet->getTitle();
            $text .= "--- SHEET: {$sheetName} ---\n";
            
            // 너무 큰 엑셀 데이터 방지 (토큰 한도 최적화)
            $highestRow = min($sheet->getHighestDataRow(), 150);
            $highestColumn = $sheet->getHighestDataColumn();
            $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);
            $highestColumnIndex = min($highestColumnIndex, 40); // AK까지

            for ($row = 1; $row <= $highestRow; $row++) {
                $rowData = [];
                for ($col = 1; $col <= $highestColumnIndex; $col++) {
                    $cell = $sheet->getCell([$col, $row]);
                    $val = $cell->getCalculatedValue();
                    if ($val !== null && $val !== '') {
                        $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                        if ($cell->isFormula()) {
                            $formula = $cell->getValue();
                            $rowData[] = "{$colLetter}{$row}: {$val} (Formula: {$formula})";
                        } else {
                            $rowData[] = "{$colLetter}{$row}: {$val}";
                        }
                    }
                }
                if (!empty($rowData)) {
                    $text .= implode(" | ", $rowData) . "\n";
                }
            }
            $text .= "\n\n";
        }
        return $text;
    }

    /**
     * AI 모델 고유의 JSON 추출 로직 (자식 클래스에서 구현)
     */
    abstract public function extractPricingFromJson(string $excelText): array;
}
