<?php

namespace App\Filament\Admin\Pages;

use App\Models\LoginHistory;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class LoginHistoryReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?int $navigationSort = 5;

    protected static string $view = 'filament.admin.pages.login-history-report';

    public static function getNavigationGroup(): ?string
    {
        return __('admin.nav_group_platform');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.nav_login_history');
    }

    public function getTitle(): string
    {
        return __('admin.nav_login_history');
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('admin.view_all_reports') ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(LoginHistory::query()->with('user')->latest('logged_in_at'))
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('admin.login_hist_col_user'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.email')
                    ->label(__('admin.col_email'))
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('user.role')
                    ->label(__('admin.login_hist_col_role'))
                    ->badge(),
                Tables\Columns\TextColumn::make('ip_address')
                    ->label(__('admin.login_hist_col_ip'))
                    ->fontFamily('mono'),
                Tables\Columns\TextColumn::make('user_agent')
                    ->label(__('admin.login_hist_col_agent'))
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('logged_in_at')
                    ->label(__('admin.login_hist_col_time'))
                    ->dateTime('d M Y, H:i:s')
                    ->sortable(),
            ])
            ->defaultSort('logged_in_at', 'desc')
            ->paginated([25, 50, 100]);
    }
}
