<?php $title = 'Mail Logs'; include_admin_header($title); ?>

<div class="admin-header-flex">
    <div>
        <h1 style="margin: 0;">Mail Logs</h1>
        <p class="text-muted-small" style="margin: 0; margin-bottom: 1.5rem;">History of sent emails.</p>
        
        <div class="filter-badges mt-3">
            <a href="/admin/mail/logs" class="btn btn-sm <?= empty($currentFilter) ? 'btn-primary' : 'btn-outline-secondary' ?>">All Logs</a>
            <a href="/admin/mail/logs?filter=contact" class="btn btn-sm <?= ($currentFilter === 'contact') ? 'btn-primary' : 'btn-outline-secondary' ?>">Contact</a>
            <a href="/admin/mail/logs?filter=direct" class="btn btn-sm <?= ($currentFilter === 'direct') ? 'btn-primary' : 'btn-outline-secondary' ?>">Direct Emails</a>
            <a href="/admin/mail/logs?filter=all" class="btn btn-sm <?= ($currentFilter === 'all') ? 'btn-primary' : 'btn-outline-secondary' ?>">All Members</a>
            <a href="/admin/mail/logs?filter=level" class="btn btn-sm <?= ($currentFilter === 'level') ? 'btn-primary' : 'btn-outline-secondary' ?>">By Level</a>
            <a href="/admin/mail/logs?filter=ids" class="btn btn-sm <?= ($currentFilter === 'ids') ? 'btn-primary' : 'btn-outline-secondary' ?>">Member IDs</a>
        </div>
    </div>

    <a href="/admin/mail" class="btn btn-secondary">
        <i class="fa-solid fa-paper-plane"></i> Send Mail
    </a>
</div>

