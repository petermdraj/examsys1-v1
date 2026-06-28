<?php

namespace App\Console\Commands;

use App\Models\EmailTemplate;
use App\Models\Subscription;
use App\Models\User;
use App\Settings\PlatformSettings;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ProcessSubscriptionExpiry extends Command
{
    protected $signature   = 'quizora:process-subscription-expiry';
    protected $description = 'Send renewal reminders, mark expired subscriptions, and enforce grace period.';

    public function handle(PlatformSettings $settings): void
    {
        $reminderDays = max(1, (int) $settings->subscription_renewal_reminder_days);
        $graceDays    = max(0, (int) $settings->subscription_grace_period_days);
        $pricingUrl   = url('/pricing');
        $appName      = $settings->app_name;

        // ── 1. Send renewal reminders ─────────────────────────────────────────
        $reminderWindow = now()->addDays($reminderDays);

        Subscription::with(['user', 'plan'])
            ->where('status', 'active')
            ->whereDate('current_period_end', $reminderWindow->toDateString())
            ->whereNull('reminder_sent_at')
            ->chunkById(100, function ($subs) use ($pricingUrl, $appName) {
                foreach ($subs as $sub) {
                    $this->sendEmail('subscription_renewal_reminder', $sub->user, [
                        'user_name'   => $sub->user->name,
                        'plan_name'   => $sub->plan->name ?? 'your plan',
                        'days_left'   => now()->diffInDays($sub->current_period_end),
                        'expiry_date' => $sub->current_period_end->format('d M Y'),
                        'pricing_url' => $pricingUrl,
                        'app_name'    => $appName,
                    ]);

                    $sub->update(['reminder_sent_at' => now()]);
                }
            });

        // ── 2. Mark active subscriptions as expired ───────────────────────────
        Subscription::with(['user', 'plan'])
            ->where('status', 'active')
            ->where('current_period_end', '<', now())
            ->chunkById(100, function ($subs) use ($graceDays, $pricingUrl, $appName) {
                foreach ($subs as $sub) {
                    $sub->update(['status' => 'expired']);

                    $graceEndsDate = $sub->current_period_end->addDays($graceDays)->format('d M Y');

                    $this->sendEmail('subscription_expired', $sub->user, [
                        'user_name'          => $sub->user->name,
                        'plan_name'          => $sub->plan->name ?? 'your plan',
                        'grace_period_days'  => $graceDays,
                        'grace_ends_date'    => $graceEndsDate,
                        'pricing_url'        => $pricingUrl,
                        'app_name'           => $appName,
                    ]);
                }
            });

        // ── 3. End grace period — disable premium features ────────────────────
        $graceCutoff = now()->subDays($graceDays);

        Subscription::with('user')
            ->where('status', 'expired')
            ->where('current_period_end', '<', $graceCutoff)
            ->chunkById(100, function ($subs) use ($pricingUrl, $appName) {
                foreach ($subs as $sub) {
                    $sub->update(['status' => 'cancelled']);

                    // Reset AI credits to zero; they can earn new ones on re-subscribe
                    User::where('id', $sub->user_id)->update(['ai_credits_free_remaining' => 0]);

                    $this->sendEmail('subscription_grace_ended', $sub->user, [
                        'user_name'   => $sub->user->name,
                        'app_name'    => $appName,
                        'pricing_url' => $pricingUrl,
                    ]);
                }
            });

        $this->info('Subscription expiry processing complete.');
    }

    private function sendEmail(string $key, User $user, array $data): void
    {
        $template = EmailTemplate::findByKey($key);

        if (! $template) {
            Log::warning("Email template '{$key}' not found or inactive.");
            return;
        }

        if (! filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        try {
            Mail::html($template->renderRaw($data), function ($message) use ($template, $user, $data) {
                $message
                    ->to($user->email, $user->name)
                    ->subject($template->renderSubject($data));
            });
        } catch (\Throwable $e) {
            Log::error("Failed to send subscription email '{$key}' to {$user->email}: " . $e->getMessage());
        }
    }
}
