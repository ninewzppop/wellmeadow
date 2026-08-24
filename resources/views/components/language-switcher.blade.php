@php
    $current = app()->getLocale();
@endphp

<div class="flex items-center gap-1 rounded-full bg-slate-100 p-1" aria-label="Language switcher">
    @foreach (['en' => 'EN', 'th' => 'TH'] as $code => $label)
        @if ($code === $current)
            <span class="flex items-center gap-1.5 rounded-full bg-white px-3 py-1 text-xs font-semibold text-slate-700 shadow-sm">
                @if ($code === 'en')
                    <svg class="h-4 w-4 rounded-full" viewBox="0 0 36 36" xmlns="http://www.w3.org/2000/svg">
                        <clipPath id="enClip"><circle cx="18" cy="18" r="18"/></clipPath>
                        <g clip-path="url(#enClip)">
                            <rect width="36" height="36" fill="#012169"/>
                            <path d="M0 0L36 36M36 0L0 36" stroke="#fff" stroke-width="6"/>
                            <path d="M0 0L36 36M36 0L0 36" stroke="#C8102E" stroke-width="2"/>
                            <path d="M18 0V36M0 18H36" stroke="#fff" stroke-width="10"/>
                            <path d="M18 0V36M0 18H36" stroke="#C8102E" stroke-width="6"/>
                        </g>
                    </svg>
                @else
                    <svg class="h-4 w-4 rounded-full" viewBox="0 0 36 36" xmlns="http://www.w3.org/2000/svg">
                        <clipPath id="thClip"><circle cx="18" cy="18" r="18"/></clipPath>
                        <g clip-path="url(#thClip)">
                            <rect width="36" height="7.2" fill="#ED1C24" y="0"/>
                            <rect width="36" height="7.2" fill="#FFFFFF" y="7.2"/>
                            <rect width="36" height="7.2" fill="#241D4F" y="14.4"/>
                            <rect width="36" height="7.2" fill="#FFFFFF" y="21.6"/>
                            <rect width="36" height="7.2" fill="#ED1C24" y="28.8"/>
                        </g>
                    </svg>
                @endif
                {{ $label }}
            </span>
        @else
            <a href="{{ route('language.switch', $code) }}"
               class="flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-medium text-slate-500 transition hover:bg-white hover:text-slate-700">
                @if ($code === 'en')
                    <svg class="h-4 w-4 rounded-full" viewBox="0 0 36 36" xmlns="http://www.w3.org/2000/svg">
                        <clipPath id="enClip2"><circle cx="18" cy="18" r="18"/></clipPath>
                        <g clip-path="url(#enClip2)">
                            <rect width="36" height="36" fill="#012169"/>
                            <path d="M0 0L36 36M36 0L0 36" stroke="#fff" stroke-width="6"/>
                            <path d="M0 0L36 36M36 0L0 36" stroke="#C8102E" stroke-width="2"/>
                            <path d="M18 0V36M0 18H36" stroke="#fff" stroke-width="10"/>
                            <path d="M18 0V36M0 18H36" stroke="#C8102E" stroke-width="6"/>
                        </g>
                    </svg>
                @else
                    <svg class="h-4 w-4 rounded-full" viewBox="0 0 36 36" xmlns="http://www.w3.org/2000/svg">
                        <clipPath id="thClip2"><circle cx="18" cy="18" r="18"/></clipPath>
                        <g clip-path="url(#thClip2)">
                            <rect width="36" height="7.2" fill="#ED1C24" y="0"/>
                            <rect width="36" height="7.2" fill="#FFFFFF" y="7.2"/>
                            <rect width="36" height="7.2" fill="#241D4F" y="14.4"/>
                            <rect width="36" height="7.2" fill="#FFFFFF" y="21.6"/>
                            <rect width="36" height="7.2" fill="#ED1C24" y="28.8"/>
                        </g>
                    </svg>
                @endif
                {{ $label }}
            </a>
        @endif
    @endforeach
</div>