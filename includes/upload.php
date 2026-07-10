<?php
/**
 * Secure File Upload Handler
 * Galaxy Portfolio CMS
 */

class FileUploader {
    private const MAX_SIZE = 10485760; // 10 MB
    private const ALLOWED_IMAGE = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'];
    private const ALLOWED_DOC   = ['application/pdf'];
    private const ALLOWED_ALL   = ['image/jpeg','image/png','image/gif','image/webp','image/svg+xml','application/pdf'];

    private string $uploadBase;
    private string $webBase = '/portfolio/uploads/';

    public function __construct() {
        $this->uploadBase = dirname(__DIR__) . '/uploads/';
    }

    public function upload(string $fileKey, string $folder = 'general', string $type = 'image'): array {
        if (!isset($_FILES[$fileKey]) || $_FILES[$fileKey]['error'] === UPLOAD_ERR_NO_FILE) {
            return ['success' => false, 'message' => 'No file provided.'];
        }
        $file = $_FILES[$fileKey];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'message' => 'Upload error: ' . $file['error']];
        }
        if ($file['size'] > self::MAX_SIZE) {
            return ['success' => false, 'message' => 'File too large. Maximum size: 10MB.'];
        }

        // Validate MIME type
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        $allowed = match($type) {
            'image' => self::ALLOWED_IMAGE,
            'doc'   => self::ALLOWED_DOC,
            default => self::ALLOWED_ALL,
        };
        if (!in_array($mime, $allowed)) {
            return ['success' => false, 'message' => 'Invalid file type: ' . $mime];
        }

        // Generate safe filename
        $ext = match($mime) {
            'image/jpeg'       => 'jpg',
            'image/png'        => 'png',
            'image/gif'        => 'gif',
            'image/webp'       => 'webp',
            'image/svg+xml'    => 'svg',
            'application/pdf'  => 'pdf',
            default            => 'bin',
        };
        $filename = uniqid('', true) . '_' . time() . '.' . $ext;
        $destDir  = $this->uploadBase . $folder . '/';
        $destPath = $destDir . $filename;

        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            return ['success' => false, 'message' => 'Failed to save file.'];
        }

        // Save to media library
        $webUrl = $this->webBase . $folder . '/' . $filename;
        try {
            Database::insert('media', [
                'admin_id'      => $_SESSION['user_id'] ?? null,
                'filename'      => $filename,
                'original_name' => sanitize($file['name']),
                'file_type'     => $mime,
                'file_size'     => $file['size'],
                'folder'        => $folder,
                'path'          => $folder . '/' . $filename,
                'url'           => $webUrl,
            ]);
        } catch (Exception $e) {
            // Non-fatal: media record not saved but file is ok
        }

        return [
            'success'  => true,
            'filename' => $filename,
            'path'     => $folder . '/' . $filename,
            'url'      => $webUrl,
            'size'     => $file['size'],
            'mime'     => $mime,
        ];
    }

    public function delete(string $relativePath): bool {
        $fullPath = $this->uploadBase . ltrim($relativePath, '/');
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }
        Database::query("DELETE FROM media WHERE path = ?", [$relativePath]);
        return true;
    }
}
