<?php

namespace App\Filament\Admin\Pages;

use App\Models\Attempt;
use App\Models\Quiz;
use App\Services\Exam\QuizResultsPublishService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ExamHistoryReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static ?int $navigationSort = 4;

    protected static string $view = 'filament.admin.pages.exam-history-report';

    public $pendingQuizzes = [];

    public static function getNavigationGroup(): ?string
    {
        return __('admin.nav_group_platform');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.nav_exam_history');
    }

    public function getTitle(): string
    {
        return __('admin.nav_exam_history');
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('admin.view_all_reports') ?? false;
    }

    public function mount(): void
    {
        $this->loadPendingQuizzes();
    }

    public function loadPendingQuizzes(): void
    {
        $this->pendingQuizzes = Quiz::query()
            ->where('hold_results_until_published', true)
            ->whereNull('results_published_at')
            ->whereHas('attempts', fn (Builder $q) => $q->whereIn('status', ['completed', 'terminated']))
            ->withCount(['attempts as completed_count' => fn (Builder $q) => $q->whereIn('status', ['completed', 'terminated'])])
            ->orderBy('title')
            ->get();
    }

    public function publishQuizResults(string $quizId): void
    {
        $quiz = Quiz::findOrFail($quizId);
        $count = app(QuizResultsPublishService::class)->publish($quiz);

        Notification::make()
            ->title(__('admin.results_published_success', ['count' => $count, 'quiz' => $quiz->title]))
            ->success()
            ->send();

        $this->loadPendingQuizzes();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Attempt::query()
                    ->whereIn('status', ['completed', 'terminated', 'timed_out'])
                    ->with(['user', 'quiz'])
                    ->latest('submitted_at')
            )
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('admin.exam_hist_col_student'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.email')
                    ->label(__('admin.col_email'))
                    ->toggleable(),
                Tables\Columns\TextColumn::make('quiz.title')
                    ->label(__('admin.exam_hist_col_quiz'))
                    ->searchable()
                    ->limit(40),
                Tables\Columns\TextColumn::make('percentage')
                    ->label(__('admin.exam_hist_col_score'))
                    ->suffix('%')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_passed')
                    ->label(__('admin.exam_hist_col_passed'))
                    ->boolean(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'completed' => 'success',
                        'terminated' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('submitted_at')
                    ->label(__('admin.exam_hist_col_submitted'))
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('quiz_id')
                    ->label(__('admin.exam_hist_filter_quiz'))
                    ->relationship('quiz', 'title'),
                Tables\Filters\TernaryFilter::make('is_passed')
                    ->label(__('admin.exam_hist_filter_passed')),
            ])
            ->defaultSort('submitted_at', 'desc')
            ->paginated([25, 50, 100]);
    }
}
