@php
    $user     = auth()->user();
    $sub      = $user?->activeSubscription()->with('plan')->first();
    $plan     = $sub?->plan;
    $planName = $plan?->name ?? 'Free';
    $planSlug = strtolower($planName);

    $totalCredits = max($user->ai_credits_free_remaining + $user->ai_credits_used, 1);
    $creditPct    = min(100, round(($user->ai_credits_free_remaining / $totalCredits) * 100));
    $barRgb       = $creditPct > 50 ? '74,222,128' : ($creditPct > 20 ? '250,204,21' : '248,113,113');

    [$cardBg, $cardBorder, $glowColor, $badgeBg, $badgeColor] = match($planSlug) {
        'pro'      => [
            'linear-gradient(145deg,rgba(109,40,217,0.4) 0%,rgba(49,10,120,0.25) 60%,rgba(10,5,30,0.2) 100%)',
            'rgba(139,92,246,0.45)', 'rgba(139,92,246,0.18)',
            'rgba(139,92,246,0.3)', '#ddd6fe',
        ],
        'business' => [
            'linear-gradient(145deg,rgba(180,83,9,0.4) 0%,rgba(120,40,5,0.25) 60%,rgba(10,5,0,0.2) 100%)',
            'rgba(245,158,11,0.45)', 'rgba(245,158,11,0.15)',
            'rgba(245,158,11,0.3)', '#fde68a',
        ],
        default    => [
            'linear-gradient(145deg,rgba(79,70,229,0.3) 0%,rgba(49,46,129,0.18) 60%,rgba(10,8,30,0.2) 100%)',
            'rgba(99,102,241,0.35)', 'rgba(99,102,241,0.15)',
            'rgba(255,255,255,0.12)', 'rgba(255,255,255,0.85)',
        ],
    };
@endphp

<style>
.spc-wrap{padding:0 12px 16px 12px}
.spc-card{border-radius:16px;padding:14px 16px;display:flex;flex-direction:column;gap:12px;position:relative;overflow:hidden}
.spc-glow{position:absolute;top:-24px;right:-24px;width:90px;height:90px;border-radius:9999px;filter:blur(28px);pointer-events:none;z-index:0}
.spc-inner{position:relative;z-index:1;display:flex;flex-direction:column;gap:12px}
.spc-header{display:flex;align-items:center;justify-content:space-between}
.spc-plan-label{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.1em;color:rgba(255,255,255,0.45)}
.spc-badge{display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:9999px;font-size:11px;font-weight:700}
.spc-credits{display:flex;flex-direction:column;gap:6px}
.spc-credits-row{display:flex;align-items:center;justify-content:space-between;font-size:12px}
.spc-credits-label{display:flex;align-items:center;gap:6px;color:rgba(255,255,255,0.6)}
.spc-credits-val{font-weight:600;color:#fff}
.spc-credits-total{color:rgba(255,255,255,0.35);font-weight:400}
.spc-bar-track{height:5px;width:100%;border-radius:9999px;background:rgba(255,255,255,0.1);overflow:hidden}
.spc-bar-fill{height:100%;border-radius:9999px}
.spc-divider{border-top:1px solid rgba(255,255,255,0.07)}
.spc-stats{display:flex;flex-direction:column;gap:9px;font-size:12px}
.spc-stat-row{display:flex;align-items:center;justify-content:space-between}
.spc-stat-label{display:flex;align-items:center;gap:6px;color:rgba(255,255,255,0.55)}
.spc-stat-val{font-weight:600;color:#fff}
.spc-upgrade{display:flex;align-items:center;justify-content:center;gap:6px;width:100%;font-size:12px;font-weight:600;padding:8px 0;border-radius:10px;color:#fff;background:linear-gradient(135deg,#6366f1,#4338ca);box-shadow:0 4px 14px rgba(99,102,241,0.35);text-decoration:none;transition:opacity 0.15s}
.spc-icon-sm{width:10px;height:10px}
.spc-icon-md{width:14px;height:14px;flex-shrink:0}
.spc-icon-upgrade{width:13px;height:13px}
</style>

<div class="spc-wrap">
    <div class="spc-card" style="border:1px solid {{ $cardBorder }};background:{{ $cardBg }};box-shadow:0 0 32px {{ $glowColor }},inset 0 1px 0 rgba(255,255,255,0.07);">
        {{-- Glow blob --}}
        <div class="spc-glow" style="background:{{ $glowColor }}"></div>

        {{-- Content wrapper above blob --}}
        <div class="spc-inner">

            {{-- Header: Plan label + badge --}}
            <div class="spc-header">
                <span class="spc-plan-label">
                    {{ __('creator.sidebar_current_plan') }}
                </span>
                <span class="spc-badge" style="background:{{ $badgeBg }};color:{{ $badgeColor }}">
                    @if($planSlug === 'business')
                        <x-heroicon-s-star class="spc-icon-sm" />
                    @elseif($planSlug === 'pro')
                        <x-heroicon-s-bolt class="spc-icon-sm" />
                    @endif
                    {{ $planName }}
                </span>
            </div>

            {{-- AI Credits --}}
            <div class="spc-credits">
                <div class="spc-credits-row">
                    <span class="spc-credits-label">
                        <x-heroicon-o-sparkles style="color:#facc15" class="spc-icon-md" />
                        {{ __('creator.sidebar_ai_credits') }}
                    </span>
                    <span class="spc-credits-val">
                        {{ $user->ai_credits_free_remaining }}<span class="spc-credits-total"> / {{ $totalCredits }}</span>
                    </span>
                </div>
                <div class="spc-bar-track">
                    <div class="spc-bar-fill" style="width:{{ $creditPct }}%;background:rgb({{ $barRgb }})"></div>
                </div>
            </div>

            {{-- Divider --}}
            <div class="spc-divider"></div>

            {{-- Stats --}}
            <div class="spc-stats">
                <div class="spc-stat-row">
                    <span class="spc-stat-label">
                        <x-heroicon-o-arrow-trending-up style="color:#60a5fa" class="spc-icon-md" />
                        {{ __('creator.sidebar_commission') }}
                    </span>
                    <span class="spc-stat-val">{{ $plan ? $plan->commission_rate . '%' : '—' }}</span>
                </div>

                <div class="spc-stat-row">
                    <span class="spc-stat-label">
                        <x-heroicon-o-rectangle-stack style="color:#a78bfa" class="spc-icon-md" />
                        {{ __('creator.sidebar_quizzes') }}
                    </span>
                    <span class="spc-stat-val">{{ $plan ? ($plan->max_published_quizzes ?? '∞') : '—' }}</span>
                </div>

                @if($sub?->current_period_end && $planSlug !== 'free')
                    <div class="spc-stat-row">
                        <span class="spc-stat-label">
                            <x-heroicon-o-calendar style="color:#34d399" class="spc-icon-md" />
                            {{ __('creator.sidebar_renews') }}
                        </span>
                        <span class="spc-stat-val">{{ $sub->current_period_end->format('d M Y') }}</span>
                    </div>
                @endif
            </div>

            {{-- Upgrade CTA --}}
            @if($planSlug === 'free' || !$plan)
                <a href="{{ route('pricing') }}"
                   class="spc-upgrade"
                   onmouseover="this.style.opacity='0.85'"
                   onmouseout="this.style.opacity='1'">
                    <x-heroicon-s-bolt class="spc-icon-upgrade" />
                    {{ __('creator.ai_btn_upgrade_plan') }}
                </a>
            @endif

        </div>
    </div>
</div>
