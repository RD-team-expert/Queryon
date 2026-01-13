# Approvals Cognito Webhook Implementation

## Overview

This document describes the implementation of the Approvals Cognito webhook system, which handles CRUD operations for approval requests submitted through Cognito Forms.

## Date Completed

**January 12, 2026**

---

## Files Created

### 1. Database Migration

**File:** `database/migrations/2026_01_12_180000_create_approvals_table.php`

This migration creates the `approvals` table with the following structure:

| Column Group | Columns |
|-------------|---------|
| **Cognito Form Info** | `cognito_id` (unique), `form_id`, `form_internal_name`, `form_name` |
| **Details Section** | `approval_reason`, `why`, `requester_first_name`, `requester_last_name`, `request_date`, `store_id`, `store_label`, `consulted_manager_first_name`, `consulted_manager_last_name` |
| **Final Decision** | `decision`, `decision_notes` |
| **Entry Metadata** | `entry_number`, `entry_admin_link`, `entry_date_created`, `entry_date_submitted`, `entry_date_updated`, `entry_public_link`, `entry_final_view_link`, `document_1_link`, `document_2_link` |
| **Entry Origin** | `origin_ip_address`, `origin_city`, `origin_country_code`, `origin_region`, `origin_timezone`, `origin_user_agent`, `origin_is_imported` |
| **Entry User** | `user_email`, `user_name` |
| **Entry Status** | `entry_action`, `entry_role`, `entry_status`, `entry_version` |

**Indexes:** `entry_number`, `store_id`, `request_date`, `decision`, `entry_status`

---

### 2. Model

**File:** `app/Models/Pizza/Approval.php`

The Eloquent model includes:

- **Fillable fields** for all table columns
- **Attribute casting** for dates, timestamps, booleans, and integers
- **Accessor methods:**
  - `getRequesterFullNameAttribute()` - Returns full requester name
  - `getConsultedManagerFullNameAttribute()` - Returns full consulted manager name
- **Query scopes:**
  - `scopeWithStatus($status)` - Filter by entry status
  - `scopeForStore($storeId)` - Filter by store
  - `scopeDateRange($startDate, $endDate)` - Filter by date range
  - `scopeWithDecision($decision)` - Filter by decision
- **Static method:** `getCsvColumns()` - Returns columns for CSV export

---

### 3. Controller

**File:** `app/Http/Controllers/Pizza/ApprovalController.php`

The controller implements clean, well-structured methods:

#### Public Methods

| Method | HTTP Method | Description |
|--------|-------------|-------------|
| `create()` | POST | Creates a new approval from Cognito webhook |
| `update()` | POST | Updates an existing approval (with upsert capability) |
| `delete()` | POST | Deletes an approval record |
| `getData()` | GET | Returns filtered JSON data |
| `exportCsv()` | GET | Exports filtered data to CSV |

#### Private Helper Methods

| Method | Description |
|--------|-------------|
| `getJsonPayload()` | Safely parses JSON from request body |
| `mapJsonToDatabase()` | Maps Cognito JSON structure to database columns |
| `parseTimestamp()` | Parses ISO 8601 timestamps to Carbon |
| `successResponse()` | Returns standardized success JSON |
| `errorResponse()` | Returns standardized error JSON |

---

### 4. Routes

**File:** `routes/api.php`

The following routes were added:

```php
/**************** Approvals Cognito Webhooks ****************/
Route::post('/approvals/create', [ApprovalController::class, 'create']);
Route::post('/approvals/update', [ApprovalController::class, 'update']);
Route::post('/approvals/delete', [ApprovalController::class, 'delete']);

// Inside check.secret middleware group
Route::get('/approvals/export', [ApprovalController::class, 'exportCsv']);

// Inside auth.verify middleware group
Route::get('/approvals/data', [ApprovalController::class, 'getData']);
```

---

## API Endpoints

### Create Approval

**POST** `/api/approvals/create`

Creates a new approval record from a Cognito webhook.

**Request Body:** Raw JSON from Cognito Forms

**Response:**
```json
{
  "success": true,
  "message": "Approval created successfully",
  "data": { /* approval object */ }
}
```

---

### Update Approval

**POST** `/api/approvals/update`

Updates an existing approval. If the record doesn't exist, it will be created (upsert behavior).

**Request Body:** Raw JSON from Cognito Forms

**Response:**
```json
{
  "success": true,
  "message": "Approval updated successfully",
  "data": { /* approval object */ }
}
```

---

### Delete Approval

**POST** `/api/approvals/delete`

Deletes an approval record by `cognito_id` or `entry_number`.

**Request Body:** Raw JSON from Cognito Forms

**Response:**
```json
{
  "success": true,
  "message": "Approval deleted successfully"
}
```

---

### Get Data (JSON)

**GET** `/api/approvals/data`

**Authentication:** Requires `auth.verify` middleware

**Query Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| `start_date` | string | Filter by start date (YYYY-MM-DD) |
| `end_date` | string | Filter by end date (YYYY-MM-DD) |
| `store_id` | string | Filter by store ID |
| `status` | string | Filter by entry status |
| `decision` | string | Filter by decision |

