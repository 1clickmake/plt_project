<?php 
$title = ($mode === 'create' ? 'Create Page' : 'Edit Page: ' . htmlspecialchars($page['title'] ?? ''));
include CM_LAYOUT_PATH . '/admin_header.php';
?>

<!-- Quill.js Dependencies -->
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>

<div class="glass-card">
    <div class="admin-header-flex">
        <h1><?= $title ?></h1>
        <a href="/admin/pages" class="btn" style="background: rgba(255,255,255,0.1); color: white;">Back to List</a>
    </div>

    <form id="pageForm" action="<?= $mode === 'create' ? '/admin/pages/create' : '/admin/pages/edit/' . $page['id'] ?>" method="POST">
        <div class="admin-grid" style="margin-bottom: 2rem;">
            <div class="form-group">
                <label class="form-label">Page Title</label>
                <input type="text" name="title" id="page_title" class="form-control" value="<?= htmlspecialchars($page['title'] ?? '') ?>" required placeholder="e.g. About Company">
            </div>
            <div class="form-group">
                <label class="form-label">URL Slug (English, numbers, hyphens only)</label>
                <input type="text" name="slug" id="page_slug" class="form-control" value="<?= htmlspecialchars($page['slug'] ?? '') ?>" placeholder="e.g. about-us">
                <small class="text-muted-small">Leave empty to generate from title</small>
            </div>
        </div>

        <div class="admin-grid" style="margin-bottom: 2rem;">
            <div class="form-group d-flex align-items-center gap-3">
                <label class="form-label mb-0">Display Title</label>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="display_title" id="display_title" <?= ($page['display_title'] ?? 1) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="display_title">Show title on page</label>
                </div>
            </div>
            <div class="form-group d-flex align-items-center gap-3">
                <label class="form-label mb-0">Container Style</label>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="use_card_style" id="use_card_style" <?= ($page['use_card_style'] ?? 1) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="use_card_style">Show in styled card box</label>
                </div>
            </div>
        </div>

        <div class="form-group" style="margin-bottom: 2rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                <label class="form-label" style="margin-bottom: 0;">Page Content</label>
                <div class="d-flex align-items-center gap-2">
                    <?php do_action('admin_page_form_content_tools'); ?>
                    <div class="btn-group" role="group" style="background: rgba(255,255,255,0.1); padding: 4px; border-radius: 8px;">
                        <input type="radio" class="btn-check" name="editor_mode" id="mode_visual" value="visual" <?= ($page['editor_mode'] ?? 'visual') === 'visual' ? 'checked' : '' ?> autocomplete="off">
                        <label class="btn btn-sm" for="mode_visual" style="color: white; border: none; padding: 0.35rem 1rem;">Visual</label>
    
                        <input type="radio" class="btn-check" name="editor_mode" id="mode_html" value="html" <?= ($page['editor_mode'] ?? 'visual') === 'html' ? 'checked' : '' ?> autocomplete="off">
                        <label class="btn btn-sm" for="mode_html" style="color: white; border: none; padding: 0.25rem 0.75rem;">HTML</label>
                    </div>
                </div>
            </div>

            <!-- Visual Editor Container -->
            <div id="visual-editor-wrapper" style="<?= ($page['editor_mode'] ?? 'visual') === 'visual' ? '' : 'display: none;' ?>">
                <div id="editor-container" style="height: 500px;">
                    
                </div>
            </div>

            <!-- Raw HTML Textarea -->
            <div id="html-editor-wrapper" style="<?= ($page['editor_mode'] ?? 'visual') === 'html' ? '' : 'display: none;' ?>">
                <textarea id="html_content" class="form-control" style="height: 500px; font-family: monospace; font-size: 0.9rem;"><?= htmlspecialchars($_raw['page']['content'] ?? '') ?></textarea>
            </div>

            <input type="hidden" name="content" id="content_input">
        </div>

        <div style="display: flex; gap: 1rem; align-items: center;">
            <button type="submit" class="btn btn-primary" style="padding: 0.75rem 2rem;">Save Page</button>
            <?php if ($mode === 'edit' && !empty($page['slug'])): ?>
                <a href="/page/<?= htmlspecialchars($page['slug']) ?>" target="_blank" class="btn" style="background: rgba(16, 185, 129, 0.2); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.4); padding: 0.75rem 1.5rem;">
                    <i class="fa-solid fa-eye me-1"></i> Preview
                </a>
            <?php endif; ?>
        </div>
    </form>
