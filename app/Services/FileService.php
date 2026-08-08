<?php

namespace App\Services;

use App\Core\Database;

class FileService {
    
    public function handleUploads($postId, $boardSlug, $files) {
        if (empty($files) || !isset($files['name'][0]) || empty($files['name'][0])) {
            return;
        }

        $db = Database::getInstance();
        $postId = (int)$postId;
        $basePublicPath = CM_PUBLIC_PATH;
        $uploadDir = '/data/' . $boardSlug . '/';
        $fullPath = rtrim($basePublicPath, '/\\') . $uploadDir;

        if (!is_dir($fullPath)) {
            mkdir($fullPath, 0777, true);
        }

        foreach ($files['name'] as $key => $name) {
            if ($files['error'][$key] === UPLOAD_ERR_OK) {
                $tmpName = $files['tmp_name'][$key];
                $originalName = basename($name);
                $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
                
                // Security check for extension
                if (in_array($extension, ['php', 'php3', 'php4', 'phtml', 'exe', 'sh', 'bat'])) {
                    continue; // Skip dangerous files
                }

                $newName = uniqid() . '.' . $extension;
                $targetFile = rtrim($fullPath, '/\\') . '/' . $newName;

                if (move_uploaded_file($tmpName, $targetFile)) {
                    try {
                        $stmt = $db->prepare("INSERT INTO post_files (post_id, filename, original_name, filepath, file_size, file_type) 
                                              VALUES (:post_id, :filename, :original_name, :filepath, :file_size, :file_type)");
                        
                        $fileSize = (int)($files['size'][$key] ?? 0);
                        $cleanOriginalName = mb_substr($originalName, 0, 200, 'UTF-8');
                        $fileType = substr($files['type'][$key] ?? 'application/octet-stream', 0, 90);

                        $stmt->execute([
                            ':post_id' => $postId,
                            ':filename' => $newName,
                            ':original_name' => $cleanOriginalName,
                            ':filepath' => $uploadDir . $newName,
                            ':file_size' => $fileSize,
                            ':file_type' => $fileType
                        ]);

                        // Resize if image
                        if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                            $this->resizeImage($targetFile, 1000);
                        }
                    } catch (\Throwable $e) {
                        error_log("File Upload Error: " . $e->getMessage());
                    }
                }
            }
        }
    }

    public function uploadEditorImage($file, $boardSlug) {
        $uploadDir = '/data/' . $boardSlug . '/';
        $fullPath = CM_PUBLIC_PATH . $uploadDir;

        if (!is_dir($fullPath)) {
            mkdir($fullPath, 0777, true);
        }

        if ($file['error'] === UPLOAD_ERR_OK) {
            $originalName = basename($file['name']);
            $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            
            // Security check
            if (in_array($extension, ['php', 'pl', 'py', 'asp', 'sh'])) {
                return ['error' => 'Invalid file type'];
            }

            $newName = 'editor_' . uniqid() . '.' . $extension;
            $targetFile = $fullPath . $newName;

            if (move_uploaded_file($file['tmp_name'], $targetFile)) {
                $this->resizeImage($targetFile, 1000);
                return ['url' => $uploadDir . $newName];
            }
        }
        return ['error' => 'Upload failed'];
    }

    private function resizeImage($filePath, $maxWidth) {
        if (!function_exists('getimagesize') || !function_exists('imagecreatetruecolor')) return;
        
        $info = @getimagesize($filePath);
        if (!$info) return;

        list($width, $height, $type) = $info;
        
        if ($width <= $maxWidth) return;

        $newWidth = $maxWidth;
        $newHeight = floor($height * ($maxWidth / $width));

        $src = null;
        switch ($type) {
            case IMAGETYPE_JPEG: 
                if (function_exists('imagecreatefromjpeg')) $src = imagecreatefromjpeg($filePath); 
                break;
            case IMAGETYPE_PNG: 
                if (function_exists('imagecreatefrompng')) $src = imagecreatefrompng($filePath); 
                break;
            case IMAGETYPE_GIF: 
                if (function_exists('imagecreatefromgif')) $src = imagecreatefromgif($filePath); 
                break;
            case IMAGETYPE_WEBP: 
                if (function_exists('imagecreatefromwebp')) $src = imagecreatefromwebp($filePath); 
                break;
        }

        if (!$src) return;

        $tmp = imagecreatetruecolor($newWidth, $newHeight);
        if (!$tmp) {
            imagedestroy($src);
            return;
        }
        
        // Preserve transparency for PNG and GIF
        if ($type == IMAGETYPE_PNG || $type == IMAGETYPE_GIF) {
            imagealphablending($tmp, false);
            imagesavealpha($tmp, true);
        }

        imagecopyresampled($tmp, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        switch ($type) {
            case IMAGETYPE_JPEG: imagejpeg($tmp, $filePath, 90); break;
            case IMAGETYPE_PNG: imagepng($tmp, $filePath); break;
            case IMAGETYPE_GIF: imagegif($tmp, $filePath); break;
            case IMAGETYPE_WEBP: imagewebp($tmp, $filePath, 90); break;
        }

        imagedestroy($tmp);
        imagedestroy($src);
    }

    public function deleteFile($filepath) {
        $fullPath = CM_PUBLIC_PATH . $filepath;
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }
    }
}
