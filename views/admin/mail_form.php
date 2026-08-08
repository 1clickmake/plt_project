<?php $title = 'Mail Sending'; include_admin_header($title); ?>

<div class="admin-header-flex">
    <div>
        <h1 style="margin: 0;">Mail Sending</h1>
        <p class="text-muted-small" style="margin: 0;">Send emails to members using Google SMTP.</p>
    </div>
    <div>
        <a href="javascript:void(0)" onclick="openSmtpSettings()" class="btn btn-secondary me-2">
            <i class="fa-solid fa-gear"></i> Settings
        </a>
        <a href="/admin/mail/logs" class="btn btn-secondary">
            <i class="fa-solid fa-list"></i> View Logs
        </a>
    </div>
</div>

<?php if (isset($_GET['msg'])): ?>
<div class="alert alert-info" style="margin-bottom: 20px;">
    <?= htmlspecialchars(urldecode($_GET['msg'])) ?>
</div>
<?php endif; ?>

<div class="glass-card">
    <form action="/admin/mail/send" method="POST" id="mailForm" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">

        <!-- Target Selection -->
        <h3 class="admin-section-header"><i class="fa-solid fa-users"></i> Recipients</h3>
        <div class="admin-section-container">
            <div class="form-group mb-4">
                <label class="form-label">Send To:</label>
                <div class="flex-gap-1">
                    <label class="selection-card active" onclick="selectTarget('all')">
                        <input type="radio" name="target_type" value="all" checked style="display: none;">
                        <i class="fa-solid fa-earth-americas fa-2x mb-2"></i>
                        <span>All Members</span>
                    </label>
                    <label class="selection-card" onclick="selectTarget('level')">
                        <input type="radio" name="target_type" value="level" style="display: none;">
                        <i class="fa-solid fa-layer-group fa-2x mb-2"></i>
                        <span>By Level</span>
                    </label>
                    <label class="selection-card" onclick="selectTarget('select')">
                        <input type="radio" name="target_type" value="select" style="display: none;">
                        <i class="fa-solid fa-user-check fa-2x mb-2"></i>
                        <span>Member IDs</span>
                    </label>
                    <label class="selection-card" onclick="selectTarget('email')">
                        <input type="radio" name="target_type" value="email" style="display: none;">
                        <i class="fa-solid fa-at fa-2x mb-2"></i>
                        <span>Direct Emails</span>
                    </label>
                </div>
            </div>

            <!-- Level Select -->
            <div id="target-level-box" style="display: none;" class="form-group">
                <label class="form-label">Select Level</label>
                <select name="target_level" class="form-select">
                    <?php foreach ($levels as $lv): ?>
                        <option value="<?= $lv ?>">Level <?= $lv ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- ID Input -->
            <div id="target-select-box" style="display: none;" class="form-group">
                <label class="form-label">Member IDs (Comma separated)</label>
                <textarea name="target_ids" class="form-control" placeholder="user1, user2, user3" rows="3"></textarea>
            </div>

            <!-- Email Input -->
            <div id="target-email-box" style="display: none;" class="form-group">
                <label class="form-label">Direct Emails (Comma separated)</label>
                <textarea name="target_emails" class="form-control" placeholder="test@test.com, user@domain.com" rows="3"></textarea>
            </div>
        </div>

        <!-- Content -->
        <h3 class="admin-section-header"><i class="fa-solid fa-envelope"></i> Message</h3>
        <div class="admin-section-container">
            <div class="form-group mb-4">
                <label class="form-label">Subject</label>
                <input type="text" name="subject" class="form-control" required placeholder="Enter email subject">
            </div>

            <div class="form-group mb-4">
                <label class="form-label">Content</label>
                <!-- Quill Editor -->
                <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
                <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>

                <div id="editor-container" style="height: 400px; background: rgba(0,0,0,0.2); color: white;"></div>
                <input type="hidden" name="content" id="content">
            </div>

            <div class="form-group">
                <label class="form-label">📎 Attachments (Max 5)</label>
                <div id="file-inputs">
                    <div class="file-input-group mb-2 d-flex gap-2">
                        <input type="file" name="attachments[]" class="form-control">
                        <button type="button" class="btn btn-primary btn-add-file" style="width: 40px; height: 38px; display: flex; align-items: center; justify-content: center;">+</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-end mt-4">
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-paper-plane"></i> Send Mail
            </button>
        </div>
    </form>
</div>

