<?php

namespace App\Filament\Admin\Resources\StudentResource\Pages;

use App\Filament\Admin\Resources\Concerns\HandlesPrivilegedUserFields;
use App\Filament\Admin\Resources\StudentResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateStudent extends CreateRecord
{
    use HandlesPrivilegedUserFields;

    protected static string $resource = StudentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['role'] = 'student';

        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        return $this->createUserWithPrivilegedFields($data);
    }
}
