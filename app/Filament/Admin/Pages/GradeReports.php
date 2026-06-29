<?php

namespace App\Filament\Admin\Pages;

use App\Models\Quiz;
use App\Models\StudentBatch;
use App\Services\Exam\ExamGradeReportService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Str;

class GradeReports extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-table-cells';

    protected static ?int $navigationSort = 5;

    protected static string $view = 'filament.admin.pages.grade-reports';

    public ?array $data = [];

    public static function getNavigationGroup(): ?string
    {
        return __('admin.nav_group_platform');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.nav_grade_reports');
    }

    public function getTitle(): string
    {
        return __('admin.nav_grade_reports');
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('admin.view_all_reports') ?? false;
    }

    public function mount(): void
    {
        $this->form->fill([
            'quiz_id'      => null,
            'batch_id'     => null,
            'attempt_mode' => 'best',
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('quiz_id')
                    ->label(__('admin.grade_field_quiz'))
                    ->options(fn () => Quiz::query()->orderBy('title')->pluck('title', 'id'))
                    ->searchable()
                    ->required()
                    ->live(),
                Select::make('batch_id')
                    ->label(__('admin.grade_field_batch'))
                    ->options(fn () => StudentBatch::query()->orderBy('name')->pluck('name', 'id'))
                    ->placeholder(__('admin.grade_field_batch_all'))
                    ->searchable(),
                Select::make('attempt_mode')
                    ->label(__('admin.grade_field_attempt_mode'))
                    ->options([
                        'best'   => __('admin.grade_attempt_best'),
                        'latest' => __('admin.grade_attempt_latest'),
                        'all'    => __('admin.grade_attempt_all'),
                    ])
                    ->default('best')
                    ->required()
                    ->live(),
            ])
            ->statePath('data');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportExcel')
                ->label(__('admin.grade_export_excel'))
                ->icon('heroicon-o-arrow-down-tray')
                ->action(fn () => $this->exportExcel()),
            Action::make('exportPdf')
                ->label(__('admin.grade_export_pdf'))
                ->icon('heroicon-o-document-arrow-down')
                ->action(fn () => $this->exportPdf()),
        ];
    }

    public function getPreviewRows(): array
    {
        $quiz = $this->resolveQuiz();
        if (! $quiz) {
            return [];
        }

        return app(ExamGradeReportService::class)
            ->buildRows(
                $quiz,
                $this->data['batch_id'] ?? null,
                $this->data['attempt_mode'] ?? 'best',
            )
            ->take(10)
            ->all();
    }

    public function exportExcel()
    {
        $quiz = $this->resolveQuiz();
        if (! $quiz) {
            Notification::make()->title(__('admin.grade_select_quiz'))->warning()->send();

            return;
        }

        $path = app(ExamGradeReportService::class)->exportExcel(
            $quiz,
            $this->data['batch_id'] ?? null,
            $this->data['attempt_mode'] ?? 'best',
        );

        if (! $path) {
            Notification::make()->title(__('admin.grade_no_data'))->warning()->send();

            return;
        }

        $filename = 'grade-report-' . Str::slug($quiz->title) . '-' . now()->format('Y-m-d') . '.xlsx';

        return response()->download(
            $path,
            $filename,
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
        )->deleteFileAfterSend();
    }

    public function exportPdf()
    {
        $quiz = $this->resolveQuiz();
        if (! $quiz) {
            Notification::make()->title(__('admin.grade_select_quiz'))->warning()->send();

            return;
        }

        $rows = app(ExamGradeReportService::class)->buildRows(
            $quiz,
            $this->data['batch_id'] ?? null,
            $this->data['attempt_mode'] ?? 'best',
        );

        if ($rows->isEmpty()) {
            Notification::make()->title(__('admin.grade_no_data'))->warning()->send();

            return;
        }

        return app(ExamGradeReportService::class)->exportPdf(
            $quiz,
            $this->data['batch_id'] ?? null,
            $this->data['attempt_mode'] ?? 'best',
        );
    }

    protected function resolveQuiz(): ?Quiz
    {
        $quizId = $this->data['quiz_id'] ?? null;

        return $quizId ? Quiz::find($quizId) : null;
    }
}
