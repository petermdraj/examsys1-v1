<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Subscription;

class PricingController extends Controller
{
    public function index()
    {
        $plans = Plan::where('is_active', true)->orderBy('sort_order')->get();
        $currentPlan = null;
        $currentSubscription = null;

        if (auth()->check() && auth()->user()->role === 'creator') {
            $currentSubscription = auth()->user()->activeSubscription()->with('plan')->first();
            $currentPlan = $currentSubscription?->plan;
        }

        return view('customer.pricing', compact('plans', 'currentPlan', 'currentSubscription'));
    }
}
