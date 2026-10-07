<?php

namespace Modules\SaaSDispatch\Entities;

use App\Models\Trips;
use Illuminate\Database\Eloquent\Model;

class CompanyCorporateInvoiceItem extends Model
{
    protected $guarded = [];
    protected $casts = ['amount' => 'decimal:2', 'tax_amount' => 'decimal:2', 'total_amount' => 'decimal:2', 'trip_snapshot' => 'array'];

    public function invoice()
    {
        return $this->belongsTo(CompanyCorporateInvoice::class, 'invoice_id');
    }

    public function trip()
    {
        return $this->belongsTo(Trips::class, 'trip_id');
    }

    public function allocation()
    {
        return $this->hasOne(CompanyCorporateInvoiceAllocation::class, 'invoice_item_id');
    }

    protected static function booted(): void
    {
        static::updating(fn () => throw new \DomainException('Issued invoice items are immutable.'));
        static::deleting(fn () => throw new \DomainException('Invoice item history cannot be deleted.'));
    }
}
