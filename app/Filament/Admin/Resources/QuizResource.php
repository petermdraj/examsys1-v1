<?php

namespace App\Filament\Admin\Resources;

use App\Traits\RestrictInDemoMode;

use App\Models\Category;
use App\Models\Quiz;
use App\Settings\PlatformSettings;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class QuizResource extends Resource
{
    use RestrictInDemoMode;
    protected static ?string $model = Quiz::class;
    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?int $navigationSort = 5;

    public static function getNavigationGroup(): ?string  { return __('admin.nav_group_content'); }
    public static function getNavigationLabel(): string   { return __('admin.nav_quizzes'); }
    public static function getModelLabel(): string        { return __('admin.quiz_model_label'); }
    public static function getPluralModelLabel(): string  { return __('admin.quiz_model_label_plural'); }

    public static function getNavigationBadge(): ?string
    {
        return (string) Quiz::where('status', 'published')->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        $sym = app(PlatformSettings::class)->currency_symbol;

        return $infolist->schema([
            Infolists\Components\Section::make(__('admin.quiz_section_overview'))
                ->columns(3)
                ->schema([
                    Infolists\Components\ImageEntry::make('cover_image')
                        ->label('')
                        ->height(120)
                        ->width(120)
                        ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->title) . '&background=6366f1&color=fff&size=128'),
                    Infolists\Components\Group::make([
                        Infolists\Components\TextEntry::make('title')->label(__('admin.quiz_col_title'))->size('lg')->weight('bold'),
                        Infolists\Components\TextEntry::make('creator.name')->label(__('admin.quiz_col_creator'))
                            ->helperText(fn ($record) => $record->creator?->email),
                        Infolists\Components\TextEntry::make('category.name')->label(__('admin.quiz_col_category'))->badge()->color('gray'),
                    ])->columnSpan(2),
                ]),

            Infolists\Components\Section::make(__('admin.quiz_section_stats'))
                ->columns(4)
                ->schema([
                    Infolists\Components\TextEntry::make('total_questions')->label(__('admin.quiz_info_questions')),
                    Infolists\Components\TextEntry::make('total_attempts')->label(__('admin.quiz_col_attempts')),
                    Infolists\Components\TextEntry::make('average_score')->label(__('admin.quiz_info_avg_score'))->suffix('%'),
                    Infolists\Components\TextEntry::make('price')
                        ->label(__('admin.quiz_col_price'))
                        ->formatStateUsing(fn ($state) => $state > 0 ? $sym . number_format((float) $state, 2) : __('admin.quiz_free_label')),
                    Infolists\Components\TextEntry::make('status')->label(__('admin.quiz_col_status'))->badge()
                        ->formatStateUsing(fn (string $state) => __('admin.quiz_status_' . $state))
                        ->color(fn ($state) => match ($state) {
                            'published' => 'success', 'draft' => 'warning',
                            'archived'  => 'danger',  default => 'gray',
                        }),
                    Infolists\Components\TextEntry::make('visibility')->label(__('admin.quiz_col_visibility'))->badge()->color('gray')
                        ->formatStateUsing(fn (string $state) => __('admin.quiz_visibility_' . $state)),
                    Infolists\Components\TextEntry::make('pass_percentage')->label(__('admin.quiz_info_pass_pct'))->suffix('%'),
                    Infolists\Components\TextEntry::make('duration_minutes')->label(__('admin.quiz_info_duration'))->suffix(' min')->default(__('admin.quiz_unlimited_label')),
                ]),

            Infolists\Components\Section::make(__('admin.quiz_section_description'))
                ->schema([
                    Infolists\Components\TextEntry::make('description')->hiddenLabel()->prose()->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        $sym = app(PlatformSettings::class)->currency_symbol;

        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('cover_image')
                    ->label('')
                    ->circular()
                    ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->title) . '&background=6366f1&color=fff&size=64'),

                Tables\Columns\TextColumn::make('title')
                    ->label(__('admin.quiz_col_title'))
                    ->searchable()
                    ->sortable()
                    ->weight('semibold')
                    ->limit(45)
                    ->description(fn ($record) => $record->meta_description ? \Str::limit($record->meta_description, 60) : null),

                Tables\Columns\TextColumn::make('creator.name')
                    ->label(__('admin.quiz_col_creator'))
                    ->searchable()
                    ->description(fn ($record) => $record->creator?->email),

                Tables\Columns\TextColumn::make('category.name')
                    ->label(__('admin.quiz_col_category'))
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('total_questions')
                    ->label(__('admin.quiz_col_questions'))
                    ->alignCenter()
                    ->sortable(),

                Tables\Columns\TextColumn::make('price')
                    ->label(__('admin.quiz_col_price'))
                    ->formatStateUsing(fn ($state) => $state > 0 ? $sym . number_format((float) $state, 2) : __('admin.quiz_free_label'))
                    ->sortable()
                    ->color(fn ($state) => $state > 0 ? 'warning' : 'success'),

                Tables\Columns\TextColumn::make('total_attempts')
                    ->label(__('admin.quiz_col_attempts'))
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('status')
                    ->label(__('admin.quiz_col_status'))
                    ->badge()
                    ->formatStateUsing(fn (string $state) => __('admin.quiz_status_' . $state))
                    ->color(fn(string $state) => match($state) {
                        'draft'     => 'warning',
                        'published' => 'success',
                        'archived'  => 'danger',
                        'scheduled' => 'primary',
                        default     => 'gray',
                    }),

                Tables\Columns\TextColumn::make('visibility')
                    ->label(__('admin.quiz_col_visibility'))
                    ->badge()
                    ->formatStateUsing(fn (string $state) => __('admin.quiz_visibility_' . $state))
                    ->color(fn ($state) => match ($state) {
                        'public'   => 'success',
                        'private'  => 'danger',
                        'unlisted' => 'warning',
                        default    => 'gray',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('admin.quiz_col_created'))
                    ->date('d M Y')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('admin.quiz_col_status'))
                    ->options([
                        'draft'     => __('admin.quiz_status_draft'),
                        'published' => __('admin.quiz_status_published'),
                        'archived'  => __('admin.quiz_status_archived'),
                        'scheduled' => __('admin.quiz_status_scheduled'),
                    ]),
                Tables\Filters\SelectFilter::make('visibility')
                    ->label(__('admin.quiz_col_visibility'))
                    ->options([
                        'public'   => __('admin.quiz_visibility_public'),
                        'private'  => __('admin.quiz_visibility_private'),
                        'unlisted' => __('admin.quiz_visibility_unlisted'),
                    ]),
                Tables\Filters\SelectFilter::make('category')
                    ->label(__('admin.quiz_col_category'))
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\Filter::make('paid_only')
                    ->label(__('admin.quiz_filter_paid_only'))
                    ->query(fn (Builder $q) => $q->where('price', '>', 0)),
                Tables\Filters\Filter::make('free_only')
                    ->label(__('admin.quiz_filter_free_only'))
                    ->query(fn (Builder $q) => $q->where('price', '=', 0)),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('publish')
                    ->label(__('admin.quiz_action_publish'))
                    ->icon('heroicon-o-rocket-launch')
                    ->color('success')
                    ->visible(fn (Quiz $r) => $r->status === 'draft')
                    ->requiresConfirmation()
                    ->action(fn (Quiz $r) => $r->update(['status' => 'published']))
                    ->after(fn () => Notification::make()->title(__('admin.quiz_notif_published'))->success()->send()),
                Tables\Actions\Action::make('archive')
                    ->label(__('admin.quiz_action_archive'))
                    ->icon('heroicon-o-archive-box')
                    ->color('danger')
                    ->visible(fn (Quiz $r) => $r->status === 'published')
                    ->requiresConfirmation()
                    ->modalDescription(__('admin.quiz_modal_archive_desc'))
                    ->action(fn (Quiz $r) => $r->update(['status' => 'archived']))
                    ->after(fn () => Notification::make()->title(__('admin.quiz_notif_archived'))->warning()->send()),
                Tables\Actions\Action::make('view_frontend')
                    ->label(__('admin.quiz_action_view_live'))
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->color('gray')
                    ->url(fn (Quiz $r) => route('quizzes.show', $r->slug))
                    ->openUrlInNewTab()
                    ->visible(fn (Quiz $r) => $r->status === 'published'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('bulk_archive')
                        ->label(__('admin.quiz_bulk_archive'))
                        ->icon('heroicon-o-archive-box')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->update(['status' => 'archived']))
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped();
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Admin\Resources\QuizResource\Pages\ListQuizzes::route('/'),
            'view'  => \App\Filament\Admin\Resources\QuizResource\Pages\ViewQuiz::route('/{record}'),
        ];
    }
}
