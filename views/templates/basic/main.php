<?php $title = 'Home'; include_header($title, $siteConfig); ?>

<div class="container">
    <div style="text-align: center; margin-bottom: 5rem; margin-top: 3rem;">
        <h1 style="font-size: 3.5rem; font-weight: 700; margin-bottom: 1rem;">PHP <span style="color: var(--primary);">Engine</span> x Neuron AI</h1>
        
        <div style="margin-bottom: 2rem;">
            <span style="background: rgba(99, 102, 241, 0.1); color: var(--primary); padding: 0.5rem 1.2rem; border-radius: 50px; font-size: 0.85rem; font-weight: 600; border: 1px solid rgba(99, 102, 241, 0.2);">
                Active Template: <?= ucfirst($siteConfig['template'] ?? 'Basic') ?>
            </span>
            <p style="margin-top: 1rem; color: var(--text-muted); font-style: italic;">
                "Congratulations! Your new template has been successfully initialized and is ready for customization."
            </p>
        </div>

        <p style="color: var(--text-muted); font-size: 1.2rem; max-width: 700px; margin: 0 auto;">
            A lightweight, high-performance PHP 8.1+ framework integration demo. 
            Powered by Neuron AI for intelligent agent orchestration and a glassmorphism admin dashboard.999
        </p>

        
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
        <?php foreach ($groups as $group): ?>
            <div class="glass-card">
                <h2 style="color: var(--primary); margin-bottom: 1rem; border-bottom: 1px solid var(--glass-border); padding-bottom: 0.5rem;">
                    <?= htmlspecialchars($group['name']) ?>
                </h2>
                <p style="color: var(--text-muted); margin-bottom: 1.5rem; font-size: 0.9rem;">
                    <?= htmlspecialchars($group['description']) ?>
                </p>
                
                <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                        <?php foreach ($group['boards'] as $board): ?>
                            <a href="/board/<?= $board['slug'] ?>" class="board-item-link">
                                <div><?= htmlspecialchars($board['title']) ?></div>
                                <div><?= htmlspecialchars($board['description']) ?></div>
                            </a>
                        <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if (empty($groups)): ?>
            <div class="glass-card" style="grid-column: 1 / -1; text-align: center; padding: 5rem;">
                <h3 style="color: var(--text-muted);">No categories available. Please log in as admin to create groups and boards.</h3>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- 최신글 리스트 섹션 (공지사항 & 자유게시판) -->
<div class="container" style="margin-top: 3rem; margin-bottom: 4rem;">
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 2.5rem;">
        <div>
            <?php 
                $board_slug = 'free'; 
                $limit = 5;
                include CM_VIEWS_PATH . '/partials/latest_posts.php'; 
            ?>
        </div>
    </div>
</div>

<!-- 최신 갤러리 섹션 -->
<div class="container" style="margin-bottom: 4rem;">
    <?php 
        $board_slug = 'gallery'; 
        $limit = 4;
        include CM_VIEWS_PATH . '/partials/latest_gallery.php'; 
    ?>
</div>

<!-- 최신 블로그 섹션 -->
<div class="container" style="margin-bottom: 5rem;">
    <?php 
        $board_slug = 'blog'; 
        $limit = 3;
        include CM_VIEWS_PATH . '/partials/latest_blog.php'; 
    ?>
</div>

<!-- 폼메일 섹션 -->
<div class="container">
    <?php include CM_VIEWS_PATH . '/partials/formmail.php'; ?>
</div>

<?php include_footer($siteConfig); ?>
