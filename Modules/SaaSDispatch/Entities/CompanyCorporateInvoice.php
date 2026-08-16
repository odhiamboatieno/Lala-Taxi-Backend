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
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'float',
        'tax_amount' => 'float',
        'total_amount' => 'float',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function account()
    {
        return $this->belongsTo(CompanyCorporateAccount::class, 'corporate_account_id');
    }
}
