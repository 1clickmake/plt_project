<?php $title = 'FAQ Management'; include_admin_header($title); ?>

<div class="glass-card">
    <!-- Category Settings Section -->
    <div>
        <div id="categoryConfigForm" class="glass-card mb-3" style="display: none; background: rgba(15, 23, 42, 0.4);">
            <form action="/admin/faq/config" method="POST">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <div class="form-group mb-3">
                    <label class="form-label">Defined Categories</label>
                    <input type="text" name="faq_category" id="faq_category_input" class="form-control" value="<?= htmlspecialchars($config_categories) ?>" placeholder="Member|Point|Board|Other">
                    <small class="text-muted-small">Separate categories with the pipe symbol ( | ).</small>
                </div>
                <button type="submit" class="btn btn-danger">Save Categories</button>
            </form>
        </div>
    </div>

    <div class="admin-header-flex">
        <h1>FAQ List</h1>
        <div>
            <button class="btn btn-primary" onclick="$('#categoryConfigForm').slideToggle()">Manage Categories</button>
            <button class="btn btn-primary" onclick="$('#createFaqForm').slideToggle(); $('#editFaqForm').slideUp();">+ New FAQ</button>
        </div>
    </div>

    <!-- Create FAQ Form -->
    <div id="createFaqForm" class="glass-card" style="display: none; background: rgba(15, 23, 42, 0.4); margin-bottom: 2rem;">
        <h2 style="margin-bottom: 1.5rem;">Add New FAQ</h2>
        <form action="/admin/faq/create" method="POST">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            <input type="hidden" name="faq_category_config" id="hidden_faq_category_config">
            <div style="margin-bottom: 1.5rem;">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label class="form-label">Category</label>
                            <select name="category" id="faq_category_select" class="form-select" required>
                                <option value="">Select Category...</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= htmlspecialchars(trim($cat)) ?>"><?= htmlspecialchars(trim($cat)) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label class="form-label">Display Order</label>
                            <input type="number" name="display_order" class="form-control" value="0">
                        </div>
                    </div>
                </div>
                <div class="form-group mb-3">
                    <label class="form-label">Question</label>
                    <input type="text" name="question" class="form-control" required placeholder="Enter the question">
                </div>
                <div class="form-group mb-3">
                    <label class="form-label">Answer</label>
                    <textarea name="answer" class="form-control" rows="5" required placeholder="Enter the answer..."></textarea>
                </div>
            </div>
            <div style="display: flex; gap: 1rem;">
                <button type="submit" class="btn btn-primary" onclick="syncCategoryConfig('create')">Create FAQ</button>
                <button type="button" class="btn" style="background: rgba(255,255,255,0.1); color: white;" onclick="$('#createFaqForm').slideUp()">Cancel</button>
            </div>
        </form>
    </div>

    <!-- Edit FAQ Form -->
    <div id="editFaqForm" class="glass-card" style="display: none; background: rgba(15, 23, 42, 0.4); margin-bottom: 2rem;">
        <h2 style="margin-bottom: 1.5rem;">Edit FAQ</h2>
        <form action="/admin/faq/update" method="POST">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            <input type="hidden" name="id" id="edit-id">
            <div style="margin-bottom: 1.5rem;">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label class="form-label">Category</label>
                            <select name="category" id="edit-category" class="form-select" required>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= htmlspecialchars(trim($cat)) ?>"><?= htmlspecialchars(trim($cat)) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label class="form-label">Display Order</label>
                            <input type="number" name="display_order" id="edit-order" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="form-group mb-3">
                    <label class="form-label">Question</label>
                    <input type="text" name="question" id="edit-question" class="form-control" required>
                </div>
                <div class="form-group mb-3">
                    <label class="form-label">Answer</label>
                    <textarea name="answer" id="edit-answer" class="form-control" rows="5" required></textarea>
                </div>
            </div>
            <div style="display: flex; gap: 1rem;">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <button type="button" class="btn" style="background: rgba(255,255,255,0.1); color: white;" onclick="$('#editFaqForm').slideUp()">Cancel</button>
            </div>
        </form>
    </div>

    <!--//category sort-->
    <div class="category-nav mb-4">
        <div class="d-flex flex-wrap gap-2">
            <a href="/admin/faq" class="btn <?= empty($currentCategory) ? 'btn-primary' : 'btn-outline-primary' ?> btn-sm">All</a>
            <?php foreach ($categories as $cat): $cat = trim($cat); if (!$cat) continue; ?>
                <a href="/admin/faq?category=<?= urlencode($cat) ?>" 
                   class="btn <?= $currentCategory === $cat ? 'btn-primary' : 'btn-outline-primary' ?> btn-sm">
                    <?= htmlspecialchars($cat) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-dark table-hover table-striped text-center">
            <thead>
                <tr>
                    <th style="width: 100px;">Order</th>
                    <th style="width: 120px;">Category</th>
                    <th>Question</th>
                    <th style="width: 150px;">Date</th>
                    <th style="width: 120px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($faqs)): ?>
                    <tr>
                        <td colspan="6" class="p-5 text-muted">No FAQs found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($faqs as $faq): ?>
                        <tr>
                            <td><?= $faq['display_order'] ?></td>
                            <td>
                                <span class="badge-role" style="background: rgba(99, 102, 241, 0.2); border: 1px solid rgba(99, 102, 241, 0.5); color: #818cf8;">
                                    <?= htmlspecialchars($faq['category']) ?>
                                </span>
                            </td>
                            <td class="text-start" style="max-width: 400px; cursor: pointer;" onclick="$(this).closest('tr').next('.faq-answer-row').toggle();">
                                <div style="font-weight: 600;"><?= htmlspecialchars($faq['question']) ?></div>
                                <div class="text-muted-small text-truncate" style="margin-top: 4px;">Learn more...</div>
                            </td>
                            <td class="text-muted-small"><?= date('Y-m-d', strtotime($faq['created_at'])) ?></td>
                            <td>
                                <div style="display: flex; gap: 1rem; justify-content: center;">
                                    <button onclick='editFaq(<?= json_encode($faq) ?>)' style="background: none; border: none; color: var(--primary); cursor: pointer; font-size: 0.8rem;">Edit</button>
                                    <form action="/admin/faq/delete" method="POST" onsubmit="return confirm('Delete this FAQ?')" style="display: inline;">
                                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                                        <input type="hidden" name="id" value="<?= $faq['id'] ?>">
                                        <button type="submit" style="background: none; border: none; color: #ef4444; cursor: pointer; font-size: 0.8rem;">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <tr class="faq-answer-row" style="display: none; background: rgba(15, 23, 42, 0.2);">
                            <td colspan="6" class="text-start p-4">
                                <div style="background: rgba(255,255,255,0.05); padding: 1.5rem; border-radius: 8px; border-left: 4px solid var(--primary);">
                                    <div style="font-weight: 700; margin-bottom: 0.5rem; color: var(--primary);">ANSWER:</div>
                                    <div style="line-height: 1.6; color: #cbd5e1;"><?= nl2br(htmlspecialchars($faq['answer'])) ?></div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?= get_pagination($page, $totalPages, $currentCategory ? "category=" . urlencode($currentCategory) : "") ?>
