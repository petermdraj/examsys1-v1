<?php

namespace App\Filament\Admin\Widgets;

use App\Models\User;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables;
use Filament\Tables\Table;

class TopLecturersWidget extends BaseWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading(__('admin.widget_top_lecturers'))
            ->description(__('admin.widget_top_lecturers_desc'))
            ->query(
                User::where('role', 'lecturer')
                    ->withCount('quizzes')
                    ->withCount(['quizzes as published_quizzes_count' => fn ($q) => $q->where('status', 'published')])
                    ->orderByDesc('published_quizzes_count')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('admin.col_lecturer'))
                    ->searchable()
                    ->weight('semibold'),

                Tables\Columns\TextColumn::make('email')
                    ->label(__('admin.col_email'))
                    ->color('gray'),

                Tables\Columns\TextColumn::make('published_quizzes_count')
                    ->label(__('admin.col_published'))
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('quizzes_count')
                    ->label(__('admin.col_total_quizzes'))
                    ->color('gray'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('admin.col_joined'))
                    ->date('d M Y')
                    ->color('gray'),
            ])
            ->paginated(false);
    }
}
