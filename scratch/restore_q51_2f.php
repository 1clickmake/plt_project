<?php
require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();
$db = \App\Core\Database::getInstance();

$stmt = $db->prepare("SELECT canvas_data FROM quote_requests WHERE id = 51");
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$cd = json_decode($row['canvas_data'], true);

// 1층 데이터
$f1_points = $cd['points'];
$f1_racks = $cd['racks'];
$f1_obstacles = $cd['obstacles'];
$f1_scale = $cd['currentScale'];

// 2층 치수: 20000mm (가로) x 22000mm (세로)
$f2_w = 20000;
$f2_h = 22000;
$f2_scale = 0.024; // 적정 스케일

$f2_originX = 480;
$f2_originY = 80;

$f2_points = [
    ['x' => $f2_originX, 'y' => $f2_originY],
    ['x' => $f2_originX + ($f2_w * $f2_scale), 'y' => $f2_originY],
    ['x' => $f2_originX + ($f2_w * $f2_scale), 'y' => $f2_originY + ($f2_h * $f2_scale)],
    ['x' => $f2_originX, 'y' => $f2_originY + ($f2_h * $f2_scale)],
    ['x' => $f2_originX, 'y' => $f2_originY]
];

$beamLen = 2585;
$smallBeamLen = 1385;
$total7BaysPx = ((2 * 85) + (7 * $beamLen)) * $f2_scale;

// 2층 랙 구성 (1번 이미지와 100% 동일)
// 1) 상단 단식 랙 (1독립 + 6연결 = 7베이)
$r1 = [
    'x' => $f2_originX + (200 * $f2_scale),
    'y' => $f2_originY + (1000 * $f2_scale / 2) + 15,
    'isHoriz' => true,
    'dir' => 1,
    'independent' => 1,
    'connected' => 6,
    'smallConnected' => 0,
    'beamLength' => $beamLen,
    'smallBeamLength' => $smallBeamLen,
    'totalLengthPx' => $total7BaysPx,
    'rackDepth' => 1000,
    'isDouble' => false,
    'isValid' => true,
    'angle' => 0,
    'bypassBays' => [
        [false, false, false, false, false, false, false],
        []
    ]
];

// 2) 중앙 복식 랙 (1독립 + 6연결 = 14베이, 4번째 칸 바이패스 2대)
$r2 = [
    'x' => $f2_originX + (200 * $f2_scale),
    'y' => $f2_originY + ($f2_h * $f2_scale * 0.52),
    'isHoriz' => true,
    'dir' => 1,
    'independent' => 1,
    'connected' => 6,
    'smallConnected' => 0,
    'beamLength' => $beamLen,
    'smallBeamLength' => $smallBeamLen,
    'totalLengthPx' => $total7BaysPx,
    'rackDepth' => 1000,
    'isDouble' => true,
    'isValid' => true,
    'angle' => 0,
    'bypassBays' => [
        [false, false, false, true, false, false, false],
        [false, false, false, true, false, false, false]
    ]
];

$f2_racks = [$r1, $r2];

// 2층 좌측 출입문
$f2_doorY = $f2_originY + ($f2_h * $f2_scale * 0.68);
$f2_obstacles = [
    [
        'type' => 'door',
        'name' => '출입문1',
        'x' => $f2_originX,
        'y' => $f2_doorY,
        'angle' => M_PI / 2,
        'length' => 3500,
        'edgeIndex' => 3,
        'ratioOnEdge' => 0.68,
        'visualDistFromStart' => 3500 * $f2_scale,
        'visualEdgeLength' => $f2_h * $f2_scale
    ]
];

$floors = [
    [
        'id' => 1,
        'index' => 0,
        'name' => '1층 (기본 창고)',
        'points' => $f1_points,
        'obstacles' => $f1_obstacles,
        'racks' => $f1_racks,
        'currentScale' => $f1_scale,
        'edgeLengths' => [30000, 30000, 30000, 30000],
        'userEnteredEdges' => [0, 1, 2],
        'bays' => 75,
        'indep' => 9,
        'conn' => 58,
        'smallConn' => 0,
        'bypass' => 8,
        'holders' => 108,
        'pallets' => 434,
        'spec' => '2585×1000×4000 (2S 3단)',
        'palletSpec' => $cd['floors'][0]['palletSpec'] ?? null,
        'image_path' => $cd['floors'][0]['image_path'] ?? null
    ],
    [
        'id' => 1788686610182,
        'index' => 1,
        'name' => '2층 (제2창고)',
        'points' => $f2_points,
        'obstacles' => $f2_obstacles,
        'racks' => $f2_racks,
        'currentScale' => $f2_scale,
        'edgeLengths' => [20000, 22000, 20000, 22000],
        'userEnteredEdges' => [0, 1, 2],
        'bays' => 21,
        'indep' => 3,
        'conn' => 16,
        'smallConn' => 0,
        'bypass' => 2,
        'holders' => 30,
        'pallets' => 122,
        'spec' => '2585×1000×4000 (2S 3단)',
        'palletSpec' => $cd['floors'][1]['palletSpec'] ?? null,
        'image_path' => $cd['floors'][1]['image_path'] ?? null
    ]
];

$cd['floors'] = $floors;
$cd['canvasFloors'] = $floors;

$newJson = json_encode($cd, JSON_UNESCAPED_UNICODE);

$updateStmt = $db->prepare("UPDATE quote_requests SET canvas_data = ? WHERE id = 51");
$updateStmt->execute([$newJson]);

echo "✅ Quote 51 2층 도면 복원 데이터 업데이트 완료!\n";
