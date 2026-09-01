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
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- External Vendor Dashboard CSS -->
    <link href="/css/vendor_dashboard.css" rel="stylesheet">
</head>
<body>

    <!-- 🧭 좌측 네비게이션 사이드바 -->
    <?php include __DIR__ . '/sidebar.php'; ?>

    <!-- 💻 우측 메인 대시보드 영역 -->
    <main class="main-content">
        <div class="top-navbar">
            <div class="navbar-title fw-bold text-light" style="font-size: 1.1rem;">
                SaaS Dashboard &gt; 단가확인
            </div>
            <div class="d-flex align-items-center gap-3">
                <div class="btn-group" role="group">
                    <a href="/vendor/quotes/<?= $quote['id'] ?>" class="btn btn-outline-info btn-sm px-3 text-light" style="font-size:0.85rem; border-color: rgba(255,255,255,0.15);">견적상세보기</a>
                    <a href="/vendor/quotes/<?= $quote['id'] ?>/price" class="btn btn-info btn-sm px-3 fw-bold text-dark" style="font-size:0.85rem;">단가확인</a>
                    <a href="/vendor/quotes/<?= $quote['id'] ?>/document" class="btn btn-outline-info btn-sm px-3 text-light" style="font-size:0.85rem; border-color: rgba(255,255,255,0.15);">견적서</a>
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
                <a href="/vendor/addon_payment" class="btn btn-outline-warning btn-sm fw-bold px-3 py-1 me-3" style="border-radius: 10px;">
                    <i class="fa-solid fa-bolt"></i> 횟수 충전
                </a>
                <?php endif; ?>
                    <i class="fa-solid fa-circle-user text-info fs-5"></i>
                    <span class="small font-monospace text-light"><?= htmlspecialchars($_SESSION['user']['username'] ?? 'User') ?>님</span>
                </div>
            </div>
        </div>

        <div class="content-body">
          
<?php
$isCompleted = !empty($quote['processed_by']) || !empty($quote['is_mailed']);
?>
            <!-- 🛠️ 상단 액션 툴바 (저장 & 초기화) -->
            <div class="d-flex justify-content-between align-items-center mb-3 px-1">
                <div>
                    <?php if ($isCompleted): ?>
                        <span class="badge bg-success text-light px-3 py-2 fs-6 shadow-sm rounded-pill">
                            <i class="fa-solid fa-lock me-1"></i> 발송 완료된 견적서 (단가/부품 수정 잠금)
                        </span>
                    <?php elseif (!empty($isCustomized)): ?>
                        <span class="badge bg-warning text-dark px-3 py-2 fs-6 shadow-sm rounded-pill">
                            <i class="fa-solid fa-pen-to-square me-1"></i> 관리자 수동 단가/부품 수정 적용 중
                        </span>
                    <?php else: ?>
                        <span class="badge bg-secondary text-light px-3 py-2 fs-6 shadow-sm rounded-pill">
                            <i class="fa-solid fa-calculator me-1"></i> 도면 기준 자동 산출 단가
                        </span>
                    <?php endif; ?>
                </div>
                <div class="d-flex gap-2">
                    <?php if ($isCompleted): ?>
                        <button type="button" class="btn btn-outline-secondary btn-sm px-3 fw-bold shadow-sm" disabled title="이미 고객에게 발송 완료된 견적서입니다.">
                            <i class="fa-solid fa-lock me-1"></i> 발송 완료 (수정 불가)
                        </button>
                    <?php else: ?>
                        <button type="button" id="btnResetDetails" class="btn btn-outline-danger btn-sm px-3 fw-bold shadow-sm">
                            <i class="fa-solid fa-rotate-left me-1"></i> 기본 도면값으로 초기화
                        </button>
                        <button type="button" id="btnSaveDetails" class="btn btn-success btn-sm px-4 fw-bold shadow-sm">
                            <i class="fa-solid fa-floppy-disk me-1"></i> 수정사항 저장하기
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <div class="row g-4"><div class="col-12"><div class="quote-template-wrapper"><div class="sheet">
              <h2 class="text-center pb-3 fw-bold">실자재 단가확인</h2>

  <!-- ============ HEADER INFO ============ -->
<?php
// 완료된 견적서인 경우, 처리한 직원의 정보 사용. 아니면 현재 접속한 세션의 직원 정보 사용
$isCompleted = !empty($quote['processed_by']);

