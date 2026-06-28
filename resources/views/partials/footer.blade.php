@php
    $footerColumns = is_array($platformSettings->footer_columns) ? $platformSettings->footer_columns : (json_decode($platformSettings->footer_columns ?? '[]', true) ?: []);
@endphp

<footer class="site-footer">
  <div class="footer-inner">
    <div class="footer-top">
      {{-- Brand + tagline + newsletter --}}
      <div>
        <div class="brand" style="margin-bottom:12px;">
          @if($platformSettings->app_logo)
            <img src="{{ Storage::url($platformSettings->app_logo) }}" alt="{{ $platformSettings->app_name }}" style="height:36px;object-fit:contain;">
          @else
            <div class="brand-mark">{{ strtoupper(substr($platformSettings->app_name, 0, 1)) }}</div>
            <div class="brand-name" style="color:var(--on-primary);">{{ $platformSettings->app_name }}</div>
          @endif
        </div>
        @if($platformSettings->footer_tagline)
          <p class="footer-tag">{{ $platformSettings->footer_tagline }}</p>
        @endif
        @if($platformSettings->footer_show_newsletter)
        @livewire('newsletter-subscribe', ['source' => 'footer'])
        @endif
      </div>

      {{-- Dynamic menu columns --}}
      @foreach($footerColumns as $col)
      <div class="footer-col">
        <h4>{{ $col['title'] ?? '' }}</h4>
        @foreach($col['items'] ?? [] as $item)
          <a href="{{ $item['url'] ?? '#' }}">{{ $item['label'] ?? '' }}</a>
        @endforeach
      </div>
      @endforeach
    </div>

    <div class="footer-bottom">
      <div>© {{ date('Y') }} {{ $platformSettings->app_name }}. {{ __('common.footer_all_rights') }}</div>
      <div class="footer-social">
        @if($platformSettings->social_facebook)
        <a href="{{ $platformSettings->social_facebook }}" title="Facebook" target="_blank" rel="noopener"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/></svg></a>
        @endif
        @if($platformSettings->social_twitter)
        <a href="{{ $platformSettings->social_twitter }}" title="Twitter / X" target="_blank" rel="noopener"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M22 5.8a8 8 0 01-2.3.6 4 4 0 001.8-2.2 8 8 0 01-2.5 1A4 4 0 0012 8.8a11 11 0 01-8-4 4 4 0 001.2 5.3A4 4 0 013 9.5a4 4 0 003.2 4 4 4 0 01-1.8.1 4 4 0 003.7 2.8A8 8 0 012 18.6a11 11 0 006 1.8c7.2 0 11.2-6 11.2-11.2v-.5A8 8 0 0022 5.8z"/></svg></a>
        @endif
        @if($platformSettings->social_instagram)
        <a href="{{ $platformSettings->social_instagram }}" title="Instagram" target="_blank" rel="noopener"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="5"/><path d="M16 11.37A4 4 0 1112.63 8 4 4 0 0116 11.37z"/><circle cx="17.5" cy="6.5" r="1.5" fill="currentColor" stroke="none"/></svg></a>
        @endif
        @if($platformSettings->social_youtube)
        <a href="{{ $platformSettings->social_youtube }}" title="YouTube" target="_blank" rel="noopener"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M22.54 6.42a2.78 2.78 0 00-1.95-2C18.88 4 12 4 12 4s-6.88 0-8.59.46a2.78 2.78 0 00-1.95 2A29 29 0 001 12a29 29 0 00.46 5.58A2.78 2.78 0 003.41 19.6C5.12 20 12 20 12 20s6.88 0 8.59-.46a2.78 2.78 0 001.95-1.95A29 29 0 0023 12a29 29 0 00-.46-5.58zM9.75 15.02V8.98L15.5 12l-5.75 3.02z"/></svg></a>
        @endif
        @if($platformSettings->social_linkedin)
        <a href="{{ $platformSettings->social_linkedin }}" title="LinkedIn" target="_blank" rel="noopener"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M16 8a6 6 0 016 6v7h-4v-7a2 2 0 00-2-2 2 2 0 00-2 2v7h-4v-7a6 6 0 016-6zM2 9h4v12H2z"/><circle cx="4" cy="4" r="2"/></svg></a>
        @endif
      </div>
    </div>
  </div>
</footer>
