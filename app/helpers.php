<?php

use App\Models\Company;

if (!function_exists('company')) {
    /**
     * Get the active company profile singleton.
     */
    function company(): Company
    {
        return Company::current();
    }
}
