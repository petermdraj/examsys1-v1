<?php

namespace App\Filament\Admin\Resources;

use App\Models\CreatorPayout;
use App\Models\EmailTemplate;
use App\Settings\PlatformSettings;
use App\Traits\RestrictInDemoMode;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Mail;

class CreatorPayoutResource extends Resource
{
    use RestrictInDemoMode;
    protected static ?string $model = CreatorPayout::class;
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?int $navigationSort = 11;

    public static function getNavigationGroup(): ?string  { return __('admin.nav_group_finance'); }
    public static function getNavigationLabel(): string   { return __('admin.nav_payouts'); }
    public static function getModelLabel(): string        { return __('admin.payout_model_label'); }
    public static function getPluralModelLabel(): string  { return __('admin.payout_model_label_plural'); }

    public static function getNavigationBadge(): ?string
    {
        $pending = CreatorPayout::where('status', 'pending')->count();
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
            Infolists\Components\Section::make(__('admin.payout_section_details'))
                ->columns(3)
                ->schema([
                    Infolists\Components\TextEntry::make('creator.name')
                        ->label(__('admin.payout_col_creator'))
                        ->helperText(fn ($record) => $record->creator?->email),
                    Infolists\Components\TextEntry::make('amount')
                        ->label(__('admin.payout_col_amount'))
                        ->formatStateUsing(fn ($state) => $sym . number_format((float) $state, 2))
                        ->color('success'),
                    Infolists\Components\TextEntry::make('status')
                        ->label(__('admin.payout_col_status'))
                        ->badge()
                        ->formatStateUsing(fn (string $state) => __('admin.payout_status_' . $state))
                        ->color(fn ($state) => match ($state) {
                            'pending'    => 'warning',
                            'processing' => 'primary',
                            'paid'       => 'success',
                            'failed'     => 'danger',
                            default      => 'gray',
                        }),
                    Infolists\Components\TextEntry::make('gateway')->label(__('admin.payout_info_method')),
                    Infolists\Components\TextEntry::make('gateway_reference')->label(__('admin.payout_info_reference'))->copyable()->default('—'),
                    Infolists\Components\TextEntry::make('requested_at')->label(__('admin.payout_info_requested_at'))->dateTime('d M Y, h:i A'),
                    Infolists\Components\TextEntry::make('processed_at')->label(__('admin.payout_info_processed_at'))->dateTime('d M Y, h:i A')->placeholder(__('admin.payout_info_not_yet')),
                ]),
            Infolists\Components\Section::make(__('admin.payout_section_notes'))
                ->schema([
                    Infolists\Components\TextEntry::make('note')
                        ->label(__('admin.payout_info_creator_note'))
                        ->default(__('admin.payout_info_no_creator_note'))
                        ->columnSpanFull(),
                    Infolists\Components\TextEntry::make('admin_note')
                        ->label(__('admin.payout_info_admin_note'))
                        ->default(__('admin.payout_info_no_admin_note'))
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        $sym = app(PlatformSettings::class)->currency_symbol;

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('creator.name')
                    ->label(__('admin.payout_col_creator'))
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record) => $record->creator?->email),

                Tables\Columns\TextColumn::make('amount')
                    ->label(__('admin.payout_col_amount'))
                    ->formatStateUsing(fn ($state) => $sym . number_format((float) $state, 2))
                    ->sortable()
                    ->weight('semibold'),

                Tables\Columns\TextColumn::make('gateway')
                    ->label(__('admin.payout_col_method'))
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn ($state) => strtoupper($state ?? '—')),

                Tables\Columns\TextColumn::make('gateway_reference')
                    ->label(__('admin.payout_col_utr_ref'))
                    ->copyable()
                    ->placeholder('—')
                    ->fontFamily('mono'),

                Tables\Columns\TextColumn::make('status')
                    ->label(__('admin.payout_col_status'))
                    ->badge()
                    ->formatStateUsing(fn (string $state) => __('admin.payout_status_' . $state))
                    ->color(fn(string $state) => match($state) {
                        'pending'    => 'warning',
                        'processing' => 'primary',
                        'paid'       => 'success',
                        'failed'     => 'danger',
                        default      => 'gray',
                    }),

