<?php
require_once __DIR__ . '/app/Services/SehwaPriceCalculator.php';

// Mock DB dependency
class MockDB {
    public function prepare() { return new MockStmt(); }
}
class MockStmt {
    public function execute() {}
    public function fetch() { return ['pricing_data' => '{}']; }
}
namespace App\Core {
    class Database {
        public static function getInstance() { return new \MockDB(); }
    }
}

// simulate VendorController logic
$rackH = 3000;
$w = 2585;
$d = 1000;
$indep = 1;
$conn = 1;
$small = 0;
$levels = 2; // => 1 load beam level
$beamLevels = 1;
$barType = 125;
$beamThick = 1.6;

$entryW = $w;
$nonEntryW = $d;
$beamL = ($entryW * 2) + 385;
$depth = $nonEntryW - 100;

$totalFrames = ($indep * 2) + $conn + $small; // 3
$totalColumns = $totalFrames * 2; // 6
$totalBeams = ($indep + $conn) * $beamLevels * 2; // 2 * 1 * 2 = 4
$totalSmallBeams = 0;

$bom = [];
$totalWeight = 0;

// 1. Column
$col = \App\Services\SehwaPriceCalculator::calcColumn([
    'type' => '85바', 'height' => $rackH, 'thickness' => 1.8, 'qty' => $totalColumns
]);
$bom[] = $col;
$totalWeight += $col['weight'] * $totalColumns;

// 2. Base
$base = \App\Services\SehwaPriceCalculator::calcColumnBase([
    'w' => 173, 'd' => 101, 'thickness' => 4, 'qty' => $totalColumns
]);
$bom[] = $base;
$totalWeight += $base['weight'] * $totalColumns;

// 3. Tie
$totalTieBeams = ($totalBeams / 2) * 4; // 2 * 4 = 8
$tie = \App\Services\SehwaPriceCalculator::calcTieBeam([
    'size' => '75*30', 'length' => $depth + 42, 'thickness' => 1.5, 'material' => '아연도', 'depth' => $depth, 'qty' => $totalTieBeams
]);
$bom[] = $tie;
$totalWeight += $tie['weight'] * $totalTieBeams;

// 4. Bracing
$straightCount = $totalFrames * 2; // 6
$x6 = floor(($rackH - 400) / 750); // 3
$diagonalCount = $totalFrames * ($x6 * 2); // 3 * 6 = 18

$strB = \App\Services\SehwaPriceCalculator::calcBracing([
    'type' => '직선', 'length' => $depth - 75, 'thickness' => 1.5, 'qty' => $straightCount
]);
$bom[] = $strB;
$totalWeight += $strB['weight'] * $straightCount;

$diaLength = sqrt(pow($depth - 125, 2) + pow(750, 2)) + 50;
$diaB = \App\Services\SehwaPriceCalculator::calcBracing([
    'type' => '경사', 'length' => $diaLength, 'thickness' => 1.5, 'qty' => $diagonalCount
]);
$bom[] = $diaB;
$totalWeight += $diaB['weight'] * $diagonalCount;

// 5. Load Beam
$beam = \App\Services\SehwaPriceCalculator::calcLoadBeam([
    'length' => $beamL, 'bar_type' => $barType, 'thickness' => $beamThick, 'qty' => $totalBeams
]);
$bom[] = $beam;
$totalWeight += $beam['weight'] * $totalBeams;

$bkt = \App\Services\SehwaPriceCalculator::calcLoadBeamBracket([
    'w' => 101, 'd' => 200, 'thickness' => 4.0, 'qty' => $totalBeams * 2
]);
$bom[] = $bkt;
$totalWeight += $bkt['weight'] * ($totalBeams * 2);

// 6. Loss
$lossTotal = \App\Services\SehwaPriceCalculator::calcLoss($totalWeight);
$bom[] = ['name' => 'Loss', 'total' => $lossTotal];

$sum = 0;
foreach($bom as $b) {
    $sum += $b['total'];
    echo str_pad($b['name'], 20) . " : " . number_format($b['total'], 2) . " / weight: " . ($b['weight']??0) . " qty: " . ($b['qty']??0) . "\n";
}
echo "Total Sum: " . $sum . "\n";
echo "Final Amount: " . \App\Services\SehwaPriceCalculator::finalAmount($sum) . "\n";

