<?php
require __DIR__ . '/../config/config.php';
require __DIR__ . '/../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

try {
    // Check if analysis exists
    if (empty($_SESSION['analysis_result'])) {
        throw new Exception('No analysis available. Please upload and analyze files first.');
    }

    $analysis = $_SESSION['analysis_result'];

    // Build HTML content
    $html = buildPdfHtml($analysis);

    // Generate PDF
    $options = new Options();
    $options->set('defaultFont', 'Arial');
    $options->set('isHtml5ParserEnabled', true);

    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    // Output PDF
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="quotes-analysis-' . date('Y-m-d-His') . '.pdf"');
    echo $dompdf->output();

} catch (Exception $e) {
    http_response_code(400);
    header('Content-Type: text/plain');
    echo 'Error generating PDF: ' . $e->getMessage();
}

function buildPdfHtml($analysis)
{
    $comparisonTable = '';
    if (!empty($analysis['comparison_table'])) {
        $comparisonTable = '<table style="width: 100%; border-collapse: collapse; margin-top: 20px;">';
        $comparisonTable .= '<thead><tr style="background-color: #f0f0f0;">';

        // Get all field names
        $fields = ['Field'];
        $quotes = [];
        if (!empty($analysis['comparison_table'][0])) {
            foreach ($analysis['comparison_table'][0] as $key => $value) {
                if ($key !== 'field') {
                    $quotes[] = $key;
                }
            }
        }
        $fields = array_merge($fields, $quotes);

        foreach ($fields as $field) {
            $comparisonTable .= '<th style="border: 1px solid #ddd; padding: 10px; text-align: left;">' . htmlspecialchars($field) . '</th>';
        }
        $comparisonTable .= '</tr></thead>';

        $comparisonTable .= '<tbody>';
        foreach ($analysis['comparison_table'] as $row) {
            $comparisonTable .= '<tr>';
            $comparisonTable .= '<td style="border: 1px solid #ddd; padding: 10px; font-weight: bold;">' . htmlspecialchars($row['field'] ?? '') . '</td>';
            foreach ($quotes as $quote) {
                $value = $row[$quote] ?? '';
                $comparisonTable .= '<td style="border: 1px solid #ddd; padding: 10px;">' . htmlspecialchars($value) . '</td>';
            }
            $comparisonTable .= '</tr>';
        }
        $comparisonTable .= '</tbody></table>';
    }

    $recommendation = '';
    if (!empty($analysis['recommendation'])) {
        $rec = $analysis['recommendation'];
        $recommendation = '<div style="margin-top: 30px; padding: 15px; background-color: #e8f5e9; border-left: 4px solid #4caf50;">';
        $recommendation .= '<h3 style="margin-top: 0; color: #2e7d32;">RECOMMENDATION</h3>';
        $recommendation .= '<p><strong>Best Option:</strong> ' . htmlspecialchars($rec['best_option'] ?? 'N/A') . '</p>';

        if (!empty($rec['reasons'])) {
            $recommendation .= '<p><strong>Reasons:</strong><ul>';
            foreach ($rec['reasons'] as $reason) {
                $recommendation .= '<li>' . htmlspecialchars($reason) . '</li>';
            }
            $recommendation .= '</ul></p>';
        }

        if (!empty($rec['confidence'])) {
            $recommendation .= '<p><strong>Confidence Level:</strong> ' . htmlspecialchars($rec['confidence']) . '</p>';
        }
        $recommendation .= '</div>';
    }

    $analysis_text = '';
    if (!empty($analysis['analysis'])) {
        $analysis_text = '<div style="margin-top: 30px;">';
        $analysis_text .= '<h3>DETAILED ANALYSIS</h3>';
        $analysis_text .= '<p>' . nl2br(htmlspecialchars($analysis['analysis'])) . '</p>';
        $analysis_text .= '</div>';
    }

    $timestamp = !empty($_SESSION['analysis_timestamp'])
        ? date('Y-m-d H:i:s', $_SESSION['analysis_timestamp'])
        : 'Unknown';

    return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Quotes Analysis Report</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        h1, h2, h3 { color: #1565c0; }
        .header { margin-bottom: 30px; border-bottom: 2px solid #1565c0; padding-bottom: 15px; }
        .timestamp { color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Quotes Analysis Report</h1>
        <p class="timestamp">Generated on: {$timestamp}</p>
    </div>

    {$comparisonTable}
    {$recommendation}
    {$analysis_text}
</body>
</html>
HTML;
}
