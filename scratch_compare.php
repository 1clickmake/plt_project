<?php
require 'vendor/autoload.php';
require 'app/Core/Database.php';
require 'app/Services/SehwaPriceCalculator.php';

$_ENV['DB_HOST'] = 'localhost';
$_ENV['DB_NAME'] = 'asamiya';
$_ENV['DB_USER'] = 'root';
$_ENV['DB_PASS'] = '';

$quote = [
    'rack_height' => '5500',
    'pallet_w' => 1000,
    'pallet_d' => 1000, // Depth usually derived from fork direction, let's assume rack depth is 1000
    'rack_levels' => 3,
    'beam_thickness' => 125,
    'fork_direction' => 'W방향 (정상)',
];

$indep = 1;
$conn = 2;
$small = 0;

$levels = 3;
$beamLevels = 2; // max(1, 3 - 1)
$barType = 125;
$beamThick = 1.6;

$rackH = 5500;
$depth = 1000;
$beamL = 2585;

$totalFrames = ($indep * 2) + $conn + $small; // 4
$totalColumns = $totalFrames * 2; // 8
$totalBeams = ($indep + $conn) * $beamLevels * 2; // 3 * 2 * 2 = 12
$totalSmallBeams = 0;

$bom = [];
$totalWeight = 0;

if ($totalColumns > 0) {
    $col = \App\Services\SehwaPriceCalculator::calcColumn([
        'type' => '85바', 'height' => $rackH, 'thickness' => 1.8, 'qty' => $totalColumns
    ]);
    $bom[] = $col;
    $totalWeight += $col['weight'] * $totalColumns;

    $base = \App\Services\SehwaPriceCalculator::calcColumnBase([
        'w' => 173, 'd' => 101, 'thickness' => 4, 'qty' => $totalColumns
    ]);
    $bom[] = $base;
    $totalWeight += $base['weight'] * $totalColumns;

    $tieCountPerFrame = 4;
    $tie = \App\Services\SehwaPriceCalculator::calcTieBeam([
        'size' => '75*30', 'length' => $depth + 42, 'thickness' => 1.5, 'material' => '아연도', 'depth' => $depth, 'qty' => $totalFrames * $tieCountPerFrame
    ]);
    // wait, I fixed tie beams in VendorController!
    // My fix:
    $tiePerLevel = 4;
    $totalTieBeams = ($totalBeams / 2) * $tiePerLevel; // (12/2)*4 = 24
    $tie = \App\Services\SehwaPriceCalculator::calcTieBeam([
        'size' => '75*30', 'length' => 1042, 'thickness' => 1.5, 'material' => '아연도', 'depth' => $depth, 'qty' => 24
    ]);
    $bom[] = $tie;
    $totalWeight += $tie['weight'] * 24;

    $straightCount = $totalFrames * 2; // 8
    $diagonalCount = $totalFrames * 6; // 24
    $strB = \App\Services\SehwaPriceCalculator::calcBracing([
        'type' => '직선', 'length' => 925, 'thickness' => 1.5, 'qty' => $straightCount
    ]);
    $bom[] = $strB;
    $totalWeight += $strB['weight'] * $straightCount;

    $diaB = \App\Services\SehwaPriceCalculator::calcBracing([
        'type' => '경사', 'length' => 1202, 'thickness' => 1.5, 'qty' => $diagonalCount
    ]);
    $bom[] = $diaB;
    $totalWeight += $diaB['weight'] * $diagonalCount;

    $bom[] = \App\Services\SehwaPriceCalculator::getFixedPart('FIX-001', $totalColumns); // 부싱 8
    $bom[] = \App\Services\SehwaPriceCalculator::getFixedPart('FIX-002', $totalColumns * 2); // 라이너 16
    $bom[] = \App\Services\SehwaPriceCalculator::getFixedPart('FIX-004', $totalColumns * 2); // 볼트 25L 16
    $bom[] = \App\Services\SehwaPriceCalculator::getFixedPart('FIX-005', $totalColumns * 4.5); // 볼트 65L 36
    $bom[] = \App\Services\SehwaPriceCalculator::getFixedPart('FIX-006', $totalColumns); // 앙카 8
}
if ($totalBeams > 0) {
    $beam = \App\Services\SehwaPriceCalculator::calcLoadBeam([
        'length' => 2585, 'bar_type' => 125, 'thickness' => 1.6, 'qty' => $totalBeams
    ]);
    $bom[] = $beam;
    $totalWeight += $beam['weight'] * $totalBeams;

    $bkt = \App\Services\SehwaPriceCalculator::calcLoadBeamBracket([
        'w' => 101, 'd' => 200, 'thickness' => 4.0, 'qty' => $totalBeams * 2
    ]);
    $bom[] = $bkt;
    $totalWeight += $bkt['weight'] * ($totalBeams * 2);

    $bom[] = \App\Services\SehwaPriceCalculator::getFixedPart('FIX-003', $totalBeams * 2); // 안전핀 24
}

$subtotal = 0;
foreach ($bom as &$item) {
    // Excel usually rounds unit price to integer or 10 won
    $item['unit_amount'] = round($item['unit_amount'], 0);
    $item['total'] = $item['unit_amount'] * $item['qty'];
    $subtotal += $item['total'];
    printf("%-20s | Qty: %3d | Unit: %8.2f | Total: %8.2f\n", $item['name'], $item['qty'], $item['unit_amount'], $item['total']);
}

$loss = \App\Services\SehwaPriceCalculator::calcLoss($totalWeight);
$loss = round($loss, 0);
$subtotal += $loss;
printf("%-20s | %s | %s | Total: %8.2f\n", "Loss (3%)", "-", "-", $loss);

$final = \App\Services\SehwaPriceCalculator::finalAmount($subtotal, 10); 
echo "Subtotal: " . round($subtotal, 2) . "\n";
echo "Final (+10% nego): " . $final . "\n";
