<?php
require __DIR__ . '/config/config.php';

// Check if uploaded files exist
if (empty($_SESSION['uploaded_files'])) {
    header('Location: upload.php');
    exit;
}

$chatHistory = $_SESSION['chat_history'] ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat with Quotes - Quotes Documents Analysis</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">Quotes Analysis</a>
        </div>
    </nav>

    <div class="container-fluid mt-4" style="max-width: 1200px; margin: 0 auto;">
        <div class="row h-100">
            <!-- Sidebar with file info -->
            <div class="col-lg-3 mb-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">📁 Uploaded Files</h5>
                    </div>
                    <div class="card-body">
                        <div class="files-reference">
                            <?php foreach ($_SESSION['uploaded_files'] as $file): ?>
                                <?php if (!empty($file['name'])): ?>
                                    <div class="mb-2">
                                        <small class="text-muted">
                                            <i class="bi bi-file"></i>
                                            <?php echo htmlspecialchars($file['name']); ?>
                                        </small>
                                        <?php if (!$file['success']): ?>
                                            <span class="badge bg-danger">Error</span>
                                        <?php else: ?>
                                            <span class="badge bg-success">Loaded</span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="mt-3">
                    <a href="upload.php" class="btn btn-secondary btn-sm w-100">← Back to Upload</a>
                    <a href="results.php" class="btn btn-outline-primary btn-sm w-100 mt-2">View Analysis</a>
                </div>
            </div>

            <!-- Main chat area -->
            <div class="col-lg-9">
                <div class="card h-100" style="display: flex; flex-direction: column;">
                    <div class="card-header bg-light">
                        <h4 class="mb-0">💬 Chat with Your Quotes</h4>
                        <small class="text-muted">Ask any questions about your quotes</small>
                    </div>

                    <!-- Chat messages -->
                    <div class="chat-messages" id="chatMessages" style="flex: 1; overflow-y: auto; padding: 1rem;">
                        <?php if (empty($chatHistory)): ?>
                            <div class="text-center text-muted mt-5">
                                <p>No messages yet. Start by asking a question about your quotes!</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($chatHistory as $msg): ?>
                                <div class="message message-<?php echo htmlspecialchars($msg['role']); ?> mb-3">
                                    <div class="message-content">
                                        <?php echo nl2br(htmlspecialchars($msg['content'])); ?>
                                    </div>
                                    <small class="text-muted d-block mt-1">
                                        <?php echo date('H:i', $msg['timestamp']); ?>
                                    </small>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Chat input form -->
                    <div class="card-footer bg-light border-top">
                        <form id="chatForm" class="chat-input-form">
                            <div class="input-group">
                                <input
                                    type="text"
                                    id="messageInput"
                                    class="form-control"
                                    placeholder="Ask a question about your quotes..."
                                    autocomplete="off"
                                >
                                <button class="btn btn-primary" type="submit" id="sendBtn">
                                    Send
                                </button>
                            </div>
                            <small class="text-muted d-block mt-2">
                                <i id="typingIndicator" style="display: none;">Typing...</i>
                            </small>
                        </form>
                    </div>
                </div>

                <!-- Chat controls -->
                <div class="mt-3 text-end">
                    <button class="btn btn-outline-danger btn-sm" id="clearChatBtn">Clear Chat History</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/chat.js"></script>
</body>
</html>
