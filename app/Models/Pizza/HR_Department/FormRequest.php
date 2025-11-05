<?php

namespace Pizza\HR_Department\Models;

use Illuminate\Database\Eloquent\Model;

class FormRequest extends Model
{
    protected $table = 'form_requests';
    protected $fillable = [
        'language_id', 'store_id', 'first_name', 'last_name',
        'phone', 'email', 'request_date', 'request_type_id',
        'manager_first_name', 'manager_last_name', 'manager_title',
        'manager_note', 'manager_issue_is_solved'
    ];

    protected $casts = [
        'request_date' => 'date',
        'manager_issue_is_solved' => 'boolean',
    ];

    // Parent relationships
    public function language()
    {
        return $this->belongsTo(Language::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function requestType()
    {
        return $this->belongsTo(RequestType::class);
    }

    // Child relationships (one-to-one)
    public function virtualCheckIn()
    {
        return $this->hasOne(VirtualCheckIn::class);
    }

    public function hotNReady()
    {
        return $this->hasOne(HotNReady::class);
    }

    public function directDepositUpdate()
    {
        return $this->hasOne(DirectDepositUpdate::class);
    }

    public function requestWTwo()
    {
        return $this->hasOne(RequestWTwo::class);
    }

    public function requestCheckStub()
    {
        return $this->hasOne(RequestCheckStub::class);
    }

    public function requestTimeOff()
    {
        return $this->hasOne(RequestTimeOff::class);
    }

    public function updateAvailability()
    {
        return $this->hasOne(UpdateAvailability::class);
    }

    public function flexConfirm()
    {
        return $this->hasOne(FlexConfirm::class);
    }

    public function healthInsurance()
    {
        return $this->hasOne(HealthInsurance::class);
    }

    public function contactInfo()
    {
        return $this->hasOne(ContactInfo::class);
    }

    public function voluntaryResignation()
    {
        return $this->hasOne(VoluntaryResignation::class);
    }

    // Child relationships (one-to-many)
    public function inventoriesItems()
    {
        return $this->hasMany(InventoriesItem::class);
    }

    public function feedbacksComplaints()
    {
        return $this->hasMany(FeedbacksComplaint::class);
    }
}