<script>
    $('#link-mail').addClass('active');

    function selectTarget(type) {
        $('.selection-card').removeClass('active');
        $('input[name="target_type"][value="' + type + '"]').closest('.selection-card').addClass('active');
        $('input[name="target_type"][value="' + type + '"]').prop('checked', true);

        if (type === 'level') {
            $('#target-level-box').slideDown();
            $('#target-select-box').slideUp();
            $('#target-email-box').slideUp();
        } else if (type === 'select') {
            $('#target-level-box').slideUp();
            $('#target-select-box').slideDown();
            $('#target-email-box').slideUp();
        } else if (type === 'email') {
            $('#target-level-box').slideUp();
            $('#target-select-box').slideUp();
            $('#target-email-box').slideDown();
        } else {
            $('#target-level-box').slideUp();
            $('#target-select-box').slideUp();
            $('#target-email-box').slideUp();
        }
    }

    // SMTP Config Modal Logic
    const isSmtpConfigured = <?= json_encode($smtpConfigured) ?>;
    
    $(document).ready(function() {
        // Force open ONLY if not configured
        if (!isSmtpConfigured) {
            $('#smtpModal').fadeIn();
        }

        // Initialize Quill
        var quill = new Quill('#editor-container', {
            theme: 'snow',
            placeholder: 'Write your email content here...',
            modules: {
                toolbar: [
                    [{ 'header': [1, 2, 3, false] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    ['blockquote', 'code-block'],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    [{ 'color': [] }, { 'background': [] }],
                    ['link', 'clean']
                ]
            }
        });

        $('#mailForm').on('submit', function() {
            $('#content').val(quill.root.innerHTML);
            
            // Check if subject/content is empty
            if (!$('input[name="subject"]').val()) {
                alert('Please enter a subject.');
                return false;
            }
            if (quill.getText().trim().length === 0 && !quill.root.innerHTML.includes('<img')) {
                alert('Please enter email content.');
                return false;
            }
            
            return confirm('Are you sure you want to send this email?');
        });

        // Dynamic File Inputs
        $(document).on('click', '.btn-add-file', function() {
            var count = $('.file-input-group').length;
            if (count < 5) {
                var newRow = `
                    <div class="file-input-group mb-2 d-flex gap-2">
                        <input type="file" name="attachments[]" class="form-control">
                        <button type="button" class="btn btn-danger btn-remove-file" style="width: 40px; height: 38px; display: flex; align-items: center; justify-content: center;">-</button>
                    </div>`;
                $('#file-inputs').append(newRow);
            } else {
                alert('You can upload up to 5 files.');
            }
        });

        $(document).on('click', '.btn-remove-file', function() {
            $(this).closest('.file-input-group').remove();
        });
    });

    function openSmtpSettings() {
        $('#smtpModal').fadeIn();
    }

    function closeSmtpSettings() {
        if (!isSmtpConfigured) {
            if(!confirm('SMTP settings are required to send emails. Close anyway?')) return;
        }
        $('#smtpModal').fadeOut();
    }
</script>

<!-- SMTP Settings Modal -->
<div id="smtpModal" class="admin-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 9999; backdrop-filter: blur(5px);">
    <div class="glass-card" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 90%; max-width: 500px; padding: 2rem;">
        <h3 class="mb-3 d-flex align-items-center">
            <i class="fa-solid fa-gear me-2 text-primary"></i> SMTP Settings
        </h3>
        <p class="text-muted-small mb-4">Please configure your Google SMTP settings to enable mail sending.</p>
        
        <form action="/admin/mail/config" method="POST">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">
            
            <div class="form-group">
                <label class="form-label">SMTP Host</label>
                <input type="text" name="smtp_host" class="form-control" value="<?= htmlspecialchars($smtpConfig['host']) ?>" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">SMTP Port</label>
                <input type="text" name="smtp_port" class="form-control" value="<?= htmlspecialchars($smtpConfig['port']) ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label">Google Email (User)</label>
                <input type="email" name="smtp_user" class="form-control" value="<?= htmlspecialchars($smtpConfig['user']) ?>" required placeholder="example@gmail.com">
            </div>

            <div class="form-group">
                <label class="form-label">App Password</label>
                <input type="password" name="smtp_pass" class="form-control" value="<?= htmlspecialchars($smtpConfig['pass']) ?>" required placeholder="Apps > Security > App Passwords">
                <small class="text-muted-small">Use an <a href="https://myaccount.google.com/apppasswords" target="_blank" style="color: #a855f7;">App Password</a>, not your login password.</small>
            </div>

            <div class="form-group">
                <label class="form-label">Sender Name</label>
                <input type="text" name="smtp_from_name" class="form-control" value="<?= htmlspecialchars($smtpConfig['from_name']) ?>" required>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <button type="button" class="btn" onclick="closeSmtpSettings()" style="background: rgba(255,255,255,0.1); color: white;">Close</button>
                <button type="submit" class="btn btn-primary">Save Settings</button>
            </div>
        </form>
    </div>
</div>

<?php include_admin_footer(); ?>
