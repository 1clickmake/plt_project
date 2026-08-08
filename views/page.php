<?php $title = $page['title']; include_header($title, $siteConfig); ?>

<div class="container">
    <!-- Breadcrumb or Title -->
    <?php if ($page['display_title'] ?? 1): ?>
    <div class="my-3">
        <div class="mb-3">
            <h1 class="page-title"><?= htmlspecialchars($page['title']) ?></h1>
            <div class="mt-2" style="width: 60px; height: 4px; background: var(--primary); border-radius: 2px;"></div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Page Content Container -->
    <div class="<?= ($page['use_card_style'] ?? 1) ? 'glass-card page-card' : '' ?>">
        <div class="page-content">
            <?= $_raw['page']['content'] ?>
        </div>
    </div>
</div>

<?php include_footer($siteConfig); ?>
