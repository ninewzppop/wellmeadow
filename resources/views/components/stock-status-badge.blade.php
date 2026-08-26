@props(['status', 'label' => null])

@php
    $badgeClasses = [
        'normal' => 'bg-emerald-100 text-emerald-700',
        'low' => 'bg-amber-100 text-amber-800',
        'out' => 'bg-red-100 text-red-700',
    ];
    $labels = [
        'normal' => __('Normal'),
        'low' => __('Low stock'),
        'out' => __('Out of stock'),
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold '.($badgeClasses[$status] ?? 'bg-slate-100 text-slate-600')]) }}>
    {{ $label ?? ($labels[$status] ?? $status) }}
</span>
