<?php

namespace App\Filament\Admin\Resources\StudentResource\Pages;

use App\Filament\Admin\Resources\Concerns\HandlesPrivilegedUserFields;
use App\Filament\Admin\Resources\StudentResource;
use App\Traits\DemoModeEditPage;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditStudent extends EditRecord
{
    use DemoModeEditPage;
    use HandlesPrivilegedUserFields;

    protected static string $resource = StudentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['role'] = 'student';

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return $this->updateUserWithPrivilegedFields($record, $data);
    }
}
