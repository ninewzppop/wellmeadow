@props(['user' => null])
@php
    $user = $user ?? auth()->user();
@endphp

<div class="relative" data-user-dropdown>
    <button type="button"
        data-dropdown-toggle
        aria-haspopup="menu"
        aria-expanded="false"
        class="flex items-center gap-2 rounded-full py-1 pl-1 pr-2 transition hover:bg-slate-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-sky-500">
        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-sky-600 text-sm font-semibold text-white">
            {{ $user->initials }}
        </span>
        <span class="hidden text-sm font-medium text-slate-700 sm:block">
            {{ $user->name }}
        </span>
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
            class="h-3.5 w-3.5 text-slate-400 transition-transform" data-dropdown-chevron>
            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
        </svg>
    </button>

    <div data-dropdown-menu
        role="menu"
        class="absolute right-0 z-50 mt-2 hidden w-64 origin-top-right rounded-lg border border-slate-200 bg-white py-2 shadow-lg">
        <div class="border-b border-slate-100 px-4 py-3">
            <p class="truncate text-sm font-semibold text-slate-800">{{ $user->name }}</p>
            <p class="mt-0.5 flex items-center gap-2">
                <span class="truncate text-xs text-slate-500">{{ $user->email }}</span>
                <span class="rounded-full bg-sky-100 px-2 py-0.5 text-xs font-semibold text-sky-700">
                    {{ __(ucfirst($user->role)) }}
                </span>
            </p>
        </div>

        <form method="POST" action="{{ route('logout') }}" role="menuitem">
            @csrf
            <button type="submit"
                class="mt-2 flex w-full items-center gap-2 px-4 py-2 text-left text-sm font-medium text-red-600 transition hover:bg-red-50">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                </svg>
                {{ __('Logout') }}
            </button>
        </form>
    </div>
</div>

<script>
    document.querySelectorAll('[data-user-dropdown]').forEach((root) => {
        const toggle = root.querySelector('[data-dropdown-toggle]');
        const menu = root.querySelector('[data-dropdown-menu]');
        const chevron = root.querySelector('[data-dropdown-chevron]');
        let open = false;

        const close = () => {
            open = false;
            menu.classList.add('hidden');
            toggle.setAttribute('aria-expanded', 'false');
            chevron.classList.remove('rotate-180');
        };

        const openMenu = () => {
            open = true;
            menu.classList.remove('hidden');
            toggle.setAttribute('aria-expanded', 'true');
            chevron.classList.add('rotate-180');
        };

        toggle.addEventListener('click', (event) => {
            event.stopPropagation();
            open ? close() : openMenu();
        });

        document.addEventListener('click', (event) => {
            if (!root.contains(event.target)) {
                close();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && open) {
                close();
                toggle.focus();
            }
        });
    });
</script>