$empColor = ($isCompleted && !empty($quote['employee_color'])) 
    ? $quote['employee_color'] 
    : (!empty($_SESSION['employee_color']) ? $_SESSION['employee_color'] : '#FFFFCC');

$empName = ($isCompleted && !empty($quote['employee_name'])) 
    ? $quote['employee_name'] . ' ' . ($quote['employee_title'] ?? '')
    : (!empty($_SESSION['employee_name']) ? $_SESSION['employee_name'] . ' ' . ($_SESSION['employee_title'] ?? '') : ($settings['manager_name'] ?? ''));

$empPhone = ($isCompleted && !empty($quote['employee_phone'])) 
    ? $quote['employee_phone'] 
    : (!empty($_SESSION['employee_phone']) ? $_SESSION['employee_phone'] : ($settings['contact_number'] ?? ''));

$days = array('일요일', '월요일', '화요일', '수요일', '목요일', '금요일', '토요일');
$quoteDate = strtotime($quote['created_at'] ?? 'now');
$quoteDateStr = date('Y년 m월 d일 ', $quoteDate) . $days[date('w', $quoteDate)];
$addrParts = explode(' ', trim($quote['address'] ?? ''));
$region = trim(($addrParts[0] ?? '') . ' ' . ($addrParts[1] ?? ''));

$palletWeight = intval($quote['pallet_weight'] ?? 1000) ?: 1000;
$beamThickness = intval($quote['beam_thickness'] ?? 125) ?: 125;
$totalPallets = !empty($quote['rack_pallets']) ? intval($quote['rack_pallets']) : 0;
if ($totalPallets <= 0) {
    $indep = intval($quote['rack_indep'] ?? 0);
    $conn = intval($quote['rack_conn'] ?? 0);
    $bypass = intval($quote['rack_bypass'] ?? 0);
    $small = intval($quote['rack_small_conn'] ?? 0);
    $levels = intval($quote['rack_levels'] ?? 2);
    $w = intval($quote['pallet_w'] ?? 1100) ?: 1100;
    $d = intval($quote['pallet_d'] ?? 1100) ?: 1100;
    $entryW = (strpos($quote['fork_direction'] ?? '', 'W') !== false) ? $w : $d;
    $beamL = ($entryW * 2) + 385;
    $palletsPerCell = ($beamL >= 2585) ? 2 : 1;
    $totalPallets = ((($indep + $conn + $bypass) * $palletsPerCell) + ($small * 1)) * $levels;
}
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
    background-color: color-mix(in srgb, <?= $empColor ?> 30%, white) !important;
}
.quote-template-wrapper .footer-bar {
    background-color: <?= $empColor ?> !important;
}
tr[style*="#FFFFCC"], th[style*="#FFFFCC"] {
    background-color: color-mix(in srgb, <?= $empColor ?> 30%, white) !important;
}
.bom-input {
    border: 1px solid #cbd5e1;
    background-color: #ffffff;
    font-size: 0.82rem;
    padding: 2px 5px;
    border-radius: 4px;
    transition: all 0.2s ease;
}
.bom-input:focus {
    border-color: #0ea5e9;
    box-shadow: 0 0 0 2px rgba(14, 165, 233, 0.2);
    outline: none;
}
.btn-del-row {
    color: #ef4444;
    cursor: pointer;
    transition: color 0.15s ease;
}
.btn-del-row:hover {
    color: #b91c1c;
}
</style>
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

  <!-- ============ MAIN QUOTE TABLE ============ -->
  <table class="quote-table" id="mainQuoteTable" style="table-layout: fixed;">
    <colgroup>
      <col style="width: 50px;">
      <col style="width: 120px;">
      <col style="width: 180px;">
      <col style="width: 60px;">
      <col style="width: 50px;">
      <col style="width: 120px;">
      <col style="width: 120px;">
      <col style="width: 150px;">
    </colgroup>
    <tr style="background-color: #FFFFCC;">
      <th>NO.</th>
      <th>품 명</th>
      <th>규 격</th>
      <th>수 량</th>
      <th>식</th>
      <th>단 가</th>
      <th>금 액</th>
      <th>비 고</th>
    </tr>

    <tr><td colspan="8" class="section-title-red">〈파렛트당 <?= $palletWeight ?>kg, 로드빔 <?= $beamThickness ?>바 <?= $totalPallets ?>plt 적재〉</td></tr>

    <tbody id="moduleRowsContainer">
