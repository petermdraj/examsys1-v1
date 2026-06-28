<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Quiz;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class QuizSearch extends Component
{
    use WithPagination;

    #[Url(history: true, except: '')]
    public string $search = '';

    /** Slug of selected category — any depth. Children shown as drill-down pills. */
    #[Url(history: true, except: '')]
    public string $category = '';

    #[Url(history: true, except: '')]
    public string $price = '';

    #[Url(history: true, except: 'newest')]
    public string $sort = 'newest';

    public function updatedSearch(): void   { $this->resetPage(); }
    public function updatedCategory(): void { $this->resetPage(); }
    public function updatedPrice(): void    { $this->resetPage(); }
    public function updatedSort(): void     { $this->resetPage(); }

    public function clearFilters(): void
    {
        $this->search   = '';
        $this->category = '';
        $this->price    = '';
        $this->resetPage();
    }

    public function render()
    {
        $query = Quiz::where('status', 'published')
            ->where('visibility', 'public')
            ->with('category', 'creator');

        if ($this->search) {
            $query->where(fn($q) =>
                $q->where('title', 'like', "%{$this->search}%")
                  ->orWhere('description', 'like', "%{$this->search}%")
            );
        }

        // Resolved active category node + breadcrumb + drill-down children
        $activeCategory  = null;
        $ancestorChain   = [];   // root → active (for breadcrumb)
        $drillChildren   = collect(); // immediate children of active node

        if ($this->category) {
            $activeCategory = Category::where('slug', $this->category)
                ->where('is_active', true)
                ->first();

            if ($activeCategory) {
                // Ancestor breadcrumb (root → this node)
                $ancestorChain = $activeCategory->ancestorChain();

                // Immediate children for next-level drill-down
                $drillChildren = $activeCategory->children()->get();

                // Filter: selected category + ALL descendants at any depth
                $all  = Category::where('is_active', true)->get(['id', 'parent_id']);
                $ids  = array_merge([$activeCategory->id], Category::descendantIds($activeCategory->id, $all));
                $query->whereIn('category_id', $ids);
            }
        }

        if ($this->price === 'free') {
            $query->where('price', 0);
        } elseif ($this->price === 'paid') {
            $query->where('price', '>', 0);
        }

        $query->when($this->sort === 'newest',     fn($q) => $q->orderByDesc('created_at'))
              ->when($this->sort === 'popular',    fn($q) => $q->orderByDesc('total_attempts'))
              ->when($this->sort === 'price_asc',  fn($q) => $q->orderBy('price'))
              ->when($this->sort === 'price_desc', fn($q) => $q->orderByDesc('price'));

        $quizzes = $query->paginate(12);

        // Grouped dropdown: top-level parents + children for sidebar
        // Eager-load 3 levels deep for the sidebar dropdown
        $categories = Category::active()
            ->whereNull('parent_id')
            ->with(['children' => fn($q) => $q->where('is_active', true)->orderBy('sort_order')
                ->with(['children' => fn($q2) => $q2->where('is_active', true)->orderBy('sort_order')])
            ])
            ->get();

        return view('livewire.quiz-search', compact(
            'quizzes', 'categories', 'activeCategory', 'ancestorChain', 'drillChildren'
        ));
    }
}
