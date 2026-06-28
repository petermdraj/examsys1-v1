@php
    $settings        = rescue(fn () => app(\App\Settings\PlatformSettings::class), null, false);
    $enabledLocales  = $settings?->enabled_locales      ?? ['en','hi','de','nl','da','no','sv','fr','ja'];
    $hiddenLocales   = $settings?->hidden_locales        ?? [];
    $extraLocales    = $settings?->extra_locales         ?? [];
    $extraFlags      = $settings?->extra_locale_flags    ?? [];
    $rtlLocales      = $settings?->rtl_locales           ?? [];
    $allLocales      = array_merge(config('app.available_locales', ['en' => 'English']), $extraLocales);
    foreach ($hiddenLocales as $_h) { unset($allLocales[$_h]); }
    $current         = app()->getLocale();

    $visibleLocales  = array_filter($allLocales, fn ($code) => in_array($code, $enabledLocales), ARRAY_FILTER_USE_KEY);
    if (count($visibleLocales) <= 1) return;

    $builtinFlags = [
        'en' => '🇬🇧', 'hi' => '🇮🇳', 'de' => '🇩🇪', 'fr' => '🇫🇷',
        'nl' => '🇳🇱', 'da' => '🇩🇰', 'no' => '🇳🇴', 'sv' => '🇸🇪', 'ja' => '🇯🇵',
    ];
    $flags = array_merge($builtinFlags, $extraFlags);

    $currentName = $allLocales[$current] ?? strtoupper($current);
    $currentFlag = $flags[$current] ?? '🌐';
    $uid = 'ls'.uniqid();
@endphp

<div class="relative me-1" style="position:relative;">
    <button
        id="{{ $uid }}-btn"
        onclick="fiLsToggle('{{ $uid }}')"
        type="button"
        class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-sm font-medium
               text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-white/10
               focus:outline-none transition-colors duration-150"
        title="Switch language"
    >
        <span class="text-base leading-none">{{ $currentFlag }}</span>
        <span class="hidden sm:inline max-w-[72px] truncate">{{ $currentName }}</span>
        <svg id="{{ $uid }}-chevron" class="h-3 w-3 opacity-50 transition-transform duration-150 flex-shrink-0"
             fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    <form method="POST" action="{{ route('language.switch') }}" id="{{ $uid }}-form" style="display:none;">
        @csrf
        <input type="hidden" name="locale" id="{{ $uid }}-input" value="{{ $current }}">
    </form>
</div>

<div id="{{ $uid }}-drop"
    style="display:none;position:fixed;z-index:99999;min-width:185px;
           background:#fff;border:1px solid #e5e7eb;border-radius:14px;
           box-shadow:0 12px 40px rgba(0,0,0,.18);overflow:hidden;overflow-y:auto;max-height:400px;">
    <div style="padding:4px 0;">
        @foreach($visibleLocales as $code => $name)
        @php
            $isActive = $current === $code;
            $isRtl    = in_array($code, $rtlLocales);
        @endphp
        <button
            type="button"
            onclick="document.getElementById('{{ $uid }}-input').value='{{ $code }}';document.getElementById('{{ $uid }}-form').submit();"
            style="width:100%;display:flex;align-items:center;gap:10px;padding:9px 15px;border:none;
                   background:{{ $isActive ? '#f0f0ff' : 'transparent' }};cursor:pointer;
                   font-size:13.5px;font-weight:{{ $isActive ? '600' : '400' }};
                   color:{{ $isActive ? '#4f46e5' : '#374151' }};text-align:left;transition:.1s;"
            onmouseenter="this.style.background='#f5f5ff'"
            onmouseleave="this.style.background='{{ $isActive ? '#f0f0ff' : 'transparent' }}'"
            dir="{{ $isRtl ? 'rtl' : 'ltr' }}"
        >
            <span style="font-size:18px;line-height:1;width:22px;text-align:center;flex-shrink:0;">{{ $flags[$code] ?? '🌐' }}</span>
            <span style="flex:1;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $name }}</span>
            @if($isRtl)
                <span style="font-size:.6rem;background:#fef3c7;color:#92400e;padding:.1rem .3rem;border-radius:3px;font-weight:700;flex-shrink:0;">RTL</span>
            @endif
            @if($isActive)
            <svg style="width:14px;height:14px;color:#6366f1;flex-shrink:0;" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
            </svg>
            @endif
        </button>
        @endforeach
    </div>
</div>

<script>
(function(){
    var uid = '{{ $uid }}';

    window.fiLsToggle = window.fiLsToggle || function(id) {
        document.querySelectorAll('[id$="-drop"]').forEach(function(el) {
            if (el.id !== id + '-drop') {
                el.style.display = 'none';
                var c = document.getElementById(el.id.replace('-drop','-chevron'));
                if (c) c.style.transform = '';
            }
        });
        var drop = document.getElementById(id + '-drop');
        var chev = document.getElementById(id + '-chevron');
        var btn  = document.getElementById(id + '-btn');
        if (!drop || !btn) return;
        var isOpen = drop.style.display !== 'none';
        if (isOpen) {
            drop.style.display = 'none';
            if (chev) chev.style.transform = '';
        } else {
            var r = btn.getBoundingClientRect();
            drop.style.top   = (r.bottom + 6) + 'px';
            drop.style.right = (window.innerWidth - r.right) + 'px';
            drop.style.left  = 'auto';
            drop.style.display = 'block';
            if (chev) chev.style.transform = 'rotate(180deg)';
        }
    };

    document.addEventListener('click', function(e) {
        var btn  = document.getElementById(uid + '-btn');
        var drop = document.getElementById(uid + '-drop');
        if (btn && drop && !btn.contains(e.target) && !drop.contains(e.target)) {
            drop.style.display = 'none';
            var chev = document.getElementById(uid + '-chevron');
            if (chev) chev.style.transform = '';
        }
    });

    window.addEventListener('resize', function() {
        var drop = document.getElementById(uid + '-drop');
        var btn  = document.getElementById(uid + '-btn');
        if (drop && btn && drop.style.display !== 'none') {
            var r = btn.getBoundingClientRect();
            drop.style.top   = (r.bottom + 6) + 'px';
            drop.style.right = (window.innerWidth - r.right) + 'px';
        }
    });
})();
</script>
