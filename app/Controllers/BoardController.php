<?php

namespace App\Controllers;

use App\Core\Database;
use App\Core\Csrf;
use App\Services\FileService;
use PDO;

class BoardController extends BaseController {
    
    public function index($vars) {
        $slug = $vars['slug'];
        $db = Database::getInstance();
        
        // Get board info
        $stmt = $db->prepare("SELECT * FROM boards WHERE slug = :slug");
        $stmt->execute(['slug' => $slug]);
        $board = $stmt->fetch();
        
        if (!$board) {
            die("Board not found");
        }

        // Permission Check: List
        if (!check_level($board['level_list'] ?? 1)) {
            alert('You do not have permission to view this board list.');
        }
        
        // Search functionality
        $search = $_GET['search'] ?? '';
        $searchWhere = '';
        $params = ['board_id' => $board['id']];
        
        if ($search) {
            $searchWhere = " AND (p.title LIKE :search OR p.content LIKE :search)";
            $params['search'] = '%' . $search . '%';
        }
        
        // Pagination logic
        $limit = $board['page_rows'] ?? 20;
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        if ($page < 1) $page = 1;
        $offset = ($page - 1) * $limit;

        // Get total count (only parent posts, reply_depth = 0)
        $stmt = $db->prepare("SELECT COUNT(*) FROM posts p WHERE p.board_id = :board_id AND p.reply_depth = 0" . $searchWhere);
        $stmt->execute($params);
        $totalItems = $stmt->fetchColumn();
        $totalPages = ceil($totalItems / $limit);

        // Get posts with limit and offset (only parent posts)
        $sql = "SELECT p.*, u.username, 
                (SELECT COUNT(*) FROM posts WHERE parent_id = p.id) as reply_count,
                (SELECT COUNT(*) FROM comments WHERE post_id = p.id) as comment_count,
                (SELECT filepath FROM post_files WHERE post_id = p.id AND file_type LIKE 'image/%' LIMIT 1) as first_file
                FROM posts p 
                JOIN users u ON p.user_id = u.user_id 
                WHERE p.board_id = :board_id AND p.reply_depth = 0" . $searchWhere . "
                ORDER BY p.created_at DESC 
                LIMIT :limit OFFSET :offset";
        $stmt = $db->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue(":$key", $val);
        }
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $posts = $stmt->fetchAll();
        
        // Load skin-specific view
        $skin = $board['skin'] ?? 'basic';
        $viewPath = "board/skins/{$skin}/list";
        
