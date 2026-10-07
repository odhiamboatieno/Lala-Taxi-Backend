<?php

namespace Modules\SaaSDispatch\Entities;

use App\Models\Company;
use Illuminate\Database\Eloquent\Model;

class CompanyCorporateInvoice extends Model
{
    protected $fillable = [
        'company_id',
        'corporate_account_id',
        'invoice_number',
        'invoice_date',
        'due_date',
        'subtotal',
        'tax_amount',
        'total_amount',
        'status',
        'notes',
        'trip_backed', 'currency_code', 'billing_timezone', 'bill_to_snapshot', 'period_start', 'period_end',
        'revision', 'sent_at', 'paid_at', 'voided_at', 'sent_by', 'paid_by', 'voided_by', 'void_reason', 'settlement_reference',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'trip_backed' => 'boolean',
        'bill_to_snapshot' => 'array',
        'period_start' => 'datetime', 'period_end' => 'datetime',
        'sent_at' => 'datetime', 'paid_at' => 'datetime', 'voided_at' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function account()
    {
        return $this->belongsTo(CompanyCorporateAccount::class, 'corporate_account_id');
    }

    public function items()
    {
        return $this->hasMany(CompanyCorporateInvoiceItem::class, 'invoice_id');
    }

    protected static function booted(): void
    {
        static::deleting(fn () => throw new \DomainException('Void unpaid invoices instead of deleting financial history.'));
    }
}
