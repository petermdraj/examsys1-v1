<?php

namespace App\Filament\Admin\Widgets;

use App\Models\User;
use App\Settings\PlatformSettings;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TopCreatorsWidget extends BaseWidget
{
    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $sym = app(PlatformSettings::class)->currency_symbol;

        return $table
            ->heading(__('admin.widget_top_creators'))
            ->description(__('admin.widget_top_creators_desc'))
            ->query(
                User::where('role', 'creator')
                    ->withCount('quizzes')
                    ->withCount(['quizzes as published_quizzes_count' => fn ($q) => $q->where('status', 'published')])
                    ->withSum(['orders as total_earned' => fn ($q) => $q->where('status', 'paid')], 'creator_earning')
                    ->orderByDesc('total_earned')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('admin.col_creator'))
                    ->searchable()
                    ->weight('semibold'),

                Tables\Columns\TextColumn::make('email')
                    ->label(__('admin.col_email'))
                    ->color('gray'),

                Tables\Columns\TextColumn::make('published_quizzes_count')
                    ->label(__('admin.col_published'))
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('quizzes_count')
                    ->label(__('admin.col_total_quizzes'))
                    ->color('gray'),

                Tables\Columns\TextColumn::make('total_earned')
                    ->label(__('admin.col_earnings'))
                    ->formatStateUsing(fn ($state) => $sym . number_format((float) $state, 2))
                    ->weight('semibold')
                    ->color('primary'),

                Tables\Columns\TextColumn::make('wallet_balance')
                    ->label(__('admin.col_wallet'))
                    ->formatStateUsing(fn ($state) => $sym . number_format((float) $state, 2))
                    ->color('gray'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('admin.col_joined'))
                    ->date('d M Y')
                    ->color('gray'),
            ])
            ->paginated(false);
    }
}
