<?php

namespace App\Filament\Lecturer\Resources\QuestionBankResource\Pages;

use App\Filament\Lecturer\Resources\QuestionBankResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBankQuestions extends ListRecords
{
    protected static string $resource = QuestionBankResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label(__('lecturer.qbank_action_add_question')),
        ];
    }
}