        $this->view($viewPath, [
            'board' => $board, 
            'posts' => $posts, 
            'page' => $page, 
            'totalPages' => $totalPages,
            'totalItems' => $totalItems,
            'limit' => $limit,
            'pageButtons' => $board['page_buttons'] ?? 5,
            'search' => $search
        ]);
    }

    public function show($vars) {
        $id = $vars['id'];
        $db = Database::getInstance();
        
        $stmt = $db->prepare("SELECT p.*, u.username, b.title as board_title, b.slug as board_slug, 
                              b.skin, b.max_replies, b.allow_comments, b.level_view, b.point_view
                              FROM posts p 
                              JOIN users u ON p.user_id = u.user_id 
                              JOIN boards b ON p.board_id = b.id 
                              WHERE p.id = :id");
        $stmt->execute(['id' => $id]);
        $post = $stmt->fetch();
        
        if (!$post) {
            die("Post not found");
        }

        // Permission Check: View
        if (!check_level($post['level_view'] ?? 1)) {
            alert('You do not have permission to view this post.');
        }

        // Apply View Points (only if logged in and not the author/admin)
        global $is_member, $user, $is_admin;
        if ($is_member && !$is_admin && $user['user_id'] !== $post['user_id']) {
            $viewPoint = (int)($post['point_view'] ?? 0);
            if ($viewPoint != 0) {
                // To prevent point draining on refresh, only deduct once per session per post
                $viewKey = 'viewed_post_' . $id;
                if (!isset($_SESSION[$viewKey])) {
                    // Check if enough points for deduction
                    if ($viewPoint < 0 && $user['point'] < abs($viewPoint)) {
                        alert('Not enough points to view this post.');
                    }
                    
                    add_point($user['user_id'], $viewPoint, 'Post View: ' . $post['title']);
                    $_SESSION['user']['point'] += $viewPoint; // Update session
                    setup_user_variables(); // Sync globals
                    $_SESSION[$viewKey] = true;
                }
            }
        }
        
        // Increase view count
        $stmt = $db->prepare("UPDATE posts SET views = views + 1 WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $post['views']++; // Update local copy
        
        // Get files
        $stmt = $db->prepare("SELECT * FROM post_files WHERE post_id = :post_id");
        $stmt->execute(['post_id' => $id]);
        $files = $stmt->fetchAll();
        
        // Get replies (답글)
        $stmt = $db->prepare("SELECT r.*, u.username FROM posts r JOIN users u ON r.user_id = u.user_id WHERE r.parent_id = :parent_id ORDER BY r.created_at ASC");
        $stmt->execute(['parent_id' => $id]);
        $replies = $stmt->fetchAll();
        
        // Get comments (댓글) with pagination (10 per page)
        $commentPage = isset($_GET['comment_page']) ? (int)$_GET['comment_page'] : 1;
        if ($commentPage < 1) $commentPage = 1;
        $commentLimit = 10;
        $commentOffset = ($commentPage - 1) * $commentLimit;
        
        // Total comments
        $stmt = $db->prepare("SELECT COUNT(*) FROM comments WHERE post_id = :post_id AND reply_depth = 0");
        $stmt->execute(['post_id' => $id]);
        $totalComments = $stmt->fetchColumn();
        $totalCommentPages = ceil($totalComments / $commentLimit);
        
        // Get parent comments
        $stmt = $db->prepare("SELECT c.*, u.username FROM comments c JOIN users u ON c.user_id = u.user_id WHERE c.post_id = :post_id AND c.reply_depth = 0 ORDER BY c.created_at DESC LIMIT :limit OFFSET :offset");
        $stmt->bindParam(':post_id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $commentLimit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $commentOffset, PDO::PARAM_INT);
        $stmt->execute();
        $comments = $stmt->fetchAll();
        
        // For each comment, get sub-comments (대댓글)
        foreach ($comments as &$comment) {
            $stmt = $db->prepare("SELECT c.*, u.username FROM comments c JOIN users u ON c.user_id = u.user_id WHERE c.parent_comment_id = :parent_id ORDER BY c.created_at ASC");
            $stmt->execute(['parent_id' => $comment['id']]);
            $comment['sub_comments'] = $stmt->fetchAll();
        }
        
        // Load skin-specific view
        $skin = $post['skin'] ?? 'basic';
        $viewPath = "board/skins/{$skin}/view";
        
        // Pass Database instance for comments/replies recursively if view needs it (though bad practice, keeping minimal changes)
        // Better: Fetch everything needed here.
        
        $this->view($viewPath, [
            'post' => $post, 
            'files' => $files,
            'replies' => $replies,
            'comments' => $comments,
            'commentPage' => $commentPage,
            'totalCommentPages' => $totalCommentPages
        ]);
    }

    public function write($vars) {
        global $is_member;
        if (!$is_member) {
            $this->redirect('/login');
        }

        $slug = $vars['slug'];
        $db = Database::getInstance();
        
        $stmt = $db->prepare("SELECT * FROM boards WHERE slug = :slug");
        $stmt->execute(['slug' => $slug]);
        $board = $stmt->fetch();

        // Permission Check: Write
        if (!check_level($board['level_write'] ?? 1)) {
            alert('You do not have permission to write here.');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Csrf::verify($_POST['csrf_token'] ?? '')) die("CSRF validation failed");

            $title = $_POST['title'] ?? '';
            $content = $_POST['content'] ?? '';
            $editor_mode = $_POST['editor_mode'] ?? 'visual';
            
            global $user;
            $stmt = $db->prepare("INSERT INTO posts (group_id, board_id, user_id, title, content, editor_mode) VALUES (:group_id, :board_id, :user_id, :title, :content, :editor_mode)");
            $stmt->execute([
                'group_id' => $board['group_id'],
                'board_id' => $board['id'],
                'user_id' => $user['user_id'],
                'title' => $title,
                'content' => $content,
                'editor_mode' => $editor_mode
            ]);
            $postId = $db->lastInsertId();
            
            // Backup logic for hosting environments where lastInsertId might fail
            if ($postId <= 0) {
                 global $user;
                 $stmt = $db->prepare("SELECT id FROM posts WHERE user_id = :user_id ORDER BY id DESC LIMIT 1");
                 $stmt->execute(['user_id' => $user['user_id']]);
                 $postId = (int)$stmt->fetchColumn();
            }

            // Handle file uploads
            if (isset($_FILES['attachments'])) {
                $fileService = new FileService();
                $fileService->handleUploads($postId, $slug, $_FILES['attachments']);
            }
            
            // Add points for posting
            $writePoint = (int)($board['point_write'] ?? 0);
            if ($writePoint != 0) {
                global $user;
                add_point($user['user_id'], $writePoint, 'Post Write: ' . $title);
                $_SESSION['user']['point'] += $writePoint; // Update session
                setup_user_variables(); // Sync globals
            }
            
            $this->redirect('/board/view/' . $postId);
            return;
        }

        // Load skin-specific write view
        $skin = $board['skin'] ?? 'basic';
        $viewPath = "board/skins/{$skin}/write";
        
        $this->view($viewPath, ['board' => $board]);
    }

    public function edit($vars) {
        $id = $vars['id'];
        $db = Database::getInstance();
        
        $stmt = $db->prepare("SELECT p.*, b.slug as board_slug 
                              FROM posts p 
                              JOIN boards b ON p.board_id = b.id 
                              WHERE p.id = :id");
        $stmt->execute(['id' => $id]);
        $post = $stmt->fetch();

        if (!$post || (!isset($_SESSION['user']) || ($_SESSION['user']['user_id'] != $post['user_id'] && $_SESSION['user']['role'] != 'admin'))) {
            die("Unauthorized");
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Csrf::verify($_POST['csrf_token'] ?? '')) die("CSRF validation failed");

            $title = $_POST['title'] ?? '';
            $content = $_POST['content'] ?? '';
            $editor_mode = $_POST['editor_mode'] ?? 'visual';
            
            $stmt = $db->prepare("UPDATE posts SET title = :title, content = :content, editor_mode = :editor_mode WHERE id = :id");
            $stmt->execute([
                'title' => $title,
                'content' => $content,
                'editor_mode' => $editor_mode,
                'id' => $id
            ]);

            // Handle file deletions
            if (isset($_POST['delete_files'])) {
                $fileService = new FileService();
                foreach ($_POST['delete_files'] as $fileId) {
                    $stmt = $db->prepare("SELECT * FROM post_files WHERE id = :id");
                    $stmt->execute(['id' => $fileId]);
                    $file = $stmt->fetch();
                    if ($file) {
                        $fileService->deleteFile($file['filepath']);
                        $stmt = $db->prepare("DELETE FROM post_files WHERE id = :id");
                        $stmt->execute(['id' => $fileId]);
                    }
                }
            }

            // Get board slug for file path
            $stmt = $db->prepare("SELECT slug FROM boards WHERE id = :id");
            $stmt->execute(['id' => $post['board_id']]);
            $board = $stmt->fetch();

            // Handle new file uploads
            if (isset($_FILES['attachments'])) {
                $fileService = new FileService();
                $fileService->handleUploads($id, $board['slug'], $_FILES['attachments']);
            }
            
            $this->redirect('/board/view/' . $id);
            return;
        }

        // Get existing files
        $stmt = $db->prepare("SELECT * FROM post_files WHERE post_id = :id");
        $stmt->execute(['id' => $id]);
        $files = $stmt->fetchAll();

        // Get board info for skin
        $stmt = $db->prepare("SELECT b.* FROM boards b JOIN posts p ON b.id = p.board_id WHERE p.id = :id");
        $stmt->execute(['id' => $id]);
        $board = $stmt->fetch();

        // Load skin-specific write view (edit uses same form as write)
        $skin = $board['skin'] ?? 'basic';
        $viewPath = "board/skins/{$skin}/write";
        
        $this->view($viewPath, ['post' => $post, 'files' => $files, 'board' => $board]);
    }

    public function delete($vars) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !Csrf::verify($_POST['csrf_token'] ?? '')) {
             die("CSRF validation failed");
        }
        $id = $vars['id'];
        $db = Database::getInstance();
        
        $stmt = $db->prepare("SELECT p.*, b.slug as board_slug 
                              FROM posts p 
                              JOIN boards b ON p.board_id = b.id 
                              WHERE p.id = :id");
        $stmt->execute(['id' => $id]);
        $post = $stmt->fetch();

        if (!$post || (!isset($_SESSION['user']) || ($_SESSION['user']['user_id'] != $post['user_id'] && $_SESSION['user']['role'] != 'admin'))) {
            die("Unauthorized");
        }

        // 1. Delete physical files from post_files table
        $stmt = $db->prepare("SELECT * FROM post_files WHERE post_id = :id");
        $stmt->execute(['id' => $id]);
        $files = $stmt->fetchAll();
        $fileService = new FileService();
        foreach ($files as $file) {
             $fileService->deleteFile($file['filepath']);
        }
        
        // Delete download logs associated with these files
        $stmt = $db->prepare("DELETE FROM file_downloads WHERE file_id IN (SELECT id FROM post_files WHERE post_id = :id)");
        $stmt->execute(['id' => $id]);

        // Remove file records from DB
        $stmt = $db->prepare("DELETE FROM post_files WHERE post_id = :id");
        $stmt->execute(['id' => $id]);

        // 2. Scan content for editor images and delete them
        preg_match_all('/<img[^>]+src="([^">]+)"/i', $post['content'], $matches);
        if (!empty($matches[1])) {
            foreach ($matches[1] as $src) {
                // Only delete if it's a local path in /data/
                if (strpos($src, '/data/') === 0) {
                    $filePath = CM_PUBLIC_PATH . $src;
                    if (file_exists($filePath)) unlink($filePath);
                }
            }
        }

        // 2.5 Delete all comments associated with the post
        $stmt = $db->prepare("DELETE FROM comments WHERE post_id = :id");
        $stmt->execute(['id' => $id]);

        // 3. Delete from DB
        $stmt = $db->prepare("DELETE FROM posts WHERE id = :id");
        $stmt->execute(['id' => $id]);
        
        $this->redirect('/board/' . $post['board_slug']);
    }

    public function bulkDelete() {
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            die("Unauthorized");
        }

        $postIds = $_POST['post_ids'] ?? [];
        if (!Csrf::verify($_POST['csrf_token'] ?? '')) die("CSRF validation failed");

        $slug = $_POST['slug'] ?? '';
        
        if (empty($postIds)) {
            $this->redirect('/board/' . $slug);
            return;
        }

        $db = Database::getInstance();
        
        foreach ($postIds as $id) {
            // 1. Get post for file deletion
            $stmt = $db->prepare("SELECT * FROM posts WHERE id = :id");
            $stmt->execute(['id' => $id]);
            $post = $stmt->fetch();

            if ($post) {
                // Delete physical files from post_files
                $stmt = $db->prepare("SELECT * FROM post_files WHERE post_id = :id");
                $stmt->execute(['id' => $id]);
                $files = $stmt->fetchAll();
                $fileService = new FileService();
                foreach ($files as $file) {
                    $fileService->deleteFile($file['filepath']);
                }

                // Delete download logs associated with these files
                $stmt = $db->prepare("DELETE FROM file_downloads WHERE file_id IN (SELECT id FROM post_files WHERE post_id = :id)");
                $stmt->execute(['id' => $id]);

                // Remove file records from DB
                $stmt = $db->prepare("DELETE FROM post_files WHERE post_id = :id");
                $stmt->execute(['id' => $id]);

                // Delete editor images
                preg_match_all('/<img[^>]+src="([^">]+)"/i', $post['content'], $matches);
                if (!empty($matches[1])) {
                    foreach ($matches[1] as $src) {
                        if (strpos($src, '/data/') === 0) {
                            $filePath = CM_PUBLIC_PATH . $src;
                            if (file_exists($filePath)) unlink($filePath);
                        }
                    }
                }

                // Delete comments associated with the post
                $stmt = $db->prepare("DELETE FROM comments WHERE post_id = :id");
                $stmt->execute(['id' => $id]);

                // Delete from DB
                $stmt = $db->prepare("DELETE FROM posts WHERE id = :id");
                $stmt->execute(['id' => $id]);
            }
        }

        $this->redirect('/board/' . $slug);
    }

    // --- Reply (답글) Methods ---
    public function writeReply($vars) {
        if (!isset($_SESSION['user'])) {
            $this->redirect('/login');
        }

        $parentId = $vars['id'];
        $db = Database::getInstance();

        // Get parent post
        $stmt = $db->prepare("SELECT p.*, b.max_replies FROM posts p JOIN boards b ON p.board_id = b.id WHERE p.id = :id");
        $stmt->execute(['id' => $parentId]);
        $parent = $stmt->fetch();

        if (!$parent || $parent['reply_depth'] > 0) {
            die("Cannot reply to a reply");
        }

        // Check reply limit
        $stmt = $db->prepare("SELECT COUNT(*) FROM posts WHERE parent_id = :parent_id");
        $stmt->execute(['parent_id' => $parentId]);
        $replyCount = $stmt->fetchColumn();

        if ($replyCount >= $parent['max_replies']) {
            die("Reply limit reached (Max: {$parent['max_replies']})");
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Csrf::verify($_POST['csrf_token'] ?? '')) die("CSRF validation failed");
            
            $content = $_POST['content'] ?? '';
            $editor_mode = $_POST['editor_mode'] ?? 'visual';

            $stmt = $db->prepare("INSERT INTO posts (group_id, board_id, user_id, parent_id, title, content, reply_depth, editor_mode) VALUES (:group_id, :board_id, :user_id, :parent_id, :title, :content, 1, :editor_mode)");
            $stmt->execute([
                'group_id' => $parent['group_id'],
                'board_id' => $parent['board_id'],
                'user_id' => $_SESSION['user']['user_id'],
                'parent_id' => $parentId,
                'title' => 'RE: ' . $parent['title'],
                'content' => $content,
                'editor_mode' => $editor_mode
            ]);

            $this->redirect('/board/view/' . $parentId);
        }
    }

    public function deleteReply($vars) {
        if (!Csrf::verify($_POST['csrf_token'] ?? '')) die("CSRF validation failed");
        
        $id = $vars['id'];
        $db = Database::getInstance();

        $stmt = $db->prepare("SELECT * FROM posts WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $reply = $stmt->fetch();

        if (!$reply || $reply['reply_depth'] == 0 || (!isset($_SESSION['user']) || ($_SESSION['user']['user_id'] != $reply['user_id'] && $_SESSION['user']['role'] != 'admin'))) {
            die("Unauthorized");
        }

        $parentId = $reply['parent_id'];
        $stmt = $db->prepare("DELETE FROM posts WHERE id = :id");
        $stmt->execute(['id' => $id]);

        $this->redirect('/board/view/' . $parentId);
    }

    // --- Comment (댓글) Methods ---
    public function addComment() {
        if (!isset($_SESSION['user'])) {
            return $this->json(['error' => 'Login required']);
        }

        $postId = $_POST['post_id'] ?? null;
        $content = $_POST['content'] ?? '';
        $parentCommentId = $_POST['parent_comment_id'] ?? null;

        if (!$postId || !$content) {
            return $this->json(['error' => 'Invalid data']);
        }
        
        if (!Csrf::verify($_POST['csrf_token'] ?? '')) {
             return $this->json(['error' => 'CSRF validation failed']);
        }

        $db = Database::getInstance();

        // Check board allows comments and get settings
        $stmt = $db->prepare("SELECT b.allow_comments, b.max_replies, b.level_comment, b.point_comment FROM posts p JOIN boards b ON p.board_id = b.id WHERE p.id = :id");
        $stmt->execute(['id' => $postId]);
        $board = $stmt->fetch();

        if (!$board || !$board['allow_comments']) {
            return $this->json(['error' => 'Comments disabled']);
        }

        // Permission Check: Comment
        if (!check_level($board['level_comment'] ?? 1)) {
            return $this->json(['error' => 'You do not have permission to write comments.']);
        }

        $replyDepth = 0;

        // If replying to a comment, check depth limit
        if ($parentCommentId) {
            $stmt = $db->prepare("SELECT reply_depth FROM comments WHERE id = :id");
            $stmt->execute(['id' => $parentCommentId]);
            $parentComment = $stmt->fetch();

            if ($parentComment && $parentComment['reply_depth'] > 0) {
                return $this->json(['error' => 'Cannot reply to sub-comment']);
            }

            // Check sub-comment limit (max 3)
            $stmt = $db->prepare("SELECT COUNT(*) FROM comments WHERE parent_comment_id = :parent_id");
            $stmt->execute(['parent_id' => $parentCommentId]);
            $subCount = $stmt->fetchColumn();

            if ($subCount >= ($board['max_replies'] ?? 3)) {
                return $this->json(['error' => 'Sub-comment limit reached']);
            }

            $replyDepth = 1;
        }

        $stmt = $db->prepare("INSERT INTO comments (post_id, user_id, parent_comment_id, content, reply_depth) VALUES (:post_id, :user_id, :parent_comment_id, :content, :reply_depth)");
        $stmt->execute([
            'post_id' => $postId,
            'user_id' => $_SESSION['user']['user_id'],
            'parent_comment_id' => $parentCommentId,
            'content' => $content,
            'reply_depth' => $replyDepth
        ]);

        // Add points for commenting
        $commentPoint = (int)($board['point_comment'] ?? 0);
        if ($commentPoint != 0) {
            add_point($_SESSION['user']['user_id'], $commentPoint, 'Comment Write');
            $_SESSION['user']['point'] += $commentPoint; // Sync session
        }

        return $this->json(['success' => true]);
    }

    public function deleteComment() {
        if (!isset($_SESSION['user'])) {
            return $this->json(['error' => 'Unauthorized']);
        }

        $id = $_POST['id'] ?? null;
        if (!Csrf::verify($_POST['csrf_token'] ?? '')) {
             return $this->json(['error' => 'CSRF validation failed']);
        }
        $db = Database::getInstance();

        $stmt = $db->prepare("SELECT * FROM comments WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $comment = $stmt->fetch();

        if (!$comment || ($_SESSION['user']['user_id'] != $comment['user_id'] && $_SESSION['user']['role'] != 'admin')) {
            return $this->json(['error' => 'Unauthorized']);
        }

        // If parent comment (depth 0), delete its sub-comments first
        if ($comment['reply_depth'] == 0) {
            $stmt = $db->prepare("DELETE FROM comments WHERE parent_comment_id = :id");
            $stmt->execute(['id' => $id]);
        }

        $stmt = $db->prepare("DELETE FROM comments WHERE id = :id");
        $stmt->execute(['id' => $id]);

        return $this->json(['success' => true]);
    }

    public function uploadEditorImage($vars) {
        if (!isset($_SESSION['user'])) {
            return $this->json(['error' => 'Unauthorized']);
        }
        // CSRF verification for editor upload might be tricky with some editors, but advisable
        // if (!Csrf::verify($_POST['csrf_token'] ?? '')) return $this->json(['error' => 'CSRF']);

        $slug = $vars['slug'];
        
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $fileService = new FileService();
            $result = $fileService->uploadEditorImage($_FILES['image'], $slug);
            return $this->json($result);
        }

        return $this->json(['error' => 'Upload failed']);
    }

    public function download($vars) {
        $fileId = $vars['id'];
        $db = Database::getInstance();

        // 1. Get file info
        $stmt = $db->prepare("SELECT * FROM post_files WHERE id = :id");
        $stmt->execute(['id' => $fileId]);
        $file = $stmt->fetch();

        if (!$file) {
            die("File not found");
        }

        $filePath = CM_PUBLIC_PATH . $file['filepath'];
        if (!file_exists($filePath)) {
            die("Physical file missing");
        }

        // 2. Logging logic
        $ip = $_SERVER['REMOTE_ADDR'];
        $userId = isset($_SESSION['user']['user_id']) ? $_SESSION['user']['user_id'] : null;

        // Check if log exists for this file + IP (+ user if logged in)
        $sql = "SELECT id FROM file_downloads WHERE file_id = :file_id AND ip_address = :ip";
        $params = ['file_id' => $fileId, 'ip' => $ip];
        
        if ($userId) {
            $sql .= " AND user_id = :user_id";
            $params['user_id'] = $userId;
        } else {
            $sql .= " AND user_id IS NULL";
        }

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $log = $stmt->fetch();

        if ($log) {
            // Update exist log (increment count)
            $stmt = $db->prepare("UPDATE file_downloads SET download_count = download_count + 1 WHERE id = :id");
            $stmt->execute(['id' => $log['id']]);
        } else {
            // Insert new log
            $stmt = $db->prepare("INSERT INTO file_downloads (file_id, user_id, ip_address) VALUES (:file_id, :user_id, :ip)");
            $stmt->execute([
                'file_id' => $fileId,
                'user_id' => $userId,
                'ip' => $ip
            ]);
        }

        // 3. Serve file
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $file['original_name'] . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    }

}
