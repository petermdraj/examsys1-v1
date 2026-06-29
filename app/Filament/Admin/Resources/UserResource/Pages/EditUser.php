<?php

namespace App\Filament\Admin\Resources\UserResource\Pages;

use App\Filament\Admin\Resources\UserResource;
use App\Models\User;
use App\Services\Quiz\QuizAssignmentService;
use App\Traits\DemoModeEditPage;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class EditUser extends EditRecord
{
    use DemoModeEditPage;

    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $privileged = Arr::only($data, [
            'role',
            'is_active',
            'student_batch_id',
            'ai_credits_free_remaining',
            'ai_credits_used',
            'email_verified_at',
        ]);
        $safe = Arr::except($data, array_keys($privileged));

        /** @var User $record */
        $previousBatchId = $record->student_batch_id;

        $record->fill($safe);
        $record->forceFill($privileged)->save();

        if ($record->role === 'student' && $record->student_batch_id !== $previousBatchId) {
            app(QuizAssignmentService::class)->syncEnrollmentsForStudent($record);
        }

        return $record;
    }
}
