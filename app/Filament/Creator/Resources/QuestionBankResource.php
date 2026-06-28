<?php

namespace App\Filament\Creator\Resources;

use App\Filament\Creator\Resources\QuestionBankResource\Pages;
use App\Models\Question;
use App\Models\QuestionCollection;
use App\Models\QuestionOption;
use App\Models\FillBlankAnswer;
use App\Services\QuestionBank\QuestionBankService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Response;

class QuestionBankResource extends Resource
{
    protected static ?string $model = Question::class;
    protected static ?string $navigationIcon   = 'heroicon-o-rectangle-stack';
    public static function getNavigationLabel(): string   { return __('creator.nav_question_bank'); }
    public static function getModelLabel(): string        { return __('creator.qbank_model_label'); }
    public static function getPluralModelLabel(): string  { return __('creator.nav_question_bank'); }
    protected static ?int    $navigationSort   = 3;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereNull('quiz_id')
            ->where('creator_id', auth()->id());
    }

    public static function canCreate(): bool
    {
        try {
            app(\App\Services\Quiz\PlanLimitService::class)->assertCanAddBankQuestion(auth()->user());
            return true;
        } catch (\App\Exceptions\PlanLimitExceededException) {
            return false;
        }
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make(__('creator.qbank_section_question'))->schema([
                Forms\Components\Select::make('type')
                    ->label(__('creator.qbank_field_type'))
                    ->options([
                        'mcq_single'   => __('creator.qbank_type_mcq_single'),
                        'mcq_multiple' => __('creator.qbank_type_mcq_multiple'),
                        'fill_blank'   => __('creator.qbank_type_fill_blank'),
                        'true_false'   => __('creator.qbank_type_true_false'),
                        'short_answer' => __('creator.qbank_type_short_answer'),
                    ])
                    ->required()->live()->columnSpan(1),
                Forms\Components\Select::make('difficulty')
                    ->label(__('creator.qbank_field_difficulty'))
                    ->options([
                        'easy'   => __('creator.qbank_diff_easy'),
                        'medium' => __('creator.qbank_diff_medium'),
                        'hard'   => __('creator.qbank_diff_hard'),
                    ])
                    ->nullable()->columnSpan(1),
                Forms\Components\Select::make('collection_id')
                    ->label(__('creator.qbank_field_collection'))
                    ->relationship('collection', 'name',
                        fn(\Illuminate\Database\Eloquent\Builder $query) => $query->where('creator_id', auth()->id()))
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')->label(__('creator.qbank_collection_name'))->required()->maxLength(255),
                        Forms\Components\TextInput::make('color')->label(__('creator.qbank_collection_color'))->maxLength(7),
                    ])
                    ->createOptionUsing(fn(array $data) => QuestionCollection::create([
                        'creator_id' => auth()->id(),
                        'name'       => $data['name'],
                        'color'      => $data['color'] ?? null,
                    ])->id)
                    ->nullable()->columnSpan(2),
                Forms\Components\Textarea::make('content')
                    ->label(__('creator.qbank_field_content'))->required()->rows(3)->columnSpan(2),
                Forms\Components\Textarea::make('explanation')
                    ->label(__('creator.qbank_field_explanation'))->rows(2)->columnSpan(2),
                Forms\Components\TextInput::make('marks')
                    ->label(__('creator.qbank_field_marks'))
                    ->numeric()->default(1)->minValue(0)->columnSpan(1),
                Forms\Components\TextInput::make('negative_marks')
                    ->label(__('creator.qbank_field_negative_marks'))
                    ->numeric()->default(0)->minValue(0)->columnSpan(1),
                Forms\Components\TextInput::make('hint')
                    ->label(__('creator.qbank_field_hint'))
                    ->columnSpan(2),
            ])->columns(2),

            Forms\Components\Section::make(__('creator.qbank_section_options'))
                ->schema([
                    Forms\Components\Repeater::make('options')
                        ->hiddenLabel()
                        ->relationship('options')
                        ->schema([
                            Forms\Components\TextInput::make('content')->label(__('creator.qbank_option_content'))->required()->columnSpan(4),
                            Forms\Components\Toggle::make('is_correct')->label(__('creator.qbank_option_correct'))->columnSpan(1),
                            Forms\Components\Hidden::make('sort_order'),
                        ])
                        ->columns(5)->orderColumn('sort_order')->minItems(2)
                        ->addActionLabel(__('creator.qbank_add_option')),
                ])
                ->visible(fn(Forms\Get $get) => in_array($get('type'), ['mcq_single', 'mcq_multiple', 'true_false'])),

            Forms\Components\Section::make(__('creator.qbank_section_answers'))
                ->schema([
                    Forms\Components\Repeater::make('fillBlankAnswers')
                        ->relationship('fillBlankAnswers')
                        ->schema([Forms\Components\TextInput::make('answer')->label(__('creator.qbank_field_answer'))->required()])
                        ->minItems(1)->addActionLabel(__('creator.qbank_add_answer')),
                ])
                ->visible(fn(Forms\Get $get) => $get('type') === 'fill_blank'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('type')
                    ->label(__('creator.qbank_field_type'))
                    ->badge()
                    ->formatStateUsing(fn(string $state) => __('creator.qbank_type_' . $state))
                    ->color(fn(string $state) => match($state) {
                        'mcq_single'   => 'primary',
                        'mcq_multiple' => 'success',
                        'fill_blank'   => 'warning',
                        'true_false'   => 'danger',
                        default        => 'gray',
                    })
                    ->width(110),
                Tables\Columns\TextColumn::make('difficulty')
                    ->label(__('creator.qbank_field_difficulty'))
                    ->badge()
                    ->formatStateUsing(fn(?string $state) => $state ? __('creator.qbank_diff_' . $state) : '—')
                    ->color(fn(?string $state) => match($state) {
                        'easy'   => 'success',
                        'medium' => 'warning',
                        'hard'   => 'danger',
                        default  => 'gray',
                    })
                    ->width(90),
                Tables\Columns\TextColumn::make('collection.name')
                    ->label(__('creator.qbank_field_collection'))->default('—')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('content')
                    ->label(__('creator.qbank_field_content_short'))->limit(80)->searchable(),
                Tables\Columns\TextColumn::make('marks')->label(__('creator.qbank_field_marks'))->sortable()->width(70),
                Tables\Columns\TextColumn::make('created_at')->label(__('creator.col_created_at'))->date()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label(__('creator.qbank_field_type'))
                    ->options([
                        'mcq_single'   => __('creator.qbank_type_mcq_single'),
                        'mcq_multiple' => __('creator.qbank_type_mcq_multiple'),
                        'fill_blank'   => __('creator.qbank_type_fill_blank'),
                        'true_false'   => __('creator.qbank_type_true_false'),
                        'short_answer' => __('creator.qbank_type_short_answer'),
                    ]),
                Tables\Filters\SelectFilter::make('difficulty')
                    ->label(__('creator.qbank_field_difficulty'))
                    ->options([
                        'easy'   => __('creator.qbank_diff_easy'),
                        'medium' => __('creator.qbank_diff_medium'),
                        'hard'   => __('creator.qbank_diff_hard'),
                    ]),
                Tables\Filters\SelectFilter::make('collection_id')
                    ->label(__('creator.qbank_field_collection'))
                    ->options(fn() => QuestionCollection::where('creator_id', auth()->id())
                        ->pluck('name', 'id')->toArray()),
            ])
            ->headerActions([
                Tables\Actions\Action::make('export_json')
                    ->label(__('creator.qbank_action_export_json'))
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function () {
                        $service = new QuestionBankService();
                        $data    = $service->exportJson(auth()->id());

                        if (empty($data['questions'])) {
                            Notification::make()->title(__('creator.qbank_notif_nothing_to_export'))->warning()->send();
                            return;
                        }

                        return Response::streamDownload(function () use ($data) {
                            echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                        }, 'question-bank-' . date('Y-m-d') . '.json', ['Content-Type' => 'application/json']);
                    }),

                Tables\Actions\Action::make('import_json')
                    ->label(__('creator.qbank_action_import_json'))
                    ->icon('heroicon-o-arrow-up-tray')
                    ->form([
                        Forms\Components\FileUpload::make('file')
                            ->label(__('creator.qbank_import_file_label'))
                            ->acceptedFileTypes(['application/json', 'text/plain'])
                            ->maxSize(2048)
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        $path = storage_path('app/public/' . $data['file']);

                        if (!file_exists($path)) {
                            Notification::make()->title(__('creator.qbank_notif_file_not_found'))->danger()->send();
                            return;
                        }

                        $json = json_decode(file_get_contents($path), true);
                        @unlink($path);

                        if (!is_array($json) || empty($json['questions'])) {
                            Notification::make()->title(__('creator.qbank_notif_no_questions'))->warning()->send();
                            return;
                        }

                        $service = new QuestionBankService();
                        $result  = $service->importJson(auth()->id(), $json);

                        $total = count($json['questions']);
                        $msg = __('creator.qbank_notif_imported', ['imported' => $result['imported'], 'total' => $total]);
                        if ($result['skipped'] > 0) $msg .= ' ' . __('creator.qbank_notif_skipped', ['n' => $result['skipped']]);
                        if ($result['errors'] > 0)  $msg .= ' ' . __('creator.qbank_notif_errors', ['n' => $result['errors']]);

                        Notification::make()->title($msg)->success()->send();
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('duplicate')
                    ->label(__('creator.qbank_action_duplicate'))
                    ->icon('heroicon-o-document-duplicate')
                    ->action(function (Question $record) {
                        $copy = Question::create([
                            'quiz_id'        => null,
                            'creator_id'     => auth()->id(),
                            'type'           => $record->type,
                            'content'        => $record->content,
                            'explanation'    => $record->explanation,
                            'marks'          => $record->marks,
                            'negative_marks' => $record->negative_marks,
                            'hint'           => $record->hint,
                            'difficulty'     => $record->difficulty,
                            'collection_id'  => $record->collection_id,
                            'sort_order'     => 0,
                        ]);

                        foreach ($record->options()->orderBy('sort_order')->get() as $i => $opt) {
                            QuestionOption::create([
                                'question_id' => $copy->id,
                                'content'     => $opt->content,
                                'is_correct'  => $opt->is_correct,
                                'sort_order'  => $i + 1,
                            ]);
                        }

                        foreach ($record->fillBlankAnswers as $ans) {
                            FillBlankAnswer::create(['question_id' => $copy->id, 'answer' => $ans->answer]);
                        }

                        Notification::make()->title(__('creator.notification_question_duplicated'))->success()->send();
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(function (Question $record) {
                        $record->options()->delete();
                        $record->fillBlankAnswers()->delete();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function ($records) {
                            foreach ($records as $r) {
                                $r->options()->delete();
                                $r->fillBlankAnswers()->delete();
                            }
                        }),
                ]),
            ])
            ->emptyStateHeading(__('creator.qbank_empty_heading'))
            ->emptyStateDescription(__('creator.qbank_empty_desc'))
            ->emptyStateIcon('heroicon-o-rectangle-stack');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListBankQuestions::route('/'),
            'create' => Pages\CreateBankQuestion::route('/create'),
            'edit'   => Pages\EditBankQuestion::route('/{record}/edit'),
        ];
    }
}
