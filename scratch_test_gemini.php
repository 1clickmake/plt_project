<?php
require 'vendor/autoload.php';
require 'app/Core/Database.php';
require 'app/Services/GeminiService.php';

try {
    $_ENV['DB_HOST'] = 'localhost';
    $_ENV['DB_NAME'] = 'asamiya';
    $_ENV['DB_USER'] = 'root';
    $_ENV['DB_PASS'] = '';
    
    $service = new \App\Services\GeminiService();
    $excelPath = __DIR__ . '/public/data/excel/price_admin_1787056204.xlsx';
    echo "Extracting text from excel...\n";
    $text = $service->extractTextFromExcel($excelPath);
    echo "Calling Gemini API...\n";
    $json = $service->extractPricingFromJson($text);
    echo "SUCCESS!\n";
    print_r($json);
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
