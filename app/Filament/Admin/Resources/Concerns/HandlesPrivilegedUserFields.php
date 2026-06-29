<?php

namespace App\Filament\Admin\Resources\Concerns;

use App\Models\User;
use App\Services\Quiz\QuizAssignmentService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

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

        $this->assertCanAssignStaffRole($privileged['role'] ?? null);

        /** @var User $user */
        $user = static::getModel()::create($safe);
        $user->forceFill($privileged)->save();
        $this->syncSpatieRole($user);

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
        $this->assertCanManageStaffUser($record);
        $this->assertCanAssignStaffRole($privileged['role'] ?? $record->role, $record);

        $previousBatchId = $record->student_batch_id;

        $record->fill($safe);
        $record->forceFill($privileged)->save();
        $this->syncSpatieRole($record);

        if ($record->role === 'student' && $record->student_batch_id !== $previousBatchId) {
            app(QuizAssignmentService::class)->syncEnrollmentsForStudent($record);
        }

        return $record;
    }

    protected function assertCanManageStaffUser(User $target): void
    {
        $actor = auth()->user();

        if (! $actor instanceof User || ! $actor->canManageStaffUser($target)) {
            throw ValidationException::withMessages([
                'role' => __('admin.staff_cannot_manage_super_admin'),
            ]);
        }
    }

    protected function assertCanAssignStaffRole(?string $role, ?User $existing = null): void
    {
        $actor = auth()->user();

        if (! $actor instanceof User) {
            abort(403);
        }

        if ($role === 'super_admin' && ! $actor->isSuperAdmin()) {
            throw ValidationException::withMessages([
                'role' => __('admin.staff_cannot_assign_super_admin'),
            ]);
        }

        if ($existing?->isSuperAdmin() && ! $actor->isSuperAdmin()) {
            throw ValidationException::withMessages([
                'role' => __('admin.staff_cannot_manage_super_admin'),
            ]);
        }

        $allowedRoles = array_keys(User::assignableStaffRoleOptions($actor));

        if ($role !== null && ! in_array($role, $allowedRoles, true)) {
            throw ValidationException::withMessages([
                'role' => __('admin.staff_invalid_role'),
            ]);
        }
    }

    protected function syncSpatieRole(User $user): void
    {
        if (! in_array($user->role, ['super_admin', 'admin', 'lecturer', 'student'], true)) {
            return;
        }

        $user->syncRoles([$user->role]);
    }
}
