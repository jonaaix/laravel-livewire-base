<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="antialiased">
<head>
    @include('partials.head')
    <style>
        @keyframes aurora-drift-1 {
            0%, 100% { transform: translate3d(-15%, -10%, 0) scale(1); }
            50%      { transform: translate3d(10%, 15%, 0) scale(1.15); }
        }
        @keyframes aurora-drift-2 {
            0%, 100% { transform: translate3d(20%, 5%, 0) scale(1.1); }
            50%      { transform: translate3d(-10%, -20%, 0) scale(0.95); }
        }
        @keyframes aurora-drift-3 {
            0%, 100% { transform: translate3d(-5%, 20%, 0) scale(0.9); }
            50%      { transform: translate3d(15%, -15%, 0) scale(1.2); }
        }
        @keyframes fade-up {
            from { opacity: 0; transform: translateY(12px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .aurora-blob {
            position: absolute;
            width: 55vmax;
            height: 55vmax;
            border-radius: 9999px;
            filter: blur(90px);
            mix-blend-mode: screen;
            will-change: transform;
        }
        .aurora-blob--1 { top: -10%; left: -10%; background: radial-gradient(circle at center, #a5b4fc 0%, transparent 60%); animation: aurora-drift-1 22s ease-in-out infinite; }
        .aurora-blob--2 { top: 20%; right: -15%; background: radial-gradient(circle at center, #f0abfc 0%, transparent 60%); animation: aurora-drift-2 26s ease-in-out infinite; }
        .aurora-blob--3 { bottom: -20%; left: 20%; background: radial-gradient(circle at center, #7dd3fc 0%, transparent 60%); animation: aurora-drift-3 30s ease-in-out infinite; }
        .dark .aurora-blob { mix-blend-mode: lighten; opacity: 0.45; }
        .dark .aurora-blob--1 { background: radial-gradient(circle at center, #4338ca 0%, transparent 60%); }
        .dark .aurora-blob--2 { background: radial-gradient(circle at center, #a21caf 0%, transparent 60%); }
        .dark .aurora-blob--3 { background: radial-gradient(circle at center, #0369a1 0%, transparent 60%); }
        .grain {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='200' height='200'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='2' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.6'/%3E%3C/svg%3E");
        }
        .fade-up    { animation: fade-up 0.9s ease-out 0.1s both; }
        .fade-up-2  { animation: fade-up 0.9s ease-out 0.3s both; }
        .fade-up-3  { animation: fade-up 0.9s ease-out 0.5s both; }
        @media (prefers-reduced-motion: reduce) {
            .aurora-blob, .fade-up, .fade-up-2, .fade-up-3 { animation: none; }
        }
    </style>
</head>
<body class="relative min-h-svh overflow-hidden bg-white text-zinc-900 dark:bg-zinc-950 dark:text-zinc-100">
    <div aria-hidden="true" class="pointer-events-none absolute inset-0 -z-10 overflow-hidden">
        <div class="aurora-blob aurora-blob--1"></div>
        <div class="aurora-blob aurora-blob--2"></div>
        <div class="aurora-blob aurora-blob--3"></div>
        <div class="grain absolute inset-0 opacity-[0.04] mix-blend-overlay dark:opacity-[0.06]"></div>
    </div>

    @if (Route::has('login'))
        <nav class="absolute inset-x-0 top-0 z-10 flex items-center justify-end gap-2 p-6 text-sm">
            @auth
                <a href="{{ route('dashboard') }}"
                   class="rounded-md border border-zinc-900/10 bg-white/40 px-4 py-1.5 backdrop-blur-md transition hover:bg-white/70 dark:border-white/15 dark:bg-white/5 dark:hover:bg-white/10">
                    {{ __('Dashboard') }}
                </a>
            @else
                <a href="{{ route('login') }}"
                   class="rounded-md px-4 py-1.5 transition hover:bg-zinc-900/5 dark:hover:bg-white/10">
                    {{ __('Log in') }}
                </a>
                @if (Route::has('register') && \App\Models\AppSetting::get('registration_enabled', true))
                    <a href="{{ route('register') }}"
                       class="rounded-md border border-zinc-900/10 bg-white/40 px-4 py-1.5 backdrop-blur-md transition hover:bg-white/70 dark:border-white/15 dark:bg-white/5 dark:hover:bg-white/10">
                        {{ __('Sign up') }}
                    </a>
                @endif
            @endauth
        </nav>
    @endif

    <main class="relative flex min-h-svh items-center justify-center px-6">
        <div class="text-center">
            <p class="fade-up mb-6 inline-flex items-center gap-2 rounded-full border border-zinc-900/10 bg-white/50 px-3 py-1 text-xs font-medium tracking-wide text-zinc-700 backdrop-blur-md dark:border-white/15 dark:bg-white/5 dark:text-zinc-300">
                <span class="relative flex size-1.5">
                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex size-1.5 rounded-full bg-emerald-500"></span>
                </span>
                {{ __('Workspace online') }}
            </p>
            <h1 class="fade-up-2 text-balance text-5xl font-semibold tracking-tight sm:text-6xl md:text-7xl">
                {{ __('Make something great.') }}
            </h1>
            <p class="fade-up-3 mx-auto mt-6 max-w-xl text-balance text-lg text-zinc-600 dark:text-zinc-400">
                {{ __('Your workspace starter — sign in or create an account to begin.') }}
            </p>
        </div>
    </main>

</body>
</html>
