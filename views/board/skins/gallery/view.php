<?php $title = $post['title']; include_header($title, $siteConfig); ?> 
<style><?php include CM_BOARD_SKINS_PATH . '/gallery/style.css'; ?></style>

<div class="container gallery-container">
    <!-- Post Header -->
    <div class="glass-card post-header-card">
        <div class="post-header-border">
            <h1 class="post-view-title"><?= htmlspecialchars($post['title']) ?></h1>
            <div class="post-meta-container">
                <div class="post-meta-left">
                    <span><i class="fa-solid fa-user"></i> <?= htmlspecialchars($post['username']) ?></span>
                    <span><i class="fa-solid fa-clock"></i> <?= date('Y-m-d H:i', strtotime($post['created_at'])) ?></span>
                    <span><i class="fa-solid fa-eye"></i> Views: <?= $post['views'] ?></span>
                </div>
                <div class="post-actions">
                    <a href="/board/<?= $post['board_slug'] ?>" class="btn btn-list">📋 List</a>
                    <?php if ($is_member && ($user['id'] == $post['user_id'] || $is_admin)): ?>
                        <a href="/board/edit/<?= $post['id'] ?>" class="btn btn-edit">✏️ Edit</a>
                        <form action="/board/delete/<?= $post['id'] ?>" method="POST" onsubmit="return confirm('Delete this post?')" class="delete-form">
                            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                            <button type="submit" class="btn btn-delete">🗑️ Delete</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Post Content -->
        <div class="post-content post-content-view">
            <?= $_raw['post']['content'] ?>

            <!--//이미지파일 업로드시 출력 시작-->
            <?php if (isset($files) && !empty($files)): ?>
                <div style="margin-bottom:1rem;">
                    <?php foreach ($files as $file): ?>
                        <?php 
                        $ext = strtolower(pathinfo($file['filename'], PATHINFO_EXTENSION));
                        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])): 
                        ?>
                            <img src="<?= $file['filepath'] ?>" alt="<?= htmlspecialchars($file['original_name']) ?>" class="responsive-img">
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <!--//이미지파일 업로드시 출력 끝-->
        </div>

        <!-- Attached Files -->
        <?php if (!empty($files)): ?>
        <div class="attached-files-area">
            <h4 class="attached-label">📎 Attached Files</h4>
            <?php foreach ($files as $file): ?>
                <div class="file-download-item">
                    <span class="file-info-text"><?= htmlspecialchars($file['original_name']) ?> (<?= number_format($file['file_size'] / 1024, 1) ?> KB)</span>
                    <a href="/board/download/<?= $file['id'] ?>" class="btn btn-primary btn-download">Download</a>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>


    <!-- Comments (댓글) Section -->
    <?php if ($post['allow_comments']): ?>
    <div class="glass-card">
        <h3 class="comments-header">💬 Comments</h3>
        
        <!-- Comment Write Form -->
        <?php if ($is_member): ?>
        <div class="comment-write-box">
            <textarea id="comment-input" class="form-control comment-textarea" rows="3" placeholder="Write a comment..."></textarea>
            <button onclick="addComment()" class="btn btn-primary">Add Comment</button>
        </div>
        <?php else: ?>
        <div class="login-request-box">
            <a href="/login" class="login-link">Login</a> to leave a comment.
        </div>
        <?php endif; ?>

        <!-- Comment List -->
        <div id="comment-list">
            <?php if (empty($comments)): ?>
                <div class="no-comments">No comments yet. Be the first to comment!</div>
            <?php else: ?>
                <?php foreach ($comments as $comment): ?>
                <div class="comment-item">
                    <div class="comment-header">
                        <div>
                            <div class="comment-author"><?= htmlspecialchars($comment['username']) ?></div>
                            <div class="comment-date"><?= date('Y-m-d H:i', strtotime($comment['created_at'])) ?></div>
                        </div>
                        <div class="comment-actions">
                            <?php if ($is_member && count($comment['sub_comments']) < ($post['max_replies'] ?? 3)): ?>
                                <button onclick="showReplyBox(<?= $comment['id'] ?>)" class="btn-reply-action">↩️ Reply</button>
                            <?php endif; ?>
                            <?php if ($is_member && ($user['id'] == $comment['user_id'] || $is_admin)): ?>
                                <button onclick="deleteComment(<?= $comment['id'] ?>)" class="btn-delete-action">🗑️</button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="comment-text"><?= nl2br(htmlspecialchars($comment['content'])) ?></div>

                    <!-- Reply Box -->
                    <div id="reply-box-<?= $comment['id'] ?>" class="reply-box">
                        <textarea id="reply-input-<?= $comment['id'] ?>" class="form-control reply-input" rows="2" placeholder="Write a reply..."></textarea>
                        <div class="reply-actions">
                            <button onclick="addSubComment(<?= $comment['id'] ?>)" class="btn btn-primary btn-send-reply">Send</button>
                            <button onclick="hideReplyBox(<?= $comment['id'] ?>)" class="btn btn-cancel-reply">Cancel</button>
                        </div>
                    </div>

                    <!-- Sub-comments -->
                    <?php if (!empty($comment['sub_comments'])): ?>
                        <div class="sub-comments-wrapper">
                            <?php foreach ($comment['sub_comments'] as $sub): ?>
                            <div class="sub-comment-item">
                                <div class="sub-comment-header">
                                    <div>
                                        <span class="sub-comment-author"><?= htmlspecialchars($sub['username']) ?></span>
                                        <span class="sub-comment-date"><?= date('Y-m-d H:i', strtotime($sub['created_at'])) ?></span>
                                    </div>
                                    <?php if ($is_member && ($user['id'] == $sub['user_id'] || $is_admin)): ?>
                                        <button onclick="deleteComment(<?= $sub['id'] ?>)" class="btn-delete-sub">🗑️</button>
                                    <?php endif; ?>
                                </div>
                                <div class="sub-comment-text"><?= nl2br(htmlspecialchars($sub['content'])) ?></div>
                            </div>
                            <?php endforeach; ?>
                            <?php if (count($comment['sub_comments']) >= ($post['max_replies'] ?? 3)): ?>
                            <div class="max-reply-alert">Maximum sub-comments reached (<?= $post['max_replies'] ?? 3 ?>/<?= $post['max_replies'] ?? 3 ?>)</div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Comment Pagination -->
        <?php if ($totalCommentPages > 1): ?>
        <div class="comment-pagination-container">
            <?php for ($i = 1; $i <= $totalCommentPages; $i++): ?>
                <a href="?comment_page=<?= $i ?>" class="btn <?= $i == $commentPage ? 'btn-page-active' : 'btn-page' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<script>
