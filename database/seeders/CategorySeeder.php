<?php
namespace Database\Seeders;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Medicine', 'color' => '#D5443F', 'icon' => 'heroicon-o-heart'],
            ['name' => 'Surgery', 'color' => '#1D6E8C', 'icon' => 'heroicon-o-scissors'],
            ['name' => 'Obstetrics', 'color' => '#7C6CF0', 'icon' => 'heroicon-o-user-group'],
            ['name' => 'Gynecology', 'color' => '#E0A431', 'icon' => 'heroicon-o-heart'],
            ['name' => 'Pediatrics', 'color' => '#2E9E68', 'icon' => 'heroicon-o-face-smile'],
            ['name' => 'Pharmacology', 'color' => '#6C2E63', 'icon' => 'heroicon-o-beaker'],
            ['name' => 'Anatomy', 'color' => '#C2861B', 'icon' => 'heroicon-o-academic-cap'],
            ['name' => 'Physiology', 'color' => '#1F7A6B', 'icon' => 'heroicon-o-bolt'],
            ['name' => 'Pathology', 'color' => '#A33B4E', 'icon' => 'heroicon-o-magnifying-glass'],
            ['name' => 'Microbiology', 'color' => '#374151', 'icon' => 'heroicon-o-bug-ant'],
            ['name' => 'Biochemistry', 'color' => '#5B7AC9', 'icon' => 'heroicon-o-flask'],
            ['name' => 'Community Medicine', 'color' => '#4D2049', 'icon' => 'heroicon-o-globe-alt'],
            ['name' => 'Radiology', 'color' => '#2C3E73', 'icon' => 'heroicon-o-camera'],
            ['name' => 'Psychiatry', 'color' => '#7A5C12', 'icon' => 'heroicon-o-chat-bubble-left-right'],
            ['name' => 'Dermatology', 'color' => '#0891b2', 'icon' => 'heroicon-o-hand-raised'],
            ['name' => 'Emergency Medicine', 'color' => '#dc2626', 'icon' => 'heroicon-o-exclamation-triangle'],
            ['name' => 'Anesthesiology', 'color' => '#6366f1', 'icon' => 'heroicon-o-moon'],
            ['name' => 'Ophthalmology', 'color' => '#16a34a', 'icon' => 'heroicon-o-eye'],
        ];

        $slugs = [];
        foreach ($categories as $i => $cat) {
            $slug = Str::slug($cat['name']);
            $slugs[] = $slug;
            Category::updateOrCreate(
                ['slug' => $slug],
                array_merge($cat, ['sort_order' => $i + 1, 'is_active' => true])
            );
        }

        Category::whereNotIn('slug', $slugs)->update(['is_active' => false]);
    }
}
