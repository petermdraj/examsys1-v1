@php
$steps = [
    1 => ['icon' => 'check-badge',   'label' => 'Requirements', 'sub' => 'Server checks'],
    2 => ['icon' => 'circle-stack',  'label' => 'Database',     'sub' => 'Connection setup'],
    3 => ['icon' => 'cog-6-tooth',   'label' => 'Application',  'sub' => 'App & admin config'],
    4 => ['icon' => 'rocket-launch', 'label' => 'Install',      'sub' => 'Run setup'],
];

$svgPaths = [
    'check-badge'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.745 3.745 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.745 3.745 0 013.296-1.043A3.745 3.745 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.745 3.745 0 013.296 1.043 3.745 3.745 0 011.043 3.296A3.745 3.745 0 0121 12z"/>',
    'circle-stack'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 5.625c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125"/>',
    'cog-6-tooth'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>',
    'envelope'      => '<path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/>',
    'credit-card'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z"/>',
    'rocket-launch' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.59 14.37a6 6 0 01-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 006.16-12.12A14.98 14.98 0 009.631 8.41m5.96 5.96a14.926 14.926 0 01-5.841 2.58m-.119-8.54a6 6 0 00-7.381 5.84h4.8m2.581-5.84a14.927 14.927 0 00-2.58 5.84m2.699 2.7c-.103.021-.207.041-.311.06a15.09 15.09 0 01-2.448-2.448 14.9 14.9 0 01.06-.312m-2.24 2.39a4.493 4.493 0 00-1.757 4.306 4.493 4.493 0 004.306-1.758M16.5 9a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"/>',
];
@endphp

{{-- ─── Main card ─────────────────────────────────────────────────────────── --}}
<div class="bg-white rounded-2xl shadow-2xl shadow-black/40 overflow-hidden flex" style="min-height:600px">

    {{-- ══════════════════════════════════
         SIDEBAR
    ══════════════════════════════════ --}}
    <aside class="w-60 bg-slate-900 flex-shrink-0 flex flex-col p-5">
        <nav class="flex-1 mt-1">
            <ul>
                @foreach($steps as $num => $s)
                @php
                    $isDone   = $num < $step;
                    $isActive = $num === $step;
                    $isFuture = $num > $step;
                    $isLast   = $num === count($steps);
                @endphp
                <li class="relative flex items-start gap-3">

                    {{-- Connector line between steps --}}
                    @if(!$isLast)
                    <div class="absolute left-[18px] top-9 bottom-0 w-0.5
                        {{ $isDone ? 'bg-violet-500/40' : 'bg-white/8' }}"
                        style="height: calc(100% - 4px)"></div>
                    @endif

                    <div class="flex-shrink-0 pt-2.5 pb-4 relative">
                        {{-- Step icon bubble --}}
                        <div class="w-9 h-9 rounded-full flex items-center justify-center
                            @if($isDone) bg-violet-500
                            @elseif($isActive) bg-violet-600 ring-2 ring-violet-400 ring-offset-2 ring-offset-slate-900
                            @else bg-white/8
                            @endif">
                            @if($isDone)
                                <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                                </svg>
                            @else
                                <svg class="w-4 h-4 {{ $isActive ? 'text-white' : 'text-white/35' }}"
                                     fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    {!! $svgPaths[$s['icon']] !!}
                                </svg>
                            @endif
                        </div>
                    </div>

                    <div class="pt-3 pb-4">
                        <p class="text-sm font-semibold leading-tight
                            @if($isActive) text-white
                            @elseif($isDone) text-violet-300
                            @else text-white/35
                            @endif">{{ $s['label'] }}</p>
                        <p class="text-xs mt-0.5
                            @if($isActive) text-violet-300
                            @else text-white/25
                            @endif">{{ $s['sub'] }}</p>
                    </div>
                </li>
                @endforeach
            </ul>
        </nav>

        {{-- Progress bar --}}
        <div class="pt-5 border-t border-white/8 mt-4">
            <div class="flex justify-between text-xs text-white/35 mb-2">
                <span class="font-medium">Progress</span>
                <span>{{ $installed ? $totalSteps : max(0, $step - 1) }}&thinsp;/&thinsp;{{ $totalSteps }}</span>
            </div>
            <div class="h-1.5 bg-white/10 rounded-full overflow-hidden">
                <div class="h-full rounded-full transition-all duration-500"
                     style="width:{{ ($installed ? 1 : (max(0, $step - 1) / $totalSteps)) * 100 }}%;background:linear-gradient(to right,#8b5cf6,#c084fc)"></div>
            </div>
        </div>
    </aside>

    {{-- ══════════════════════════════════
         CONTENT AREA
    ══════════════════════════════════ --}}
    <div class="flex-1 p-8 sm:p-10 overflow-y-auto">


