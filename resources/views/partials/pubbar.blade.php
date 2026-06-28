<div class="pubbar">
  <div class="pubbar-links">
    <a href="{{ route('home') }}">{{ __('common.pubbar_explore') }}</a>
    <a href="{{ route('quizzes.index') }}">{{ __('common.pubbar_quizzes') }}</a>
    <a href="{{ route('categories.index') }}">{{ __('common.pubbar_categories') }}</a>
    <a href="{{ route('for-creators') }}">{{ __('common.pubbar_for_creators') }}</a>
  </div>
  <a href="{{ route('quizzes.index') }}" class="pubbar-search">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4-4"/></svg>
    {{ __('common.pubbar_search') }}
  </a>
  <div class="pubbar-actions"></div>
</div>
