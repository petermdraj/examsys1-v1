<?php

namespace App\Filament\Admin\Resources\StudentBatchResource\Pages;

use App\Filament\Admin\Resources\StudentBatchResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStudentBatches extends ListRecords
{
    protected static string $resource = StudentBatchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
