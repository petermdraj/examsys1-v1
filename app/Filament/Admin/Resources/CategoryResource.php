<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\CategoryResource\Pages;
use App\Models\Category;
use App\Support\CategoryIcons;
use App\Traits\RestrictInDemoMode;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class CategoryResource extends Resource
{
    use RestrictInDemoMode;
    protected static ?string $model = Category::class;
    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string  { return __('admin.nav_group_content'); }
    public static function getNavigationLabel(): string   { return __('admin.nav_categories'); }
    public static function getModelLabel(): string        { return __('admin.category_model_label'); }
    public static function getPluralModelLabel(): string  { return __('admin.category_model_label_plural'); }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make(__('admin.category_section_details'))
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label(__('admin.category_field_name'))
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn ($state, Forms\Set $set) => $set('slug', Str::slug($state))),

                    Forms\Components\TextInput::make('slug')
                        ->label(__('admin.category_field_slug'))
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->helperText(__('admin.category_slug_helper')),

                    Forms\Components\Textarea::make('description')
                        ->label(__('admin.category_field_description'))
                        ->rows(3)
                        ->columnSpanFull(),

                    Forms\Components\Select::make('parent_id')
                        ->label(__('admin.category_field_parent'))
                        ->relationship('parent', 'name')
                        ->searchable()
                        ->preload()
                        ->nullable()
                        ->placeholder(__('admin.category_parent_placeholder')),
                ])
                ->columns(2)
                ->columnSpan(2),

            Forms\Components\Section::make(__('admin.category_section_appearance'))
                ->schema([
                    Forms\Components\Select::make('icon')
                        ->label(__('admin.category_field_icon'))
                        ->options(CategoryIcons::selectOptions())
                        ->searchable()
                        ->live()
                        ->helperText(__('admin.category_icon_helper')),

                    Forms\Components\ColorPicker::make('color')
                        ->label(__('admin.category_field_color'))
                        ->helperText(__('admin.category_color_helper')),

                    Forms\Components\Placeholder::make('icon_preview')
                        ->label(__('admin.category_field_preview'))
                        ->content(function (Forms\Get $get): \Illuminate\Support\HtmlString {
                            $icon  = $get('icon');
                            $color = $get('color') ?: '#6C2E63';
                            if (! $icon) {
                                return new \Illuminate\Support\HtmlString('<span style="color:#9ca3af;font-size:13px;">' . __('admin.category_icon_select_hint') . '</span>');
                            }
                            $svg    = CategoryIcons::svg($icon, 40, '#ffffff');
                            $b64    = base64_encode($svg);
                            return new \Illuminate\Support\HtmlString(
                                '<div style="display:flex;align-items:center;gap:12px;">'
                                . '<div style="width:64px;height:64px;border-radius:14px;background:' . $color . ';display:flex;align-items:center;justify-content:center;">'
                                . '<img src="data:image/svg+xml;base64,' . $b64 . '" width="40" height="40">'
                                . '</div>'
                                . '<span style="font-size:13px;color:#6b7280;">' . ucfirst($icon) . '</span>'
                                . '</div>'
                            );
                        }),

                    Forms\Components\TextInput::make('sort_order')
                        ->numeric()
                        ->default(0)
                        ->helperText(__('admin.category_sort_order_helper')),

                    Forms\Components\Toggle::make('is_active')
                        ->label(__('admin.category_field_is_active'))
                        ->default(true)
                        ->helperText(__('admin.category_is_active_helper')),
                ])
                ->columnSpan(1),
        ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Icon + color swatch
                Tables\Columns\ImageColumn::make('icon')
                    ->label('')
                    ->width(36)
                    ->height(36)
                    ->extraImgAttributes(fn ($record) => [
                        'style' => 'border-radius:8px;padding:7px;background:' . ($record->color ?: '#6C2E63') . ';',
                    ])
                    ->getStateUsing(function ($record): string {
                        if (! $record->icon) return '';
                        $color = $record->color ?: '#6C2E63';
                        $svg   = CategoryIcons::svg($record->icon, 22, '#ffffff');
                        return 'data:image/svg+xml;base64,' . base64_encode($svg);
                    }),

                Tables\Columns\TextColumn::make('name')
                    ->label(__('admin.category_col_name'))
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),

                Tables\Columns\TextColumn::make('parent.name')
                    ->label(__('admin.category_col_parent'))
                    ->badge()
                    ->color('gray')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('quizzes_count')
                    ->counts('quizzes')
                    ->label(__('admin.category_col_quizzes'))
                    ->badge()
                    ->color(fn ($state) => $state > 0 ? 'success' : 'gray')
                    ->sortable(),

                Tables\Columns\TextColumn::make('children_count')
                    ->counts('children')
                    ->label(__('admin.category_col_subcategories'))
                    ->badge()
                    ->color(fn ($state) => $state > 0 ? 'info' : 'gray')
                    ->sortable(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label(__('admin.category_col_order'))
                    ->sortable()
                    ->alignCenter()
                    ->color('gray'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('admin.category_col_active'))
                    ->boolean()
                    ->sortable(),
            ])
            ->defaultSort('sort_order', 'asc')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label(__('admin.category_filter_status'))
                    ->trueLabel(__('admin.category_filter_active_only'))
                    ->falseLabel(__('admin.category_filter_inactive_only')),

                Tables\Filters\SelectFilter::make('parent_id')
                    ->label(__('admin.category_filter_type'))
                    ->options([
                        '' => __('admin.category_filter_top_level'),
                    ])
                    ->query(fn ($query, $data) => $data['value'] === ''
                        ? $query->whereNull('parent_id')
                        : $query),
            ])
            ->actions([
                Tables\Actions\Action::make('toggle_active')
                    ->label(fn (Category $record) => $record->is_active ? __('admin.category_action_deactivate') : __('admin.category_action_activate'))
                    ->icon(fn (Category $record) => $record->is_active ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn (Category $record) => $record->is_active ? 'warning' : 'success')
                    ->action(fn (Category $record) => $record->update(['is_active' => ! $record->is_active]))
                    ->tooltip(fn (Category $record) => $record->is_active ? __('admin.category_tooltip_hide') : __('admin.category_tooltip_show')),

                Tables\Actions\EditAction::make()->iconButton(),
                Tables\Actions\DeleteAction::make()->iconButton(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('activate')
                        ->label(__('admin.category_bulk_activate'))
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->action(fn ($records) => $records->each->update(['is_active' => true]))
                        ->deselectRecordsAfterCompletion(),

                    Tables\Actions\BulkAction::make('deactivate')
                        ->label(__('admin.category_bulk_deactivate'))
                        ->icon('heroicon-o-eye-slash')
                        ->color('warning')
                        ->action(fn ($records) => $records->each->update(['is_active' => false]))
                        ->deselectRecordsAfterCompletion(),

                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading(__('admin.category_empty_heading'))
            ->emptyStateDescription(__('admin.category_empty_description'))
            ->emptyStateIcon('heroicon-o-tag');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit'   => Pages\EditCategory::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) Category::where('is_active', true)->count();
    }

    public static function getNavigationBadgeColor(): string
    {
        return 'success';
    }
}
