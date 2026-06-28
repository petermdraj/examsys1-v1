<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\UserResource\Pages;
use App\Traits\RestrictInDemoMode;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Settings\PlatformSettings;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use STS\FilamentImpersonate\Tables\Actions\Impersonate;

class UserResource extends Resource
{
    use RestrictInDemoMode;
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string  { return __('admin.nav_group_platform'); }
    public static function getNavigationLabel(): string   { return __('admin.nav_users'); }
    public static function getModelLabel(): string        { return __('admin.user_model_label'); }
    public static function getPluralModelLabel(): string  { return __('admin.user_model_label_plural'); }

    public static function getNavigationBadge(): ?string
    {
        return (string) User::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary';
    }

    public static function form(Form $form): Form
    {
        $sym = app(PlatformSettings::class)->currency_symbol;

        return $form->schema([
            Forms\Components\Section::make(__('admin.user_section_identity'))
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label(__('admin.user_col_name'))
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('email')
                        ->label(__('admin.user_col_email'))
                        ->email()
                        ->required()
                        ->unique(User::class, 'email', ignoreRecord: true)
                        ->maxLength(255),
                    Forms\Components\TextInput::make('password')
                        ->label(__('admin.user_field_password'))
                        ->password()
                        ->dehydrateStateUsing(fn ($s) => bcrypt($s))
                        ->dehydrated(fn ($s) => filled($s))
                        ->required(fn (string $operation) => $operation === 'create')
                        ->helperText(__('admin.user_password_helper')),
                    Forms\Components\Select::make('role')
                        ->label(__('admin.user_col_role'))
                        ->options([
                            'super_admin' => __('admin.user_role_super_admin'),
                            'creator'     => __('admin.user_role_creator'),
                            'customer'    => __('admin.user_role_customer'),
                        ])
                        ->required(),
                    Forms\Components\TextInput::make('phone')->label(__('admin.user_field_phone'))->maxLength(30),
                    Forms\Components\TextInput::make('country_code')->maxLength(5)->label(__('admin.user_field_country_code')),
                    Forms\Components\Toggle::make('is_active')
                        ->label(__('admin.user_field_account_active'))
                        ->default(true)
                        ->helperText(__('admin.user_account_active_helper')),
                ]),

            Forms\Components\Section::make(__('admin.user_section_ai_wallet'))
                ->columns(2)
                ->description(__('admin.user_section_ai_wallet_desc'))
                ->schema([
                    Forms\Components\TextInput::make('ai_credits_free_remaining')
                        ->numeric()
                        ->minValue(0)
                        ->default(0)
                        ->label(__('admin.user_field_ai_credits_free'))
                        ->suffix('gens'),
                    Forms\Components\TextInput::make('ai_credits_used')
                        ->numeric()
                        ->default(0)
                        ->label(__('admin.user_field_ai_credits_used'))
                        ->disabled()
                        ->dehydrated(false),
                    Forms\Components\TextInput::make('wallet_balance')
                        ->numeric()
                        ->minValue(0)
                        ->default(0)
                        ->prefix($sym)
                        ->label(__('admin.user_field_wallet_balance')),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        $sym = app(PlatformSettings::class)->currency_symbol;

        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('avatar')
                    ->label('')
                    ->circular()
                    ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->name) . '&background=6366f1&color=fff&size=64'),

                Tables\Columns\TextColumn::make('name')
                    ->label(__('admin.user_col_name'))
                    ->searchable()
                    ->sortable()
                    ->weight('semibold')
                    ->description(fn ($record) => $record->email),

                Tables\Columns\TextColumn::make('role')
                    ->label(__('admin.user_col_role'))
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'super_admin' => 'danger',
                        'creator'     => 'success',
                        default       => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => ucfirst(str_replace('_', ' ', $state))),

                Tables\Columns\TextColumn::make('activeSubscription.plan.name')
                    ->label(__('admin.user_col_plan'))
                    ->badge()
                    ->color('primary')
                    ->default('—')
                    ->visible(fn () => true),

