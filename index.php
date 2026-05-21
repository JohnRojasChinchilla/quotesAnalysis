<?php
require __DIR__ . '/config/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quotes Documents Analysis</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 80px 20px;
            text-align: center;
            margin-bottom: 50px;
        }
        .hero-section h1 {
            font-size: 3rem;
            margin-bottom: 20px;
            color: white;
        }
        .hero-section p {
            font-size: 1.3rem;
            margin-bottom: 30px;
        }
        .feature-box {
            padding: 30px;
            text-align: center;
            margin-bottom: 30px;
            border-radius: 8px;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
        }
        .feature-box i {
            font-size: 48px;
            margin-bottom: 15px;
        }
        .feature-box h4 {
            margin-top: 15px;
            color: #333;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">Quotes Analysis</a>
        </div>
    </nav>

    <div class="hero-section">
        <h1>Quotes Analysis Tool</h1>
        <p>Upload quotes documents and let AI analyze them to find the best option to buy</p>
        <a href="upload.php" class="btn btn-light btn-lg">Get Started</a>
    </div>

    <div class="container mb-5">
        <div class="row">
            <div class="col-md-4">
                <div class="feature-box">
                    <i>📤</i>
                    <h4>Easy Upload</h4>
                    <p>Upload multiple quote files in CSV, Excel, or PDF format</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-box">
                    <i>🤖</i>
                    <h4>AI Analysis</h4>
                    <p>Intelligent comparison and analysis using Azure Foundry</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-box">
                    <i>📊</i>
                    <h4>Results</h4>
                    <p>Get comparison table, analysis, and AI recommendations</p>
                </div>
            </div>
        </div>

        <div class="row mt-5">
            <div class="col-lg-8 mx-auto">
                <h3 class="text-center mb-4">How It Works</h3>
                <ol class="list-group list-group-numbered">
                    <li class="list-group-item">Upload your quote documents (CSV, Excel, PDF)</li>
                    <li class="list-group-item">Our AI automatically extracts quote information</li>
                    <li class="list-group-item">Get a detailed comparison table</li>
                    <li class="list-group-item">Receive an AI recommendation on the best option</li>
                    <li class="list-group-item">Download results as PDF for your records</li>
                </ol>
            </div>
        </div>

        <div class="text-center mt-5 mb-4">
            <a href="upload.php" class="btn btn-primary btn-lg">Start Analyzing Now</a>
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>