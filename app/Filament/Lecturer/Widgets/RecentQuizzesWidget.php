<?php

namespace App\Filament\Lecturer\Widgets;

use App\Models\Quiz;
use App\Settings\PlatformSettings;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentQuizzesWidget extends BaseWidget
{
    protected static ?int $sort = 3;
    protected static ?string $heading = null;
    public function getHeading(): string { return __('lecturer.recent_quizzes_heading'); }
    protected int | string | array $columnSpan = 1;

    public function table(Table $table): Table
    {
        $sym = app(PlatformSettings::class)->currency_symbol;

        return $table
            ->query(
                Quiz::where('lecturer_id', auth()->id())
                    ->latest()
                    ->limit(6)
            )
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label(__('lecturer.col_title'))
                    ->limit(30)
                    ->weight('semibold')
                    ->description(fn ($record) => $record->category?->name),

                Tables\Columns\TextColumn::make('total_questions')
                    ->label(__('lecturer.col_qs'))
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('total_attempts')
                    ->label(__('lecturer.col_attempts'))
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('price')
                    ->formatStateUsing(fn ($state) => $state > 0 ? $sym . number_format((float) $state, 2) : '')
                    ->color(fn ($state) => $state > 0 ? 'warning' : 'gray'),

                Tables\Columns\TextColumn::make('status')
                    ->label(__('lecturer.col_status'))
                    ->badge()
                    ->formatStateUsing(fn (string $state) => __('lecturer.status_' . $state))
                    ->color(fn(string $state) => match($state) {
                        'draft'     => 'warning',
                        'published' => 'success',
                        'archived'  => 'danger',
                        'scheduled' => 'primary',
                        default     => 'gray',
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('edit')
                    ->icon('heroicon-m-pencil-square')
                    ->url(fn (Quiz $r) => route('filament.lecturer.resources.quizzes.edit', $r))
                    ->iconButton(),
            ])
            ->paginated(false);
    }
}
