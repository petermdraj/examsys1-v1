<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Order;
use App\Settings\PlatformSettings;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RecentOrdersWidget extends BaseWidget
{
    protected static ?int $sort = 5;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $sym = app(PlatformSettings::class)->currency_symbol;

        return $table
            ->heading(__('admin.widget_recent_orders'))
            ->description(__('admin.widget_recent_orders_desc'))
            ->query(
                Order::with(['user:id,name,email', 'quiz:id,title'])
                    ->where('status', 'paid')
                    ->latest('paid_at')
                    ->limit(8)
            )
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('admin.recent_orders_col_customer'))
                    ->weight('semibold'),

                Tables\Columns\TextColumn::make('quiz.title')
                    ->label(__('admin.recent_orders_col_quiz'))
                    ->limit(40)
                    ->color('gray'),

                Tables\Columns\TextColumn::make('amount')
                    ->label(__('admin.recent_orders_col_amount'))
                    ->formatStateUsing(fn ($state) => $sym . number_format((float) $state, 2))
                    ->weight('semibold'),

                Tables\Columns\TextColumn::make('platform_commission')
                    ->label(__('admin.recent_orders_col_commission'))
                    ->formatStateUsing(fn ($state) => $sym . number_format((float) $state, 2))
                    ->color('success'),

                Tables\Columns\TextColumn::make('gateway')
                    ->label(__('admin.recent_orders_col_gateway'))
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'razorpay' => 'info',
                        'stripe'   => 'primary',
                        'paypal'   => 'warning',
                        default    => 'gray',
                    }),

                Tables\Columns\TextColumn::make('paid_at')
                    ->label(__('admin.recent_orders_col_paid_at'))
                    ->dateTime('d M Y, H:i')
                    ->color('gray'),
            ])
            ->paginated(false);
    }
}
