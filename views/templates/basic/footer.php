<footer class="premium-footer">
    <div class="container">
        <div class="footer-container">
            <!-- Brand Column -->
            <div class="footer-brand">
                <a href="/" class="footer-logo" style="display: inline-block; margin-bottom: 1.5rem;">
                    <?php if (($siteConfig['logo_type'] ?? 'text') === 'image' && !empty($siteConfig['logo_image'])): ?>
                        <img src="<?= $siteConfig['logo_image'] ?>" alt="<?= htmlspecialchars($siteConfig['site_name']) ?>" style="max-height: 40px;">
                    <?php else: ?>
                        <span style="font-size: 1.5rem; font-weight: 800; color: var(--text-main);">
                            <?= htmlspecialchars(!empty($siteConfig['logo_text']) ? $siteConfig['logo_text'] : ($siteConfig['site_name'] ?? 'NEURON AI')) ?>
                        </span>
                    <?php endif; ?>
                </a>
                <p class="footer-desc">
                    Experience the next generation of PHP development with Neuron AI Framework. 
                    Premium design components and seamless high-performance architecture.
                </p>
            </div>

            <!-- Navigation Column -->
            <div class="footer-nav">
                <h4 class="footer-section-title">Legal & Info</h4>
                <ul class="footer-links">
                    <?php if (!empty($footerPages)): ?>
                        <?php foreach ($footerPages as $fPage): ?>
                            <li><a href="/page/<?= $fPage['slug'] ?>"><?= htmlspecialchars($fPage['title']) ?></a></li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li><a href="/page/about-us">About Us</a></li>
                        <li><a href="/page/terms-of-service">Terms of Service</a></li>
                        <li><a href="/page/privacy-policy">Privacy Policy</a></li>
                    <?php endif; ?>
                </ul>
            </div>

            <!-- Contact Column -->
            <div class="footer-contact">
                <h4 class="footer-section-title">Contact Us</h4>
                <ul class="footer-info-list">
                    <?php if (!empty($siteConfig['company_tel'])): ?>
                        <li class="footer-info-item">
                            <i class="fa-solid fa-phone"></i>
                            <span><?= htmlspecialchars($siteConfig['company_tel']) ?></span>
                        </li>
                    <?php endif; ?>
                    <?php if (!empty($siteConfig['company_email'])): ?>
                        <li class="footer-info-item">
                            <i class="fa-solid fa-envelope"></i>
                            <span><?= htmlspecialchars($siteConfig['company_email']) ?></span>
                        </li>
                    <?php endif; ?>
                    <?php if (!empty($siteConfig['company_address'])): ?>
                        <li class="footer-info-item">
                            <i class="fa-solid fa-location-dot"></i>
                            <span><?= htmlspecialchars($siteConfig['company_address']) ?></span>
                        </li>
                    <?php endif; ?>
                    <?php if (empty($siteConfig['company_tel']) && empty($siteConfig['company_email'])): ?>
                        <li class="footer-info-item">
                            <i class="fa-solid fa-info-circle"></i>
                            <span>Please update company info in admin.</span>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>

        <!-- Company Meta Info -->
        <?php if (!empty($siteConfig['company_name']) || !empty($siteConfig['company_owner'])): ?>
            <div class="company-meta">
                <?php if (!empty($siteConfig['company_name'])): ?>
                    <div class="meta-item"><span class="meta-label">Company:</span> <?= htmlspecialchars($siteConfig['company_name']) ?></div>
                <?php endif; ?>
                <?php if (!empty($siteConfig['company_owner'])): ?>
                    <div class="meta-item"><span class="meta-label">Representative:</span> <?= htmlspecialchars($siteConfig['company_owner']) ?></div>
                <?php endif; ?>
                <?php if (!empty($siteConfig['company_license_num'])): ?>
                    <div class="meta-item"><span class="meta-label">License:</span> <?= htmlspecialchars($siteConfig['company_license_num']) ?></div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="footer-bottom">
            <div class="copyright">
                &copy; <?= date('Y') ?> <?= htmlspecialchars($siteConfig['site_name'] ?? 'Neuron AI PHP') ?>. All rights reserved.
            </div>
            <div class="footer-social">
                <a href="#" class="text-muted"><i class="fa-brands fa-facebook"></i></a>
                <a href="#" class="text-muted"><i class="fa-brands fa-twitter"></i></a>
                <a href="#" class="text-muted"><i class="fa-brands fa-instagram"></i></a>
                <a href="#" class="text-muted"><i class="fa-brands fa-github"></i></a>
            </div>
        </div>
    </div>
</footer>

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<?php
include CM_LAYOUT_PATH . '/footer.php';
?>
