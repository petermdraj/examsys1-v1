<x-filament-panels::page>
<style>
.rp-card { background:var(--rp-card-bg,#fff); border:1px solid var(--rp-border,#e5e7eb); border-radius:16px; }
.dark .rp-card { --rp-card-bg:#111827; --rp-border:#1f2937; }

.rp-kpi { padding:20px; position:relative; overflow:hidden; }
.rp-kpi .kpi-icon { width:36px; height:36px; border-radius:10px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.rp-kpi .kpi-num { font-size:2.4rem; font-weight:800; line-height:1; letter-spacing:-.02em; }
.rp-kpi .kpi-label { font-size:.7rem; font-weight:700; letter-spacing:.1em; text-transform:uppercase; color:#9ca3af; margin-bottom:10px; }
.rp-kpi .kpi-sub { font-size:.72rem; color:#9ca3af; margin-top:6px; }
.rp-kpi .kpi-bar { position:absolute; bottom:0; left:0; right:0; height:3px; border-radius:0 0 16px 16px; }
.rp-kpi .kpi-glow { position:absolute; top:-40px; right:-40px; width:100px; height:100px; border-radius:50%; opacity:.06; }

.rp-sel { background:var(--rp-card-bg,#fff); border:1px solid var(--rp-border,#e5e7eb); border-radius:16px; padding:18px 22px; display:flex; align-items:center; gap:20px; }
.dark .rp-sel { --rp-card-bg:#111827; --rp-border:#1f2937; }

.rp-section { background:var(--rp-card-bg,#fff); border:1px solid var(--rp-border,#e5e7eb); border-radius:16px; padding:22px; }
.dark .rp-section { --rp-card-bg:#111827; --rp-border:#1f2937; }
.rp-section-title { font-size:.8rem; font-weight:700; letter-spacing:.05em; text-transform:uppercase; color:#9ca3af; margin-bottom:18px; }

/* Bar chart */
.dist-row { display:flex; align-items:center; gap:10px; margin-bottom:14px; }
.dist-label { font-size:.75rem; color:#9ca3af; width:54px; flex-shrink:0; text-align:right; }
.dist-track { flex:1; height:8px; background:rgba(156,163,175,.15); border-radius:99px; overflow:hidden; }
.dist-fill { height:100%; border-radius:99px; transition:width .4s ease; }
.dist-count { font-size:.75rem; font-weight:700; color:#d1d5db; width:22px; text-align:right; flex-shrink:0; }

/* Table */
.rp-table { width:100%; border-collapse:collapse; }
.rp-table th { font-size:.68rem; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:#6b7280; padding:0 8px 10px; }
.rp-table td { padding:10px 8px; border-top:1px solid rgba(156,163,175,.1); }
.rp-table tr:hover td { background:rgba(108,46,99,.04); }
.dark .rp-table tr:hover td { background:rgba(108,46,99,.12); }

.avatar { width:32px; height:32px; border-radius:50%; background:linear-gradient(135deg,#6C2E63,#9d4a8f); color:#fff; display:flex; align-items:center; justify-content:center; font-size:.7rem; font-weight:800; flex-shrink:0; }
.badge-pass { display:inline-flex; align-items:center; gap:4px; padding:3px 10px; border-radius:99px; font-size:.7rem; font-weight:700; background:#d1fae5; color:#059669; }
.badge-fail { display:inline-flex; align-items:center; gap:4px; padding:3px 10px; border-radius:99px; font-size:.7rem; font-weight:700; background:#fee2e2; color:#dc2626; }
.dark .badge-pass { background:rgba(5,150,105,.2); color:#34d399; }
.dark .badge-fail { background:rgba(220,38,38,.2); color:#f87171; }

/* Meta grid */
.meta-item { display:flex; align-items:center; gap:12px; }
.meta-icon { width:34px; height:34px; border-radius:9px; background:rgba(156,163,175,.1); display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.meta-label { font-size:.7rem; color:#9ca3af; }
.meta-value { font-size:.875rem; font-weight:700; color:#111827; }
.dark .meta-value { color:#f9fafb; }

/* Status badges */
.badge-published { background:#dcfce7; color:#16a34a; padding:3px 10px; border-radius:99px; font-size:.7rem; font-weight:700; }
.badge-draft     { background:#fef9c3; color:#ca8a04; padding:3px 10px; border-radius:99px; font-size:.7rem; font-weight:700; }
.dark .badge-published { background:rgba(22,163,74,.2); color:#4ade80; }
.dark .badge-draft     { background:rgba(202,138,4,.2); color:#fbbf24; }

/* Layout helpers */
.rp-page       { display:flex; flex-direction:column; gap:20px; }
.rp-sel-left   { flex:1; min-width:0; }
.rp-sel-label  { font-size:.68rem; font-weight:700; letter-spacing:.1em; text-transform:uppercase; color:#9ca3af; margin-bottom:4px; }
.rp-sel-select { width:100%; max-width:480px; font-size:1.05rem; font-weight:700; background:transparent; border:none; outline:none; cursor:pointer; color:inherit; appearance:none; -webkit-appearance:none; -moz-appearance:none; background-image:none; }
.rp-sel-meta   { display:flex; align-items:center; gap:10px; flex-shrink:0; }
.rp-sel-count  { font-size:.75rem; color:#9ca3af; }
.rp-sel-dot    { color:#d1d5db; }
.rp-grid-4     { display:grid; grid-template-columns:repeat(4,1fr); gap:16px; }
.rp-grid-dist  { display:grid; grid-template-columns:280px 1fr; gap:16px; }
.rp-grid-meta  { display:grid; grid-template-columns:repeat(6,1fr); gap:16px; }

/* KPI card inner flex */
.kpi-hdr       { display:flex; align-items:flex-start; justify-content:space-between; }
.kpi-icon-v    { width:16px; height:16px; }

/* Attempt table */
.rp-count-badge { font-size:.72rem; color:#9ca3af; background:rgba(156,163,175,.1); padding:3px 10px; border-radius:99px; }
.rp-section-tbl { padding:22px 0; }
.rp-tbl-hdr     { display:flex; align-items:center; justify-content:space-between; padding:0 22px 16px; }
.rp-td-student  { padding-left:22px; }
.rp-td-center   { text-align:center; }
.rp-td-date     { text-align:right; padding-right:22px; font-size:.75rem; color:#9ca3af; }
.rp-td-time     { color:#6b7280; }
.rp-student-wrap{ display:flex; align-items:center; gap:10px; }
.rp-student-name{ font-weight:600; font-size:.875rem; }
.rp-score-main  { font-weight:700; font-size:.95rem; }
.rp-score-frac  { display:block; font-size:.7rem; color:#9ca3af; }

/* Pass rate inline bar */
.rp-pr-track    { margin-top:8px; height:5px; background:rgba(156,163,175,.15); border-radius:99px; overflow:hidden; }
.rp-pr-fill     { height:100%; border-radius:99px; }

/* Meta item in quiz config */
.rp-meta-item   { display:flex; align-items:center; gap:10px; padding:14px; border-radius:12px; background:rgba(156,163,175,.06); }
.rp-meta-icon   { width:32px; height:32px; border-radius:9px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.rp-meta-icon svg { width:15px; height:15px; }
.rp-meta-label  { font-size:.67rem; color:#9ca3af; font-weight:600; text-transform:uppercase; letter-spacing:.06em; }
.rp-meta-value  { font-size:.9rem; font-weight:700; color:inherit; margin-top:1px; }

/* Empty state */
.rp-empty       { display:flex; flex-direction:column; align-items:center; justify-content:center; padding:80px 0; color:#9ca3af; }
.rp-empty svg   { width:48px; height:48px; margin-bottom:12px; }
.rp-empty-title { font-size:1rem; font-weight:600; margin-bottom:4px; }
.rp-empty-sub   { font-size:.875rem; margin-bottom:16px; }
.rp-empty-sm    { font-size:.875rem; }
.rp-empty-cta   { display:inline-flex; align-items:center; gap:6px; padding:10px 20px; border-radius:10px; font-size:.875rem; font-weight:700; color:#fff; background:#6C2E63; text-decoration:none; }

/* KPI per-card static styles */
.kpi-c-purple   { color:#8b5cf6; }
.kpi-c-blue     { color:#3b82f6; }
.kpi-c-amber    { color:#f59e0b; }
.kpi-bg-purple  { background:rgba(139,92,246,.12); }
.kpi-bg-blue    { background:rgba(59,130,246,.12); }
.kpi-bg-amber   { background:rgba(245,158,11,.12); }
.kpi-bar-purple { background:linear-gradient(90deg,#8b5cf6,#a78bfa); }
.kpi-bar-blue   { background:linear-gradient(90deg,#3b82f6,#60a5fa); }
.kpi-bar-amber  { background:linear-gradient(90deg,#f59e0b,#fbbf24); }
.kpi-sub-passed { color:#10b981; font-weight:600; }
.kpi-sub-failed { color:#ef4444; font-weight:600; }
.kpi-sub-dim    { color:#d1d5db; }
.kpi-pct-sm     { font-size:1.4rem; opacity:.6; }
.kpi-min-sm     { font-size:1.2rem; opacity:.6; }

/* Table header rows */
.rp-th-border   { border-top:1px solid rgba(156,163,175,.1); }
.rp-th-left     { text-align:left; padding-left:22px; }
.rp-th-center   { text-align:center; }
.rp-th-right    { text-align:right; padding-right:22px; }
.rp-section-title-inline { margin-bottom:0; }

/* SVG icon sizes */
.ico-10 { width:10px; height:10px; }

/* Empty no-data text */
.rp-nodata      { text-align:center; padding:32px 0; color:#9ca3af; font-size:.875rem; }
.rp-noattempts  { text-align:center; padding:40px; color:#9ca3af; font-size:.875rem; }
</style>

<div class="rp-page">

    {{-- ── Quiz selector ── --}}
    <div class="rp-sel">
        <div class="rp-sel-left">
            <div class="rp-sel-label">{{ __('lecturer.rp_viewing_for') }}</div>
            <div style="position:relative;display:inline-block;max-width:480px;width:100%;">
                <select wire:change="selectQuiz($event.target.value)"
                    style="width:100%;font-size:1.05rem;font-weight:700;background:transparent;border:none;outline:none;cursor:pointer;color:inherit;appearance:none;-webkit-appearance:none;-moz-appearance:none;background-image:none;padding-right:24px;">
                    <option value="" disabled @selected(!$selectedQuizId)>— Select a quiz —</option>
                    @foreach($quizzes as $q)
                        <option value="{{ $q->id }}" @selected($q->id === $selectedQuizId)>{{ $q->title }}</option>
                    @endforeach
                </select>
                <svg style="position:absolute;right:2px;top:50%;transform:translateY(-50%);pointer-events:none;" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" opacity="0.4"><path d="M6 9l6 6 6-6"/></svg>
            </div>
        </div>
        @if($quiz)
        <div class="rp-sel-meta">
            <span class="badge-{{ $quiz->status === 'published' ? 'published' : 'draft' }}">{{ ucfirst($quiz->status) }}</span>
            <span class="rp-sel-count">{{ $quiz->total_questions }} questions</span>
        </div>
        @endif
    </div>

    @if($quiz && $stats)

    {{-- ── KPI cards ── --}}
    <div class="rp-grid-4">

        {{-- Total Attempts --}}
        <div class="rp-card rp-kpi">
            <div class="kpi-glow kpi-c-purple"></div>
            <div class="kpi-hdr">
                <div class="kpi-label">{{ __('lecturer.rp_total_attempts') }}</div>
                <div class="kpi-icon kpi-bg-purple">
                    <svg class="kpi-icon-v kpi-c-purple" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
                </div>
            </div>
            <div class="kpi-num kpi-c-purple">{{ $stats['total_attempts'] }}</div>
            <div class="kpi-sub">
                <span class="kpi-sub-passed">{{ __('lecturer.rp_n_passed', ['n' => $stats['passed']]) }}</span>
                &nbsp;·&nbsp;
                <span class="kpi-sub-failed">{{ __('lecturer.rp_n_failed', ['n' => $stats['failed']]) }}</span>
            </div>
            <div class="kpi-bar kpi-bar-purple"></div>
        </div>

        {{-- Avg Score --}}
        <div class="rp-card rp-kpi">
            <div class="kpi-glow kpi-c-blue"></div>
            <div class="kpi-hdr">
                <div class="kpi-label">{{ __('lecturer.rp_avg_score') }}</div>
                <div class="kpi-icon kpi-bg-blue">
                    <svg class="kpi-icon-v kpi-c-blue" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M18 20V10M12 20V4M6 20v-6"/></svg>
                </div>
            </div>
            <div class="kpi-num kpi-c-blue">{{ $stats['avg_score'] }}<span class="kpi-pct-sm">%</span></div>
            <div class="kpi-sub">{{ __('lecturer.rp_top') }} <strong class="kpi-sub-dim">{{ $stats['top_score'] }}%</strong> &nbsp;·&nbsp; {{ __('lecturer.rp_low') }} <strong class="kpi-sub-dim">{{ $stats['lowest_score'] }}%</strong></div>
            <div class="kpi-bar kpi-bar-blue"></div>
        </div>

        {{-- Pass Rate --}}
        @php $pr = $stats['pass_rate']; $prColor = $pr >= 70 ? '#10b981' : ($pr >= 40 ? '#f59e0b' : '#ef4444'); @endphp
        <div class="rp-card rp-kpi">
            <div class="kpi-glow" style="background:{{ $prColor }};"></div>
            <div class="kpi-hdr">
                <div class="kpi-label">{{ __('lecturer.rp_pass_rate') }}</div>
                <div class="kpi-icon" style="background:{{ $prColor }}1a;">
                    <svg class="kpi-icon-v" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="color:{{ $prColor }};"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <div class="kpi-num" style="color:{{ $prColor }};">{{ $pr }}<span class="kpi-pct-sm">%</span></div>
            <div class="rp-pr-track">
                <div class="rp-pr-fill" style="width:{{ $pr }}%; background:{{ $prColor }};"></div>
            </div>
            <div class="kpi-bar" style="background:{{ $prColor }};"></div>
        </div>

        {{-- Avg Time --}}
        <div class="rp-card rp-kpi">
            <div class="kpi-glow kpi-c-amber"></div>
            <div class="kpi-hdr">
                <div class="kpi-label">{{ __('lecturer.rp_avg_time') }}</div>
                <div class="kpi-icon kpi-bg-amber">
                    <svg class="kpi-icon-v kpi-c-amber" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                </div>
            </div>
            <div class="kpi-num kpi-c-amber">{{ $stats['avg_time'] }}<span class="kpi-min-sm"> min</span></div>
            <div class="kpi-sub">{{ $quiz->duration_minutes ? __('lecturer.rp_of_min_limit', ['n' => $quiz->duration_minutes]) : __('lecturer.rp_no_time_limit') }}</div>
            <div class="kpi-bar kpi-bar-amber"></div>
        </div>
    </div>

    {{-- ── Distribution + Table ── --}}
    <div class="rp-grid-dist">

        {{-- Score Distribution --}}
        <div class="rp-section">
            <div class="rp-section-title">{{ __('lecturer.rp_score_distribution') }}</div>
            @if($stats['total_attempts'] > 0)
            @php $dColors = ['#ef4444','#f97316','#eab308','#3b82f6','#10b981']; @endphp
            @foreach($scoreBuckets as $b)
            <div class="dist-row">
                <div class="dist-label">{{ $b['label'] }}</div>
                <div class="dist-track">
                    <div class="dist-fill" style="width:{{ max(4,$b['pct']) }}%; background:{{ $dColors[$loop->index] }};"></div>
                </div>
                <div class="dist-count">{{ $b['count'] }}</div>
            </div>
            @endforeach
            @else
            <div class="rp-nodata">{{ __('lecturer.rp_no_data_yet') }}</div>
            @endif
        </div>

        {{-- Recent Attempts --}}
        <div class="rp-section rp-section-tbl">
            <div class="rp-tbl-hdr">
                <div class="rp-section-title rp-section-title-inline">{{ __('lecturer.rp_recent_attempts') }}</div>
                <span class="rp-count-badge">
                    {{ $recentAttempts->count() }} of {{ $stats['total_attempts'] }}
                </span>
            </div>

            @if($recentAttempts->count())
            <table class="rp-table">
                <thead>
                    <tr class="rp-th-border">
                        <th class="rp-th-left">Student</th>
                        <th class="rp-th-center">Score</th>
                        <th class="rp-th-center">Result</th>
                        <th class="rp-th-right">Date</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($recentAttempts as $a)
                <tr>
                    <td class="rp-td-student">
                        <div class="rp-student-wrap">
                            <div class="avatar">{{ strtoupper(substr($a->user->name,0,1)) }}</div>
                            <span class="rp-student-name">{{ $a->user->name }}</span>
                        </div>
                    </td>
                    <td class="rp-td-center">
                        <span class="rp-score-main">{{ number_format($a->percentage,1) }}%</span>
                        <span class="rp-score-frac">{{ $a->score }}/{{ $a->total_marks }}</span>
                    </td>
                    <td class="rp-td-center">
                        @if($a->is_passed)
                        <span class="badge-pass">
                            <svg class="ico-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            Pass
                        </span>
                        @else
                        <span class="badge-fail">
                            <svg class="ico-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            Fail
                        </span>
                        @endif
                    </td>
                    <td class="rp-td-date">
                        {{ $a->submitted_at?->format('d M') }}<br>
                        <span class="rp-td-time">{{ $a->submitted_at?->format('H:i') }}</span>
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
            @else
            <div class="rp-noattempts">{{ __('lecturer.rp_no_attempts_yet') }}</div>
            @endif
        </div>
    </div>

    {{-- ── Quiz config ── --}}
    <div class="rp-section">
        <div class="rp-section-title">{{ __('lecturer.rp_quiz_config') }}</div>
        <div class="rp-grid-meta">
            @php
                $meta = array_values(array_filter([
                    ['label'=>__('lecturer.rp_meta_questions'),    'value'=>$quiz->total_questions,          'color'=>'#8b5cf6', 'icon'=>'M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                    ['label'=>__('lecturer.rp_meta_total_marks'),  'value'=>$quiz->total_marks,              'color'=>'#f59e0b', 'icon'=>'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z'],
                    ['label'=>__('lecturer.rp_meta_pass_pct'),     'value'=>$quiz->pass_percentage.'%',      'color'=>'#10b981', 'icon'=>'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                    ['label'=>__('lecturer.rp_meta_duration'),     'value'=>$quiz->duration_minutes ? $quiz->duration_minutes.' min' : __('lecturer.rp_unlimited'),'color'=>'#3b82f6','icon'=>'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                    ['label'=>__('lecturer.rp_meta_max_attempts'), 'value'=>$quiz->max_attempts ?? __('lecturer.rp_unlimited'),'color'=>'#6366f1','icon'=>'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15'],
                ]));
            @endphp
            @foreach($meta as $m)
            <div class="rp-meta-item">
                <div class="rp-meta-icon" style="background:{{ $m['color'] }}1a;">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" style="color:{{ $m['color'] }};">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $m['icon'] }}"/>
                    </svg>
                </div>
                <div>
                    <div class="rp-meta-label">{{ $m['label'] }}</div>
                    <div class="rp-meta-value">{{ $m['value'] }}</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    @elseif(!$quizzes->count())
    <div class="rp-empty">
        <svg style="opacity:.3;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg>
        <p class="rp-empty-title">No quizzes yet</p>
        <p class="rp-empty-sub">Create your first quiz to start seeing reports.</p>
        <a href="{{ route('filament.lecturer.resources.quizzes.create') }}" class="rp-empty-cta">
            + Create Quiz
        </a>
    </div>
    @else
    <div class="rp-empty">
        <svg style="opacity:.25;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg>
        <p class="rp-empty-sm">Select a quiz above to view its analytics.</p>
    </div>
    @endif

</div>
</x-filament-panels::page>
