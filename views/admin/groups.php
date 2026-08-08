<?php $title = 'Board Groups'; include_admin_header($title); ?>

<div class="glass-card">
    <div class="admin-header-flex">
        <h1>Board Groups</h1>
        <button class="btn btn-primary" onclick="$('#editGroupForm').hide(); $('#createGroupForm').slideToggle()">+ New Group</button>
    </div>

    <!-- Create Group Form -->
    <!-- Create Group Form -->
    <div id="createGroupForm" class="glass-card" style="display: none; background: rgba(15, 23, 42, 0.4); margin-bottom: 2rem;">
		<h2 style="margin-bottom: 1.5rem;">New Group</h2>
        <form action="/admin/groups" method="POST">
            <div class="admin-grid" style="margin-bottom: 1.5rem;">
                <div class="form-group">
                    <label class="form-label">Group Name</label>
                    <input type="text" name="name" class="form-control" required placeholder="e.g. Community">
                </div>
                <div class="form-group">
                    <label class="form-label">Slug (Optional)</label>
                    <input type="text" name="slug" class="form-control" placeholder="e.g. community">
                </div>
            </div>
            <div class="form-group mb-4">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="3"></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Create Group</button>
        </form>
    </div>

    <!-- Edit Group Form -->
    <!-- Edit Group Form -->
    <div id="editGroupForm" class="glass-card" style="display: none; background: rgba(15, 23, 42, 0.4); margin-bottom: 2rem;">
        <h2 style="margin-bottom: 1.5rem;">Edit Group</h2>
        <form action="/admin/groups/update" method="POST">
            <input type="hidden" name="id" id="edit-id">
            <div class="admin-grid" style="margin-bottom: 1.5rem;">
                <div class="form-group">
                    <label class="form-label">Group Name</label>
                    <input type="text" name="name" id="edit-name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Slug</label>
                    <input type="text" name="slug" id="edit-slug" class="form-control" required>
                </div>
            </div>
            <div class="form-group mb-4">
                <label class="form-label">Description</label>
                <textarea name="description" id="edit-description" class="form-control" rows="3"></textarea>
            </div>
            <div style="display: flex; gap: 1rem;">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <button type="button" class="btn" style="background: rgba(255,255,255,0.1); color: white;" onclick="$('#editGroupForm').slideUp()">Cancel</button>
            </div>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-dark table-hover table-striped text-center">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Slug</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($groups as $group): ?>
                    <tr>
                        <td><?= $group['id'] ?></td>
                        <td class="text-primary-weight"><?= htmlspecialchars($group['name']) ?></td>
                        <td><code><?= htmlspecialchars($group['slug']) ?></code></td>
                        <td class="text-muted-small"><?= htmlspecialchars($group['description']) ?></td>
                        <td>
                            <div style="display: flex; gap: 1rem; justify-content: center;">
                                <button onclick='editGroup(<?= json_encode($group) ?>)' style="background: none; border: none; color: var(--primary); cursor: pointer; font-size: 0.8rem;">Edit</button>
                                <form action="/admin/groups/delete" method="POST" onsubmit="return confirm('Delete this group and all its boards?')" style="display: inline;">
                                    <input type="hidden" name="id" value="<?= $group['id'] ?>">
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
    $('#link-groups').addClass('active');

    function editGroup(group) {
        $('#edit-id').val(group.id);
        $('#edit-name').val(group.name);
        $('#edit-slug').val(group.slug);
        $('#edit-description').val(group.description);
        $('#createGroupForm').slideUp();
        $('#editGroupForm').slideDown();
        $('html, body').animate({ scrollTop: 0 }, 'slow');
    }
</script>

<?php include_admin_footer(); ?>
