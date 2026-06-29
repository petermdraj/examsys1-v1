<?php

namespace App\Filament\Lecturer\Resources\QuestionBankResource\Pages;

use App\Filament\Lecturer\Resources\QuestionBankResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBankQuestion extends EditRecord
{
    protected static string $resource = QuestionBankResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
