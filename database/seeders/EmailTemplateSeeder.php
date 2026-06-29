<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EmailTemplateSeeder extends Seeder
{
    private const REMOVED_KEYS = [
        'purchase_receipt',
        'subscription_renewal_reminder',
        'subscription_expired',
        'subscription_grace_ended',
        'payout_paid',
        'payout_rejected',
    ];

    public function run(): void
    {
        EmailTemplate::whereIn('key', self::REMOVED_KEYS)->delete();

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
}
