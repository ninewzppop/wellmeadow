@props(['counts', 'urgent', 'baseRoute', 'expiryCounts' => null])

@php
    $cards = [
        ['label' => __('Total items'), 'value' => $counts['total'], 'params' => [], 'color' => 'text-[#112D6E] bg-[#112D6E]/10'],
        ['label' => __('Normal'), 'value' => $counts['normal'], 'params' => ['status' => 'normal'], 'color' => 'text-emerald-700 bg-emerald-100'],
        ['label' => __('Low stock'), 'value' => $counts['low'], 'params' => ['status' => 'low'], 'color' => 'text-amber-800 bg-amber-100'],
        ['label' => __('Out of stock'), 'value' => $counts['out'], 'params' => ['status' => 'out'], 'color' => 'text-red-700 bg-red-100'],
    ];
    if ($expiryCounts !== null) {
        $cards[] = ['label' => __('Near expiry (<= 90 days)'), 'value' => $expiryCounts['nearExpiry'], 'params' => ['expiry' => 'near-expiry'], 'color' => 'text-orange-700 bg-orange-100'];
        $cards[] = ['label' => __('Expired'), 'value' => $expiryCounts['expired'], 'params' => ['expiry' => 'expired'], 'color' => 'text-rose-800 bg-rose-200'];
    }
@endphp

<div class="mb-6 space-y-6">
    <div class="grid grid-cols-2 gap-4 md:grid-cols-3 xl:grid-cols-5">
        @foreach ($cards as $card)
            <a href="?{{ http_build_query($card['params']) }}"
               class="rounded-lg bg-white p-4 shadow transition hover:shadow-md">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $card['label'] }}</p>
                <p class="mt-2 flex items-center justify-between">
                    <span class="text-3xl font-bold {{ explode(' ', $card['color'])[0] }}">{{ $card['value'] }}</span>
                    <span class="flex h-9 w-9 items-center justify-center rounded-full {{ $card['color'] }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-5 w-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
                        </svg>
                    </span>
                </p>
            </a>
        @endforeach
    </div>

    <div class="overflow-hidden rounded-lg bg-white shadow">
        <div class="border-b border-slate-200 px-4 py-3">
            <h2 class="text-sm font-bold uppercase tracking-wide text-[#112D6E]">{{ __('Restock urgently') }}</h2>
        </div>
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50">
                <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <th class="px-4 py-2">{{ __('Code') }}</th>
                    <th class="px-4 py-2">{{ __('Name') }}</th>
                    <th class="px-4 py-2">{{ __('In stock') }}</th>
                    <th class="px-4 py-2">{{ __('Reorder level') }}</th>
                    <th class="px-4 py-2">{{ __('Status') }}</th>
                    <th class="px-4 py-2"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($urgent as $row)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-2 font-mono text-xs">{{ $row->Drug_No ?? $row->Item_No }}</td>
                        <td class="px-4 py-2 font-medium text-slate-900">{{ $row->Name }}</td>
                        <td class="px-4 py-2 font-semibold {{ ($row->QtyInStock ?? 0) === 0 ? 'text-red-600' : 'text-amber-700' }}">{{ $row->QtyInStock ?? 0 }}</td>
                        <td class="px-4 py-2 text-slate-600">{{ $row->ReorderLvl ?? '-' }}</td>
                        <td class="px-4 py-2"><x-stock-status-badge :status="$row->stock_status" /></td>
                        <td class="px-4 py-2 text-right whitespace-nowrap">
                            <a href="{{ route($baseRoute.'.edit', $row) }}"
                               class="rounded bg-amber-500 px-3 py-1.5 text-xs font-medium text-white hover:bg-amber-600">
                                {{ __('Restock') }}
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-6 text-center text-slate-400">{{ __('Nothing needs restocking right now.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
