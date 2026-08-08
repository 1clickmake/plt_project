<?php $title = 'Member Management'; include_admin_header($title); ?>

<div class="glass-card">
    <div class="admin-header-flex">
        <h1>Member Management</h1>
        <button class="btn btn-primary" onclick="$('#createUserForm').slideToggle(); $('#editUserForm').slideUp();">+ New Member</button>
    </div>

    <!-- Create User Form -->
    <div id="createUserForm" class="glass-card" style="display: none; background: rgba(15, 23, 42, 0.4); margin-bottom: 2rem;">
        <h2 style="margin-bottom: 1.5rem;">Register New Member</h2>
        <form action="/admin/users/create" method="POST">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            <div class="admin-grid" style="margin-bottom: 1.5rem;">
                <div class="form-group">
                    <label class="form-label">User ID</label>
                    <input type="text" name="user_id" class="form-control" required placeholder="Enter user ID">
                </div>
                <div class="form-group">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-control" required placeholder="Enter username">
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required placeholder="Enter email address">
                </div>
                <div class="form-group">
                    <label class="form-label">Role</label>
                    <select name="role" class="form-select">
                        <option value="user">USER</option>
                        <option value="admin">ADMIN</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required placeholder="Enter password">
                </div>
                <div class="form-group">
                    <label class="form-label">Country</label>
                    <input type="text" name="country" class="form-control" placeholder="KR, US, etc.">
                </div>
                <div class="form-group">
                    <label class="form-label">Point</label>
                    <input type="number" name="point" class="form-control" value="0">
                </div>
                <div class="form-group">
                    <label class="form-label">Level (1-10)</label>
                    <select name="level" class="form-select">
                        <?php for($i=1; $i<=10; $i++): ?>
                            <option value="<?= $i ?>" <?= $i === 1 ? 'selected' : '' ?>>Level <?= $i ?> <?= $i === 10 ? '(Super Admin)' : '' ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>
            <div style="display: flex; gap: 1rem;">
                <button type="submit" class="btn btn-primary">Create Member</button>
                <button type="button" class="btn" style="background: rgba(255,255,255,0.1); color: white;" onclick="$('#createUserForm').slideUp()">Cancel</button>
            </div>
        </form>
    </div>

    <div id="editUserForm" class="glass-card" style="display: none; background: rgba(15, 23, 42, 0.4); margin-bottom: 2rem;">
        <h2 id="editFormTitle" style="margin-bottom: 1.5rem;">Edit Member</h2>
        <form action="/admin/users/update" method="POST">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            <input type="hidden" name="id" id="edit-id">
            <div class="admin-grid" style="margin-bottom: 1.5rem;">
                <div class="form-group">
                    <label class="form-label">User ID</label>
                    <input type="text" name="user_id" id="edit-userid" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" id="edit-username" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" id="edit-email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Role</label>
                    <select name="role" id="edit-role" class="form-select">
                        <option value="user">USER</option>
                        <option value="admin">ADMIN</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">New Password (leave blank to keep current)</label>
                    <input type="password" name="password" class="form-control" placeholder="Optional">
                </div>
                <div class="form-group">
                    <label class="form-label">Country</label>
                    <input type="text" name="country" id="edit-country" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Point</label>
                    <input type="number" name="point" id="edit-point" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Level (1-10)</label>
                    <select name="level" id="edit-level" class="form-select">
                        <?php for($i=1; $i<=10; $i++): ?>
                            <option value="<?= $i ?>">Level <?= $i ?> <?= $i === 10 ? '(Super Admin)' : '' ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>
            <div style="display: flex; gap: 1rem;">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <button type="button" class="btn" style="background: rgba(255,255,255,0.1); color: white;" onclick="$('#editUserForm').slideUp()">Cancel</button>
            </div>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-dark table-hover table-striped text-center">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Point/Level</th>
                    <th>Country</th>
                    <th>Joined At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= $user['id'] ?></td>
                        <td class="text-primary-weight"><?= htmlspecialchars($user['user_id']) ?></td>
                        <td style="font-weight: 600;"><?= htmlspecialchars($user['username']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td>
                            <span class="badge-role" style="background: <?= $user['role'] === 'admin' ? '#6366f1' : 'rgba(255,255,255,0.1)' ?>;">
                                <?= strtoupper($user['role']) ?>
                            </span>
                        </td>
                        <td>
                            <div class="text-muted-small" style="line-height: 1.4;">
                                <span style="color: #fbbf24;"><i class="fa-solid fa-coins"></i> <?= number_format($user['point']) ?></span><br>
                                <span style="color: #60a5fa;"><i class="fa-solid fa-shield"></i> LV.<?= $user['level'] ?></span>
                            </div>
                        </td>
                        <td>
                            <div style="display: flex; align-items: center; justify-content: center; gap: 8px;">
                                <img src="https://flagcdn.com/20x15/<?= strtolower($user['country'] === 'Unknown' ? 'un' : $user['country']) ?>.png" 
                                     onerror="this.src='https://flagcdn.com/20x15/un.png'"
                                     style="border-radius: 2px;">
                                <span class="text-muted-small"><?= htmlspecialchars($user['country']) ?></span>
                            </div>
                        </td>
                        <td class="text-muted-small"><?= $user['created_at'] ?></td>
                        <td>
                            <div style="display: flex; gap: 1rem; justify-content: center;">
                                <button onclick='editUser(<?= json_encode($user) ?>)' style="background: none; border: none; color: var(--primary); cursor: pointer; font-size: 0.8rem;">Edit</button>
                                <form action="/admin/users/delete" method="POST" onsubmit="return confirm('Are you sure you want to delete this user?')" style="display: inline;">
                                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                                    <input type="hidden" name="id" value="<?= $user['id'] ?>">
                                    <button type="submit" style="background: none; border: none; color: #ef4444; cursor: pointer; font-size: 0.8rem;">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    <?= get_pagination($page, $totalPages) ?>
</div>

<script>
    $('#link-users').addClass('active');

    function editUser(user) {
        $('#createUserForm').slideUp();
        $('#edit-id').val(user.id);
        $('#edit-userid').val(user.user_id);
        $('#edit-username').val(user.username);
        $('#edit-email').val(user.email);
        $('#edit-role').val(user.role);
        $('#edit-country').val(user.country);
        $('#edit-point').val(user.point);
        $('#edit-level').val(user.level);
        $('#editUserForm').slideDown();
        $('html, body').animate({ scrollTop: 0 }, 'slow');
    }
</script>

<?php include_admin_footer(); ?>