<div class="glass-card">
    <form id="mail-logs-form" action="/admin/mail/bulk-delete" method="POST">
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
        <input type="hidden" name="filter" value="<?= htmlspecialchars($currentFilter) ?>">
        <input type="hidden" name="page" value="<?= $page ?>">

        <div style="margin-bottom: 1rem;">
            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete selected logs?')">
                <i class="fa-solid fa-trash-can"></i> Bulk Delete
            </button>
        </div>

        <div class="table-responsive">
        <style>
            .log-subject {
                cursor: pointer;
                color: #6366f1;
                text-decoration: none;
                font-weight: 500;
            }
            .log-subject:hover {
                text-decoration: underline;
                color: #818cf8;
            }
        </style>
        <table class="table table-dark table-hover table-striped text-center">
            <thead>
                <tr>
                    <th style="width: 40px;">
                        <input type="checkbox" id="check-all" class="form-check-input">
                    </th>
                    <th style="width: 50px;">No.</th>
                    <th style="width: 150px;">Target</th>
                    <th>Subject</th>
                    <th style="width: 100px;">Status</th>
                    <th style="width: 160px;">Sent At</th>
                    <th>Error Message</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($logs)): ?>
                    <tr>
                        <td colspan="7" class="text-center">No logs found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($logs as $index => $log): ?>
                        <tr>
                            <td>
                                <input type="checkbox" name="ids[]" value="<?= $log['id'] ?>" class="form-check-input log-checkbox">
                            </td>
                            <td><?= $totalItems - ($page - 1) * $limit - $index ?></td>
                            <td>
                                <!-- Display target_info if available, else fallback to recipient (truncated) -->
                                <?php 
                                    if (($log['target_info'] ?? '') === 'contact') {
                                        echo '<span class="badge" style="background: #a855f7; color: white;">Contact</span>';
                                    } else {
                                        $target = $log['target_info'] ?? $log['recipient'];
                                        // if it's a long list of emails and no target_info, truncate
                                        if (empty($log['target_info']) && strlen($target) > 20) {
                                            $target = mb_substr($target, 0, 20) . '...';
                                        }
                                        echo htmlspecialchars($target);
                                    }
                                ?>
                            </td>
                            <td class="text-start">
                                <span class="log-subject" onclick='openLogDetail(<?= htmlspecialchars(json_encode($log), ENT_QUOTES, "UTF-8") ?>)'>
                                    <?= htmlspecialchars($log['subject']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($log['status'] === 'success'): ?>
                                    <span class="badge badge-success">Success</span>
                                <?php elseif ($log['status'] === 'partial'): ?>
                                    <span class="badge badge-warning">Partial</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">Failed</span>
                                <?php endif; ?>
                            </td>
                            <td><?= $log['sent_at'] ?></td>
                            <td><?= htmlspecialchars($log['error_message'] ?? '-') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    </form>

    <!-- Pagination -->
    <?php 
        $paginationSearch = $currentFilter ? '&filter=' . urlencode($currentFilter) : '';
        echo get_pagination($page, $totalPages, $paginationSearch); 
    ?>
</div>

<!-- Mail Detail Modal -->
<div id="mailDetailModal" class="mail-modal-overlay">
    <div class="glass-card mail-modal-container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="mb-0"><i class="fa-solid fa-envelope-open-text me-2"></i> Mail Details</h3>
            <button onclick="$('#mailDetailModal').fadeOut()" class="btn btn-sm btn-secondary"><i class="fa-solid fa-times"></i></button>
        </div>

        <div class="detail-group mb-4">
            <label class="text-muted-small d-block mb-1">Subject</label>
            <div id="detail-subject" class="fw-bold fs-5 p-2 bg-dark-soft rounded"></div>
        </div>

        <div class="detail-group mb-4" id="group-recipients">
            <label class="text-muted-small d-block mb-1">Recipients</label>
            <div id="detail-recipients" class="mail-recipients-box"></div>
        </div>

        <div id="contact-info-section" style="display:none; border: 1px dashed rgba(255,255,255,0.2); padding: 1rem; border-radius: 12px; margin-bottom: 1.5rem; background: rgba(168, 85, 247, 0.05);">
            <div class="row">
                <div class="col-md-4">
                    <label class="text-muted-small d-block">Sender Name</label>
                    <div id="detail-sender-name" class="fw-bold"></div>
                </div>
                <div class="col-md-4">
                    <label class="text-muted-small d-block">Sender Email</label>
                    <div id="detail-sender-email" class="fw-bold"></div>
                </div>
                <div class="col-md-4">
                    <label class="text-muted-small d-block">Phone Number</label>
                    <div id="detail-sender-phone" class="fw-bold"></div>
                </div>
            </div>
        </div>

        <div class="detail-group mb-4">
            <label class="text-muted-small d-block mb-1">Content</label>
            <div id="detail-content" class="mail-content-box"></div>
        </div>

        <div class="detail-group mb-2">
            <label class="text-muted-small d-block mb-1">📎 Attachments</label>
            <div id="detail-attachments" class="mail-attachments-box"></div>
        </div>

        <div class="text-end mt-4">
            <button onclick="$('#mailDetailModal').fadeOut()" class="btn btn-secondary px-4">Close</button>
        </div>
    </div>
</div>

<script>
    $('#link-mail-logs').addClass('active');

    // Check All functionality
    $('#check-all').on('change', function() {
        $('.log-checkbox').prop('checked', $(this).is(':checked'));
    });

    // If any checkbox is unchecked, uncheck the master
    $('.log-checkbox').on('change', function() {
        if (!$(this).is(':checked')) {
            $('#check-all').prop('checked', false);
        }
    });

    function openLogDetail(log) {
        $('#detail-subject').text(log.subject);
        
        // Format recipients: split by comma and join with newline
        var formattedRecipients = log.recipient.split(',').map(s => s.trim()).filter(s => s).join('\n');
        $('#detail-recipients').html(formattedRecipients.replace(/\n/g, '<br>'));
        
        // Fix for content showing tags: ensure it's rendered as HTML
        // If the data was saved escaped, we might need to decode it once or just use .html()
        // We'll use a temporary element to decode entities if they exist.
        var decodeEntity = function(str) {
            var textarea = document.createElement("textarea");
            textarea.innerHTML = str;
            return textarea.value;
        };
        
        var content = log.content;
        // If it looks like escaped HTML (contains &lt;), decode it
        if (content.indexOf('&lt;') !== -1) {
            content = decodeEntity(content);
        }
        $('#detail-content').html(content); 
        
        // Handle contact info
        if (log.target_info === 'contact') {
            $('#contact-info-section').show();
            $('#detail-sender-name').text(log.sender_name || '-');
            $('#detail-sender-email').text(log.sender_email || '-');
            $('#detail-sender-phone').text(log.sender_phone || '-');
            $('#group-recipients').hide(); // Admin knows who they are
        } else {
            $('#contact-info-section').hide();
            $('#group-recipients').show();
        }
        
        // Attachments: split by comma and show line by line with icons
        if (log.attachments && log.attachments.trim() !== '') {
            var attList = log.attachments.split(',').map(s => s.trim()).filter(s => s);
            var attHtml = attList.map(name => '<div class="mb-1"><i class="fa-solid fa-file-arrow-down me-2"></i>' + name + '</div>').join('');
            $('#detail-attachments').html(attHtml);
        } else {
            $('#detail-attachments').html('<div class="text-muted">No attachments</div>');
        }

        $('#mailDetailModal').fadeIn();
    }
</script>

<?php include_admin_footer(); ?>
