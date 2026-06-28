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
            ['name' => 'Technology', 'color' => '#6C2E63', 'icon' => 'heroicon-o-cpu-chip'],
            ['name' => 'Science', 'color' => '#1F7A6B', 'icon' => 'heroicon-o-beaker'],
            ['name' => 'Mathematics', 'color' => '#C2861B', 'icon' => 'heroicon-o-calculator'],
            ['name' => 'History', 'color' => '#2C3E73', 'icon' => 'heroicon-o-book-open'],
            ['name' => 'Geography', 'color' => '#A33B4E', 'icon' => 'heroicon-o-globe-alt'],
            ['name' => 'Language & Literature', 'color' => '#374151', 'icon' => 'heroicon-o-language'],
            ['name' => 'General Knowledge', 'color' => '#4D2049', 'icon' => 'heroicon-o-light-bulb'],
            ['name' => 'Business & Finance', 'color' => '#1D6E8C', 'icon' => 'heroicon-o-chart-bar'],
            ['name' => 'Aptitude & Reasoning', 'color' => '#7A5C12', 'icon' => 'heroicon-o-puzzle-piece'],
            ['name' => 'Programming', 'color' => '#2E9E68', 'icon' => 'heroicon-o-code-bracket'],
            ['name' => 'Medical & Health', 'color' => '#D5443F', 'icon' => 'heroicon-o-heart'],
            ['name' => 'Law & Governance', 'color' => '#5B7AC9', 'icon' => 'heroicon-o-scale'],
            ['name' => 'Environment', 'color' => '#2E9E68', 'icon' => 'heroicon-o-globe-europe-africa'],
            ['name' => 'Arts & Culture', 'color' => '#E0A431', 'icon' => 'heroicon-o-paint-brush'],
            ['name' => 'Sports', 'color' => '#D5443F', 'icon' => 'heroicon-o-trophy'],
            ['name' => 'Current Affairs', 'color' => '#6C2E63', 'icon' => 'heroicon-o-newspaper'],
            ['name' => 'Economics', 'color' => '#1F7A6B', 'icon' => 'heroicon-o-arrow-trending-up'],
            ['name' => 'Psychology', 'color' => '#7C6CF0', 'icon' => 'heroicon-o-academic-cap'],
            ['name' => 'Engineering', 'color' => '#374151', 'icon' => 'heroicon-o-wrench-screwdriver'],
            ['name' => 'Competitive Exams', 'color' => '#C2861B', 'icon' => 'heroicon-o-clipboard-document-check'],
        ];

        foreach ($categories as $i => $cat) {
            Category::firstOrCreate(
                ['slug' => Str::slug($cat['name'])],
                array_merge($cat, ['sort_order' => $i + 1, 'is_active' => true])
            );
        }
    }
}
