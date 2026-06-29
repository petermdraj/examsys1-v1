<?php

namespace App\Filament\Admin\Resources\UserResource\Pages;

use App\Filament\Admin\Resources\UserResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function handleRecordCreation(array $data): Model
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
            app(\App\Services\Quiz\QuizAssignmentService::class)->syncEnrollmentsForStudent($user);
        }

        return $user;
    }
}
