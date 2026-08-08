<?php $title = $board['title']; include_header($title, $siteConfig); ?>
<style><?php include CM_BOARD_SKINS_PATH . '/blog/style.css'; ?></style>

<div class="container blog-container">
    <div class="blog-header">
        <div>
            <h1 class="blog-title"><?= htmlspecialchars($board['title']) ?> 📸</h1>
            <p class="blog-desc"><?= htmlspecialchars($board['description'] ?? '') ?></p>
        </div>
        <div style="display: flex; gap: 0.5rem; align-items: center;">
            <?php if ($is_member): ?>
                <?php if ($is_admin): ?>
                    <div class="admin-controls">
                        <div class="form-check" style="margin-bottom: 0;">
                            <input type="checkbox" id="check-all" class="form-check-input">
                            <label class="form-check-label admin-label" for="check-all">Select All</label>
                        </div>
                        <button class="btn btn-sm btn-danger" id="btn-bulk-delete" style="display: none;" onclick="bulkDelete()">Delete Selected</button>
                    </div>
                <?php endif; ?>
                <a href="/board/write/<?= $board['slug'] ?>" class="btn btn-primary">📷 Upload Post</a>
            <?php endif; ?>
        </div>
    </div>

    <!-- ... (Search Bar) ... -->

    <!-- Gallery Grid -->
    <?php if (empty($posts)): ?>
    <div class="glass-card blog-empty">
        <?= !empty($search) ? 'No search results found.' : 'No posts yet. Be the first to upload!' ?>
    </div>
    <?php else: ?>
    <div class="blog-grid">
        <?php foreach ($posts as $post): ?>
            <?php
            // ... (Thumbnail and summary logic) ...
            // 1. Get thumbnail from DB first_file, fallback to content extraction
            $thumbnail = $post['first_file'] ?? null;
            if (!$thumbnail) {
                preg_match('/<img[^>]+src="([^">]+)"/i', $post['content'], $matches);
                $thumbnail = $matches[1] ?? null;
            }
            
            // 2. Text summary (refined for blog style)
            // Decode entities first in case tags are stored as &lt;p&gt;
            $decoded_content = htmlspecialchars_decode($post['content']);
            $content_summary = mb_strimwidth(strip_tags($decoded_content), 0, 160, '...');
            ?>
            <div class="blog-item" onclick="location.href='/board/view/<?= $post['id'] ?>'" style="position: relative;">
                <?php if ($is_admin): ?>
                    <div style="position: absolute; top: 10px; left: 10px; z-index: 10;" onclick="event.stopPropagation();">
                        <input type="checkbox" name="post_ids[]" value="<?= $post['id'] ?>" class="form-check-input post-checkbox" style="width: 20px; height: 20px; box-shadow: 0 0 10px rgba(0,0,0,0.5);">
                    </div>
                <?php endif; ?>

                <?php if ($thumbnail): ?>
                    <div class="blog-image" style="background-image: url('<?= htmlspecialchars($thumbnail) ?>');"></div>
                <?php endif; ?>
                
                <div class="blog-info">
                    <h3 class="blog-post-title"><?= htmlspecialchars($post['title']) ?></h3>
                    <p class="blog-post-summary"><?= htmlspecialchars($content_summary) ?></p>
                    
                    <div class="blog-meta">
                        <!-- ... meta info ... -->
                        <div class="meta-left">
                            <span><i class="fa-solid fa-user"></i> <?= htmlspecialchars($post['username']) ?></span>
                            <span><i class="fa-solid fa-clock"></i> <?= date('Y.m.d', strtotime($post['created_at'])) ?></span>
                        </div>
                        <div class="meta-right">
                            <span><i class="fa-solid fa-eye"></i> <?= number_format($post['views'] ?? 0) ?></span>
                            <span><i class="fa-solid fa-comment"></i> <?= number_format($post['comment_count']) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <?= get_pagination($page, $totalPages, $search ?? '', $pageButtons ?? 5) ?>
    <?php endif; ?>
</div>

<script>
$(document).ready(function() {
    $('#check-all').on('change', function() {
        $('.post-checkbox').prop('checked', $(this).prop('checked'));
        toggleDeleteButton();
    });

    $(document).on('change', '.post-checkbox', function() {
        if ($('.post-checkbox:checked').length === $('.post-checkbox').length) {
            $('#check-all').prop('checked', true);
        } else {
            $('#check-all').prop('checked', false);
        }
        toggleDeleteButton();
    });

    function toggleDeleteButton() {
        if ($('.post-checkbox:checked').length > 0) {
            $('#btn-bulk-delete').fadeIn();
        } else {
            $('#btn-bulk-delete').fadeOut();
        }
    }
});

function bulkDelete() {
    const selectedIds = [];
    $('.post-checkbox:checked').each(function() {
        selectedIds.push($(this).val());
    });

    if (selectedIds.length === 0) return;

    if (confirm('Delete ' + selectedIds.length + ' selected posts? All associated files and comments will be permanently removed.')) {
        const form = $('<form action="/board/bulk-delete" method="POST"></form>');
        form.append('<input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">');
        form.append('<input type="hidden" name="slug" value="<?= $board['slug'] ?>">');
        selectedIds.forEach(id => {
            form.append('<input type="hidden" name="post_ids[]" value="' + id + '">');
        });
        $('body').append(form);
        form.submit();
    }
}
</script>

<?php include_footer($siteConfig); ?>
