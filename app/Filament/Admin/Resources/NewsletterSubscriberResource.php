<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\NewsletterSubscriberResource\Pages;
use App\Models\NewsletterSubscriber;
use App\Traits\RestrictInDemoMode;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;

class NewsletterSubscriberResource extends Resource
{
    use RestrictInDemoMode;
    protected static ?string $model = NewsletterSubscriber::class;

    protected static ?string $navigationIcon  = 'heroicon-o-envelope';
    protected static ?int    $navigationSort  = 1;

    public static function getNavigationGroup(): ?string  { return __('admin.nav_group_marketing'); }
    public static function getNavigationLabel(): string   { return __('admin.nav_newsletter_subscribers'); }
    public static function getModelLabel(): string        { return __('admin.newsletter_model_label'); }
    public static function getPluralModelLabel(): string  { return __('admin.newsletter_model_label_plural'); }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::where('is_active', true)->count();
    }

    public static function getNavigationBadgeColor(): string
    {
        return 'success';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('email')
                ->label(__('admin.newsletter_field_email'))
                ->email()
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('name')
                ->label(__('admin.newsletter_field_name'))
                ->maxLength(255),
            Forms\Components\Select::make('source')
                ->label(__('admin.newsletter_field_source'))
                ->options([
                    'footer'   => __('admin.newsletter_source_footer'),
                    'checkout' => __('admin.newsletter_source_checkout'),
                    'popup'    => __('admin.newsletter_source_popup'),
                    'manual'   => __('admin.newsletter_source_manual'),
                ])
                ->default('manual')
                ->native(false),
            Forms\Components\Toggle::make('is_active')
                ->label(__('admin.newsletter_field_active'))
                ->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('email')
                    ->label(__('admin.newsletter_col_email'))
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('name')
                    ->label(__('admin.newsletter_col_name'))
                    ->searchable()
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('source')
                    ->label(__('admin.newsletter_col_source'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'footer'   => 'primary',
                        'checkout' => 'info',
                        'popup'    => 'warning',
                        default    => 'gray',
                    }),
                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('admin.newsletter_col_subscribed'))
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('subscribed_at')
                    ->label(__('admin.newsletter_col_subscribed_at'))
                    ->dateTime('M j, Y')
                    ->sortable(),
            ])
            ->defaultSort('subscribed_at', 'desc')
            ->filters([
                TernaryFilter::make('is_active')
                    ->label(__('admin.newsletter_filter_status'))
                    ->trueLabel(__('admin.newsletter_filter_active_only'))
                    ->falseLabel(__('admin.newsletter_filter_unsubscribed_only')),
                SelectFilter::make('source')
                    ->label(__('admin.newsletter_field_source'))
                    ->options([
                        'footer'   => __('admin.newsletter_source_footer'),
                        'checkout' => __('admin.newsletter_source_checkout'),
                        'popup'    => __('admin.newsletter_source_popup'),
                        'manual'   => __('admin.newsletter_source_manual'),
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('unsubscribe')
                    ->label(__('admin.newsletter_action_unsubscribe'))
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->is_active)
                    ->action(fn ($record) => $record->update([
                        'is_active'        => false,
                        'unsubscribed_at'  => now(),
                    ])),
                Tables\Actions\Action::make('resubscribe')
                    ->label(__('admin.newsletter_action_resubscribe'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => ! $record->is_active)
                    ->action(fn ($record) => $record->update([
                        'is_active'       => true,
                        'unsubscribed_at' => null,
                        'subscribed_at'   => now(),
                    ])),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('export_csv')
                        ->label(__('admin.newsletter_bulk_export_csv'))
                        ->icon('heroicon-o-arrow-down-tray')
                        ->action(function ($records) {
                            $csv  = "Email,Name,Source,Subscribed At\n";
                            foreach ($records as $r) {
                                $csv .= implode(',', [
                                    '"' . $r->email . '"',
                                    '"' . ($r->name ?? '') . '"',
                                    '"' . $r->source . '"',
                                    '"' . $r->subscribed_at?->toDateTimeString() . '"',
                                ]) . "\n";
                            }
                            return response()->streamDownload(
                                fn () => print($csv),
                                'newsletter-subscribers-' . now()->format('Y-m-d') . '.csv',
                                ['Content-Type' => 'text/csv'],
                            );
                        }),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->headerActions([
                Tables\Actions\Action::make('export_all')
                    ->label(__('admin.newsletter_header_export_all'))
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->action(function () {
                        $records = NewsletterSubscriber::where('is_active', true)->get();
                        $csv  = "Email,Name,Source,Subscribed At\n";
                        foreach ($records as $r) {
                            $csv .= implode(',', [
                                '"' . $r->email . '"',
                                '"' . ($r->name ?? '') . '"',
                                '"' . $r->source . '"',
                                '"' . $r->subscribed_at?->toDateTimeString() . '"',
                            ]) . "\n";
                        }
                        return response()->streamDownload(
                            fn () => print($csv),
                            'newsletter-subscribers-' . now()->format('Y-m-d') . '.csv',
                            ['Content-Type' => 'text/csv'],
                        );
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListNewsletterSubscribers::route('/'),
            'create' => Pages\CreateNewsletterSubscriber::route('/create'),
            'edit'   => Pages\EditNewsletterSubscriber::route('/{record}/edit'),
        ];
    }
}
