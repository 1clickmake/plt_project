<?php
use App\Core\Database;

/**
 * Latest Gallery Partial
 */

if (!isset($board_slug)) $board_slug = 'gallery';
if (!isset($limit)) $limit = 4;

$db = Database::getInstance();

$stmt = $db->prepare("SELECT * FROM boards WHERE slug = ?");
$stmt->execute([$board_slug]);
$board = $stmt->fetch();

if ($board) {
    $sql = "SELECT p.*, u.username,
            (SELECT filepath FROM post_files WHERE post_id = p.id ORDER BY id ASC LIMIT 1) as first_file,
            (SELECT COUNT(*) FROM comments WHERE post_id = p.id) as comment_count 
            FROM posts p 
            LEFT JOIN users u ON p.user_id = u.user_id
            WHERE p.board_id = ? 
            ORDER BY p.created_at DESC 
            LIMIT " . (int)$limit;
            
    $stmt = $db->prepare($sql);
    $stmt->execute([$board['id']]);
    $posts = $stmt->fetchAll();
?>
<div class="latest-gallery-widget mb-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>
            <i class="fa-solid fa-image me-2 gallery-icon"></i>
            <?= htmlspecialchars($board['title']) ?>
        </h4>
        <a href="/board/<?= $board_slug ?>" class="text-muted text-decoration-none gallery-more">
            More <i class="fa-solid fa-angle-right ms-1"></i>
        </a>
    </div>

    <div class="row g-3">
        <?php foreach ($posts as $post): 
            $thumbnail = get_thumbnail($post, '/images/no-img.png');
        ?>
            <div class="col-6 col-md-3">
                <a href="/board/view/<?= $post['id'] ?>" class="gallery-card-link text-decoration-none">
                    <div class="glass-card p-0 overflow-hidden h-100">
                        <div class="gallery-thumb-wrapper" style="background: url('<?= htmlspecialchars($thumbnail) ?>') center/cover no-repeat;">
                            <?php if ((time() - strtotime($post['created_at'])) < 86400): ?>
                                <span class="badge bg-danger position-absolute gallery-new-badge">N</span>
                            <?php endif; ?>
                        </div>
                        <div class="p-2">
                            <p class="text-dark text-truncate mb-0 gallery-latest-title">
                                <?= htmlspecialchars($post['title']) ?>
                            </p>
                            <div class="d-flex justify-content-between align-items-center mt-1">
                                <span class="text-muted gallery-meta"><?= htmlspecialchars($post['username']) ?></span>
                                <span class="text-muted gallery-meta"><i class="fa-regular fa-comment me-1"></i><?= $post['comment_count'] ?></span>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
        <?php if (empty($posts)): ?>
            <div class="col-12">
                <div class="glass-card text-center p-4 text-muted">No images found.</div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php 
}
?>