function addComment() {
    const content = $('#comment-input').val().trim();
    if (!content) {
        alert('Please write a comment');
        return;
    }

    $.post('/board/comment/add', {
        post_id: <?= $post['id'] ?>,
        content: content,
        csrf_token: '<?= $csrf_token ?>'
    }).done(function(response) {
        if (response.success) {
            location.reload();
        } else {
            alert(response.error || 'Failed to add comment');
        }
    }).fail(function() {
        alert('Network error');
    });
}

function deleteComment(id) {
    if (!confirm('Delete this comment?')) return;

    $.post('/board/comment/delete', { id: id, csrf_token: '<?= $csrf_token ?>' }).done(function(response) {
        if (response.success) {
            location.reload();
        } else {
            alert(response.error || 'Failed to delete');
        }
    });
}

function showReplyBox(commentId) {
    $('#reply-box-' + commentId).slideDown();
}

function hideReplyBox(commentId) {
    $('#reply-box-' + commentId).slideUp();
    $('#reply-input-' + commentId).val('');
}

function addSubComment(parentId) {
    const content = $('#reply-input-' + parentId).val().trim();
    if (!content) {
        alert('Please write a reply');
        return;
    }

    $.post('/board/comment/add', {
        post_id: <?= $post['id'] ?>,
        parent_comment_id: parentId,
        content: content,
        csrf_token: '<?= $csrf_token ?>'
    }).done(function(response) {
        if (response.success) {
            location.reload();
        } else {
            alert(response.error || 'Failed to add reply');
        }
    }).fail(function() {
        alert('Network error');
    });
}
</script>

<?php include_footer($siteConfig); ?>