{{-- ───────────────────────────────────────────────────────────
     STEP 1 — System Requirements
─────────────────────────────────────────────────────────── --}}
@if($step === 1)
<div class="step-content">
    <p class="text-xs font-bold text-violet-600 uppercase tracking-widest mb-1">Step 1 of {{ $totalSteps }}</p>
    <h2 class="text-2xl font-bold text-slate-900 tracking-tight">System Requirements</h2>
    <p class="text-slate-500 text-sm mt-1 mb-6">Verifying your server meets all prerequisites.</p>

    @php $hardFail = collect($requirements)->contains(fn($r) => !$r['pass'] && !($r['warn'] ?? false)); @endphp

    <div class="space-y-2 mb-6">
        @foreach($requirements as $req)
        @php $pass = $req['pass']; $warn = $req['warn'] ?? false; @endphp
        <div class="flex items-center gap-3 px-4 py-3 rounded-xl border text-sm
            {{ $pass ? 'bg-emerald-50 border-emerald-100' : ($warn ? 'bg-amber-50 border-amber-100' : 'bg-red-50 border-red-100') }}">
            <div class="flex-shrink-0 w-6 h-6 rounded-full flex items-center justify-center
                {{ $pass ? 'bg-emerald-500' : ($warn ? 'bg-amber-400' : 'bg-red-500') }}">
                @if($pass)
                    <svg class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                @elseif($warn)
                    <svg class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                @else
                    <svg class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                @endif
            </div>
            <div class="flex-1 min-w-0">
                <span class="font-semibold {{ $pass ? 'text-emerald-800' : ($warn ? 'text-amber-800' : 'text-red-800') }}">{{ $req['label'] }}</span>
                @if(!$pass && isset($req['note']))
                    <p class="text-xs mt-0.5 {{ $warn ? 'text-amber-600' : 'text-red-600' }}">{{ $req['note'] }}</p>
                @endif
            </div>
            <span class="flex-shrink-0 text-xs font-bold {{ $pass ? 'text-emerald-600' : ($warn ? 'text-amber-500' : 'text-red-600') }}">
                {{ $pass ? 'PASS' : ($warn ? 'WARN' : 'FAIL') }}
            </span>
        </div>
        @endforeach
    </div>

    @if($hardFail)
        <div class="flex items-start gap-3 p-4 bg-red-50 border border-red-200 rounded-xl mb-6 text-sm text-red-700">
            <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
            <p>Some required extensions are missing. Please ask your hosting provider to enable them before continuing.</p>
        </div>
    @else
        <div class="flex items-center gap-3 p-4 bg-emerald-50 border border-emerald-200 rounded-xl mb-6 text-sm text-emerald-700 font-semibold">
            <svg class="w-5 h-5 text-emerald-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            All critical requirements met — you can proceed!
        </div>
    @endif

    <div class="flex justify-end">
        <button wire:click="next" {{ $hardFail ? 'disabled' : '' }}
            class="inline-flex items-center gap-2 px-6 py-2.5 bg-violet-600 hover:bg-violet-700 disabled:opacity-40 disabled:cursor-not-allowed text-white font-semibold rounded-xl text-sm transition-colors shadow-lg shadow-violet-200/60">
            Continue
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
        </button>
    </div>
</div>
@endif


