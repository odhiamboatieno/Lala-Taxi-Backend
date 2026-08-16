<?php

namespace Modules\UserLevels\Http\Controllers;

class DriverLevelController extends BaseUserLevelController
{
    protected function levelType(): string
    {
        return 'driver';
    }

    protected function viewBase(): string
    {
        return 'userlevels::driver_levels';
    }

    protected function routeBase(): string
    {
        return 'userlevels.driver-levels';
    }
}
