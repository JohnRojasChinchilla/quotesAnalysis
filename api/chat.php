<?php
require __DIR__ . '/../config/config.php';
require __DIR__ . '/../vendor/autoload.php';

use QuotesAnalysis\AzureFoundryClient;

header('Content-Type: application/json');

try {
    // Check if uploaded files exist
    if (empty($_SESSION['uploaded_files'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'No files uploaded. Please upload quote files first.'
        ]);
        exit;
    }

    // Get the user message
    $message = trim($_POST['message'] ?? '');
    if (empty($message)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Message cannot be empty.'
        ]);
        exit;
    }

    // Extract quotes from uploaded files (with file names for context)
    $quotesData = [];
    foreach ($_SESSION['uploaded_files'] as $fileInfo) {
        if (!empty($fileInfo['data']) && $fileInfo['success']) {
            $fileName = $fileInfo['name'] ?? 'Unknown';

            if (is_array($fileInfo['data'])) {
                // For structured data, wrap with file name
                foreach ($fileInfo['data'] as $item) {
                    $quotesData[] = [
                        '_file' => $fileName,
                        '_data' => $item
                    ];
                }
            } else {
                // For unstructured data (text)
                $quotesData[] = [
                    '_file' => $fileName,
                    '_data' => $fileInfo['data']
                ];
            }
        }
    }

    if (empty($quotesData)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'No quotes found in uploaded files.'
        ]);
        exit;
    }

    // Initialize chat history if needed
    if (empty($_SESSION['chat_history'])) {
        $_SESSION['chat_history'] = [];
    }

    // Create Azure client and get response
    $client = new AzureFoundryClient();
    $response = $client->sendChatMessage($message, $quotesData);

    // Store messages in session
    $_SESSION['chat_history'][] = [
        'role' => 'user',
        'content' => $message,
        'timestamp' => time()
    ];

    $_SESSION['chat_history'][] = [
        'role' => 'assistant',
        'content' => $response,
        'timestamp' => time()
    ];

    // Return response
    echo json_encode([
        'success' => true,
        'message' => $response
    ]);

} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error: ' . $e->getMessage()
    ]);
}
?>
