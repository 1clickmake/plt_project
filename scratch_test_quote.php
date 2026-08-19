<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/app/Services/SehwaPriceCalculator.php';
$quote = [
    'rack_indep' => 3,
    'rack_conn' => 10,
    'rack_small_conn' => 3,
    'rack_levels' => 3, // 3단
    'pallet_w' => 1100,
    'pallet_d' => 1100,
    'rack_height' => 5500,
    'fork_direction' => 'W', // or D
    'rack_tie_per_level' => 4,
    'beam_thickness' => 125,
];

$indep = intval($quote['rack_indep']);
$conn = intval($quote['rack_conn']);
$small = intval($quote['rack_small_conn']);
$levels = intval($quote['rack_levels']) ?: 2;
$beamLevels = max(1, $levels - 1); 

$rackH = 5500;
$w = 1100;
$d = 1100;
$entryW = $w;
$nonEntryW = $d;
$beamL = ($entryW * 2) + 385; // 1100*2 + 385 = 2585
$depth = $nonEntryW - 100; // 1100 - 100 = 1000

$totalFrames = ($indep * 2) + $conn + $small; // 6 + 10 + 3 = 19
$totalColumns = $totalFrames * 2; // 38
$totalBeams = ($indep + $conn) * $beamLevels * 2; // 13 * 2 * 2 = 52
$totalSmallBeams = $small * $beamLevels * 2; // 3 * 2 * 2 = 12

// BOM
$bom = [];
$totalWeight = 0;

$col = \App\Services\SehwaPriceCalculator::calcColumn(['type' => '85바', 'height' => $rackH, 'thickness' => 1.8, 'qty' => $totalColumns]);
$bom[] = $col;

$base = \App\Services\SehwaPriceCalculator::calcColumnBase(['w' => 173, 'd' => 101, 'thickness' => 4, 'qty' => $totalColumns]);
$bom[] = $base;

$tiePerLevel = 4;
$totalTieBeams = ($totalBeams / 2) * $tiePerLevel; // 26 * 4 = 104
$totalSmallTieBeams = ($totalSmallBeams / 2) * 2; // 6 * 2 = 12
$tie = \App\Services\SehwaPriceCalculator::calcTieBeam(['size' => '75*30', 'length' => $depth + 42, 'thickness' => 1.5, 'material' => '아연도', 'depth' => $depth, 'qty' => $totalTieBeams + $totalSmallTieBeams]);
$bom[] = $tie;

$loadBeam = \App\Services\SehwaPriceCalculator::calcLoadBeam(['size' => '125*50', 'length' => 2585, 'thickness' => 1.6, 'qty' => $totalBeams]);
$bom[] = $loadBeam;

$smallLoadBeam = \App\Services\SehwaPriceCalculator::calcLoadBeam(['size' => '125*50', 'length' => 1385, 'thickness' => 1.6, 'qty' => $totalSmallBeams]);
$bom[] = $smallLoadBeam;

$tieBeam2585 = \App\Services\SehwaPriceCalculator::calcTieBeam(['size' => '125용', 'length' => 2585, 'thickness' => 0, 'material' => '볼트/너트', 'qty' => $totalBeams]);
// Wait, is there a 2585 tie beam? 
// Actually, let's just use the exact logic from VendorController

// Let's copy the VendorController logic directly to match exactly.
$bracingQty = \App\Services\SehwaPriceCalculator::calcBracingQty($rackH, $depth, $totalFrames);
$bracing = \App\Services\SehwaPriceCalculator::calcBracing([
    'w' => 25, 'd' => 15, 'thickness' => 1.5, 'material' => '아연도',
    'height' => $rackH, 'depth' => $depth, 'qty' => $bracingQty
]);
$bom[] = $bracing;

$boltQty = $totalColumns * 4;
$bolt = \App\Services\SehwaPriceCalculator::calcAccessory(['name' => 'Wedge Anchor', 'spec' => 'M12*100L', 'qty' => $boltQty]);
$bom[] = $bolt;

$shimQty = $totalColumns * 2;
$shim = \App\Services\SehwaPriceCalculator::calcAccessory(['name' => 'Shim Plate', 'spec' => '2.0T/3.0T', 'qty' => $shimQty]);
$bom[] = $shim;

$pinQty = $totalBeams * 2 + $totalSmallBeams * 2;
$pin = \App\Services\SehwaPriceCalculator::calcAccessory(['name' => 'Safety Pin', 'spec' => 'ø4', 'qty' => $pinQty]);
$bom[] = $pin;

$holderQty = 16;
$holder = \App\Services\SehwaPriceCalculator::calcAccessory(['name' => 'Holder', 'spec' => '1S용', 'qty' => $holderQty]);
// Let's assume holder is mapped to something?
// Actually just looking at total price

echo "=== BOM ===\n";
$sum = 0;
foreach($bom as $item) {
    echo "{$item['name']} ({$item['spec']}): {$item['qty']}ea -> " . number_format($item['price'] * $item['qty']) . " KRW\n";
    $sum += $item['price'] * $item['qty'];
}
$withLoss = $sum * 1.03;
$total = \App\Services\SehwaPriceCalculator::finalAmount($withLoss);

echo "Total Raw: " . number_format($sum) . "\n";
echo "Total With Loss: " . number_format($withLoss) . "\n";
echo "Final Server Price: " . number_format($total) . "\n";
