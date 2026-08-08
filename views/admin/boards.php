<?php 
$title = 'Board Management'; 
include_admin_header($title);

if (empty($groups)) {
    alert('Please create at least one board group first.', '/admin/groups');
}
?>

<div class="glass-card">
    <div class="admin-header-flex">
        <h1>Boards</h1>
        <button class="btn btn-primary" onclick="$('#editBoardForm').hide(); $('#createBoardForm').slideToggle()">+ New Board</button>
    </div>

    <!-- Create Board Form -->
    <div id="createBoardForm" class="glass-card" style="display: none; background: rgba(15, 23, 42, 0.4); margin-bottom: 2rem;">
        <h2 style="margin-bottom: 1.5rem;">New Board</h2>
        <form action="/admin/boards" method="POST">
            <div class="admin-grid" style="margin-bottom: 1.5rem;">
                <!-- Basic Settings -->
                <div class="form-group">
                    <label class="form-label">Select Group</label>
                    <select name="group_id" class="form-select" required>
                        <?php foreach ($groups as $group): ?>
                            <option value="<?= $group['id'] ?>"><?= htmlspecialchars($group['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Board Title</label>
                    <input type="text" name="title" class="form-control" required placeholder="e.g. Free Topic">
                </div>
                <div class="form-group">
                    <label class="form-label">Slug</label>
                    <input type="text" name="slug" class="form-control" placeholder="e.g. free-topic">
                </div>
                <div class="form-group">
                    <label class="form-label">Skin</label>
                    <select name="skin" class="form-select">
                        <?php foreach ($skins as $skin): ?>
                            <option value="<?= $skin['value'] ?>"><?= htmlspecialchars($skin['label']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Max Replies (답글 제한)</label>
                    <input type="number" name="max_replies" class="form-control" value="3" min="0" max="10">
                </div>
                <div class="form-group">
                    <label class="form-label">Allow Comments</label>
                    <select name="allow_comments" class="form-select">
                        <option value="1">Yes</option>
                        <option value="0">No</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Rows Per Page (목록 출력 수)</label>
                    <input type="number" name="page_rows" class="form-control" value="20" min="1" max="100">
                </div>
                <div class="form-group">
                    <label class="form-label">Page Buttons (페이징 버튼 수)</label>
                    <input type="number" name="page_buttons" class="form-control" value="5" min="1" max="20">
                </div>
            </div>

            <div class="admin-grid" style="margin-bottom: 1.5rem;">
                <!-- Permission Settings -->
                <div style="background: rgba(255,255,255,0.03); padding: 1.5rem; border-radius: 12px; border: 1px solid rgba(255,255,255,0.05);">
                    <h4 class="admin-section-header" style="font-size: 0.9rem; color: var(--primary);"><i class="fa-solid fa-lock"></i> Access Levels (1-10)</h4>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label class="form-label" style="font-size: 0.8rem;">List Level</label>
                            <select name="level_list" class="form-select form-select-sm">
                                <?php for($i=1; $i<=10; $i++): ?><option value="<?= $i ?>"><?= $i ?></option><?php endfor; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label" style="font-size: 0.8rem;">View Level</label>
                            <select name="level_view" class="form-select form-select-sm">
                                <?php for($i=1; $i<=10; $i++): ?><option value="<?= $i ?>"><?= $i ?></option><?php endfor; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label" style="font-size: 0.8rem;">Write Level</label>
                            <select name="level_write" class="form-select form-select-sm">
                                <?php for($i=1; $i<=10; $i++): ?><option value="<?= $i ?>"><?= $i ?></option><?php endfor; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label" style="font-size: 0.8rem;">Comment Level</label>
                            <select name="level_comment" class="form-select form-select-sm">
                                <?php for($i=1; $i<=10; $i++): ?><option value="<?= $i ?>"><?= $i ?></option><?php endfor; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Point Settings -->
                <div style="background: rgba(255,255,255,0.03); padding: 1.5rem; border-radius: 12px; border: 1px solid rgba(255,255,255,0.05);">
                    <h4 class="admin-section-header" style="font-size: 0.9rem; color: #fbbf24;"><i class="fa-solid fa-coins"></i> Point Settings</h4>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label class="form-label" style="font-size: 0.8rem;">Write Post</label>
                            <input type="number" name="point_write" class="form-control form-control-sm" value="0">
                        </div>
                        <div class="form-group">
                            <label class="form-label" style="font-size: 0.8rem;">View Post (+/-)</label>
                            <input type="number" name="point_view" class="form-control form-control-sm" value="0">
                        </div>
                        <div class="form-group" style="grid-column: span 2;">
                            <label class="form-label" style="font-size: 0.8rem;">Write Comment</label>
                            <input type="number" name="point_comment" class="form-control form-control-sm" value="0">
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="2"></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Create Board</button>
        </form>
    </div>

    <!-- Edit Board Form -->
    <div id="editBoardForm" class="glass-card" style="display: none; background: rgba(15, 23, 42, 0.4); margin-bottom: 2rem;">
        <h2 style="margin-bottom: 1.5rem;">Edit Board</h2>
        <form action="/admin/boards/update" method="POST">
            <input type="hidden" name="id" id="edit-id">
            <div class="admin-grid" style="margin-bottom: 1.5rem;">
                <div class="form-group">
                    <label class="form-label">Select Group</label>
                    <select name="group_id" id="edit-group-id" class="form-select" required>
                        <?php foreach ($groups as $group): ?>
                            <option value="<?= $group['id'] ?>"><?= htmlspecialchars($group['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Board Title</label>
                    <input type="text" name="title" id="edit-title" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Slug (Cannot be changed)</label>
                    <input type="text" id="edit-slug" class="form-control" readonly style="opacity: 0.6; cursor: not-allowed;">
                </div>
                <div class="form-group">
                    <label class="form-label">Skin</label>
                    <select name="skin" id="edit-skin" class="form-select">
                        <?php foreach ($skins as $skin): ?>
                            <option value="<?= $skin['value'] ?>"><?= htmlspecialchars($skin['label']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Max Replies (답글 제한)</label>
                    <input type="number" name="max_replies" id="edit-max-replies" class="form-control" min="0" max="10">
                </div>
                <div class="form-group">
                    <label class="form-label">Allow Comments</label>
                    <select name="allow_comments" id="edit-allow-comments" class="form-select">
                        <option value="1">Yes</option>
                        <option value="0">No</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Rows Per Page (목록 출력 수)</label>
                    <input type="number" name="page_rows" id="edit-page-rows" class="form-control" min="1" max="100">
                </div>
                <div class="form-group">
                    <label class="form-label">Page Buttons (페이징 버튼 수)</label>
                    <input type="number" name="page_buttons" id="edit-page-buttons" class="form-control" min="1" max="20">
                </div>
            </div>

            <div class="admin-grid" style="margin-bottom: 1.5rem;">
                <!-- Permission Settings -->
                <div style="background: rgba(255,255,255,0.03); padding: 1.5rem; border-radius: 12px; border: 1px solid rgba(255,255,255,0.05);">
                    <h4 class="admin-section-header" style="font-size: 0.9rem; color: var(--primary);"><i class="fa-solid fa-lock"></i> Access Levels (1-10)</h4>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label class="form-label" style="font-size: 0.8rem;">List Level</label>
                            <select name="level_list" id="edit-level-list" class="form-select form-select-sm">
                                <?php for($i=1; $i<=10; $i++): ?><option value="<?= $i ?>"><?= $i ?></option><?php endfor; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label" style="font-size: 0.8rem;">View Level</label>
                            <select name="level_view" id="edit-level-view" class="form-select form-select-sm">
                                <?php for($i=1; $i<=10; $i++): ?><option value="<?= $i ?>"><?= $i ?></option><?php endfor; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label" style="font-size: 0.8rem;">Write Level</label>
                            <select name="level_write" id="edit-level-write" class="form-select form-select-sm">
                                <?php for($i=1; $i<=10; $i++): ?><option value="<?= $i ?>"><?= $i ?></option><?php endfor; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label" style="font-size: 0.8rem;">Comment Level</label>
                            <select name="level_comment" id="edit-level-comment" class="form-select form-select-sm">
                                <?php for($i=1; $i<=10; $i++): ?><option value="<?= $i ?>"><?= $i ?></option><?php endfor; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Point Settings -->
                <div style="background: rgba(255,255,255,0.03); padding: 1.5rem; border-radius: 12px; border: 1px solid rgba(255,255,255,0.05);">
                    <h4 class="admin-section-header" style="font-size: 0.9rem; color: #fbbf24;"><i class="fa-solid fa-coins"></i> Point Settings</h4>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label class="form-label" style="font-size: 0.8rem;">Write Post</label>
                            <input type="number" name="point_write" id="edit-point-write" class="form-control form-control-sm">
                        </div>
                        <div class="form-group">
                            <label class="form-label" style="font-size: 0.8rem;">View Post (+/-)</label>
                            <input type="number" name="point_view" id="edit-point-view" class="form-control form-control-sm">
                        </div>
                        <div class="form-group" style="grid-column: span 2;">
                            <label class="form-label" style="font-size: 0.8rem;">Write Comment</label>
                            <input type="number" name="point_comment" id="edit-point-comment" class="form-control form-control-sm">
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group mb-4">
                <label class="form-label">Description</label>
                <textarea name="description" id="edit-description" class="form-control" rows="2"></textarea>
            </div>
            <div style="display: flex; gap: 1rem;">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <button type="button" class="btn" style="background: rgba(255,255,255,0.1); color: white;" onclick="$('#editBoardForm').slideUp()">Cancel</button>
            </div>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-dark table-hover table-striped text-center">
            <thead>
                <tr>
                    <th>Group</th>
                    <th>Board Title</th>
                    <th>Slug</th>
                    <th>Skin</th>
                    <th>Max Replies</th>
                    <th>Comments</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($boards as $board): ?>
                    <tr>
                        <td><span class="text-primary-weight" style="font-size: 0.8rem;"><?= htmlspecialchars($board['group_name']) ?></span></td>
                        <td style="font-weight: 600;">
                            <a href="/board/<?= $board['slug'] ?>" target="_blank" style="color: inherit; text-decoration: none;" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='inherit'">
                                <?= htmlspecialchars($board['title']) ?>
                                <i class="fa-solid fa-arrow-up-right-from-square" style="font-size: 0.7rem; margin-left: 5px; opacity: 0.5;"></i>
                            </a>
                        </td>
                        <td><code><?= htmlspecialchars($board['slug']) ?></code></td>
                        <td><span class="badge-role" style="background: rgba(99, 102, 241, 0.2); color: var(--primary);"><?= strtoupper($board['skin'] ?? 'basic') ?></span></td>
                        <td class="text-muted-small"><?= $board['max_replies'] ?? 3 ?></td>
                        <td><?= ($board['allow_comments'] ?? 1) ? '<i class="fa-solid fa-check text-success"></i>' : '<i class="fa-solid fa-xmark text-danger"></i>' ?></td>
                        <td class="text-muted-small"><?= $board['created_at'] ?></td>
                        <td>
                            <div style="display: flex; gap: 1rem; justify-content: center;">
                                <button onclick='editBoard(<?= json_encode($board) ?>)' style="background: none; border: none; color: var(--primary); cursor: pointer; font-size: 0.8rem;">Edit</button>
                                <form action="/admin/boards/delete" method="POST" onsubmit="return confirm('Delete this board?')" style="display: inline;">
                                    <input type="hidden" name="id" value="<?= $board['id'] ?>">
                                    <button type="submit" style="background: none; border: none; color: #ef4444; cursor: pointer; font-size: 0.8rem;">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    $('#link-boards').addClass('active');

    function editBoard(board) {
        $('#edit-id').val(board.id);
        $('#edit-group-id').val(board.group_id);
        $('#edit-title').val(board.title);
        $('#edit-slug').val(board.slug);
        $('#edit-description').val(board.description);
        $('#edit-skin').val(board.skin || 'basic');
        $('#edit-max-replies').val(board.max_replies || 3);
        $('#edit-allow-comments').val(board.allow_comments !== undefined ? board.allow_comments : 1);
        $('#edit-page-rows').val(board.page_rows || 20);
        $('#edit-page-buttons').val(board.page_buttons || 5);
        $('#edit-level-list').val(board.level_list || 1);
        $('#edit-level-view').val(board.level_view || 1);
        $('#edit-level-write').val(board.level_write || 1);
        $('#edit-level-comment').val(board.level_comment || 1);
        $('#edit-point-write').val(board.point_write || 0);
        $('#edit-point-view').val(board.point_view || 0);
        $('#edit-point-comment').val(board.point_comment || 0);
        $('#createBoardForm').hide();
        $('#editBoardForm').slideDown();
        $('html, body').animate({ scrollTop: 0 }, 'slow');
    }
</script>

<?php include_admin_footer(); ?>
