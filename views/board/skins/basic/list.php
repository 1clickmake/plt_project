<?php $title = $board['title']; include_header($title, $siteConfig); ?>

<div class="container basic-container">
    <div class="basic-list-header">
        <div>
            <h1 class="basic-title"><?= htmlspecialchars($board['title']) ?></h1>
            <p class="basic-desc"><?= htmlspecialchars($board['description'] ?? '') ?></p>
        </div>
        <div style="display: flex; gap: 0.5rem; align-items: center;">
            <?php if ($is_member): ?>
                <?php if ($is_admin): ?>
                    <button class="btn btn-danger" id="btn-bulk-delete" style="display: none;" onclick="bulkDelete()">Delete Selected</button>
                <?php endif; ?>
                <a href="/board/write/<?= $board['slug'] ?>" class="btn btn-primary">✏️ Write Post</a>
            <?php endif; ?>
        </div>
    </div>

    <!-- ... (Search Bar) ... -->

    <div class="glass-card">
		<div class="table-responsive">
			<table class=" basic-table" style="min-width:1000px;">
				<thead class="basic-table-head">
					<tr>
						<?php if ($is_admin): ?>
							<th style="width: 40px;"><input type="checkbox" id="check-all" class="form-check-input"></th>
						<?php endif; ?>
						<th class="col-no">No.</th>
						<th>Title</th>
						<th class="col-author">Author</th>
						<th class="col-views">Views</th>
						<th class="col-replies">Replies</th>
						<th class="col-comments">Comments</th>
						<th class="col-date">Date</th>
					</tr>
				</thead>
				<tbody>
					<?php if (empty($posts)): ?>
					<tr>
						<td colspan="<?= ($is_admin) ? 8 : 7 ?>" class="empty-state">
							<?= !empty($search) ? 'No search results found.' : 'No posts yet. Be the first to write!' ?>
						</td>
					</tr>
					<?php else: ?>
						<?php foreach ($posts as $idx => $post): ?>
						<tr class="post-row" onclick="location.href='/board/view/<?= $post['id'] ?>'">
							<?php if ($is_admin): ?>
								<td onclick="event.stopPropagation();"><input type="checkbox" name="post_ids[]" value="<?= $post['id'] ?>" class="form-check-input post-checkbox"></td>
							<?php endif; ?>
							<td class="cell-no"><?= $totalItems - (($page - 1) * $limit) - $idx ?></td>
							<!-- ... rest of row ... -->
							<td class="cell-title">
								<?= htmlspecialchars($post['title']) ?>
								<?php if ($post['reply_count'] > 0): ?>
									<span class="reply-count">[<?= $post['reply_count'] ?>]</span>
								<?php endif; ?>
							</td>
							<td class="cell-author"><?= htmlspecialchars($post['username']) ?></td>
							<td class="cell-center"><?= $post['views'] ?? 0 ?></td>
							<td class="cell-replies"><?= $post['reply_count']  ?></td>
							<td class="cell-comments"><?= $post['comment_count'] ?></td>
							<td class="cell-date"><?= date('Y-m-d H:i', strtotime($post['created_at'])) ?></td>
						</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
			</table>
        </div>
        
        <!-- Pagination -->
        <?= get_pagination($page, $totalPages, $search ?? '', $pageButtons ?? 5) ?>
    </div>
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
