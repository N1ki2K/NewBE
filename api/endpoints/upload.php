<?php
// ====================================================
// File Upload Endpoints
// ====================================================

class UploadEndpoints {

    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    // POST /api/upload/image
    public function uploadImage() {
        $user = AuthMiddleware::requireEditorOrAdmin();

        if (!isset($_FILES['image'])) {
            errorResponse('No image file provided', 400);
        }

        $file = $_FILES['image'];

        // Validate file
        $types = function_exists('get_allowed_image_types') ? get_allowed_image_types() : array();
        $this->validateFile($file, $types);

        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '_' . time() . '.' . $extension;
        $targetPath = UPLOAD_PICTURES_DIR . $filename;

        // Move file
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            errorResponse('Failed to upload file', 500);
        }

        // Generate URL (adjust to match your server structure)
        $url = rtrim(UPLOAD_PICTURES_PUBLIC_PATH, '/') . '/' . $filename;

        // Save to media_files table
        $this->db->insert('media_files', [
            'filename' => $filename,
            'original_name' => $file['name'],
            'file_path' => $targetPath,
            'file_type' => 'image',
            'mime_type' => $file['type'],
            'file_size' => $file['size'],
            'uploaded_by' => $user['id']
        ]);

        jsonResponse([
            'url' => $url,
            'filename' => $filename,
            'originalName' => $file['name'],
            'size' => $file['size'],
            'message' => 'Image uploaded successfully'
        ]);
    }

    // GET /api/upload/pictures
    public function getPicturesImages() {
        AuthMiddleware::authenticate();

        $files = glob(UPLOAD_PICTURES_DIR . '*');
        $images = [];

        foreach ($files as $file) {
            if (is_file($file)) {
                $filename = basename($file);
                $images[] = [
                    'filename' => $filename,
                    'url' => rtrim(UPLOAD_PICTURES_PUBLIC_PATH, '/') . '/' . $filename,
                    'size' => filesize($file),
                    'modified' => filemtime($file)
                ];
            }
        }

        // Sort by modified date descending
        usort($images, function($a, $b) {
            return $b['modified'] - $a['modified'];
        });

        jsonResponse(['images' => $images, 'total' => count($images)]);
    }

    // DELETE /api/upload/pictures/:filename
    public function deletePictureImage($filename) {
        AuthMiddleware::requireEditorOrAdmin();

        $filePath = UPLOAD_PICTURES_DIR . $filename;

        if (!file_exists($filePath)) {
            errorResponse('File not found', 404);
        }

        if (!unlink($filePath)) {
            errorResponse('Failed to delete file', 500);
        }

        // Remove from database if exists
        $this->db->delete('media_files', 'filename = ?', [$filename]);

        jsonResponse(['message' => 'Image deleted successfully']);
    }

    // POST /api/upload/document
    public function uploadDocument() {
        $user = AuthMiddleware::requireEditorOrAdmin();

        if (!isset($_FILES['document'])) {
            errorResponse('No document file provided', 400);
        }

        $file = $_FILES['document'];

        // Validate file
        $types = function_exists('get_allowed_document_types') ? get_allowed_document_types() : array();
        $this->validateFile($file, $types);

        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '_' . time() . '.' . $extension;
        $targetPath = UPLOAD_DOCUMENTS_DIR . $filename;

        // Move file
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            errorResponse('Failed to upload document', 500);
        }

        $url = rtrim(UPLOAD_DOCUMENTS_PUBLIC_PATH, '/') . '/' . $filename;

        // Save to media_files table
        $this->db->insert('media_files', [
            'filename' => $filename,
            'original_name' => $file['name'],
            'file_path' => $targetPath,
            'file_type' => 'document',
            'mime_type' => $file['type'],
            'file_size' => $file['size'],
            'uploaded_by' => $user['id']
        ]);

        jsonResponse([
            'url' => $url,
            'filename' => $filename,
            'originalName' => $file['name'],
            'size' => $file['size'],
            'message' => 'Document uploaded successfully'
        ]);
    }

    // GET /api/upload/documents
    public function getDocuments() {
        AuthMiddleware::authenticate();

        $files = glob(UPLOAD_DOCUMENTS_DIR . '*');
        $documents = [];

        foreach ($files as $file) {
            if (is_file($file)) {
                $filename = basename($file);
                $documents[] = [
                    'filename' => $filename,
                    'url' => rtrim(UPLOAD_DOCUMENTS_PUBLIC_PATH, '/') . '/' . $filename,
                    'size' => filesize($file),
                    'modified' => filemtime($file)
                ];
            }
        }

        usort($documents, function($a, $b) {
            return $b['modified'] - $a['modified'];
        });

        jsonResponse(['documents' => $documents, 'total' => count($documents)]);
    }

    // DELETE /api/upload/documents/:filename
    public function deleteDocument($filename) {
        AuthMiddleware::requireEditorOrAdmin();

        $filePath = UPLOAD_DOCUMENTS_DIR . $filename;

        if (!file_exists($filePath)) {
            errorResponse('File not found', 404);
        }

        if (!unlink($filePath)) {
            errorResponse('Failed to delete file', 500);
        }

        $this->db->delete('media_files', 'filename = ?', [$filename]);

        jsonResponse(['message' => 'Document deleted successfully']);
    }

    // POST /api/upload/presentation
    public function uploadPresentation() {
        $user = AuthMiddleware::requireEditorOrAdmin();

        if (!isset($_FILES['presentation'])) {
            errorResponse('No presentation file provided', 400);
        }

        $file = $_FILES['presentation'];

        // Validate file
        $types = function_exists('get_allowed_presentation_types') ? get_allowed_presentation_types() : array();
        $this->validateFile($file, $types);

        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '_' . time() . '.' . $extension;
        $targetPath = UPLOAD_PRESENTATIONS_DIR . $filename;

        // Move file
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            errorResponse('Failed to upload presentation', 500);
        }

        $url = rtrim(UPLOAD_PRESENTATIONS_PUBLIC_PATH, '/') . '/' . $filename;

        // Save to media_files table
        $this->db->insert('media_files', [
            'filename' => $filename,
            'original_name' => $file['name'],
            'file_path' => $targetPath,
            'file_type' => 'presentation',
            'mime_type' => $file['type'],
            'file_size' => $file['size'],
            'uploaded_by' => $user['id']
        ]);

        jsonResponse([
            'url' => $url,
            'filename' => $filename,
            'originalName' => $file['name'],
            'size' => $file['size'],
            'message' => 'Presentation uploaded successfully'
        ]);
    }

    // GET /api/upload/presentations
    public function getPresentations() {
        AuthMiddleware::authenticate();

        $files = glob(UPLOAD_PRESENTATIONS_DIR . '*');
        $presentations = [];

        foreach ($files as $file) {
            if (is_file($file)) {
                $filename = basename($file);
                $presentations[] = [
                    'filename' => $filename,
                    'url' => rtrim(UPLOAD_PRESENTATIONS_PUBLIC_PATH, '/') . '/' . $filename,
                    'size' => filesize($file),
                    'modified' => filemtime($file)
                ];
            }
        }

        usort($presentations, function($a, $b) {
            return $b['modified'] - $a['modified'];
        });

        jsonResponse(['presentations' => $presentations, 'total' => count($presentations)]);
    }

    // DELETE /api/upload/presentations/:filename
    public function deletePresentation($filename) {
        AuthMiddleware::requireEditorOrAdmin();

        $filePath = UPLOAD_PRESENTATIONS_DIR . $filename;

        if (!file_exists($filePath)) {
            errorResponse('File not found', 404);
        }

        if (!unlink($filePath)) {
            errorResponse('Failed to delete file', 500);
        }

        $this->db->delete('media_files', 'filename = ?', [$filename]);

        jsonResponse(['message' => 'Presentation deleted successfully']);
    }

    // POST /api/news/:newsId/attachments
    public function uploadNewsAttachment($newsId) {
        $user = AuthMiddleware::requireEditorOrAdmin();

        if (!isset($_FILES['file'])) {
            errorResponse('No file provided', 400);
        }

        // Verify news article exists
        $news = $this->db->fetchOne("SELECT id FROM news WHERE id = ?", [$newsId]);
        if (!$news) {
            errorResponse('News article not found', 404);
        }

        $file = $_FILES['file'];

        // Validate file (allow images and documents)
        $imageTypes = function_exists('get_allowed_image_types') ? get_allowed_image_types() : array();
        $documentTypes = function_exists('get_allowed_document_types') ? get_allowed_document_types() : array();
        $allowedTypes = array_merge($imageTypes, $documentTypes);
        $this->validateFile($file, $allowedTypes);

        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'news_' . $newsId . '_' . uniqid() . '.' . $extension;
        $targetPath = UPLOAD_DOCUMENTS_DIR . $filename;

        // Move file
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            errorResponse('Failed to upload file', 500);
        }

        // Save to news_attachments table
        $attachmentId = $this->db->insert('news_attachments', [
            'news_id' => $newsId,
            'filename' => $filename,
            'original_name' => $file['name'],
            'file_path' => $targetPath,
            'file_type' => $file['type'],
            'file_size' => $file['size']
        ]);

        jsonResponse([
            'id' => $attachmentId,
            'filename' => $filename,
            'originalName' => $file['name'],
            'url' => rtrim(UPLOAD_DOCUMENTS_PUBLIC_PATH, '/') . '/' . $filename,
            'message' => 'Attachment uploaded successfully'
        ], 201);
    }

    // Helper method to validate uploaded files
    private function validateFile($file, $allowedTypes) {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            errorResponse('File upload error: ' . $file['error'], 400);
        }

        if ($file['size'] > UPLOAD_MAX_SIZE) {
            errorResponse('File size exceeds maximum allowed size', 400);
        }

        if (!in_array($file['type'], $allowedTypes)) {
            errorResponse('File type not allowed', 400);
        }
    }
}
