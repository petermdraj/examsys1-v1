<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use Carbon\Carbon;

class EmailVerificationOtp extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly User   $user,
        public readonly string $otp,
        public readonly string $appName,
    ) {
        // Set locale in constructor — Mailables are resolved in the queue worker
        // which bypasses web middleware, so locale must be set explicitly here.
        $locale = $user->preferred_locale ?? config('app.locale', 'en');
        $available = array_keys(config('app.available_locales', ['en' => 'English']));
        if (!in_array($locale, $available)) {
            $locale = config('app.locale', 'en');
        }
        App::setLocale($locale);
        Carbon::setLocale($locale);
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: __('emails.otp_subject'));
    }

    public function content(): Content
    {
        return new Content(view: 'emails.otp-verify');
    }
}
