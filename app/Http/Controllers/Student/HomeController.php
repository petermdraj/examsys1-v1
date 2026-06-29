<?php
namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Quiz;
use App\Services\Quiz\QuizAssignmentService;
use App\Settings\PlatformSettings;

class HomeController extends Controller
{
    public function index(PlatformSettings $platformSettings)
    {
        $categories = Category::where('is_active', true)->orderBy('sort_order')->take(12)->get();
        $featuredQuery = Quiz::with(['creator', 'category'])
            ->published()->public()
            ->orderByDesc('total_attempts');

        if (auth()->check() && auth()->user()->role === 'student') {
            app(QuizAssignmentService::class)->scopeAssignedToStudent($featuredQuery, auth()->user());
        }

        $featured = $featuredQuery->take(6)->get();
        return view('student.home', compact('categories', 'featured', 'platformSettings'));
    }
}
