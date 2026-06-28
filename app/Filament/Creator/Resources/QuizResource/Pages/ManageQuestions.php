<?php

namespace App\Filament\Creator\Resources\QuizResource\Pages;

use App\Exceptions\PlanLimitExceededException;
use App\Filament\Creator\Resources\QuizResource;
use App\Models\Question;
use App\Models\QuestionCollection;
use App\Models\Quiz;
use App\Models\QuestionOption;
use App\Models\FillBlankAnswer;
use App\Services\QuestionBank\QuestionBankService;
use App\Services\Quiz\PlanLimitService;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;

class ManageQuestions extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = QuizResource::class;
    protected static string $view = 'filament.creator.pages.manage-questions';

    // Store only the UUID — avoids Livewire model serialization issues
    public string $quizId = '';

    public function mount(string $record): void
    {
        $quiz = Quiz::findOrFail($record);
        abort_unless($quiz->creator_id === auth()->id(), 403);
        $this->quizId = $quiz->id;
    }

    protected function getQuiz(): Quiz
    {
        return Quiz::findOrFail($this->quizId);
    }

    public function getTitle(): string
    {
        return 'Questions — ' . $this->getQuiz()->title;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Question::where('quiz_id', $this->quizId)->orderBy('sort_order'))
            ->columns([
                Tables\Columns\TextColumn::make('sort_order')->label(__('creator.col_sort_order'))->sortable()->width(50),
                Tables\Columns\TextColumn::make('type')
                    ->formatStateUsing(fn($state) => match($state) {
                        'mcq_single'   => 'MCQ Single',
                        'mcq_multiple' => 'MCQ Multi',
                        'fill_blank'   => 'Fill Blank',
                        'true_false'   => 'True/False',
                        'short_answer' => 'Short Ans.',
                        default        => $state,
                    })
                    ->badge()
                    ->color(fn(string $state) => match($state) {
                        'mcq_single'   => 'primary',
                        'mcq_multiple' => 'success',
                        'fill_blank'   => 'warning',
                        'true_false'   => 'danger',
                        default        => 'gray',
                    }),
                Tables\Columns\TextColumn::make('content')->limit(80)->searchable()->label(__('creator.qbank_field_content_short')),
                Tables\Columns\TextColumn::make('marks')->sortable(),
                Tables\Columns\TextColumn::make('negative_marks')->label(__('creator.col_neg_marks')),
            ])
            ->actions([
                Tables\Actions\Action::make('edit')
                    ->icon('heroicon-o-pencil')
                    ->form(fn(Question $record) => $this->questionForm($record))
                    ->fillForm(fn(Question $record) => array_merge($record->toArray(), [
                        'options'       => $record->options->toArray(),
                        'blank_answers' => $record->fillBlankAnswers->toArray(),
                    ]))
                    ->action(function (array $data, Question $record): void {
                        $record->update([
                            'type'           => $data['type'],
                            'content'        => $data['content'],
                            'explanation'    => $data['explanation'] ?? null,
                            'marks'          => $data['marks'],
                            'negative_marks' => $data['negative_marks'],
                        ]);
                        $record->options()->delete();
                        foreach ($data['options'] ?? [] as $i => $opt) {
                            QuestionOption::create([
                                'question_id' => $record->id,
                                'content'     => $opt['content'],
                                'is_correct'  => $opt['is_correct'] ?? false,
                                'sort_order'  => $i + 1,
                            ]);
                        }
                        $record->fillBlankAnswers()->delete();
                        foreach ($data['blank_answers'] ?? [] as $ans) {
                            FillBlankAnswer::create([
                                'question_id' => $record->id,
                                'answer'      => $ans['answer'],
                            ]);
                        }
                        $quiz = $this->getQuiz();
                        $quiz->update([
                            'total_questions' => $quiz->questions()->count(),
                            'total_marks'     => $quiz->questions()->sum('marks'),
                        ]);
                    }),
                Tables\Actions\Action::make('save_to_bank')
                    ->label(__('creator.action_save_to_bank'))
                    ->icon('heroicon-o-archive-box-arrow-down')
                    ->color('gray')
                    ->form([
                        Forms\Components\Select::make('collection_id')
                            ->label(__('creator.qbank_collection_optional'))
                            ->options(fn() => QuestionCollection::where('creator_id', auth()->id())
                                ->pluck('name', 'id')->toArray())
                            ->nullable(),
                    ])
                    ->action(function (array $data, Question $record): void {
                        $service = new QuestionBankService();
                        $saved   = $service->saveToBank(auth()->id(), $record, $data['collection_id'] ?? null);
                        if ($saved) {
                            Notification::make()->title(__('creator.qbank_notif_saved_to_bank'))->success()->send();
                        } else {
                            Notification::make()->title(__('creator.qbank_notif_already_in_bank'))->warning()->send();
                        }
                    }),
                Tables\Actions\Action::make('delete')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (Question $record): void {
                        $record->options()->delete();
                        $record->fillBlankAnswers()->delete();
                        $record->delete();
                        $quiz = $this->getQuiz();
                        $quiz->update([
                            'total_questions' => $quiz->questions()->count(),
                            'total_marks'     => $quiz->questions()->sum('marks'),
                        ]);
                    }),
            ])
            ->emptyStateHeading(__('creator.manage_q_empty_heading'))
            ->emptyStateDescription(__('creator.manage_q_empty_desc'))
            ->emptyStateIcon('heroicon-o-question-mark-circle');
    }

    public function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label(__('creator.btn_back_to_quiz'))
                ->url(fn() => QuizResource::getUrl('edit', ['record' => $this->quizId]))
                ->color('gray'),
            Actions\Action::make('ai_generate')
                ->label(__('creator.generate_with_ai'))
                ->icon('heroicon-o-sparkles')
                ->color('warning')
                ->url(fn() => route('filament.creator.pages.ai-generator', ['quiz_id' => $this->quizId])),
            Actions\Action::make('add_question')
                ->label(__('creator.add_question'))
                ->icon('heroicon-o-plus')
                ->form($this->questionForm())
                ->action(function (array $data): void {
                    $quiz = $this->getQuiz();

                    // Enforce plan question limits
                    try {
                        app(PlanLimitService::class)->assertCanAddQuestion(auth()->user(), $quiz->id);
                    } catch (PlanLimitExceededException $e) {
                        Notification::make()->title(__('creator.notif_plan_limit_reached'))->body($e->getMessage())->danger()->send();
                        return;
                    }

                    $sortOrder = $quiz->questions()->max('sort_order') ?? 0;
                    $question  = $quiz->questions()->create([
                        'type'           => $data['type'],
                        'content'        => $data['content'],
                        'explanation'    => $data['explanation'] ?? null,
                        'marks'          => $data['marks'],
                        'negative_marks' => $data['negative_marks'],
                        'sort_order'     => $sortOrder + 1,
                    ]);
                    foreach ($data['options'] ?? [] as $i => $opt) {
                        QuestionOption::create([
                            'question_id' => $question->id,
                            'content'     => $opt['content'],
                            'is_correct'  => $opt['is_correct'] ?? false,
                            'sort_order'  => $i + 1,
                        ]);
                    }
                    foreach ($data['blank_answers'] ?? [] as $ans) {
                        FillBlankAnswer::create([
                            'question_id' => $question->id,
                            'answer'      => $ans['answer'],
                        ]);
                    }
                    $quiz->update([
                        'total_questions' => $quiz->questions()->count(),
                        'total_marks'     => $quiz->questions()->sum('marks'),
                    ]);
                }),
        ];
    }

    private function questionForm(?Question $record = null): array
    {
        return [
            Forms\Components\Select::make('type')
                ->options([
                    'mcq_single'   => 'MCQ (Single Answer)',
                    'mcq_multiple' => 'MCQ (Multiple Answers)',
                    'fill_blank'   => 'Fill in the Blank',
                    'true_false'   => 'True / False',
                    'short_answer' => 'Short Answer',
                ])
                ->required()->live(),
            Forms\Components\Textarea::make('content')
                ->label(__('creator.qbank_field_content_short'))->required()->rows(3)->columnSpanFull(),
            Forms\Components\Textarea::make('explanation')
                ->rows(2)->columnSpanFull(),
            Forms\Components\TextInput::make('marks')
                ->numeric()->default(1)->minValue(0),
            Forms\Components\TextInput::make('negative_marks')
                ->numeric()->default(0)->minValue(0),
            Forms\Components\Repeater::make('options')
                ->label(__('creator.qbank_section_options'))
                ->schema([
                    Forms\Components\TextInput::make('content')->required()->columnSpan(4),
                    Forms\Components\Toggle::make('is_correct')->label(__('creator.qbank_option_correct'))->columnSpan(1),
                ])
                ->columns(5)
                ->visible(fn(Forms\Get $get) => in_array($get('type'), ['mcq_single', 'mcq_multiple', 'true_false']))
                ->minItems(2)->columnSpanFull(),
            Forms\Components\Repeater::make('blank_answers')
                ->label(__('creator.qbank_section_answers'))
                ->schema([
                    Forms\Components\TextInput::make('answer')->required(),
                ])
                ->visible(fn(Forms\Get $get) => $get('type') === 'fill_blank')
                ->minItems(1)->columnSpanFull(),
        ];
    }
}
