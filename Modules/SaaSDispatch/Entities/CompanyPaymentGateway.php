<?php

namespace Modules\SaaSDispatch\Entities;

use Illuminate\Database\Eloquent\Model;

class CompanyPaymentGateway extends Model
{
    protected $table = 'company_payment_gateways';

    protected $guarded = [];
}
