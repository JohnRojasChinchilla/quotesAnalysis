<?php
require __DIR__ . '/../config/config.php';
require __DIR__ . '/../vendor/autoload.php';

use QuotesAnalysis\FileParser;

header('Content-Type: application/json');

try {
    // Validate request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Only POST requests are allowed');
    }

    // Check if files are present
    if (empty($_FILES['files'])) {
        throw new Exception('No files uploaded');
    }

    $uploadDir = ensureSessionDir();
    $results = [];
    $fileCount = 0;

    // Handle multiple file uploads
    $files = $_FILES['files'];
    $fileArray = is_array($files['name']) ? $files : [$files];

    foreach ($fileArray as $file) {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $results[] = [
                'name' => $file['name'],
                'success' => false,
                'error' => 'Upload error: ' . $this->getUploadError($file['error']),
            ];
            continue;
        }

        // Validate file
        $validation = FileParser::validateFile($file['tmp_name'], $file['name'], MAX_FILE_SIZE);
        if (!$validation['valid']) {
            $results[] = [
                'name' => $file['name'],
                'success' => false,
                'error' => $validation['error'],
            ];
            continue;
        }

        // Check file count
        if ($fileCount >= MAX_FILES) {
            $results[] = [
                'name' => $file['name'],
                'success' => false,
                'error' => 'Maximum ' . MAX_FILES . ' files allowed',
            ];
            continue;
        }

        try {
            // Save file
            $destName = uniqid() . '_' . sanitizeFilename($file['name']);
            $destPath = $uploadDir . $destName;

            if (!move_uploaded_file($file['tmp_name'], $destPath)) {
                throw new Exception('Failed to save file');
            }

            // Parse file
            $parser = new FileParser($destPath, $file['name']);
            $parsedData = $parser->parse();

            $results[] = [
                'name' => $file['name'],
                'success' => true,
                'filename' => $destName,
                'data' => $parsedData,
            ];

            $fileCount++;
        } catch (Exception $e) {
            $results[] = [
                'name' => $file['name'],
                'success' => false,
                'error' => 'Parse error: ' . $e->getMessage(),
            ];
        }
    }

    // Store results in session for later analysis
    $_SESSION['uploaded_files'] = $results;

    echo json_encode([
        'success' => true,
        'files' => $results,
        'message' => 'Files uploaded successfully',
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
    ]);
}

function getUploadError($code)
{
    return match ($code) {
        UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
        UPLOAD_ERR_FORM_SIZE => 'File exceeds form MAX_FILE_SIZE',
        UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
        UPLOAD_ERR_NO_FILE => 'No file was uploaded',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file',
        UPLOAD_ERR_EXTENSION => 'Upload blocked by extension',
        default => 'Unknown upload error',
    };
}

function sanitizeFilename($filename)
{
    return preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
}
