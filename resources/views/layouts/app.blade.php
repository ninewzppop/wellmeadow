<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('Dashboard')) &middot; {{ __('Hospital System') }}</title>
    @vite('resources/css/app.css')
</head>
<body class="h-full font-sans text-slate-800 antialiased">
    @auth
        <div class="flex min-h-full">
            @include('layouts.sidebar')

            <div class="flex min-w-0 flex-1 flex-col">
                <header class="flex items-center justify-between border-b border-slate-200 bg-white px-4 py-2.5 shadow-sm sm:px-6">
                    <button type="button" data-sidebar-toggle
                        class="rounded p-2 text-slate-500 hover:bg-slate-100 lg:hidden"
                        aria-label="Toggle navigation">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-6 w-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                    </button>

                    <div class="ml-auto flex items-center gap-3">
                        <x-language-switcher />
                        <x-user-dropdown />
                    </div>
                </header>

                <main class="flex-1 px-4 py-6 sm:px-6 lg:px-8">
                    @if (session('status'))
                        <div class="mb-4 rounded border border-green-300 bg-green-50 px-4 py-3 text-sm text-green-800">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if (! empty($consultingAppointments) && $consultingAppointments->isNotEmpty())
                        <div class="mb-4 rounded-xl border border-amber-300 bg-amber-50 px-4 py-3 shadow-sm">
                            <p class="text-xs font-bold uppercase tracking-wide text-amber-700">{{ __('Currently consulting') }}</p>
                            <div class="mt-2 flex flex-wrap gap-2">
                                @foreach ($consultingAppointments as $consult)
                                    <a href="{{ $consult->patient ? route('patients.show', $consult->patient) : route('rooms.show', $consult->room ?? $consult->Room_No) }}"
                                       class="inline-flex items-center gap-2 rounded-full bg-white px-4 py-2 text-sm font-semibold text-amber-800 shadow-sm ring-1 ring-amber-200 hover:bg-amber-100">
                                        <span class="relative flex h-2 w-2">
                                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-amber-500 opacity-75"></span>
                                            <span class="relative inline-flex h-2 w-2 rounded-full bg-amber-600"></span>
                                        </span>
                                        {{ __('Consulting: :name', ['name' => $consult->patient?->full_name ?? $consult->Pt_No]) }}
                                        <span class="text-xs font-normal text-amber-600">— {{ $consult->room?->RoomName ?? $consult->Room_No }} · {{ $consult->Appt_No }}</span>
                                    </a>
                                @endforeach
                            </div>
                            <p class="mt-1 text-[11px] text-amber-600">{{ __('Click to open patient details — no search needed.') }}</p>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="mb-4 rounded border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-800">
                            <strong>Please fix the following errors:</strong>
                            <ul class="mt-1 list-inside list-disc">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @yield('content')
                </main>
            </div>
        </div>
    @endauth

    @guest
        <div class="relative mx-auto max-w-4xl px-4 py-10">
            <div class="absolute right-4 top-4">
                <x-language-switcher />
            </div>
            @yield('content')
        </div>
    @endguest

    @stack('scripts')

    <script>
        document.querySelector('[data-sidebar-toggle]')?.addEventListener('click', () => {
            document.querySelector('[data-sidebar]')?.classList.toggle('-translate-x-full');
        });
    </script>
</body>
</html>