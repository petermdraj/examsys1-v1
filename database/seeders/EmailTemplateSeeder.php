<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EmailTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'key'  => 'attempt_result',
                'name' => 'Quiz Result',
                'subject' => 'Your quiz result — {{quiz_title}}',
                'variables' => [
                    ['name' => 'user_name',    'description' => "Recipient's full name"],
                    ['name' => 'quiz_title',   'description' => 'Title of the quiz'],
                    ['name' => 'score',        'description' => 'Score achieved'],
                    ['name' => 'total_marks',  'description' => 'Total possible marks'],
                    ['name' => 'percentage',   'description' => 'Score percentage'],
                    ['name' => 'result_label', 'description' => 'PASSED or FAILED'],
                    ['name' => 'time_taken',   'description' => 'Time taken (HH:MM:SS)'],
                    ['name' => 'result_url',   'description' => 'Link to full result page'],
                ],
                'body' => $this->attemptResultBody(),
            ],
            [
                'key'  => 'purchase_receipt',
                'name' => 'Purchase Receipt',
                'subject' => 'Purchase confirmed — {{quiz_title}}',
                'variables' => [
                    ['name' => 'user_name',   'description' => "Recipient's full name"],
                    ['name' => 'quiz_title',  'description' => 'Title of the purchased quiz'],
                    ['name' => 'order_id',    'description' => 'Short order reference'],
                    ['name' => 'amount',      'description' => 'Amount paid (formatted)'],
                    ['name' => 'paid_at',     'description' => 'Payment date/time'],
                    ['name' => 'quiz_url',    'description' => 'Link to start the quiz'],
                ],
                'body' => $this->purchaseReceiptBody(),
            ],
            [
                'key'  => 'weekly_digest',
                'name' => 'Weekly Digest',
                'subject' => 'New quizzes this week on {{app_name}} 🎯',
                'variables' => [
                    ['name' => 'user_name',   'description' => "Recipient's full name"],
                    ['name' => 'app_name',    'description' => 'Platform name'],
                    ['name' => 'week_range',  'description' => 'e.g. Jun 9 – Jun 16, 2026'],
                    ['name' => 'quiz_list',   'description' => 'Auto-generated HTML list of new quizzes'],
                    ['name' => 'browse_url',  'description' => 'Link to quiz discovery page'],
                    ['name' => 'prefs_url',   'description' => 'Link to notification preferences'],
                ],
                'body' => $this->weeklyDigestBody(),
            ],
            [
                'key'  => 'subscription_renewal_reminder',
                'name' => 'Subscription Renewal Reminder',
                'subject' => 'Your {{plan_name}} plan expires in {{days_left}} days',
                'variables' => [
                    ['name' => 'user_name',     'description' => "Recipient's full name"],
                    ['name' => 'plan_name',     'description' => 'Subscription plan name'],
                    ['name' => 'days_left',     'description' => 'Days until subscription expires'],
                    ['name' => 'expiry_date',   'description' => 'Subscription expiry date'],
                    ['name' => 'pricing_url',   'description' => 'Link to the pricing / renewal page'],
                    ['name' => 'app_name',      'description' => 'Platform name'],
                ],
                'body' => $this->subscriptionRenewalReminderBody(),
            ],
            [
                'key'  => 'subscription_expired',
                'name' => 'Subscription Expired',
                'subject' => 'Your {{plan_name}} subscription has expired',
                'variables' => [
                    ['name' => 'user_name',        'description' => "Recipient's full name"],
                    ['name' => 'plan_name',         'description' => 'Subscription plan name'],
                    ['name' => 'grace_period_days', 'description' => 'Grace period in days'],
                    ['name' => 'grace_ends_date',   'description' => 'Date when grace period ends'],
                    ['name' => 'pricing_url',       'description' => 'Link to the pricing / renewal page'],
                    ['name' => 'app_name',          'description' => 'Platform name'],
                ],
                'body' => $this->subscriptionExpiredBody(),
            ],
            [
                'key'  => 'subscription_grace_ended',
                'name' => 'Grace Period Ended — Premium Features Disabled',
                'subject' => 'Your premium features have been paused on {{app_name}}',
                'variables' => [
                    ['name' => 'user_name',   'description' => "Recipient's full name"],
                    ['name' => 'app_name',    'description' => 'Platform name'],
                    ['name' => 'pricing_url', 'description' => 'Link to the pricing / renewal page'],
                ],
                'body' => $this->subscriptionGraceEndedBody(),
            ],
            [
                'key'  => 'payout_paid',
                'name' => 'Payout Processed',
                'subject' => 'Your payout of {{amount}} has been sent!',
                'variables' => [
                    ['name' => 'user_name',         'description' => "Creator's full name"],
                    ['name' => 'amount',             'description' => 'Payout amount (formatted, e.g. ₹500.00)'],
                    ['name' => 'gateway',            'description' => 'Payment method / UPI ID used'],
                    ['name' => 'gateway_reference',  'description' => 'UTR / transaction reference number'],
                    ['name' => 'processed_at',       'description' => 'Date and time payment was processed'],
                    ['name' => 'admin_note',         'description' => 'Optional note from the admin'],
                    ['name' => 'earnings_url',       'description' => 'Link to creator earnings page'],
                    ['name' => 'app_name',           'description' => 'Platform name'],
                ],
                'body' => $this->payoutPaidBody(),
            ],
            [
                'key'  => 'payout_rejected',
                'name' => 'Payout Rejected',
                'subject' => 'Your payout request of {{amount}} was not approved',
                'variables' => [
                    ['name' => 'user_name',   'description' => "Creator's full name"],
                    ['name' => 'amount',      'description' => 'Payout amount (formatted)'],
                    ['name' => 'admin_note',  'description' => 'Reason for rejection from the admin'],
                    ['name' => 'earnings_url','description' => 'Link to creator earnings page'],
                    ['name' => 'app_name',    'description' => 'Platform name'],
                ],
                'body' => $this->payoutRejectedBody(),
            ],
        ];

        foreach ($templates as $tpl) {
            EmailTemplate::updateOrCreate(
                ['key' => $tpl['key']],
                array_merge($tpl, ['id' => Str::uuid()])
            );
        }
    }

    private function wrap(string $headerBg, string $headerContent, string $bodyContent): string
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>
*{box-sizing:border-box;margin:0;padding:0;}
body{font-family:'Helvetica Neue',Arial,sans-serif;background:#F3F0FF;padding:32px 16px;color:#1E1B4B;}
.wrap{max-width:580px;margin:0 auto;}
.card{background:#fff;border-radius:16px;overflow:hidden;}
.header{background:{$headerBg};padding:36px 32px;text-align:center;}
.header h1{color:#fff;font-size:22px;font-weight:700;line-height:1.3;}
.header p{color:rgba(255,255,255,.75);font-size:14px;margin-top:6px;}
.body{padding:32px;}
p{font-size:15px;line-height:1.6;margin-bottom:16px;}
.stat{display:flex;justify-content:space-between;padding:12px 0;border-bottom:1px solid #eee;font-size:14px;}
.cta{display:inline-block;margin-top:24px;padding:14px 36px;background:{$headerBg};color:#fff;text-decoration:none;border-radius:10px;font-weight:700;font-size:15px;}
.footer{background:#F9F8FF;padding:20px 32px;text-align:center;font-size:12px;color:#9CA3AF;}
</style>
</head>
<body>
<div class="wrap">
<div class="card">
<div class="header">{$headerContent}</div>
<div class="body">{$bodyContent}</div>
<div class="footer">© {{app_name}}. All rights reserved.</div>
</div>
</div>
</body>
</html>
HTML;
    }

    private function attemptResultBody(): string
    {
        return $this->wrap(
            '#6C2E63',
            '<h1>Quiz Completed</h1><p>{{quiz_title}}</p>',
            <<<HTML
<p>Hi {{user_name}},</p>
<p>Here are your results for <strong>{{quiz_title}}</strong>:</p>
<div class="stat"><span>Score</span><strong>{{score}} / {{total_marks}}</strong></div>
<div class="stat"><span>Percentage</span><strong>{{percentage}}%</strong></div>
<div class="stat"><span>Result</span><strong>{{result_label}}</strong></div>
<div class="stat"><span>Time Taken</span><strong>{{time_taken}}</strong></div>
<div style="text-align:center;"><a href="{{result_url}}" class="cta">View Full Results</a></div>
HTML
        );
    }

    private function purchaseReceiptBody(): string
    {
        return $this->wrap(
            '#6C2E63',
            '<h1>Purchase Confirmed!</h1><p>Order #{{order_id}}</p>',
            <<<HTML
<p>Hi {{user_name}},</p>
<p>Thank you for your purchase. You now have access to:</p>
<div class="stat"><span>Quiz</span><strong>{{quiz_title}}</strong></div>
<div class="stat"><span>Amount Paid</span><strong>{{amount}}</strong></div>
<div class="stat"><span>Date</span><strong>{{paid_at}}</strong></div>
<div style="text-align:center;"><a href="{{quiz_url}}" class="cta">Start Quiz Now</a></div>
HTML
        );
    }

    private function weeklyDigestBody(): string
    {
        return $this->wrap(
            '#6C2E63',
            '<h1>New quizzes this week 🎯</h1><p>{{week_range}}</p>',
            <<<HTML
<p>Hi {{user_name}},</p>
<p>Here's what's new on {{app_name}} this week. Jump in and test your knowledge!</p>
{{quiz_list}}
<div style="text-align:center;margin-top:24px;">
  <a href="{{browse_url}}" class="cta">Browse All Quizzes</a>
</div>
<p style="font-size:12px;color:#9CA3AF;margin-top:24px;text-align:center;">
  <a href="{{prefs_url}}" style="color:#6C2E63;">Manage notification preferences</a>
</p>
HTML
        );
    }

    private function subscriptionRenewalReminderBody(): string
    {
        return $this->wrap(
            '#E0A431',
            '<h1>Your plan expires soon</h1><p>{{plan_name}}</p>',
            <<<HTML
<p>Hi {{user_name}},</p>
<p>This is a friendly reminder that your <strong>{{plan_name}}</strong> subscription expires on <strong>{{expiry_date}}</strong> — that's <strong>{{days_left}} days</strong> from now.</p>
<p>Renewing keeps your AI generation tokens, ability to sell paid quizzes, and all premium features without interruption.</p>
<div style="text-align:center;"><a href="{{pricing_url}}" class="cta">Renew My Plan</a></div>
<p style="font-size:13px;color:#9CA3AF;margin-top:16px;">If you choose not to renew, your account will enter a short grace period before premium features are paused.</p>
HTML
        );
    }

    private function subscriptionExpiredBody(): string
    {
        return $this->wrap(
            '#EF4444',
            '<h1>Subscription Expired</h1><p>{{plan_name}}</p>',
            <<<HTML
<p>Hi {{user_name}},</p>
<p>Your <strong>{{plan_name}}</strong> subscription has expired. You are currently in a <strong>{{grace_period_days}}-day grace period</strong> — your premium features remain active until <strong>{{grace_ends_date}}</strong>.</p>
<p>After the grace period, AI generation credits will be reset to zero and selling paid quizzes will be disabled until you renew.</p>
<div style="text-align:center;"><a href="{{pricing_url}}" class="cta">Renew Now</a></div>
HTML
        );
    }

    private function subscriptionGraceEndedBody(): string
    {
        return $this->wrap(
            '#6B7280',
            '<h1>Premium Features Paused</h1><p>{{app_name}}</p>',
            <<<HTML
<p>Hi {{user_name}},</p>
<p>Your grace period has ended. Your premium features on <strong>{{app_name}}</strong> have been paused:</p>
<ul style="margin:16px 0;padding-left:20px;font-size:15px;line-height:1.8;">
  <li>AI generation credits reset to 0</li>
  <li>Paid quiz publishing disabled</li>
</ul>
<p>Your existing quizzes and earnings are safe — they're not going anywhere. Renewing your plan restores everything instantly.</p>
<div style="text-align:center;"><a href="{{pricing_url}}" class="cta">Reactivate My Plan</a></div>
HTML
        );
    }

    private function payoutPaidBody(): string
    {
        return $this->wrap(
            '#16a34a',
            '<h1>Payment Sent! 🎉</h1><p>{{amount}} has been transferred to you</p>',
            <<<HTML
<p>Hi {{user_name}},</p>
<p>Great news — your payout has been processed and the money is on its way!</p>
<div class="stat"><span>Amount</span><strong>{{amount}}</strong></div>
<div class="stat"><span>Sent to</span><strong>{{gateway}}</strong></div>
<div class="stat"><span>Reference / UTR</span><strong>{{gateway_reference}}</strong></div>
<div class="stat"><span>Processed on</span><strong>{{processed_at}}</strong></div>
{{#admin_note}}<div style="margin-top:16px;padding:10px 14px;background:#f0fdf4;border-left:3px solid #16a34a;border-radius:8px;font-size:13px;"><strong>Note from admin:</strong> {{admin_note}}</div>{{/admin_note}}
<div style="text-align:center;"><a href="{{earnings_url}}" class="cta">View Earnings</a></div>
<p style="font-size:13px;color:#9CA3AF;margin-top:16px;">Bank transfers may take 1–2 business days to reflect depending on your bank.</p>
HTML
        );
    }

    private function payoutRejectedBody(): string
    {
        return $this->wrap(
            '#dc2626',
            '<h1>Payout Not Approved</h1><p>{{amount}} · Review required</p>',
            <<<HTML
<p>Hi {{user_name}},</p>
<p>Unfortunately your payout request of <strong>{{amount}}</strong> could not be processed at this time.</p>
<div style="margin:16px 0;padding:12px 16px;background:#fef2f2;border-left:3px solid #dc2626;border-radius:8px;font-size:14px;">
  <strong>Reason:</strong> {{admin_note}}
</div>
<p>Your balance has not been affected. You can submit a new payout request once the issue is resolved.</p>
<div style="text-align:center;"><a href="{{earnings_url}}" class="cta">Go to Earnings</a></div>
<p style="font-size:13px;color:#9CA3AF;margin-top:16px;">If you think this is a mistake, please contact support.</p>
HTML
        );
    }
}