                Tables\Columns\TextColumn::make('quizzes_count')
                    ->counts('quizzes')
                    ->label(__('admin.user_col_quizzes'))
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('attempts_count')
                    ->counts('attempts')
                    ->label(__('admin.user_col_attempts'))
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('wallet_balance')
                    ->label(__('admin.user_col_wallet'))
                    ->formatStateUsing(fn ($state) => $sym . number_format((float) $state, 2))
                    ->sortable()
                    ->color(fn ($state) => $state > 0 ? 'success' : 'gray'),

                Tables\Columns\TextColumn::make('ai_credits_free_remaining')
                    ->label(__('admin.user_col_ai_credits'))
                    ->alignCenter()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('admin.user_col_active'))
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('admin.user_col_joined'))
                    ->date('d M Y')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->options([
                        'super_admin' => __('admin.user_role_super_admin'),
                        'creator'     => __('admin.user_role_creator'),
                        'customer'    => __('admin.user_role_customer'),
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label(__('admin.user_filter_account_status'))
                    ->trueLabel(__('admin.user_filter_active_only'))
                    ->falseLabel(__('admin.user_filter_suspended_only')),
                Tables\Filters\Filter::make('has_wallet')
                    ->label(__('admin.user_filter_has_wallet'))
                    ->query(fn (Builder $q) => $q->where('wallet_balance', '>', 0)),
            ])
            ->actions([
                Impersonate::make()
                    ->label(__('admin.user_action_login_as'))
                    ->icon('heroicon-o-arrow-right-on-rectangle')
                    ->redirectTo(fn ($record) => match ($record->role) {
                        'creator'     => route('filament.creator.pages.dashboard'),
                        'super_admin' => route('filament.admin.pages.dashboard'),
                        default       => route('home'),
                    })
                    ->backTo(route('filament.admin.resources.users.index')),

                Tables\Actions\Action::make('toggle_active')
                    ->label(fn ($record) => $record->is_active ? __('admin.user_action_suspend') : __('admin.user_action_activate'))
                    ->icon(fn ($record) => $record->is_active ? 'heroicon-o-no-symbol' : 'heroicon-o-check-circle')
                    ->color(fn ($record) => $record->is_active ? 'danger' : 'success')
                    ->hidden(fn ($record) => $record->role === 'super_admin')
                    ->requiresConfirmation()
                    ->modalHeading(fn ($record) => $record->is_active ? __('admin.user_modal_suspend_heading') : __('admin.user_modal_activate_heading'))
                    ->modalDescription(fn ($record) => $record->is_active
                        ? __('admin.user_modal_suspend_desc')
                        : __('admin.user_modal_activate_desc'))
                    ->action(fn ($record) => $record->update(['is_active' => ! $record->is_active]))
                    ->after(fn () => Notification::make()->title(__('admin.user_status_updated'))->success()->send()),

                Tables\Actions\Action::make('grant_credits')
                    ->label(__('admin.user_action_grant_credits'))
                    ->icon('heroicon-o-sparkles')
                    ->color('warning')
                    ->visible(fn ($record) => in_array($record->role, ['creator', 'super_admin']))
                    ->form([
                        Forms\Components\TextInput::make('credits')
                            ->label(__('admin.user_credits_to_add'))
                            ->numeric()
                            ->minValue(1)
                            ->required(),
                    ])
                    ->action(function ($record, array $data) {
                        $record->increment('ai_credits_free_remaining', (int) $data['credits']);
                        Notification::make()->title(__('admin.user_credits_granted', ['count' => $data['credits']]))->success()->send();
                    }),

                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('activate')
                        ->label(__('admin.user_bulk_activate'))
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->update(['is_active' => true]))
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('deactivate')
                        ->label(__('admin.user_bulk_suspend'))
                        ->icon('heroicon-o-no-symbol')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->reject(fn ($r) => $r->role === 'super_admin')->each->update(['is_active' => false]))
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\DeleteBulkAction::make()
                        ->action(fn ($records) => $records->reject(fn ($r) => $r->role === 'super_admin')->each->delete()),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->poll('60s');
    }

    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return $record->role !== 'super_admin';
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit'   => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
