<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>Quiz Result</title>
<style>
body { font-family: Arial, sans-serif; background: #f5f5f5; margin: 0; padding: 20px; }
.card { max-width: 560px; margin: 0 auto; background: #fff; border-radius: 12px; overflow: hidden; }
.header { background: #6C2E63; color: #fff; padding: 32px; text-align: center; }
.header h1 { margin: 0; font-size: 24px; }
.body { padding: 32px; }
.stat { display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #eee; }
.cta { display: block; margin: 24px auto 0; padding: 14px 32px; background: #6C2E63; color: #fff; text-decoration: none; border-radius: 8px; text-align: center; font-weight: bold; }
.footer { padding: 16px 32px; background: #f9f9f9; font-size: 12px; color: #999; text-align: center; }
</style>
</head>
<body>
<div class="card">
    <div class="header">
        <h1>{{ $attempt->is_passed ? __('emails.result_passed_heading') : __('emails.result_completed_heading') }}</h1>
        <p>{{ $quiz->title }}</p>
    </div>
    <div class="body">
        <p>{{ __('emails.hi_name', ['name' => $user->name]) }},</p>
        <p>{{ __('emails.result_intro') }}</p>
        <div class="stat"><span>{{ __('emails.result_label_score') }}</span><strong>{{ $attempt->score }} / {{ $attempt->total_marks }}</strong></div>
        <div class="stat"><span>{{ __('emails.result_label_percentage') }}</span><strong>{{ number_format($attempt->percentage, 1) }}%</strong></div>
        <div class="stat"><span>{{ __('emails.result_label_result') }}</span><strong style="color: {{ $attempt->is_passed ? '#2E9E68' : '#D5443F' }}">{{ $attempt->is_passed ? __('emails.result_status_passed') : __('emails.result_status_failed') }}</strong></div>
        <div class="stat"><span>{{ __('emails.result_label_time_taken') }}</span><strong>{{ gmdate('H:i:s', $attempt->time_taken_seconds ?? 0) }}</strong></div>
        <a href="{{ url('/attempt/' . $attempt->id . '/result') }}" class="cta">{{ __('emails.result_cta') }}</a>
    </div>
    <div class="footer">© {{ date('Y') }} {{ __('common.app_name') }}. {{ __('emails.footer_rights') }}</div>
</div>
</body>
</html>