{{-- ───────────────────────────────────────────────────────────
     STEP 2 — Database
─────────────────────────────────────────────────────────── --}}
@if($step === 2)
<div class="step-content">
    <p class="text-xs font-bold text-violet-600 uppercase tracking-widest mb-1">Step 2 of {{ $totalSteps }}</p>
    <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Database Configuration</h2>
    <p class="text-slate-500 text-sm mt-1 mb-6">Enter your MySQL credentials. The database must already exist.</p>

    @if($dbError)
        <div class="flex items-start gap-3 p-4 bg-red-50 border border-red-200 rounded-xl mb-5 text-sm text-red-700">
            <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
            <p>{{ $dbError }}</p>
        </div>
    @endif
    @if($dbTested && !$dbError)
        <div class="flex items-center gap-3 p-4 bg-emerald-50 border border-emerald-200 rounded-xl mb-5 text-sm text-emerald-700 font-semibold">
            <svg class="w-5 h-5 text-emerald-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Connection successful!
        </div>
    @endif

    <div class="space-y-4">
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-1.5">Host</label>
                <input wire:model="dbHost" type="text" placeholder="127.0.0.1"
                    class="inp w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-sm text-slate-900">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-1.5">Port</label>
                <input wire:model="dbPort" type="text" placeholder="3306"
                    class="inp w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-sm text-slate-900">
            </div>
        </div>
        <div>
            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-1.5">Database Name</label>
            <input wire:model="dbName" type="text" placeholder="examsys1"
                class="inp w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-sm text-slate-900">
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-1.5">Username</label>
                <input wire:model="dbUser" type="text" placeholder="root"
                    class="inp w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-sm text-slate-900">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-1.5">Password</label>
                <input wire:model="dbPassword" type="password" placeholder="••••••••"
                    class="inp w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-sm text-slate-900">
            </div>
        </div>
    </div>

    <div class="flex items-center gap-3 mt-8">
        <button wire:click="prev"
            class="inline-flex items-center gap-2 px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl text-sm transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
            Back
        </button>
        <button wire:click="testDatabase" wire:loading.attr="disabled" wire:target="testDatabase"
            class="inline-flex items-center gap-2 px-5 py-2.5 border border-violet-300 hover:bg-violet-50 text-violet-700 font-semibold rounded-xl text-sm transition-colors">
            <span wire:loading.remove wire:target="testDatabase">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.348a1.125 1.125 0 010 1.971l-11.54 6.347a1.125 1.125 0 01-1.667-.985V5.653z"/></svg>
            </span>
            <span wire:loading wire:target="testDatabase">
                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
            </span>
            Test Connection
        </button>
        <button wire:click="next"
            class="ml-auto inline-flex items-center gap-2 px-6 py-2.5 bg-violet-600 hover:bg-violet-700 text-white font-semibold rounded-xl text-sm transition-colors shadow-lg shadow-violet-200/60">
            Continue
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
        </button>
    </div>
</div>
@endif


