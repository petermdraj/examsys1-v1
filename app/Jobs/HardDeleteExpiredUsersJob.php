<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class HardDeleteExpiredUsersJob implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        // Permanently delete users soft-deleted more than 30 days ago.
        // Chunk to avoid loading all expired rows into memory at once.
        User::onlyTrashed()
            ->where('deleted_at', '<=', now()->subDays(30))
            ->chunkById(500, function ($users) {
                $users->each->forceDelete();
            });
    }
}
