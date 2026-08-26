@extends('layouts.app')

@section('title', $item->exists ? __('Edit Drug') : __('New Drug'))

@section('content')
    @php
        $editing = $item->exists;
        $route = $editing ? route('pharmacy.update', $item) : route('pharmacy.store');
    @endphp

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-[#112D6E]">
            {{ $editing ? __('Edit drug').' — '.$item->Drug_No : __('New drug') }}
        </h1>
    </div>

    <form method="POST" action="{{ $route }}" class="space-y-8">
        @csrf
        @if ($editing)
            @method('PUT')
        @endif

        <section class="rounded-lg bg-white p-6 shadow">
            <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-slate-500">{{ __('Drug details') }}</h2>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label for="Name" class="block text-sm font-medium text-slate-700">{{ __('Name *') }}</label>
                    <input type="text" id="Name" name="Name" value="{{ old('Name', $item->Name) }}" required maxlength="100"
                           class="mt-1 w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none">
                </div>
                <div>
                    <label for="Suppl_No" class="block text-sm font-medium text-slate-700">{{ __('Supplier') }}</label>
                    <select id="Suppl_No" name="Suppl_No"
                            class="mt-1 w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none">
                        <option value="">-</option>
                        @foreach ($suppliers as $supplier)
                            <option value="{{ $supplier->Suppl_No }}" @selected(old('Suppl_No', $item->Suppl_No) === $supplier->Suppl_No)>
                                {{ $supplier->Name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="Dosage" class="block text-sm font-medium text-slate-700">{{ __('Dosage') }}</label>
                    <input type="text" id="Dosage" name="Dosage" value="{{ old('Dosage', $item->Dosage) }}" maxlength="30"
                           class="mt-1 w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none">
                </div>
                <div>
                    <label for="AdminMethod" class="block text-sm font-medium text-slate-700">{{ __('Admin method') }}</label>
                    <input type="text" id="AdminMethod" name="AdminMethod" value="{{ old('AdminMethod', $item->AdminMethod) }}" maxlength="30"
                           class="mt-1 w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none">
                </div>
                <div>
                    <label for="ExpiryDate" class="block text-sm font-medium text-slate-700">{{ __('Expiry date') }}</label>
                    <input type="date" id="ExpiryDate" name="ExpiryDate" value="{{ old('ExpiryDate', $item->ExpiryDate?->format('Y-m-d')) }}"
                           class="mt-1 w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none">
                </div>
                <div class="md:col-span-2">
                    <label for="Description" class="block text-sm font-medium text-slate-700">{{ __('Description') }}</label>
                    <input type="text" id="Description" name="Description" value="{{ old('Description', $item->Description) }}" maxlength="255"
                           class="mt-1 w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none">
                </div>
            </div>
        </section>

        <section class="rounded-lg bg-white p-6 shadow">
            <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-slate-500">{{ __('Stock levels') }}</h2>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div>
                    <label for="QtyInStock" class="block text-sm font-medium text-slate-700">{{ __('Qty in stock *') }}</label>
                    <input type="number" id="QtyInStock" name="QtyInStock" min="0" required value="{{ old('QtyInStock', $item->QtyInStock ?? 0) }}"
                           {{ $editing ? 'readonly' : '' }}
                           class="mt-1 w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none {{ $editing ? 'bg-slate-100' : '' }}">
                    @if ($editing)
                        <p class="mt-1 text-xs text-slate-400">{{ __('Change via restock / adjust actions below.') }}</p>
                    @endif
                </div>
                <div>
                    <label for="ReorderLvl" class="block text-sm font-medium text-slate-700">{{ __('Reorder level *') }}</label>
                    <input type="number" id="ReorderLvl" name="ReorderLvl" min="0" required value="{{ old('ReorderLvl', $item->ReorderLvl ?? 0) }}"
                           class="mt-1 w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none">
                </div>
                <div>
                    <label for="CostPerUnit" class="block text-sm font-medium text-slate-700">{{ __('Cost per unit') }}</label>
                    <input type="number" step="0.01" min="0" id="CostPerUnit" name="CostPerUnit" value="{{ old('CostPerUnit', $item->CostPerUnit) }}"
                           class="mt-1 w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none">
                </div>
            </div>
            @if ($errors->has('QtyInStock') || $errors->has('ReorderLvl'))
                <p class="mt-2 text-xs text-red-600">{{ __('Required — used to calculate low-stock status.') }}</p>
            @endif
        </section>

        <div class="flex items-center gap-3">
            <button type="submit"
                    class="rounded bg-sky-600 px-6 py-2 text-sm font-medium text-white hover:bg-sky-700">
                {{ $editing ? __('Save changes') : __('Create drug') }}
            </button>
            <a href="{{ route('pharmacy.index') }}" class="text-sm text-slate-600 hover:text-slate-900">{{ __('Cancel') }}</a>
        </div>
    </form>

    @if ($editing)
        <section id="stock-actions" class="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-2">
            <form method="POST" action="{{ route('pharmacy.restock', $item) }}" class="rounded-lg bg-white p-6 shadow">
                @csrf
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-emerald-600">{{ __('Restock (+ quantity in)') }}</h2>
                <div class="flex items-end gap-3">
                    <div class="w-32">
                        <label for="restock-qty" class="block text-sm font-medium text-slate-700">{{ __('Quantity') }}</label>
                        <input type="number" id="restock-qty" name="quantity" min="1" required
                               class="mt-1 w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none">
                    </div>
                    <div class="flex-1">
                        <label for="restock-note" class="block text-sm font-medium text-slate-700">{{ __('Note') }}</label>
                        <input type="text" id="restock-note" name="note" maxlength="255"
                               class="mt-1 w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none">
                    </div>
                    <button type="submit" class="rounded bg-emerald-600 px-5 py-2 text-sm font-medium text-white hover:bg-emerald-700">
                        {{ __('Restock') }}
                    </button>
                </div>
            </form>

            <form method="POST" action="{{ route('pharmacy.adjust', $item) }}" class="rounded-lg bg-white p-6 shadow">
                @csrf
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-red-600">{{ __('Adjust down (- quantity out)') }}</h2>
                <div class="flex items-end gap-3">
                    <div class="w-32">
                        <label for="adjust-qty" class="block text-sm font-medium text-slate-700">{{ __('Quantity') }}</label>
                        <input type="number" id="adjust-qty" name="quantity" min="1" max="{{ $item->QtyInStock ?? 0 }}" required
                               class="mt-1 w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none">
                    </div>
                    <div class="flex-1">
                        <label for="adjust-note" class="block text-sm font-medium text-slate-700">{{ __('Reason *') }}</label>
                        <input type="text" id="adjust-note" name="note" maxlength="255" required
                               class="mt-1 w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none">
                    </div>
                    <button type="submit" class="rounded bg-red-600 px-5 py-2 text-sm font-medium text-white hover:bg-red-700">
                        {{ __('Adjust') }}
                    </button>
                </div>
            </form>
        </section>
    @endif
@endsection
