<?php
include CM_LAYOUT_PATH . '/header.php';
?>
    <style>
        .navbar {
            background-color: rgba(255, 255, 255, 0.9) !important;
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
        }
        .navbar.scrolled {
            background-color: #ffffff !important;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            border-bottom: 1px solid rgba(0, 0, 0, 0.12);
        }
        /* 헤더 내 모든 텍스트, 로고, 아이콘 다크 처리 */
        .navbar .nav-links a, 
        .navbar .navbar-brand, 
        .navbar .mobile-nav-item, 
        .navbar .username-link,
        .navbar .mobile-user-actions a,
        .navbar .sidebar-toggle {
            color: #0f172a !important;
            font-weight: 600;
            transition: color 0.25s ease;
        }
        .navbar .nav-links a:hover, 
        .navbar .mobile-nav-item:hover,
        .navbar .navbar-brand:hover,
        .navbar .username-link:hover,
        .navbar .mobile-user-actions a:hover,
        .navbar .sidebar-toggle:hover {
            color: #f16819 !important;
        }
        
        .navbar .sidebar-toggle {
            background: transparent;
            border: none;
            font-size: 1.35rem;
            line-height: 1;
        }

        /* Offcanvas styling */
        #mobileMenu {
            background-color: #F4F6F8 !important;
        }
        #mobileMenu .offcanvas-title {
            color: #0f172a !important;
            font-weight: bold;
        }
        #mobileMenu .mobile-nav-item, #mobileMenu .username-link, #mobileMenu a {
            color: #0f172a !important;
        }
        #mobileMenu .btn-close {
            filter: invert(0);
            opacity: 1;
        }
    </style>
    <nav id="mainNavbar" class="navbar navbar-light fixed-top" style="z-index: 1030;">
        <!-- Mobile Left: Login Icons -->
        <div class="mobile-user-actions">
            <?php if ($is_member): ?>
                <a href="/mypage" title="My Page"><i class="fa-regular fa-user"></i></a>
            <?php else: ?>
                <a href="/login" title="Login"><i class="fa-regular fa-user"></i></a>
            <?php endif; ?>
        </div>

        <!-- Center: Logo -->
        <a href="/" class="navbar-brand">
            <?php if (($siteConfig['logo_type'] ?? 'text') === 'image' && !empty($siteConfig['logo_image'])): ?>
                <img src="<?= $siteConfig['logo_image'] ?>" alt="<?= htmlspecialchars($siteConfig['site_name']) ?>" style="max-height: 40px;">
            <?php else: ?>
                <?= htmlspecialchars(!empty($siteConfig['logo_text']) ? $siteConfig['logo_text'] : ($siteConfig['site_name'] ?? 'NEURON AI')) ?>
            <?php endif; ?>
        </a>

        <!-- PC & Mobile Right Actions -->
        <div class="navbar-actions">
            <!-- PC Nav Links -->
            <div class="nav-links">
                <a href="/about"><i class="fa-regular fa-lightbulb"></i> 서비스 소개</a>
                <a href="/#pricing">Price</a>
                <a href="/faq">FAQ</a>
                <?php if ($is_member): ?>
                    <?php if ($is_admin): ?>
                        <a href="/admin"><i class="fa-solid fa-gauge-high"></i> Admin Panel</a>
                    <?php endif; ?>
                    <a href="/vendor"><i class="fa-solid fa-building"></i> 견적관리</a>
                    <a href="/mypage" class="username-link" style="font-size: 0.9rem; text-decoration: none;">
                        <i class="fa-solid fa-circle-user"></i> <?= htmlspecialchars($user['username']) ?>
                    </a>
                    <a href="/logout" class="btn btn-sm btn-outline-dark rounded-pill px-3 py-1">Logout</a>
                <?php else: ?>
                    <a href="/login" class="px-2">Login</a>
                    <a href="/register" class="btn btn-sm rounded-pill px-3 py-1 text-white fw-bold" style="background-color: #f16819; border: none;">Sign Up</a>
                <?php endif; ?>
            </div>

            <!-- Hamburger Toggle (Shown on Mobile) -->
            <button class="sidebar-toggle" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileMenu" aria-controls="mobileMenu" aria-label="메뉴 열기">
                <i class="fa-solid fa-bars"></i>
            </button>
        </div>
    </nav>

    <!-- Offcanvas Mobile Menu -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="mobileMenu" aria-labelledby="mobileMenuLabel" style="z-index: 9999; background-color: #F4F6F8;">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title fw-bold text-dark" id="mobileMenuLabel">MENU</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <div class="d-flex flex-column gap-3 mt-2">
                
                <a href="/about" class="text-dark text-decoration-none fw-medium fs-5 px-3 py-2 rounded" style="background-color: #ffffff; border: 1px solid #e2e8f0;">
                    <i class="fa-regular fa-lightbulb text-muted me-2"></i> 서비스 소개
                </a>

                <a href="/#pricing" class="text-dark text-decoration-none fw-medium fs-5 px-3 py-2 rounded" style="background-color: #ffffff; border: 1px solid #e2e8f0;">
                    <i class="fa-solid fa-won-sign text-muted me-2"></i> Price
                </a>
                
                <a href="/faq" class="text-dark text-decoration-none fw-medium fs-5 px-3 py-2 rounded" style="background-color: #ffffff; border: 1px solid #e2e8f0;">
                    <i class="fa-solid fa-question-circle text-muted me-2"></i> FAQ
                </a>

                <?php if ($is_member): ?>
                    <?php if ($is_admin): ?>
                        <a href="/admin" class="text-dark text-decoration-none fw-medium fs-5 px-3 py-2 rounded" style="background-color: #ffffff; border: 1px solid #e2e8f0;">
                            <i class="fa-solid fa-gauge-high text-muted me-2"></i> Admin Panel
                        </a>
                    <?php endif; ?>
                    
                    <a href="/vendor/settings" class="text-dark text-decoration-none fw-medium fs-5 px-3 py-2 rounded" style="background-color: #ffffff; border: 1px solid #e2e8f0;">
                        <i class="fa-solid fa-building text-muted me-2"></i> SaaS Settings
                    </a>
                    
                    <a href="/mypage" class="text-dark text-decoration-none fw-medium fs-5 px-3 py-2 rounded" style="background-color: #ffffff; border: 1px solid #e2e8f0;">
                        <i class="fa-solid fa-circle-user text-muted me-2"></i> My Page
                    </a>
                    
                    <div class="mt-4 pt-4 border-top">
                        <div class="mb-3 text-muted fs-7 text-center">
                            <i class="fa-solid fa-user"></i> <?= htmlspecialchars($user['username']) ?> 님 환영합니다
                        </div>
                        <a href="/logout" class="btn btn-secondary w-100 fs-5 py-2">Logout</a>
                    </div>
                <?php else: ?>
                    <div class="mt-4 d-flex flex-column gap-3">
                        <a href="/login" class="btn btn-outline-secondary w-100 fs-5 py-2" style="background-color: #ffffff;">Login</a>
                        <a href="/register" class="btn text-white w-100 fs-5 py-2" style="background-color: #f16819; border: none;">Sign Up</a>
                    </div>
                <?php endif; ?>
                
            </div>
        </div>
    </div> 
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var navbar = document.getElementById('mainNavbar');
            window.addEventListener('scroll', function() {
                if (window.scrollY > 50) {
                    navbar.classList.add('scrolled');
                } else {
                    navbar.classList.remove('scrolled');
                }
            });
        });
    </script>
