@extends('layouts.app')

@section('title', $supplier->exists ? __('Edit Supplier') : __('New Supplier'))

@section('content')
    @php
        $editing = $supplier->exists;
    @endphp

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-[#112D6E]">{{ $editing ? __('Edit supplier') : __('New supplier') }}</h1>
    </div>

    <form method="POST"
          action="{{ $editing ? route('suppliers.update', $supplier) : route('suppliers.store') }}"
          class="space-y-8">
        @csrf
        @if ($editing)
            @method('PUT')
        @endif

        <section class="rounded-lg bg-white p-6 shadow">
            <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-slate-500">{{ __('Supplier details') }}</h2>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                @if ($editing)
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Supplier No *') }}</label>
                    <input type="text" value="{{ $supplier->Suppl_No }}" disabled
                           class="mt-1 w-full rounded border border-slate-300 bg-slate-100 px-3 py-2 text-sm text-slate-500 cursor-not-allowed">
                </div>
                @endif
                <div>
                    <label for="Name" class="block text-sm font-medium text-slate-700">{{ __('Name') }}</label>
                    <input type="text" id="Name" name="Name" value="{{ old('Name', $supplier->Name) }}"
                           class="mt-1 w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none">
                </div>
                <div class="md:col-span-2">
                    <label for="Address" class="block text-sm font-medium text-slate-700">{{ __('Address') }}</label>
                    <input type="text" id="Address" name="Address" value="{{ old('Address', $supplier->Address) }}"
                           class="mt-1 w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none">
                </div>
                <div>
                    <label for="TelNo" class="block text-sm font-medium text-slate-700">{{ __('Tel No') }}</label>
                    <input type="text" id="TelNo" name="TelNo" value="{{ old('TelNo', $supplier->TelNo) }}"
                           class="mt-1 w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none">
                </div>
                <div>
                    <label for="FaxNo" class="block text-sm font-medium text-slate-700">{{ __('Fax No') }}</label>
                    <input type="text" id="FaxNo" name="FaxNo" value="{{ old('FaxNo', $supplier->FaxNo) }}"
                           class="mt-1 w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none">
                </div>
            </div>
        </section>

        <div class="flex items-center gap-3">
            <button type="submit"
                    class="rounded bg-sky-600 px-6 py-2 text-sm font-medium text-white hover:bg-sky-700">
                {{ $editing ? __('Save changes') : __('Create supplier') }}
            </button>
            <a href="{{ route('suppliers.index') }}" class="text-sm text-slate-600 hover:text-slate-900">{{ __('Cancel') }}</a>
        </div>
    </form>
@endsection