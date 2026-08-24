@extends('layouts.app')

@section('title', $supplier->Name ?? __('Supplier Details'))

@section('content')
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-[#112D6E]">{{ $supplier->Name ?? __('Supplier Details') }}</h1>
        <div class="flex gap-3">
            <a href="{{ route('suppliers.edit', $supplier) }}"
               class="rounded bg-sky-600 px-4 py-2 text-sm font-medium text-white hover:bg-sky-700">
                {{ __('Edit') }}
            </a>
            <a href="{{ route('suppliers.index') }}"
               class="rounded bg-slate-100 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-200">
                {{ __('Back to list') }}
            </a>
        </div>
    </div>

    <div class="rounded-lg bg-white shadow">
        <dl class="grid grid-cols-1 gap-6 p-6 md:grid-cols-2">
            <div>
                <dt class="text-sm font-medium text-slate-500">{{ __('Supplier No') }}</dt>
                <dd class="mt-1 text-sm font-mono text-slate-900">{{ $supplier->Suppl_No }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-slate-500">{{ __('Name') }}</dt>
                <dd class="mt-1 text-sm text-slate-900">{{ $supplier->Name }}</dd>
            </div>
            <div class="md:col-span-2">
                <dt class="text-sm font-medium text-slate-500">{{ __('Address') }}</dt>
                <dd class="mt-1 text-sm text-slate-600">{{ $supplier->Address }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-slate-500">{{ __('Tel No') }}</dt>
                <dd class="mt-1 text-sm text-slate-600">{{ $supplier->TelNo }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-slate-500">{{ __('Fax No') }}</dt>
                <dd class="mt-1 text-sm text-slate-600">{{ $supplier->FaxNo }}</dd>
            </div>
        </dl>
    </div>
@endsection