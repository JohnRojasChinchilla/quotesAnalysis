<?php

namespace QuotesAnalysis;

use PhpOffice\PhpSpreadsheet\IOFactory as SpreadsheetFactory;
use Smalot\PdfParser\Parser as PdfParser;

class FileParser
{
    private $file;
    private $filename;

    public function __construct($filePath, $originalFilename)
    {
        if (!file_exists($filePath)) {
            throw new \Exception("File not found: {$filePath}");
        }
        $this->file = $filePath;
        $this->filename = $originalFilename;
    }

    public function parse()
    {
        $ext = strtolower(pathinfo($this->filename, PATHINFO_EXTENSION));

        return match ($ext) {
            'csv' => $this->parseCSV(),
            'xlsx', 'xls' => $this->parseExcel(),
            'pdf' => $this->parsePDF(),
            'txt' => $this->parseText(),
            default => throw new \Exception("Unsupported file type: {$ext}"),
        };
    }

    private function parseCSV()
    {
        $data = [];
        if (($handle = fopen($this->file, 'r')) !== false) {
            $headers = null;
            while (($row = fgetcsv($handle)) !== false) {
                if ($headers === null) {
                    $headers = $row;
                } else {
                    $data[] = array_combine($headers, $row);
                }
            }
            fclose($handle);
        }
        return $data;
    }

    private function parseExcel()
    {
        try {
            $spreadsheet = SpreadsheetFactory::load($this->file);
            $worksheet = $spreadsheet->getActiveSheet();
            $data = [];
            $headers = null;
            $highestRow = $worksheet->getHighestRow();

            for ($row = 1; $row <= $highestRow; $row++) {
                $rowData = [];
                foreach ($worksheet->getRowIterator($row, $row) as $rowItem) {
                    foreach ($rowItem->getCellIterator() as $cell) {
                        $rowData[] = $cell->getValue();
                    }
                }

                if ($headers === null) {
                    $headers = $rowData;
                } else {
                    $data[] = array_combine($headers, $rowData);
                }
            }
            return $data;
        } catch (\Exception $e) {
            throw new \Exception("Failed to parse Excel file: " . $e->getMessage());
        }
    }

    private function parsePDF()
    {
        try {
            $parser = new PdfParser();
            $pdf = $parser->parseFile($this->file);
            $text = $pdf->getText();
            return $this->parseTextContent($text);
        } catch (\Exception $e) {
            throw new \Exception("Failed to parse PDF file: " . $e->getMessage());
        }
    }

    private function parseText()
    {
        $text = file_get_contents($this->file);
        return $this->parseTextContent($text);
    }

    private function parseTextContent($text)
    {
        return [
            'content' => $text,
            'text' => $text,
            'raw' => $text,
        ];
    }

    public static function validateFile($tmpPath, $originalName, $maxSize = null)
    {
        if ($maxSize === null) {
            $maxSize = 10 * 1024 * 1024; // 10MB default
        }

        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $allowedExts = ['csv', 'xlsx', 'xls', 'pdf', 'txt'];

        if (!in_array($ext, $allowedExts)) {
            return [
                'valid' => false,
                'error' => "File type not allowed. Supported: " . implode(', ', $allowedExts),
            ];
        }

        if (filesize($tmpPath) > $maxSize) {
            return [
                'valid' => false,
                'error' => "File size exceeds maximum of " . round($maxSize / 1024 / 1024) . "MB",
            ];
        }

        return ['valid' => true];
    }
}
