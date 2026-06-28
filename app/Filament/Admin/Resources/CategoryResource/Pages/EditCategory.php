<?php
namespace App\Filament\Admin\Resources\CategoryResource\Pages;
use App\Filament\Admin\Resources\CategoryResource;
use App\Traits\DemoModeEditPage;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
class EditCategory extends EditRecord {
    use DemoModeEditPage;
    protected static string $resource = CategoryResource::class;
    protected function getHeaderActions(): array { return [Actions\DeleteAction::make()]; }
}