**Response:**
```json
{
  "success": true,
  "record_count": 10,
  "data": [ /* array of approval objects */ ]
}
```

---

### Export CSV

**GET** `/api/approvals/export`

**Authentication:** Requires `check.secret` middleware

**Query Parameters:** Same as Get Data endpoint

**Response:** CSV file download (`approvals_export_YYYY-MM-DD_HHmmss.csv`)

---

## Cognito Form JSON Mapping

The following mapping shows how Cognito JSON fields map to database columns:

```
Cognito JSON Path                                → Database Column
──────────────────────────────────────────────────────────────────
Id                                               → cognito_id
Form.Id                                          → form_id
Form.InternalName                                → form_internal_name
Form.Name                                        → form_name
Details.WhatIsTheThingThatYouNeedApprovalFor     → approval_reason
Details.Why                                      → why
Details.Name.First                               → requester_first_name
Details.Name.Last                                → requester_last_name
Details.TodaysDate                               → request_date
Details.YourStore.Id                             → store_id
Details.YourStore.Label                          → store_label
Details.NameTheManagerWhoYouConsulted.First      → consulted_manager_first_name
Details.NameTheManagerWhoYouConsulted.Last       → consulted_manager_last_name
TheFinalDecision.Decision                        → decision
TheFinalDecision.Notes                           → decision_notes
Entry.Number                                     → entry_number
Entry.AdminLink                                  → entry_admin_link
Entry.DateCreated                                → entry_date_created
Entry.DateSubmitted                              → entry_date_submitted
Entry.DateUpdated                                → entry_date_updated
Entry.PublicLink                                 → entry_public_link
Entry.FinalViewLink                              → entry_final_view_link
Entry.Document1                                  → document_1_link
Entry.Document2                                  → document_2_link
Entry.Origin.IpAddress                           → origin_ip_address
Entry.Origin.City                                → origin_city
Entry.Origin.CountryCode                         → origin_country_code
Entry.Origin.Region                              → origin_region
Entry.Origin.Timezone                            → origin_timezone
Entry.Origin.UserAgent                           → origin_user_agent
Entry.Origin.IsImported                          → origin_is_imported
Entry.User.Email                                 → user_email
Entry.User.Name                                  → user_name
Entry.Action                                     → entry_action
Entry.Role                                       → entry_role
Entry.Status                                     → entry_status
Entry.Version                                    → entry_version
```

---

## Best Practices Applied

### 1. **Clean Code Structure**
- Separated concerns with private helper methods
- Consistent JSON response format
- Proper error handling with try-catch blocks

### 2. **Database Design**
- Added indexes for commonly queried columns
- Used appropriate data types (date, timestamp, boolean)
- All fields nullable to handle partial data

### 3. **Logging**
- Comprehensive logging for debugging
- Log levels: `info`, `warning`, `error`
- Structured log context for easy searching

### 4. **Query Scopes**
- Reusable query filters in the model
- Chainable scope methods for flexible queries

### 5. **Upsert Behavior**
- Update endpoint creates record if not found
- Prevents data loss from timing issues

### 6. **Security**
- Export endpoints protected by `check.secret` middleware
- Data endpoints protected by `auth.verify` middleware

---

## Usage Examples

### Cognito Webhook Configuration

In Cognito Forms, set up webhooks for the APPROVALS form:

| Event | Webhook URL |
|-------|-------------|
| On Submit | `https://your-domain.com/api/approvals/create` |
| On Update | `https://your-domain.com/api/approvals/update` |
| On Delete | `https://your-domain.com/api/approvals/delete` |

### Fetching Data with Filters

```bash
# Get all approvals for a specific store
GET /api/approvals/data?store_id=946-2

# Get approvals within a date range
GET /api/approvals/data?start_date=2026-01-01&end_date=2026-01-31

# Get only approved records
GET /api/approvals/data?decision=Approved

# Combine filters
GET /api/approvals/data?store_id=946-2&status=Submitted&start_date=2026-01-01
```

### Exporting to CSV

```bash
# Export all approvals
GET /api/approvals/export

# Export with filters
GET /api/approvals/export?store_id=946-2&start_date=2026-01-01&end_date=2026-01-31
```

---

## Testing

To test the webhook manually, you can use the sample JSON from `cog.json`:

```bash
curl -X POST https://your-domain.com/api/approvals/create \
  -H "Content-Type: application/json" \
  -d @cog.json
```

---

## Future Enhancements

1. **Validation Rules** - Add request validation with Laravel Form Requests
2. **Events/Observers** - Trigger notifications on status changes
3. **API Resources** - Use Laravel API Resources for response transformation
4. **Pagination** - Add pagination to `getData()` for large datasets
5. **Soft Deletes** - Implement soft deletes for data recovery

---

## Summary

| Item | Status |
|------|--------|
| Database Migration | ✅ Created & Migrated |
| Eloquent Model | ✅ Created |
| Controller (CRUD) | ✅ Created |
| CSV Export | ✅ Implemented |
| Routes | ✅ Registered |
| Documentation | ✅ Complete |
