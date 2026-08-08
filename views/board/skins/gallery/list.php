<?php $title = $board['title']; include_header($title, $siteConfig); ?>
<style><?php include CM_BOARD_SKINS_PATH . '/gallery/style.css'; ?></style>

<div class="container gallery-container">
    <div class="gallery-header">
        <div>
            <h1 class="gallery-title"><?= htmlspecialchars($board['title']) ?> 📸</h1>
            <p class="gallery-desc"><?= htmlspecialchars($board['description'] ?? '') ?></p>
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
    <div class="glass-card gallery-empty">
        <?= !empty($search) ? 'No search results found.' : 'No posts yet. Be the first to upload!' ?>
    </div>
    <?php else: ?>
    <div class="gallery-grid">
        <?php foreach ($posts as $post): ?>
            <?php
            // ... (Thumbnail logic) ...
            $thumbnail = get_thumbnail($post, '/images/no-img.png');
            ?>
            <div class="gallery-item" onclick="location.href='/board/view/<?= $post['id'] ?>'" style="position: relative;">
                <?php if ($is_admin): ?>
                    <div style="position: absolute; top: 10px; left: 10px; z-index: 10;" onclick="event.stopPropagation();">
                        <input type="checkbox" name="post_ids[]" value="<?= $post['id'] ?>" class="form-check-input post-checkbox" style="width: 20px; height: 20px; box-shadow: 0 0 10px rgba(0,0,0,0.5);">
                    </div>
                <?php endif; ?>

                <div class="gallery-image" style="background-image: url('<?= htmlspecialchars($thumbnail) ?>');">
                    <div class="gallery-overlay">
                        <div class="gallery-stats">
                            <span><i class="fa-solid fa-eye"></i> <?= $post['views'] ?? 0 ?></span>
                            <span><i class="fa-solid fa-comment"></i> <?= $post['comment_count'] ?></span>
                        </div>
                    </div>
                </div>
                <div class="gallery-info">
                    <h4><?= htmlspecialchars($post['title']) ?></h4>
                    <div class="gallery-meta">
                        <span><i class="fa-solid fa-user"></i> <?= htmlspecialchars($post['username']) ?></span>
                        <span><i class="fa-solid fa-clock"></i> <?= date('M d', strtotime($post['created_at'])) ?></span>
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