</div>

<?php do_action('admin_page_form_after_form'); ?>

<script>
    $('#link-pages').addClass('active');

    $(document).ready(function() {
        // Auto-generate slug from title for new pages
        <?php if ($mode === 'create'): ?>
        $('#page_title').on('input', function() {
            let title = $(this).val();
            let slug = title.toLowerCase()
                            .replace(/[^a-z0-9가-힣\s-]/g, '')
                            .replace(/\s+/g, '-')
                            .replace(/-+/g, '-');
            $('#page_slug').val(slug);
        });
        <?php endif; ?>

        // Initialize Quill
        var quill = new Quill('#editor-container', {
            modules: {
                toolbar: {
                    container: [
                        [{ header: [1, 2, 3, false] }],
                        ['bold', 'italic', 'underline', 'strike'],
                        ['blockquote', 'code-block'],
                        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                        [{ 'color': [] }, { 'background': [] }],
                        ['link', 'image', 'clean']
                    ],
                    handlers: {
                        image: imageHandler
                    }
                }
            },
            placeholder: 'Write your page content here...',
            theme: 'snow'
        });

        // Expose quill globally so plugins can interact with it
        window.pageEditor = quill;

        // Set initial content
        const initialContent = $('#html_content').val();
        if (initialContent) {
            quill.clipboard.dangerouslyPasteHTML(initialContent);
        }

        let isDirty = false;
        quill.on('text-change', function(delta, oldDelta, source) {
            if (source === 'user') {
                isDirty = true;
            }
        });

        function imageHandler() {
            const input = document.createElement('input');
            input.setAttribute('type', 'file');
            input.setAttribute('accept', 'image/*');
            input.click();

            input.onchange = () => {
                const file = input.files[0];
                const formData = new FormData();
                formData.append('image', file);

                fetch('/admin/pages/upload-image', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(result => {
                    const range = quill.getSelection(true);
                    quill.insertEmbed(range.index, 'image', result.url);
                    quill.setSelection(range.index + 1);
                })
                .catch(error => console.error('Error:', error));
            };
        }

        // Mode Switching Logic
        $('input[name="editor_mode"]').change(function() {
            if (this.value === 'html') {
                // Switching to HTML Mode
                // Only sync from Quill if user has edited in Visual mode
                if (isDirty) {
                    $('#html_content').val(quill.root.innerHTML);
                }
                $('#visual-editor-wrapper').hide();
                $('#html-editor-wrapper').show();
            } else {
                // Switching to Visual Mode
                // Load content from Textarea to Quill
                quill.clipboard.dangerouslyPasteHTML($('#html_content').val());
                
                // Reset dirty flag since we just loaded fresh content
                isDirty = false; 
                
                $('#html-editor-wrapper').hide();
                $('#visual-editor-wrapper').show();
            }
        });

        // Form Submission
        $('#pageForm').on('submit', function() {
            var mode = $('input[name="editor_mode"]:checked').val();
            
            if (mode === 'html') {
                // If in HTML mode, save from textarea
                $('#content_input').val($('#html_content').val());
            } else {
                // If in Visual mode, save from Quill
                $('#content_input').val(quill.root.innerHTML);
            }
        });

        // Let plugins add their own JS logic
        <?php do_action('admin_page_form_scripts'); ?>
    });
</script>


<?php include CM_LAYOUT_PATH . '/admin_footer.php'; ?>
