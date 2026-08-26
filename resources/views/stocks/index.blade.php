@extends('layouts.app')

@section('title', __('Stock'))

@section('content')
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-[#112D6E]">{{ __('Stock') }}</h1>
        <a href="{{ route('stock.create') }}"
           class="rounded bg-sky-600 px-4 py-2 text-sm font-medium text-white hover:bg-sky-700">
            {{ __('+ New item') }}
        </a>
    </div>

    <x-inventory-dashboard :counts="$counts" :urgent="$urgent" base-route="stock" />

    <form method="GET" action="{{ route('stock.index') }}" class="mb-4 flex flex-wrap items-center gap-3">
        <input type="text" name="search" value="{{ $search }}" placeholder="{{ __('Search name / code / category') }}"
               class="w-64 rounded border border-slate-300 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none">
        <select name="type"
                class="rounded border border-slate-300 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none">
            <option value="">{{ __('All types') }}</option>
            @foreach (\App\Models\CentralStock::itemTypes() as $value => $label)
                <option value="{{ $value }}" @selected(request('type') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <select name="status"
                class="rounded border border-slate-300 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none">
            <option value="">{{ __('All statuses') }}</option>
            @foreach (\App\Models\CentralStock::stockStatusLabels() as $value => $label)
                <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <button type="submit" class="rounded bg-[#112D6E] px-4 py-2 text-sm font-medium text-white hover:bg-[#0d2254]">
            {{ __('Filter') }}
        </button>
        <a href="{{ route('stock.index') }}" class="text-sm text-slate-500 hover:text-slate-700">{{ __('Clear') }}</a>
    </form>

    <div class="overflow-hidden rounded-lg bg-white shadow">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50">
                <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <th class="px-4 py-3">{{ __('Item No') }}</th>
                    <th class="px-4 py-3">{{ __('Name') }}</th>
                    <th class="px-4 py-3">{{ __('Category') }}</th>
                    <th class="px-4 py-3">{{ __('In stock') }}</th>
                    <th class="px-4 py-3">{{ __('Reorder level') }}</th>
                    <th class="px-4 py-3">{{ __('Supplier') }}</th>
                    <th class="px-4 py-3">{{ __('Status') }}</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($items as $item)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 font-mono text-xs">{{ $item->Item_No }}</td>
                        <td class="px-4 py-3">
                            <span class="font-medium text-slate-900">{{ $item->Name }}</span>
                            <span class="block text-xs text-slate-400">{{ $item->Description }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $item->ItemType === \App\Models\CentralStock::TYPE_SURGICAL ? 'bg-violet-100 text-violet-700' : 'bg-sky-100 text-sky-700' }}">
                                {{ \App\Models\CentralStock::itemTypes()[$item->ItemType] ?? $item->ItemType }}
                            </span>
                        </td>
                        <td class="px-4 py-3 font-semibold {{ ($item->QtyInStock ?? 0) === 0 ? 'text-red-600' : 'text-slate-800' }}">{{ $item->QtyInStock ?? 0 }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $item->ReorderLvl ?? '-' }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $item->supplier?->Name }}</td>
                        <td class="px-4 py-3"><x-stock-status-badge :status="$item->stock_status" /></td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <a href="{{ route('stock.edit', $item) }}" class="text-sky-600 hover:text-sky-800">{{ __('Edit') }}</a>
                            <a href="{{ route('stock.history', $item) }}" class="ml-2 text-slate-500 hover:text-slate-700">{{ __('History') }}</a>
                            <form action="{{ route('stock.destroy', $item) }}" method="POST"
                                  onsubmit="return confirm('{{ addslashes(__('Delete :name?', ['name' => $item->Name])) }}');" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="ml-2 text-red-600 hover:text-red-800">{{ __('Delete') }}</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-10 text-center text-slate-400">
                            {{ __('No stock items yet.') }}
                            <a href="{{ route('stock.create') }}" class="text-sky-600 hover:underline">{{ __('Add the first one') }}</a>.
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
