<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Admin' ?> - Admin Panel</title>
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Admin Custom CSS -->
    <link href="/css/admin.css?v=<?php echo filemtime(CM_PUBLIC_PATH . '/css/admin.css'); ?>" rel="stylesheet">
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-4.0.0-beta.min.js"></script>
    <meta name="csrf-token" content="<?= $csrf_token ?? '' ?>">
    <?php do_action('admin_header_head'); ?>
</head>
<body class="admin-body">

<div class="admin-wrapper">
    <!-- Sidebar -->
    <aside class="admin-sidebar" id="sidebar">
        <a href="/admin" class="sidebar-brand">
            <i class="fa-solid fa-bolt"></i>
            <span>ADMIN</span>
        </a>

        <div class="sidebar-nav">
            <!-- Category 1: 커뮤니티 (Community) -->
            <div class="nav-category">
                <i class="fa-solid fa-users-gear"></i> 커뮤니티 관리
            </div>
            <a href="/admin" id="link-dashboard">
                <i class="fa-solid fa-house"></i> Dashboard
            </a>
            <a href="/admin/config" id="link-config">
                <i class="fa-solid fa-sliders"></i> Site Config
            </a>
            <a href="/admin/users" id="link-users">
                <i class="fa-solid fa-user-group"></i> User Manager
            </a>
            <a href="/admin/point" id="link-point">
                <i class="fa-solid fa-coins"></i> Point Manager
            </a>
            <a href="/admin/groups" id="link-groups">
                <i class="fa-solid fa-layer-group"></i> Board Groups
            </a>
            <a href="/admin/boards" id="link-boards">
                <i class="fa-solid fa-list-check"></i> Board Manager
            </a>
            <a href="/admin/faq" id="link-faq">
                <i class="fa-solid fa-question-circle"></i> FAQ Manager
            </a>
            <a href="/admin/pages" id="link-pages">
                <i class="fa-solid fa-file-lines"></i> Page Manager
            </a>
            <a href="/admin/visitors" id="link-visitors">
                <i class="fa-solid fa-chart-line"></i> Visitors
            </a>
            <a href="/admin/mail" id="link-mail">
                <i class="fa-solid fa-envelope"></i> Mail Manager
            </a>

            <!-- Category 2: 쇼핑몰 (Mall) -->
            <div class="nav-category" style="margin-top: 1.5rem; border-top: 1px solid rgba(255,255,255,0.05); pt-3">
                <i class="fa-solid fa-cart-shopping"></i> 쇼핑몰 관리
            </div>
            <a href="/admin/products" id="link-products">
                <i class="fa-solid fa-boxes-stacked"></i> 상품 승인/관리
            </a>
            <a href="/admin/orders" id="link-orders">
                <i class="fa-solid fa-receipt"></i> 전체 주문 내역
            </a>
            <a href="/admin/sellers" id="link-sellers">
                <i class="fa-solid fa-store"></i> 판매자 관리
            </a>
            <a href="/admin/settlements" id="link-settlements">
                <i class="fa-solid fa-money-bill-transfer"></i> 정산 승인/관리
            </a>

            <?php 
            $pluginMenuItems = \App\Core\PluginManager::getInstance()->getAdminMenuItems();
            if (!empty($pluginMenuItems)): 
            ?>
            <div class="nav-category">Plugins</div>
            <?php foreach ($pluginMenuItems as $item): ?>
                <a href="<?= $item['url'] ?>" id="<?= $item['id'] ?>">
                    <i class="<?= $item['icon'] ?>"></i> <?= $item['title'] ?>
                </a>
            <?php endforeach; ?>
            <?php endif; ?>

            <div class="nav-category">System</div>
            <a href="/" target="_blank" style="margin-top: auto; background: rgba(255,255,255,0.03);">
                <i class="fa-solid fa-arrow-up-right-from-square"></i> Live Site
            </a>
            <a href="/logout" style="color: #ef4444;">
                <i class="fa-solid fa-power-off"></i> Logout
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="admin-main">
        <!-- Topbar -->
        <header class="admin-topbar">
            <div class="topbar-left">
                <button class="mobile-toggle" onclick="$('#sidebar').toggleClass('active')">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <h5 style="margin: 0; font-weight: 600;"><?= $title ?? 'Dashboard' ?></h5>
            </div>

            <div class="topbar-right">
                <div class="user-profile">
                    <div style="text-align: right; line-height: 1.2;">
                        <div style="font-size: 0.85rem; font-weight: 600;"><?= htmlspecialchars($user['username'] ?? 'Admin') ?></div>
                        <div style="font-size: 0.7rem; color: var(--text-muted);"><?= strtoupper($is_super ? 'SUPER ADMIN' : ($is_admin ? 'ADMIN' : 'USER')) ?></div>
                    </div>
                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($user['username'] ?? 'A') ?>&background=6366f1&color=fff" alt="Profile">
                </div>
            </div>
        </header>

        <div class="admin-content">
