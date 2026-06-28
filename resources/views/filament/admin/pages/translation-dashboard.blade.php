<x-filament-panels::page>
@php
    function tColor(int $pct): array {
        if ($pct >= 95) return ['#10b981', 'rgba(16,185,129,.15)', '#10b98133'];
        if ($pct >= 70) return ['#f59e0b', 'rgba(245,158,11,.15)', '#f59e0b33'];
        return             ['#ef4444', 'rgba(239,68,68,.15)',  '#ef444433'];
    }

    $current        = $stats[$selectedLocale] ?? null;
    $allPcts        = collect($stats)->pluck('overall_pct');
    $avgPct         = $allPcts->count() ? (int) round($allPcts->avg()) : 0;
    $enabledLocales = rescue(fn() => app(\App\Settings\PlatformSettings::class)->enabled_locales, ['en'], false);
@endphp

<style>
.td-wrap        { display:flex; flex-direction:column; gap:1.5rem; }
.td-topbar      { display:flex; align-items:center; justify-content:space-between; gap:1rem; flex-wrap:wrap; }
.td-summary     { display:flex; align-items:center; gap:2rem; }
.td-stat        { display:flex; flex-direction:column; }
.td-stat-val    { font-size:1.4rem; font-weight:800; line-height:1; }
.td-stat-lbl    { font-size:.7rem; font-weight:500; color:#6b7280; margin-top:.2rem; text-transform:uppercase; letter-spacing:.05em; }

/* Tabs */
.td-tabs        { display:flex; gap:.375rem; overflow-x:auto; padding-bottom:2px; scrollbar-width:none; align-items:center; }
.td-tabs::-webkit-scrollbar { display:none; }
.td-tab         { display:inline-flex; align-items:center; gap:.4rem; padding:.45rem .85rem; border-radius:8px; border:none; cursor:pointer;
                  font-size:.75rem; font-weight:600; white-space:nowrap; transition:all .15s; background:rgba(255,255,255,.05); color:#9ca3af; }
.td-tab:hover   { background:rgba(255,255,255,.09); color:#d1d5db; }
.td-tab.active  { color:#f9fafb; }
.td-tab-dot     { width:6px; height:6px; border-radius:50%; flex-shrink:0; }
.td-tab-del     { display:inline-flex;align-items:center;justify-content:center;width:14px;height:14px;border-radius:50%;
                  background:rgba(239,68,68,.15);color:#ef4444;border:none;cursor:pointer;padding:0;font-size:10px;
                  line-height:1;transition:.15s;flex-shrink:0;margin-left:1px; }
.td-tab-del:hover { background:rgba(239,68,68,.3); }

/* Card */
.td-card        { border-radius:16px; border:1px solid rgba(255,255,255,.08); background:rgba(255,255,255,.02); overflow:hidden; }
.td-card-hdr    { display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:1rem;
                  padding:1.25rem 1.5rem; border-bottom:1px solid rgba(255,255,255,.07); }
.td-locale-info { display:flex; align-items:center; gap:1rem; }
.td-ring-wrap   { position:relative; width:54px; height:54px; flex-shrink:0; }
.td-ring-wrap svg { transform:rotate(-90deg); }
.td-ring-label  { position:absolute; inset:0; display:flex; align-items:center; justify-content:center; font-size:.68rem; font-weight:800; }
.td-locale-name { font-size:1.05rem; font-weight:700; color:#f9fafb; }
.td-locale-code { font-size:.65rem; font-family:'IBM Plex Mono',monospace; font-weight:500;
                  background:rgba(255,255,255,.08); color:#9ca3af; padding:.1rem .4rem; border-radius:4px; }
.td-locale-sub  { font-size:.78rem; color:#9ca3af; margin-top:.2rem; }
.td-status-pill { display:inline-flex; align-items:center; gap:.375rem; padding:.35rem .85rem;
                  border-radius:999px; font-size:.7rem; font-weight:600; border:1px solid; }

/* Table */
.td-table       { width:100%; border-collapse:collapse; }
.td-table th    { font-size:.68rem; font-weight:600; letter-spacing:.07em; text-transform:uppercase;
                  color:#4b5563; padding:.6rem 1.25rem; border-bottom:1px solid rgba(255,255,255,.06); }
.td-table td    { padding:.7rem 1.25rem; font-size:.8125rem; border-top:1px solid rgba(255,255,255,.04); }
.td-table tr:hover td { background:rgba(255,255,255,.03); }
.td-file-row    { cursor:pointer; }
.td-file-row:hover td { background:rgba(99,102,241,.05) !important; }
.td-file-row.editing td { background:rgba(99,102,241,.1) !important; }
.mono-file      { font-family:'IBM Plex Mono',monospace; font-size:.7rem; color:#9ca3af; }
.bar-track      { height:4px; border-radius:999px; background:rgba(255,255,255,.07); overflow:hidden; flex:1; min-width:50px; }
.bar-fill       { height:100%; border-radius:999px; }

/* Delete confirm banner */
.td-del-banner  { background:rgba(239,68,68,.08);border-top:1px solid rgba(239,68,68,.2);padding:.75rem 1.5rem;
                  display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap; }

/* Inline editor */
.td-editor      { border-top:1px solid rgba(255,255,255,.07); }
.td-editor-hdr  { display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap;
                  padding:1rem 1.5rem;background:rgba(99,102,241,.06);border-bottom:1px solid rgba(99,102,241,.15); }
.td-editor-title { font-size:.875rem;font-weight:700;color:#818cf8;display:flex;align-items:center;gap:.5rem; }
.td-search-box  { display:flex;align-items:center;gap:.5rem;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);
                  border-radius:8px;padding:.35rem .75rem;flex:1;max-width:300px; }
.td-search-box svg { flex-shrink:0;opacity:.5; }
.td-search-box input { background:transparent;border:none;outline:none;color:#d1d5db;font-size:.8125rem;width:100%; }
.td-search-box input::placeholder { color:#4b5563; }
.td-kv-row      { display:grid;grid-template-columns:1fr 1.4fr auto;gap:0;border-top:1px solid rgba(255,255,255,.04);align-items:stretch; }
.td-kv-row:hover { background:rgba(255,255,255,.02); }
.td-key-cell    { padding:.55rem 1.25rem;font-family:'IBM Plex Mono',monospace;font-size:.72rem;color:#6b7280;
                  word-break:break-all;align-self:center;border-right:1px solid rgba(255,255,255,.05); }
.td-val-cell    { padding:.4rem .75rem;position:relative; }
.td-val-input   { width:100%;background:transparent;border:none;outline:none;color:#e5e7eb;font-size:.8125rem;
                  padding:.3rem 0;resize:none;min-height:32px;font-family:inherit;line-height:1.5; }
.td-val-input:focus { color:#fff; }
.td-val-input.changed { background:rgba(99,102,241,.08);border-radius:4px;padding:.3rem .5rem; }
.td-en-cell     { padding:.55rem 1rem;font-size:.75rem;color:#4b5563;word-break:break-word;align-self:center;
                  border-left:1px solid rgba(255,255,255,.04);max-width:280px; }
.td-editor-footer { padding:1rem 1.5rem;display:flex;align-items:center;justify-content:space-between;
                    gap:1rem;border-top:1px solid rgba(255,255,255,.06);background:rgba(255,255,255,.01); }
.td-kv-section  { max-height:520px;overflow-y:auto;scrollbar-width:thin;scrollbar-color:rgba(255,255,255,.1) transparent; }
.td-kv-head     { display:grid;grid-template-columns:1fr 1.4fr auto;gap:0;padding:.5rem 1.25rem;
                  font-size:.68rem;font-weight:600;letter-spacing:.07em;text-transform:uppercase;color:#4b5563;
                  border-bottom:1px solid rgba(255,255,255,.06);position:sticky;top:0;background:#111827;z-index:1; }

/* Modal */
.td-modal-bg    { position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:9998;display:flex;align-items:center;justify-content:center; }
.td-modal       { background:#1f2937;border:1px solid rgba(255,255,255,.1);border-radius:20px;padding:2rem;width:100%;
                  max-width:420px;box-shadow:0 24px 64px rgba(0,0,0,.5);position:relative;z-index:9999; }
.td-modal h3    { font-size:1.1rem;font-weight:700;color:#f9fafb;margin:0 0 .35rem; }
.td-modal p     { font-size:.8rem;color:#9ca3af;margin:0 0 1.5rem; }
.td-input       { width:100%;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.12);border-radius:10px;
                  padding:.6rem .9rem;color:#f3f4f6;font-size:.875rem;outline:none;transition:.15s;box-sizing:border-box; }
.td-input:focus { border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,.2); }
.td-label       { font-size:.72rem;font-weight:600;color:#9ca3af;letter-spacing:.05em;text-transform:uppercase;margin-bottom:.4rem;display:block; }
.td-field       { margin-bottom:1rem; }
.td-modal-footer { display:flex;gap:.75rem;justify-content:flex-end;margin-top:1.5rem; }
.td-btn-cancel  { padding:.55rem 1.1rem;border-radius:8px;border:1px solid rgba(255,255,255,.12);background:transparent;
                  color:#9ca3af;font-size:.8125rem;font-weight:600;cursor:pointer;transition:.15s; }
.td-btn-cancel:hover { background:rgba(255,255,255,.06);color:#d1d5db; }
.td-btn-primary { padding:.55rem 1.25rem;border-radius:8px;border:none;background:#6366f1;color:#fff;font-size:.8125rem;font-weight:600;cursor:pointer;transition:.15s; }
.td-btn-primary:hover { background:#4f46e5; }
</style>

<div class="td-wrap">

    {{-- ── Top bar ───────────────────────────────────────────────── --}}
    <div class="td-topbar">
        <div class="td-summary">
            <div class="td-stat">
                <span class="td-stat-val" style="color:#f9fafb;">{{ count($stats) }}</span>
                <span class="td-stat-lbl">Locales</span>
            </div>
            <div style="width:1px;height:32px;background:rgba(255,255,255,.08);"></div>
            <div class="td-stat">
                @php [$ac] = tColor($avgPct); @endphp
                <span class="td-stat-val" style="color:{{ $ac }};">{{ $avgPct }}%</span>
                <span class="td-stat-lbl">Avg coverage</span>
            </div>
            <div style="width:1px;height:32px;background:rgba(255,255,255,.08);"></div>
            <div class="td-stat">
                <span class="td-stat-val" style="color:#f9fafb;">{{ collect($stats)->sum('total_en') }}</span>
                <span class="td-stat-lbl">Total EN keys</span>
            </div>
        </div>

        <div style="display:flex;gap:.5rem;align-items:center;">
            <button wire:click="openAddModal" type="button"
                style="display:inline-flex;align-items:center;gap:.4rem;padding:.45rem 1rem;border-radius:8px;border:1px solid rgba(99,102,241,.4);
                       background:rgba(99,102,241,.12);color:#818cf8;font-size:.8125rem;font-weight:600;cursor:pointer;transition:.15s;"
                onmouseenter="this.style.background='rgba(99,102,241,.22)'"
                onmouseleave="this.style.background='rgba(99,102,241,.12)'"
            >
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                Add Language
            </button>

            <x-filament::button wire:click="refresh" wire:loading.attr="disabled" color="gray" size="sm">
                <span wire:loading.remove wire:target="refresh" style="display:flex;align-items:center;gap:6px;">
                    <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    Refresh
                </span>
                <span wire:loading wire:target="refresh">Refreshing…</span>
            </x-filament::button>
        </div>
    </div>

    @if(empty($stats))
    <div style="border:2px dashed rgba(255,255,255,.08);border-radius:16px;padding:5rem 2rem;text-align:center;">
        <p style="color:#6b7280;font-weight:500;margin-bottom:.35rem;">No additional locales configured.</p>
        <p style="font-size:.78rem;color:#4b5563;">Click <strong style="color:#818cf8;">Add Language</strong> above to get started.</p>
    </div>
    @else

    {{-- ── Tab bar ─────────────────────────────────────────────────── --}}
    <div class="td-tabs">
        @foreach($stats as $locale => $data)
        @php [$tc,,$border] = tColor($data['overall_pct']); @endphp
        <div style="display:inline-flex;align-items:center;gap:2px;">
            <button
                wire:click="selectLocale('{{ $locale }}')"
                class="td-tab {{ $selectedLocale === $locale ? 'active' : '' }}"
                style="{{ $selectedLocale === $locale ? "background:{$border}; border:1px solid {$tc}44; color:{$tc};" : 'border:1px solid transparent;' }}"
            >
                <span class="td-tab-dot" style="background:{{ $tc }};"></span>
                {{ $data['name'] }}
                <span style="font-size:.65rem;font-weight:500;opacity:.75;">{{ $data['overall_pct'] }}%</span>
                @if(!empty($data['is_custom']))
                    <span style="font-size:.6rem;background:rgba(99,102,241,.2);color:#818cf8;padding:.1rem .35rem;border-radius:4px;font-weight:700;">custom</span>
                @endif
            </button>
            @if($selectedLocale === $locale)
            <button wire:click="confirmDelete('{{ $locale }}')" class="td-tab-del" title="Delete {{ $data['name'] }}" type="button">✕</button>
            @endif
        </div>
        @endforeach
    </div>

    {{-- ── Locale card ──────────────────────────────────────────────── --}}
    @if($current)
    @php
        $pct    = $current['overall_pct'];
        [$main, $alphaBg, $border] = tColor($pct);
        $circ   = round(2 * M_PI * 22, 2);
        $offset = round($circ * (1 - $pct / 100), 2);
        $label  = $pct >= 95 ? 'Complete' : ($pct >= 70 ? 'Needs work' : 'Incomplete');
        $isEnabled = in_array($selectedLocale, $enabledLocales);
    @endphp

    <div class="td-card">

        {{-- Header --}}
        <div class="td-card-hdr" style="background:{{ $alphaBg }};">
            <div class="td-locale-info">
                <div class="td-ring-wrap">
                    <svg width="54" height="54" viewBox="0 0 54 54">
                        <circle cx="27" cy="27" r="22" fill="none" stroke="rgba(255,255,255,.08)" stroke-width="4.5"/>
                        <circle cx="27" cy="27" r="22" fill="none" stroke="{{ $main }}" stroke-width="4.5"
                                stroke-linecap="round" stroke-dasharray="{{ $circ }}" stroke-dashoffset="{{ $offset }}"/>
                    </svg>
                    <span class="td-ring-label" style="color:{{ $main }};">{{ $pct }}%</span>
                </div>
                <div>
                    <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.25rem;">
                        <span class="td-locale-name">{{ $current['name'] }}</span>
                        <span class="td-locale-code">{{ $selectedLocale }}</span>
                        @if(!empty($current['is_custom']))
                            <span style="font-size:.65rem;background:rgba(99,102,241,.2);color:#818cf8;padding:.15rem .5rem;border-radius:6px;font-weight:700;">Custom</span>
                        @endif
                    </div>
                    <span class="td-locale-sub">
                        <span style="color:{{ $main }};font-weight:600;">{{ number_format($current['total_hit']) }}</span>
                        &nbsp;of {{ number_format($current['total_en']) }} keys translated
                        @if($current['total_en'] - $current['total_hit'] > 0)
                            &nbsp;·&nbsp;<span style="color:#ef4444;">{{ number_format($current['total_en'] - $current['total_hit']) }} missing</span>
                        @endif
                    </span>
                </div>
            </div>

            <div style="display:flex;align-items:center;gap:.75rem;flex-shrink:0;flex-wrap:wrap;">
                <span class="td-status-pill" style="color:{{ $main }};border-color:{{ $main }}44;background:{{ $alphaBg }};">
                    @if($pct >= 95)<svg width="12" height="12" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    @elseif($pct >= 70)<svg width="12" height="12" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                    @else<svg width="12" height="12" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>@endif
                    {{ $label }}
                </span>

                @if($selectedLocale !== 'en')
                <button wire:click="toggleLocale('{{ $selectedLocale }}')" wire:loading.attr="disabled" type="button"
                    style="display:inline-flex;align-items:center;gap:.4rem;padding:.35rem .85rem;border-radius:999px;border:1px solid;font-size:.7rem;font-weight:600;cursor:pointer;transition:all .15s;
                    {{ $isEnabled ? 'background:rgba(34,197,94,.12);color:#22c55e;border-color:#22c55e44;' : 'background:rgba(107,114,128,.1);color:#9ca3af;border-color:#4b556344;' }}"
                >
                    <svg width="11" height="11" viewBox="0 0 20 20" fill="currentColor">
                        @if($isEnabled)<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        @else<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>@endif
                    </svg>
                    {{ $isEnabled ? 'Enabled in switcher' : 'Disabled — click to enable' }}
                </button>
                @else
                <span style="font-size:.7rem;color:#4b5563;padding:.35rem .7rem;border-radius:999px;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.08);">Always on (default)</span>
                @endif
            </div>
        </div>

        {{-- Delete confirm banner --}}
        @if($confirmDeleteLocale === $selectedLocale && $selectedLocale !== 'en')
        <div class="td-del-banner">
            <div>
                <span style="color:#f87171;font-weight:700;font-size:.875rem;">⚠ Delete {{ $current['name'] }} ({{ $selectedLocale }})?</span>
                <p style="color:#9ca3af;font-size:.75rem;margin:.2rem 0 0;">This permanently deletes <code style="font-family:monospace;background:rgba(255,255,255,.07);padding:.1rem .3rem;border-radius:3px;">lang/{{ $selectedLocale }}/</code> and removes this language from the platform.</p>
            </div>
            <div style="display:flex;gap:.5rem;flex-shrink:0;">
                <button wire:click="confirmDelete('')" type="button"
                    style="padding:.4rem .9rem;border-radius:7px;border:1px solid rgba(255,255,255,.12);background:transparent;color:#9ca3af;font-size:.75rem;font-weight:600;cursor:pointer;">Cancel</button>
                <button wire:click="deleteLocale('{{ $selectedLocale }}')" type="button" wire:loading.attr="disabled"
                    style="padding:.4rem .9rem;border-radius:7px;border:none;background:#ef4444;color:#fff;font-size:.75rem;font-weight:700;cursor:pointer;">
                    <span wire:loading.remove wire:target="deleteLocale">Yes, delete it</span>
                    <span wire:loading wire:target="deleteLocale">Deleting…</span>
                </button>
            </div>
        </div>
        @endif

        {{-- Progress bar --}}
        <div style="height:2px;background:rgba(255,255,255,.05);">
            <div style="height:100%;width:{{ $pct }}%;background:{{ $main }};transition:width .5s ease;"></div>
        </div>

        {{-- File table — click a row to open inline editor --}}
        <table class="td-table">
            <thead>
                <tr>
                    <th style="text-align:left;width:30%;">File <span style="font-weight:400;opacity:.5;font-size:.62rem;">(click to edit)</span></th>
                    <th style="text-align:right;">EN Keys</th>
                    <th style="text-align:right;">Translated</th>
                    <th style="text-align:right;">Missing</th>
                    <th style="text-align:left;padding-left:1.25rem;width:26%;">Coverage</th>
                </tr>
            </thead>
            <tbody>
                @foreach($current['files'] as $file)
                @php [$fc] = tColor($file['pct'] >= 100 ? 100 : ($file['pct'] >= 80 ? 80 : 0)); @endphp
                <tr class="td-file-row {{ $editingFile === $file['file'] ? 'editing' : '' }}"
                    wire:click="openEditor('{{ $file['file'] }}')" title="Click to edit {{ $file['file'] }}">
                    <td>
                        <div style="display:flex;align-items:center;gap:.5rem;">
                            <span style="width:6px;height:6px;border-radius:50%;background:{{ $fc }};flex-shrink:0;"></span>
                            <span class="mono-file">{{ $file['file'] }}</span>
                            @if($editingFile === $file['file'])
                                <span style="font-size:.6rem;background:rgba(99,102,241,.2);color:#818cf8;padding:.1rem .35rem;border-radius:4px;font-weight:700;">editing</span>
                            @endif
                        </div>
                    </td>
                    <td style="text-align:right;color:#4b5563;font-variant-numeric:tabular-nums;">{{ $file['en_count'] }}</td>
                    <td style="text-align:right;color:#34d399;font-weight:600;font-variant-numeric:tabular-nums;">{{ $file['translated'] }}</td>
                    <td style="text-align:right;font-variant-numeric:tabular-nums;">
                        @if($file['missing'] > 0)<span style="color:#f87171;font-weight:700;">{{ $file['missing'] }}</span>
                        @else<span style="color:#374151;">—</span>@endif
                    </td>
                    <td style="padding-left:1.25rem;padding-right:1.25rem;">
                        <div style="display:flex;align-items:center;gap:.5rem;">
                            <div class="bar-track"><div class="bar-fill" style="width:{{ $file['pct'] }}%;background:{{ $fc }};"></div></div>
                            <span style="font-size:.7rem;font-weight:600;color:{{ $fc }};width:2.5rem;text-align:right;flex-shrink:0;">{{ $file['pct'] }}%</span>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- ── Inline Translation Editor ────────────────────────── --}}
        @if($editingFile && !empty($editingKeys))
        @php
            // Load English source for side-by-side reference
            $enFilePath = lang_path('en/' . $editingFile);
            $enFlat     = [];
            if (file_exists($enFilePath)) {
                $enFlat = [];
                $enRaw = require $enFilePath;
                $flatFn = function(array $arr, string $prefix = '') use (&$flatFn, &$enFlat) {
                    foreach ($arr as $k => $v) {
                        $full = $prefix ? "{$prefix}.{$k}" : $k;
                        if (is_array($v)) $flatFn($v, $full);
                        else $enFlat[$full] = $v;
                    }
                };
                $flatFn($enRaw);
            }

            // Filter by search
            $filteredKeys = $editingKeys;
            if (!empty($editorSearch)) {
                $q = strtolower($editorSearch);
                $filteredKeys = array_filter($editingKeys, function($v, $k) use ($q) {
                    return str_contains(strtolower($k), $q) || str_contains(strtolower($v), $q);
                }, ARRAY_FILTER_USE_BOTH);
            }
        @endphp

        <div class="td-editor">
            {{-- Editor header --}}
            <div class="td-editor-hdr">
                <div class="td-editor-title">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Editing <code style="font-family:'IBM Plex Mono',monospace;font-size:.8rem;background:rgba(99,102,241,.15);padding:.1rem .4rem;border-radius:4px;">{{ $editingFile }}</code>
                    for <strong style="color:#f9fafb;">{{ $current['name'] }}</strong>
                    <span style="font-size:.7rem;font-weight:500;color:#6b7280;">({{ count($editingKeys) }} keys)</span>
                    @if($editorDirty)
                        <span style="font-size:.68rem;background:rgba(245,158,11,.2);color:#f59e0b;padding:.15rem .5rem;border-radius:999px;font-weight:700;">● Unsaved changes</span>
                    @endif
                </div>
                <div style="display:flex;align-items:center;gap:.75rem;">
                    <div class="td-search-box">
                        <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4-4"/></svg>
                        <input wire:model.live.debounce.250ms="editorSearch" type="text" placeholder="Filter keys…" autocomplete="off">
                        @if($editorSearch)
                        <button wire:click="$set('editorSearch','')" type="button" style="background:none;border:none;color:#6b7280;cursor:pointer;padding:0;font-size:12px;line-height:1;">✕</button>
                        @endif
                    </div>
                    <button wire:click="closeEditor" type="button"
                        style="display:inline-flex;align-items:center;gap:.35rem;padding:.4rem .85rem;border-radius:7px;border:1px solid rgba(255,255,255,.1);background:transparent;color:#9ca3af;font-size:.75rem;font-weight:600;cursor:pointer;">
                        Close editor
                    </button>
                </div>
            </div>

            {{-- Column headers --}}
            <div class="td-kv-head">
                <span>Key</span>
                <span>{{ $current['name'] }} translation</span>
                <span style="max-width:280px;">English (reference)</span>
            </div>

            {{-- Key-value rows --}}
            <div class="td-kv-section">
                @forelse($filteredKeys as $key => $val)
                @php $enVal = $enFlat[$key] ?? ''; @endphp
                <div class="td-kv-row">
                    <div class="td-key-cell" title="{{ $key }}">{{ $key }}</div>
                    <div class="td-val-cell">
                        <textarea
                            wire:change="updateKey('{{ addslashes($key) }}', $event.target.value)"
                            class="td-val-input {{ $val !== ($enFlat[$key] ?? $val) ? 'changed' : '' }}"
                            rows="1"
                            oninput="this.style.height='auto';this.style.height=this.scrollHeight+'px'"
                            style="height:auto;"
                        >{{ $val }}</textarea>
                    </div>
                    <div class="td-en-cell" title="{{ $enVal }}">{{ Str::limit($enVal, 120) }}</div>
                </div>
                @empty
                <div style="padding:2rem 1.25rem;text-align:center;color:#4b5563;font-size:.8rem;">
                    No keys match "{{ $editorSearch }}"
                </div>
                @endforelse
            </div>

            {{-- Editor footer --}}
            <div class="td-editor-footer">
                <span style="font-size:.75rem;color:#6b7280;">
                    @if($editorSearch)
                        Showing {{ count($filteredKeys) }} of {{ count($editingKeys) }} keys
                    @else
                        {{ count($editingKeys) }} keys total
                    @endif
                </span>
                <div style="display:flex;gap:.75rem;align-items:center;">
                    @if($editorDirty)
                    <span style="font-size:.75rem;color:#f59e0b;">Unsaved changes will be lost if you switch locale or file.</span>
                    @endif
                    <button wire:click="saveTranslations" type="button"
                        wire:loading.attr="disabled"
                        style="display:inline-flex;align-items:center;gap:.4rem;padding:.55rem 1.25rem;border-radius:8px;border:none;
                               background:{{ $editorDirty ? '#6366f1' : 'rgba(255,255,255,.06)' }};
                               color:{{ $editorDirty ? '#fff' : '#6b7280' }};
                               font-size:.8125rem;font-weight:700;cursor:{{ $editorDirty ? 'pointer' : 'default' }};transition:.15s;">
                        <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                        <span wire:loading.remove wire:target="saveTranslations">Save translations</span>
                        <span wire:loading wire:target="saveTranslations">Saving…</span>
                    </button>
                </div>
            </div>
        </div>
        @endif

    </div>
    @endif
    @endif

    <p style="font-size:.7rem;text-align:center;color:#374151;padding-bottom:.25rem;">
        Stats cached 5 min — click <strong style="color:#9ca3af;">Refresh</strong> to bust the cache.
    </p>

</div>

{{-- ── Add Language Modal ──────────────────────────────────────── --}}
@if($showAddModal)
@php $filteredLocales = $this->getFilteredLocales(); @endphp
<div class="td-modal-bg" wire:click.self="closeAddModal">
    <div class="td-modal" style="max-width:520px;">

        {{-- Header --}}
        <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:1.5rem;">
            <div>
                <h3 style="margin:0 0 .25rem;">Add New Language</h3>
                <p style="margin:0;">Search and select a language. Flag, name and RTL are auto-filled.</p>
            </div>
            <button wire:click="closeAddModal" type="button"
                style="background:rgba(255,255,255,.06);border:none;color:#9ca3af;width:28px;height:28px;border-radius:6px;cursor:pointer;font-size:14px;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-left:.75rem;">✕</button>
        </div>

        {{-- Selected locale display --}}
        @if($newLocaleCode)
        <div style="display:flex;align-items:center;gap:.75rem;padding:.85rem 1rem;margin-bottom:1rem;
                    background:rgba(99,102,241,.1);border:1px solid rgba(99,102,241,.3);border-radius:12px;">
            <span style="font-size:2rem;line-height:1;flex-shrink:0;">{{ $newLocaleFlag ?: '🌐' }}</span>
            <div style="flex:1;min-width:0;">
                <div style="font-size:.9375rem;font-weight:700;color:#f9fafb;">{{ $newLocaleName }}</div>
                <div style="font-size:.72rem;color:#818cf8;margin-top:.1rem;display:flex;align-items:center;gap:.5rem;">
                    <code style="background:rgba(99,102,241,.2);padding:.1rem .4rem;border-radius:4px;font-family:monospace;">{{ $newLocaleCode }}</code>
                    @if($newLocaleRtl)<span style="background:rgba(245,158,11,.15);color:#f59e0b;padding:.1rem .4rem;border-radius:4px;font-weight:700;">RTL</span>@endif
                </div>
            </div>
            <button wire:click="$set('newLocaleCode','')" type="button"
                style="background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.1);color:#9ca3af;padding:.3rem .6rem;border-radius:7px;font-size:.72rem;cursor:pointer;">
                Change
            </button>
        </div>
        @endif

        {{-- Locale search + list --}}
        @if(!$newLocaleCode)
        <div class="td-field" style="margin-bottom:.75rem;">
            <div class="td-search-box" style="max-width:100%;">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4-4"/></svg>
                <input wire:model.live.debounce.150ms="localePickerSearch"
                       type="text" placeholder="Search language… (e.g. Spanish, ar, French)"
                       autocomplete="off" style="font-size:.875rem;" autofocus>
                @if($localePickerSearch)
                <button wire:click="$set('localePickerSearch','')" type="button"
                    style="background:none;border:none;color:#6b7280;cursor:pointer;padding:0;font-size:12px;">✕</button>
                @endif
            </div>
        </div>

        <div style="max-height:280px;overflow-y:auto;border:1px solid rgba(255,255,255,.08);border-radius:12px;scrollbar-width:thin;scrollbar-color:rgba(255,255,255,.1) transparent;">
            @forelse($filteredLocales as $code => $info)
            <button
                type="button"
                wire:click="selectKnownLocale('{{ $code }}')"
                style="width:100%;display:flex;align-items:center;gap:.75rem;padding:.7rem 1rem;border:none;
                       background:transparent;cursor:pointer;text-align:left;transition:.1s;
                       border-bottom:1px solid rgba(255,255,255,.04);"
                onmouseenter="this.style.background='rgba(99,102,241,.08)'"
                onmouseleave="this.style.background='transparent'"
            >
                <span style="font-size:1.375rem;line-height:1;width:28px;text-align:center;flex-shrink:0;">{{ $info['flag'] }}</span>
                <span style="flex:1;min-width:0;">
                    <span style="font-size:.875rem;font-weight:600;color:#e5e7eb;display:block;">{{ $info['name'] }}</span>
                    <span style="font-size:.7rem;color:#6b7280;font-family:monospace;">{{ $code }}</span>
                </span>
                @if($info['rtl'])
                <span style="font-size:.62rem;background:rgba(245,158,11,.15);color:#f59e0b;padding:.15rem .4rem;border-radius:4px;font-weight:700;flex-shrink:0;">RTL</span>
                @endif
                <svg style="width:14px;height:14px;color:#4b5563;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </button>
            @empty
            <div style="padding:2rem;text-align:center;color:#4b5563;font-size:.8rem;">
                No languages match "{{ $localePickerSearch }}"
            </div>
            @endforelse
        </div>
        @endif

        {{-- RTL override (shown after selection, auto-set but overridable) --}}
        @if($newLocaleCode)
        <div style="display:flex;align-items:center;justify-content:space-between;padding:.85rem 1rem;
                    background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.08);border-radius:10px;margin-bottom:1rem;">
            <div>
                <div style="font-size:.8125rem;font-weight:600;color:#d1d5db;">Right-to-left (RTL) layout</div>
                <div style="font-size:.72rem;color:#6b7280;margin-top:.15rem;">Auto-detected · override if needed</div>
            </div>
            <button type="button" wire:click="$toggle('newLocaleRtl')"
                style="width:44px;height:24px;border-radius:999px;border:none;cursor:pointer;position:relative;transition:.2s;flex-shrink:0;
                       background:{{ $newLocaleRtl ? '#6366f1' : 'rgba(255,255,255,.12)' }};">
                <span style="position:absolute;top:2px;width:20px;height:20px;background:#fff;border-radius:50%;transition:.2s;
                             {{ $newLocaleRtl ? 'right:2px;left:auto;' : 'left:2px;' }}"></span>
            </button>
        </div>

        {{-- Native name override --}}
        <div class="td-field">
            <label class="td-label">Native name <span style="font-weight:400;opacity:.5;">(auto-filled, editable)</span></label>
            <input wire:model="newLocaleName" type="text" class="td-input" autocomplete="off">
        </div>
        @endif

        <div class="td-modal-footer" style="margin-top:{{ $newLocaleCode ? '0' : '1rem' }};">
            <button wire:click="closeAddModal" type="button" class="td-btn-cancel">Cancel</button>
            @if($newLocaleCode)
            <button wire:click="addLocale" type="button" class="td-btn-primary" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="addLocale" style="display:flex;align-items:center;gap:.4rem;">
                    <span style="font-size:1.1rem;">{{ $newLocaleFlag ?: '🌐' }}</span>
                    Add {{ $newLocaleName }}
                </span>
                <span wire:loading wire:target="addLocale">Adding…</span>
            </button>
            @else
            <button type="button" class="td-btn-primary" disabled style="opacity:.4;cursor:not-allowed;">Select a language first</button>
            @endif
        </div>
    </div>
</div>
@endif

<script>
// Auto-resize textareas on page load
document.addEventListener('livewire:navigated', () => autoResizeAll());
document.addEventListener('DOMContentLoaded', () => autoResizeAll());
document.addEventListener('livewire:update', () => setTimeout(autoResizeAll, 50));
function autoResizeAll() {
    document.querySelectorAll('.td-val-input').forEach(el => {
        el.style.height = 'auto';
        el.style.height = el.scrollHeight + 'px';
    });
}
</script>

</x-filament-panels::page>
