<style>
/* qsr- extracted inline styles */
.qsr-hidden         { display:none; }
.qsr-filters-title  { font-weight:700;font-size:15px; }
.qsr-arrow          { flex:none;transition:transform .15s; }
.qsr-dd-panel-init  { display:none;position:fixed;z-index:300;width:240px; }
.qsr-indent-icon    { flex-shrink:0; }
.qsr-indent-l2      { opacity:.4; }
.qsr-indent-l3      { opacity:.3; }
.qsr-sort-select    { width:auto; }
.qsr-subcat-pills   { margin-top:8px; }
.qsr-subcaret       { opacity:.5;flex-shrink:0; }
.qsr-empty-title    { margin:16px 0 8px;font-size:20px; }
.qsr-clear-filters-btn { margin-top:16px; }
.qsr-qcard-wrap     { position:relative; }
.qsr-fav-btn        { position:absolute;bottom:10px;right:10px;z-index:10; }
.qsr-cd             { display:none;position:absolute;bottom:0;left:0;right:0;
                      border-radius:0;padding:6px 12px;
                      background:rgba(0,0,0,.55);backdrop-filter:blur(6px);
                      color:#fff;font-size:12px;gap:6px; }
.qsr-cd-dot         { width:7px;height:7px;border-radius:50%;flex-shrink:0;display:inline-block;vertical-align:middle;margin-right:5px; }
.qsr-cd-label       { font-weight:600;font-size:10px;text-transform:uppercase;letter-spacing:.07em;opacity:.85;vertical-align:middle; }
.qsr-cd-timer       { font-family:monospace;font-weight:700;float:right; }
.qsr-avatar         { background:var(--brand-primary);width:20px;height:20px;font-size:10px; }
</style>

<div>

  {{-- ── MOBILE FILTER TOGGLE ───────────────────────────── --}}
  <div class="qs-mobile-bar">
    <div class="qs-mobile-search">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
      <input type="text" wire:model.live.debounce.400ms="search" placeholder="{{ __('quiz.search_placeholder') }}" class="qs-search-input">
    </div>
    <button class="qs-filter-toggle" onclick="document.getElementById('qs-drawer').classList.toggle('open')">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="4" y1="6" x2="20" y2="6"/><line x1="8" y1="12" x2="20" y2="12"/><line x1="12" y1="18" x2="20" y2="18"/></svg>
      {{ __('quiz.filters') }}
    </button>
  </div>

  {{-- ── MAIN LAYOUT ────────────────────────────────────── --}}
  <div class="qs-layout">

    {{-- SIDEBAR --}}
    <aside class="qs-sidebar card" id="qs-drawer">
      <div class="qs-sidebar-header">
        <span class="qsr-filters-title">{{ __('quiz.filters') }}</span>
        <button class="qs-drawer-close" onclick="document.getElementById('qs-drawer').classList.remove('open')" aria-label="Close filters">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6L6 18M6 6l12 12"/></svg>
        </button>
      </div>

      {{-- Search (desktop only inside sidebar) --}}
      <div class="qs-field qs-desktop-only">
        <label class="qs-label">{{ __('quiz.search') }}</label>
        <div class="qs-search-wrap">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
          <input type="text" wire:model.live.debounce.400ms="search" placeholder="{{ __('quiz.search_placeholder') }}" class="qs-search-input">
        </div>
      </div>

      {{-- Category (searchable dropdown) --}}
      <div class="qs-field">
        <label class="qs-label">{{ __('quiz.filter_category') }}</label>
        <div class="qs-dd" id="cat-dd">
          {{-- Trigger --}}
          <button type="button" class="qs-dd-trigger" id="cat-trigger" onclick="catDdToggle()">
            <span class="qs-dd-value" id="cat-label">{{ $category ? $categories->firstWhere('slug', $category)?->name ?? __('quiz.filter_all_categories') : __('quiz.filter_all_categories') }}</span>
            <svg id="cat-arrow" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="qsr-arrow"><path d="M6 9l6 6 6-6"/></svg>
          </button>

          {{-- Panel (position:fixed to escape overflow containers) --}}
          <div class="qs-dd-panel qsr-dd-panel-init" id="cat-panel">
            <div class="qs-dd-search">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
              <input type="text" id="cat-search" class="qs-dd-input" placeholder="{{ __('quiz.search_categories') }}" oninput="catDdFilter(this.value)" onkeydown="if(event.key==='Escape')catDdClose()">
            </div>
            <div class="qs-dd-opts" id="cat-opts">
              <button type="button" class="qs-dd-opt {{ !$category ? 'active' : '' }}" onclick="catDdPick('', '{{ __('quiz.filter_all_categories') }}')">{{ __('quiz.filter_all_categories') }}</button>
              @foreach($categories as $cat)
                {{-- Parent category row --}}
                <button type="button"
                        class="qs-dd-opt qs-dd-parent {{ $category === $cat->slug ? 'active' : '' }}"
                        data-label="{{ $cat->name }}"
                        onclick="catDdPick('{{ $cat->slug }}', '{{ $cat->name }}')">
                  {{ $cat->name }}
                  @if($cat->children->isNotEmpty())
                    <span class="qs-dd-parent-badge">{{ $cat->children->count() }}</span>
                  @endif
                </button>
                {{-- Children (level 2) --}}
                @foreach($cat->children as $child)
                  <button type="button"
                          class="qs-dd-opt qs-dd-child {{ $category === $child->slug ? 'active' : '' }}"
                          data-label="{{ $cat->name }} › {{ $child->name }}"
                          onclick="catDdPick('{{ $child->slug }}', '{{ $cat->name }} › {{ $child->name }}')">
                    <svg width="10" height="10" viewBox="0 0 10 10" fill="none" class="qsr-indent-icon qsr-indent-l2"><path d="M2 2v5h6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    {{ $child->name }}
                  </button>
                  {{-- Grandchildren (level 3) --}}
                  @foreach($child->children as $grand)
                    <button type="button"
                            class="qs-dd-opt qs-dd-grand {{ $category === $grand->slug ? 'active' : '' }}"
                            data-label="{{ $cat->name }} › {{ $child->name }} › {{ $grand->name }}"
                            onclick="catDdPick('{{ $grand->slug }}', '{{ $cat->name }} › {{ $child->name }} › {{ $grand->name }}')">
                      <svg width="10" height="10" viewBox="0 0 10 10" fill="none" class="qsr-indent-icon qsr-indent-l3"><path d="M2 2v5h6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                      {{ $grand->name }}
                    </button>
                  @endforeach
                @endforeach
              @endforeach
              <p class="qs-dd-empty qsr-hidden" id="cat-empty">{{ __('quiz.no_categories_found') }}</p>
            </div>
          </div>
        </div>
      </div>

@script
<script>
'use strict';
  // ── Category dropdown helpers (run once, $wire is stable here) ──
  window.catDdToggle = function() {
    var p = document.getElementById('cat-panel');
    var a = document.getElementById('cat-arrow');
    var t = document.getElementById('cat-trigger');
    var open = p.style.display === 'block';
    if (!open) {
      var r = t.getBoundingClientRect();
      p.style.top   = (r.bottom + 6) + 'px';
      p.style.left  = r.left + 'px';
      p.style.width = r.width + 'px';
    }
    p.style.display = open ? 'none' : 'block';
    a.style.transform = open ? '' : 'rotate(180deg)';
    if (!open) { setTimeout(function(){ var s=document.getElementById('cat-search'); s&&s.focus(); },50); }
  };

  window.catDdClose = function() {
    var p = document.getElementById('cat-panel');
    var a = document.getElementById('cat-arrow');
    if (p) p.style.display = 'none';
    if (a) a.style.transform = '';
  };

  window.catDdPick = function(slug, label) {
    var el = document.getElementById('cat-label');
    if (el) el.textContent = label;
    catDdClose();
    var s = document.getElementById('cat-search');
    if (s) { s.value = ''; catDdFilter(''); }
    document.querySelectorAll('#cat-opts .qs-dd-opt').forEach(function(b) {
      b.classList.remove('active');
      if (slug === '' ? !b.dataset.label : b.dataset.label === label) b.classList.add('active');
    });
    $wire.set('category', slug);
  };

  window.catDdFilter = function(q) {
    var lq = q.toLowerCase();
    var visible = 0;
    document.querySelectorAll('#cat-opts .qs-dd-opt').forEach(function(b) {
      var lbl = (b.dataset.label || b.textContent).toLowerCase();
      var show = !q || lbl.includes(lq);
      b.style.display = show ? '' : 'none';
      if (show && b.dataset.label) visible++;
    });
    var empty = document.getElementById('cat-empty');
    if (empty) empty.style.display = (q && visible === 0) ? '' : 'none';
  };

  window.catClearAll = function() {
    // Reset dropdown UI first
    var el = document.getElementById('cat-label');
    if (el) el.textContent = '{{ __('quiz.filter_all_categories') }}';
    catDdClose();
    document.querySelectorAll('#cat-opts .qs-dd-opt').forEach(function(b){ b.classList.remove('active'); });
    var first = document.querySelector('#cat-opts .qs-dd-opt');
    if (first) first.classList.add('active');
    // Single Livewire call — clears all three properties atomically
    $wire.call('clearFilters');
  };

  // Close panel on outside click
  document.addEventListener('click', function(e) {
    var dd    = document.getElementById('cat-dd');
    var panel = document.getElementById('cat-panel');
    if (!dd || !panel) return;
    if (!dd.contains(e.target) && !panel.contains(e.target)) catDdClose();
  });
</script>
@endscript

      {{-- Sort (mobile only inside drawer) --}}
      <div class="qs-field qs-mobile-only">
        <label class="qs-label">{{ __('quiz.sort_by') }}</label>
        <select wire:model.live="sort" class="qs-select">
          <option value="newest">{{ __('quiz.sort_newest') }}</option>
          <option value="popular">{{ __('quiz.sort_popular') }}</option>
        </select>
      </div>

      {{-- Active filters clear --}}
      @if($search || $category)
      <button onclick="catClearAll()" class="qs-clear-btn">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6L6 18M6 6l12 12"/></svg>
        {{ __('quiz.clear_all_filters') }}
      </button>
      @endif
    </aside>

    {{-- RESULTS --}}
    <div class="qs-results">

      {{-- Top bar --}}
      <div class="qs-topbar">
        <p class="qs-count">
          <b>{{ $quizzes->total() }}</b> {{ Str::plural('quiz', $quizzes->total()) }} found
          @if($search) for "<em>{{ $search }}</em>"@endif
        </p>
        <select wire:model.live="sort" class="qs-select qs-desktop-only qsr-sort-select">
          <option value="newest">{{ __('quiz.sort_newest') }}</option>
          <option value="popular">{{ __('quiz.sort_popular') }}</option>
        </select>
      </div>

      {{-- Category breadcrumb + drill-down (works at any depth) --}}
      @if($activeCategory)
      <div class="qs-cat-nav">
        {{-- Breadcrumb trail --}}
        <div class="qs-breadcrumb">
          <button type="button" wire:click="$set('category', '')" class="qs-bc-crumb qs-bc-all">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M3 12L12 3l9 9"/><path d="M9 21V12h6v9"/></svg>
            All
          </button>
          @foreach($ancestorChain as $ancestor)
            <svg class="qs-bc-sep" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 18l6-6-6-6"/></svg>
            @if(!$loop->last)
              <button type="button" wire:click="$set('category', '{{ $ancestor->slug }}')" class="qs-bc-crumb">{{ $ancestor->name }}</button>
            @else
              <span class="qs-bc-crumb qs-bc-active">{{ $ancestor->name }}</span>
            @endif
          @endforeach
        </div>

        {{-- Drill-down children (next level) --}}
        @if($drillChildren->isNotEmpty())
        <div class="qs-subcat-pills qsr-subcat-pills">
          @foreach($drillChildren as $child)
          <button type="button"
                  wire:click="$set('category', '{{ $child->slug }}')"
                  class="qs-subcat-pill">
            {{ $child->name }}
            @if($child->children()->exists())
              <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="qsr-subcaret"><path d="M9 18l6-6-6-6"/></svg>
            @endif
          </button>
          @endforeach
        </div>
        @endif
      </div>
      @endif

      {{-- Loading overlay --}}
      <div wire:loading wire:target="search,category,sort" class="qs-loading">
        <div class="qs-spinner"></div>
      </div>

      {{-- Empty state --}}
      @if($quizzes->isEmpty())
      <div class="qs-empty" wire:loading.remove wire:target="search,category,sort">
        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="var(--text-muted)" stroke-width="1.5"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
        <h3 class="qsr-empty-title">{{ __('quiz.no_results') }}</h3>
        <p class="sec">{{ __('quiz.no_results_desc') }}</p>
        @if($search || $category)
        <button wire:click="clearFilters" class="btn btn-ghost qsr-clear-filters-btn">{{ __('quiz.clear_filters') }}</button>
        @endif
      </div>

      @else
      {{-- Quiz grid --}}
      <div class="qs-grid" wire:loading.class="qs-grid-loading" wire:target="search,category,sort">
        @foreach($quizzes as $quiz)
        @php $idx = ($loop->index % 9) + 1; @endphp
        <div class="qcard-wrap qsr-qcard-wrap">
          @auth
          <div class="qsr-fav-btn">
            @livewire('toggle-favourite', ['quizId' => $quiz->id], key('fav-'.$quiz->id))
          </div>
          @endauth
        <a href="{{ route('quizzes.show', $quiz->slug) }}" class="qcard">
          {{-- Cover --}}
          <div class="qcard-cover cov-{{ $idx }}"
            @if($quiz->cover_image)
              style="background-image:url('{{ Storage::url($quiz->cover_image) }}');background-size:cover;background-position:center;"
            @endif
          >
            <div class="qcard-cover-top">
              <span class="qcard-cat">{{ $quiz->category->name }}</span>
            </div>
            <div class="qcard-title-overlay" style="{{ ($quiz->start_at || $quiz->end_at) ? 'padding-bottom:36px;' : '' }}">
              <h3 class="qcard-title">{{ $quiz->title }}</h3>
            </div>
            @if($quiz->start_at || $quiz->end_at)
            <div class="qcard-cd qsr-cd" id="qcd-{{ $quiz->id }}"
              data-start="{{ $quiz->start_at?->utc()->timestamp }}"
              data-end="{{ $quiz->end_at?->utc()->timestamp }}">
              <span class="qcd-dot qsr-cd-dot"></span>
              <span class="qcd-label qsr-cd-label"></span>
              <span class="qcd-timer qsr-cd-timer"></span>
            </div>
            @endif
          </div>

          {{-- Body --}}
          <div class="qcard-body">
            <div class="qcard-meta">
              <span>
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>
                {{ $quiz->total_questions }} Qs
              </span>
              <span>
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
                {{ number_format($quiz->total_attempts) }}
              </span>
              @if($quiz->duration_minutes)
              <span>
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                {{ $quiz->duration_minutes }}m
              </span>
              @endif
            </div>
            <div class="qcard-author">
              <div class="avatar qsr-avatar">{{ strtoupper(substr($quiz->lecturer->name, 0, 1)) }}</div>
              {{ $quiz->lecturer->name }}
            </div>
          </div>
        </a>
        </div>{{-- .qcard-wrap --}}
        @endforeach
      </div>

      {{-- Pagination --}}
      <div class="qs-pager">
        {{ $quizzes->links() }}
      </div>
      @endif

    </div>
  </div>

</div>

<script>
'use strict';
(function initCardCountdowns() {
  function pad(n) { return String(n).padStart(2, '0'); }
  function fmt(diffMs) {
    const t = Math.max(0, Math.floor(diffMs / 1000));
    const h = Math.floor(t / 3600), m = Math.floor((t % 3600) / 60), s = t % 60;
    return h > 0 ? `${pad(h)}:${pad(m)}:${pad(s)}` : `${pad(m)}:${pad(s)}`;
  }

  function updateCard(el) {
    const startMs = el.dataset.start ? el.dataset.start * 1000 : null;
    const endMs   = el.dataset.end   ? el.dataset.end   * 1000 : null;
    const now     = Date.now();
    const dot   = el.querySelector('.qcd-dot');
    const lbl   = el.querySelector('.qcd-label');
    const tmr   = el.querySelector('.qcd-timer');

    if (startMs && now < startMs) {
      el.style.display = 'block';
      dot.style.background = '#f59e0b';
      lbl.textContent = '{{ __('quiz.countdown_starts_in') }}';
      tmr.textContent = fmt(startMs - now);
    } else if (endMs && now < endMs) {
      el.style.display = 'block';
      dot.style.background = '#10b981';
      lbl.textContent = '{{ __('quiz.countdown_ends_in') }}';
      tmr.textContent = fmt(endMs - now);
    } else {
      el.style.display = 'none';
    }
  }

  function runAll() {
    document.querySelectorAll('.qcard-cd').forEach(updateCard);
  }

  runAll();
  setInterval(runAll, 1000);

  // Re-run after Livewire updates (pagination / search)
  document.addEventListener('livewire:navigated', runAll);
  document.addEventListener('livewire:update', () => setTimeout(runAll, 100));
})();
</script>

</div>