<?php 
      if (isset($modules) && is_array($modules)) {
          $index = 1;
          foreach ($modules as $modIndex => $mod) {
?>
    <!-- 모듈 요약 행 (클릭 시 토글) -->
    <tr class="module-row" data-mod-index="<?= $modIndex ?>" data-type="<?= htmlspecialchars($mod['type'] ?? '') ?>" style="cursor: pointer; background-color: #f8fafc;" data-bs-toggle="collapse" data-bs-target="#collapseBom<?= $modIndex ?>" aria-expanded="false">
      <td class="center fw-bold row-no"><?= $index++ ?></td>
      <td class="center fw-bold text-primary mod-name"><?= htmlspecialchars($mod['name']) ?></td>
      <td class="center mod-spec"><?= htmlspecialchars($mod['spec']) ?></td>
      <td class="center fw-bold text-danger">
          <input type="number" step="1" min="1" class="form-control form-control-sm text-center fw-bold text-danger mod-qty-input bom-input" value="<?= intval($mod['qty']) ?>" onclick="event.stopPropagation();" style="width: 50px; margin: 0 auto;">
      </td>
      <td class="center">대</td>
      <td class="right fw-bold mod-unit-price" data-raw="<?= intval($mod['raw_price'] ?? 0) ?>"><?= number_format($mod['unit_price']) ?></td>
      <td class="right fw-bold mod-total-price"><?= number_format($mod['total_price']) ?></td>
      <td class="center"><span class="mod-remark"><?= htmlspecialchars($mod['remark']) ?></span> <i class="fa-solid fa-chevron-down ms-1 text-muted toggle-icon" style="font-size: 0.8rem; transition: all 0.3s ease;"></i></td>
    </tr>
    
    <!-- 모듈 상세 BOM (토글 영역) -->
    <tr class="bom-collapse-row" data-mod-index="<?= $modIndex ?>">
      <td colspan="8" class="p-0 border-0">
        <div class="collapse" id="collapseBom<?= $modIndex ?>">
          <div class="p-3" style="background-color: #f1f5f9; border-bottom: 2px solid #cbd5e1;">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div class="small fw-bold text-secondary">
                    <i class="fa-solid fa-cube me-1"></i> [<?= htmlspecialchars($mod['remark']) ?>] 1대당 구성 부품 <span class="text-muted">(수량/단가를 직접 수정할 수 있습니다)</span>
                </div>
                <button type="button" class="btn btn-outline-primary btn-sm py-0 px-2 btn-add-bom-row" data-mod-index="<?= $modIndex ?>" style="font-size: 0.78rem;">
                    <i class="fa-solid fa-plus me-1"></i> 부품 추가
                </button>
            </div>
            <table class="table table-sm table-bordered mb-0 bom-table" data-mod-index="<?= $modIndex ?>" style="font-size: 0.85rem; background-color: white;">
              <thead>
                <tr>
                  <th style="background-color: #FFFFCC !important; width: 160px;">부품명</th>
                  <th style="background-color: #FFFFCC !important; width: 220px;">규격</th>
                  <th class="text-center" style="background-color: #FFFFCC !important; width: 80px;">수량</th>
                  <th class="text-end" style="background-color: #FFFFCC !important; width: 110px;">단가</th>
                  <th class="text-end" style="background-color: #FFFFCC !important; width: 120px;">합계금액</th>
                  <th class="text-center" style="background-color: #FFFFCC !important; width: 50px;">삭제</th>
                </tr>
              </thead>
              <tbody class="bom-tbody" data-mod-index="<?= $modIndex ?>">
                <?php foreach($mod['bom'] as $bIdx => $b): 
                    $isLoss = (trim($b['name'] ?? '') === 'Loss');
                ?>
                <tr class="bom-item-row" data-is-loss="<?= $isLoss ? '1' : '0' ?>">
                  <td>
                    <input type="text" class="form-control form-control-sm bom-input bom-part-name" value="<?= htmlspecialchars($b['name']) ?>" <?= $isLoss ? 'readonly' : '' ?>>
                  </td>
                  <td>
                    <input type="text" class="form-control form-control-sm bom-input bom-part-spec" value="<?= htmlspecialchars($b['spec']) ?>" <?= $isLoss ? 'readonly' : '' ?>>
                  </td>
                  <td class="text-center">
                    <?php if ($isLoss): ?>
                      <span class="text-muted">-</span>
                      <input type="hidden" class="bom-part-qty" value="-">
                    <?php else: ?>
                      <input type="number" step="any" class="form-control form-control-sm text-center bom-input bom-part-qty" value="<?= $b['qty'] ?>">
                    <?php endif; ?>
                  </td>
                  <td class="text-end">
                    <?php if ($isLoss): ?>
                      <span class="text-muted">-</span>
                      <input type="hidden" class="bom-part-unit" value="-">
                    <?php else: ?>
                      <input type="number" step="any" class="form-control form-control-sm text-end bom-input bom-part-unit" value="<?= is_numeric($b['unit_amount']) ? floor((float)$b['unit_amount']) : $b['unit_amount'] ?>">
                    <?php endif; ?>
                  </td>
                  <td class="text-end fw-bold bom-part-total" data-val="<?= is_numeric($b['total'] ?? null) ? floor((float)$b['total']) : 0 ?>">
                    <?= is_numeric($b['total'] ?? null) ? number_format(floor((float)$b['total'])) : $b['total'] ?>
                  </td>
                  <td class="text-center">
                    <i class="fa-solid fa-trash-can btn-del-row btn-del-bom" title="삭제"></i>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </td>
    </tr>
<?php
          }
      }
      
      $totalFrames = 0;
      $totalRackQty = 0;
      if (isset($modules) && is_array($modules)) {
          foreach ($modules as $mod) {
              if (trim($mod['name']) === '파렛트랙') {
                  $isIndep = ($mod['type'] === '독립');
                  $totalFrames += (int)$mod['qty'] * ($isIndep ? 2 : 1);
                  $totalRackQty += (int)$mod['qty'];
              }
          }
      }
      $linerQty = $totalFrames * 2;
      $linerUnitPrice = empty($quote['pricing_rule_id']) ? 0 : 500;
      $linerTotal = $linerQty * $linerUnitPrice;
