# Quotes Analysis Web App

An intelligent web application that analyzes quote documents and uses AI to recommend the best purchasing option.

## Features

- **Multi-format Support**: Upload quotes in CSV, Excel (XLSX/XLS), PDF, or plain text format
- **AI-Powered Analysis**: Uses Claude API via Azure Foundry to automatically analyze and compare quotes
- **Smart Comparison**: Automatically extracts key fields and creates a comparison table
- **Recommendations**: Get AI-powered recommendations on which quote offers the best value
- **PDF Export**: Download analysis results as a professional PDF report
- **Drag & Drop**: Easy file upload with drag-and-drop interface

## Requirements

- PHP 7.4 or higher
- XAMPP or similar local web server
- Composer (for dependency management)
- Azure Foundry API key (or Anthropic Claude API key)

## Installation

### 1. Clone/Setup the Project
```bash
cd c:/xampp/htdocs/quotesAnalysis
```

### 2. Install Dependencies
```bash
composer install
```

### 3. Configure Azure API Key
1. Copy `.env.example` to `.env`
2. Add your Azure Foundry API key:
   ```
   AZURE_API_KEY=your_api_key_here
   AZURE_API_ENDPOINT=https://api.anthropic.com/v1/messages
   ```

### 4. Set Permissions
Make sure the `uploads/` directory is writable:
```bash
chmod 755 uploads/
```

### 5. Start XAMPP
- Start Apache and MySQL from XAMPP Control Panel
- Navigate to `http://localhost/quotesAnalysis`

## Usage

### Basic Workflow

1. **Home Page**: Visit `http://localhost/quotesAnalysis`
2. **Upload Quotes**: Click "Get Started" and upload your quote documents
3. **Analyze**: Click "Analyze Quotes" to process the documents with AI
4. **View Results**: See the comparison table and AI recommendation
5. **Download**: Export results as PDF

### Supported File Formats

- **CSV**: Quote data in comma-separated values format
- **Excel**: XLSX or XLS files with quote data
- **PDF**: PDF documents containing quote information
- **Text**: Plain text files with quote content

## Project Structure

```
quotesAnalysis/
├── index.php                    # Home page
├── upload.php                   # File upload interface
├── results.php                  # Analysis results display
├── api/
│   ├── upload.php              # File upload API endpoint
│   ├── analyze.php             # Analysis API endpoint
│   └── download-pdf.php        # PDF export endpoint
├── lib/
│   ├── FileParser.php          # Multi-format file parsing
│   └── AzureFoundryClient.php  # Claude API integration
├── config/
│   └── config.php              # Configuration and utilities
├── assets/
│   ├── css/style.css           # Custom styling
│   └── js/app.js               # Frontend logic
├── uploads/                     # Temporary file storage
├── composer.json               # PHP dependencies
├── .env.example                # Environment variables template
└── .gitignore                  # Git ignore rules
```

## API Endpoints

### POST `/api/upload.php`
Uploads and parses quote files.

**Request**: Multipart form data with file uploads
**Response**: 
```json
{
  "success": true,
  "files": [
    {
      "name": "quotes.csv",
      "success": true,
      "filename": "unique_filename",
      "data": {...}
    }
  ]
}
```

### POST `/api/analyze.php`
Sends parsed quotes to Claude API for analysis.

**Request**:
```json
{
  "context": "Optional additional context"
}
```

**Response**:
```json
{
  "success": true,
  "analysis": {
    "comparison_table": [...],
    "analysis": "Detailed analysis text",
    "recommendation": {
      "best_option": "Quote #X (Vendor Name)",
      "reasons": [...],
      "confidence": "high"
    }
  }
}
```

### GET `/api/download-pdf.php`
Downloads the analysis results as PDF.

## Configuration

Edit `config/config.php` to customize:

- `MAX_FILE_SIZE`: Maximum file size (default: 10MB)
- `MAX_FILES`: Maximum files per analysis (default: 5)
- `SESSION_TIMEOUT`: Session expiration time (default: 1 hour)
- `MODEL_NAME`: Claude model to use (default: claude-opus-4-1)

## Environment Variables

Required environment variables (set in `.env`):

- `AZURE_API_KEY`: Your Azure Foundry API key
- `AZURE_API_ENDPOINT`: Azure Foundry endpoint URL
- `DEBUG_MODE`: Enable debug logging (optional)

## Troubleshooting

### Files not uploading
- Check that `uploads/` directory exists and is writable
- Verify file size is under 10MB limit
- Check supported file formats (CSV, Excel, PDF, TXT)

### API errors
- Verify `AZURE_API_KEY` is set in `.env`
- Check API endpoint is correct and accessible
- Enable `DEBUG_MODE` to see detailed error logs

### PDF export not working
- Ensure DomPDF is installed via Composer
- Check server has sufficient memory (min 256MB recommended)

## Technologies Used

- **PHP**: Backend server logic
- **Bootstrap 5**: Responsive UI framework
- **PhpSpreadsheet**: Excel/CSV parsing
- **Smalot/PdfParser**: PDF text extraction
- **DomPDF**: PDF generation
- **Claude API (via Azure Foundry)**: AI analysis
- **JavaScript**: Frontend interactivity

## License

MIT License

## Support

For issues or questions, please check the troubleshooting section or enable DEBUG_MODE for detailed logs.
