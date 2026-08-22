<?php
$currentUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$dbSidebar = \App\Core\Database::getInstance();
$stmtSidebar = $dbSidebar->prepare("SELECT company_name FROM vendor_settings WHERE user_id = ?");
$stmtSidebar->execute([$_SESSION['user']['user_id']]);
$companyNameSidebar = $stmtSidebar->fetchColumn();
$displayBrand = $companyNameSidebar ?: 'SETTING';
?>
<aside class="sidebar">
    <a href="/vendor/" class="sidebar-brand" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; display: flex; align-items: center; gap: 8px;">
        <span>⚙️</span>
        <span title="<?= htmlspecialchars($displayBrand) ?>" style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 140px;"><?= htmlspecialchars($displayBrand) ?></span>
    </a>
    <div class="sidebar-menu">
        <a href="/vendor/settings" class="menu-item <?= (strpos($currentUri, '/vendor/settings') === 0) ? 'active' : '' ?>">
            <i class="fa-solid fa-sliders"></i>
            <span>공급사 설정 관리</span>
        </a>
        <a href="/vendor/employees" class="menu-item <?= (strpos($currentUri, '/vendor/employees') === 0) ? 'active' : '' ?>">
            <i class="fa-solid fa-users"></i>
            <span>직원 관리</span>
        </a>
        <a href="/vendor/profiles" class="menu-item <?= (strpos($currentUri, '/vendor/profiles') === 0) ? 'active' : '' ?>">
            <i class="fa-solid fa-people-arrows"></i>
            <span>프로필 전환</span>
        </a>
        <hr>
        <a href="/vendor/pricing" class="menu-item <?= (strpos($currentUri, '/vendor/pricing') === 0) ? 'active' : '' ?>">
            <i class="fa-solid fa-file-excel"></i>
            <span>단가표(엑셀) 관리</span>
        </a>
        <a href="/vendor/quotes" class="menu-item <?= (strpos($currentUri, '/vendor/quotes') === 0) ? 'active' : '' ?>">
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
