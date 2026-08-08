<?php 
$title = isset($post) ? 'Edit Post' : 'Write Post';
include_header($title, $siteConfig); 
?>
<style><?php include CM_BOARD_SKINS_PATH . '/basic/style.css'; ?></style>

<!-- Quill.js Dependencies (No jQuery dependency, compatible with JQ4) -->
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>

<div class="container basic-container">
    <div class="glass-card">
        <div class="write-header">
            <h1 class="write-title"><?= $title ?></h1>
            <p class="write-subtitle">Board: <?= htmlspecialchars($board['title']) ?></p>
        </div>
        
        <form id="writeForm" action="<?= $_SERVER['REQUEST_URI'] ?>" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            <div class="form-group" style="margin-bottom: 1rem;">
                <label>Title</label>
                <input type="text" name="title" class="form-control" value="<?= isset($post) ? htmlspecialchars($post['title']) : '' ?>" required placeholder="Enter post title...">
            </div>
            
            <div class="form-group" style="margin-bottom: 1rem;">
                <div class="editor-mode-container">
                    <label class="editor-label">Content</label>
                    <div class="btn-group mode-switch-group" role="group">
                        <input type="radio" class="btn-check" name="editor_mode" id="mode_visual" value="visual" <?= ($post['editor_mode'] ?? 'visual') === 'visual' ? 'checked' : '' ?> autocomplete="off">
                        <label class="btn btn-sm btn-mode-label" for="mode_visual">Visual</label>

                        <input type="radio" class="btn-check" name="editor_mode" id="mode_html" value="html" <?= ($post['editor_mode'] ?? 'visual') === 'html' ? 'checked' : '' ?> autocomplete="off">
                        <label class="btn btn-sm btn-mode-label" for="mode_html">HTML</label>
                    </div>
                </div>

                <!-- Quill Editor Container -->
                <div id="visual-editor-wrapper" style="<?= ($post['editor_mode'] ?? 'visual') === 'visual' ? '' : 'display: none;' ?>">
                    <div id="editor-container" class="editor-container-style"></div>
                </div>

                <!-- Raw HTML Textarea -->
                <div id="html-editor-wrapper" style="<?= ($post['editor_mode'] ?? 'visual') === 'html' ? '' : 'display: none;' ?>">
                    <textarea id="html_content" class="form-control html-editor-textarea"><?= isset($post) ? htmlspecialchars($_raw['post']['content']) : '' ?></textarea>
                </div>
                
                <!-- Hidden input to store HTML content -->
                <input type="hidden" name="content" id="content">
            </div>

            <div class="form-group" style="margin-top: 2rem;">
                <label class="attachments-label">📎 Attachments (Max 5)</label>
                
                <?php if (isset($files) && !empty($files)): ?>
                    <div class="existing-files-wrapper">
                        <p class="existing-files-note">Existing Files:</p>
                        <?php foreach ($files as $file): ?>
                            <div class="existing-file-item">
                                <span class="file-name"><?= htmlspecialchars($file['original_name']) ?></span>
                                <label class="delete-file-label">
                                    <input type="checkbox" name="delete_files[]" value="<?= $file['id'] ?>"> Delete
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div id="file-inputs">
                    <div class="file-input-group">
                        <input type="file" name="attachments[]" class="form-control">
                        <button type="button" class="btn btn-primary btn-add-file">+</button>
                    </div>
                </div>
            </div>
            
            <div class="write-actions">
                <button type="submit" class="btn btn-primary btn-submit">
                    <?= isset($post) ? '✏️ Update Post' : '📝 Submit Post' ?>
                </button>
                <a href="/board/<?= $board['slug'] ?>" class="btn btn-cancel">❌ Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialize Quill
    var quill = new Quill('#editor-container', {
        modules: {
            toolbar: {
                container: [
                    [{ header: [1, 2, false] }],
                    ['bold', 'italic', 'underline'],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    ['link', 'image', 'code-block'],
                    ['clean']
                ],
                handlers: {
                    image: imageHandler
                }
            }
        },
        placeholder: 'Write your content here...',
        theme: 'snow'
    });

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

            <?php $currentSlug = isset($board) ? $board['slug'] : (isset($post['board_slug']) ? $post['board_slug'] : ''); ?>
            fetch('/board/upload-image/<?= $currentSlug ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(result => {
                const range = quill.getSelection(true);
                quill.insertEmbed(range.index, 'image', result.url);
                quill.setSelection(range.index + 1);
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Image upload failed');
            });
        };
    }

    // Mode Switching Logic
    $('input[name="editor_mode"]').change(function() {
        if (this.value === 'html') {
            if (isDirty) {
                $('#html_content').val(quill.root.innerHTML);
            }
            $('#visual-editor-wrapper').hide();
            $('#html-editor-wrapper').show();
        } else {
            quill.clipboard.dangerouslyPasteHTML($('#html_content').val());
            isDirty = false;
            $('#html-editor-wrapper').hide();
            $('#visual-editor-wrapper').show();
        }
    });

    // Initialize Visibility (in case JS loads after partial render or for state management)
    const initialMode = $('input[name="editor_mode"]:checked').val();
    if (initialMode === 'html') {
        $('#visual-editor-wrapper').hide();
        $('#html-editor-wrapper').show();
    } else {
        $('#html-editor-wrapper').hide();
        $('#visual-editor-wrapper').show();
    }

    // Handle form submission
    $('#writeForm').on('submit', function() {
        var mode = $('input[name="editor_mode"]:checked').val();
        if (mode === 'html') {
            $('#content').val($('#html_content').val());
        } else {
            $('#content').val(quill.root.innerHTML);
        }
    });


    // Dynamic File Inputs
    $(document).on('click', '.btn-add-file', function() {
        var count = $('.file-input-group').length;
        if (count < 5) {
            var newRow = `
                <div class="file-input-group">
                    <input type="file" name="attachments[]" class="form-control">
                    <button type="button" class="btn btn-remove-file">−</button>
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
</script>

<?php include_footer($siteConfig); ?>
