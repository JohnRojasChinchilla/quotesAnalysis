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

                <!-- Action buttons -->
                <div class="mt-4">
                    <button id="uploadBtn" class="btn btn-primary btn-lg" disabled>Upload Files</button>
                    <button id="analyzeBtn" class="btn btn-success btn-lg" style="display: none;">Analyze Quotes</button>
                    <button id="resetBtn" class="btn btn-secondary" onclick="location.reload()">Reset</button>
                </div>

                <!-- Additional context -->
                <div class="mt-4">
                    <label for="contextInput" class="form-label">Additional Context (Optional):</label>
                    <textarea class="form-control" id="contextInput" rows="3" placeholder="e.g., We prefer faster delivery over lower cost"></textarea>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/app.js"></script>
</body>
</html>
