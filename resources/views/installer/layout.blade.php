<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Quizora — Installation Wizard</title>
<link rel="stylesheet" href="{{ asset('css/installer.css') }}">
<style>
    *, *::before, *::after { box-sizing: border-box; }
    body { font-family: system-ui, -apple-system, 'Segoe UI', sans-serif; }

    .bg-installer {
        background-color: #0f0a1e;
        background-image:
            radial-gradient(ellipse 80% 60% at 20% 0%, rgba(109,40,217,0.35) 0%, transparent 60%),
            radial-gradient(ellipse 60% 50% at 80% 100%, rgba(124,58,237,0.25) 0%, transparent 55%);
    }

    .inp { outline: none; transition: border-color 0.15s, box-shadow 0.15s; }
    .inp:focus { border-color: #7c3aed !important; box-shadow: 0 0 0 3px rgba(124,58,237,0.15); }

    .log-scroll::-webkit-scrollbar { width: 6px; }
    .log-scroll::-webkit-scrollbar-track { background: #1f2937; border-radius: 3px; }
    .log-scroll::-webkit-scrollbar-thumb { background: #4b5563; border-radius: 3px; }

    @keyframes shimmer {
        0%   { background-position: -200% 0; }
        100% { background-position:  200% 0; }
    }
    .shimmer {
        background: linear-gradient(90deg, #334155 25%, #475569 50%, #334155 75%);
        background-size: 200% 100%;
        animation: shimmer 1.4s infinite;
    }

    @keyframes fadeSlide {
        from { opacity: 0; transform: translateY(10px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .step-content { animation: fadeSlide 0.22s ease; }
</style>
@livewireStyles
</head>
<body class="bg-installer min-h-screen flex flex-col items-center justify-center p-4 sm:p-8">

    <div class="w-full max-w-5xl">

        {{-- Brand bar --}}
        <div class="flex items-center gap-3 mb-5 px-1">
            <div class="w-9 h-9 rounded-xl bg-violet-600 flex items-center justify-center shadow-lg shadow-violet-900/60">
                <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                </svg>
            </div>
            <span class="text-white font-bold text-xl tracking-tight">Quizora</span>
            <span class="ml-auto text-xs text-white/35 font-medium tracking-widest uppercase">Installation Wizard</span>
        </div>

        {{-- The Livewire component renders here --}}
        {{ $slot }}

        <p class="text-center text-white/20 text-xs mt-5">Quizora &copy; {{ date('Y') }} &nbsp;·&nbsp; All rights reserved</p>
    </div>

@livewireScripts
</body>
</html>
