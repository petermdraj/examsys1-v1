<?php

namespace App\Jobs;

use App\Models\Attempt;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Mail;

class SendAttemptResultMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public User $user,
        public ?Attempt $attempt,
    ) {
        $this->onQueue('emails');
    }

    public function handle(): void
    {
        if (! $this->attempt) {
            return;
        }

        // Queue workers bypass web middleware — set locale explicitly here.
        $locale = $this->user->preferred_locale ?? config('app.locale', 'en');
        $available = array_keys(config('app.available_locales', ['en' => 'English']));
        if (! in_array($locale, $available)) {
            $locale = config('app.locale', 'en');
        }
        App::setLocale($locale);
        Carbon::setLocale($locale);

        $prefs = $this->user->notification_preferences ?? [];
        if (($prefs['notify_quiz_results'] ?? true) === false) {
            return;
        }

        Mail::send('emails.attempt-result', [
            'user'    => $this->user,
            'attempt' => $this->attempt,
            'quiz'    => $this->attempt->quiz,
        ], function ($m) {
            $m->to($this->user->email, $this->user->name)
              ->subject(__('emails.result_subject') . ' — ' . $this->attempt->quiz->title);
        });
    }
}
