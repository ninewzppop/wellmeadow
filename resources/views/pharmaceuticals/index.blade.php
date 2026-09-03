@extends('layouts.app')

@section('title', __('Pharmacy'))

@section('content')
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-[#112D6E]">{{ __('Pharmacy') }}</h1>
        <a href="{{ route('pharmacy.create') }}"
           class="rounded bg-sky-600 px-4 py-2 text-sm font-medium text-white hover:bg-sky-700">
            {{ __('+ New drug') }}
        </a>
    </div>

    <x-inventory-dashboard :counts="$counts" :urgent="$urgent" base-route="pharmacy" :expiry-counts="['expired' => $counts['expired']]" />

    <form method="GET" action="{{ route('pharmacy.index') }}" class="mb-4 flex flex-wrap items-center gap-3">
        <input type="text" name="search" value="{{ $search }}" placeholder="{{ __('Search name / code / description') }}"
               class="w-64 rounded border border-slate-300 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none">
        <select name="status"
                class="rounded border border-slate-300 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none">
            <option value="">{{ __('All statuses') }}</option>
            @foreach (\App\Models\Pharmaceutical::stockStatusLabels() as $value => $label)
                <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <select name="expiry"
                class="rounded border border-slate-300 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none">
            <option value="">{{ __('All expiry states') }}</option>
            @foreach (\App\Models\Pharmaceutical::expiryStatusLabels() as $value => $label)
                <option value="{{ $value }}" @selected(request('expiry') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <button type="submit" class="rounded bg-[#112D6E] px-4 py-2 text-sm font-medium text-white hover:bg-[#0d2254]">
            {{ __('Filter') }}
        </button>
        <a href="{{ route('pharmacy.index') }}" class="text-sm text-slate-500 hover:text-slate-700">{{ __('Clear') }}</a>
    </form>

    <div class="overflow-hidden rounded-lg bg-white shadow">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50">
                <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <th class="px-4 py-3">{{ __('Drug No') }}</th>
                    <th class="px-4 py-3">{{ __('Name') }}</th>
                    <th class="px-4 py-3">{{ __('Dosage') }}</th>
                    <th class="px-4 py-3">{{ __('Method') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('In stock') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('Reorder level') }}</th>
                    <th class="px-4 py-3">{{ __('Expiry date') }}</th>
                    <th class="px-4 py-3">{{ __('Status') }}</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($items as $drug)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 font-mono text-xs">{{ $drug->Drug_No }}</td>
                        <td class="px-4 py-3">
                            <span class="font-medium text-slate-900">{{ $drug->Name }}</span>
                            <span class="block text-xs text-slate-400">{{ $drug->Description }}</span>
                        </td>
                        <td class="px-4 py-3 text-slate-600">{{ $drug->Dosage }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $drug->AdminMethod }}</td>
                        <td class="px-4 py-3 text-right font-semibold {{ ($drug->QtyInStock ?? 0) === 0 ? 'text-red-600' : 'text-slate-800' }}">{{ $drug->QtyInStock ?? 0 }}</td>
                        <td class="px-4 py-3 text-right text-slate-600">{{ $drug->ReorderLvl ?? '-' }}</td>
                        <td class="px-4 py-3">
                            @if ($drug->ExpiryDate)
                                <span class="{{ $drug->expiry_status === 'expired' ? 'font-semibold text-rose-700' : ($drug->expiry_status === 'near-expiry' ? 'text-orange-600' : 'text-slate-600') }}">
                                    {{ $drug->ExpiryDate->format('d/m/Y') }}
                                </span>
                            @else
                                <span class="text-slate-400">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3"><x-stock-status-badge :status="$drug->stock_status" /></td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <a href="{{ route('pharmacy.edit', $drug) }}" class="text-sky-600 hover:text-sky-800">{{ __('Edit') }}</a>
                            <a href="{{ route('pharmacy.history', $drug) }}" class="ml-2 text-slate-500 hover:text-slate-700">{{ __('History') }}</a>
                            <form action="{{ route('pharmacy.destroy', $drug) }}" method="POST"
                                  onsubmit="return confirm('{{ addslashes(__('Delete :name?', ['name' => $drug->Name])) }}');" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="ml-2 text-red-600 hover:text-red-800">{{ __('Delete') }}</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-4 py-10 text-center text-slate-400">
                            {{ __('No pharmaceutical records yet.') }}
                            <a href="{{ route('pharmacy.create') }}" class="text-sky-600 hover:underline">{{ __('Add the first one') }}</a>.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $items->links() }}
    </div>
@endsection
