<?php

namespace App\Filament\Lecturer\Pages;

use App\Models\Quiz;
use App\Models\QuestionCollection;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class AiGenerator extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-sparkles';
    public static function getNavigationLabel(): string { return __('lecturer.nav_ai_generator'); }
    protected static ?int $navigationSort = 2;
    protected static string $view = 'filament.lecturer.pages.ai-generator';

    public ?string $quiz_id = null;
    public ?Quiz $quiz = null;
    public array $bankCollections = [];

    public function mount(): void
    {
        $this->quiz_id = request('quiz_id');
        if ($this->quiz_id) {
            $this->quiz = Quiz::where('id', $this->quiz_id)
                ->where('lecturer_id', auth()->id())
                ->first();
        }
        $this->bankCollections = QuestionCollection::where('lecturer_id', auth()->id())
            ->orderBy('name')->get(['id', 'name'])->toArray();
    }

    public function getTitle(): string
    {
        return $this->quiz
            ? 'AI Generator — ' . $this->quiz->title
            : 'AI Question Generator';
    }

    public function saveAndRedirect(int $count, string $collectionName, string $redirectTo): void
    {
        $label = $collectionName ? "in \"{$collectionName}\"" : '';
        Notification::make()
            ->title("{$count} " . str('question')->plural($count) . " saved {$label}")
            ->success()
            ->send();

        $this->redirect($redirectTo);
    }
}
