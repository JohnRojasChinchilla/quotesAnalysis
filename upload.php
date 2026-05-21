<?php
require __DIR__ . '/config/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Quotes - Quotes Documents Analysis</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">Quotes Analysis</a>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <h1 class="mb-4">Upload Quote Documents</h1>

                <div class="alert alert-info">
                    <strong>Supported formats:</strong> CSV, Excel (XLSX, XLS), PDF, Text files
                    <br><strong>Max file size:</strong> 10MB per file | <strong>Max files:</strong> 5
                </div>

                <!-- File upload area -->
                <div class="upload-area" id="uploadArea">
                    <div class="upload-content">
                        <i class="upload-icon">📄</i>
                        <h3>Drag & drop your files here</h3>
                        <p>or click to select files</p>
                        <input type="file" id="fileInput" multiple accept=".csv,.xlsx,.xls,.pdf,.txt" style="display: none;">
                    </div>
                </div>

                <!-- File list -->
                <div id="fileList" class="mt-4" style="display: none;">
                    <h5>Selected Files:</h5>
                    <div id="fileListContent"></div>
                </div>

                <!-- Progress bar -->
                <div id="progressContainer" class="mt-4" style="display: none;">
                    <div class="progress">
                        <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%"></div>
                    </div>
                    <p id="progressText" class="text-center mt-2">Uploading...</p>
                </div>

                <!-- Error messages -->
                <div id="errorContainer" class="mt-4" style="display: none;">
                    <div class="alert alert-danger" id="errorMessage"></div>
                </div>

                <!-- Success messages -->
                <div id="successContainer" class="mt-4" style="display: none;">
                    <div class="alert alert-success">
                        <strong>Upload successful!</strong> Files have been uploaded and parsed.
                    </div>
                </div>

                <!-- Quick Summary Section (shown after upload) -->
                <div id="quickSummaryContainer" class="mt-4" style="display: none;">
                    <div class="card bg-light">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">📊 Quick Summary</h5>
                        </div>
                        <div class="card-body" id="quickSummaryContent">
                            <!-- Summary will be populated here -->
                        </div>
                    </div>
                </div>

                <!-- Comparison Criteria Section -->
                <div id="criteriaContainer" class="mt-4" style="display: none;">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">What would you like to compare?</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="criteriaInput" class="form-label">Specify comparison criteria (optional):</label>
                                <textarea class="form-control" id="criteriaInput" rows="3" placeholder="e.g., 'Compare by price and delivery time', 'Focus on warranty terms and support', 'Prioritize cost per unit and performance'"></textarea>
                                <small class="text-muted">Leave empty for standard comparison</small>
                            </div>
                            <div class="form-group">
                                <label for="contextInput" class="form-label">Additional Context (optional):</label>
                                <textarea class="form-control" id="contextInput" rows="2" placeholder="e.g., We prefer faster delivery over lower cost"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action buttons -->
                <div class="mt-4">
                    <button id="uploadBtn" class="btn btn-primary btn-lg" disabled>Upload Files</button>
                    <button id="getQuickSummaryBtn" class="btn btn-info btn-lg" style="display: none;">Get Quick Summary</button>
                    <button id="analyzeBtn" class="btn btn-success btn-lg" style="display: none;">Get Detailed Analysis</button>
                    <button id="resetBtn" class="btn btn-secondary" onclick="location.reload()">Reset</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/app.js"></script>
</body>
</html>
