<aside data-sidebar
    class="fixed inset-y-0 left-0 z-40 flex w-64 flex-col bg-sky-900 text-sky-100 transition-transform lg:sticky lg:top-0 lg:max-h-screen lg:translate-x-0 -translate-x-full">
    <div class="flex items-center justify-between px-5 py-4">
        <a href="{{ route('dashboard.index') }}" class="text-lg font-bold text-white">{{ __('Hospital System') }}</a>
        <button type="button" data-sidebar-toggle class="rounded p-1 text-sky-300 hover:bg-sky-800 lg:hidden" aria-label="{{ __('Close navigation') }}">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <nav class="flex-1 space-y-6 overflow-y-auto px-3 pb-6 text-sm">
        <ul class="space-y-1">
            <li>
                <a href="{{ route('dashboard.index') }}"
                    class="block rounded-md px-3 py-2 font-medium {{ request()->routeIs('dashboard.index') ? 'bg-sky-700 text-white' : 'hover:bg-sky-800' }}">
                    {{ __('Dashboard') }}
                </a>
            </li>
        </ul>

        <div>
            <p class="px-3 pb-1 text-xs font-semibold uppercase tracking-wide text-sky-400">{{ __('Patients') }}</p>
            <ul class="space-y-1">
                @foreach ([
                    ['label' => __('Patients'), 'route' => 'patients.index'],
                    ['label' => __('Appointments'), 'route' => 'appointments.index'],
                    ['label' => __('In-patients'), 'route' => 'in-patients.index'],
                    ['label' => __('Medications'), 'route' => 'medications.index'],
                    ['label' => __('Allergies'), 'route' => 'allergies.index'],
                ] as $item)
                    <li>
                        <a href="{{ route($item['route']) }}"
                            class="block rounded-md px-3 py-2 {{ request()->routeIs($item['route']) ? 'bg-sky-700 text-white' : 'hover:bg-sky-800' }}">
                            {{ $item['label'] }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>

        <div>
            <p class="px-3 pb-1 text-xs font-semibold uppercase tracking-wide text-sky-400">{{ __('Resources') }}</p>
            <ul class="space-y-1">
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
                            class="block rounded-md px-3 py-2 {{ request()->routeIs($item['route']) ? 'bg-sky-700 text-white' : 'hover:bg-sky-800' }}">
                            {{ $item['label'] }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>

        <div>
            <p class="px-3 pb-1 text-xs font-semibold uppercase tracking-wide text-sky-400">{{ __('Personnel') }}</p>
            <ul class="space-y-1">
                @foreach ([
                    ['label' => __('Staff'), 'route' => 'staff.index'],
                    ['label' => __('Search staff'), 'route' => 'staff.search'],
                    ['label' => __('Allocations'), 'route' => 'allocations.index'],
                    ['label' => __('Rota'), 'route' => 'rota.index'],
                ] as $item)
                    <li>
                        <a href="{{ route($item['route']) }}"
                            class="block rounded-md px-3 py-2 {{ request()->routeIs($item['route']) ? 'bg-sky-700 text-white' : 'hover:bg-sky-800' }}">
                            {{ $item['label'] }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>

        <div>
            <p class="px-3 pb-1 text-xs font-semibold uppercase tracking-wide text-sky-400">{{ __('Reference') }}</p>
            <ul class="space-y-1">
                @foreach ([
                    ['label' => __('Suppliers'), 'route' => 'suppliers.index'],
                    ['label' => __('Local doctors'), 'route' => 'local-doctors.index'],
                ] as $item)
                    <li>
                        <a href="{{ route($item['route']) }}"
                            class="block rounded-md px-3 py-2 {{ request()->routeIs($item['route']) ? 'bg-sky-700 text-white' : 'hover:bg-sky-800' }}">
                            {{ $item['label'] }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>

        @auth
            @if (auth()->user()->isAdmin())
                <div>
                    <p class="px-3 pb-1 text-xs font-semibold uppercase tracking-wide text-sky-400">{{ __('System') }}</p>
                    <ul class="space-y-1">
                        <li>
                            <a href="{{ route('users.index') }}"
                                class="block rounded-md px-3 py-2 {{ request()->routeIs('users.index') ? 'bg-sky-700 text-white' : 'hover:bg-sky-800' }}">
                                {{ __('Users & Roles') }}
                            </a>
                        </li>
                    </ul>
                </div>
            @endif
        @endauth
    </nav>
</aside>