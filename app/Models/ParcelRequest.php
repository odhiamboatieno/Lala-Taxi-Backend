<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ParcelRequest extends Model
{
    protected $table = 'parcel_requests';

    protected $fillable = [
        'request_id',
        'parcel_category_id',
        'parcel_task_id',
        'category',
        'task_type',
        'recipient_name',
        'recipient_phone',
        'recipient_phone_dial_code',
        'recipient_phone_iso_code',
        'recipient_address',
        'parcel_description',
        'estimated_weight',
        'special_instructions',
        'pickup_contact_name',
        'pickup_contact_phone',
        'proof_of_delivery_required',
        'pod_signature',
        'pod_photo',
    ];

    protected $casts = [
        'proof_of_delivery_required' => 'boolean',
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(Request::class, 'request_id');
    }

    public function parcelCategory(): BelongsTo
    {
        return $this->belongsTo(ParcelCategory::class, 'parcel_category_id');
    }

    public function parcelTask(): BelongsTo
    {
        return $this->belongsTo(ParcelTask::class, 'parcel_task_id');
    }

    public function trip()
    {
        return $this->request ? $this->request->trip : null;
    }
}
