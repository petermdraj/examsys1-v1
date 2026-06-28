<?php
namespace App\Filament\Admin\Resources\UserResource\Pages;
use App\Filament\Admin\Resources\UserResource;
use App\Traits\DemoModeEditPage;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
class EditUser extends EditRecord {
    use DemoModeEditPage;
    protected static string $resource = UserResource::class;
    protected function getHeaderActions(): array { return [Actions\DeleteAction::make()]; }
}
