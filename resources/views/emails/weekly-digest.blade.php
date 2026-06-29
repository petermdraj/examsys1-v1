<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>{{ __('emails.digest_section_label') }}</title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: 'Helvetica Neue', Arial, sans-serif; background: #F3F0FF; padding: 32px 16px; color: #1E1B4B; }
.wrap { max-width: 580px; margin: 0 auto; }
.card { background: #fff; border-radius: 16px; overflow: hidden; }
.header { background: #6C2E63; padding: 36px 32px; text-align: center; }
.header h1 { color: #fff; font-size: 22px; font-weight: 700; line-height: 1.3; }
.header p { color: rgba(255,255,255,.75); font-size: 14px; margin-top: 6px; }
.body { padding: 32px; }
.greeting { font-size: 15px; margin-bottom: 20px; line-height: 1.5; }
.section-label { font-size: 11px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: #6C2E63; margin-bottom: 16px; }
.quiz-list { display: flex; flex-direction: column; gap: 12px; margin-bottom: 28px; }
.quiz-item { border: 1.5px solid #E9E5FF; border-radius: 12px; padding: 16px; display: flex; gap: 14px; align-items: flex-start; }
.quiz-cover { width: 56px; height: 56px; border-radius: 8px; object-fit: cover; flex: none; background: #6C2E63; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 22px; font-weight: 700; }
.quiz-info { flex: 1; min-width: 0; }
.quiz-title { font-size: 14px; font-weight: 700; color: #1E1B4B; line-height: 1.35; margin-bottom: 4px; }
.quiz-meta { font-size: 12px; color: #6B7280; line-height: 1.4; }
.quiz-badge { display: inline-block; font-size: 11px; font-weight: 600; padding: 2px 8px; border-radius: 20px; margin-top: 6px; }
.badge-free { background: #D1FAE5; color: #065F46; }
.badge-paid { background: #FEF3C7; color: #92400E; }
.cta-wrap { text-align: center; margin-bottom: 28px; }
.cta { display: inline-block; background: #6C2E63; color: #fff; text-decoration: none; font-weight: 700; font-size: 15px; padding: 14px 36px; border-radius: 10px; }
.divider { border: none; border-top: 1px solid #F0EEFF; margin: 24px 0; }
.unsubscribe { font-size: 12px; color: #9CA3AF; text-align: center; line-height: 1.6; }
.unsubscribe a { color: #6C2E63; text-decoration: underline; }
.footer { background: #F9F8FF; padding: 20px 32px; text-align: center; font-size: 12px; color: #9CA3AF; }
</style>
</head>
<body>
<div class="wrap">
  <div class="card">

    <div class="header">
      <h1>{{ __('emails.digest_heading') }}</h1>
      <p>{{ now()->subWeek()->format('M j') }} – {{ now()->format('M j, Y') }}</p>
    </div>

    <div class="body">
      <p class="greeting">
        {{ __('emails.hi_name', ['name' => $user->name]) }},<br><br>
        {{ __('emails.digest_intro') }}
      </p>

      <div class="section-label">{{ __('emails.digest_section_label') }}</div>

      <div class="quiz-list">
        @foreach($quizzes as $quiz)
        <div class="quiz-item">
          @if($quiz->cover_image)
            <img class="quiz-cover" src="{{ url(Storage::url($quiz->cover_image)) }}" alt="{{ $quiz->title }}">
          @else
            <div class="quiz-cover">{{ strtoupper(substr($quiz->title, 0, 1)) }}</div>
          @endif
          <div class="quiz-info">
            <div class="quiz-title">{{ $quiz->title }}</div>
            <div class="quiz-meta">
              {{ $quiz->category?->name }}
              @if($quiz->total_questions) · {{ $quiz->total_questions }} questions @endif
              @if($quiz->duration_minutes) · {{ $quiz->duration_minutes }} min @endif
            </div>
            <span class="quiz-badge badge-free">{{ __('quiz.card_free') }}</span>
          </div>
        </div>
        @endforeach
      </div>

      <div class="cta-wrap">
        <a href="{{ url('/quizzes') }}" class="cta">{{ __('emails.digest_cta') }}</a>
      </div>

      <hr class="divider">

      <p class="unsubscribe">
        {{ __('emails.digest_unsubscribe') }}<br>
        <a href="{{ url('/profile') }}">{{ __('emails.digest_manage_prefs') }}</a>
      </p>
    </div>

    <div class="footer">
      © {{ date('Y') }} {{ __('common.app_name') }}. {{ __('emails.footer_rights') }}
    </div>

  </div>
</div>
</body>
</html>
