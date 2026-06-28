<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PlanResource\Pages;
use App\Models\Plan;
use App\Settings\PlatformSettings;
use App\Traits\RestrictInDemoMode;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class PlanResource extends Resource
{
    use RestrictInDemoMode;
    protected static ?string $model = Plan::class;
    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?int $navigationSort = 2;

    public static function getNavigationLabel(): string  { return __('admin.nav_plans'); }
    public static function getModelLabel(): string       { return __('admin.plan_model_label'); }
    public static function getPluralModelLabel(): string { return __('admin.plan_model_label_plural'); }

    public static function form(Form $form): Form
    {
        $sym = app(PlatformSettings::class)->currency_symbol;

        return $form->schema([
            Forms\Components\Section::make(__('admin.plan_section_details'))
                ->description(__('admin.plan_section_details_desc'))
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label(__('admin.plan_field_name'))
                        ->required()
                        ->maxLength(100)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn ($state, Forms\Set $set) => $set('slug', Str::slug($state))),

                    Forms\Components\TextInput::make('slug')
                        ->label(__('admin.plan_field_slug'))
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->helperText(__('admin.plan_slug_helper')),

                    Forms\Components\TextInput::make('price_monthly')
                        ->label(__('admin.plan_field_price_monthly'))
                        ->numeric()
                        ->prefix($sym)
                        ->suffix(__('admin.plan_suffix_mo'))
                        ->default(0)
                        ->helperText(__('admin.plan_price_monthly_helper')),

                    Forms\Components\TextInput::make('price_yearly')
                        ->label(__('admin.plan_field_price_yearly'))
                        ->numeric()
                        ->prefix($sym)
                        ->suffix(__('admin.plan_suffix_yr'))
                        ->default(0)
                        ->helperText(__('admin.plan_price_yearly_helper')),

                    Forms\Components\TextInput::make('sort_order')
                        ->numeric()
                        ->default(0)
                        ->helperText(__('admin.plan_sort_order_helper')),

                    Forms\Components\Toggle::make('is_active')
                        ->label(__('admin.plan_field_visible'))
                        ->default(true)
                        ->helperText(__('admin.plan_is_active_helper')),
                ])
                ->columns(2)
                ->columnSpan(1),

            Forms\Components\Section::make(__('admin.plan_section_limits'))
                ->description(__('admin.plan_section_limits_desc'))
                ->schema([
                    Forms\Components\TextInput::make('ai_free_generations')
                        ->label(__('admin.plan_field_ai_tokens'))
                        ->numeric()
                        ->default(10)
                        ->suffix(__('admin.plan_suffix_tokens_mo'))
                        ->helperText(__('admin.plan_ai_tokens_helper')),

                    Forms\Components\TextInput::make('ai_charge_per_generation')
                        ->label(__('admin.plan_field_ai_charge_extra'))
                        ->numeric()
                        ->prefix($sym)
                        ->suffix(__('admin.plan_suffix_token'))
                        ->default(5.00)
                        ->step(0.01)
                        ->helperText(__('admin.plan_ai_charge_helper')),

                    Forms\Components\TextInput::make('commission_rate')
                        ->label(__('admin.plan_field_commission'))
                        ->numeric()
                        ->suffix('%')
                        ->default(20)
                        ->minValue(0)
                        ->maxValue(100)
                        ->helperText(__('admin.plan_commission_helper')),

                    Forms\Components\TextInput::make('max_published_quizzes')
                        ->label(__('admin.plan_field_max_quizzes'))
                        ->numeric()
                        ->nullable()
                        ->helperText(__('admin.plan_unlimited_helper')),

                    Forms\Components\TextInput::make('max_questions_per_quiz')
                        ->label(__('admin.plan_field_max_qs_per_quiz'))
                        ->numeric()
                        ->minValue(1)
                        ->nullable()
                        ->helperText(__('admin.plan_unlimited_helper')),

                    Forms\Components\TextInput::make('max_questions_in_bank')
                        ->label(__('admin.plan_field_max_qs_bank'))
                        ->numeric()
                        ->minValue(1)
                        ->nullable()
                        ->helperText(__('admin.plan_bank_helper')),

                    Forms\Components\Toggle::make('can_sell_paid_quizzes')
                        ->label(__('admin.plan_field_can_sell'))
                        ->default(false)
                        ->helperText(__('admin.plan_can_sell_helper')),

                    Forms\Components\Toggle::make('allow_custom_certificate_logo')
                        ->label(__('admin.plan_field_custom_cert_logo'))
                        ->default(false)
                        ->helperText(__('admin.plan_custom_cert_logo_helper')),
                ])
                ->columns(2)
                ->columnSpan(1),

            Forms\Components\Section::make(__('admin.plan_section_features'))
                ->description(__('admin.plan_section_features_desc'))
                ->schema([
                    Forms\Components\TagsInput::make('features')
                        ->label(__('admin.plan_field_features'))
                        ->placeholder(__('admin.plan_features_placeholder'))
                        ->helperText(__('admin.plan_features_helper'))
                        ->columnSpanFull(),
                ])
                ->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        $sym = app(PlatformSettings::class)->currency_symbol;

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('#')
                    ->sortable()
                    ->width('40px')
                    ->alignCenter()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('name')
                    ->label(__('admin.plan_col_name'))
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),

                Tables\Columns\TextColumn::make('price_monthly')
                    ->label(__('admin.plan_col_monthly'))
                    ->formatStateUsing(fn ($state) => $state > 0 ? $sym . number_format((float)$state, 0) : __('admin.plan_free_label'))
                    ->badge()
                    ->color(fn ($state) => $state > 0 ? 'primary' : 'success'),

                Tables\Columns\TextColumn::make('price_yearly')
                    ->label(__('admin.plan_col_yearly'))
                    ->formatStateUsing(fn ($state) => $state > 0 ? $sym . number_format((float)$state, 0) : '-')
                    ->color('gray'),

                Tables\Columns\TextColumn::make('commission_rate')
                    ->label(__('admin.plan_col_commission'))
                    ->formatStateUsing(fn ($state) => $state . '%')
                    ->badge()
                    ->color('warning'),

                Tables\Columns\TextColumn::make('ai_free_generations')
                    ->label(__('admin.plan_col_ai_tokens'))
                    ->formatStateUsing(fn ($state) => number_format($state) . ' /mo')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('max_published_quizzes')
                    ->label(__('admin.plan_col_max_quizzes'))
                    ->formatStateUsing(fn ($state) => $state ?? __('admin.plan_unlimited_label'))
                    ->alignCenter()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('max_questions_per_quiz')
                    ->label(__('admin.plan_col_qs_per_quiz'))
                    ->formatStateUsing(fn ($state) => $state ?? '∞')
                    ->alignCenter()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('max_questions_in_bank')
                    ->label(__('admin.plan_col_bank_limit'))
                    ->formatStateUsing(fn ($state) => $state ? number_format($state) : '∞')
                    ->alignCenter()
                    ->color('gray'),

                Tables\Columns\IconColumn::make('can_sell_paid_quizzes')
                    ->label(__('admin.plan_col_paid_quizzes'))
                    ->boolean(),

                Tables\Columns\TextColumn::make('subscriptions_count')
                    ->counts('subscriptions')
                    ->label(__('admin.plan_col_subscribers'))
                    ->badge()
                    ->color(fn ($state) => $state > 0 ? 'info' : 'gray')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('admin.plan_col_active'))
                    ->boolean()
                    ->sortable(),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->actions([
                Tables\Actions\Action::make('toggle_active')
                    ->label(fn (Plan $record) => $record->is_active ? __('admin.plan_action_deactivate') : __('admin.plan_action_activate'))
                    ->icon(fn (Plan $record) => $record->is_active ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn (Plan $record) => $record->is_active ? 'warning' : 'success')
                    ->action(fn (Plan $record) => $record->update(['is_active' => ! $record->is_active])),

                Tables\Actions\EditAction::make()->iconButton(),
                Tables\Actions\DeleteAction::make()->iconButton(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading(__('admin.plan_empty_heading'))
            ->emptyStateDescription(__('admin.plan_empty_description'))
            ->emptyStateIcon('heroicon-o-credit-card');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPlans::route('/'),
            'create' => Pages\CreatePlan::route('/create'),
            'edit'   => Pages\EditPlan::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) Plan::where('is_active', true)->count();
    }

    public static function getNavigationBadgeColor(): string
    {
        return 'primary';
    }
}
