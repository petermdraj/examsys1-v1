<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Settings\PlatformSettings;

class ForCreatorsController extends Controller
{
    public function index(PlatformSettings $platformSettings)
    {
        $plans = Plan::where('is_active', true)->orderBy('sort_order')->get();

        return view('customer.for-creators', compact('platformSettings', 'plans'));
    }
}
