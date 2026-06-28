<?php

namespace App\Filament\Creator\Resources\QuestionBankResource\Pages;

use App\Filament\Creator\Resources\QuestionBankResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBankQuestions extends ListRecords
{
    protected static string $resource = QuestionBankResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label(__('creator.qbank_action_add_question')),
        ];
    }
}
