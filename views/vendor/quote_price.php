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
</head>
<body>

    <!-- 🧭 좌측 네비게이션 사이드바 -->
    <aside class="sidebar">
        <a href="/" class="sidebar-brand">
            <span>⚙️</span>
            <span>ASAMIYA SAAS</span>
        </a>
        <div class="sidebar-menu">
            <a href="/vendor/settings" class="menu-item">
                <i class="fa-solid fa-sliders"></i>
                <span>공급사 설정 관리</span>
            </a>
            <a href="/vendor/quotes" class="menu-item active">
                <i class="fa-solid fa-envelope-open-text"></i>
                <span>견적요청 수신함</span>
            </a>
            <hr style="border-color: rgba(255,255,255,0.08); margin: 15px 0;">
            <a href="/" class="menu-item">
                <i class="fa-solid fa-house"></i>
                <span>홈페이지 메인</span>
            </a>
            <a href="/logout" class="menu-item" style="color: #f87171;">
                <i class="fa-solid fa-power-off"></i>
                <span>로그아웃</span>
            </a>
        </div>
    </aside>

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
                    <i class="fa-solid fa-circle-user text-info fs-5"></i>
                    <span class="small font-monospace text-light"><?= htmlspecialchars($_SESSION['user']['username'] ?? 'User') ?>님</span>
                </div>
            </div>
        </div>

        <div class="content-body">
            <div class="row g-4"><div class="col-12"><div class="quote-template-wrapper"><div class="sheet">

  <!-- ============ HEADER INFO ============ -->
<?php
$days = array('일요일', '월요일', '화요일', '수요일', '목요일', '금요일', '토요일');
$quoteDate = strtotime($quote['created_at'] ?? 'now');
$quoteDateStr = date('Y년 m월 d일 ', $quoteDate) . $days[date('w', $quoteDate)];
$addrParts = explode(' ', trim($quote['address'] ?? ''));
$region = trim(($addrParts[0] ?? '') . ' ' . ($addrParts[1] ?? ''));
?>
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
      <td style="width:150px;"><?= htmlspecialchars($settings['contact_number'] ?? '') ?></td>
      <td class="label-cell" style="width:50px;">F A X</td>
      <td><?= htmlspecialchars($settings['fax_number'] ?? '') ?></td>
      <td class="label-cell">담 당 자</td>
      <td colspan="3"><?= htmlspecialchars($quote['name'] ?? '') ?> 님</td>
    </tr>
    <tr>
      <td class="label-cell">담당자</td>
      <td><?= htmlspecialchars($settings['manager_name'] ?? '') ?></td>
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
  <table class="quote-table" style="table-layout: fixed;">
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

    <tr><td colspan="8" class="section-title-red">〈 <?= htmlspecialchars($quote['rack_type'] ?? '일반 파렛트랙') ?> 〉</td></tr>

<?php 
      if (isset($modules) && is_array($modules)) {
          $index = 1;
          foreach ($modules as $modIndex => $mod) {
?>
    <!-- 모듈 요약 행 (클릭 시 토글) -->
    <tr style="cursor: pointer; background-color: #f8fafc;" data-bs-toggle="collapse" data-bs-target="#collapseBom<?= $modIndex ?>" aria-expanded="false">
      <td class="center fw-bold"><?= $index++ ?></td>
      <td class="center fw-bold text-primary"><?= htmlspecialchars($mod['name']) ?></td>
      <td class="center"><?= htmlspecialchars($mod['spec']) ?></td>
      <td class="center fw-bold text-danger"><?= number_format($mod['qty']) ?></td>
      <td class="center">대</td>
      <td class="right fw-bold"><?= number_format($mod['unit_price']) ?></td>
      <td class="right fw-bold"><?= number_format($mod['total_price']) ?></td>
      <td class="center"><?= htmlspecialchars($mod['remark']) ?> <i class="fa-solid fa-chevron-down ms-1 text-muted" style="font-size: 0.8rem;"></i></td>
    </tr>
    
    <!-- 모듈 상세 BOM (토글 영역) -->
    <tr>
      <td colspan="8" class="p-0 border-0">
        <div class="collapse" id="collapseBom<?= $modIndex ?>">
          <div class="p-3" style="background-color: #f1f5f9; border-bottom: 2px solid #cbd5e1;">
            <div class="small fw-bold mb-2 text-secondary"><i class="fa-solid fa-cube me-1"></i> [<?= htmlspecialchars($mod['remark']) ?>] 1대당 구성 부품 (단가에 이윤/네고 선반영됨)</div>
            <table class="table table-sm table-bordered mb-0" style="font-size: 0.85rem; background-color: white;">
              <thead>
                <tr>
                  <th style="background-color: #FFFFCC !important;">부품명</th>
                  <th style="background-color: #FFFFCC !important;">규격</th>
                  <th class="text-center" style="background-color: #FFFFCC !important;">수량</th>
                  <th class="text-end" style="background-color: #FFFFCC !important;">단가</th>
                  <th class="text-end" style="background-color: #FFFFCC !important;">합계금액</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach($mod['bom'] as $b): ?>
                <tr>
                  <td><?= htmlspecialchars($b['name']) ?></td>
                  <td><?= htmlspecialchars($b['spec']) ?></td>
                  <td class="text-center"><?= is_numeric($b['qty']) ? number_format($b['qty']) : $b['qty'] ?></td>
                  <td class="text-end"><?= is_numeric($b['unit_amount']) ? number_format($b['unit_amount']) : $b['unit_amount'] ?></td>
                  <td class="text-end"><?= is_numeric($b['total']) ? number_format($b['total']) : $b['total'] ?></td>
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
      
      $totalRackQty = 0;
      if (isset($modules) && is_array($modules)) {
          foreach ($modules as $mod) {
              if (trim($mod['name']) === '파렛트랙') {
                  $totalRackQty += (int)$mod['qty'];
              }
          }
      }
?>
    
    <tr class="sum-row" style="background-color: #FFFFCC;">
      <td colspan="2" class="center fw-bold">합&nbsp;&nbsp;&nbsp;&nbsp;계</td>
      <td class="center"></td>
      <td class="center fw-bold text-danger"><?= $totalRackQty > 0 ? number_format($totalRackQty) : '' ?></td>
      <td class="center fw-bold"><?= $totalRackQty > 0 ? '대' : '' ?></td>
      <td class="center"></td>
      <td class="right fw-bold" style="color: #ef4444; font-size: 1.1rem;"><?= number_format($overallTotal ?? 0) ?></td>
      <td class="center fw-bold">원 (네고 10% 포함)</td>
    </tr>
  </table>

 

  

</div></div></div></div></div></main>


    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
