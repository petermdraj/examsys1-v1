<?php

namespace App\Services\AI;

use App\Models\User;

class AiCreditService
{
    public function consume(User $user, ?string $referenceId = null): string
    {
        return 'free';
    }

    public function refund(User $user, string $source = 'free', ?string $referenceId = null): void
    {
        // No-op: AI generation is unlimited.
    }

    public function adminGrant(User $user, int $credits, string $note = ''): void
    {
        // Legacy hook for admin panel; credits are not enforced.
    }
}
