<x-filament-panels::page>
<style>
:root {
    --e-card: #ffffff;
    --e-card-border: #e5e7eb;
    --e-card-border-sub: #f3f4f6;
    --e-card-hover: #f9fafb;
    --e-text: #111827;
    --e-text-sub: #6b7280;
    --e-text-muted: #9ca3af;
    --e-text-faint: #d1d5db;
    --e-row-stripe: #f9fafb;
}
.dark {
    --e-card: #111827;
    --e-card-border: #1f2937;
    --e-card-border-sub: #1f2937;
    --e-card-hover: #1f2937;
    --e-text: #f9fafb;
    --e-text-sub: #9ca3af;
    --e-text-muted: #6b7280;
    --e-text-faint: #374151;
    --e-row-stripe: #0f172a;
}
.e-card { background:var(--e-card); border:1px solid var(--e-card-border); border-radius:16px; }
.e-card-text { color:var(--e-text); }
.e-sub  { color:var(--e-text-sub); }
.e-muted { color:var(--e-text-muted); }

/* Reusable layout helpers */
.e-grid-4    { display:grid; grid-template-columns:repeat(4,1fr); gap:16px; }
.e-grid-2-1  { display:grid; grid-template-columns:2fr 1fr; gap:24px; }
.e-pad-20    { padding:20px; }
.e-flex-sb   { display:flex; align-items:center; justify-content:space-between; }
.e-hdr-row   { display:flex; align-items:center; justify-content:space-between; margin-bottom:12px; }
.e-stat-icon { width:32px; height:32px; border-radius:8px; display:flex; align-items:center; justify-content:center; }
.e-stat-label{ font-size:10px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:var(--e-text-muted); }
.e-stat-val  { font-size:24px; font-weight:800; color:var(--e-text); line-height:1; }
.e-stat-sub  { font-size:11px; color:var(--e-text-muted); margin-top:4px; }
.e-small-icon{ width:15px; height:15px; }

