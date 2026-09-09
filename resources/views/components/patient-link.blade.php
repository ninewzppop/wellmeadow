{{-- Patient name that links to patient details when the viewer is allowed to see them. --}}
@props(['patient'])
@if ($patient)
    @can('view', $patient)
        <a href="{{ route('patients.show', $patient) }}" {{ $attributes->merge(['class' => 'hover:text-blue-600 hover:underline']) }}>{{ $patient->full_name }}</a>
    @else
        <span {{ $attributes }}>{{ $patient->full_name }}</span>
    @endcan
@else
    <span {{ $attributes }}>—</span>
@endif
