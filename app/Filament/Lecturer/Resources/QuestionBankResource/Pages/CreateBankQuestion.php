<?php

namespace App\Filament\Lecturer\Resources\QuestionBankResource\Pages;

use App\Filament\Lecturer\Resources\QuestionBankResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBankQuestion extends CreateRecord
{
    protected static string $resource = QuestionBankResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['quiz_id']     = null;
        $data['lecturer_id'] = auth()->id();
        $data['sort_order']  = 0;

        return $data;
    }
}