?>
    </tbody>

    <!-- 바닥수평라이너 행 -->
    <tr id="linerRow" style="background-color: #f8fafc; <?= ($linerQty <= 0) ? 'display:none;' : '' ?>">
      <td class="center fw-bold text-muted" id="linerIndex">-</td>
      <td class="center fw-bold text-secondary">바닥수평라이너</td>
      <td class="center">130×100mm 1.2t</td>
      <td class="center fw-bold text-secondary" id="linerQtyText"><?= number_format($linerQty) ?></td>
      <td class="center">개</td>
      <td class="right fw-bold" id="linerUnitText"><?= number_format($linerUnitPrice) ?></td>
      <td class="right fw-bold text-secondary" id="linerTotalText"><?= number_format($linerTotal) ?></td>
      <td class="center text-muted" style="font-size: 0.85rem;">기둥 수량과 동일</td>
    </tr>

    <!-- ============ 기본 단가 외 기타 추가 품목 ============ -->
    <tr style="background-color: #fef3c7;">
        <td colspan="8" class="p-2 fw-bold text-dark" style="border-top: 2px solid #f59e0b; border-bottom: 1px solid #fcd34d;">
            <div class="d-flex justify-content-between align-items-center px-2">
                <span><i class="fa-solid fa-layer-group me-1 text-warning"></i> 기본 단가 외 기타 추가 품목 (안전바, 가드 등 개별 품목)</span>
                <button type="button" id="btnAddCustomItem" class="btn btn-warning btn-sm py-1 px-3 fw-bold text-dark shadow-sm" style="font-size: 0.82rem;">
                    <i class="fa-solid fa-plus me-1"></i> 기타 품목 추가
                </button>
            </div>
        </td>
    </tr>

    <tbody id="customItemsContainer">
      <?php if (!empty($customItems)): ?>
        <?php foreach ($customItems as $cIdx => $cItem): ?>
        <tr class="custom-item-row" style="background-color: #fffbeb;">
          <td class="center fw-bold text-warning custom-row-no">-</td>
          <td>
            <input type="text" class="form-control form-control-sm bom-input custom-item-name" value="<?= htmlspecialchars($cItem['name'] ?? '') ?>" placeholder="품명 (예: 안전바)">
          </td>
          <td>
            <input type="text" class="form-control form-control-sm bom-input custom-item-spec" value="<?= htmlspecialchars($cItem['spec'] ?? '') ?>" placeholder="규격">
          </td>
          <td class="center">
            <input type="number" step="1" min="1" class="form-control form-control-sm text-center bom-input custom-item-qty" value="<?= intval($cItem['qty'] ?? 1) ?>">
          </td>
          <td class="center">
            <input type="text" class="form-control form-control-sm text-center bom-input custom-item-unit" value="<?= htmlspecialchars($cItem['unit'] ?? '개') ?>" style="width: 45px; margin: 0 auto;">
          </td>
          <td class="right">
            <input type="number" step="any" class="form-control form-control-sm text-end bom-input custom-item-price" value="<?= intval($cItem['unit_price'] ?? 0) ?>">
          </td>
          <td class="right fw-bold custom-item-total"><?= number_format($cItem['total_price'] ?? 0) ?></td>
          <td class="center">
            <div class="d-flex align-items-center gap-1">
              <input type="text" class="form-control form-control-sm bom-input custom-item-remark" value="<?= htmlspecialchars($cItem['remark'] ?? '') ?>" placeholder="비고">
              <i class="fa-solid fa-trash-can ms-1 btn-del-row btn-del-custom-item" title="삭제"></i>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>

    <!-- ============ TOTAL ROW ============ -->
    <tr class="sum-row" style="background-color: #FFFFCC;">
      <td colspan="2" class="center fw-bold">합&nbsp;&nbsp;&nbsp;&nbsp;계</td>
      <td class="center"></td>
      <td class="center fw-bold text-danger" id="totalRackQtyText"><?= $totalRackQty > 0 ? number_format($totalRackQty) : '' ?></td>
      <td class="center fw-bold" id="totalRackQtyUnit"><?= $totalRackQty > 0 ? '대' : '' ?></td>
      <td class="center"></td>
      <td class="right fw-bold" id="overallTotalText" style="color: #ef4444; font-size: 1.1rem;"><?= number_format($overallTotal ?? 0) ?></td>
      <td class="center fw-bold">원 (네고 10% 포함)</td>
    </tr>
  </table>

