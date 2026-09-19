<?php
require_once __DIR__ . '/../app/Services/SehwaPriceCalculator.php';
try {
    $h = \App\Services\SehwaPriceCalculator::calcHolder(['length' => 200, 'thickness' => 2.0, 'qty' => 1]);
    $hWeight = $h['weight'];

    $bolt = \App\Services\SehwaPriceCalculator::getFixedPart('FIX-004', 4);
    $lossTotal = \App\Services\SehwaPriceCalculator::calcLoss($hWeight);
    
    $sumRaw = $h['total'] + $bolt['total'] + $lossTotal;
    $hFinal = \App\Services\SehwaPriceCalculator::finalAmount($sumRaw);
    
    echo "Success! Final: $hFinal\n";
} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
