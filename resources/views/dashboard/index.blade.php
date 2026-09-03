@extends('layouts.app')

@section('title', __('Dashboard'))

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <h1 class="text-2xl font-bold text-slate-800">{{ __('Dashboard') }}</h1>
        <div class="flex items-center gap-2">
            <span class="hidden text-xs text-slate-400 sm:inline">{{ __('Last updated') }}: {{ $lastUpdated->format('d/m/Y H:i:s') }}</span>
            <x-report-button :href="route('reports.wards')" />
        </div>
    </div>

    {{-- Ward filter + Last Updated (Group A vs B) --}}
    <form method="GET" class="mb-4 flex flex-wrap items-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
        <label for="ward" class="text-sm font-medium text-slate-600">{{ __('Ward') }}</label>
        <select id="ward" name="ward" class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
            <option value="">{{ __('All wards') }}</option>
            @foreach ($wardsForFilter as $w)
                <option value="{{ $w->Wd_No }}" @selected($wardFilter === $w->Wd_No)>{{ $w->Wd_Name }} ({{ $w->Wd_No }})</option>
            @endforeach
        </select>
        <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">{{ __('Filter') }}</button>
        @if ($wardFilter)
            <a href="{{ route('dashboard.index') }}" class="text-sm text-slate-500 hover:text-slate-700">{{ __('Clear') }}</a>
            <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">{{ __('Filtered by') }} {{ $wardsForFilter->firstWhere('Wd_No', $wardFilter)?->Wd_Name }}</span>
        @endif
        <span class="ml-auto text-xs text-slate-400 sm:hidden">{{ __('Last updated') }}: {{ $lastUpdated->format('d/m/Y H:i:s') }}</span>
        <span class="hidden text-xs text-slate-500 sm:ml-auto sm:inline">{{ __('Group A (ward-filtered): Queue / Beds / Overstay / Waiting / Staff · Group B (snapshot): Patients / Reorder / Allergy') }}</span>
    </form>

    <script type="application/json" id="dashboard-chart-data">
        @json($chartData)
    </script>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-lg bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500">{{ __('Total patients') }}</p>
            <p class="mt-1 text-3xl font-bold text-slate-800">{{ number_format($totalPatients) }}</p>
            <p class="mt-1 text-xs text-slate-400">{{ __('Registered this week :count people', ['count' => $newPatientsThisWeek]) }}</p>
        </div>

        <div class="rounded-lg bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500">{{ __('New patients today') }}</p>
            <p class="mt-1 text-3xl font-bold text-sky-700">{{ number_format($newPatientsToday) }}</p>
            <p class="mt-1 text-xs text-slate-400">{{ __('From the Patient table (DateReg)') }}</p>
        </div>

        <div class="rounded-lg bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500">{{ __('Appointments today') }}</p>
            <p class="mt-1 text-3xl font-bold text-sky-700">{{ number_format($appointmentsToday) }}</p>
            <p class="mt-1 text-xs text-slate-400">{{ __('From the Appointment table (ApptDate)') }}</p>
        </div>

        <div class="rounded-lg bg-white p-5 shadow-sm">
            <div class="flex items-start justify-between gap-2">
                <p class="text-sm font-medium text-slate-500">{{ __('Staff on duty this week') }} @if($wardFilter)<span class="text-xs font-normal text-blue-600">· {{ $wardsForFilter->firstWhere('Wd_No', $wardFilter)?->Wd_Name }}</span>@endif</p>
                <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-600">{{ __('Total') }} {{ $staffOnDuty }}</span>
            </div>
            <div class="mt-3 grid grid-cols-3 gap-2 text-center">
                @foreach (['Morning', 'Evening', 'Night'] as $shift)
                    @php $cnt = $staffShiftBreakdown[$shift] ?? 0; $isLow = $cnt > 0 && $cnt < $staffLowThreshold; @endphp
                    <div class="rounded-lg border px-2 py-3 {{ $isLow ? 'border-amber-300 bg-amber-50' : 'border-slate-200 bg-slate-50' }}">
                        <p class="text-xs font-medium {{ $isLow ? 'text-amber-700' : 'text-slate-500' }}">{{ __($shift) }}</p>
                        <p class="mt-1 text-xl font-bold {{ $isLow ? 'text-amber-700' : 'text-slate-800' }}">{{ $cnt }}</p>
                        @if($isLow)
                            <p class="mt-1 text-[10px] font-semibold text-amber-600">⚠ {{ __('Low') }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
            @php $sumShifts = collect(['Morning','Evening','Night'])->sum(fn($s)=> $staffShiftBreakdown[$s] ?? 0); @endphp
            <p class="mt-2 text-center text-[11px] {{ $sumShifts !== $staffOnDuty ? 'text-amber-600' : 'text-slate-400' }}">
                {{ __('Sum shifts') }} {{ $sumShifts }} {{ $sumShifts !== $staffOnDuty ? '≠ ' . __('total (staff works multiple shifts)') : '· ' . __('Group A, threshold') }} {{ $staffLowThreshold }}
            </p>
        </div>
    </div>



    {{-- Operational today — 4 KPIs --}}
    <div class="mt-4 grid grid-cols-2 gap-4 lg:grid-cols-4">
        <a href="{{ route('in-patients.index', ['status' => 'waiting']) }}" class="rounded-lg bg-white p-5 shadow-sm hover:shadow-md transition">
            <p class="text-sm font-medium text-slate-500">{{ __('Waiting for bed') }}</p>
            <p class="mt-1 text-3xl font-bold {{ $waitingListCount > 5 ? 'text-amber-600' : 'text-slate-800' }}">{{ $waitingListCount }}</p>
            <p class="mt-1 text-xs text-slate-400">InPatient DateWaitList</p>
        </a>
        <a href="{{ route('in-patients.index') }}" class="rounded-lg bg-white p-5 shadow-sm hover:shadow-md transition">
            <p class="text-sm font-medium text-slate-500">{{ __('Overstay') }}</p>
            <p class="mt-1 text-3xl font-bold {{ $overstayCount > 0 ? 'text-red-600' : 'text-emerald-600' }}">{{ $overstayCount }}</p>
            <p class="mt-1 text-xs text-slate-400">DatePlaced+ExpStayDays &lt; today</p>
        </a>
        <a href="{{ route('requisitions.index') }}" class="rounded-lg bg-white p-5 shadow-sm hover:shadow-md transition">
            <p class="text-sm font-medium text-slate-500">{{ __('Requisitions pending') }}</p>
            <p class="mt-1 text-3xl font-bold text-sky-700">{{ $pendingRequisitions }}</p>
            <p class="mt-1 text-xs text-slate-400">Wardrequisitions Pending</p>
        </a>
        <a href="{{ route('medications.index') }}" class="rounded-lg bg-white p-5 shadow-sm hover:shadow-md transition">
            <p class="text-sm font-medium text-slate-500">{{ __('Med orders pending') }}</p>
            <p class="mt-1 text-3xl font-bold text-violet-700">{{ $pendingMedications }}</p>
            <p class="mt-1 text-xs text-slate-400">MedicationOrder pending</p>
        </a>
    </div>

    <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-2">
        <div class="rounded-lg bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500">{{ __('Bed occupancy') }} <span class="text-xs font-normal text-slate-400">— {{ __('current only, no trend V1') }}</span></p>
            <p class="mt-1 text-3xl font-bold text-emerald-700">{{ $bedOccupancy }}%</p>
            <div class="mt-3 h-2 w-full overflow-hidden rounded-full bg-slate-200">
                <div class="h-full rounded-full bg-emerald-500" style="width: {{ $bedOccupancy }}%"></div>
            </div>
            <p class="mt-2 text-xs text-slate-400">{{ __(':occupied / :total beds (Bed.BedStatus)', ['occupied' => $occupiedBeds, 'total' => $totalBeds]) }}</p>
            <div class="mt-3 flex gap-2 text-xs">
                <span class="rounded-full bg-emerald-100 px-2 py-1 font-semibold text-emerald-700">{{ $waitingListCount }} waiting</span>
                <span class="rounded-full bg-red-100 px-2 py-1 font-semibold text-red-700">{{ $overstayCount }} overstay</span>
            </div>
        </div>

        <div class="rounded-lg bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500">{{ __('Today by status') }} ({{ $appointmentsToday }})</p>
            <div class="mt-3 space-y-2">
                @foreach (['waiting list' => __('Waiting list'), 'in consultation' => __('In consultation'), 'scheduled' => __('Scheduled'), 'completed' => __('Completed')] as $s => $lab)
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-slate-600">{{ $lab }}</span>
                        <span class="font-semibold text-slate-800">{{ $appointmentsTodayByStatus[$s] ?? 0 }}</span>
                    </div>
                @endforeach
            </div>
            <div class="mt-3 border-t pt-3">
                <p class="text-xs font-semibold text-slate-500">{{ __('Appointments this week (:count)', ['count' => $appointmentsWeek->sum('total')]) }}</p>
                <div class="mt-2 space-y-1">
                    @foreach ($appointmentStatusLabels as $status => $label)
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-slate-500">{{ $label }}</span>
                            <span class="font-medium text-slate-700">{{ $appointmentsWeek[$status]->total ?? 0 }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
            @if ($inConsultationToday->isNotEmpty())
                <div class="mt-3 rounded-lg border border-amber-200 bg-amber-50 p-3">
                    <p class="text-xs font-bold text-amber-700">{{ __('In consultation now') }}</p>
                    <div class="mt-1 flex flex-wrap gap-1.5">
                        @foreach ($inConsultationToday as $c)
                            <a href="{{ $c->patient ? route('patients.show', $c->patient) : route('rooms.show', $c->Room_No) }}" class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-amber-700 ring-1 ring-amber-200 hover:bg-amber-100">{{ $c->patient?->full_name ?? $c->Pt_No }} — {{ $c->room?->RoomName ?? $c->Room_No }}</a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-2">
        <div class="rounded-lg bg-white p-5 shadow-sm">
            <p class="mb-4 text-sm font-medium text-slate-500">{{ __('New patients per day — 14 days') }}</p>
            <div class="h-64"><canvas id="patients-trend"></canvas></div>
        </div>
        <div class="rounded-lg bg-white p-5 shadow-sm">
            <p class="mb-4 text-sm font-medium text-slate-500">{{ __('Appointments per day — 14 days') }}</p>
            <div class="h-64"><canvas id="appointments-trend"></canvas></div>
        </div>
    </div>

    <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-2">
        <div class="rounded-lg bg-white p-5 shadow-sm">
            <div class="mb-3 flex items-center justify-between">
                <p class="text-sm font-medium text-slate-500">{{ __('Appointments today') }} <span class="text-xs font-normal text-slate-400">— {{ $todayAppointments->count() }}</span></p>
                @if ($todayAppointments->count() > 5)
                    <a href="{{ route('appointments.index') }}" class="text-xs font-medium text-blue-600 hover:underline">{{ __('View all') }} ({{ $todayAppointments->count() }})</a>
                @endif
            </div>
            @if ($todayAppointments->isEmpty())
                <p class="text-sm text-slate-400">{{ __('No appointments today') }}</p>
            @else
                <div class="max-h-[260px] overflow-y-auto overflow-x-auto -mx-1 px-1">
                    <table class="w-full text-left text-sm">
                        <thead class="sticky top-0 bg-white">
                            <tr class="border-b border-slate-200 text-xs uppercase tracking-wide text-slate-400">
                                <th class="py-2 pr-3">{{ __('Time') }}</th>
                                <th class="py-2 pr-3">{{ __('Patient') }}</th>
                                <th class="py-2 pr-3">{{ __('Doctor') }}</th>
                                <th class="py-2 pr-3">{{ __('Room') }}</th>
                                <th class="py-2">{{ __('Status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($todayAppointments->take(5) as $appointment)
                                <tr class="border-b border-slate-100">
                                    <td class="py-2 pr-3">{{ $appointment->ApptTime?->format('H:i') ?? '—' }}</td>
                                    <td class="py-2 pr-3">{{ $appointment->patient?->full_name ?? $appointment->Pt_No }}</td>
                                    <td class="py-2 pr-3">{{ $appointment->consultant?->full_name ?? '—' }}</td>
                                    <td class="py-2 pr-3">{{ $appointment->room?->RoomName ?? $appointment->Room_No ?? '—' }}</td>
                                    <td class="py-2">
                                        @php $status = $appointment->status; @endphp
                                        <span class="rounded-full px-2 py-0.5 text-xs font-medium
                                            {{ $status === 'completed' ? 'bg-green-100 text-green-700' : ($status === 'cancelled' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700') }}">
                                            {{ $appointmentStatusLabels[$status] ?? $status }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if ($todayAppointments->count() > 5)
                    <a href="{{ route('appointments.index') }}" class="mt-2 block text-center text-xs font-medium text-blue-600 hover:underline">{{ __('View all') }} ({{ $todayAppointments->count() }}) →</a>
                @endif
            @endif
        </div>

        <div class="rounded-lg bg-white p-5 shadow-sm">
            <div class="mb-3 flex items-center justify-between">
                <p class="text-sm font-medium text-slate-500">{{ __('Available beds per ward') }}</p>
                <span class="text-xs text-slate-400">{{ $wardBedStats->count() }} {{ __('wards') }}</span>
            </div>
            @if ($wardBedStats->isEmpty())
                <p class="text-sm text-slate-400">{{ __('No wards defined yet.') }}</p>
            @else
                @php $displayWards = $wardBedStats->take(5); $remaining = $wardBedStats->count() - $displayWards->count(); @endphp
                <div class="max-h-[260px] space-y-2 overflow-y-auto pr-1">
                    @foreach ($displayWards as $stat)
                        <div class="flex items-center justify-between rounded-lg border border-slate-200 px-3 py-2">
                            <div>
                                <p class="text-sm font-semibold text-slate-800">{{ $stat['ward']->Wd_Name }}</p>
                                <p class="text-xs text-slate-400">{{ $stat['ward']->Wd_No }} · {{ $stat['ward']->Location ?? '—' }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-bold {{ $stat['available'] == 0 ? 'text-red-600' : ($stat['available'] <= 2 ? 'text-amber-600' : 'text-emerald-600') }}">{{ $stat['available'] }} / {{ $stat['total'] }}</p>
                                <p class="text-xs text-slate-400">{{ __('available') }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
                @if ($remaining > 0)
                    <p class="mt-2 text-center text-xs text-slate-400">+{{ $remaining }} {{ __('more wards') }}</p>
                @endif
                <a href="{{ route('wards.index') }}" class="mt-2 block text-center text-xs font-medium text-blue-600 hover:underline">{{ __('View all wards') }} ({{ $wardBedStats->count() }}) →</a>
            @endif
        </div>

        <div class="rounded-lg bg-white p-5 shadow-sm">
            <div class="mb-2 flex items-center justify-between">
                <p class="text-sm font-medium text-slate-500">{{ __('Stock/medication near reorder') }}</p>
                <span class="text-xs text-slate-400">{{ $lowDrugs->count() + $surgicalAlerts->count() + $nonSurgicalAlerts->count() }} {{ __('items') }}</span>
            </div>
            @if ($lowDrugs->isEmpty() && $surgicalAlerts->isEmpty() && $nonSurgicalAlerts->isEmpty())
                <p class="py-6 text-center text-sm text-slate-400">{{ __('No items near the reorder level') }}</p>
            @else
                <div class="max-h-[320px] space-y-4 overflow-y-auto pr-1">
                    {{-- Pharmaceutical — limit 3 --}}
                    <div>
                        <p class="mb-1.5 text-xs font-bold uppercase tracking-wide text-violet-700">{{ __('Pharmaceutical') }} <span class="font-normal normal-case text-slate-400">· {{ $lowDrugs->count() }}</span></p>
                        @if ($lowDrugs->isEmpty())
                            <p class="text-xs text-slate-400">{{ __('No low pharmaceutical items') }}</p>
                        @else
                            <ul class="space-y-1.5">
                                @foreach ($lowDrugs->take(3) as $drug)
                                    <li class="flex items-center justify-between rounded bg-red-50 px-3 py-2 text-sm">
                                        <span class="truncate pr-2 text-slate-700">{{ $drug->Name }}</span>
                                        <span class="shrink-0 text-xs text-red-600">{{ $drug->QtyInStock ?? 0 }}/{{ $drug->ReorderLvl ?? '-' }}</span>
                                    </li>
                                @endforeach
                            </ul>
                            @if ($lowDrugs->count() > 3)
                                <a href="{{ route('pharmacy.index', ['status' => 'low']) }}" class="mt-1 block text-center text-xs font-medium text-violet-600 hover:underline">+{{ $lowDrugs->count() - 3 }} {{ __('more') }} →</a>
                            @endif
                        @endif
                    </div>
                    {{-- Surgical supply — limit 3 --}}
                    <div>
                        <p class="mb-1.5 text-xs font-bold uppercase tracking-wide text-sky-700">{{ __('Surgical supply') }} <span class="font-normal normal-case text-slate-400">· {{ $surgicalAlerts->count() }}</span></p>
                        @if ($surgicalAlerts->isEmpty())
                            <p class="text-xs text-slate-400">{{ __('No low surgical items') }}</p>
                        @else
                            <ul class="space-y-1.5">
                                @foreach ($surgicalAlerts->take(3) as $item)
                                    <li class="flex items-center justify-between rounded bg-amber-50 px-3 py-2 text-sm">
                                        <span class="truncate pr-2 text-slate-700">{{ $item->Name }}</span>
                                        <span class="shrink-0 text-xs text-amber-700">{{ $item->QtyInStock ?? 0 }}/{{ $item->ReorderLvl ?? '-' }}</span>
                                    </li>
                                @endforeach
                            </ul>
                            @if ($surgicalAlerts->count() > 3)
                                <a href="{{ route('stock.index', ['type' => 'surgical', 'status' => 'low']) }}" class="mt-1 block text-center text-xs font-medium text-sky-600 hover:underline">+{{ $surgicalAlerts->count() - 3 }} {{ __('more') }} →</a>
                            @endif
                        @endif
                    </div>
                    {{-- Non-surgical supply — limit 3 --}}
                    <div>
                        <p class="mb-1.5 text-xs font-bold uppercase tracking-wide text-emerald-700">{{ __('Non-surgical supply') }} <span class="font-normal normal-case text-slate-400">· {{ $nonSurgicalAlerts->count() }}</span></p>
                        @if ($nonSurgicalAlerts->isEmpty())
                            <p class="text-xs text-slate-400">{{ __('No low non-surgical items') }}</p>
                        @else
                            <ul class="space-y-1.5">
                                @foreach ($nonSurgicalAlerts->take(3) as $item)
                                    <li class="flex items-center justify-between rounded bg-emerald-50 px-3 py-2 text-sm">
                                        <span class="truncate pr-2 text-slate-700">{{ $item->Name }}</span>
                                        <span class="shrink-0 text-xs text-emerald-700">{{ $item->QtyInStock ?? 0 }}/{{ $item->ReorderLvl ?? '-' }}</span>
                                    </li>
                                @endforeach
                            </ul>
                            @if ($nonSurgicalAlerts->count() > 3)
                                <a href="{{ route('stock.index', ['type' => 'non-surgical', 'status' => 'low']) }}" class="mt-1 block text-center text-xs font-medium text-emerald-600 hover:underline">+{{ $nonSurgicalAlerts->count() - 3 }} {{ __('more') }} →</a>
                            @endif
                        @endif
                    </div>
                </div>
            @endif
            <div class="mt-3 flex gap-2 text-xs">
                <span class="rounded-full bg-orange-100 px-2 py-1 font-semibold text-orange-700">{{ $nearExpiryCount }} near expiry</span>
                <span class="rounded-full bg-red-100 px-2 py-1 font-semibold text-red-700">{{ $expiredCount }} expired</span>
            </div>
            <a href="{{ route('pharmacy.index') }}" class="mt-2 block text-center text-xs font-medium text-blue-600 hover:underline">{{ __('View all') }} →</a>
        </div>

        {{-- Allergy Coverage Widget — Group B (compact horizontal) --}}
        <div class="rounded-lg bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between gap-2">
                <p class="text-sm font-medium text-slate-500">{{ __('Allergy coverage') }}</p>
                <a href="{{ route('allergies.index') }}" class="text-xs font-medium text-blue-600 hover:underline">{{ __('View') }} →</a>
            </div>
            <div class="mt-3 grid grid-cols-2 gap-4">
                <div>
                    <div class="flex items-baseline gap-2">
                        <p class="text-3xl font-bold leading-none text-slate-800">{{ number_format($allergyCoverage, 1) }}%</p>
                        <span class="text-xs text-slate-500">{{ $allergyPatientCount }}/{{ $totalPatients }}</span>
                    </div>
                    <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-slate-200">
                        <div class="h-full rounded-full bg-violet-500" style="width: {{ min(100, $allergyCoverage) }}%"></div>
                    </div>
                    <p class="mt-1 text-[11px] leading-tight text-slate-400">{{ __('with allergy record') }}</p>
                </div>
                <div class="rounded-lg border px-3 py-2.5 text-center {{ $severeAllergyCount > 0 ? 'border-red-200 bg-red-50' : 'border-slate-200 bg-slate-50' }}">
                    <p class="text-[11px] font-semibold uppercase tracking-wide {{ $severeAllergyCount > 0 ? 'text-red-700' : 'text-slate-500' }}">{{ __('Severe') }}</p>
                    <p class="text-2xl font-bold leading-none {{ $severeAllergyCount > 0 ? 'text-red-600' : 'text-slate-700' }}">{{ $severeAllergyCount }}</p>
                    <p class="mt-1 text-[11px] leading-tight {{ $severeAllergyCount > 0 ? 'text-red-600' : 'text-slate-400' }}">
                        @if($severeAllergyCount > 0)
                            {{ $severePatientCount }} {{ __('patients') }}
                        @else
                            {{ __('None') }}
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @vite('resources/js/dashboard.js')
@endpush