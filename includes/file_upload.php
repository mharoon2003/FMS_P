<?php
function validateAndUploadFile($file, $upload_dir, $allowed_extensions = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'txt', 'jpg', 'png', 'zip', 'mp4', 'mov', 'avi', 'webm']) {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'No file uploaded or upload error'];
    }

    $max_size = 100 * 1024 * 1024;
    if ($file['size'] > $max_size) {
        return ['success' => false, 'error' => 'File size exceeds 10MB limit'];
    }

    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($file_extension, $allowed_extensions)) {
        return ['success' => false, 'error' => 'File type not allowed. Allowed: ' . implode(', ', $allowed_extensions)];
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    $allowed_mimes = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'text/plain',
        'image/jpeg',
        'image/png',
        'video/mp4',
        'video/quicktime', 
        'video/x-msvideo', 
        'video/webm',
        'application/zip',
        'application/x-zip-compressed'
    ];

    if (!in_array($mime_type, $allowed_mimes)) {
        return ['success' => false, 'error' => 'Invalid file type detected'];
    }

    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $safe_filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', basename($file['name']));
    $full_path = $upload_dir . $safe_filename;

    if (move_uploaded_file($file['tmp_name'], $full_path)) {
        // Return path relative to web root
        $web_path = str_replace($_SERVER['DOCUMENT_ROOT'], '', $full_path);
        return ['success' => true, 'path' => ltrim($web_path, '/')];
    }

    return ['success' => false, 'error' => 'Failed to save file'];
}