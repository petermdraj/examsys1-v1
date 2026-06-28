<?php
namespace App\Models;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory, HasUuid;
    protected $fillable = ['name','slug','description','icon','color','parent_id','sort_order','is_active'];
    protected function casts(): array { return ['is_active' => 'boolean']; }

    public function scopeActive($q) { return $q->where('is_active', true)->orderBy('sort_order'); }
    public function parent()   { return $this->belongsTo(Category::class, 'parent_id'); }
    public function children() { return $this->hasMany(Category::class, 'parent_id')->where('is_active', true)->orderBy('sort_order'); }
    public function quizzes()  { return $this->hasMany(Quiz::class); }

    /**
     * All descendant IDs at any depth (recursive, uses cached flat list to avoid N+1).
     * Pass the full flat collection to avoid repeated DB queries.
     */
    public static function descendantIds(string|int $id, ?\Illuminate\Support\Collection $all = null): array
    {
        $all      ??= static::where('is_active', true)->get(['id', 'parent_id']);
        $children   = $all->where('parent_id', $id)->pluck('id')->toArray();
        $result     = $children;
        foreach ($children as $childId) {
            $result = array_merge($result, static::descendantIds($childId, $all));
        }
        return $result;
    }

    /**
     * Ancestor chain from root → this node (inclusive), ordered root-first.
     */
    public function ancestorChain(): array
    {
        $chain = [$this];
        $node  = $this;
        while ($node->parent_id) {
            $node    = $node->parent()->with('parent')->first();
            $chain[] = $node;
        }
        return array_reverse($chain);
    }

    /**
     * Recursive quiz count (own + all descendants).
     */
    public function totalPublishedQuizzesCount(): int
    {
        $all  = static::where('is_active', true)->get(['id', 'parent_id']);
        $ids  = array_merge([$this->id], static::descendantIds($this->id, $all));
        return Quiz::whereIn('category_id', $ids)
            ->where('status', 'published')
            ->where('visibility', 'public')
            ->count();
    }
}
