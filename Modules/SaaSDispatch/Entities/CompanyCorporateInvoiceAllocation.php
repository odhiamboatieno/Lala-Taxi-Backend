<?php

namespace Modules\SaaSDispatch\Entities;

use Illuminate\Database\Eloquent\Model;

class CompanyCorporateInvoiceAllocation extends Model
{
    protected $primaryKey = 'trip_id';
    public $incrementing = false;
    public $timestamps = false;
    protected $guarded = [];
    protected $casts = ['created_at' => 'datetime'];

    public function item()
    {
        return $this->belongsTo(CompanyCorporateInvoiceItem::class, 'invoice_item_id');
    }
}
