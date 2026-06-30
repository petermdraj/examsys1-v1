@auth
    @if(($isAssigned ?? false) || ($isEnrolled ?? false))
        @if($canAttempt)
            <form method="POST" action="{{ route('attempt.start', $quiz->slug) }}" class="js-start-exam-form {{ $formClass ?? '' }}">@csrf
                <button type="submit" class="js-start-exam-btn {{ $buttonClass ?? 'btn btn-primary' }}">{{ $label }}</button>
            </form>
        @else
            <p class="muted js-start-blocked-msg {{ $blockedClass ?? '' }}" style="text-align:center;font-size:14px;">
                @if($attemptBlockReason === 'before_start')
                    {{ __('quiz.attempt_blocked_before_start') }}
                @elseif($attemptBlockReason === 'after_end')
                    {{ __('quiz.attempt_blocked_after_end') }}
                @else
                    {{ __('quiz.attempt_not_available') }}
                @endif
            </p>
            <form method="POST" action="{{ route('attempt.start', $quiz->slug) }}" class="js-start-exam-form {{ $formClass ?? '' }}" style="display:none;">@csrf
                <button type="submit" class="js-start-exam-btn {{ $buttonClass ?? 'btn btn-primary' }}" disabled>{{ $label }}</button>
            </form>
        @endif
    @elseif(auth()->user()->role === 'student')
        <p class="muted {{ $blockedClass ?? '' }}" style="text-align:center;font-size:14px;">{{ __('quiz.not_assigned') }}</p>
    @endif
@else
    <a href="{{ route('login') }}" class="{{ $buttonClass ?? 'btn btn-primary' }}">{{ __('quiz.login_to_start') }}</a>
@endauth
