let selectedFiles = [];

const uploadArea = document.getElementById('uploadArea');
const fileInput = document.getElementById('fileInput');
const uploadBtn = document.getElementById('uploadBtn');
const getQuickSummaryBtn = document.getElementById('getQuickSummaryBtn');
const analyzeBtn = document.getElementById('analyzeBtn');
const chatBtn = document.getElementById('chatBtn');
const fileList = document.getElementById('fileList');
const fileListContent = document.getElementById('fileListContent');
const progressContainer = document.getElementById('progressContainer');
const progressBar = document.getElementById('progressBar');
const progressText = document.getElementById('progressText');
const errorContainer = document.getElementById('errorContainer');
const errorMessage = document.getElementById('errorMessage');
const successContainer = document.getElementById('successContainer');
const quickSummaryContainer = document.getElementById('quickSummaryContainer');
const quickSummaryContent = document.getElementById('quickSummaryContent');
const criteriaContainer = document.getElementById('criteriaContainer');
const contextInput = document.getElementById('contextInput');
const criteriaInput = document.getElementById('criteriaInput');

// Drag and drop
uploadArea.addEventListener('click', () => fileInput.click());
uploadArea.addEventListener('dragover', (e) => {
    e.preventDefault();
    uploadArea.classList.add('drag-over');
});
uploadArea.addEventListener('dragleave', () => {
    uploadArea.classList.remove('drag-over');
});
uploadArea.addEventListener('drop', (e) => {
    e.preventDefault();
    uploadArea.classList.remove('drag-over');
    handleFiles(e.dataTransfer.files);
});

fileInput.addEventListener('change', (e) => {
    handleFiles(e.target.files);
});

function handleFiles(files) {
    selectedFiles = Array.from(files);
    updateFileList();

    if (selectedFiles.length > 0) {
        uploadBtn.disabled = false;
        fileList.style.display = 'block';
    }
}

function updateFileList() {
    fileListContent.innerHTML = '';

    selectedFiles.forEach((file, index) => {
        const fileSize = (file.size / 1024 / 1024).toFixed(2);
        const fileItem = document.createElement('div');
        fileItem.className = 'file-item alert alert-secondary';
        fileItem.innerHTML = `
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <strong>${file.name}</strong><br>
                    <small>${fileSize} MB</small>
                </div>
                <button type="button" class="btn btn-sm btn-danger" onclick="removeFile(${index})">Remove</button>
            </div>
        `;
        fileListContent.appendChild(fileItem);
    });
}

function removeFile(index) {
    selectedFiles.splice(index, 1);
    updateFileList();

    if (selectedFiles.length === 0) {
        uploadBtn.disabled = true;
        fileList.style.display = 'none';
    }
}

uploadBtn.addEventListener('click', uploadFiles);
getQuickSummaryBtn.addEventListener('click', getQuickSummary);
analyzeBtn.addEventListener('click', analyzeQuotes);
chatBtn.addEventListener('click', () => {
    window.location.href = 'chat.php';
});

function uploadFiles() {
    if (selectedFiles.length === 0) {
        showError('Please select files to upload');
        return;
    }

    const formData = new FormData();
    selectedFiles.forEach(file => {
        formData.append('files[]', file);
    });

    progressContainer.style.display = 'block';
    errorContainer.style.display = 'none';
    successContainer.style.display = 'none';
    quickSummaryContainer.style.display = 'none';
    criteriaContainer.style.display = 'none';
    uploadBtn.disabled = true;

    // Simulate progress
    let progress = 0;
    const progressInterval = setInterval(() => {
        if (progress < 90) {
            progress += Math.random() * 40;
            progressBar.style.width = Math.min(progress, 90) + '%';
        }
    }, 100);

    fetch('api/upload.php', {
        method: 'POST',
        body: formData,
    })
        .then(response => response.json())
        .then(data => {
            clearInterval(progressInterval);
            progressBar.style.width = '100%';

            if (data.success) {
                successContainer.style.display = 'block';
                uploadBtn.style.display = 'none';
                getQuickSummaryBtn.style.display = 'inline-block';
                chatBtn.style.display = 'inline-block';
                progressContainer.style.display = 'none';

                // Check for any individual file errors
                const failedFiles = data.files.filter(f => !f.success);
                if (failedFiles.length > 0) {
                    const failedNames = failedFiles.map(f => f.name + ' (' + f.error + ')').join('\n');
                    showError('Some files failed to process:\n' + failedNames);
                }
            } else {
                showError(data.error || 'Upload failed');
                uploadBtn.disabled = false;
                progressContainer.style.display = 'none';
            }
        })
        .catch(error => {
            clearInterval(progressInterval);
            showError('Upload failed: ' + error.message);
            uploadBtn.disabled = false;
            progressContainer.style.display = 'none';
        });
}

