<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffMeetingDocumentation extends Model
{
    protected $table = 'staff_meeting_documentations';

    protected $fillable = [
        'cognito_id',
        'entry_number',
        'meeting_date',
        'store_label',
        'attendance_screenshot_url',
        'reports_screenshot_url',
        'meeting_outcome',
        'notes',
        'general_managers',
        'store_managers',
        'specialists',
        'submitted_at',
    ];

    protected $casts = [
        'entry_number' => 'integer',
        'meeting_date' => 'date',
        'general_managers' => 'array',
        'store_managers' => 'array',
        'specialists' => 'array',
        'submitted_at' => 'datetime',
    ];

    /**
     * Get CSV exportable columns.
     */
    public static function getCsvColumns(): array
    {
        return [
            'id',
            'cognito_id',
            'entry_number',
            'meeting_date',
            'store_label',
            'attendance_screenshot_url',
            'reports_screenshot_url',
            'meeting_outcome',
            'notes',
            'general_managers',
            'store_managers',
            'specialists',
            'submitted_at',
            'created_at',
        ];
    }
}
