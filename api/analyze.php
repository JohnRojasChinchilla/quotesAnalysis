<?php
require __DIR__ . '/../config/config.php';
require __DIR__ . '/../vendor/autoload.php';

use QuotesAnalysis\AzureFoundryClient;

header('Content-Type: application/json');

try {
    // Validate request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Only POST requests are allowed');
    }

    // Check if files have been uploaded
    if (empty($_SESSION['uploaded_files'])) {
        throw new Exception('No files uploaded. Please upload files first.');
    }

    // Collect parsed data from uploaded files
    $allQuotesData = [];
    $successCount = 0;

    foreach ($_SESSION['uploaded_files'] as $fileInfo) {
        if ($fileInfo['success']) {
            $allQuotesData[] = $fileInfo['data'];
            $successCount++;
        }
    }

    if ($successCount === 0) {
        throw new Exception('No successfully parsed files available for analysis');
    }

    // Get optional context from request
    $requestBody = json_decode(file_get_contents('php://input'), true);
    $context = $requestBody['context'] ?? '';

    // Call Azure Foundry for analysis
    $client = new AzureFoundryClient();
    $analysisResult = $client->analyzeQuotes($allQuotesData, $context);

    // Store result in session
    $_SESSION['analysis_result'] = $analysisResult;
    $_SESSION['analysis_timestamp'] = time();

    echo json_encode([
        'success' => true,
        'analysis' => $analysisResult,
        'message' => 'Analysis completed successfully',
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
    ]);
    if (DEBUG_MODE) {
        error_log($e->getTraceAsString());
    }
}
