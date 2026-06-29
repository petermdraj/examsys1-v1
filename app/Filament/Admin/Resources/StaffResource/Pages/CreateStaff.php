<?php

namespace App\Filament\Admin\Resources\StaffResource\Pages;

use App\Filament\Admin\Resources\Concerns\HandlesPrivilegedUserFields;
use App\Filament\Admin\Resources\StaffResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateStaff extends CreateRecord
{
    use HandlesPrivilegedUserFields;

    protected static string $resource = StaffResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return $this->createUserWithPrivilegedFields($data);
    }
}
