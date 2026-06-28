<?php
namespace App\Filament\Admin\Resources\PlanResource\Pages;
use App\Filament\Admin\Resources\PlanResource;
use App\Traits\DemoModeEditPage;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
class EditPlan extends EditRecord {
    use DemoModeEditPage;
    protected static string $resource = PlanResource::class;
    protected function getHeaderActions(): array { return [Actions\DeleteAction::make()]; }
}
