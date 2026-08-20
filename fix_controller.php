<?php
$content = file_get_contents('c:\Users\hades\OneDrive\Desktop\작업폴더\plt_project\app\Controllers\VendorController.php');

$search = <<<EOT
        $stmt = $db->prepare("SELECT * FROM vendor_settings WHERE user_id = :uid");
        $stmt->execute(['uid' => \$userId]);
        \$settings = \$stmt->fetch() ?: [];

        \$result = \$this->buildQuoteModules(\$quote);
        if(\$w == 0) \$w = 1100;
EOT;

$replace = <<<EOT
        \$stmt = \$db->prepare("SELECT * FROM vendor_settings WHERE user_id = :uid");
        \$stmt->execute(['uid' => \$userId]);
        \$settings = \$stmt->fetch() ?: [];

        \$result = \$this->buildQuoteModules(\$quote);
        \$modules = \$result['modules'];
        \$overallTotal = \$result['overallTotal'];

        \$this->view('vendor/quote_price', [
            'quote' => \$quote, 
            'settings' => \$settings, 
            'modules' => \$modules,
            'overallTotal' => \$overallTotal
        ]);
    }

    private function buildQuoteModules(\$quote) {
        // BOM 계산 로직 (동적 산출)
        require_once __DIR__ . '/../Services/SehwaPriceCalculator.php';
        
        // 견적 당시의 단가표(Rule)를 주입하여 과거 단가 고정
        \$vendorId = \$quote['vendor_user_id'] ?? 1;
        \$ruleId = \$quote['pricing_rule_id'] ?? null;
        \App\Services\SehwaPriceCalculator::setRuleContext(\$vendorId, \$ruleId);

        \$indep = intval(\$quote['rack_indep']);
        \$conn = intval(\$quote['rack_conn']);
        \$small = intval(\$quote['rack_small_conn']);
        
        // 단수 (levels) 계산: 엑셀에서 단수는 '로드빔 단수' (예: 2S 3단이면 로드빔 단수는 2단)
        // DB의 rack_levels가 3이면 실제 로드빔은 2단이 됨.
        \$levels = intval(\$quote['rack_levels']) ?: 2;
        \$beamLevels = max(1, \$levels - 1); 
        
        // 빔 두께와 바(Bar) 타입 
        \$barType = intval(\$quote['beam_thickness'] ?? 125); 
        \$beamThick = 1.6; // 일반 파렛트랙 기본 1.6t
        
        \$w = intval(\$quote['pallet_w']);
        \$d = intval(\$quote['pallet_d']);
        if(\$w == 0) \$w = 1100;
EOT;

if (strpos($content, $search) !== false) {
    $content = str_replace($search, $replace, $content);
    file_put_contents('c:\Users\hades\OneDrive\Desktop\작업폴더\plt_project\app\Controllers\VendorController.php', $content);
    echo "Fixed VendorController.\n";
} else {
    echo "Target not found.\n";
}