/* Payout row details */
.e-payout-row        { display:flex; align-items:center; gap:12px; padding:14px 20px; border-bottom:1px solid var(--e-card-border-sub); }
.e-payout-icon-wrap  { width:36px; height:36px; border-radius:50%; background:rgba(0,0,0,.06); display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.e-payout-icon       { width:15px; height:15px; }
.e-payout-details    { flex:1; min-width:0; }
.e-payout-title-row  { display:flex; align-items:center; gap:8px; margin-bottom:2px; }
.e-payout-amount     { font-size:14px; font-weight:700; color:var(--e-text); }
.e-payout-badge      { font-size:10px; font-weight:600; padding:2px 8px; border-radius:20px; }
.e-payout-gateway    { font-size:11px; color:var(--e-text-sub); overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.e-payout-proc       { font-size:10px; color:var(--e-text-muted); margin-top:2px; }
.e-payout-date-col   { text-align:right; flex-shrink:0; }
.e-payout-date-day   { font-size:11px; color:var(--e-text-muted); }
.e-payout-date-year  { font-size:10px; color:var(--e-text-faint); }

/* Section header */
.e-sec-hdr { padding:16px 20px; border-bottom:1px solid var(--e-card-border-sub); }
.e-sec-ttl  { font-size:13px; font-weight:700; color:var(--e-text); }
.e-sec-sub  { font-size:11px; color:var(--e-text-muted); margin-top:2px; }

/* Sale row quiz info */
.e-sale-title { font-size:.875rem; font-weight:600; color:var(--e-text); overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.e-sale-date  { font-size:.7rem; color:var(--e-text-muted); margin-top:2px; }

/* AI Wallet internals */
.e-aw-hdr        { display:flex; align-items:center; justify-content:space-between; margin-bottom:12px; position:relative; z-index:1; }
.e-aw-title-wrap { display:flex; align-items:center; gap:7px; }
.e-aw-icon-wrap  { width:28px; height:28px; border-radius:8px; background:rgba(167,139,250,.25); display:flex; align-items:center; justify-content:center; }
.e-aw-label      { font-size:10px; font-weight:700; letter-spacing:.1em; text-transform:uppercase; color:rgba(196,181,253,.8); }
.e-aw-generate   { font-size:10px; font-weight:600; color:rgba(167,139,250,1); background:rgba(139,92,246,.2); border:1px solid rgba(139,92,246,.3); padding:3px 10px; border-radius:20px; text-decoration:none; position:relative; z-index:2; }
.e-aw-balance    { position:relative; z-index:1; margin-bottom:14px; }
.e-aw-balance-row{ display:flex; align-items:baseline; gap:8px; }
.e-aw-amount     { font-size:32px; font-weight:800; color:#fff; line-height:1.1; }
.e-aw-unit       { font-size:13px; font-weight:600; color:rgba(196,181,253,.8); }
.e-aw-alloc      { font-size:11px; color:rgba(196,181,253,.6); margin-top:4px; }
.e-aw-topup      { position:relative; z-index:1; padding-top:12px; border-top:1px solid rgba(139,92,246,.25); }
.e-aw-btn        { display:inline-flex; align-items:center; gap:6px; font-size:12px; font-weight:600; color:#fff; background:rgba(139,92,246,.5); border:1px solid rgba(167,139,250,.4); padding:7px 16px; border-radius:20px; cursor:pointer; transition:background .15s; }
.e-aw-hint       { font-size:10px; color:rgba(196,181,253,.5); margin-top:8px; }

/* Empty payout state */
.e-empty-box        { padding:40px 20px; text-align:center; }
.e-empty-icon-wrap  { width:44px; height:44px; border-radius:50%; background:var(--e-card-border); display:flex; align-items:center; justify-content:center; margin:0 auto 12px; }
.e-empty-title      { font-size:13px; font-weight:500; color:var(--e-text-sub); }
.e-empty-sub        { font-size:11px; margin-top:4px; }

/* Available balance card */
.e-bal-card        { position:relative; overflow:hidden; border-radius:16px; padding:20px; color:white; background:linear-gradient(135deg,#6C2E63 0%,#9333EA 100%); }
.e-bal-orb-1       { position:absolute; right:-16px; top:-16px; width:96px; height:96px; border-radius:50%; background:rgba(255,255,255,.1); }
.e-bal-orb-2       { position:absolute; right:-8px; bottom:-24px; width:64px; height:64px; border-radius:50%; background:rgba(255,255,255,.08); }
.e-bal-label       { font-size:10px; font-weight:700; letter-spacing:.1em; text-transform:uppercase; opacity:.7; margin-bottom:4px; }
.e-bal-amount      { font-size:28px; font-weight:800; line-height:1.1; }
.e-bal-sub         { font-size:11px; opacity:.6; margin-top:4px; }
.e-bal-eligible-mt { margin-top:12px; }
.e-bal-badge       { display:inline-flex; align-items:center; gap:4px; font-size:11px; font-weight:600; background:rgba(255,255,255,.2); border-radius:20px; padding:3px 10px; }
.e-svg-11          { width:11px; height:11px; }
.e-svg-14          { width:14px; height:14px; }
.e-svg-13          { width:13px; height:13px; }
.e-svg-20          { width:20px; height:20px; }

/* Growth indicator */
.e-growth-row      { font-size:11px; margin-top:4px; display:flex; align-items:center; gap:3px; }

/* Sale row quiz icon */
.e-sale-icon       { background:linear-gradient(135deg,#6C2E63,#9333EA); }

/* Payout header totals */
.e-payout-totals    { text-align:right; }
.e-payout-total-amt { font-size:13px; font-weight:700; color:#16a34a; }
.e-payout-total-lbl { font-size:10px; color:var(--e-text-muted); }

/* Payout admin note */
.e-admin-note-label { font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.06em; margin-bottom:2px; }
.e-admin-note-body  { font-size:11px; color:var(--e-text); line-height:1.5; }

/* Payout stat icon backgrounds */
.e-icon-bg-indigo  { background:#eef2ff; }
.e-icon-bg-green   { background:#f0fdf4; }
.e-icon-bg-amber   { background:#fffbeb; }

/* payout status row backgrounds — dark-mode-safe using opacity */
.ps-paid    { background:rgba(16,185,129,.08); }
.ps-pending { background:rgba(245,158,11,.08); }
.ps-proc    { background:rgba(59,130,246,.08); }
.ps-fail    { background:rgba(239,68,68,.08); }

/* Recent Sales hover — works in both modes */
.sale-row:hover { background:var(--e-card-hover) !important; }

/* AI Wallet card */
.ai-wallet-card {
    border-radius:16px;
    overflow:hidden;
    background:linear-gradient(135deg,#451a6e 0%,#1e1040 100%);
    border:1px solid rgba(139,92,246,.35);
    padding:18px 20px 16px;
    position:relative;
}
.dark .ai-wallet-card {
    border-color:rgba(139,92,246,.45);
}
.ai-wallet-card::before {
    content:'';
    position:absolute;
    right:-20px; top:-20px;
    width:100px; height:100px;
    border-radius:50%;
    background:rgba(139,92,246,.15);
}
.ai-wallet-card::after {
    content:'';
    position:absolute;
    right:20px; bottom:-30px;
    width:70px; height:70px;
    border-radius:50%;
    background:rgba(167,139,250,.08);
}
</style>
<div class="space-y-6">

    {{-- ── Top stats ──────────────────────────────────────────────────── --}}
    <div class="e-grid-4">

        {{-- Available balance --}}
        <div class="e-bal-card">
            <div class="e-bal-orb-1"></div>
            <div class="e-bal-orb-2"></div>
            <p class="e-bal-label">{{ __('creator.earn_available_balance') }}</p>
            <p class="e-bal-amount">{{ $sym }}{{ number_format($pending, 2) }}</p>
            <p class="e-bal-sub">{{ __('creator.earn_ready_withdraw') }}</p>
            @if($pending >= 100)
            <div class="e-bal-eligible-mt">
                <span class="e-bal-badge">
                    <svg class="e-svg-11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                    Eligible for payout
                </span>
            </div>
            @endif
        </div>

        {{-- Total earned --}}
        <div class="e-card e-pad-20">
            <div class="e-hdr-row">
                <p class="e-stat-label">{{ __('creator.earn_total_earned') }}</p>
                <div class="e-stat-icon e-icon-bg-indigo">
                    <svg class="e-small-icon" fill="none" viewBox="0 0 24 24" stroke="#6366f1" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75"/></svg>
                </div>
            </div>
            <p class="e-stat-val">{{ $sym }}{{ number_format($totalEarned, 2) }}</p>
            <p class="e-stat-sub">{{ __('creator.earn_lifetime_earnings') }}</p>
        </div>

        {{-- This month --}}
        <div class="e-card e-pad-20">
            <div class="e-hdr-row">
                <p class="e-stat-label">{{ __('creator.earn_this_month') }}</p>
                <div class="e-stat-icon e-icon-bg-green">
                    <svg class="e-small-icon" fill="none" viewBox="0 0 24 24" stroke="#22c55e" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6L9 12.75l4.286-4.286a11.948 11.948 0 014.306 6.43l.776 2.898m0 0l3.182-5.511m-3.182 5.51l-5.511-3.181"/></svg>
                </div>
            </div>
            <p class="e-stat-val">{{ $sym }}{{ number_format($thisMonth, 2) }}</p>
            @if($monthGrowth !== null)
            <p class="e-growth-row" style="color:{{ $monthGrowth >= 0 ? '#16a34a' : '#ef4444' }};">
                @if($monthGrowth >= 0)
                    <svg class="e-svg-11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 10.5L12 3m0 0l7.5 7.5M12 3v18"/></svg>
                    +{{ $monthGrowth }}% vs last month
                @else
                    <svg class="e-svg-11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 13.5L12 21m0 0l-7.5-7.5M12 21V3"/></svg>
                    {{ $monthGrowth }}% vs last month
                @endif
            </p>
            @else
            <p class="e-stat-sub">{{ now()->format('F Y') }}</p>
            @endif
        </div>

        {{-- Total sales --}}
        <div class="e-card e-pad-20">
            <div class="e-hdr-row">
                <p class="e-stat-label">{{ __('creator.earn_total_sales') }}</p>
                <div class="e-stat-icon e-icon-bg-amber">
                    <svg class="e-small-icon" fill="none" viewBox="0 0 24 24" stroke="#f59e0b" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z"/></svg>
                </div>
            </div>
            <p class="e-stat-val">{{ $totalSales }}</p>
            <p class="e-stat-sub">
                @if($totalSales > 0) {{ __('creator.earn_avg_sale', ['avg' => $sym.number_format($totalEarned / $totalSales, 0)]) }} @else {{ __('creator.earn_no_sales_yet') }} @endif
            </p>
        </div>
    </div>

    {{-- ── Two-column: Sales + Payouts ────────────────────────────────── --}}
    <div class="e-grid-2-1">

        {{-- Recent Sales (wider) --}}
        <div class="e-card" style="overflow:hidden;"> {{-- keep: overflow needed for border-radius --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-800">
                <div>
                    <h3 class="font-semibold text-gray-900 dark:text-white">{{ __('creator.earn_recent_sales') }}</h3>
                    <p class="text-xs text-gray-400 mt-0.5">{{ __('creator.earn_last_20') }}</p>
                </div>
                @if($totalPaidOut > 0)
                <span class="text-xs font-medium text-gray-500 bg-gray-100 dark:bg-gray-800 px-2.5 py-1 rounded-full">
                    {{ $sym }}{{ number_format($totalPaidOut, 2) }} {{ __('creator.earn_paid_out') }}
                </span>
                @endif
            </div>

            @forelse($orders as $order)
            <div class="sale-row flex items-center gap-4 px-6 py-3.5 transition-colors" style="border-bottom:1px solid var(--e-card-border-sub);">
                {{-- Quiz icon --}}
                <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0"
                     class="e-sale-icon">
                    <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z"/>
                    </svg>
                </div>
                {{-- Quiz info --}}
                <div class="flex-1 min-w-0">
                    <p class="e-sale-title">{{ $order->quiz?->title ?? 'Deleted Quiz' }}</p>
                    <p class="e-sale-date">{{ $order->paid_at?->format('d M Y, h:i A') ?? '—' }}</p>
                </div>
                {{-- Amounts --}}
                <div class="text-right flex-shrink-0">
                    <p class="text-sm font-bold text-green-600">{{ $sym }}{{ number_format($order->creator_earning, 2) }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">
                        of {{ $sym }}{{ number_format($order->amount, 2) }}
                        <span class="text-red-400 ml-1">-{{ $sym }}{{ number_format($order->platform_commission, 2) }} fee</span>
                    </p>
                </div>
            </div>
            @empty
            <div class="flex flex-col items-center justify-center py-16 text-center">
                <div class="w-14 h-14 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center mb-3">
                    <svg class="w-7 h-7 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z"/>
                    </svg>
                </div>
                <p class="font-medium text-gray-500">{{ __('creator.earn_no_sales_yet') }}</p>
                <p class="text-sm text-gray-400 mt-1">{{ __('creator.earn_no_sales_sub') }}</p>
            </div>
            @endforelse
        </div>

        {{-- Right column: Payout history + wallet --}}
        <div class="space-y-4">

            {{-- AI Wallet card (always shown) --}}
            <div class="ai-wallet-card">
                {{-- Header row --}}
                <div class="e-aw-hdr">
                    <div class="e-aw-title-wrap">
                        <div class="e-aw-icon-wrap">
                            <svg class="e-svg-14" fill="none" viewBox="0 0 24 24" stroke="rgba(196,181,253,1)" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/></svg>
                        </div>
                        <span class="e-aw-label">{{ __('creator.earn_ai_wallet') }}</span>
                    </div>
                    <a href="{{ \App\Filament\Creator\Pages\AiGenerator::getUrl() }}" class="e-aw-generate">
                        {{ __('creator.earn_generate_ai') }} ↗
                    </a>
                </div>

                {{-- Token balance --}}
                <div class="e-aw-balance">
                    <div class="e-aw-balance-row">
                        <p class="e-aw-amount">{{ number_format($tokensRemaining) }}</p>
                        <p class="e-aw-unit">{{ __('creator.earn_tokens_left') }}</p>
                    </div>
                    @if($monthlyAllocation > 0)
                    <p class="e-aw-alloc">
                        {{ number_format($monthlyAllocation) }} {{ __('creator.earn_tokens_per_month') }}
                    </p>
                    @else
                    <p class="e-aw-alloc">
                        @if($tokensRemaining > 0) Ready for AI quiz generation @else Add tokens to use AI generation @endif
                    </p>
                    @endif
                </div>

                {{-- Top-up button --}}
                <div class="e-aw-topup">
                    <button type="button" wire:click="mountAction('topup_tokens')" class="e-aw-btn">
                        <svg class="e-svg-13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        {{ __('creator.earn_get_more_tokens') }}
                    </button>
                    <p class="e-aw-hint">{{ __('creator.earn_token_equals') }}</p>
                </div>
            </div>

            {{-- Payout history --}}
            <div class="e-card" style="overflow:hidden;"> {{-- keep: overflow needed --}}

                {{-- Header --}}
                <div class="e-sec-hdr">
                    <div class="e-flex-sb">
                        <div>
                            <p class="e-sec-ttl">{{ __('creator.earn_payout_history') }}</p>
                            <p class="e-sec-sub">{{ $payouts->count() }} {{ __('creator.earn_requests') }}</p>
                        </div>
                        @if($totalPaidOut > 0)
                        <div class="e-payout-totals">
                            <p class="e-payout-total-amt">{{ $sym }}{{ number_format($totalPaidOut, 2) }}</p>
                            <p class="e-payout-total-lbl">{{ __('creator.earn_total_paid_out') }}</p>
                        </div>
                        @endif
                    </div>
                </div>

                @forelse($payouts as $payout)
                @php
                    $statusConfig = match($payout->status) {
                        'paid'       => ['cls' => 'ps-paid',    'icon_color' => '#16a34a', 'badge_bg' => '#dcfce7', 'badge_color' => '#15803d', 'label' => __('creator.earn_status_paid')],
                        'pending'    => ['cls' => 'ps-pending', 'icon_color' => '#d97706', 'badge_bg' => '#fef3c7', 'badge_color' => '#92400e', 'label' => __('creator.earn_status_pending')],
                        'processing' => ['cls' => 'ps-proc',    'icon_color' => '#2563eb', 'badge_bg' => '#dbeafe', 'badge_color' => '#1d4ed8', 'label' => __('creator.earn_status_processing')],
                        default      => ['cls' => 'ps-fail',    'icon_color' => '#dc2626', 'badge_bg' => '#fee2e2', 'badge_color' => '#b91c1c', 'label' => __('creator.earn_status_failed')],
                    };
                @endphp
                <div class="{{ $statusConfig['cls'] }} e-payout-row">

                    {{-- Icon --}}
                    <div class="e-payout-icon-wrap">
                        @if($payout->status === 'paid')
                            <svg class="e-payout-icon" fill="none" viewBox="0 0 24 24" stroke="{{ $statusConfig['icon_color'] }}" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                        @elseif($payout->status === 'pending')
                            <svg class="e-payout-icon" fill="none" viewBox="0 0 24 24" stroke="{{ $statusConfig['icon_color'] }}" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        @elseif($payout->status === 'processing')
                            <svg class="e-payout-icon" fill="none" viewBox="0 0 24 24" stroke="{{ $statusConfig['icon_color'] }}" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                        @else
                            <svg class="e-payout-icon" fill="none" viewBox="0 0 24 24" stroke="{{ $statusConfig['icon_color'] }}" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        @endif
                    </div>

                    {{-- Details --}}
                    <div class="e-payout-details">
                        <div class="e-payout-title-row">
                            <p class="e-payout-amount">{{ $sym }}{{ number_format($payout->amount, 2) }}</p>
                            <span class="e-payout-badge" style="background:{{ $statusConfig['badge_bg'] }};color:{{ $statusConfig['badge_color'] }};">
                                {{ $statusConfig['label'] }}
                            </span>
                        </div>
                        <p class="e-payout-gateway">{{ $payout->gateway }}</p>
                        @if($payout->processed_at)
                            <p class="e-payout-proc">Processed {{ $payout->processed_at->format('d M Y') }}</p>
                        @endif
                        @if($payout->admin_note)
                            <div style="margin-top:6px;padding:6px 10px;border-radius:8px;background:{{ $payout->status === 'failed' ? 'rgba(239,68,68,.08)' : 'rgba(99,102,241,.07)' }};border-left:3px solid {{ $payout->status === 'failed' ? '#ef4444' : '#6366f1' }};">
                                <p class="e-admin-note-label" style="color:{{ $payout->status === 'failed' ? '#dc2626' : '#6366f1' }};">{{ __('creator.earn_admin_note') }}</p>
                                <p class="e-admin-note-body">{{ $payout->admin_note }}</p>
                            </div>
                        @endif
                    </div>

                    {{-- Date --}}
                    <div class="e-payout-date-col">
                        <p class="e-payout-date-day">{{ $payout->requested_at->format('d M') }}</p>
                        <p class="e-payout-date-year">{{ $payout->requested_at->format('Y') }}</p>
                    </div>
                </div>
                @empty
                <div class="e-empty-box">
                    <div class="e-empty-icon-wrap">
                        <svg class="e-svg-20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75"/></svg>
                    </div>
                    <p class="e-empty-title">{{ __('creator.earn_no_payouts_yet') }}</p>
                    @if($pending >= 100)
                    <p class="e-empty-sub" style="color:#6366f1;">{{ $sym }}{{ number_format($pending, 2) }} available to withdraw</p> {{-- unique color --}}
                    @else
                    <p class="e-empty-sub e-muted">Min. {{ $sym }}100 required to request</p>
                    @endif
                </div>
                @endforelse
            </div>
        </div>
    </div>

</div>
</x-filament-panels::page>
