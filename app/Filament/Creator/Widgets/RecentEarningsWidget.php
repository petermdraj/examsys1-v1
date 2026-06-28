<?php

namespace App\Filament\Creator\Widgets;

use App\Models\Order;
use App\Settings\PlatformSettings;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentEarningsWidget extends BaseWidget
{
    protected static ?int $sort = 4;
    protected static ?string $heading = null;
    protected int | string | array $columnSpan = 1;

    public function getHeading(): string { return __('creator.recent_sales_heading'); }

    public function table(Table $table): Table
    {
        $sym = app(PlatformSettings::class)->currency_symbol;

        return $table
            ->query(
                Order::whereHas('quiz', fn ($q) => $q->where('creator_id', auth()->id()))
                    ->where('status', 'paid')
                    ->latest('paid_at')
                    ->limit(6)
            )
            ->columns([
                Tables\Columns\TextColumn::make('quiz.title')
                    ->label(__('creator.recent_sales_col_quiz'))
                    ->limit(28)
                    ->weight('semibold'),

                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('creator.recent_sales_col_buyer'))
                    ->limit(20),

                Tables\Columns\TextColumn::make('creator_earning')
                    ->label(__('creator.recent_sales_col_earned'))
                    ->formatStateUsing(fn ($state) => $sym . number_format((float) $state, 2))
                    ->color('success')
                    ->weight('semibold'),

                Tables\Columns\TextColumn::make('paid_at')
                    ->label(__('creator.recent_sales_col_date'))
                    ->date('d M')
                    ->color('gray'),
            ])
            ->paginated(false);
    }
}
