<?php

namespace App\Filament\Admin\Resources\Concerns;

use App\Models\User;
use App\Services\Quiz\QuizAssignmentService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

trait HandlesPrivilegedUserFields
{
    protected function createUserWithPrivilegedFields(array $data): User
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

        /** @var User $user */
        $user = static::getModel()::create($safe);
        $user->forceFill($privileged)->save();

        if ($user->role === 'student' && $user->student_batch_id) {
            app(QuizAssignmentService::class)->syncEnrollmentsForStudent($user);
        }

        return $user;
    }

    protected function updateUserWithPrivilegedFields(Model $record, array $data): User
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
