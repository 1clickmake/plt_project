<?php
namespace App\Controllers;

use App\Core\Database;

class VendorController extends BaseController {
    // ... settings() 등 기존 코드 유지 ...

    public function quotePrice($vars) {
        // ... 기존 인증 및 quote 조회 로직 유지 ...
        $quote = ['rack_w'=>2585,'rack_d'=>1000,'rack_h'=>3500,'rack_levels'=>2,'rack_tie_per_level'=>4]; // 예시
        $rackW = $quote['rack_w'] ?? 2585;
        $depth = $quote['rack_d'] ?? 1000;
        $rackH = $quote['rack_h'] ?? 3500;
        
        $bom = [];
        $totalWeight = 0;
        $totalColumns = 6; // N7 예시
        $totalFrames = 3;
        $totalBeams = 8;
        $totalSmallBeams = 0;
        $beamL = $rackW - 95;
        $barType = 125;
        $beamThick = 1.6;

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

            // FIXED Line 244-247: 엑셀 L4 값 직접 사용, W로 추정하던 로직 제거
            // 기존: $tiePerLevel = ($rackW >= 2785 ? 4 : ...) 
            // 수정: quote에서 tie_per_level 직접 받음 (엑셀과 동일)
            $tiePerLevel = $quote['rack_tie_per_level'] ?? 4; // 엑셀 L4 = 단당 타이빔 수
            $totalTieBeams = ($totalBeams > 0) ? ($totalBeams / 2) * $tiePerLevel : 0;
            $totalSmallTieBeams = 0;
            
            $tie = \App\Services\SehwaPriceCalculator::calcTieBeam([
                'size' => '75*30', 'length' => $depth + 42, 'thickness' => 1.5, 'material' => '아연도', 'depth' => $depth, 'qty' => $totalTieBeams
            ]);
            $bom[] = $tie;
            $totalWeight += $tie['weight'] * $totalTieBeams;

            // 브레싱
            $straightCount = $totalFrames * 2;
            // FIXED Line 261: ROUNDDOWN -> floor() 유지 (이미 올바름)
            $x6 = floor(($rackH - 400) / 750); // 엑셀 ROUNDDOWN((H4-400)/750,0)
            $diagonalCount = $totalFrames * $x6; 
            
            $strB = \App\Services\SehwaPriceCalculator::calcBracing([
                'type' => '직선', 'length' => $depth - 75, 'thickness' => 1.5, 'qty' => $straightCount
            ]);
            $bom[] = $strB;
            $totalWeight += $strB['weight'] * $straightCount;

            // FIXED Line 273: SQRT 전체 정밀도 유지 (round 제거)
            $diaLength = sqrt(pow($depth - 125, 2) + pow(750, 2)) + 50;
            
            $diaB = \App\Services\SehwaPriceCalculator::calcBracing([
                'type' => '경사', 'length' => $diaLength, 'thickness' => 1.5, 'qty' => $diagonalCount
            ]);
            $bom[] = $diaB;
            $totalWeight += $diaB['weight'] * $diagonalCount;

            // 부자재 - FIXED Line 282-285: 볼트 65L 수량 엑셀 공식으로 수정
            $bom[] = \App\Services\SehwaPriceCalculator::getFixedPart('FIX-001', $totalColumns); // 부싱
            $bom[] = \App\Services\SehwaPriceCalculator::getFixedPart('FIX-002', $totalColumns * 2); // 라이너
            $bom[] = \App\Services\SehwaPriceCalculator::getFixedPart('FIX-004', $totalColumns * 2); // 볼트 25L
            
            // 기존: $totalColumns * 4.5 (고정값) -> 1,730원 오차 원인!
            // 수정: 엑셀 N18 = N7/2*(X6+3)
            $bolt65Qty = (int) floor($totalColumns / 2 * ($x6 + 3)); // FIXED
            $bom[] = \App\Services\SehwaPriceCalculator::getFixedPart('FIX-005', $bolt65Qty); // 볼트 65L
            
            $bom[] = \App\Services\SehwaPriceCalculator::getFixedPart('FIX-006', $totalColumns); // 앙카
        }
        // ... 나머지 로드빔, Loss 등 동일 ...
    }
}