{{-- ───────────────────────────────────────────────────────────
     STEP 3 — App Config
─────────────────────────────────────────────────────────── --}}
@if($step === 3)
<div class="step-content">
    <p class="text-xs font-bold text-violet-600 uppercase tracking-widest mb-1">Step 3 of {{ $totalSteps }}</p>
    <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Application Setup</h2>
    <p class="text-slate-500 text-sm mt-1 mb-6">Configure your app's name, URL, and create the super admin account.</p>

    <div class="space-y-4">
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-1.5">App Name</label>
                <input wire:model="appName" type="text" placeholder="ExamSys"
                    class="inp w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-sm text-slate-900">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-1.5">App URL</label>
                <input wire:model="appUrl" type="url" placeholder="https://yoursite.com"
                    class="inp w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-sm text-slate-900">
            </div>
        </div>

        <div class="relative my-2">
            <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-slate-200"></div></div>
            <div class="relative flex justify-center"><span class="px-3 bg-white text-xs font-bold text-slate-400 uppercase tracking-widest">Admin Account</span></div>
        </div>

        <div>
            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-1.5">Full Name</label>
            <input wire:model="adminName" type="text" placeholder="Admin"
                class="inp w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-sm text-slate-900">
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-1.5">Email Address</label>
                <input wire:model="adminEmail" type="email" placeholder="admin@yoursite.com"
                    class="inp w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-sm text-slate-900">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-1.5">Password <span class="text-slate-400 font-normal normal-case">(min 8 chars)</span></label>
                <input wire:model="adminPassword" type="password" placeholder="••••••••"
                    class="inp w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-sm text-slate-900">
            </div>
        </div>

        {{-- Demo Data --}}
        <div class="relative my-2 pt-1">
            <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-slate-200"></div></div>
            <div class="relative flex justify-center"><span class="px-3 bg-white text-xs font-bold text-slate-400 uppercase tracking-widest">Demo Data</span></div>
        </div>

        {{-- Toggle --}}
        <div class="flex items-center justify-between p-4 bg-slate-50 border border-slate-200 rounded-xl">
            <div>
                <p class="text-sm font-semibold text-slate-800">Install sample data</p>
                <p class="text-xs text-slate-500 mt-0.5">Pre-populate your platform with categories, quizzes, and users so it's ready to demo.</p>
            </div>
            <button type="button" wire:click="toggleDemo"
                class="relative flex-shrink-0 w-12 h-6 rounded-full transition-colors duration-200 focus:outline-none
                    {{ $installDemo ? 'bg-violet-600' : 'bg-slate-300' }}">
                <span class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform duration-200
                    {{ $installDemo ? 'translate-x-6' : 'translate-x-0' }}"></span>
            </button>
        </div>

        @if($installDemo)
        <div class="grid grid-cols-3 gap-3">
            @foreach([
                ['value' => 'competition', 'label' => 'Competition Exams', 'sub' => 'UPSC, SSC, Banking, Railway', 'icon' => '🏆'],
                ['value' => 'school',      'label' => 'School Portal',      'sub' => 'Math, Science, English, History', 'icon' => '🏫'],
                ['value' => 'professional','label' => 'Professional Tests', 'sub' => 'PHP, JS, PM, Marketing, HR', 'icon' => '💼'],
            ] as $demo)
            <button type="button" wire:click="setDemoType('{{ $demo['value'] }}')"
                class="text-left p-4 rounded-xl border-2 transition-all
                    {{ $demoType === $demo['value'] ? 'border-violet-500 bg-violet-50' : 'border-slate-200 bg-slate-50 hover:border-slate-300' }}">
                <div class="text-2xl mb-2">{{ $demo['icon'] }}</div>
                <p class="text-sm font-bold {{ $demoType === $demo['value'] ? 'text-violet-800' : 'text-slate-800' }}">{{ $demo['label'] }}</p>
                <p class="text-xs mt-0.5 {{ $demoType === $demo['value'] ? 'text-violet-500' : 'text-slate-500' }}">{{ $demo['sub'] }}</p>
                @if($demoType === $demo['value'])
                <div class="mt-2 inline-flex items-center gap-1 text-xs font-bold text-violet-600">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                    Selected
                </div>
                @endif
            </button>
            @endforeach
        </div>
        @endif
    </div>

    <div class="flex items-center gap-3 mt-8">
        <button wire:click="prev"
            class="inline-flex items-center gap-2 px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl text-sm transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
            Back
        </button>
        <button wire:click="next"
            class="ml-auto inline-flex items-center gap-2 px-6 py-2.5 bg-violet-600 hover:bg-violet-700 text-white font-semibold rounded-xl text-sm transition-colors shadow-lg shadow-violet-200/60">
            Continue
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
        </button>
    </div>
</div>
@endif


{{-- ───────────────────────────────────────────────────────────
     STEP 6 — Install
─────────────────────────────────────────────────────────── --}}
@if($step === 4)
@php
/*
 * Build the install step definitions for the progress UI.
 * Status is derived purely from server-side $currentStep — no Alpine needed.
 *
 * Status logic per row:
 *   done    → step number < $currentStep
 *   running → step number === $currentStep (and no error)
 *   error   → step number === $currentStep AND $installError
 *   pending → step number > $currentStep
 */
$installStepDefs = array_filter([
    1 => 'Writing configuration',
    2 => 'Running database migrations',
    3 => 'Seeding default data',
    4 => 'Creating admin account',
    5 => 'Linking storage directory',
    6 => $installDemo ? 'Installing demo data (' . $demoType . ')' : null,
    7 => 'Finalising setup',
]);

$totalInstallSteps = count($installStepDefs);

// Completed count for progress bar (skip step 0 = not started)
$completedSteps = max(0, $currentStep - 1);
$progressPct    = $totalInstallSteps > 0
    ? min(100, (int) round($completedSteps / $totalInstallSteps * 100))
    : 0;
@endphp

<div class="step-content">

