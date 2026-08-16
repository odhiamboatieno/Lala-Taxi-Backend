<?php

namespace Modules\UserLevels\Http\Controllers;

class CustomerLevelController extends BaseUserLevelController
{
    protected function levelType(): string
    {
        return 'rider';
    }

    protected function viewBase(): string
    {
        return 'userlevels::customer_levels';
    }

    protected function routeBase(): string
    {
        return 'userlevels.customer-levels';
    }
}
