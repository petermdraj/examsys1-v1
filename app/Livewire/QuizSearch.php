<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Quiz;
use App\Services\Quiz\QuizAssignmentService;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class QuizSearch extends Component
{
    use WithPagination;

    #[Url(history: true, except: '')]
    public string $search = '';

    #[Url(history: true, except: '')]
    public string $category = '';

    #[Url(history: true, except: 'newest')]
    public string $sort = 'newest';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedCategory(): void
    {
        $this->resetPage();
    }

    public function updatedSort(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->search   = '';
        $this->category = '';
        $this->resetPage();
    }

    public function render()
    {
        $query = Quiz::where('status', 'published')
            ->where('visibility', 'public')
            ->with('category', 'lecturer');

        if (auth()->check() && auth()->user()->role === 'student') {
            app(QuizAssignmentService::class)->scopeAssignedToStudent($query, auth()->user());
        }

        if ($this->search) {
            $query->where(fn ($q) => $q->where('title', 'like', "%{$this->search}%")
                ->orWhere('description', 'like', "%{$this->search}%"));
        }

        $activeCategory = null;
        $ancestorChain  = [];
        $drillChildren  = collect();

        if ($this->category) {
            $activeCategory = Category::where('slug', $this->category)
                ->where('is_active', true)
                ->first();

            if ($activeCategory) {
                $ancestorChain = $activeCategory->ancestorChain();
                $drillChildren = $activeCategory->children()->get();

                $all = Category::where('is_active', true)->get(['id', 'parent_id']);
                $ids = array_merge([$activeCategory->id], Category::descendantIds($activeCategory->id, $all));
                $query->whereIn('category_id', $ids);
            }
        }

        $query->when($this->sort === 'newest', fn ($q) => $q->orderByDesc('created_at'))
            ->when($this->sort === 'popular', fn ($q) => $q->orderByDesc('total_attempts'));

        $quizzes = $query->paginate(12);

        $categories = Category::active()
            ->whereNull('parent_id')
            ->with(['children' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order')
                ->with(['children' => fn ($q2) => $q2->where('is_active', true)->orderBy('sort_order')]),
            ])
            ->get();

        return view('livewire.quiz-search', compact(
            'quizzes', 'categories', 'activeCategory', 'ancestorChain', 'drillChildren'
        ));
    }
}
