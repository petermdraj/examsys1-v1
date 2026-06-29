<?php
namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Quiz;
use App\Services\Quiz\QuizAssignmentService;
use Illuminate\Support\Facades\Cache;

class QuizController extends Controller
{
    public function __construct(private QuizAssignmentService $assignmentService) {}

    public function categories()
    {
        $categories = Category::where('is_active', true)
            ->whereNull('parent_id')
            ->with(['children' => fn($q) => $q->where('is_active', true)->orderBy('sort_order')])
            ->orderBy('sort_order')
            ->get();

        $allCats = Category::where('is_active', true)->get(['id', 'parent_id']);
        foreach ($categories as $cat) {
            $ids = array_merge([$cat->id], Category::descendantIds($cat->id, $allCats));
            $query = Quiz::whereIn('category_id', $ids)->where('status', 'published')->where('visibility', 'public');
            if (auth()->check() && auth()->user()->role === 'student') {
                $this->assignmentService->scopeAssignedToStudent($query, auth()->user());
            }
            $cat->total_quizzes_count = $query->count();
        }

        return view('student.categories', compact('categories'));
    }

    public function index()
    {
        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();
        $query = Quiz::with(['creator', 'category'])
            ->published()->public()
            ->when(request('q'), fn($query, $q) => $query->where('title', 'like', "%$q%"))
            ->when(request('category'), fn($query, $cat) => $query->whereHas('category', fn($q) => $q->where('slug', $cat)));

        if (auth()->check() && auth()->user()->role === 'student') {
            $this->assignmentService->scopeAssignedToStudent($query, auth()->user());
        }

        $quizzes = $query->paginate(9)->withQueryString();

        return view('student.quiz.index', compact('quizzes', 'categories'));
    }

    public function show(string $slug)
    {
        $quiz = Quiz::with(['creator', 'category', 'questions.options'])->where('slug', $slug)->published()->firstOrFail();
        $user = auth()->user();
        $isAssigned = $user && $user->role === 'student'
            ? $this->assignmentService->isAssignedToStudent($quiz, $user)
            : false;
        $isEnrolled = $user && $quiz->enrollments()->where('user_id', $user->id)->exists();

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

        return view('student.quiz.show', compact('quiz', 'isEnrolled', 'isAssigned', 'leaderboard'));
    }
}
