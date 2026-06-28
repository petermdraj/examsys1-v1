<?php

namespace App\Jobs;

use App\Models\Attempt;
use App\Services\Exam\CertificateService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;

class GenerateCertificateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public Attempt $attempt) {}

    public function handle(CertificateService $service): void
    {
        // Queue workers bypass web middleware — set user locale for PDF generation.
        $locale = $this->attempt->user->preferred_locale ?? config('app.locale', 'en');
        $available = array_keys(config('app.available_locales', ['en' => 'English']));
        if (!in_array($locale, $available)) {
            $locale = config('app.locale', 'en');
        }
        App::setLocale($locale);
        Carbon::setLocale($locale);

        $service->generate($this->attempt);

        // Restore default locale after PDF generation
        App::setLocale(config('app.locale', 'en'));
        Carbon::setLocale(config('app.locale', 'en'));
    }
}
