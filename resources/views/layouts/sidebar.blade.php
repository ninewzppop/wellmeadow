<aside data-sidebar
    class="fixed inset-y-0 left-0 z-40 flex w-64 flex-col bg-sky-900 text-sky-100 transition-transform lg:sticky lg:top-0 lg:max-h-screen lg:translate-x-0 -translate-x-full">
    <div class="flex items-center justify-between px-5 py-4">
        <a href="{{ route('dashboard.index') }}" class="text-lg font-bold text-white">Hospital System</a>
        <button type="button" data-sidebar-toggle class="rounded p-1 text-sky-300 hover:bg-sky-800 lg:hidden" aria-label="Close navigation">
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
                    Dashboard
                </a>
            </li>
        </ul>

        <div>
            <p class="px-3 pb-1 text-xs font-semibold uppercase tracking-wide text-sky-400">ผู้ป่วย</p>
            <ul class="space-y-1">
                @foreach ([
                    ['label' => 'ผู้ป่วย', 'route' => 'patients.index'],
                    ['label' => 'นัดหมาย', 'route' => 'appointments.index'],
                    ['label' => 'ผู้ป่วยใน', 'route' => 'in-patients.index'],
                    ['label' => 'ใบสั่งยา', 'route' => 'medications.index'],
                    ['label' => 'อาการแพ้', 'route' => 'allergies.index'],
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
            <p class="px-3 pb-1 text-xs font-semibold uppercase tracking-wide text-sky-400">ทรัพยากร</p>
            <ul class="space-y-1">
                @foreach ([
                    ['label' => 'วอร์ด & เตียง', 'route' => 'wards.index'],
                    ['label' => 'รายงานวอร์ด', 'route' => 'wards.report'],
                    ['label' => 'ห้องตรวจ', 'route' => 'rooms.index'],
                    ['label' => 'สต็อกกลาง', 'route' => 'stock.index'],
                    ['label' => 'เภสัชภัณฑ์', 'route' => 'pharmacy.index'],
                    ['label' => 'ใบเบิกของ', 'route' => 'requisitions.index'],
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
            <p class="px-3 pb-1 text-xs font-semibold uppercase tracking-wide text-sky-400">บุคลากร</p>
            <ul class="space-y-1">
                @foreach ([
                    ['label' => 'บุคลากร', 'route' => 'staff.index'],
                    ['label' => 'ค้นหาบุคลากร', 'route' => 'staff.search'],
                    ['label' => 'การจัดเวร', 'route' => 'allocations.index'],
                    ['label' => 'ตารางเวร', 'route' => 'rota.index'],
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
            <p class="px-3 pb-1 text-xs font-semibold uppercase tracking-wide text-sky-400">อ้างอิง</p>
            <ul class="space-y-1">
                @foreach ([
                    ['label' => 'ซัพพลายเออร์', 'route' => 'suppliers.index'],
                    ['label' => 'แพทย์ท้องถิ่น', 'route' => 'local-doctors.index'],
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
                    <p class="px-3 pb-1 text-xs font-semibold uppercase tracking-wide text-sky-400">ระบบ</p>
                    <ul class="space-y-1">
                        <li>
                            <a href="{{ route('users.index') }}"
                                class="block rounded-md px-3 py-2 {{ request()->routeIs('users.index') ? 'bg-sky-700 text-white' : 'hover:bg-sky-800' }}">
                                ผู้ใช้ & บทบาท
                            </a>
                        </li>
                    </ul>
                </div>
            @endif
        @endauth
    </nav>
</aside>