                Tables\Columns\TextColumn::make('requested_at')
                    ->label(__('admin.payout_col_requested_at'))
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('processed_at')
                    ->label(__('admin.payout_col_processed_at'))
                    ->date('d M Y')
                    ->placeholder('—'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('admin.payout_col_status'))
                    ->options([
                        'pending'    => __('admin.payout_status_pending'),
                        'processing' => __('admin.payout_status_processing'),
                        'paid'       => __('admin.payout_status_paid'),
                        'failed'     => __('admin.payout_status_failed'),
                    ]),
                Tables\Filters\Filter::make('pending_only')
                    ->label(__('admin.payout_filter_pending'))
                    ->query(fn (Builder $q) => $q->where('status', 'pending'))
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),

                Tables\Actions\Action::make('approve')
                    ->label(__('admin.payout_action_approve_pay'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (CreatorPayout $r) => in_array($r->status, ['pending', 'processing']))
                    ->form([
                        Forms\Components\TextInput::make('gateway_reference')
                            ->label(__('admin.payout_form_utr'))
                            ->helperText(__('admin.payout_form_utr_helper'))
                            ->required(),
                        Forms\Components\Textarea::make('note')
                            ->label(__('admin.payout_form_admin_note_optional'))
                            ->rows(2),
                    ])
                    ->action(function (CreatorPayout $r, array $data) {
                        $settings = app(PlatformSettings::class);
                        $sym      = $settings->currency_symbol;

                        \Illuminate\Support\Facades\DB::transaction(function () use ($r, $data) {
                            $r->update([
                                'status'            => 'paid',
                                'processed_at'      => now(),
                                'gateway_reference' => $data['gateway_reference'],
                                'admin_note'        => $data['note'] ?? null,
                            ]);
                            \App\Models\User::where('id', $r->creator_id)
                                ->decrement('wallet_balance', $r->amount);
                        });

                        // Send payout_paid email to creator
                        $r->load('creator');
                        $template = EmailTemplate::findByKey('payout_paid');
                        if ($template && $r->creator) {
                            $vars = [
                                'user_name'        => $r->creator->name,
                                'amount'           => $sym . number_format($r->amount, 2),
                                'gateway'          => $r->gateway,
                                'gateway_reference'=> $data['gateway_reference'],
                                'processed_at'     => now()->format('d M Y, h:i A'),
                                'admin_note'       => $data['note'] ?? '',
                                'earnings_url'     => url('/creator/earnings'),
                                'app_name'         => $settings->app_name,
                            ];
                            try {
                                Mail::html($template->renderRaw($vars), fn($m) =>
                                    $m->to($r->creator->email, $r->creator->name)
                                      ->subject($template->renderSubject($vars))
                                );
                            } catch (\Throwable) {}
                        }

                        Notification::make()->title(__('admin.payout_notif_paid'))->success()->send();
                    }),

                Tables\Actions\Action::make('mark_processing')
                    ->label(__('admin.payout_action_mark_processing'))
                    ->icon('heroicon-o-arrow-path')
                    ->color('primary')
                    ->visible(fn (CreatorPayout $r) => $r->status === 'pending')
                    ->action(function (CreatorPayout $r) {
                        $r->update(['status' => 'processing']);
                        Notification::make()->title(__('admin.payout_notif_processing'))->success()->send();
                    }),

                Tables\Actions\Action::make('reject')
                    ->label(__('admin.payout_action_reject'))
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (CreatorPayout $r) => in_array($r->status, ['pending', 'processing']))
                    ->form([
                        Forms\Components\Textarea::make('note')
                            ->label(__('admin.payout_form_reject_reason'))
                            ->helperText(__('admin.payout_form_reject_helper'))
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (CreatorPayout $r, array $data) {
                        $settings = app(PlatformSettings::class);
                        $sym      = $settings->currency_symbol;

                        $r->update([
                            'status'     => 'failed',
                            'admin_note' => $data['note'],
                        ]);

                        // Send payout_rejected email to creator
                        $r->load('creator');
                        $template = EmailTemplate::findByKey('payout_rejected');
                        if ($template && $r->creator) {
                            $vars = [
                                'user_name'   => $r->creator->name,
                                'amount'      => $sym . number_format($r->amount, 2),
                                'admin_note'  => $data['note'],
                                'earnings_url'=> url('/creator/earnings'),
                                'app_name'    => $settings->app_name,
                            ];
                            try {
                                Mail::html($template->renderRaw($vars), fn($m) =>
                                    $m->to($r->creator->email, $r->creator->name)
                                      ->subject($template->renderSubject($vars))
                                );
                            } catch (\Throwable) {}
                        }

                        Notification::make()->title(__('admin.payout_notif_rejected'))->warning()->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('bulk_approve')
                        ->label(__('admin.payout_bulk_mark_processing'))
                        ->icon('heroicon-o-arrow-path')
                        ->color('primary')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each(fn ($r) => $r->status === 'pending' && $r->update(['status' => 'processing'])))
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('requested_at', 'desc')
            ->striped();
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Admin\Resources\CreatorPayoutResource\Pages\ListCreatorPayouts::route('/'),
            'view'  => \App\Filament\Admin\Resources\CreatorPayoutResource\Pages\ViewCreatorPayout::route('/{record}'),
        ];
    }
}
