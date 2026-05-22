<?php
require __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

try {
    $_SESSION['chat_history'] = [];
    echo json_encode(['success' => true]);
} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
