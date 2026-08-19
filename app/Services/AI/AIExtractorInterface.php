<?php

namespace App\Services\AI;

interface AIExtractorInterface
{
    /**
     * 엑셀 파일을 읽어 텍스트로 변환
     * 
     * @param string $filePath
     * @return string
     */
    public function extractTextFromExcel(string $filePath): string;

    /**
     * AI 모델을 호출하여 단가 JSON 추출
     * 
     * @param string $excelText
     * @return array
     */
    public function extractPricingFromJson(string $excelText): array;
}