</div></div></div></div></div></main>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const quoteId = <?= json_encode($quote['id']) ?>;

        // Sehwa Price Calculator final amount formula: round((raw * 1.1) / 100) * 100
        function calcFinalAmount(rawAmount) {
            return Math.round((rawAmount * 1.1) / 100) * 100;
        }

        // 🌟 실시간 계산 함수
        function recalculateAll() {
            let runningIndex = 1;
            let totalRackQty = 0;
            let totalFrames = 0;
            let grandTotal = 0;

            // 1. 모듈별 BOM 및 합계 계산
            const moduleRows = document.querySelectorAll('.module-row');
            moduleRows.forEach(modRow => {
                const modIndex = modRow.getAttribute('data-mod-index');
                const modType = modRow.getAttribute('data-type');
                const qtyInput = modRow.querySelector('.mod-qty-input');
                const modQty = parseInt(qtyInput ? qtyInput.value : 0) || 0;

                modRow.querySelector('.row-no').innerText = runningIndex++;

                // BOM 목록 합계 계산
                const bomTable = document.querySelector(`.bom-table[data-mod-index="${modIndex}"]`);
                let bomRawSum = 0;

                if (bomTable) {
                    const bomItemRows = bomTable.querySelectorAll('.bom-item-row');
                    bomItemRows.forEach(bRow => {
                        const isLoss = bRow.getAttribute('data-is-loss') === '1';
                        if (isLoss) {
                            // Loss는 기존 값을 가져오거나 0
                            const totalEl = bRow.querySelector('.bom-part-total');
                            const val = parseFloat(totalEl ? totalEl.getAttribute('data-val') : 0) || 0;
                            bomRawSum += val;
                        } else {
                            const qtyEl = bRow.querySelector('.bom-part-qty');
                            const unitEl = bRow.querySelector('.bom-part-unit');
                            const totalEl = bRow.querySelector('.bom-part-total');

                            const qty = parseFloat(qtyEl ? qtyEl.value : 0) || 0;
                            const unit = parseFloat(unitEl ? unitEl.value : 0) || 0;
                            const rowTotal = Math.floor(qty * unit);

                            if (totalEl) {
                                totalEl.innerText = rowTotal.toLocaleString();
                                totalEl.setAttribute('data-val', rowTotal);
                            }
                            bomRawSum += rowTotal;
                        }
                    });
                }

                // 모듈 단가 및 총액 산출
                const modUnitPrice = calcFinalAmount(bomRawSum);
                const modTotalPrice = modUnitPrice * modQty;

                const unitPriceEl = modRow.querySelector('.mod-unit-price');
                const totalPriceEl = modRow.querySelector('.mod-total-price');

                if (unitPriceEl) {
                    unitPriceEl.innerText = modUnitPrice.toLocaleString();
                    unitPriceEl.setAttribute('data-raw', bomRawSum);
                }
                if (totalPriceEl) {
                    totalPriceEl.innerText = modTotalPrice.toLocaleString();
                }

                grandTotal += modTotalPrice;
                totalRackQty += modQty;

                if (modType === '독립') {
                    totalFrames += modQty * 2;
                } else if (modType === '연결' || modType === '작은연결' || modType === '바이패스') {
                    totalFrames += modQty * 1;
                }
            });

            // 2. 바닥수평라이너 자동 계산
            const linerRow = document.getElementById('linerRow');
            const linerQty = totalFrames * 2;
            const linerUnitPrice = <?= empty($quote['pricing_rule_id']) ? 0 : 500 ?>;
            const linerTotal = linerQty * linerUnitPrice;

            if (linerQty > 0) {
                linerRow.style.display = '';
                document.getElementById('linerIndex').innerText = runningIndex++;
                document.getElementById('linerQtyText').innerText = linerQty.toLocaleString();
                document.getElementById('linerTotalText').innerText = linerTotal.toLocaleString();
                grandTotal += linerTotal;
            } else {
                linerRow.style.display = 'none';
            }

            // 3. 기타 추가 품목 계산
            const customRows = document.querySelectorAll('.custom-item-row');
            customRows.forEach(cRow => {
                cRow.querySelector('.custom-row-no').innerText = runningIndex++;
                const qtyEl = cRow.querySelector('.custom-item-qty');
                const priceEl = cRow.querySelector('.custom-item-price');
                const totalEl = cRow.querySelector('.custom-item-total');

                const qty = parseInt(qtyEl ? qtyEl.value : 0) || 0;
                const price = parseInt(priceEl ? priceEl.value : 0) || 0;
                const total = qty * price;

                if (totalEl) {
                    totalEl.innerText = total.toLocaleString();
                }
                grandTotal += total;
            });

            // 4. 합계 행 갱신
            document.getElementById('totalRackQtyText').innerText = totalRackQty > 0 ? totalRackQty.toLocaleString() : '';
            document.getElementById('totalRackQtyUnit').innerText = totalRackQty > 0 ? '대' : '';
            document.getElementById('overallTotalText').innerText = grandTotal.toLocaleString();
        }

        // 🌟 실시간 이벤트 리스너 등록 (수량/단가 입력 시 자동 재계산)
        document.addEventListener('input', function(e) {
            if (e.target.matches('.mod-qty-input, .bom-part-qty, .bom-part-unit, .custom-item-qty, .custom-item-price')) {
                recalculateAll();
            }
        });

        // 🌟 BOM 부품 추가 버튼
        document.addEventListener('click', function(e) {
            const addBomBtn = e.target.closest('.btn-add-bom-row');
            if (addBomBtn) {
                const modIndex = addBomBtn.getAttribute('data-mod-index');
                const tbody = document.querySelector(`.bom-tbody[data-mod-index="${modIndex}"]`);
                if (tbody) {
                    const tr = document.createElement('tr');
                    tr.className = 'bom-item-row';
                    tr.setAttribute('data-is-loss', '0');
                    tr.innerHTML = `
                        <td><input type="text" class="form-control form-control-sm bom-input bom-part-name" placeholder="부품명 (예: 추가 타이빔)"></td>
                        <td><input type="text" class="form-control form-control-sm bom-input bom-part-spec" placeholder="규격"></td>
                        <td class="text-center"><input type="number" step="any" class="form-control form-control-sm text-center bom-input bom-part-qty" value="1"></td>
                        <td class="text-end"><input type="number" step="any" class="form-control form-control-sm text-end bom-input bom-part-unit" value="0"></td>
                        <td class="text-end fw-bold bom-part-total" data-val="0">0</td>
                        <td class="text-center"><i class="fa-solid fa-trash-can btn-del-row btn-del-bom" title="삭제"></i></td>
                    `;
                    tbody.appendChild(tr);
                    recalculateAll();
                }
            }

            // BOM 부품 삭제
            if (e.target.closest('.btn-del-bom')) {
                const row = e.target.closest('.bom-item-row');
                if (row) {
                    row.remove();
                    recalculateAll();
                }
            }

            // 기타 품목 추가
            if (e.target.closest('#btnAddCustomItem')) {
                const container = document.getElementById('customItemsContainer');
                const tr = document.createElement('tr');
                tr.className = 'custom-item-row';
                tr.style.backgroundColor = '#fffbeb';
                tr.innerHTML = `
                    <td class="center fw-bold text-warning custom-row-no">-</td>
                    <td><input type="text" class="form-control form-control-sm bom-input custom-item-name" placeholder="품명 (예: 안전바)"></td>
                    <td><input type="text" class="form-control form-control-sm bom-input custom-item-spec" placeholder="규격"></td>
                    <td class="center"><input type="number" step="1" min="1" class="form-control form-control-sm text-center bom-input custom-item-qty" value="1"></td>
                    <td class="center"><input type="text" class="form-control form-control-sm text-center bom-input custom-item-unit" value="개" style="width: 45px; margin: 0 auto;"></td>
                    <td class="right"><input type="number" step="any" class="form-control form-control-sm text-end bom-input custom-item-price" value="0"></td>
                    <td class="right fw-bold custom-item-total">0</td>
                    <td class="center">
                        <div class="d-flex align-items-center gap-1">
                            <input type="text" class="form-control form-control-sm bom-input custom-item-remark" placeholder="비고">
                            <i class="fa-solid fa-trash-can ms-1 btn-del-row btn-del-custom-item" title="삭제"></i>
                        </div>
                    </td>
                `;
                container.appendChild(tr);
                recalculateAll();
            }

            // 기타 품목 삭제
            if (e.target.closest('.btn-del-custom-item')) {
                const row = e.target.closest('.custom-item-row');
                if (row) {
                    row.remove();
                    recalculateAll();
                }
            }
        });

        // 🌟 저장하기 버튼 AJAX
        document.getElementById('btnSaveDetails').addEventListener('click', function() {
            recalculateAll();

            // 데이터 수집
            const modules = [];
            const moduleRows = document.querySelectorAll('.module-row');
            moduleRows.forEach(modRow => {
                const modIndex = modRow.getAttribute('data-mod-index');
                const modType = modRow.getAttribute('data-type');
                const name = modRow.querySelector('.mod-name').innerText.trim();
                const spec = modRow.querySelector('.mod-spec').innerText.trim();
                const remark = modRow.querySelector('.mod-remark').innerText.trim();
                const qty = parseInt(modRow.querySelector('.mod-qty-input').value) || 0;
                const unitPriceEl = modRow.querySelector('.mod-unit-price');
                const unitPrice = parseInt(unitPriceEl.innerText.replace(/,/g, '')) || 0;
                const rawPrice = parseInt(unitPriceEl.getAttribute('data-raw')) || 0;
                const totalPrice = parseInt(modRow.querySelector('.mod-total-price').innerText.replace(/,/g, '')) || 0;

                const bom = [];
                const bomTable = document.querySelector(`.bom-table[data-mod-index="${modIndex}"]`);
                if (bomTable) {
                    const bomRows = bomTable.querySelectorAll('.bom-item-row');
                    bomRows.forEach(bRow => {
                        const isLoss = bRow.getAttribute('data-is-loss') === '1';
                        const bName = bRow.querySelector('.bom-part-name').value.trim();
                        const bSpec = bRow.querySelector('.bom-part-spec').value.trim();
                        const bQty = isLoss ? '-' : (parseFloat(bRow.querySelector('.bom-part-qty').value) || 0);
                        const bUnit = isLoss ? '-' : (parseFloat(bRow.querySelector('.bom-part-unit').value) || 0);
                        const totalEl = bRow.querySelector('.bom-part-total');
                        const bTotal = parseFloat(totalEl ? totalEl.getAttribute('data-val') : 0) || 0;

                        bom.push({
                            name: bName,
                            spec: bSpec,
                            qty: bQty,
                            unit_amount: bUnit,
                            total: bTotal
                        });
                    });
                }

                modules.push({
                    type: modType,
                    name: name,
                    spec: spec,
                    remark: remark,
                    qty: qty,
                    unit_price: unitPrice,
                    raw_price: rawPrice,
                    total_price: totalPrice,
                    bom: bom
                });
            });

            const customItems = [];
            const customRows = document.querySelectorAll('.custom-item-row');
            customRows.forEach(cRow => {
                const name = cRow.querySelector('.custom-item-name').value.trim();
                const spec = cRow.querySelector('.custom-item-spec').value.trim();
                const qty = parseInt(cRow.querySelector('.custom-item-qty').value) || 0;
                const unit = cRow.querySelector('.custom-item-unit').value.trim() || '개';
                const unitPrice = parseInt(cRow.querySelector('.custom-item-price').value) || 0;
                const totalPrice = parseInt(cRow.querySelector('.custom-item-total').innerText.replace(/,/g, '')) || 0;
                const remark = cRow.querySelector('.custom-item-remark').value.trim();

                if (name !== '') {
                    customItems.push({
                        name: name,
                        spec: spec,
                        qty: qty,
                        unit: unit,
                        unit_price: unitPrice,
                        total_price: totalPrice,
                        remark: remark
                    });
                }
            });

            const overallTotal = parseInt(document.getElementById('overallTotalText').innerText.replace(/,/g, '')) || 0;

            const payload = {
                modules: modules,
                custom_items: customItems,
                overallTotal: overallTotal
            };

            Swal.fire({
                title: '저장 중...',
                text: '수정된 단가 및 부품 내역을 저장하고 있습니다.',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            fetch(`/vendor/quotes/${quoteId}/save_details`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '저장 완료!',
                        text: data.message || '단가 및 부품 변경사항이 성공적으로 저장되었습니다.',
                        confirmButtonColor: '#10b981'
                    }).then(() => location.reload());
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: '저장 실패',
                        text: data.message || '저장 중 오류가 발생했습니다.'
                    });
                }
            })
            .catch(err => {
                Swal.fire({
                    icon: 'error',
                    title: '오류 발생',
                    text: '네트워크 통신 중 에러가 발생했습니다.'
                });
            });
        });

        // 🌟 기본값 초기화 버튼
        document.getElementById('btnResetDetails').addEventListener('click', function() {
            Swal.fire({
                title: '초기화 확인',
                text: '수동으로 수정한 부품 및 단가 내역을 모두 지우고, 처음 도면 산출 값으로 되돌리시겠습니까?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
                confirmButtonText: '네, 초기화합니다',
                cancelButtonText: '취소'
            }).then(result => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: '초기화 중...',
                        didOpen: () => Swal.showLoading()
                    });

                    fetch(`/vendor/quotes/${quoteId}/reset_details`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: '초기화 완료',
                                text: data.message,
                                confirmButtonColor: '#10b981'
                            }).then(() => location.reload());
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: '초기화 실패',
                                text: data.message
                            });
                        }
                    })
                    .catch(() => {
                        Swal.fire({
                            icon: 'error',
                            title: '통신 오류',
                            text: '초기화 처리 중 에러가 발생했습니다.'
                        });
                    });
                }
            });
        });
        // 토글 아이콘(화살표) 애니메이션 변경 로직
        const collapsibles = document.querySelectorAll('.bom-collapse-row .collapse');
        collapsibles.forEach(col => {
            col.addEventListener('show.bs.collapse', function () {
                const moduleRow = this.closest('.bom-collapse-row').previousElementSibling;
                const icon = moduleRow.querySelector('.toggle-icon');
                if (icon) {
                    icon.classList.remove('fa-chevron-down', 'text-muted');
                    icon.classList.add('fa-chevron-up', 'text-danger');
                }
            });
            col.addEventListener('hide.bs.collapse', function () {
                const moduleRow = this.closest('.bom-collapse-row').previousElementSibling;
                const icon = moduleRow.querySelector('.toggle-icon');
                if (icon) {
                    icon.classList.remove('fa-chevron-up', 'text-danger');
                    icon.classList.add('fa-chevron-down', 'text-muted');
                }
            });
        });
    });
    </script>
</body>
</html>
