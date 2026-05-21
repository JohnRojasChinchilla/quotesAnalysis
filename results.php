<?php
require __DIR__ . '/config/config.php';

// Check if analysis exists
$analysis = $_SESSION['analysis_result'] ?? null;
if (!$analysis) {
    header('Location: upload.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analysis Results - Quotes Documents Analysis</title>
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
            <div class="col-lg-10 mx-auto">
                <h1 class="mb-4">Analysis Results</h1>

                <!-- Recommendation Box -->
                <?php if (!empty($analysis['recommendation'])): ?>
                    <div class="recommendation-box alert alert-success" role="alert">
                        <h3 class="alert-heading">✓ Recommended Quote</h3>
                        <p><strong>Best Option:</strong> <?php echo htmlspecialchars($analysis['recommendation']['best_option'] ?? 'N/A'); ?></p>

                        <?php if (!empty($analysis['recommendation']['reasons'])): ?>
                            <p><strong>Why:</strong></p>
                            <ul>
                                <?php foreach ($analysis['recommendation']['reasons'] as $reason): ?>
                                    <li><?php echo htmlspecialchars($reason); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>

                        <?php if (!empty($analysis['recommendation']['confidence'])): ?>
                            <p><small><strong>Confidence:</strong> <?php echo ucfirst(htmlspecialchars($analysis['recommendation']['confidence'])); ?></small></p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <!-- Comparison Table -->
                <?php if (!empty($analysis['comparison_table'])): ?>
                    <div class="table-responsive mt-4">
                        <h3 class="mb-3">Comparison Table</h3>
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <?php
                                    $firstRow = $analysis['comparison_table'][0];
                                    foreach ($firstRow as $key => $value):
                                    ?>
                                        <th><?php echo htmlspecialchars($key); ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($analysis['comparison_table'] as $row): ?>
                                    <tr>
                                        <?php foreach ($row as $value): ?>
                                            <td><?php echo htmlspecialchars($value); ?></td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>

                <!-- Detailed Analysis -->
                <?php if (!empty($analysis['analysis'])): ?>
                    <div class="mt-5">
                        <h3>Detailed Analysis</h3>
                        <div class="card">
                            <div class="card-body">
                                <?php echo nl2br(htmlspecialchars($analysis['analysis'])); ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Actions -->
                <div class="mt-5 mb-4">
                    <a href="api/download-pdf.php" class="btn btn-primary" target="_blank">
                        📥 Download as PDF
                    </a>
                    <a href="upload.php" class="btn btn-secondary">
                        📤 Upload New Files
                    </a>
                    <a href="index.php" class="btn btn-outline-secondary">
                        🏠 Home
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
