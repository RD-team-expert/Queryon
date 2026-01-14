# Approvals Excel Importer - Implementation Guide

## Overview

A simple Excel importer has been added to the Approvals system, allowing bulk import of approval records from Excel files exported from Cognito Forms.

---

## What Was Added

### 1. Import Methods in ApprovalController

**File:** `app/Http/Controllers/Pizza/ApprovalController.php`

#### New Methods:
- `showImportForm()` - Displays the import page
- `import()` - Processes the Excel file upload and imports data
- `mapExcelColumns()` - Maps Excel headers to column indexes
- `mapExcelRowToDatabase()` - Converts Excel row data to database format
- `parseExcelDate()` - Parses date values (M/D/YYYY format)
- `parseExcelDateTime()` - Parses datetime values (M/D/YYYY H:MM AM format)

### 2. Import Blade View

**File:** `resources/views/approvals/import.blade.php`

A clean, modern interface featuring:
- File selector with drag-and-drop visual feedback
- Upload button
- Success/error message display
- List of expected Excel columns
- Responsive design with gradient background

### 3. Web Routes

**File:** `routes/web.php`

```php
GET  /approvals/import  - Show import form
POST /approvals/import  - Process file upload
```

---

## How to Use

### Step 1: Access the Import Page

Navigate to: `http://your-domain.com/approvals/import`

### Step 2: Prepare Excel File

Your Excel file should have these exact column headers:

| Column Header | Description |
|---------------|-------------|
| `APPROVALS_Id` | Unique approval ID |
| `Details_Name_First` | Requester first name |
| `Details_Name_Last` | Requester last name |
| `Details_TodaysDate` | Request date (M/D/YYYY) |
| `Details_YourStore` | Store ID |
| `Details_YourStore_Label` | Store name |
| `Details_WhatIsTheThingThatYouNeedApprovalFor` | Approval reason |
| `Details_NameTheManagerWhoYouConsulted_First` | Manager first name |
| `Details_NameTheManagerWhoYouConsulted_Last` | Manager last name |
| `Details_Why` | Reason explanation |
| `TheFinalDecision_Decision` | Decision (optional) |
| `TheFinalDecision_Notes` | Decision notes (optional) |
| `Entry_Status` | Status (e.g., "Submitted") |
| `Entry_DateCreated` | Created date (M/D/YYYY H:MM AM) |
| `Entry_DateSubmitted` | Submitted date (M/D/YYYY H:MM AM) |
| `Entry_DateUpdated` | Updated date (M/D/YYYY H:MM AM) |

### Step 3: Upload File

1. Click the file selector area
2. Choose your Excel file (.xlsx or .xls)
3. Click "Upload & Import"

---

## Excel Column Mapping

```
Excel Column                                      → Database Column
────────────────────────────────────────────────────────────────────────
APPROVALS_Id                                      → cognito_id
Details_Name_First                                → requester_first_name
Details_Name_Last                                 → requester_last_name
Details_TodaysDate                                → request_date
Details_YourStore                                 → store_id
Details_YourStore_Label                           → store_label
Details_WhatIsTheThingThatYouNeedApprovalFor      → approval_reason
Details_NameTheManagerWhoYouConsulted_First       → consulted_manager_first_name
Details_NameTheManagerWhoYouConsulted_Last        → consulted_manager_last_name
Details_Why                                       → why
TheFinalDecision_Decision                         → decision
TheFinalDecision_Notes                            → decision_notes
Entry_Status                                      → entry_status
Entry_DateCreated                                 → entry_date_created
Entry_DateSubmitted                               → entry_date_submitted
Entry_DateUpdated                                 → entry_date_updated
```

**Static Values Set During Import:**
- `form_id` = "1318"
- `form_internal_name` = "APPROVALS"
- `form_name` = "APPROVALS"

---

## Features

### ✅ Smart Import Logic

1. **Upsert Behavior**: If a record with the same `cognito_id` exists, it will be updated; otherwise, a new record is created
2. **Error Handling**: Individual row errors don't stop the entire import
3. **Skip Empty Rows**: Automatically skips blank rows
4. **Date Parsing**: Handles multiple date formats:
   - Numeric Excel dates
   - M/D/YYYY format
   - M/D/YYYY H:MM AM format

### ✅ Validation

- File must be Excel format (.xlsx or .xls)
- Maximum file size: 10MB
- Required column: `APPROVALS_Id`

### ✅ Feedback

After import completes, you'll see:
- Number of records successfully imported
- Number of records skipped
- First 5 error messages (if any)

---

## Example Data

**Sample Excel Row:**
```
APPROVALS_Id: 103
Details_Name_First: test1
Details_Name_Last: test1
Details_TodaysDate: 1/14/2026
Details_YourStore: 36
Details_YourStore_Label: Cass City - 39
Details_WhatIsTheThingThatYouNeedApprovalFor: Menu manager changes
Details_NameTheManagerWhoYouConsulted_First: test11
Details_NameTheManagerWhoYouConsulted_Last: test11
Details_Why: why why?
TheFinalDecision_Decision: (empty)
TheFinalDecision_Notes: (empty)
Entry_Status: Submitted
Entry_DateCreated: 1/14/2026 10:41 AM
Entry_DateSubmitted: 1/14/2026 10:41 AM
Entry_DateUpdated: 1/14/2026 10:41 AM
```

**Resulting Database Record:**
```php
[
    'cognito_id' => '103',
    'form_id' => '1318',
    'form_name' => 'APPROVALS',
    'requester_first_name' => 'test1',
    'requester_last_name' => 'test1',
    'request_date' => '2026-01-14',
    'store_id' => '36',
    'store_label' => 'Cass City - 39',
    'approval_reason' => 'Menu manager changes',
    'consulted_manager_first_name' => 'test11',
    'consulted_manager_last_name' => 'test11',
    'why' => 'why why?',
    'decision' => null,
    'decision_notes' => null,
    'entry_status' => 'Submitted',
    'entry_date_created' => '2026-01-14 10:41:00',
    'entry_date_submitted' => '2026-01-14 10:41:00',
    'entry_date_updated' => '2026-01-14 10:41:00',
]
```

---

## Technical Details

### Database Transaction
All imports are wrapped in a database transaction. If a critical error occurs, all changes are rolled back.

### Logging
All import activities are logged:
- Import start/completion
- Row-level errors
- Success/failure statistics

### Performance
- Processes rows in memory efficiently
- Suitable for files with thousands of records
- Uses PhpSpreadsheet for Excel parsing

---

## Error Handling

### Common Errors and Solutions

| Error | Cause | Solution |
|-------|-------|----------|
| "Excel file is empty" | No data rows in file | Ensure file has data below header row |
| "The file field is required" | No file selected | Select a file before clicking upload |
| "The file must be a file of type: xlsx, xls" | Wrong file format | Use only .xlsx or .xls files |
| "Row X: ..." | Data issue in specific row | Check the row data and format |

---

## Summary

✅ **Files Modified:**
- `app/Http/Controllers/Pizza/ApprovalController.php` - Added import methods
- `routes/web.php` - Added import routes

✅ **Files Created:**
- `resources/views/approvals/import.blade.php` - Import UI

✅ **Features:**
- Simple file upload interface
- Upsert functionality (create or update)
- Date/datetime parsing
- Error reporting
- Success feedback

---

## Access URL

```
http://your-domain.com/approvals/import
```

No authentication is currently required, but you can add middleware to the routes if needed.
