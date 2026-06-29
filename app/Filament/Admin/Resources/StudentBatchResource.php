<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\StudentBatchResource\Pages;
use App\Models\StudentBatch;
use App\Traits\RestrictInDemoMode;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class StudentBatchResource extends Resource
{
    use RestrictInDemoMode;

    protected static ?string $model = StudentBatch::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.nav_group_platform');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.nav_student_batches');
    }

    public static function getModelLabel(): string
    {
        return __('admin.student_batch_model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.student_batch_model_label_plural');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make(__('admin.student_batch_section_details'))
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label(__('admin.student_batch_field_name'))
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('code')
                        ->label(__('admin.student_batch_field_code'))
                        ->maxLength(50)
                        ->unique(ignoreRecord: true)
                        ->helperText(__('admin.student_batch_code_helper')),
                    Forms\Components\Textarea::make('description')
                        ->label(__('admin.student_batch_field_description'))
                        ->rows(3)
                        ->columnSpanFull(),
                    Forms\Components\Toggle::make('is_active')
                        ->label(__('admin.student_batch_field_active'))
                        ->default(true),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('admin.student_batch_field_name'))
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),
                Tables\Columns\TextColumn::make('code')
                    ->label(__('admin.student_batch_field_code'))
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('students_count')
                    ->counts('students')
                    ->label(__('admin.student_batch_col_students'))
                    ->alignCenter(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('admin.student_batch_field_active'))
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('admin.col_created_at'))
                    ->date('d M Y')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label(__('admin.student_batch_field_active')),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListStudentBatches::route('/'),
            'create' => Pages\CreateStudentBatch::route('/create'),
            'edit'   => Pages\EditStudentBatch::route('/{record}/edit'),
        ];
    }
}
