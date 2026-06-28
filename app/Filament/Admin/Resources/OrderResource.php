<?php

namespace App\Filament\Admin\Resources;

use App\Traits\RestrictInDemoMode;

use App\Models\Order;
use App\Settings\PlatformSettings;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrderResource extends Resource
{
    use RestrictInDemoMode;
    protected static ?string $model = Order::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?int $navigationSort = 10;

    public static function getNavigationGroup(): ?string  { return __('admin.nav_group_finance'); }
    public static function getNavigationLabel(): string   { return __('admin.nav_orders'); }
    public static function getModelLabel(): string        { return __('admin.order_model_label'); }
    public static function getPluralModelLabel(): string  { return __('admin.order_model_label_plural'); }

    public static function getNavigationBadge(): ?string
    {
        $pending = Order::where('status', 'pending')->count();
        return $pending > 0 ? (string) $pending : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        $sym = app(PlatformSettings::class)->currency_symbol;

        return $infolist->schema([
            Infolists\Components\Section::make(__('admin.order_section_summary'))
                ->columns(3)
                ->schema([
                    Infolists\Components\TextEntry::make('id')
                        ->label(__('admin.order_col_id'))
                        ->copyable()
                        ->fontFamily('mono'),
                    Infolists\Components\TextEntry::make('status')
                        ->label(__('admin.order_col_status'))
                        ->badge()
                        ->formatStateUsing(fn (string $state) => __('admin.order_status_' . $state))
                        ->color(fn ($state) => match ($state) {
                            'paid'     => 'success',
                            'pending'  => 'warning',
                            'failed'   => 'danger',
                            'refunded' => 'gray',
                            default    => 'gray',
                        }),
                    Infolists\Components\TextEntry::make('gateway')
                        ->label(__('admin.order_col_gateway'))
                        ->badge()
                        ->color('primary'),
                ]),

            Infolists\Components\Section::make(__('admin.order_section_people'))
                ->columns(2)
                ->schema([
                    Infolists\Components\TextEntry::make('user.name')
                        ->label(__('admin.order_col_buyer'))
                        ->helperText(fn ($record) => $record->user?->email),
                    Infolists\Components\TextEntry::make('quiz.title')
                        ->label(__('admin.order_col_quiz'))
                        ->helperText(fn ($record) => $record->quiz?->creator?->name ? 'by ' . $record->quiz->creator->name : null),
                ]),

            Infolists\Components\Section::make(__('admin.order_section_financials'))
                ->columns(3)
                ->schema([
                    Infolists\Components\TextEntry::make('amount')
                        ->label(__('admin.order_info_total_paid'))
                        ->formatStateUsing(fn ($state) => $sym . number_format((float) $state, 2))
                        ->color('success'),
                    Infolists\Components\TextEntry::make('platform_commission')
                        ->label(__('admin.order_col_commission'))
                        ->formatStateUsing(fn ($state) => $sym . number_format((float) $state, 2))
                        ->color('primary'),
                    Infolists\Components\TextEntry::make('creator_earning')
                        ->label(__('admin.order_info_creator_earned'))
                        ->formatStateUsing(fn ($state) => $sym . number_format((float) $state, 2))
                        ->color('warning'),
                    Infolists\Components\TextEntry::make('currency')->label(__('admin.order_col_currency'))->badge()->color('gray'),
                    Infolists\Components\TextEntry::make('paid_at')->label(__('admin.order_col_paid_at'))->dateTime('d M Y, h:i A'),
                    Infolists\Components\TextEntry::make('created_at')->label(__('admin.order_info_placed_at'))->dateTime('d M Y, h:i A'),
                ]),

            Infolists\Components\Section::make(__('admin.order_section_gateway'))
                ->columns(2)
                ->schema([
                    Infolists\Components\TextEntry::make('gateway_order_id')
                        ->label(__('admin.order_gateway_order_id'))
                        ->copyable()
                        ->fontFamily('mono')
                        ->default('—'),
                    Infolists\Components\TextEntry::make('gateway_payment_id')
                        ->label(__('admin.order_gateway_payment_id'))
                        ->copyable()
                        ->fontFamily('mono')
                        ->default('—'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        $sym = app(PlatformSettings::class)->currency_symbol;

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label(__('admin.order_col_id'))
                    ->limit(8)
                    ->copyable()
                    ->fontFamily('mono')
                    ->tooltip(fn ($record) => $record->id),

                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('admin.order_col_buyer'))
                    ->searchable()
                    ->description(fn ($record) => $record->user?->email),

                Tables\Columns\TextColumn::make('quiz.title')
                    ->label(__('admin.order_col_quiz'))
                    ->limit(35)
                    ->searchable()
                    ->description(fn ($record) => $record->quiz?->creator?->name ? 'by ' . $record->quiz->creator->name : null),

                Tables\Columns\TextColumn::make('amount')
                    ->label(__('admin.order_col_amount'))
                    ->formatStateUsing(fn ($state) => $sym . number_format((float) $state, 2))
                    ->sortable()
                    ->weight('semibold'),

                Tables\Columns\TextColumn::make('platform_commission')
                    ->label(__('admin.order_col_commission'))
                    ->formatStateUsing(fn ($state) => $sym . number_format((float) $state, 2))
                    ->color('primary'),

                Tables\Columns\TextColumn::make('creator_earning')
                    ->label(__('admin.order_col_creator_earning'))
                    ->formatStateUsing(fn ($state) => $sym . number_format((float) $state, 2))
                    ->color('warning'),

                Tables\Columns\TextColumn::make('status')
                    ->label(__('admin.order_col_status'))
                    ->badge()
                    ->formatStateUsing(fn (string $state) => __('admin.order_status_' . $state))
                    ->color(fn(string $state) => match($state) {
                        'pending'  => 'warning',
                        'paid'     => 'success',
                        'failed'   => 'danger',
                        'refunded' => 'gray',
                        default    => 'gray',
                    }),

                Tables\Columns\TextColumn::make('gateway')
                    ->label(__('admin.order_col_gateway'))
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn ($state) => ucfirst($state)),

                Tables\Columns\TextColumn::make('paid_at')
                    ->label(__('admin.order_col_paid_at'))
                    ->dateTime('d M Y')
                    ->sortable()
                    ->placeholder('—'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('admin.order_col_status'))
                    ->options([
                        'pending'  => __('admin.order_status_pending'),
                        'paid'     => __('admin.order_status_paid'),
                        'failed'   => __('admin.order_status_failed'),
                        'refunded' => __('admin.order_status_refunded'),
                    ]),
                Tables\Filters\SelectFilter::make('gateway')
                    ->label(__('admin.order_col_gateway'))
                    ->options([
                        'razorpay' => 'Razorpay',
                        'stripe'   => 'Stripe',
                        'paypal'   => 'PayPal',
                        'wallet'   => __('admin.order_gateway_wallet'),
                    ]),
                Tables\Filters\Filter::make('paid_today')
                    ->label(__('admin.order_filter_paid_today'))
                    ->query(fn (Builder $q) => $q->where('status', 'paid')->whereDate('paid_at', today())),
                Tables\Filters\Filter::make('paid_this_month')
                    ->label(__('admin.order_filter_paid_this_month'))
                    ->query(fn (Builder $q) => $q->where('status', 'paid')->whereMonth('paid_at', now()->month)),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('refund')
                    ->label(__('admin.order_action_refund'))
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('danger')
                    ->visible(fn (Order $r) => $r->status === 'paid')
                    ->requiresConfirmation()
                    ->modalHeading(__('admin.order_modal_refund_heading'))
                    ->modalDescription(__('admin.order_modal_refund_desc'))
                    ->action(function (Order $r) {
                        $r->update(['status' => 'refunded']);
                        Notification::make()->title(__('admin.order_notif_refunded'))->success()->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('export_csv')
                        ->label(__('admin.order_bulk_export_csv'))
                        ->icon('heroicon-o-arrow-down-tray')
                        ->action(function ($records) {
                            $sym = app(PlatformSettings::class)->currency_symbol;
                            $csv = "Order ID,Buyer,Quiz,Amount,Commission,Creator Earning,Status,Gateway,Paid At\n";
                            foreach ($records as $r) {
                                $csv .= implode(',', [
                                    $r->id,
                                    '"' . ($r->user?->name ?? '') . '"',
                                    '"' . ($r->quiz?->title ?? '') . '"',
                                    $r->amount,
                                    $r->platform_commission,
                                    $r->creator_earning,
                                    $r->status,
                                    $r->gateway,
                                    $r->paid_at,
                                ]) . "\n";
                            }
                            return response()->streamDownload(fn () => print($csv), 'orders-' . now()->format('Y-m-d') . '.csv');
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped();
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Admin\Resources\OrderResource\Pages\ListOrders::route('/'),
            'view'  => \App\Filament\Admin\Resources\OrderResource\Pages\ViewOrder::route('/{record}'),
        ];
    }
}