{{-- ══════════════════════════════════════════════════════════
     ✅ SUCCESS — shown once $installed === true
══════════════════════════════════════════════════════════ --}}
@if($installed)
<div class="flex flex-col items-center text-center py-6">
    <div class="relative mb-6">
        <div class="w-24 h-24 rounded-full flex items-center justify-center ring-8 ring-emerald-50"
             style="background:linear-gradient(135deg,#d1fae5,#a7f3d0)">
            <svg class="w-12 h-12 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
            </svg>
        </div>
        <span class="absolute -top-1 -right-1 w-3 h-3 rounded-full bg-violet-400 opacity-80"></span>
        <span class="absolute -bottom-0 -left-2 w-2 h-2 rounded-full bg-fuchsia-400 opacity-70"></span>
    </div>
    <h2 class="text-2xl font-bold text-slate-900 mb-2">Installation Complete!</h2>
    <p class="text-slate-500 text-sm max-w-sm leading-relaxed">Your platform is live and ready to go. Log in to the admin panel to get started.</p>
    <div class="flex gap-3 mt-7">
        <a href="/admin"
           class="inline-flex items-center gap-2 px-6 py-3 bg-violet-600 hover:bg-violet-700 text-white font-semibold rounded-xl text-sm transition-colors shadow-lg shadow-violet-200">
            Open Admin Panel
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
        </a>
        <a href="/"
           class="inline-flex items-center gap-2 px-6 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl text-sm transition-colors">
            Homepage
        </a>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════
     ⏳ PROGRESS — shown while $currentStep >= 1 && !$installed
     wire:poll calls advanceStep() every 800 ms; stops when
     $installed or $installError is true.
══════════════════════════════════════════════════════════ --}}
@elseif($currentStep >= 1)
<div @if(!$installError && !$installed) wire:poll.800ms="advanceStep" @endif>

    <p class="text-xs font-bold text-violet-600 uppercase tracking-widest mb-1">Step 4 of {{ $totalSteps }}</p>
    <h2 class="text-2xl font-bold text-slate-900 tracking-tight mb-1">Installing…</h2>
    <p class="text-slate-500 text-sm mb-5">Please keep this window open while we set up your platform.</p>

    {{-- Progress bar --}}
    <div class="h-2 bg-slate-100 rounded-full overflow-hidden mb-5">
        <div class="h-full rounded-full transition-all duration-700 ease-out"
             style="background:linear-gradient(90deg,#7c3aed,#c026d3); width:{{ $progressPct }}%">
        </div>
    </div>

    {{-- Step list --}}
    <div class="space-y-2 mb-5">
        @foreach($installStepDefs as $n => $label)
        @php
            if ($installError && $n === $currentStep) {
                $status = 'error';
            } elseif ($n < $currentStep) {
                $status = 'done';
            } elseif ($n === $currentStep) {
                $status = 'running';
            } else {
                $status = 'pending';
            }
        @endphp
        <div class="flex items-center gap-3 px-4 py-3 rounded-xl border
            @if($status === 'done')    bg-emerald-50 border-emerald-100
            @elseif($status === 'running') bg-violet-50 border-violet-200 shadow-sm
            @elseif($status === 'error')   bg-red-50 border-red-100
            @else                          bg-slate-50 border-slate-100
            @endif">

            {{-- Status icon bubble --}}
            <div class="w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0
                @if($status === 'done')    bg-emerald-500
                @elseif($status === 'running') bg-violet-600
                @elseif($status === 'error')   bg-red-500
                @else                          bg-slate-200
                @endif">
                @if($status === 'pending')
                    <span class="text-[10px] font-bold text-slate-400 leading-none">{{ $loop->iteration }}</span>
                @elseif($status === 'running')
                    <svg class="w-3.5 h-3.5 text-white animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-30" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                @elseif($status === 'done')
                    <svg class="w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                    </svg>
                @else
                    <svg class="w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                @endif
            </div>

            {{-- Label --}}
            <p class="text-sm font-medium flex-1
                @if($status === 'done')    text-emerald-700
                @elseif($status === 'running') text-violet-700
                @elseif($status === 'error')   text-red-700
                @else                          text-slate-400
                @endif">{{ $label }}</p>

            {{-- Right badge --}}
            @if($status === 'done')
                <span class="text-xs font-semibold text-emerald-600 bg-emerald-100 px-2 py-0.5 rounded-full">Done</span>
            @elseif($status === 'running')
                <span class="text-xs font-semibold text-violet-600 bg-violet-100 px-2 py-0.5 rounded-full">Running…</span>
            @elseif($status === 'error')
                <span class="text-xs font-semibold text-red-600 bg-red-100 px-2 py-0.5 rounded-full">Failed</span>
            @endif
        </div>
        @endforeach
    </div>

    {{-- Error box --}}
    @if($installError)
    <div class="flex items-start gap-3 p-4 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700">
        <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
        </svg>
        <div>
            <p class="font-semibold mb-0.5">Installation step failed</p>
            <p class="text-red-600 text-xs font-mono break-all mt-1">{{ $installErrMsg }}</p>
            <p class="mt-2 text-xs text-red-500">Fix the error above and refresh the page to retry.</p>
        </div>
    </div>
    @endif

