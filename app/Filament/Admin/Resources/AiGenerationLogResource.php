<?php

namespace App\Filament\Admin\Resources;

use App\Traits\RestrictInDemoMode;

use App\Models\AiGenerationLog;
use App\Settings\PlatformSettings;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AiGenerationLogResource extends Resource
{
    use RestrictInDemoMode;
    protected static ?string $model = AiGenerationLog::class;
    protected static ?string $navigationIcon = 'heroicon-o-cpu-chip';
    protected static ?int $navigationSort = 12;

    public static function getNavigationGroup(): ?string  { return __('admin.nav_group_configuration'); }
    public static function getNavigationLabel(): string   { return __('admin.nav_ai_logs'); }
    public static function getModelLabel(): string        { return __('admin.ai_log_model_label'); }
    public static function getPluralModelLabel(): string  { return __('admin.ai_log_model_label_plural'); }

    public static function getNavigationBadge(): ?string
    {
        $failed = AiGenerationLog::where('status', 'failed')
            ->whereDate('created_at', today())
            ->count();
        return $failed > 0 ? $failed . ' failed' : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        $sym = app(PlatformSettings::class)->currency_symbol;

        return $infolist->schema([
            Infolists\Components\Section::make(__('admin.ai_log_section_details'))
                ->columns(3)
                ->schema([
                    Infolists\Components\TextEntry::make('user.name')
                        ->label(__('admin.ai_log_col_creator'))
                        ->helperText(fn ($record) => $record->user?->email),
                    Infolists\Components\TextEntry::make('quiz.title')
                        ->label(__('admin.ai_log_col_quiz'))
                        ->default('—'),
                    Infolists\Components\TextEntry::make('status')
                        ->label(__('admin.ai_log_col_status'))
                        ->badge()
                        ->formatStateUsing(fn (string $state) => __('admin.ai_log_status_' . $state))
                        ->color(fn ($state) => match ($state) {
                            'success' => 'success',
                            'failed'  => 'danger',
                            'partial' => 'warning',
                            default   => 'gray',
                        }),
                    Infolists\Components\TextEntry::make('model')
                        ->label(__('admin.ai_log_col_model'))
                        ->badge()
                        ->color('primary'),
                    Infolists\Components\TextEntry::make('questions_generated')->label(__('admin.ai_log_col_questions')),
                    Infolists\Components\TextEntry::make('tokens_used')->label(__('admin.ai_log_col_tokens_used')),
                    Infolists\Components\TextEntry::make('charge_applied')
                        ->label(__('admin.ai_log_col_charge'))
                        ->formatStateUsing(fn ($state) => $state > 0 ? $sym . number_format((float) $state, 4) : __('admin.ai_log_free_label'))
                        ->color(fn ($state) => $state > 0 ? 'danger' : 'success'),
                    Infolists\Components\IconEntry::make('was_free')->boolean()->label(__('admin.ai_log_col_free_quota')),
                    Infolists\Components\TextEntry::make('created_at')->label(__('admin.ai_log_col_date'))->dateTime('d M Y, h:i A'),
                ]),

            Infolists\Components\Section::make(__('admin.ai_log_section_prompt'))
                ->schema([
                    Infolists\Components\TextEntry::make('prompt')
                        ->hiddenLabel()
                        ->columnSpanFull()
                        ->prose(),
                ]),

            Infolists\Components\Section::make(__('admin.ai_log_section_options'))
                ->columns(3)
                ->schema([
                    Infolists\Components\TextEntry::make('options.count')->label(__('admin.ai_log_opt_count'))->default('—'),
                    Infolists\Components\TextEntry::make('options.type')->label(__('admin.ai_log_opt_type'))->default('—'),
                    Infolists\Components\TextEntry::make('options.difficulty')->label(__('admin.ai_log_opt_difficulty'))->default('—'),
                    Infolists\Components\TextEntry::make('options.language')->label(__('admin.ai_log_opt_language'))->default('—'),
                ]),

            Infolists\Components\Section::make(__('admin.ai_log_section_error'))
                ->visible(fn ($record) => filled($record->error_message))
                ->schema([
                    Infolists\Components\TextEntry::make('error_message')
                        ->label('')
                        ->color('danger')
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        $sym = app(PlatformSettings::class)->currency_symbol;

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('admin.ai_log_col_creator'))
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record) => $record->user?->email),

                Tables\Columns\TextColumn::make('prompt')
                    ->label(__('admin.ai_log_col_prompt'))
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->prompt)
                    ->searchable(),

                Tables\Columns\TextColumn::make('quiz.title')
                    ->label(__('admin.ai_log_col_quiz'))
                    ->limit(30)
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('questions_generated')
                    ->label(__('admin.ai_log_col_questions_short'))
                    ->alignCenter()
                    ->sortable(),

                Tables\Columns\TextColumn::make('tokens_used')
                    ->label(__('admin.ai_log_col_tokens'))
                    ->alignCenter()
                    ->sortable(),

                Tables\Columns\TextColumn::make('charge_applied')
                    ->label(__('admin.ai_log_col_cost'))
                    ->formatStateUsing(fn ($state) => $state > 0 ? $sym . number_format((float) $state, 4) : __('admin.ai_log_free_label'))
                    ->color(fn ($state) => $state > 0 ? 'danger' : 'success')
                    ->sortable(),

                Tables\Columns\IconColumn::make('was_free')
                    ->label(__('admin.ai_log_col_free'))
                    ->boolean()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('model')
                    ->label(__('admin.ai_log_col_model'))
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('status')
                    ->label(__('admin.ai_log_col_status'))
                    ->badge()
                    ->formatStateUsing(fn (string $state) => __('admin.ai_log_status_' . $state))
                    ->color(fn(string $state) => match($state) {
                        'success' => 'success',
                        'failed'  => 'danger',
                        'partial' => 'warning',
                        default   => 'gray',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('admin.ai_log_col_when'))
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'success' => __('admin.ai_log_status_success'),
                        'failed'  => __('admin.ai_log_status_failed'),
                        'partial' => __('admin.ai_log_status_partial'),
                    ]),
                Tables\Filters\TernaryFilter::make('was_free')
                    ->label(__('admin.ai_log_filter_credit_type'))
                    ->trueLabel(__('admin.ai_log_filter_free_only'))
                    ->falseLabel(__('admin.ai_log_filter_paid_only')),
                Tables\Filters\Filter::make('today')
                    ->label(__('admin.ai_log_filter_today'))
                    ->query(fn (Builder $q) => $q->whereDate('created_at', today())),
                Tables\Filters\Filter::make('failed_only')
                    ->label(__('admin.ai_log_filter_failures'))
                    ->query(fn (Builder $q) => $q->where('status', 'failed'))
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->poll('30s');
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Admin\Resources\AiGenerationLogResource\Pages\ListAiGenerationLogs::route('/'),
            'view'  => \App\Filament\Admin\Resources\AiGenerationLogResource\Pages\ViewAiGenerationLog::route('/{record}'),
        ];
    }
}
