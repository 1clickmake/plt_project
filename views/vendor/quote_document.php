<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>공급사 관리 센터 - 견적 요청 상세</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- External Vendor Dashboard CSS -->
    <link href="/css/vendor_dashboard.css" rel="stylesheet">

<style>
.print-input { border: none; background: transparent; width: 100%; outline: none; }
.print-input.text-right { text-align: right; }
.print-input.text-center { text-align: center; }
.print-input:focus { border-bottom: 1px dashed #999; }
@media print { .print-input { border: none !important; } }
</style>
</head>
<body>

    <!-- 🧭 좌측 네비게이션 사이드바 -->
    <?php include __DIR__ . '/sidebar.php'; ?>

    <!-- 💻 우측 메인 대시보드 영역 -->
    <main class="main-content">
        <div class="top-navbar">
            <div class="navbar-title fw-bold text-light" style="font-size: 1.1rem;">
                SaaS Dashboard &gt; 견적서
            </div>
            <div class="d-flex align-items-center gap-3">
                <div class="btn-group" role="group">
                    <a href="/vendor/quotes/<?= $quote['id'] ?>" class="btn btn-outline-info btn-sm px-3 text-light" style="font-size:0.85rem; border-color: rgba(255,255,255,0.15);">견적상세보기</a>
                    <a href="/vendor/quotes/<?= $quote['id'] ?>/price" class="btn btn-outline-info btn-sm px-3 text-light" style="font-size:0.85rem; border-color: rgba(255,255,255,0.15);">단가확인</a>
                    <a href="/vendor/quotes/<?= $quote['id'] ?>/document" class="btn btn-info btn-sm px-3 fw-bold text-dark" style="font-size:0.85rem;">견적서</a>
                </div>
                <a href="/vendor/quotes" class="btn btn-outline-secondary btn-sm rounded px-3" style="font-size:0.8rem; border-color: rgba(255,255,255,0.15); color:#cbd5e1;">
                    ◀ 목록으로 돌아가기
                </a>
    <div class="user-profile d-flex align-items-center gap-2">
                <?php
                    $dbBtn = \App\Core\Database::getInstance();
                    $stmtBtn = $dbBtn->prepare("SELECT plan FROM users WHERE user_id = ?");
                    $stmtBtn->execute([$_SESSION['user']['user_id']]);
                    $btnPlan = $stmtBtn->fetchColumn();
                    if ($btnPlan !== 'pro'):
                ?>
                <!-- <!-- <a href="/vendor/addon_payment" class="btn btn-outline-warning btn-sm fw-bold px-3 py-1 me-3" style="border-radius: 10px;">
                    <i class="fa-solid fa-bolt"></i> 횟수 충전
                </a> --> -->
                <?php endif; ?>
                    <i class="fa-solid fa-circle-user text-info fs-5"></i>
                    <span class="small font-monospace text-light"><?= htmlspecialchars($user['username'] ?? 'User') ?>님</span>
                </div>
            </div>
        </div>

        <div class="content-body">
            <div class="row g-4"><div class="col-12"><div class="quote-template-wrapper"><div class="sheet">

  <!-- ============ HEADER INFO ============ -->
<?php
// 완료된 견적서인 경우, 처리한 직원의 정보 사용. 아니면 현재 접속한 세션의 직원 정보 사용
$isCompleted = !empty($quote['processed_by']);

$empColor = ($isCompleted && !empty($quote['employee_color'])) 
    ? $quote['employee_color'] 
    : (!empty($_SESSION['employee_color']) ? $_SESSION['employee_color'] : '#b7d99b');

$empName = ($isCompleted && !empty($quote['employee_name'])) 
    ? $quote['employee_name'] . ' ' . ($quote['employee_title'] ?? '')
    : (!empty($_SESSION['employee_name']) ? $_SESSION['employee_name'] . ' ' . ($_SESSION['employee_title'] ?? '') : ($settings['manager_name'] ?? ''));

$empPhone = ($isCompleted && !empty($quote['employee_phone'])) 
    ? $quote['employee_phone'] 
    : (!empty($_SESSION['employee_phone']) ? $_SESSION['employee_phone'] : ($settings['contact_number'] ?? ''));
?>
<?php
// Hex 색상을 RGB로 변환하여 rgba() 형태로 만들기 (html2canvas가 color-mix를 지원하지 않음)
$hex = ltrim($empColor, '#');
if (strlen($hex) == 3) {
    $r = hexdec(str_repeat(substr($hex, 0, 1), 2));
    $g = hexdec(str_repeat(substr($hex, 1, 1), 2));
    $b = hexdec(str_repeat(substr($hex, 2, 1), 2));
} else if (strlen($hex) == 6) {
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));
} else {
    $r = 183; $g = 217; $b = 155; // default fallback (#b7d99b)
}
$rgba30 = "rgba($r, $g, $b, 0.3)";
?>
<style>
.sheet .green-bg,
.sheet .label-cell,
.sheet .quote-table th,
.sheet .sum-row td,
.sheet .project-title,
.sheet .info-title,
.sheet .info-sub-title,
.quote-template-wrapper .caution {
    background-color: <?= $rgba30 ?> !important;
}
.quote-template-wrapper .footer-bar {
    background-color: <?= $empColor ?> !important;
}
@media print {
    .sheet .green-bg,
    .sheet .label-cell,
    .sheet .quote-table th,
    .sheet .sum-row td,
    .sheet .project-title,
    .sheet .info-title,
    .sheet .info-sub-title,
    .quote-template-wrapper .caution {
        background-color: <?= $rgba30 ?> !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    .quote-template-wrapper .footer-bar {
        background-color: <?= $empColor ?> !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
}
</style>
<?php
$conditionType = $quote['condition_type'] ?? 'both';
$palletWeight = intval($quote['pallet_weight'] ?? 1000);
if ($palletWeight == 0) $palletWeight = 1000;
$beamThickness = intval($quote['beam_thickness'] ?? 125);

$indep = intval($quote['rack_indep'] ?? 0);
$conn = intval($quote['rack_conn'] ?? 0);
$bypass = intval($quote['rack_bypass'] ?? 0);
$small = intval($quote['rack_small_conn'] ?? 0);
$levels = intval($quote['rack_levels'] ?? 0);
$stages = 0;

if ($levels <= 0 && !empty($modules)) {
    foreach ($modules as $mod) {
        $text = ($mod['name'] ?? '') . ' ' . ($mod['spec'] ?? '') . ' ' . ($mod['remark'] ?? '');
        if (preg_match('/(\d+)\s*[sS]\s*(\d+)\s*단/iu', $text, $matches)) {
            $stages = intval($matches[1]);
            $levels = intval($matches[2]);
            break;
        }
    }
}

if ($levels <= 0) {
    $levels = 2; // Default fallback
}
if ($stages <= 0) {
    $stages = max(1, $levels - 1);
}

$totalFrames = ($indep * 2) + ($bypass * 2) + $conn + $small;
$totalColumns = $totalFrames * 2;
$linerQty = $totalColumns;
$linerTotal = $linerQty * 500;

$w = intval($quote['pallet_w'] ?? 1100);
$d = intval($quote['pallet_d'] ?? 1100);
if ($w == 0) $w = 1100;
if ($d == 0) $d = 1100;

$entryW = (strpos($quote['fork_direction'] ?? '', 'W') !== false) ? $w : $d;
$beamL = ($entryW * 2) + 385;
$palletsPerCell = ($beamL >= 2585) ? 2 : 1;
$totalPalletsPerLevel = (($indep + $conn + $bypass) * $palletsPerCell) + ($small * 1);
$totalPallets = $totalPalletsPerLevel * $levels;

$sumQty = 0;
$sumPrice = 0;
$sumRawPrice = 0;
if (!empty($modules)) {
    foreach ($modules as $m) {
        if (strpos($m['name'], '파렛트랙') !== false) {
            $sumQty += intval($m['qty']);
        }
        $sumPrice += intval($m['total_price']);
        $sumRawPrice += intval($m['raw_price'] ?? 0) * intval($m['qty']);
    }
}
$supplyPrice = $sumPrice; 
$vat = floor($supplyPrice * 0.1);
$grandTotal = $supplyPrice + $vat;
?>
<?php
$days = array('일요일', '월요일', '화요일', '수요일', '목요일', '금요일', '토요일');
$quoteDate = strtotime($quote['created_at'] ?? 'now');
$quoteDateStr = date('Y년 m월 d일 ', $quoteDate) . $days[date('w', $quoteDate)];
$addrParts = explode(' ', trim($quote['address'] ?? ''));
$region = trim(($addrParts[0] ?? '') . ' ' . ($addrParts[1] ?? ''));
?>
  <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 25px; margin-top: 10px;">
    <!-- 좌측 로고 -->
    <div style="width: 250px;">
      <?php if (!empty($settings['company_logo'])): ?>
        <img src="<?= htmlspecialchars($settings['company_logo']) ?>" alt="Logo" style="max-height: 45px;">
      <?php endif; ?>
    </div>
    <!-- 가운데 타이틀 -->
    <div style="flex-grow: 1; text-align: center;">
      <h1 style="margin: 0; font-size: 36px; font-weight: 900; letter-spacing: 15px; color: #111;">견 적 서</h1>
    </div>
    <!-- 우측 문서번호 -->
    <div style="width: 250px; text-align: right; font-size: 13px; color: #333; font-weight: bold;">
      문서번호: <?= date('Ymd', strtotime($quote['created_at'] ?? 'now')) ?>-<?= str_pad($quote['id'] ?? 0, 4, '0', STR_PAD_LEFT) ?>
    </div>
  </div>

  <table class="header-table">
    <tr>
      <td class="label-cell">상 호</td>
      <td colspan="3"><?= htmlspecialchars($settings['company_name'] ?? '') ?></td>
      <td class="label-cell">견적일자</td>
      <td colspan="3" class="center"><?= $quoteDateStr ?></td>
    </tr>
    <tr>
      <td class="label-cell">본 사</td>
      <td colspan="3"><?= htmlspecialchars($settings['headquarters_address'] ?? '') ?></td>
      <td class="label-cell">업체명</td>
      <td colspan="3"><?= htmlspecialchars($quote['company'] ?? '') ?></td>
    </tr>
    <tr>
      <td class="label-cell">T E L</td>
      <td style="width:150px;"><?= htmlspecialchars($empPhone) ?></td>
      <td class="label-cell" style="width:50px;">F A X</td>
      <td><?= htmlspecialchars($settings['fax_number'] ?? '') ?></td>
      <td class="label-cell">담 당 자</td>
      <td colspan="3"><?= htmlspecialchars($quote['name'] ?? '') ?> 님</td>
    </tr>
    <tr>
      <td class="label-cell">담당자</td>
      <td><?= htmlspecialchars($empName) ?></td>
      <td class="label-cell">E-MAIL</td>
      <td><span class="blue-link"><?= htmlspecialchars($settings['manager_email'] ?? '') ?></span></td>
      <td class="label-cell">E-MAIL</td>
      <td><span class="blue-link"><?= htmlspecialchars($quote['email'] ?? '') ?></span></td>
      <td class="label-cell">T E L</td>
      <td class="center"><?= htmlspecialchars($quote['phone'] ?? '') ?></td>
    </tr>
    <tr>
      <td class="label-cell">공 장</td>
      <td colspan="3"><?= htmlspecialchars($settings['factory_address'] ?? '') ?></td>
      <td class="label-cell">지역</td>
      <td colspan="3" class="red"><?= htmlspecialchars($region) ?></td>
    </tr>
  </table>
  <div class="notice">·아래와 같이 견적 합니다.</div>

  <!-- ============ MAIN QUOTE TABLE ============ -->
  <table class="quote-table">
    <colgroup>
      <col class="no-col">
      <col class="name-col">
      <col class="spec-col">
      <col class="qty-col">
      <col class="unit-col">
      <col class="price-col">
      <col class="amount-col">
      <col class="remark-col">
    </colgroup>
    <tr>
      <th>NO.</th>
      <th>품 명</th>
      <th>규 격</th>
      <th>수 량</th>
      <th>식</th>
      <th>단 가</th>
      <th>금 액</th>
      <th>비 고</th>
    </tr>

    <!-- 신규랙 -->
    <?php if ($conditionType === 'new' || $conditionType === 'both'): ?>
    <tr><td colspan="8" class="section-title-yellow">〈신규랙〉</td></tr>
    <?php if(!empty($modules)): ?>
        <?php foreach($modules as $idx => $mod): ?>
        <tr>
          <td class="center"><?= $idx + 1 ?></td>
          <td class="center"><?= htmlspecialchars($mod['name']) ?></td>
          <td class="center"><?= htmlspecialchars($mod['spec']) ?></td>
          <td class="center"><?= number_format($mod['qty']) ?></td>
          <td class="center">대</td>

          <td class="right">
              <?php if(strpos($mod['name'], '파렛트랙') !== false): ?>
                  <span class="calc-rack-unit rack-new" data-raw="<?= intval($mod['raw_price'] ?? 0) ?>" data-qty="<?= intval($mod['qty']) ?>">
                      <?= number_format($mod['raw_price'] ?? 0) ?>
                  </span>
              <?php else: ?>
                  <input type="text" id="other-unit-new-<?= $idx ?>" class="print-input text-right text-danger calc-other-unit other-new" data-qty="<?= intval($mod['qty']) ?>" value="<?= number_format($mod['unit_price']) ?>">
              <?php endif; ?>
          </td>
          <td class="right">
              <?php if(strpos($mod['name'], '파렛트랙') !== false): ?>
                  <span class="calc-rack-total rack-new-total">
                      <?= number_format(intval($mod['raw_price'] ?? 0) * intval($mod['qty'])) ?>
                  </span>
              <?php else: ?>
                  <span class="calc-other-total other-new-total">
                      <?= number_format($mod['total_price']) ?>
                  </span>
              <?php endif; ?>
          </td>

          <td class="center"><?= htmlspecialchars($mod['remark']) ?></td>
        </tr>
        <?php endforeach; ?>
    <?php endif; ?>

    <?php if(!empty($customItems)): ?>
        <?php foreach($customItems as $cIdx => $cItem): ?>
        <tr>
          <td class="center"><?= (isset($modules) ? count($modules) : 0) + $cIdx + 1 ?></td>
          <td class="center fw-bold text-primary"><?= htmlspecialchars($cItem['name'] ?? '') ?></td>
          <td class="center"><?= htmlspecialchars($cItem['spec'] ?? '') ?></td>
          <td class="center"><?= number_format($cItem['qty'] ?? 1) ?></td>
          <td class="center"><?= htmlspecialchars($cItem['unit'] ?? '개') ?></td>
          <td class="right">
              <input type="text" id="custom-unit-new-<?= $cIdx ?>" class="print-input text-right text-danger calc-other-unit other-new" data-qty="<?= intval($cItem['qty'] ?? 1) ?>" value="<?= number_format($cItem['unit_price'] ?? 0) ?>">
          </td>
          <td class="right">
              <span class="calc-other-total other-new-total">
                  <?= number_format($cItem['total_price'] ?? 0) ?>
              </span>
          </td>
          <td class="center"><?= htmlspecialchars($cItem['remark'] ?? '') ?></td>
        </tr>
        <?php endforeach; ?>
    <?php endif; ?>

    <tr><td colspan="8" class="section-title-red">〈파렛트당 <?= $palletWeight ?>kg, 로드빔 <?= $beamThickness ?>바 <?= $totalPallets ?>plt 적재〉</td></tr>
    <tr>
      <td></td>
      <td class="center">운반비</td>
      <td class="center red"><input type="text" id="transport-region-new" class="print-input text-center text-danger" value="<?= htmlspecialchars($region) ?>"></td>
      <td class="center"><input type="number" id="transport-qty-new" class="print-input text-center calc-bottom-input" value="1"></td>
      <td class="center">대</td>
      <td class="right"><input type="text" id="transport-unit-new" class="print-input text-right calc-bottom-input" value="0"></td>
      <td class="right" id="transport-total-new">0</td>
      <td></td>
    </tr>
    <tr>
      <td></td>
      <td class="center">설치비</td>
      <td class="center"><input type="text" id="install-spec-new" class="print-input text-center text-danger" value=""></td>
      <td class="center"><input type="number" id="install-qty-new" class="print-input text-center calc-bottom-input" value="1"></td>
      <td class="center">식</td>
      <td class="right"><input type="text" id="install-unit-new" class="print-input text-right calc-bottom-input" value="0"></td>
      <td class="right" id="install-total-new">0</td>
      <td></td>
    </tr>
    <tr>
      <td></td>
      <td></td>
      <td></td>
      <td></td>
      <td></td>
      <td class="right red"><input type="text" id="truncate-label-new" class="print-input text-right text-danger" value="천단위절사"></td>
      <td class="right red"><input type="text" id="truncate-amount-new" class="print-input text-right text-danger calc-bottom-input" value="0"></td>
      <td class="center red">최저가</td>
    </tr>
    <tr>
      <td colspan="2" class="center">공 급 가 액</td>
      <td colspan="3" class="center">(귀사 지게차 지원조건)</td>
      <td colspan="2" class="right" id="final-supply-price-new"><?= number_format($supplyPrice) ?></td>
      <td class="center red">(V.A.T 별도)</td>
    </tr>
    <tr>
      <td colspan="5" class="center">부 가 가 치 세</td>
      <td colspan="2" class="right" id="final-vat-new"><?= number_format($vat) ?></td>
      <td></td>
    </tr>
    <tr class="sum-row">
      <td colspan="2" class="center">합&nbsp;&nbsp;&nbsp;&nbsp;계</td>
      <td class="center"><?= number_format($sumQty) ?></td>
      <td colspan="2" class="center">대</td>
      <td colspan="2" class="right red" style="font-size:18px; font-weight:bold;">
        <span id="final-grand-total-new"><?= number_format($grandTotal) ?></span></td>
      <td></td>
    </tr>
    <?php endif; ?>

    <!-- 중고랙 -->
    <?php if ($conditionType === 'used' || $conditionType === 'both'): ?>
    <tr><td colspan="8" class="section-title-gray">〈중고랙〉</td></tr>
    <?php if(!empty($modules)): ?>
        <?php foreach($modules as $idx => $mod): ?>
        <tr>
          <td class="center"><?= $idx + 1 ?></td>
          <td class="center">중고 <?= htmlspecialchars($mod['name']) ?></td>
          <td class="center"><?= htmlspecialchars($mod['spec']) ?></td>
          <td class="center"><?= number_format($mod['qty']) ?></td>
          <td class="center">대</td>

          <td class="right">
              <?php if(strpos($mod['name'], '파렛트랙') !== false): ?>
                  <span class="calc-rack-unit rack-used" data-raw="<?= intval($mod['raw_price'] ?? 0) ?>" data-qty="<?= intval($mod['qty']) ?>">
                      <?= number_format($mod['raw_price'] ?? 0) ?>
                  </span>
              <?php else: ?>
                  <input type="text" id="other-unit-used-<?= $idx ?>" class="print-input text-right text-danger calc-other-unit other-used" data-qty="<?= intval($mod['qty']) ?>" value="<?= number_format($mod['unit_price']) ?>">
              <?php endif; ?>
          </td>
          <td class="right">
              <?php if(strpos($mod['name'], '파렛트랙') !== false): ?>
                  <span class="calc-rack-total rack-used-total">
                      <?= number_format(intval($mod['raw_price'] ?? 0) * intval($mod['qty'])) ?>
                  </span>
              <?php else: ?>
                  <span class="calc-other-total other-used-total">
                      <?= number_format($mod['total_price']) ?>
                  </span>
              <?php endif; ?>
          </td>

          <td class="center"><?= htmlspecialchars($mod['remark']) ?></td>
        </tr>
        <?php endforeach; ?>
    <?php endif; ?>
    <tr><td colspan="8" class="section-title-red">〈파렛트당 <?= $palletWeight ?>kg, 로드빔 <?= $beamThickness ?>바 <?= $totalPallets ?>plt 적재〉</td></tr>
    <tr>
      <td></td>
      <td class="center">운반비</td>
      <td class="center red"><input type="text" id="transport-region-used" class="print-input text-center text-danger" value="<?= htmlspecialchars($region) ?>"></td>
      <td class="center"><input type="number" id="transport-qty-used" class="print-input text-center calc-bottom-input" value="1"></td>
      <td class="center">대</td>
      <td class="right"><input type="text" id="transport-unit-used" class="print-input text-right calc-bottom-input" value="0"></td>
      <td class="right" id="transport-total-used">0</td>
      <td></td>
    </tr>
    <tr>
      <td></td>
      <td class="center">설치비</td>
      <td class="center"><input type="text" id="install-spec-used" class="print-input text-center text-danger" value=""></td>
      <td class="center"><input type="number" id="install-qty-used" class="print-input text-center calc-bottom-input" value="1"></td>
      <td class="center">식</td>
      <td class="right"><input type="text" id="install-unit-used" class="print-input text-right calc-bottom-input" value="0"></td>
      <td class="right" id="install-total-used">0</td>
      <td></td>
    </tr>
    <tr>
      <td></td>
      <td></td>
      <td></td>
      <td></td>
      <td></td>
      <td class="right red"><input type="text" id="truncate-label-used" class="print-input text-right text-danger" value="천단위절사"></td>
      <td class="right red"><input type="text" id="truncate-amount-used" class="print-input text-right text-danger calc-bottom-input" value="0"></td>
      <td class="center red">최저가</td>
    </tr>
    <tr>
      <td colspan="2" class="center">공 급 가 액</td>
      <td colspan="3" class="center">(귀사 지게차 지원조건)</td>
      <td colspan="2" class="right" id="final-supply-price-used"><?= number_format($supplyPrice) ?></td>
      <td class="center red">(V.A.T 별도)</td>
    </tr>
    <tr>
      <td colspan="5" class="center">부 가 가 치 세</td>
      <td colspan="2" class="right" id="final-vat-used"><?= number_format($vat) ?></td>
      <td></td>
    </tr>
    <tr class="sum-row">
      <td colspan="2" class="center">합&nbsp;&nbsp;&nbsp;&nbsp;계</td>
      <td class="center"><?= number_format($sumQty) ?></td>
      <td colspan="2" class="center">대</td>
      <td colspan="2" class="right red" style="font-size:18px; font-weight:bold;">
        <span id="final-grand-total-used"><?= number_format($grandTotal) ?></span></td>
      <td></td>
    </tr>
    <?php endif; ?>
  </table>

  <!-- ============ PROJECT BLOCK ============ -->
  <div style="margin-top:16px;">
    <div class="project-title">·PROJECT&nbsp;&nbsp;☆ PALLET RACK 설치 ☆</div>
    <table class="project-table">
      <tr>
        <td class="label" style="width:200px;">·납품 가능 일자 : 추후 협의</td>
        <td>·결제계좌: <span class="red" style="font-weight:bold;"><?= htmlspecialchars($settings['bank_account'] ?? '') ?></span></td>
      </tr>
      <tr>
        <td class="label">·설치장소 : 귀사지정장소</td>
        <td>·설치시 : 계약금 70%, 잔금 30%(공사 후)</td>
      </tr>
      <tr>
        <td class="label">·견적서유효기간 : 30일</td>
        <td>·납품시 : 100% 선결제</td>
      </tr>
    </table>
  </div>

  <!-- ============ INFORMATION BLOCK ============ -->
  <div style="margin-top:10px;">
    <div class="info-title py-2">·Information·</div>
    <table class="info-table">
      <colgroup>
        <col style="width:45%;">
        <col style="width:55%;">
      </colgroup>
      <tr>
        <td class="info-sub-title">·참고이미지·</td>
        <td class="info-sub-title">·M E M O·</td>
      </tr>
      <tr>
        <td>
          <div class="rack-img">
            <?php
              // $stages and $levels have already been calculated at the top
              // using either rack_levels or falling back to the module remarks.
              $levelYs = [];
              if ($stages > 0) {
                  $startY = 25;
                  $endY = 80; // 가장 아래 로드빔 위치 상향 조정 (기존 95)
                  if ($stages == 1) {
                      $levelYs[] = 55; // 1단일때 중앙에 하나
                  } else {
                      $spacing = ($endY - $startY) / ($stages - 1);
                      for ($i = 0; $i < $stages; $i++) {
                          $levelYs[] = $startY + ($i * $spacing);
                      }
                  }
              }
            ?>
            <div style="font-size:12px; font-weight:bold; margin-bottom:2px;">파렛트랙</div>
            <div style="font-size:11px; margin-bottom:6px;">예시(<?= $stages ?>S <?= $levels ?>단 기준)</div>
            <svg width="260" height="130" viewBox="0 0 260 130">
              <!-- 독립 rack -->
              <g stroke="#2255aa" stroke-width="4" fill="none">
                <line x1="30" y1="15" x2="30" y2="115"/>
                <line x1="90" y1="15" x2="90" y2="115"/>
              </g>
              <g stroke="#c0392b" stroke-width="6">
                <?php foreach($levelYs as $y): ?>
                <line x1="20" y1="<?= $y ?>" x2="100" y2="<?= $y ?>"/>
                <?php endforeach; ?>
              </g>
              <text x="60" y="128" font-size="11" text-anchor="middle" fill="#000">독립</text>

              <!-- 연결 rack -->
              <g stroke="#2255aa" stroke-width="4" fill="none">
                <!-- 왼쪽 기둥 없음 (독립 기둥 공유) -->
                <line x1="210" y1="15" x2="210" y2="115"/>
              </g>
              <g stroke="#c0392b" stroke-width="6">
                <?php foreach($levelYs as $y): ?>
                <line x1="140" y1="<?= $y ?>" x2="220" y2="<?= $y ?>"/>
                <?php endforeach; ?>
              </g>
              <text x="180" y="128" font-size="11" text-anchor="middle" fill="#000">연결</text>
            </svg>
          </div>
          <div class="caution">자재 특성상 출고시 도장색상이 변동 될수 있습니다.</div>
        </td>
        <td style="padding:12px;">
          <ol class="memo-list">
            <li>부가가치세 포함</li>
            <li>색상: 분체도장 (로드빔 - 주황색, 기둥 - 파란색)</li>
            <li>색상: 분체도장 (중량랙·경량랙 - 아이보리)</li>
            <li>설치시 현장 지게차 지원조건</li>
          </ol>
          <div class="contact-line">
            대표자 연락처: <?= htmlspecialchars($settings['contact_number'] ?? '') ?>&nbsp;&nbsp;대표자 : <?= htmlspecialchars($settings['manager_name'] ?? '') ?>
          </div>
        </td>
      </tr>
    </table>
  </div>

  <div class="footer-bar"><?= strtoupper(htmlspecialchars($settings['company_name'] ?? '')) ?></div>

</div></div></div></div></div></main>


    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- ============ MARGIN CALCULATOR (FIXED) ============ -->
<style>
.margin-calculator {
    position: fixed;
    top: 20px;
    right: 20px;
    background: #fff;
    border: 2px solid #333;
    border-radius: 4px;
    box-shadow: 0 8px 16px rgba(0,0,0,0.2);
    width: 290px;
    z-index: 1050;
    font-size: 13px;
    color: #333;
}
.margin-calculator-header {
    background: #333;
    color: #fff;
    padding: 8px 12px;
    font-weight: bold;
    font-size: 14px;
    text-align: center;
}
.margin-calculator table {
    width: 100%;
    border-collapse: collapse;
}
.margin-calculator th, .margin-calculator td {
    border: 1px solid #ccc;
    padding: 6px;
    text-align: right;
    vertical-align: middle;
}
.margin-calculator th {
    background-color: #ffeb3b;
    text-align: center;
    font-weight: bold;
    color: #333;
}
.margin-calculator .label-cell {
    background-color: #f8f9fa;
    text-align: left;
    font-weight: 600;
    width: 40%;
}
.margin-calculator input {
    width: 100%;
    border: 1px solid #aaa;
    text-align: right;
    padding: 4px;
    border-radius: 3px;
    font-weight: bold;
}
.margin-calculator input:focus {
    outline: none;
    border-color: #0d6efd;
    box-shadow: 0 0 0 2px rgba(13,110,253,.25);
}
@media print {
    .margin-calculator {
        display: none !important;
    }
}
</style>

<div class="margin-calculator">
    <div class="margin-calculator-header">💡 단가/마진 시뮬레이터</div>
    
    <!-- TABS -->
    <ul class="nav nav-tabs nav-fill" style="margin-top:0; border-bottom:1px solid #dee2e6; font-size:12px;">
      <?php if ($conditionType === 'new' || $conditionType === 'both'): ?>
      <li class="nav-item">
        <a class="nav-link <?= ($conditionType !== 'used') ? 'active' : '' ?> py-1 rounded-0" href="#calc-tab-new" data-bs-toggle="tab" style="color:#333; font-weight:bold;">신규랙</a>
      </li>
      <?php endif; ?>
      <?php if ($conditionType === 'used' || $conditionType === 'both'): ?>
      <li class="nav-item">
        <a class="nav-link <?= ($conditionType === 'used') ? 'active' : '' ?> py-1 rounded-0" href="#calc-tab-used" data-bs-toggle="tab" style="color:#666; font-weight:bold;">중고랙</a>
      </li>
      <?php endif; ?>
    </ul>

    <!-- TAB CONTENT -->
    <div class="tab-content" style="padding: 10px;">
        <!-- NEW RACK TAB -->
        <?php if ($conditionType === 'new' || $conditionType === 'both'): ?>
        <div class="tab-pane fade <?= ($conditionType !== 'used') ? 'show active' : '' ?>" id="calc-tab-new">
            <table>
                <tbody>
                    <tr>
                        <td class="label-cell">원가총액</td>
                        <td id="calc-new-raw" style="font-weight:bold; color:#198754;">0</td>
                    </tr>
                    <tr>
                        <td class="label-cell">수평라이너</td>
                        <td style="color:#666; font-size:12px;">
                            <div style="display:flex; align-items:center;">
                                <?= $linerQty ?>개 × 
                                <input type="number" id="liner-price-new" class="calc-bottom-input" value="500" style="width:50px; margin:0 4px; padding:0 2px; text-align:right;">원 
                                <span id="liner-total-new-display" style="display:none;">(-<?= number_format($linerTotal) ?>)</span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="label-cell">목표마진</td>
                        <td><input type="number" id="calc-new-target" value="0"></td>
                    </tr>
                    <tr>
                        <td class="label-cell">현재마진</td>
                        <td id="calc-new-current" style="font-weight:bold; color:#dc3545;">0</td>
                    </tr>
                    <tr>
                        <td class="label-cell">마진(%)</td>
                        <td>
                            <div style="display:flex; align-items:center;">
                                <input type="number" id="calc-new-percent" value="0" style="flex:1;">
                                <span style="margin-left:4px; font-weight:bold;">%</span>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
        
        <!-- USED RACK TAB -->
        <?php if ($conditionType === 'used' || $conditionType === 'both'): ?>
        <div class="tab-pane fade <?= ($conditionType === 'used') ? 'show active' : '' ?>" id="calc-tab-used">
            <table>
                <tbody>
                    <tr>
                        <td class="label-cell">원가총액</td>
                        <td id="calc-used-raw" style="font-weight:bold; color:#198754;">0</td>
                    </tr>
                    <tr>
                        <td class="label-cell">수평라이너</td>
                        <td style="color:#666; font-size:12px;">
                            <div style="display:flex; align-items:center;">
                                <?= $linerQty ?>개 × 
                                <input type="number" id="liner-price-used" class="calc-bottom-input" value="500" style="width:50px; margin:0 4px; padding:0 2px; text-align:right;">원 
                                <span id="liner-total-used-display" style="display:none;">(-<?= number_format($linerTotal) ?>)</span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="label-cell">목표마진</td>
                        <td><input type="number" id="calc-used-target" value="0"></td>
                    </tr>
                    <tr>
                        <td class="label-cell">현재마진</td>
                        <td id="calc-used-current" style="font-weight:bold; color:#dc3545;">0</td>
                    </tr>
                    <tr>
                        <td class="label-cell">마진(%)</td>
                        <td>
                            <div style="display:flex; align-items:center;">
                                <input type="number" id="calc-used-percent" value="0" style="flex:1;">
                                <span style="margin-left:4px; font-weight:bold;">%</span>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Action Buttons -->
    <div style="padding: 10px; border-top: 1px solid #dee2e6; display: flex; gap: 8px;">
        <button type="button" class="btn btn-sm btn-outline-secondary" style="flex:1; font-weight:bold;" onclick="window.print();">
            <i class="fa-solid fa-print"></i> 프린트
        </button>
        <button type="button" class="btn btn-sm btn-primary" style="flex:1; font-weight:bold;" data-bs-toggle="modal" data-bs-target="#emailModal">
            <i class="fa-solid fa-envelope"></i> 메일 보내기
        </button>
    </div>
</div>
<!-- ============ END MARGIN CALCULATOR ============ -->

<!-- Email Modal -->
<div class="modal fade" id="emailModal" tabindex="-1" aria-labelledby="emailModalLabel" aria-hidden="true" style="z-index: 1060;">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="emailModalLabel"><i class="fa-solid fa-envelope"></i> 견적서 메일 전송</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="alert alert-info py-2" style="font-size: 13px;">
            <i class="fa-solid fa-circle-info"></i> 현재 화면의 견적서가 <b>PDF 파일로 자동 첨부</b>되어 발송됩니다.
        </div>
        <form id="emailSendForm">
          <div class="mb-3">
            <label for="emailTo" class="form-label fw-bold text-dark">수신자 메일 주소</label>
            <input type="email" class="form-control" id="emailTo" value="<?= htmlspecialchars($quote['email'] ?? '') ?>" placeholder="고객 이메일 입력" required>
          </div>
          <div class="mb-3">
            <label for="emailSubject" class="form-label fw-bold text-dark">메일 제목</label>
            <input type="text" class="form-control" id="emailSubject" value="[견적서] <?= htmlspecialchars($settings['company_name'] ?? '아사미야') ?>에서 요청하신 견적서를 보내드립니다." required>
          </div>
          <div class="mb-3">
            <label for="emailBody" class="form-label fw-bold text-dark">메일 내용</label>
            <textarea class="form-control" id="emailBody" rows="4" required>안녕하세요, 
요청하신 견적서를 첨부 파일로 보내드립니다.
검토해 보시고 문의 사항이 있으시면 언제든 연락 주시기 바랍니다.

감사합니다.</textarea>
          </div>
          <div class="mb-3">
            <label for="emailExtraFiles" class="form-label fw-bold text-dark">추가 첨부파일 <span class="text-muted fw-normal">(선택)</span></label>
            <input class="form-control" type="file" id="emailExtraFiles" multiple>
            <div class="form-text">PDF 견적서 외에 추가로 보낼 도면이나 자료가 있다면 첨부하세요.</div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">취소</button>
        <button type="button" id="btnSubmitEmail" class="btn btn-primary" onclick="submitEmailForm();"><i class="fa-solid fa-paper-plane"></i> 발송하기</button>
      </div>
    </div>
  </div>
</div>

<div id="saved-quote-data" style="display:none" data-details="<?= base64_encode($_raw['quote']['admin_quote_details'] ?? '{}') ?>"></div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
async function submitEmailForm() {
    const to = document.getElementById('emailTo').value;
    const subject = document.getElementById('emailSubject').value;
    const body = document.getElementById('emailBody').value;
    const extraFilesInput = document.getElementById('emailExtraFiles');
    
    if(!to || !subject) {
        alert('이메일 주소와 제목을 입력해주세요.');
        return;
    }
    
    const sendBtn = document.getElementById('btnSubmitEmail');
    sendBtn.disabled = true;
    sendBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> 발송 중...';
    
    // ★ 데이터 수집을 먼저! (계산기 숨기기 전에 해야 값이 살아있음)
    let activeType = document.getElementById('calc-tab-used') && document.getElementById('calc-tab-used').classList.contains('active') ? 'used' : 'new';
    let finalMargin = document.getElementById('calc-' + activeType + '-percent') ? document.getElementById('calc-' + activeType + '-percent').value : 0;
    let finalPriceText = document.getElementById('final-grand-total-' + activeType) ? document.getElementById('final-grand-total-' + activeType).innerText : '0';
    let finalPrice = parseInt(finalPriceText.replace(/,/g, '')) || 0;
    
    let details = {};
    document.querySelectorAll('input, select, textarea').forEach(el => {
        if (el.id && !['emailTo', 'emailSubject', 'emailBody', 'emailExtraFiles'].includes(el.id)) {
            details[el.id] = el.value;
        }
    });
    
    // 임시로 계산기 숨기기 (PDF 캡처용)
    const marginCalc = document.querySelector('.margin-calculator');
    if(marginCalc) marginCalc.style.display = 'none';
    
    const element = document.querySelector('.sheet');
    
    // 캡처 전 렌더링 오차 방지를 위해 임시 스타일 적용
    const originalMargin = element.style.margin;
    const originalTransform = element.style.transform;
    element.style.margin = '0';
    element.style.transform = 'none';
    
    const opt = {
      margin:       0,
      filename:     '견적서.pdf',
      image:        { type: 'jpeg', quality: 0.98 },
      html2canvas:  { 
          scale: 2, 
          useCORS: true, 
          scrollY: 0, 
          scrollX: 0,
          windowWidth: 1000,
          x: 0,
          y: 0
      },
      jsPDF:        { unit: 'px', format: [1000, element.offsetHeight], orientation: 'portrait' }
    };
    
    try {
        const pdfBlob = await html2pdf().set(opt).from(element).output('blob');
        
        // 스타일 원상 복구
        element.style.margin = originalMargin;
        element.style.transform = originalTransform;
        
        const formData = new FormData();
        
        formData.append('admin_margin', finalMargin);
        formData.append('admin_price', finalPrice);
        formData.append('admin_quote_details', JSON.stringify(details));
        
        formData.append('to', to);
        formData.append('subject', subject);
        formData.append('body', body);
        formData.append('quote_pdf', pdfBlob, 'quote.pdf');
        
        if (extraFilesInput.files.length > 0) {
            for(let i=0; i<extraFilesInput.files.length; i++) {
                formData.append('extra_files[]', extraFilesInput.files[i]);
            }
        }
        
        const response = await fetch('/vendor/quotes/<?= $quote['id'] ?>/send_email', {
            method: 'POST',
            body: formData
        });
        
        const resData = await response.json();
        if(resData.success) {
            alert('메일이 성공적으로 발송되었습니다!');
            window.location.reload();
        } else {
            alert('발송 실패: ' + resData.message);
        }
    } catch (err) {
        console.error(err);
        alert('오류가 발생했습니다: ' + err.message);
    } finally {
        if(marginCalc) marginCalc.style.display = 'block';
        sendBtn.disabled = false;
        sendBtn.innerHTML = '<i class="fa-solid fa-paper-plane"></i> 발송하기';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Number formatting helper
    const formatNum = (num) => Math.round(num).toLocaleString('ko-KR');
    const parseNum = (str) => parseInt(str.replace(/,/g, '')) || 0;

    // Elements
    const inputNewPercent = document.getElementById('calc-new-percent');
    const inputUsedPercent = document.getElementById('calc-used-percent');
    const inputNewTarget = document.getElementById('calc-new-target');
    const inputUsedTarget = document.getElementById('calc-used-target');
    
    // Check if already mailed
    let isMailed = <?= !empty($quote['is_mailed']) ? 'true' : 'false' ?>;
    let savedMargin = <?= isset($quote['admin_margin']) ? (float)$quote['admin_margin'] : 0 ?>;

    if (isMailed) {
        if (inputNewPercent) inputNewPercent.value = savedMargin;
        if (inputUsedPercent) inputUsedPercent.value = savedMargin;
        // Populate details from hidden data attribute (base64 encoded)
        let detailsB64 = document.getElementById('saved-quote-data').getAttribute('data-details');
        if (detailsB64) {
            try {
                // atob()은 Latin-1 바이너리로 디코딩 → 한글 깨짐. TextDecoder로 UTF-8 디코딩
                const bytes = Uint8Array.from(atob(detailsB64), c => c.charCodeAt(0));
                let detailsRaw = new TextDecoder('utf-8').decode(bytes);
                let savedDetails = JSON.parse(detailsRaw);
                for (let id in savedDetails) {
                    let el = document.getElementById(id);
                    if (el) {
                        el.value = savedDetails[id];
                    }
                }
            } catch(e) { console.error('Failed to parse details:', e); }
        }
        
        // ★ 값 복원 후 마진 재계산 트리거 (아래 calculateMargin 함수 정의 후 호출)
        setTimeout(function() {
            if (typeof calculateMargin === 'function') calculateMargin();
            
            // 재계산 후에 모든 입력 비활성화
            document.querySelectorAll('input, select, textarea').forEach(inp => {
                if (inp.id && !['emailTo', 'emailSubject', 'emailBody', 'emailExtraFiles'].includes(inp.id)) {
                    inp.disabled = true;
                }
            });
        }, 100);
        
        // Hide email modal button or disable it
        const topSendBtn = document.querySelector('button[data-bs-target="#emailModal"]');
        if (topSendBtn) {
            topSendBtn.disabled = true;
            topSendBtn.innerHTML = '<i class="fa-solid fa-check"></i> 발송 완료됨';
            topSendBtn.className = 'btn btn-secondary';
        }
    }
    
    // We only have one table of items currently, so we'll apply the ACTIVE tab's margin.
    // Ideally, New Racks and Used Racks would be separated in the HTML.
    // For now, we apply the logic to all .calc-rack-unit elements based on the active tab.
    
    function calculateMargin() {
        // Calculate New Racks
        calcSectionMargin('new', parseFloat(inputNewPercent.value) || 0);
        // Calculate Used Racks
        calcSectionMargin('used', parseFloat(inputUsedPercent.value) || 0);
    }
    
    function calcSectionMargin(type, marginPercent) {
        let sumRaw = 0;
        let sumSell = 0;
        
        document.querySelectorAll('.rack-' + type).forEach(el => {
            let rawUnit = parseInt(el.getAttribute('data-raw')) || 0;
            let qty = parseInt(el.getAttribute('data-qty')) || 0;
            
            let sellUnit = rawUnit + (rawUnit * (marginPercent / 100));
            let rawTotal = rawUnit * qty;
            let sellTotal = sellUnit * qty;
            
            sumRaw += rawTotal;
            sumSell += sellTotal;
            
            el.innerText = formatNum(sellUnit);
            el.closest('tr').querySelector('.rack-' + type + '-total').innerText = formatNum(sellTotal);
        });
        
        // Update grand totals for this section
        updateGrandTotals(type);
    }
    
    function updateGrandTotals(type) {
        let totalAmount = 0;
        
        let sumRaw = 0;
        let sumSell = 0;
        
        // Sum Pallet Racks
        document.querySelectorAll('.rack-' + type + '-total').forEach(el => {
            let sellTotal = parseNum(el.innerText);
            totalAmount += sellTotal;
            sumSell += sellTotal;
        });
        
        // Calculate Raw Racks for margin
        document.querySelectorAll('.rack-' + type).forEach(el => {
            let rawUnit = parseInt(el.getAttribute('data-raw')) || 0;
            let qty = parseInt(el.getAttribute('data-qty')) || 0;
            sumRaw += rawUnit * qty;
        });
        
        // Sum Other items
        document.querySelectorAll('.other-' + type).forEach(el => {
            let unit = parseNum(el.value);
            let qty = parseInt(el.getAttribute('data-qty')) || 0;
            let total = unit * qty;
            el.closest('tr').querySelector('.other-' + type + '-total').innerText = formatNum(total);
            totalAmount += total;
        });
        
        // Transport
        let transUnit = parseNum(document.getElementById('transport-unit-' + type).value);
        let transQty = parseNum(document.getElementById('transport-qty-' + type).value);
        let transTotal = transUnit * transQty;
        document.getElementById('transport-total-' + type).innerText = formatNum(transTotal);
        totalAmount += transTotal;
        
        // Install
        let instUnit = parseNum(document.getElementById('install-unit-' + type).value);
        let instQty = parseNum(document.getElementById('install-qty-' + type).value);
        let instTotal = instUnit * instQty;
        document.getElementById('install-total-' + type).innerText = formatNum(instTotal);
        totalAmount += instTotal;
        
        // Truncate (always negative)
        let truncInput = document.getElementById('truncate-amount-' + type);
        let truncVal = parseNum(truncInput.value);
        if (truncVal > 0) {
            truncVal = -truncVal;
            truncInput.value = formatNum(truncVal);
        } else if (truncVal !== 0 && truncInput.value !== formatNum(truncVal)) {
            truncInput.value = formatNum(truncVal);
        }
        totalAmount += truncVal;
        
        // Update margin UI here to include truncate deduction
        let linerPriceInput = document.getElementById('liner-price-' + type);
        let linerUnitPrice = linerPriceInput ? (parseInt(linerPriceInput.value) || 0) : 500;
        let linerQty = <?= $linerQty ?>;
        let linerTotal = linerQty * linerUnitPrice;
        
        let linerTotalDisplay = document.getElementById('liner-total-' + type + '-display');
        if (linerTotalDisplay) {
            linerTotalDisplay.innerText = '(-' + formatNum(linerTotal) + ')';
        }
        
        let currentMargin = (sumSell - sumRaw) + truncVal - linerTotal;
        
        let rawEl = document.getElementById('calc-' + type + '-raw');
        if (rawEl) rawEl.innerText = formatNum(sumRaw);
        
        document.getElementById('calc-' + type + '-current').innerText = formatNum(currentMargin);
        
        // Supply Price
        let supplyPrice = totalAmount;
        document.getElementById('final-supply-price-' + type).innerText = formatNum(supplyPrice);
        
        // VAT
        let vat = Math.floor(supplyPrice * 0.1);
        document.getElementById('final-vat-' + type).innerText = formatNum(vat);
        
        // Grand Total
        document.getElementById('final-grand-total-' + type).innerText = formatNum(supplyPrice + vat);
    }
    
    // Setup inputs listener
    document.querySelectorAll('.calc-bottom-input').forEach(input => {
        input.addEventListener('input', function() {
            // Check if this is new or used section
            let type = this.id.endsWith('-used') ? 'used' : 'new';
            updateGrandTotals(type);
        });
    });
    
    document.querySelectorAll('.calc-other-unit').forEach(input => {
        input.addEventListener('input', function() {
            let type = this.classList.contains('other-used') ? 'used' : 'new';
            updateGrandTotals(type);
        });
    });


    // Event Listeners
    if (inputNewPercent) {
        inputNewPercent.addEventListener('input', function() {
            if (inputNewTarget) inputNewTarget.value = '0';
            calculateMargin();
        });
    }
    if (inputUsedPercent) {
        inputUsedPercent.addEventListener('input', function() {
            if (inputUsedTarget) inputUsedTarget.value = '0';
            calculateMargin();
        });
    }
    
    function calculatePercentFromTarget(type) {
        let targetInput = document.getElementById('calc-' + type + '-target');
        let percentInput = document.getElementById('calc-' + type + '-percent');
        if (!targetInput || !percentInput) return;
        
        let targetVal = parseNum(targetInput.value);
        let sumRaw = 0;
        document.querySelectorAll('.rack-' + type).forEach(el => {
            let rawUnit = parseInt(el.getAttribute('data-raw')) || 0;
            let qty = parseInt(el.getAttribute('data-qty')) || 0;
            sumRaw += rawUnit * qty;
        });
        
        if (sumRaw > 0) {
            let truncInput = document.getElementById('truncate-amount-' + type);
            let truncVal = truncInput ? parseNum(truncInput.value) : 0;
            if (truncVal > 0) truncVal = -truncVal;
            
            let linerPriceInput = document.getElementById('liner-price-' + type);
            let linerUnitPrice = linerPriceInput ? (parseInt(linerPriceInput.value) || 0) : 500;
            let linerQty = <?= isset($linerQty) ? (int)$linerQty : 0 ?>; 
            let linerTotal = linerQty * linerUnitPrice;
            
            let requiredPercent = ((targetVal - truncVal + linerTotal) / sumRaw) * 100;
            percentInput.value = requiredPercent.toFixed(2);
            calcSectionMargin(type, requiredPercent);
        }
    }

    if (inputNewTarget) inputNewTarget.addEventListener('input', function() { calculatePercentFromTarget('new'); });
    if (inputUsedTarget) inputUsedTarget.addEventListener('input', function() { calculatePercentFromTarget('used'); });
    
    // Listen to tab changes to recalculate
    document.querySelectorAll('a[data-bs-toggle="tab"]').forEach(tab => {
        tab.addEventListener('shown.bs.tab', calculateMargin);
    });
    


    // Select text on focus for all inputs
    document.querySelectorAll('input').forEach(input => {
        input.addEventListener('focus', function() {
            this.select();
        });
    });

    // Initial calc
    calculateMargin();
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const calc = document.querySelector('.margin-calculator');
    const header = document.querySelector('.margin-calculator-header');
    
    if (calc && header) {
        let isDragging = false;
        let offsetX, offsetY;

        header.style.cursor = 'move';
        header.title = "마우스로 드래그해서 이동할 수 있습니다";

        header.addEventListener('mousedown', function(e) {
            isDragging = true;
            const rect = calc.getBoundingClientRect();
            offsetX = e.clientX - rect.left;
            offsetY = e.clientY - rect.top;
            calc.style.right = 'auto'; // Right 속성 해제 (Left로 움직이기 위해)
        });

        document.addEventListener('mousemove', function(e) {
            if (!isDragging) return;
            calc.style.left = (e.clientX - offsetX) + 'px';
            calc.style.top = (e.clientY - offsetY) + 'px';
        });

        document.addEventListener('mouseup', function() {
            isDragging = false;
        });
    }
});
</script>

</body>
</html>
