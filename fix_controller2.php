<?php
$content = file_get_contents('c:\Users\hades\OneDrive\Desktop\작업폴더\plt_project\app\Controllers\VendorController.php');

$search = <<<EOT
    private function buildQuoteModules(\$quote) {
        // BOM 계산 로직 (동적 산출)
        require_once __DIR__ . '/../Services/SehwaPriceCalculator.php';
        
        \$indep = intval(\$quote['rack_indep']);
EOT;

$replace = <<<EOT
    private function buildQuoteModules(\$quote) {
        // BOM 계산 로직 (동적 산출)
        require_once __DIR__ . '/../Services/SehwaPriceCalculator.php';
        
        // 견적 당시의 단가표(Rule)를 주입하여 과거 단가 고정
        \$vendorId = \$quote['vendor_user_id'] ?? 1;
        \$ruleId = \$quote['pricing_rule_id'] ?? null;
        \App\Services\SehwaPriceCalculator::setRuleContext(\$vendorId, \$ruleId);

        \$indep = intval(\$quote['rack_indep']);
EOT;

if (strpos($content, $search) !== false) {
    $content = str_replace($search, $replace, $content);
    file_put_contents('c:\Users\hades\OneDrive\Desktop\작업폴더\plt_project\app\Controllers\VendorController.php', $content);
    echo "Fixed buildQuoteModules.\n";
} else {
    echo "Target not found for buildQuoteModules.\n";
}
