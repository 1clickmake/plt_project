<?php
include CM_LAYOUT_PATH . '/header.php';
?>
    <nav class="navbar">
        <!-- Mobile Left: Login Icons -->
        <div class="mobile-user-actions">
            <?php if ($is_member): ?>
                <a href="/mypage" title="My Page"><i class="fa-regular fa-user"></i></a>
            <?php else: ?>
                <a href="/login" title="Login"><i class="fa-regular fa-user"></i></a>
            <?php endif; ?>
        </div>

        <!-- PC Left Dummy (Not needed in grid but helps spacing in PC if flex) -->
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
    <div class="offcanvas offcanvas-end" tabindex="-1" id="mobileMenu" aria-labelledby="mobileMenuLabel" style="z-index: 9999;">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="mobileMenuLabel">MENU</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <div class="d-flex flex-column">
                
                <!-- User Profile / Auth Links -->
                <?php if ($is_member): ?>
                    <div style="background: rgba(255,255,255,0.05); padding: 1.25rem; border-radius: 12px; margin-bottom: 1.5rem; text-align: center; border: 1px solid rgba(255,255,255,0.1);">
                        <div style="display: flex; align-items: center; justify-content: center; gap: 10px; margin-bottom: 1rem;">
                            <i class="fa-solid fa-circle-user" style="font-size: 2rem; color: var(--text-muted);"></i>
                            <div style="text-align: left;">
                                <div style="font-size: 1rem; font-weight: 600; color: white;">
                                    <?= htmlspecialchars($user['username']) ?>
                                </div>
                                <div style="font-size: 0.75rem; color: var(--text-muted);">
                                    <?= $is_super ? 'Super Admin' : ($is_admin ? 'Administrator' : 'Member') ?>
                                </div>
                            </div>
                        </div>
                        <div style="display: flex; gap: 0.5rem;">
                            <?php if ($is_admin): ?>
                                <a href="/admin" class="btn btn-sm btn-primary w-100">Admin</a>
                            <?php endif; ?>
                            <a href="/vendor/settings" class="btn btn-sm w-100" style="background: #10b981; color: white; border: none;">SaaS Set</a>
                            <a href="/mypage" class="btn btn-sm w-100" style="background: #a855f7; color: white; border: none;">My Page</a>
                            <a href="/logout" class="btn btn-sm btn-danger w-100">Logout</a>
                        </div>
                    </div>
                <?php else: ?>
                    <div style="background: rgba(255,255,255,0.05); padding: 1.25rem; border-radius: 12px; margin-bottom: 1.5rem; border: 1px solid rgba(255,255,255,0.1); text-align: center;">
                        <h6 style="color: var(--text-muted); margin-bottom: 1rem; font-size: 0.9rem;">Welcome to <?= htmlspecialchars($siteConfig['site_name']) ?></h6>
                        <div style="display: flex; gap: 0.5rem;">
                            <a href="/login" class="btn btn-primary w-100" style="background: var(--primary); border: none;">Login</a>
                            <a href="/register" class="btn w-100" style="background: #a855f7; color: white; border: none;">Sign Up</a>
                        </div>
                    </div>
                <?php endif; ?>

                <a href="/" class="mobile-nav-item">
                    <i class="fa-solid fa-house"></i> Home 
                </a>
                <a href="/faq" class="mobile-nav-item">
                    <i class="fa-solid fa-question-circle"></i> FAQ
                </a>
                
                <?php
                // Ensure $groups is available
                if (!isset($groups)) {
                    $db = \App\Core\Database::getInstance();
                    $groups = $db->query("SELECT * FROM board_groups ORDER BY id ASC")->fetchAll();
                    foreach ($groups as &$group) {
                        $stmt = $db->prepare("SELECT * FROM boards WHERE group_id = :id ORDER BY id ASC");
                        $stmt->execute(['id' => $group['id']]);
                        $group['boards'] = $stmt->fetchAll();
                    }
                }
                ?>
                
                <?php foreach ($groups as $group): ?>
                    <div style="margin: 1rem 0 0.5rem; padding-left: 1rem; color: var(--text-muted); font-size: 0.8rem; text-transform: uppercase;">
                        <?= htmlspecialchars($group['name']) ?>
                    </div>
                    <?php foreach ($group['boards'] as $board): ?>
                        <a href="/board/<?= $board['slug'] ?>" class="mobile-nav-item">
                            <i class="fa-solid fa-list-ul"></i> <?= htmlspecialchars($board['title']) ?>
                        </a>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div> 
