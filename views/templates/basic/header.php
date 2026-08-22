<?php
include CM_LAYOUT_PATH . '/header.php';
?>
    <style>
        .navbar {
            background-color: transparent !important;
            transition: all 0.3s ease;
        }
        .navbar.scrolled {
            background-color: rgba(255, 255, 255, 0.3) !important;
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }
        .navbar .nav-links a, .navbar-brand, .mobile-nav-item, .username-link {
            color: #ffffff !important;
            font-weight: 500;
            transition: color 0.3s ease;
        }
        .navbar.scrolled .nav-links a, .navbar.scrolled .navbar-brand, .navbar.scrolled .mobile-nav-item, .navbar.scrolled .username-link {
            color: #0f172a !important;
        }
        .navbar .nav-links a:hover, .mobile-nav-item:hover {
            color: #f16819 !important;
        }
        
        /* Offcanvas styling */
        #mobileMenu {
            background-color: #F4F6F8 !important;
        }
        #mobileMenu .offcanvas-title {
            color: #1e293b !important;
            font-weight: bold;
        }
        #mobileMenu .mobile-nav-item, #mobileMenu .username-link {
            color: #1e293b !important;
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
                <a href="/faq">FAQ</a>
                <?php if ($is_member): ?>
                    <?php if ($is_admin): ?>
                        <a href="/admin"><i class="fa-solid fa-gauge-high"></i> Admin Panel</a>
                    <?php endif; ?>
                    <a href="/vendor/settings"><i class="fa-solid fa-building"></i> SaaS Settings</a>
                    <a href="/mypage" style="color: var(--text-muted); font-size: 0.85rem; text-decoration: none;" class="username-link">
                        <i class="fa-solid fa-circle-user"></i> <?= htmlspecialchars($user['username']) ?>
                    </a>
                    <a href="/logout" class="btn btn-secondary text-light">Logout</a>
                <?php else: ?>
                    <a href="/login">Login</a>
                    <a href="/register">Sign Up</a>
                <?php endif; ?>
            </div>

            <!-- Hamburger Toggle (Shown on Mobile) -->
            <button class="sidebar-toggle" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileMenu" aria-controls="mobileMenu">
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
