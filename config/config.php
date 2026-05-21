<?php
session_start();

// Load .env file
loadEnvFile(__DIR__ . '/../.env');

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
define('AZURE_API_VERSION', getenv('AZURE_API_VERSION') ?: '2024-05-01-preview'); // API version for Azure
define('MODEL_NAME', 'gpt-4o-mini'); // OpenAI GPT-4o-mini model
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

function loadEnvFile($envPath)
{
    if (!file_exists($envPath)) {
        return;
    }

    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Skip comments
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        // Parse KEY=VALUE
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);

            // Remove quotes if present
            if ((strpos($value, '"') === 0 && strrpos($value, '"') === strlen($value) - 1) ||
                (strpos($value, "'") === 0 && strrpos($value, "'") === strlen($value) - 1)) {
                $value = substr($value, 1, -1);
            }

            putenv("{$key}={$value}");
            $_ENV[$key] = $value;
        }
    }
}
