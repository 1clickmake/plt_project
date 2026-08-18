<?php
namespace App\Services;

use App\Core\Database;
use PDO;

/**
 * 벤더 단가 계산 로직 엔진
 * - AI가 추출하여 DB에 저장한 엑셀 단가(vendor_pricing_rules)를 동적으로 로드하여 계산.
 * - 엑셀과 동일한 결과 도출을 위해 중량은 소수점 3자리, 단가는 정수로 반올림.
 */
class SehwaPriceCalculator
{
    private static $config = null;

    // 데이터베이스에서 최신 추출 단가표 JSON을 로드
    public static function loadConfig(int $vendorId = 1)
    {
        if (self::$config !== null) return;
        
        try {
            $db = Database::getInstance();
            if (!$db) {
                // CLI나 특정 환경 대비
                $db = new PDO("mysql:host=localhost;dbname=asamiya;charset=utf8mb4", "root", "");
            }
            $stmt = $db->prepare("SELECT pricing_data FROM vendor_pricing_rules WHERE vendor_id = :vid ORDER BY created_at DESC LIMIT 1");
            $stmt->execute(['vid' => $vendorId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($row && !empty($row['pricing_data'])) {
                self::$config = json_decode($row['pricing_data'], true);
            } else {
                self::$config = [];
            }
        } catch (\Exception $e) {
            self::$config = [];
        }
    }

    // 기본 강재단가
    public static function getBaseSteelPrice(): int
    {
        self::loadConfig();
        return self::$config['base_steel_price'] ?? 980;
    }

    // 두께별 가산 (원/kg)
    public static function getThicknessAddon(float $t): int
    {
        self::loadConfig();
        $addons = self::$config['thickness_addon'] ?? [];
        if ($t <= 1.4) return $addons['t_le_1_4'] ?? 40;
        if ($t <= 1.6) return $addons['t_gt_1_4_le_1_6'] ?? 20;
        if ($t < 2.0)  return $addons['t_gt_1_6_lt_2_0'] ?? 10;
        return $addons['t_ge_2_0'] ?? 0;
    }

    // 강재 단가 결정 (원/kg)
    public static function getSteelUnitPrice(float $t, bool $isGalvanized = false): int
    {
        self::loadConfig();
        $price = self::getBaseSteelPrice() + self::getThicknessAddon($t);

        if ($isGalvanized) {
            $price += self::$config['galvanized_addon'] ?? 120; // 아연도 가산
            if ($t < 1.0) {
                $price += self::$config['galvanized_thin_extra'] ?? 20;
            }
        }
        return $price;
    }

    // --------------------------------------------------
    // 1. 기둥 (85바 / 95바)
    // --------------------------------------------------
    public static function calcColumn(array $params): array
    {
        self::loadConfig();
        
        $type      = $params['type'] ?? '85바';   // 85바 | 95바
        $height    = $params['height'];            // mm
        $thickness = $params['thickness'];         // t
        $qty       = $params['qty'] ?? 1;

        $factor = ($type === '85바') ? 243 : 265;
        $weight = round($factor * $height * $thickness * 7.85 / 1000000, 3); // kg (소수점 3자리 반올림)

        $steelPrice = self::getSteelUnitPrice($thickness);

        $paintT = max($thickness, 1.8);
        $paintWeight = round($factor * $height * $paintT * 7.85 / 1000000, 3);
        $paintPerKg = self::$config['process_fees']['column_paint_per_kg'] ?? 150;
        $paintCost = $paintPerKg * $paintWeight;

        if ($type === '85바') {
            $under = self::$config['process_fees']['column_85_under_8800'] ?? 1100;
            $over = self::$config['process_fees']['column_85_over_8800'] ?? 1300;
            $processPerM = ($height < 8800) ? $under : $over;
        } else {
            $under = self::$config['process_fees']['column_95_under_8800'] ?? 1200;
            $over = self::$config['process_fees']['column_95_over_8800'] ?? 1800;
            $processPerM = ($height < 8800) ? $under : $over;
        }
        $processCost = ($height / 1000) * $processPerM;

        $unitAmount = round(($weight * $steelPrice) + $paintCost + $processCost, 0); // 원단위 반올림
        $total = $unitAmount * $qty;

        return [
            'name'        => "{$type} 기둥",
            'spec'        => "H{$height} x {$thickness}t",
            'weight'      => $weight,
            'qty'         => $qty,
            'steel_price' => $steelPrice,
            'paint'       => round($paintCost, 0),
            'process'     => round($processCost, 0),
            'unit_amount' => $unitAmount,
            'total'       => $total,
        ];
    }

    // --------------------------------------------------
    // 2. 기둥 베이스 (아연도)
    // --------------------------------------------------
    public static function calcColumnBase(array $params): array
    {
        self::loadConfig();
        
        $w = $params['w'] ?? 173;      // mm
        $d = $params['d'] ?? 101;
        $t = $params['thickness'] ?? 4;
        $qty = $params['qty'] ?? 1;

        $weight = round($w * $d * $t * 7.85 / 1000000, 3);
        
        $baseSteelKg = self::$config['process_fees']['column_base_steel_per_kg'] ?? 1100;
        $baseEa = self::$config['process_fees']['column_base_ea'] ?? 300;
        
        $unitAmount = round(($weight * $baseSteelKg) + $baseEa, 0);

        return [
            'name'        => "기둥 베이스(아연도)",
            'spec'        => "{$w}x{$d}x{$t}t",
            'weight'      => $weight,
            'qty'         => $qty,
            'unit_amount' => $unitAmount,
            'total'       => $unitAmount * $qty,
        ];
    }

    // --------------------------------------------------
    // 2-1. 로드빔 B.K.T (브라켓)
    // --------------------------------------------------
    public static function calcLoadBeamBracket(array $params): array
    {
        self::loadConfig();
        
        $w = $params['w'] ?? 101;      // mm
        $d = $params['d'] ?? 200;
        $t = $params['thickness'] ?? 4.0;
        $qty = $params['qty'] ?? 1;

        $weight = round($w * $d * $t * 7.85 / 1000000, 3);
        
        $bktSteelKg = self::$config['process_fees']['load_beam_bkt_steel_per_kg'] ?? 1020;
        $bktEa = self::$config['process_fees']['load_beam_bkt_ea'] ?? 350;
        
        $unitAmount = round(($weight * $bktSteelKg) + $bktEa, 0);   

        return [
            'name'        => "로드빔 B.K.T",
            'spec'        => "{$w}x{$d}x{$t}t",
            'weight'      => $weight,
            'qty'         => $qty,
            'unit_amount' => $unitAmount,
            'total'       => $unitAmount * $qty,
        ];
    }

    // --------------------------------------------------
    // 3. 일반 로드빔
    // --------------------------------------------------
    public static function calcLoadBeam(array $params): array
    {
        self::loadConfig();
        
        $rackW     = $params['length'];            // mm 
        $cutLength = $rackW - 95;                  // 실제 자재 절단 길이
        $barType   = $params['bar_type'] ?? 125;   // 75, 100, 125, 150
        $thickness = $params['thickness'];
        $qty       = $params['qty'] ?? 1;

        $lookupArr = self::$config['beam_lookup'] ?? [
            ['bar' => 75, 'section' => 158, 'process' => 1100],
            ['bar' => 100, 'section' => 210, 'process' => 1400],
            ['bar' => 125, 'section' => 236, 'process' => 1500],
            ['bar' => 150, 'section' => 261, 'process' => 1700],
        ];
        
        $section = 236;
        $processBase = 1500;
        foreach ($lookupArr as $item) {
            if ($item['bar'] == $barType) {
                $section = $item['section'];
                $processBase = $item['process'];
                break;
            }
        }

        $weight = round($cutLength * $section * 2 * $thickness * 7.85 / 1000000, 3);
        $steelPrice = self::getSteelUnitPrice($thickness);

        // load_beam_process_kg 가 빈값이거나 누락된 경우 기본값 160
        $processKg = self::$config['process_fees']['load_beam_process_kg'] ?? 160; 
        if (empty($processKg)) $processKg = 160;

        $processPerEa = ($cutLength < 2990) ? $processBase : ($processBase + 500);
        $extraPerMm = self::$config['process_fees']['load_beam_extra_per_mm'] ?? 1.1;
        $extra = ($cutLength + 100) * $extraPerMm; 

        $unitAmount = round(($weight * $steelPrice)
                    + ($weight * $processKg)
                    + $processPerEa
                    + $extra, 0);

        return [
            'name'        => "일반 로드빔 {$barType}바",
            'spec'        => "L{$rackW} x {$barType}t",
            'weight'      => $weight,
            'qty'         => $qty,
            'steel_price' => $steelPrice,
            'unit_amount' => $unitAmount,
            'total'       => $unitAmount * $qty,
        ];
    }

    // --------------------------------------------------
    // 4. 타이빔 (일반 - 아연도/HR)
    // --------------------------------------------------
    public static function calcTieBeam(array $params): array
    {
        self::loadConfig();
        
        $size       = $params['size'] ?? '75*30';  // '75*30' | '75*45'
        $length     = $params['length'];           // mm
        $thickness  = $params['thickness'];
        $material   = $params['material'] ?? '아연도'; 
        $depth      = $params['depth'] ?? 1000;    
        $qty        = $params['qty'] ?? 1;

        $factor = ($size === '75*30') ? 135 : 173;
        $weight = round($factor * $length * $thickness * 7.85 / 1000000, 3);

        $isGalv = ($material === '아연도');
        $steelPrice = self::getSteelUnitPrice($thickness, $isGalv);

        $processKg = self::$config['process_fees']['tie_beam_process_kg'] ?? 240;
        $processEa = self::$config['process_fees']['tie_beam_ea'] ?? 400;

        $extra = 0;
        if (!$isGalv) {
            $hrPaint = self::$config['process_fees']['tie_beam_hr_paint_per_m'] ?? 600;
            $extra = ($depth / 1000) * $hrPaint;
        }

        $unitAmount = round(($weight * $steelPrice)
                    + ($weight * $processKg)
                    + $processEa
                    + $extra, 0);

        return [
            'name'        => "타이빔({$material})",
            'spec'        => "{$size} L{$length} x {$thickness}t",
            'weight'      => $weight,
            'qty'         => $qty,
            'steel_price' => $steelPrice,
            'unit_amount' => $unitAmount,
            'total'       => $unitAmount * $qty,
        ];
    }

    // --------------------------------------------------
    // 5. 브레싱 (직선 / 경사)
    // --------------------------------------------------
    public static function calcBracing(array $params): array
    {
        self::loadConfig();
        
        $type      = $params['type'] ?? '직선';    // '직선' | '경사'
        $length    = $params['length'];            // mm
        $thickness = $params['thickness'];
        $material  = $params['material'] ?? '아연도'; 
        $qty       = $params['qty'] ?? 1;

        $weight = round(93 * $length * $thickness * 7.85 / 1000000, 3);

        $isGalv = ($material === '아연도');
        $steelPrice = self::getSteelUnitPrice($thickness, $isGalv);

        $processKg = self::$config['process_fees']['bracing_process_kg'] ?? 240;
        $extra = 0;
        if (!$isGalv) {
            $hrPaint = self::$config['process_fees']['bracing_hr_paint_per_m'] ?? 500;
            $extra = ($length / 1000) * $hrPaint; 
        }

        $unitAmount = round(($weight * $steelPrice) + ($weight * $processKg) + $extra, 0);

        return [
            'name'        => "{$type} 브레싱({$material})",
            'spec'        => "L{$length} x {$thickness}t",
            'weight'      => $weight,
            'qty'         => $qty,
            'unit_amount' => $unitAmount,
            'total'       => $unitAmount * $qty,
        ];
    }

    // --------------------------------------------------
    // 6. 복렬 홀더 (아연도 ㄷ절곡 기준)
    // --------------------------------------------------
    public static function calcHolder(array $params): array
    {
        self::loadConfig();
        
        $h         = $params['length'] ?? 200; // 배면 간격 (H)
        $thickness = $params['thickness'] ?? 2.0; 
        $qty       = $params['qty'] ?? 1;

        // 중량: 118 * (H+70) * t * 7.85 / 10^6
        $weight = round(118 * ($h + 70) * $thickness * 7.85 / 1000000, 3);
        $steelPrice = self::getSteelUnitPrice($thickness, true);

        $processEa = self::$config['holder_fees']['c_holder_ea'] ?? 1200;

        $unitAmount = round(($weight * $steelPrice) + $processEa, 0);

        return [
            'name'        => "홀더 (아연도 ㄷ절곡)",
            'spec'        => "W118 x L{$h} x {$thickness}t",
            'weight'      => $weight,
            'qty'         => $qty,
            'unit_amount' => $unitAmount,
            'total'       => $unitAmount * $qty,
        ];
    }

    // --------------------------------------------------
    // 7. 고정단가 부품
    // --------------------------------------------------
    public static function getFixedPart(string $code, int $qty): array
    {
        self::loadConfig();
        
        $dbParts = self::$config['fixed_parts'] ?? [];
        $p = null;
        foreach ($dbParts as $part) {
            if ($part['code'] === $code) {
                $p = $part;
                break;
            }
        }
        
        // Fallback
        if (!$p) {
            $parts = [
                'FIX-001' => ['name' => '부싱', 'spec' => 'ø15×15-46mm', 'price' => 120],
                'FIX-002' => ['name' => '라이너', 'spec' => '130×100mm (1.2t)', 'price' => 270],
                'FIX-003' => ['name' => '안전핀', 'spec' => '-', 'price' => 110],
                'FIX-004' => ['name' => '볼트·너트', 'spec' => '3/8인치-25L', 'price' => 110],
                'FIX-005' => ['name' => '볼트·너트', 'spec' => '3/8인치-65L', 'price' => 130],
                'FIX-006' => ['name' => '앙카볼트', 'spec' => '1/2인치-100(4인치)', 'price' => 280],
                'FIX-007' => ['name' => 'PP안정좌(일반)', 'spec' => '-', 'price' => 150],
                'FIX-008' => ['name' => 'PP안정좌(오메가)', 'spec' => '-', 'price' => 170],
                'FIX-009' => ['name' => '홀더(복렬 연결용)', 'spec' => '-', 'price' => 1500], 
            ];
            $p = $parts[$code] ?? ['name' => '알수없음', 'spec' => '', 'price' => 0];
        }
        
        $unitAmount = round($p['price'], 0);

        return [
            'name'        => $p['name'],
            'spec'        => $p['spec'],
            'qty'         => $qty,
            'unit_amount' => $unitAmount,
            'total'       => $unitAmount * $qty,
        ];
    }

    // --------------------------------------------------
    // 7. Loss (로스) 계산
    // --------------------------------------------------
    public static function calcLoss(float $totalWeightKg): float
    {
        self::loadConfig();
        $lossRate = self::$config['loss_rate'] ?? 0.03;
        return round($totalWeightKg * $lossRate * self::getBaseSteelPrice(), 0);
    }

    // --------------------------------------------------
    // 8. 최종 금액 (네고 + 반올림)
    // --------------------------------------------------
    public static function finalAmount(float $subtotal, float $negoRate = 10.0): int
    {
        self::loadConfig();
        
        // 파라미터가 10.0 이고 DB에 다른 기본값이 있다면 그것을 사용
        if ($negoRate == 10.0 && isset(self::$config['default_nego_rate'])) {
            $negoRate = self::$config['default_nego_rate'];
        }

        $afterNego = $subtotal * (1 + $negoRate / 100);
        return (int) (round($afterNego / 10) * 10); // 10원 단위 반올림
    }
}
