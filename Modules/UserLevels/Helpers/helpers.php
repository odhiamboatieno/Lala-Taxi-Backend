<?php

if (! function_exists('user_levels_module_enabled')) {
    function user_levels_module_enabled(): bool
    {
        return (bool) config('user_levels.name');
    }
}
