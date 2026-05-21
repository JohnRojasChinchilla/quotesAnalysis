<?php

namespace QuotesAnalysis;

class AzureFoundryClient
{
    private $apiKey;
    private $endpoint;
    private $modelName;
    private $timeout;
    private $apiVersion;

    public function __construct($apiKey = null, $endpoint = null, $modelName = null, $timeout = 60, $apiVersion = null)
    {
        $this->apiKey = $apiKey ?: AZURE_API_KEY;
        $this->endpoint = $endpoint ?: AZURE_API_ENDPOINT;
        $this->modelName = $modelName ?: MODEL_NAME;
        $this->timeout = $timeout;
        $this->apiVersion = $apiVersion ?: (defined('AZURE_API_VERSION') ? AZURE_API_VERSION : '2024-05-01-preview');

        if (empty($this->apiKey)) {
            throw new \Exception("Azure API Key not configured. Set AZURE_API_KEY environment variable.");
        }
    }

    public function getQuickSummary($quotesData)
    {
        $quotesText = $this->formatQuotesForAnalysis($quotesData);
        $prompt = $this->buildQuickSummaryPrompt($quotesText);
        return $this->callApi($prompt);
    }

    public function analyzeQuotes($quotesData, $context = '', $comparisonCriteria = '')
    {
        // Convert parsed data to text format for Claude
        $quotesText = $this->formatQuotesForAnalysis($quotesData);

        $prompt = $this->buildDetailedAnalysisPrompt($quotesText, $context, $comparisonCriteria);

        return $this->callApi($prompt);
    }

    private function formatQuotesForAnalysis($quotesData)
    {
        $formatted = "QUOTES DATA TO ANALYZE:\n\n";

        if (is_array($quotesData)) {
            // If it's structured data (from CSV/Excel)
            if (!empty($quotesData) && is_array(reset($quotesData))) {
                foreach ($quotesData as $index => $quote) {
                    $formatted .= "Quote #" . ($index + 1) . ":\n";
                    foreach ($quote as $key => $value) {
                        $valueStr = is_array($value) ? json_encode($value) : (string)$value;
                        $formatted .= "  {$key}: {$valueStr}\n";
                    }
                    $formatted .= "\n";
                }
            } else {
                // If it's unstructured text data
                foreach ($quotesData as $key => $value) {
                    if (is_string($value) && strlen($value) > 0) {
                        $formatted .= trim($value) . "\n\n";
                    }
                }
            }
        } else {
            $formatted .= $quotesData;
        }

        return $formatted;
    }

    private function buildQuickSummaryPrompt($quotesText)
    {
        return <<<PROMPT
You are an expert procurement analyst. Analyze the following quotes and provide a CONCISE summary.

Return ONLY valid JSON in this format:
{
    "total_quotes": 3,
    "vendors": ["Vendor A", "Vendor B", "Vendor C"],
    "key_differences": [
        "Price range varies from X to Y",
        "Delivery times range from A to B days",
        "Main differences in specifications: ..."
    ],
    "summary": "Brief 2-3 sentence summary of the quotes"
}

QUOTES DATA:
{$quotesText}

IMPORTANT: Return ONLY the JSON object, no markdown formatting or extra text.
PROMPT;
    }

    private function buildDetailedAnalysisPrompt($quotesText, $context = '', $comparisonCriteria = '')
    {
        $contextNote = $context ? "\n\nContext: {$context}" : '';
        $criteriaNote = $comparisonCriteria ? "\n\nUser wants to compare specifically: {$comparisonCriteria}" : '';

        return <<<PROMPT
You are an expert procurement analyst. Analyze the following quotes and provide a detailed comparison.

1. **COMPARISON TABLE**: Extract and organize all key fields (vendor/supplier name, price, delivery time, payment terms, specifications, etc.) into a clear comparison table
2. **ANALYSIS**: Compare the quotes across price, value, quality, and terms
3. **RECOMMENDATION**: Clearly state which quote is the best option to buy and why

You MUST respond with ONLY valid JSON in this exact format:
{
    "comparison_table": [
        {
            "field": "Vendor Name",
            "quote_1": "...",
            "quote_2": "...",
            "quote_3": "..."
        }
    ],
    "analysis": "Detailed comparison analysis...",
    "recommendation": {
        "best_option": "Quote #X (Vendor Name)",
        "reasons": ["reason 1", "reason 2", "reason 3"],
        "confidence": "high/medium/low"
    }
}

QUOTES DATA:
{$quotesText}{$contextNote}{$criteriaNote}

IMPORTANT: Return ONLY the JSON object, no markdown formatting or extra text.
PROMPT;
    }

    private function buildAnalysisPrompt($quotesText, $context = '')
    {
        $contextNote = $context ? "\nContext: {$context}" : '';

        return <<<PROMPT
You are an expert procurement analyst. Analyze the following quotes and provide:

1. **COMPARISON TABLE**: Extract and organize all key fields (vendor/supplier name, price, delivery time, payment terms, specifications, etc.) into a clear comparison table
2. **ANALYSIS**: Compare the quotes across price, value, quality, and terms
3. **RECOMMENDATION**: Clearly state which quote is the best option to buy and why

You MUST respond with ONLY valid JSON in this exact format:
{
    "comparison_table": [
        {
            "field": "Vendor Name",
            "quote_1": "...",
            "quote_2": "...",
            "quote_3": "..."
        }
    ],
    "analysis": "Detailed comparison analysis...",
    "recommendation": {
        "best_option": "Quote #X (Vendor Name)",
        "reasons": ["reason 1", "reason 2", "reason 3"],
        "confidence": "high/medium/low"
    }
}

QUOTES DATA:
{$quotesText}{$contextNote}

IMPORTANT: Return ONLY the JSON object, no markdown formatting or extra text.
PROMPT;
    }

    private function callApi($prompt)
    {
        // Prepare request for OpenAI/Azure GPT models
        $payload = [
            'model' => $this->modelName,
            'max_tokens' => 4096,
            'temperature' => 0.7,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $prompt,
                ]
            ],
        ];

        // Build full endpoint URL for Azure Foundry
        $url = rtrim($this->endpoint, '/') . '/chat/completions';

        // Only add api-version if not using /v1 path (OpenAI-compatible endpoint)
        if (strpos($this->endpoint, '/v1') === false) {
            $url .= '?api-version=' . urlencode($this->apiVersion);
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey,
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new \Exception("API request failed: {$error}");
        }

        if ($httpCode !== 200 && $httpCode !== 201) {
            throw new \Exception("API error (HTTP {$httpCode}): {$response}");
        }

        $decoded = json_decode($response, true);

        // OpenAI/Azure GPT response format
        if (!empty($decoded['choices'][0]['message']['content'])) {
            $content = $decoded['choices'][0]['message']['content'];
        } else {
            throw new \Exception("Invalid API response format: " . json_encode($decoded));
        }

        // Extract JSON from response (GPT sometimes wraps it in markdown)
        if (preg_match('/```(?:json)?\s*(\{[\s\S]*?\})\s*```/', $content, $matches)) {
            $jsonStr = $matches[1];
        } elseif (preg_match('/\{[\s\S]*\}/', $content, $matches)) {
            $jsonStr = $matches[0];
        } else {
            throw new \Exception("Failed to extract JSON from response. Response: " . substr($content, 0, 200));
        }

        $parsed = json_decode($jsonStr, true);
        if (!$parsed) {
            throw new \Exception("Failed to parse JSON response: " . substr($jsonStr, 0, 200));
        }

        return $parsed;
    }
}
