@props(['status'])

@php
    $badgeClasses = [
        'waiting list' => 'bg-sky-100 text-sky-700',
        'scheduled' => 'bg-blue-100 text-blue-700',
        'in consultation' => 'bg-amber-100 text-amber-800',
        'completed-medication' => 'bg-emerald-100 text-emerald-700',
        'completed-waitlist' => 'bg-violet-100 text-violet-700',
        'completed' => 'bg-green-100 text-green-700',
    ];
    $statusLabel = \App\Models\Appointment::statusLabels()[$status]
        ?? ucfirst(str_replace('-', ' ', (string) $status));
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold '.($badgeClasses[$status] ?? 'bg-slate-100 text-slate-600')]) }}>
    {{ $statusLabel }}
</span>