function getQuickSummary() {
    progressContainer.style.display = 'block';
    progressBar.style.width = '0%';
    progressText.textContent = 'Generating quick summary...';
    errorContainer.style.display = 'none';
    getQuickSummaryBtn.disabled = true;

    // Simulate progress
    let progress = 0;
    const progressInterval = setInterval(() => {
        if (progress < 90) {
            progress += Math.random() * 30;
            progressBar.style.width = Math.min(progress, 90) + '%';
        }
    }, 150);

    fetch('api/quick-summary.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({}),
    })
        .then(response => response.json())
        .then(data => {
            clearInterval(progressInterval);
            progressBar.style.width = '100%';

            if (data.success) {
                displayQuickSummary(data.summary);
                criteriaContainer.style.display = 'block';
                analyzeBtn.style.display = 'inline-block';
                getQuickSummaryBtn.style.display = 'none';
                progressContainer.style.display = 'none';
            } else {
                showError(data.error || 'Failed to generate summary');
                getQuickSummaryBtn.disabled = false;
                progressContainer.style.display = 'none';
            }
        })
        .catch(error => {
            clearInterval(progressInterval);
            showError('Summary generation failed: ' + error.message);
            getQuickSummaryBtn.disabled = false;
            progressContainer.style.display = 'none';
        });
}

function displayQuickSummary(summary) {
    let html = '<div class="summary-content">';

    if (summary.total_quotes) {
        html += `<p><strong>Total Quotes:</strong> ${summary.total_quotes}</p>`;
    }

    if (summary.vendors && summary.vendors.length > 0) {
        html += `<p><strong>Vendors:</strong> ${summary.vendors.join(', ')}</p>`;
    }

    if (summary.key_differences && summary.key_differences.length > 0) {
        html += '<p><strong>Key Differences:</strong></p><ul>';
        summary.key_differences.forEach(diff => {
            html += `<li>${diff}</li>`;
        });
        html += '</ul>';
    }

    if (summary.summary) {
        html += `<p><strong>Summary:</strong> ${summary.summary}</p>`;
    }

    html += '</div>';
    quickSummaryContent.innerHTML = html;
    quickSummaryContainer.style.display = 'block';
}

function analyzeQuotes() {
    progressContainer.style.display = 'block';
    progressBar.style.width = '0%';
    progressText.textContent = 'Analyzing quotes...';
    errorContainer.style.display = 'none';
    analyzeBtn.disabled = true;

    // Simulate progress
    let progress = 0;
    const progressInterval = setInterval(() => {
        if (progress < 90) {
            progress += Math.random() * 30;
            progressBar.style.width = Math.min(progress, 90) + '%';
        }
    }, 150);

    const payload = {};
    if (contextInput.value.trim()) {
        payload.context = contextInput.value.trim();
    }
    if (criteriaInput.value.trim()) {
        payload.comparisonCriteria = criteriaInput.value.trim();
    }

    fetch('api/analyze.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(payload),
    })
        .then(response => response.json())
        .then(data => {
            clearInterval(progressInterval);
            progressBar.style.width = '100%';

            if (data.success) {
                progressText.textContent = 'Analysis complete! Redirecting...';
                setTimeout(() => {
                    window.location.href = 'results.php';
                }, 1500);
            } else {
                showError(data.error || 'Analysis failed');
                analyzeBtn.disabled = false;
                progressContainer.style.display = 'none';
            }
        })
        .catch(error => {
            clearInterval(progressInterval);
            showError('Analysis failed: ' + error.message);
            analyzeBtn.disabled = false;
            progressContainer.style.display = 'none';
        });
}

function showError(message) {
    errorContainer.style.display = 'block';
    errorMessage.textContent = message;
}
