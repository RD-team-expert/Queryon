<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import Approvals</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 40px;
            max-width: 500px;
            width: 100%;
        }

        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
            text-align: center;
        }

        .subtitle {
            color: #666;
            text-align: center;
            margin-bottom: 30px;
            font-size: 14px;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .form-group {
            margin-bottom: 24px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
            font-size: 14px;
        }

        .file-input-wrapper {
            position: relative;
            overflow: hidden;
            display: inline-block;
            width: 100%;
        }

        .file-input-wrapper input[type=file] {
            position: absolute;
            left: -9999px;
        }

        .file-input-label {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
            background-color: #f8f9fa;
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
        }

        .file-input-label:hover {
            background-color: #e9ecef;
            border-color: #667eea;
        }

        .file-input-label.has-file {
            background-color: #e7f3ff;
            border-color: #667eea;
        }

        .file-icon {
            font-size: 48px;
            margin-bottom: 10px;
        }

        .file-text {
            color: #666;
            font-size: 14px;
        }

        .file-name {
            margin-top: 10px;
            padding: 8px 12px;
            background-color: #fff;
            border-radius: 6px;
            color: #667eea;
            font-weight: 600;
            font-size: 13px;
        }

        .btn {
            width: 100%;
            padding: 14px 24px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }

        .btn:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
        }

        .info-box {
            margin-top: 20px;
            padding: 16px;
            background-color: #f8f9fa;
            border-radius: 8px;
            font-size: 13px;
            color: #666;
        }

        .info-box strong {
            color: #333;
            display: block;
            margin-bottom: 8px;
        }

        .info-box ul {
            margin-left: 20px;
            margin-top: 8px;
        }

        .info-box li {
            margin-bottom: 4px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Import Approvals</h1>
        <p class="subtitle">Upload an Excel file to import approval records</p>

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-error">
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-error">
                <ul style="margin-left: 20px;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('approvals.import') }}" method="POST" enctype="multipart/form-data" id="importForm">
            @csrf
            
            <div class="form-group">
                <label for="file">Select Excel File</label>
                <div class="file-input-wrapper">
                    <input type="file" name="file" id="file" accept=".xlsx,.xls" required>
                    <label for="file" class="file-input-label" id="fileLabel">
                        <div>
                            <div class="file-icon">📁</div>
                            <div class="file-text">Click to select Excel file (.xlsx, .xls)</div>
                            <div class="file-name" id="fileName" style="display: none;"></div>
                        </div>
                    </label>
                </div>
            </div>

            <button type="submit" class="btn" id="submitBtn">Upload & Import</button>
        </form>

        <div class="info-box">
            <strong>Expected Excel Columns:</strong>
            <ul>
                <li>APPROVALS_Id</li>
                <li>Details_Name_First / Details_Name_Last</li>
                <li>Details_TodaysDate</li>
                <li>Details_YourStore / Details_YourStore_Label</li>
                <li>Details_WhatIsTheThingThatYouNeedApprovalFor</li>
                <li>Details_NameTheManagerWhoYouConsulted_First / Last</li>
                <li>Details_Why</li>
                <li>TheFinalDecision_Decision / TheFinalDecision_Notes</li>
                <li>Entry_Status / Entry_DateCreated / Entry_DateSubmitted / Entry_DateUpdated</li>
            </ul>
        </div>
    </div>

    <script>
        const fileInput = document.getElementById('file');
        const fileLabel = document.getElementById('fileLabel');
        const fileName = document.getElementById('fileName');
        const submitBtn = document.getElementById('submitBtn');
        const importForm = document.getElementById('importForm');

        fileInput.addEventListener('change', function(e) {
            if (e.target.files.length > 0) {
                const file = e.target.files[0];
                fileName.textContent = file.name;
                fileName.style.display = 'block';
                fileLabel.classList.add('has-file');
            } else {
                fileName.style.display = 'none';
                fileLabel.classList.remove('has-file');
            }
        });

        importForm.addEventListener('submit', function() {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Importing...';
        });
    </script>
</body>
</html>
