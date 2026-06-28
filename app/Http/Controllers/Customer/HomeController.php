<?php
namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Quiz;
use App\Settings\PlatformSettings;

class HomeController extends Controller
{
    public function index(PlatformSettings $platformSettings)
    {
        $categories = Category::where('is_active', true)->orderBy('sort_order')->take(12)->get();
        $featured = Quiz::with(['creator', 'category'])
            ->published()->public()
            ->orderByDesc('total_attempts')
            ->take(6)->get();
        return view('customer.home', compact('categories', 'featured', 'platformSettings'));
    }
}