</div>

<script>
    $('#link-faq').addClass('active');

    function editFaq(faq) {
        $('#createFaqForm').slideUp();
        $('#edit-id').val(faq.id);
        $('#edit-category').val(faq.category);
        $('#edit-question').val(faq.question);
        $('#edit-answer').val(faq.answer);
        $('#edit-order').val(faq.display_order);
        $('#editFaqForm').slideDown();
        $('html, body').animate({ scrollTop: $('#editFaqForm').offset().top - 100 }, 'slow');
    }

    function syncCategoryConfig(mode) {
        $('#hidden_faq_category_config').val($('#faq_category_input').val());
    }

    // Dynamic Category List Update
    document.getElementById('faq_category_input').addEventListener('input', function() {
        const categories = this.value.split('|');
        const selects = ['#faq_category_select', '#edit-category'];
        
        selects.forEach(selector => {
            const select = document.querySelector(selector);
            const currentVal = select.value;
            
            select.innerHTML = selector === '#faq_category_select' ? '<option value="">Select Category...</option>' : '';
            categories.forEach(function(cat) {
                cat = cat.trim();
                if(cat) {
                    const option = document.createElement('option');
                    option.value = cat;
                    option.textContent = cat;
                    if(cat === currentVal) option.selected = true;
                    select.appendChild(option);
                }
            });
        });
    });
</script>

<?php include_admin_footer(); ?>
