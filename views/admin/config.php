<?php $title = 'Site Configuration'; include_admin_header($title); ?>

<div class="admin-sticky-header admin-header-flex">
    <div>
        <h1 style="margin: 0; font-size: 1.5rem; font-weight: 700;">Site Configuration</h1>
        <p class="text-muted-small" style="margin: 0;">Manage your website's global settings, logo, and company information.</p>
    </div>
    <button type="submit" form="configForm" class="btn btn-primary" style="box-shadow: 0 4px 15px rgba(99, 102, 241, 0.4);">
        <i class="fa-solid fa-save"></i> Save Changes
    </button>
</div>

<div class="glass-card">

    <form id="configForm" action="/admin/config" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
        
        <!-- Section 1: General Identity -->
        <h3 class="admin-section-header"><i class="fa-solid fa-globe"></i> General Identity</h3>
        <div class="admin-section-container">
            <div class="form-group mb-4">
                <label class="form-label">Site Name</label>
                <input type="text" name="site_name" class="form-control form-control-lg" value="<?= htmlspecialchars($config['site_name'] ?? '') ?>" placeholder="e.g. My Awesome Site" required>
                <small class="help-text">This name will appear in the browser title bar and footer.</small>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <label class="form-label mb-3">Logo Type</label>
                    <div class="flex-gap-1" style="margin-bottom: 1.5rem;">
                        <label class="selection-card <?= ($config['logo_type'] ?? 'text') === 'text' ? 'active' : '' ?>" onclick="selectLogoType('text')">
                            <input type="radio" name="logo_type" value="text" <?= ($config['logo_type'] ?? 'text') === 'text' ? 'checked' : '' ?> style="display: none;">
                            <i class="fa-solid fa-font fa-2x mb-2"></i>
                            <span>Text Logo</span>
                        </label>
                        <label class="selection-card <?= ($config['logo_type'] ?? 'text') === 'image' ? 'active' : '' ?>" onclick="selectLogoType('image')">
                            <input type="radio" name="logo_type" value="image" <?= ($config['logo_type'] ?? 'text') === 'image' ? 'checked' : '' ?> style="display: none;">
                            <i class="fa-solid fa-image fa-2x mb-2"></i>
                            <span>Image Logo</span>
                        </label>
                    </div>
                </div>

                <div class="col-md-12">
                    <!-- Text Logo Input -->
                    <div id="logo-text-box" style="display: <?= ($config['logo_type'] ?? 'text') === 'text' ? 'block' : 'none' ?>;">
                        <label class="form-label">Logo Text</label>
                        <input type="text" name="logo_text" class="form-control" value="<?= htmlspecialchars($config['logo_text'] ?? '') ?>" placeholder="Enter text to display as logo">
                    </div>

                    <!-- Image Logo Input -->
                    <div id="logo-image-box" style="display: <?= ($config['logo_type'] ?? 'text') === 'image' ? 'block' : 'none' ?>;">
                        <label class="form-label">Logo Image Upload</label>
                        <div class="flex-gap-15">
                            <div style="flex: 1;">
                                <input type="file" name="logo_image" class="form-control" accept="image/*">
                                <small class="help-text">Recommended size: Height 40px - 60px (PNG or SVG)</small>
                            </div>
                            <?php if (!empty($config['logo_image'])): ?>
                                <div class="logo-preview-container">
                                    <p class="text-muted-small mb-2">Current Logo</p>
                                    <img src="<?= $config['logo_image'] ?>" style="max-height: 40px; display: block; margin: 0 auto;">
                                    <input type="hidden" name="current_logo_image" value="<?= $config['logo_image'] ?>">
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 2: Appearance -->
        <h3 class="admin-section-header"><i class="fa-solid fa-palette"></i> Appearance</h3>
        <div class="admin-section-container">
            <div class="form-group">
                <label class="form-label">Site Template</label>
                <div class="template-input-group">
                    <input type="text" class="form-control" style="width: auto; flex: 1; max-width: 300px;" value="<?= ucfirst($config['template'] ?? 'basic') ?>" readonly disabled>
                    <select name="template" class="form-select" style="flex: 2; max-width: 400px;">
                        <?php foreach ($templates as $tmpl): ?>
                            <option value="<?= $tmpl['value'] ?>" <?= ($config['template'] ?? 'basic') === $tmpl['value'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($tmpl['label']) ?> Theme
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <small class="help-text">Select the base theme for your website structure (Header/Footer/Layout).</small>
            </div>
        </div>

        <!-- Section 3: Template Builder -->
        <h3 class="admin-section-header"><i class="fa-solid fa-magic"></i> Template Builder</h3>
        <div class="admin-section-container">
            <div class="row align-items-end">
                <div class="col-md-8">
                    <label class="form-label">Create New Template</label>
                    <div class="flex-gap-1">
                        <input type="text" id="new_template_name" class="form-control" placeholder="e.g. basic_luxury" style="flex: 1;">
                        <button type="button" onclick="createNewTemplate()" class="btn btn-success" style="white-space: nowrap;">
                            <i class="fa-solid fa-plus-circle"></i> Create
                        </button>
                    </div>
                    <small class="help-text">
                        Entering a name will automatically copy all files from <code>basic</code> template to: <br>
                        - <code>views/templates/[name]/</code> <br>
                        - <code>public/assets/templates/[name]/</code>
                    </small>
                </div>
                <div class="col-md-4" style="text-align: right;">
                     <div id="template-status" class="status-alert-success">
                        Success! Template created.
                     </div>
                </div>
            </div>
        </div>
        
        <!-- Section 4: Member Info -->
        <h3 class="admin-section-header"><i class="fa-solid fa-user-plus"></i> Member Registration Settings</h3>
        <div class="admin-section-container">
            <div class="admin-grid">
                <div class="form-group">
                    <label class="form-label">Default Join Points</label>
                    <input type="number" name="join_point" class="form-control" value="<?= htmlspecialchars($config['join_point'] ?? 0) ?>" placeholder="Points given on signup">
                    <small class="text-muted-small">Points automatically granted when a new user joins.</small>
                </div>
                <div class="form-group">
                    <label class="form-label">Default Join Level</label>
                    <select name="join_level" class="form-select">
                        <?php for($i=1; $i<=9; $i++): ?>
                            <option value="<?= $i ?>" <?= (isset($config['join_level']) && $config['join_level'] == $i) ? 'selected' : '' ?>>Level <?= $i ?></option>
                        <?php endfor; ?>
                    </select>
                    <small class="text-muted-small">Level automatically assigned on signup (Max 9, Admin is 10).</small>
                </div>
            </div>
        </div>

        <!-- Section 5: Company Info  -->
        <h3 class="admin-section-header"><i class="fa-solid fa-building"></i> Company Information</h3>
        <div class="admin-section-container">
            <div class="admin-grid">
                <div class="form-group">
                    <label class="form-label">Company Name</label>
                    <input type="text" name="company_name" class="form-control" value="<?= htmlspecialchars($config['company_name'] ?? '') ?>" placeholder="e.g. Neuron AI Corp.">
                </div>
                <div class="form-group">
                    <label class="form-label">Representative (Owner)</label>
                    <input type="text" name="company_owner" class="form-control" value="<?= htmlspecialchars($config['company_owner'] ?? '') ?>" placeholder="e.g. John Doe">
                </div>
                <div class="form-group">
                    <label class="form-label">Business License Number</label>
                    <input type="text" name="company_license_num" class="form-control" value="<?= htmlspecialchars($config['company_license_num'] ?? '') ?>" placeholder="e.g. 123-45-67890">
                </div>
                <div class="form-group">
                    <label class="form-label">Telephone</label>
                    <input type="text" name="company_tel" class="form-control" value="<?= htmlspecialchars($config['company_tel'] ?? '') ?>" placeholder="e.g. 02-1234-5678">
                </div>
                <div class="form-group">
                    <label class="form-label">Contact Email</label>
                    <input type="email" name="company_email" class="form-control" value="<?= htmlspecialchars($config['company_email'] ?? '') ?>" placeholder="e.g. contact@example.com">
                </div>
                <div class="form-group">
                    <label class="form-label">Address</label>
                    <input type="text" name="company_address" class="form-control" value="<?= htmlspecialchars($config['company_address'] ?? '') ?>" placeholder="e.g. 123 Gangnam-daero, Seoul">
                </div>
            </div>
        </div>
        <!-- Section 6: Mall Settings -->
        <h3 class="admin-section-header"><i class="fa-solid fa-cart-shopping"></i> Open Market Mall Settings</h3>
        <div class="admin-section-container">
            <div class="admin-grid">
                <div class="form-group">
                    <label class="form-label">Default Mall Commission (%)</label>
                    <div class="input-group">
                        <input type="number" name="mall_commission" class="form-control" value="<?= htmlspecialchars($config['mall_commission'] ?? 10) ?>" min="0" max="100">
                        <span class="input-group-text">%</span>
                    </div>
                    <small class="text-muted-small">기본 판매 수수료입니다. 정산 시 자동으로 차감 계산됩니다.</small>
                </div>
                <div class="form-group">
                    <label class="form-label">Mall Service Name</label>
                    <input type="text" name="mall_name" class="form-control" value="<?= htmlspecialchars($config['mall_name'] ?? 'Open Market') ?>" placeholder="e.g. Neuron Mall">
                    <small class="text-muted-small">쇼핑몰 서비스 전용 노출 명칭입니다.</small>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    $('#link-config').addClass('active');

    function selectLogoType(type) {
        // Visual selection update
        $('.selection-card').removeClass('active');
        if (type === 'text') {
            $('.selection-card:first-child').addClass('active');
            $('#logo-text-box').slideDown(200);
            $('#logo-image-box').slideUp(200);
            // Check the radio input
            $('input[name="logo_type"][value="text"]').prop('checked', true);
        } else {
            $('.selection-card:last-child').addClass('active');
            $('#logo-text-box').slideUp(200);
            $('#logo-image-box').slideDown(200);
            // Check the radio input
            $('input[name="logo_type"][value="image"]').prop('checked', true);
        }
    }

    function createNewTemplate() {
        const name = $('#new_template_name').val().trim();
        if (!name) {
            alert('Please enter a template name.');
            return;
        }

        if (!confirm(`Create template "${name}"? This will generate folder structure and default files.`)) {
            return;
        }

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/admin/config/create-template';
        
        const nameInput = document.createElement('input');
        nameInput.type = 'hidden';
        nameInput.name = 'template_name';
        nameInput.value = name;
        
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = 'csrf_token';
        csrfInput.value = '<?= $csrf_token ?>';
        
        form.appendChild(nameInput);
        form.appendChild(csrfInput);
        document.body.appendChild(form);
        form.submit();
    }
</script>

<?php include_admin_footer(); ?>
