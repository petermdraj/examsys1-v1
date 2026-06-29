<?php

namespace App\Filament\Admin\Resources\StaffResource\Pages;

use App\Filament\Admin\Resources\Concerns\HandlesPrivilegedUserFields;
use App\Filament\Admin\Resources\StaffResource;
use App\Traits\DemoModeEditPage;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditStaff extends EditRecord
{
    use DemoModeEditPage;
    use HandlesPrivilegedUserFields;

    protected static string $resource = StaffResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return $this->updateUserWithPrivilegedFields($record, $data);
    }
}
