<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\EmailTemplateResource\Pages;
use App\Models\EmailTemplate;
use App\Traits\RestrictInDemoMode;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EmailTemplateResource extends Resource
{
    use RestrictInDemoMode;
    protected static ?string $model = EmailTemplate::class;
    protected static ?string $navigationIcon  = 'heroicon-o-envelope-open';
    protected static ?int    $navigationSort  = 20;

    public static function getNavigationGroup(): ?string  { return __('admin.nav_group_configuration'); }
    public static function getNavigationLabel(): string   { return __('admin.nav_email_templates'); }
    public static function getModelLabel(): string        { return __('admin.email_tpl_model_label'); }
    public static function getPluralModelLabel(): string  { return __('admin.email_tpl_model_label_plural'); }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make(__('admin.email_tpl_section_identity'))
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('key')
                        ->label(__('admin.email_tpl_field_key'))
                        ->helperText(__('admin.email_tpl_field_key_helper'))
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->alphaDash()
                        ->maxLength(60),
                    Forms\Components\TextInput::make('name')
                        ->label(__('admin.email_tpl_field_name'))
                        ->required()
                        ->maxLength(100),
                    Forms\Components\TextInput::make('subject')
                        ->label(__('admin.email_tpl_field_subject'))
                        ->helperText(__('admin.email_tpl_field_subject_helper'))
                        ->required()
                        ->columnSpanFull(),
                    Forms\Components\Toggle::make('is_active')
                        ->label(__('admin.email_tpl_field_active'))
                        ->default(true)
                        ->inline(false),
                ]),

            Forms\Components\Section::make(__('admin.email_tpl_section_variables'))
                ->description(__('admin.email_tpl_section_variables_desc'))
                ->schema([
                    Forms\Components\Repeater::make('variables')
                        ->schema([
                            Forms\Components\TextInput::make('name')
                                ->label(__('admin.email_tpl_var_name'))
                                ->helperText(__('admin.email_tpl_var_name_helper'))
                                ->required()
                                ->alphaDash()
                                ->columnSpan(1),
                            Forms\Components\TextInput::make('description')
                                ->label(__('admin.email_tpl_var_description'))
                                ->required()
                                ->columnSpan(2),
                        ])
                        ->columns(3)
                        ->addActionLabel(__('admin.email_tpl_add_variable'))
                        ->reorderable(false)
                        ->collapsible()
                        ->label(''),
                ]),

            Forms\Components\Section::make(__('admin.email_tpl_section_body'))
                ->schema([
                    Forms\Components\Textarea::make('body')
                        ->label('')
                        ->rows(28)
                        ->extraInputAttributes(['style' => 'font-family:monospace;font-size:12px;'])
                        ->helperText(__('admin.email_tpl_field_body_helper'))
                        ->columnSpanFull()
                        ->required(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('admin.email_tpl_col_template'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('key')
                    ->label(__('admin.email_tpl_col_key'))
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('subject')
                    ->label(__('admin.email_tpl_col_subject'))
                    ->limit(50)
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('admin.email_tpl_col_active'))
                    ->boolean(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('admin.email_tpl_col_updated_at'))
                    ->since()
                    ->sortable(),
            ])
            ->defaultSort('name')
            ->actions([
                Tables\Actions\Action::make('preview')
                    ->label(__('admin.email_tpl_action_preview'))
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->modalHeading(fn (EmailTemplate $r) => __('admin.email_tpl_modal_preview_heading') . ' — ' . $r->name)
                    ->modalWidth('4xl')
                    ->modalContent(fn (EmailTemplate $r) => view('filament.admin.email-template-preview', ['template' => $r]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel(__('admin.email_tpl_modal_close')),

                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListEmailTemplates::route('/'),
            'create' => Pages\CreateEmailTemplate::route('/create'),
            'edit'   => Pages\EditEmailTemplate::route('/{record}/edit'),
        ];
    }
}
