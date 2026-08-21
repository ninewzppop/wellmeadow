<aside data-sidebar
    class="fixed inset-y-0 left-0 z-40 flex w-64 flex-col bg-white text-slate-600 transition-transform lg:sticky lg:top-0 lg:max-h-screen lg:translate-x-0 -translate-x-full border-r border-slate-200">
    <div class="flex items-center gap-3 border-b border-slate-200 px-5 py-4">
        <img src="{{ asset('images/logo.png') }}" alt="Logo" class="h-10 w-10 rounded-lg object-contain">
        <div>
            <span class="block text-sm font-extrabold leading-tight text-[#112D6E]">WELLMEADOW</span>
            <span class="block text-xs text-slate-400">Hospital</span>
        </div>
        <button type="button" data-sidebar-toggle class="ml-auto rounded p-1 text-slate-400 hover:bg-slate-100 lg:hidden" aria-label="{{ __('Close navigation') }}">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <nav class="no-scrollbar flex-1 space-y-6 overflow-y-auto px-3 pb-6 text-sm mt-4">
        {{-- Dashboard --}}
        <ul class="space-y-1">
            <li>
                <a href="{{ route('dashboard.index') }}"
                    class="flex items-center gap-3 rounded-md px-3 py-2 font-medium {{ request()->routeIs('dashboard.index') ? 'bg-[#112D6E]/10 text-[#112D6E]' : 'hover:bg-slate-100' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5 shrink-0">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                    </svg>
                    {{ __('Dashboard') }}
                </a>
            </li>
        </ul>

        {{-- Patients --}}
        <div>
            <p class="border-b border-slate-200 px-3 pb-2 text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('Patients') }}</p>
            <ul class="mt-2 space-y-1">
                @foreach ([
                    ['label' => __('Appointments'), 'route' => 'appointments.index'],
                    ['label' => __('In-patients'), 'route' => 'in-patients.index'],
                    ['label' => __('Medications'), 'route' => 'medications.index'],
                    ['label' => __('Allergies'), 'route' => 'allergies.index'],
                ] as $item)
                    <li>
                        <a href="{{ route($item['route']) }}"
                            class="flex items-center gap-3 rounded-md px-3 py-2 {{ request()->routeIs($item['route']) ? 'bg-[#112D6E]/10 text-[#112D6E]' : 'hover:bg-slate-100' }}">
                            @if ($item['route'] === 'appointments.index')
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5 shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                                </svg>
                            @elseif ($item['route'] === 'in-patients.index')
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5 shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 7.125C2.25 6.504 2.754 6 3.375 6h6c.621 0 1.125.504 1.125 1.125v3.75c0 .621-.504 1.125-1.125 1.125h-6a1.125 1.125 0 0 1-1.125-1.125v-3.75ZM14.25 8.625c0-.621.504-1.125 1.125-1.125h5.25c.621 0 1.125.504 1.125 1.125v8.25c0 .621-.504 1.125-1.125 1.125h-5.25a1.125 1.125 0 0 1-1.125-1.125v-8.25ZM3.75 16.125c0-.621.504-1.125 1.125-1.125h5.25c.621 0 1.125.504 1.125 1.125v2.25c0 .621-.504 1.125-1.125 1.125h-5.25a1.125 1.125 0 0 1-1.125-1.125v-2.25Z" />
                                </svg>
                            @elseif ($item['route'] === 'medications.index')
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5 shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 0 1-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 0 1 4.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0 1 12 15a9.065 9.065 0 0 0-6.23.693L5 14.5m14.8.8 1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0 1 12 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5" />
                                </svg>
                            @elseif ($item['route'] === 'allergies.index')
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5 shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                                </svg>
                            @endif
                            {{ $item['label'] }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>

        {{-- Resources --}}
        <div>
            <p class="border-b border-slate-200 px-3 pb-2 text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('Resources') }}</p>
            <ul class="mt-2 space-y-1">
                @foreach ([
                    ['label' => __('Wards & Beds'), 'route' => 'wards.index'],
                    ['label' => __('Ward report'), 'route' => 'wards.report'],
                    ['label' => __('Rooms'), 'route' => 'rooms.index'],
                    ['label' => __('Stock'), 'route' => 'stock.index'],
                    ['label' => __('Pharmacy'), 'route' => 'pharmacy.index'],
                    ['label' => __('Requisitions'), 'route' => 'requisitions.index'],
                ] as $item)
                    <li>
                        <a href="{{ route($item['route']) }}"
                            class="flex items-center gap-3 rounded-md px-3 py-2 {{ request()->routeIs($item['route']) ? 'bg-[#112D6E]/10 text-[#112D6E]' : 'hover:bg-slate-100' }}">
                            @if ($item['route'] === 'wards.index')
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5 shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21h.75a.75.75 0 0 0 .75-.75v-5.174a1.5 1.5 0 0 1 .572-1.192l2.344-1.68a.75.75 0 0 0 .434-.681V6.39a.75.75 0 0 1 .75-.75h.75a.75.75 0 0 1 .75.75v4.5a.75.75 0 0 0 .75.75h.75a.75.75 0 0 0 .75-.75V9.349a.75.75 0 0 1 .434-.681l2.344-1.68A1.5 1.5 0 0 1 16.5 6.39v-2.09a.75.75 0 0 0-.75-.75h-3a.75.75 0 0 0-.75.75V6" />
                                </svg>
                            @elseif ($item['route'] === 'wards.report')
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5 shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                                </svg>
                            @elseif ($item['route'] === 'rooms.index')
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5 shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21h.75a.75.75 0 0 0 .75-.75v-5.174a1.5 1.5 0 0 1 .572-1.192l2.344-1.68a.75.75 0 0 0 .434-.681V6.39a.75.75 0 0 1 .75-.75h.75a.75.75 0 0 1 .75.75v4.5a.75.75 0 0 0 .75.75h.75a.75.75 0 0 0 .75-.75V9.349a.75.75 0 0 1 .434-.681l2.344-1.68A1.5 1.5 0 0 1 16.5 6.39v-2.09a.75.75 0 0 0-.75-.75h-3a.75.75 0 0 0-.75.75V6" />
                                </svg>
                            @elseif ($item['route'] === 'stock.index')
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5 shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
                                </svg>
                            @elseif ($item['route'] === 'pharmacy.index')
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5 shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 0 1-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 0 1 4.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0 1 12 15a9.065 9.065 0 0 0-6.23.693L5 14.5m14.8.8 1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0 1 12 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5" />
                                </svg>
                            @elseif ($item['route'] === 'requisitions.index')
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5 shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                </svg>
                            @endif
                            {{ $item['label'] }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>

        {{-- Personnel --}}
        <div>
            <p class="border-b border-slate-200 px-3 pb-2 text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('Personnel') }}</p>
            <ul class="mt-2 space-y-1">
                @foreach ([
                    ['label' => __('Staff'), 'route' => 'staff.index'],
                    ['label' => __('Search staff'), 'route' => 'staff.search'],
                    ['label' => __('Allocations'), 'route' => 'allocations.index'],
                    ['label' => __('Rota'), 'route' => 'rota.index'],
                ] as $item)
                    <li>
                        <a href="{{ route($item['route']) }}"
                            class="flex items-center gap-3 rounded-md px-3 py-2 {{ request()->routeIs($item['route']) ? 'bg-[#112D6E]/10 text-[#112D6E]' : 'hover:bg-slate-100' }}">
                            @if ($item['route'] === 'staff.index')
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5 shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128H5.228A2 2 0 0 1 5 17.119V5a2 2 0 0 1 2-2h6" />
                                </svg>
                            @elseif ($item['route'] === 'staff.search')
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5 shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                                </svg>
                            @elseif ($item['route'] === 'allocations.index')
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5 shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
                                </svg>
                            @elseif ($item['route'] === 'rota.index')
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5 shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                            @endif
                            {{ $item['label'] }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>

        {{-- Reference --}}
        <div>
            <p class="border-b border-slate-200 px-3 pb-2 text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('Reference') }}</p>
            <ul class="mt-2 space-y-1">
                @foreach ([
                    ['label' => __('Suppliers'), 'route' => 'suppliers.index'],
                    ['label' => __('Local doctors'), 'route' => 'local-doctors.index'],
                ] as $item)
                    <li>
                        <a href="{{ route($item['route']) }}"
                            class="flex items-center gap-3 rounded-md px-3 py-2 {{ request()->routeIs($item['route']) ? 'bg-[#112D6E]/10 text-[#112D6E]' : 'hover:bg-slate-100' }}">
                            @if ($item['route'] === 'suppliers.index')
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5 shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.141-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
                                </svg>
                            @elseif ($item['route'] === 'local-doctors.index')
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5 shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                </svg>
                            @endif
                            {{ $item['label'] }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>

        {{-- System (Admin only) --}}
        @auth
            @if (auth()->user()->isAdmin())
                <div>
                    <p class="border-b border-slate-200 px-3 pb-2 text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('System') }}</p>
                    <ul class="mt-2 space-y-1">
                        <li>
                            <a href="{{ route('users.index') }}"
                                class="flex items-center gap-3 rounded-md px-3 py-2 {{ request()->routeIs('users.index') ? 'bg-[#112D6E]/10 text-[#112D6E]' : 'hover:bg-slate-100' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5 shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                </svg>
                                {{ __('Users & Roles') }}
                            </a>
                        </li>
                    </ul>
                </div>
            @endif
        @endauth
    </nav>
</aside>
