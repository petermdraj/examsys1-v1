<button
    wire:click="toggle"
    wire:loading.attr="disabled"
    title="{{ $isFavourited ? 'Remove from favourites' : 'Save to favourites' }}"
    class="fav-btn {{ $isFavourited ? 'fav-btn--active' : '' }}"
    style="
        display:inline-flex;align-items:center;justify-content:center;
        width:36px;height:36px;border-radius:50%;border:none;cursor:pointer;
        background:{{ $isFavourited ? 'rgba(239,68,68,.12)' : 'rgba(0,0,0,.06)' }};
        transition:background .15s,transform .1s;
        flex-shrink:0;
    "
    onmouseenter="this.style.background='{{ $isFavourited ? 'rgba(239,68,68,.2)' : 'rgba(0,0,0,.12)' }}'"
    onmouseleave="this.style.background='{{ $isFavourited ? 'rgba(239,68,68,.12)' : 'rgba(0,0,0,.06)' }}'"
>
    <svg width="17" height="17" viewBox="0 0 24 24"
         fill="{{ $isFavourited ? '#ef4444' : 'none' }}"
         stroke="{{ $isFavourited ? '#ef4444' : 'currentColor' }}"
         stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
    </svg>
</button>
