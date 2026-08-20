@php
    $current = app()->getLocale();
@endphp

<div class="flex items-center gap-1 rounded-full bg-slate-100 p-1" aria-label="Language switcher">
    @foreach (['en' => 'EN', 'th' => 'TH'] as $code => $label)
        @if ($code === $current)
            <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-slate-700 shadow-sm">{{ $label }}</span>
        @else
            <a href="{{ route('language.switch', $code) }}"
               class="rounded-full px-3 py-1 text-xs font-medium text-slate-500 transition hover:bg-white hover:text-slate-700">
                {{ $label }}
            </a>
        @endif
    @endforeach
</div>