<?php
require 'vendor/autoload.php';
require 'app/Core/Database.php';

$_ENV['DB_HOST'] = 'localhost';
$_ENV['DB_NAME'] = 'asamiya';
$_ENV['DB_USER'] = 'root';
$_ENV['DB_PASS'] = '';

$quote = [
    'rack_height' => '5500',
    'pallet_w' => 1100,
    'pallet_d' => 1100,
    'rack_levels' => 3,
    'beam_thickness' => 125,
    'fork_direction' => 'W방향 (정상)',
];

$indep = 1;
$conn = 2;
$small = 0;

$levels = intval($quote['rack_levels']) ?: 2;
$beamLevels = max(1, $levels - 1); 

$barType = intval($quote['beam_thickness'] ?? 125); 
$beamThick = 1.6; 

$w = intval($quote['pallet_w']);
$d = intval($quote['pallet_d']);
if($w == 0) $w = 1100;
if($d == 0) $d = 1100;

$rackH = intval(preg_replace('/[^0-9]/', '', $quote['rack_height'] ?? ''));
if ($rackH <= 0) $rackH = ($quote['pallet_h'] + 150) * $levels;

$entryW = (strpos($quote['fork_direction'], 'W') !== false) ? $w : $d;
$nonEntryW = (strpos($quote['fork_direction'], 'W') !== false) ? $d : $w;
$beamL = ($entryW * 2) + 385;
$depth = $nonEntryW - 100;

$totalFrames = ($indep * 2) + $conn + $small; 
$totalColumns = $totalFrames * 2;
$totalBeams = ($indep + $conn) * $beamLevels * 2;
$totalSmallBeams = $small * $beamLevels * 2;
$tieCountPerFrame = ($rackH >= 3000) ? 4 : 2;

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

    $tiePerLevel = ($beamL >= 2785) ? 4 : (($beamL >= 2585) ? 4 : 2); 
    $totalTieBeams = ($totalBeams > 0) ? ($totalBeams / 2) * $tiePerLevel : 0;
    $totalSmallTieBeams = ($totalSmallBeams > 0) ? ($totalSmallBeams / 2) * 2 : 0; 
    
    $tie = \App\Services\SehwaPriceCalculator::calcTieBeam([
        'size' => '75*30', 'length' => $depth + 42, 'thickness' => 1.5, 'material' => '아연도', 'depth' => $depth, 'qty' => $totalTieBeams + $totalSmallTieBeams
    ]);
    $bom[] = $tie;
    $totalWeight += $tie['weight'] * ($totalTieBeams + $totalSmallTieBeams);

    $straightCount = $totalFrames * 2; 
    $diagonalCount = $totalFrames * 6; 
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

    $bom[] = \App\Services\SehwaPriceCalculator::getFixedPart('FIX-001', $totalColumns); 
    $bom[] = \App\Services\SehwaPriceCalculator::getFixedPart('FIX-002', $totalColumns * 2); 
    $bom[] = \App\Services\SehwaPriceCalculator::getFixedPart('FIX-004', $totalColumns * 2); 
    $bom[] = \App\Services\SehwaPriceCalculator::getFixedPart('FIX-005', $totalColumns * 4.5); 
    $bom[] = \App\Services\SehwaPriceCalculator::getFixedPart('FIX-006', $totalColumns); 
}
if ($totalBeams > 0) {
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

    $bom[] = \App\Services\SehwaPriceCalculator::getFixedPart('FIX-003', $totalBeams * 2); 
}

$subtotal = 0;
foreach ($bom as $item) {
    $subtotal += $item['total'];
    printf("%-20s | Qty: %3s | Unit: %8.2f | Total: %8.2f\n", $item['name'], $item['qty'], $item['unit_amount'], $item['total']);
}

$loss = \App\Services\SehwaPriceCalculator::calcLoss($totalWeight);
$subtotal += $loss;
printf("%-20s | %s | %s | Total: %8.2f\n", "Loss (3%)", "-", "-", $loss);

$final = \App\Services\SehwaPriceCalculator::finalAmount($subtotal, 10); 
echo "Subtotal: " . round($subtotal, 2) . "\n";
echo "Final (+10% nego): " . $final . "\n";
