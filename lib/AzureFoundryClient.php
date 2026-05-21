<?php

namespace QuotesAnalysis;

class AzureFoundryClient
{
    private $apiKey;
    private $endpoint;
    private $modelName;
    private $timeout;

    public function __construct($apiKey = null, $endpoint = null, $modelName = null, $timeout = 60)
    {
        $this->apiKey = $apiKey ?: AZURE_API_KEY;
        $this->endpoint = $endpoint ?: AZURE_API_ENDPOINT;
        $this->modelName = $modelName ?: MODEL_NAME;
        $this->timeout = $timeout;

        if (empty($this->apiKey)) {
            throw new \Exception("Azure API Key not configured. Set AZURE_API_KEY environment variable.");
        }
    }

    public function analyzeQuotes($quotesData, $context = '')
    {
        // Convert parsed data to text format for Claude
        $quotesText = $this->formatQuotesForAnalysis($quotesData);

        $prompt = $this->buildAnalysisPrompt($quotesText, $context);

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
                        $formatted .= "  {$key}: {$value}\n";
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

    private function buildAnalysisPrompt($quotesText, $context = '')
    {
        $contextNote = $context ? "\nContext: {$context}" : '';

        return <<<PROMPT
You are an expert procurement analyst. Analyze the following quotes and provide:

1. **COMPARISON TABLE**: Extract and organize all key fields (vendor/supplier name, price, delivery time, payment terms, specifications, etc.) into a clear comparison table
2. **ANALYSIS**: Compare the quotes across price, value, quality, and terms
3. **RECOMMENDATION**: Clearly state which quote is the best option to buy and why

Format your response as JSON with this structure:
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

{$quotesText}{$contextNote}

Ensure the JSON is valid and complete.
PROMPT;
    }

    private function callApi($prompt)
    {
        // Prepare request for Claude API
        $payload = [
            'model' => $this->modelName,
            'max_tokens' => 4096,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $prompt,
                ]
            ],
        ];

        $ch = curl_init($this->endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'x-api-key: ' . $this->apiKey,
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

        if ($httpCode !== 200) {
            throw new \Exception("API error (HTTP {$httpCode}): {$response}");
        }

        $decoded = json_decode($response, true);
        if (!$decoded || empty($decoded['content'][0]['text'])) {
            throw new \Exception("Invalid API response format");
        }

        $content = $decoded['content'][0]['text'];

        // Extract JSON from response
        if (preg_match('/\{[\s\S]*\}/', $content, $matches)) {
            return json_decode($matches[0], true);
        }

        throw new \Exception("Failed to parse API response as JSON");
    }
}
