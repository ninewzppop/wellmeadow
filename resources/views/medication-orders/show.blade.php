@extends('layouts.app')

@section('title', __('Medication order') . ' ' . $order->Order_No)

@section('content')
<div class="mx-auto max-w-4xl">
    <a href="{{ route('medications.index') }}" class="mb-4 inline-flex items-center gap-1 text-xs font-medium text-slate-500 hover:text-slate-700">
        ← {{ __('Back to dispensing queue') }}
    </a>

    @if (session('status'))
        <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <ul class="list-inside list-disc">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="text-lg font-bold text-slate-900">{{ __('Medication order') }} {{ $order->Order_No }}</h1>
                <p class="mt-1 text-xs text-slate-500">
                    {{ __('Order time') }}: {{ $order->OrderedAt?->format('d/m/Y H:i') ?? '—' }}
                    · {{ __('Status') }}:
                    @if ($order->status === 'pending')
                        <span class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-700">{{ __('Pending') }}</span>
                    @elseif ($order->status === 'dispensed')
                        <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-700">{{ __('Dispensed') }}</span>
                    @else
                        <span class="rounded-full bg-red-100 px-2 py-0.5 text-xs font-semibold text-red-700">{{ __('Cancelled') }}</span>
                    @endif
                </p>
            </div>
            <div class="text-right text-xs text-slate-500">
                <p><span class="font-medium text-slate-700">{{ __('Patient') }}:</span> {{ $order->patient?->full_name ?? '—' }} ({{ __('HN') }} {{ $order->Pt_No }})</p>
                <p><span class="font-medium text-slate-700">{{ __('Prescriber') }}:</span> {{ $order->prescriber?->full_name ?? $order->Stf_No ?? '—' }}</p>
                @if ($order->appointment)
                    <p><span class="font-medium text-slate-700">{{ __('Appointment') }}:</span> {{ $order->Appt_No }}</p>
                @endif
            </div>
        </div>

        @if ($conflicts->isNotEmpty())
            <div class="mt-4 rounded-xl border border-red-200 bg-red-50 p-3 text-xs text-red-800">
                <p class="font-bold">⚠️ {{ __('Allergy warning') }}</p>
                <p class="mt-1">{{ __('The following ordered drugs match a recorded allergy for this patient: :list.', ['list' => $conflicts->map(fn ($a) => $a->Allergy_Name ?: $a->drug?->Name ?: $a->Drug_No)->unique()->implode(', ')]) }}</p>
            </div>
        @endif

        <div class="mt-6 overflow-hidden rounded-xl border border-slate-200">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-400">
                        <th class="px-4 py-2">{{ __('Drug') }}</th>
                        <th class="px-4 py-2">{{ __('Units per day') }}</th>
                        <th class="px-4 py-2">{{ __('Administration method') }}</th>
                        <th class="px-4 py-2">{{ __('Start date') }}</th>
                        <th class="px-4 py-2">{{ __('Finish date') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($order->items as $item)
                        <tr>
                            <td class="px-4 py-2 font-medium text-slate-800">{{ $item->drug?->Name ?? $item->Drug_No }} <span class="text-xs font-normal text-slate-400">({{ $item->Drug_No }})</span></td>
                            <td class="px-4 py-2 text-slate-600">{{ $item->UnitsPerDay }}</td>
                            <td class="px-4 py-2 text-slate-600">{{ $item->AdminMethod }}</td>
                            <td class="px-4 py-2 text-slate-600">{{ $item->StartDate?->format('d/m/Y') }}</td>
                            <td class="px-4 py-2 text-slate-600">{{ $item->FinishDate?->format('d/m/Y') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($order->status === 'pending')
            <div class="mt-6 grid gap-4 sm:grid-cols-2">
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4">
                    <h3 class="text-sm font-bold text-emerald-800">{{ __('Confirm dispense & payment') }}</h3>
                    <p class="mt-1 text-xs text-emerald-700">{{ __('Confirm that the medication was dispensed and payment was received. This will record the items in the patient medication history and remove the order from the queue.') }}</p>
                    <form method="POST" action="{{ route('medications.confirm', $order) }}" class="mt-3">
                        @csrf
                        <button type="submit"
                                onclick="return confirm('{{ __('Confirm dispense and payment for this order?') }}')"
                                class="w-full rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">
                            {{ __('Confirm dispense & payment') }}
                        </button>
                    </form>
                </div>

                <div class="rounded-xl border border-red-200 bg-red-50 p-4">
                    <h3 class="text-sm font-bold text-red-800">{{ __('Cancel order') }}</h3>
                    <p class="mt-1 text-xs text-red-700">{{ __('If the order was made in error or the patient declined, cancel it here with a reason.') }}</p>
                    <form method="POST" action="{{ route('medications.cancel', $order) }}" class="mt-3 space-y-2">
                        @csrf
                        <textarea name="CancelReason" rows="2" required maxlength="255" placeholder="{{ __('Cancellation reason') }} *"
                                  class="w-full rounded-lg border border-red-200 bg-white px-3 py-2 text-sm focus:border-red-400 focus:outline-none"></textarea>
                        <button type="submit"
                                onclick="return confirm('{{ __('Cancel this medication order?') }}')"
                                class="w-full rounded-lg bg-white px-4 py-2 text-sm font-semibold text-red-700 ring-1 ring-inset ring-red-200 hover:bg-red-50">
                            {{ __('Cancel order') }}
                        </button>
                    </form>
                </div>
            </div>
        @elseif ($order->status === 'dispensed')
            <p class="mt-6 rounded-xl bg-emerald-50 px-4 py-3 text-center text-sm font-medium text-emerald-700">{{ __('This order has been dispensed and recorded in the patient history.') }}</p>
        @else
            <div class="mt-6 rounded-xl bg-slate-50 px-4 py-3 text-sm text-slate-600">
                <p class="font-semibold text-slate-700">{{ __('Cancelled') }}</p>
                <p class="mt-1 text-xs">{{ $order->CancelReason }}</p>
                <p class="mt-1 text-xs text-slate-400">{{ $order->CancelledAt?->format('d/m/Y H:i') }}</p>
            </div>
        @endif
    </div>
</div>
@endsection
