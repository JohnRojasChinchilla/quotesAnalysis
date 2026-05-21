<?php
session_start();

// Configuration for Quotes Analysis App
define('APP_NAME', 'Quotes Analysis');
define('APP_VERSION', '1.0.0');

// File upload settings
define('UPLOAD_DIR', __DIR__ . '/../uploads/temp/');
define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10MB
define('MAX_FILES', 5);
define('ALLOWED_EXTENSIONS', ['csv', 'xlsx', 'xls', 'pdf', 'txt']);

// Azure Foundry / Claude API settings
define('AZURE_API_KEY', getenv('AZURE_API_KEY') ?: ''); // Set via environment variable
define('AZURE_API_ENDPOINT', getenv('AZURE_API_ENDPOINT') ?: 'https://api.anthropic.com/v1/messages');
define('MODEL_NAME', 'claude-opus-4-1'); // Or whichever model you're using
define('API_TIMEOUT', 60); // seconds

// Session settings
define('SESSION_TIMEOUT', 3600); // 1 hour
define('SESSION_CLEANUP_PROBABILITY', 0.1); // 10% chance to cleanup old files

// Error settings
define('DEBUG_MODE', getenv('DEBUG_MODE') ?: false);

// Ensure upload directory exists
if (!is_dir(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}

// Helper functions
function getSessionId() {
    return session_id();
}

function getSessionUploadDir() {
    return UPLOAD_DIR . getSessionId() . '/';
}

function ensureSessionDir() {
    $dir = getSessionUploadDir();
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    return $dir;
}

function cleanupOldFiles() {
    if (mt_rand(1, 100) > (SESSION_CLEANUP_PROBABILITY * 100)) {
        return; // Skip cleanup this time
    }

    $baseDir = UPLOAD_DIR;
    if (!is_dir($baseDir)) return;

    $cutoffTime = time() - SESSION_TIMEOUT;
    foreach (scandir($baseDir) as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = $baseDir . $item;
        if (is_dir($path) && filemtime($path) < $cutoffTime) {
            deleteDirectory($path);
        }
    }
}

function deleteDirectory($dir) {
    if (!is_dir($dir)) return;
    foreach (scandir($dir) as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = $dir . '/' . $item;
        if (is_dir($path)) {
            deleteDirectory($path);
        } else {
            unlink($path);
        }
    }
    rmdir($dir);
}

// Initialize
cleanupOldFiles();
ensureSessionDir();
