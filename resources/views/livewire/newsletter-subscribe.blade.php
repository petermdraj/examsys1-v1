<style>
.ns-wrap{position:relative}
.ns-duplicate{background:rgba(255,255,255,.12)}
.ns-form{position:relative}
.ns-error{position:absolute;bottom:-20px;left:0;font-size:11px;color:#fca5a5}
.ns-loading{opacity:.6}
</style>

<div class="ns-wrap">
  @if($status === 'success')
    <div class="footer-news-success">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg>
      {{ __('common.newsletter_subscribed') }}
    </div>
  @elseif($status === 'duplicate')
    <div class="footer-news-success ns-duplicate">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="9"/><path d="M12 8v4M12 16h.01"/></svg>
      {{ __('common.newsletter_already_subscribed') }}
    </div>
  @else
    <form wire:submit="subscribe" class="footer-news ns-form">
      <input
        type="email"
        wire:model="email"
        placeholder="{{ __('common.newsletter_email_placeholder') }}"
        autocomplete="email"
      >
      @error('email')
        <span class="ns-error">{{ $message }}</span>
      @enderror
      <button type="submit" wire:loading.attr="disabled">
        <span wire:loading.remove>{{ __('common.newsletter_subscribe_btn') }}</span>
        <span wire:loading class="ns-loading">…</span>
      </button>
    </form>
  @endif
</div>