</div>{{-- /wire:poll wrapper --}}

{{-- ══════════════════════════════════════════════════════════
     🔍 PRE-INSTALL — shown before clicking Install Now
══════════════════════════════════════════════════════════ --}}
@else
<p class="text-xs font-bold text-violet-600 uppercase tracking-widest mb-1">Step 4 of {{ $totalSteps }}</p>
<h2 class="text-2xl font-bold text-slate-900 tracking-tight">Ready to Install</h2>
<p class="text-slate-500 text-sm mt-1 mb-5">Review your configuration, then click <strong>Install Now</strong> to complete setup.</p>

<div class="border border-slate-200 rounded-2xl overflow-hidden mb-5">
    <div class="px-5 py-3 bg-slate-50 border-b border-slate-200">
        <p class="text-xs font-bold text-slate-500 uppercase tracking-widest">Configuration Summary</p>
    </div>
    <div class="divide-y divide-slate-100">
        @foreach([
            ['App Name',   $appName],
            ['App URL',    $appUrl],
            ['Database',   $dbName . ' @ ' . $dbHost . ':' . $dbPort],
            ['Admin Email',$adminEmail],
            ['Demo Data',  $installDemo ? ucfirst($demoType) . ' demo' : '— None'],
        ] as [$lbl, $val])
        <div class="flex items-center justify-between px-5 py-3">
            <span class="text-sm text-slate-500">{{ $lbl }}</span>
            <span class="text-sm font-semibold text-slate-800">{{ $val }}</span>
        </div>
        @endforeach
    </div>
</div>

<div class="flex items-start gap-3 p-4 bg-amber-50 border border-amber-200 rounded-xl mb-6 text-sm text-amber-700">
    <svg class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
    </svg>
    <p>This will run migrations and overwrite your <code class="bg-amber-100 px-1 rounded text-xs font-mono">.env</code>. Ensure your database is empty before proceeding.</p>
</div>

<div class="flex items-center gap-3">
    <button wire:click="prev"
        class="inline-flex items-center gap-2 px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl text-sm transition-colors">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
        </svg>
        Back
    </button>

    <button wire:click="startInstall"
            wire:loading.attr="disabled"
            wire:loading.class="opacity-60 cursor-not-allowed"
            class="ml-auto inline-flex items-center gap-2 px-7 py-3 bg-violet-600 hover:bg-violet-700 text-white font-bold rounded-xl text-sm transition-colors shadow-lg shadow-violet-200/60">
        <svg wire:loading.remove wire:target="startInstall"
             class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.59 14.37a6 6 0 01-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 006.16-12.12A14.98 14.98 0 009.631 8.41m5.96 5.96a14.926 14.926 0 01-5.841 2.58m-.119-8.54a6 6 0 00-7.381 5.84h4.8m2.581-5.84a14.927 14.927 0 00-2.58 5.84m2.699 2.7c-.103.021-.207.041-.311.06a15.09 15.09 0 01-2.448-2.448 14.9 14.9 0 01.06-.312m-2.24 2.39a4.493 4.493 0 00-1.757 4.306 4.493 4.493 0 004.306-1.758M16.5 9a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"/>
        </svg>
        <svg wire:loading wire:target="startInstall"
             class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
        </svg>
        Install Now
    </button>
</div>
@endif

</div>{{-- /step-content --}}
@endif


    </div>{{-- /content area --}}
</div>{{-- /main card --}}
