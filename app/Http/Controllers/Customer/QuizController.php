<?php
namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Quiz;
use Illuminate\Support\Facades\Cache;

class QuizController extends Controller
{
    public function categories()
    {
        $categories = Category::where('is_active', true)
            ->whereNull('parent_id')
            ->with(['children' => fn($q) => $q->where('is_active', true)->orderBy('sort_order')])
            ->orderBy('sort_order')
            ->get();

        // Load quiz counts for parents and children
        // Use recursive count so depth-3+ descendants are included
        $allCats = Category::where('is_active', true)->get(['id', 'parent_id']);
        foreach ($categories as $cat) {
            $ids = array_merge([$cat->id], Category::descendantIds($cat->id, $allCats));
            $cat->total_quizzes_count = Quiz::whereIn('category_id', $ids)
                ->where('status', 'published')->where('visibility', 'public')->count();
        }

        return view('customer.categories', compact('categories'));
    }

    public function index()
    {
        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();
        $quizzes = Quiz::with(['creator', 'category'])
            ->published()->public()
            ->when(request('q'), fn($query, $q) => $query->where('title', 'like', "%$q%"))
            ->when(request('category'), fn($query, $cat) => $query->whereHas('category', fn($q) => $q->where('slug', $cat)))
            ->when(request('price') === 'free', fn($query) => $query->where('price', 0))
            ->when(request('price') === 'paid', fn($query) => $query->where('price', '>', 0))
            ->paginate(9)->withQueryString();
        return view('customer.quiz.index', compact('quizzes', 'categories'));
    }

    public function show(string $slug)
    {
        $quiz = Quiz::with(['creator', 'category', 'questions.options'])->where('slug', $slug)->published()->firstOrFail();
        $isEnrolled = auth()->check() && $quiz->enrollments()->where('user_id', auth()->id())->exists();

        // Leaderboard: top 10 by percentage, cached 5 min. Only shown when quiz has attempts.
        $leaderboard = [];
        if ($quiz->total_attempts > 0) {
            $leaderboard = Cache::remember("leaderboard:{$quiz->id}", 300, function () use ($quiz) {
                return \App\Models\Attempt::query()
                    ->with('user:id,name')
                    ->where('quiz_id', $quiz->id)
                    ->where('status', 'completed')
                    ->orderByDesc('percentage')
                    ->orderBy('time_taken_seconds')
                    ->limit(10)
                    ->get(['id', 'user_id', 'percentage', 'score', 'total_marks', 'time_taken_seconds'])
                    ->map(fn($a) => [
                        'user_name'       => $a->user?->name ?? 'Unknown',
                        'percentage'      => $a->percentage,
                        'score'           => $a->score,
                        'total_marks'     => $a->total_marks,
                        'time_taken_seconds' => $a->time_taken_seconds,
                    ])
                    ->toArray();
            });
        }

        return view('customer.quiz.show', compact('quiz', 'isEnrolled', 'leaderboard'));
    }
}
