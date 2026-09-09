@extends('layouts.app')

@section('title', __('Forbidden'))

@section('content')
    <div class="mx-auto flex min-h-[60vh] max-w-lg flex-col items-center justify-center text-center">
        <p class="text-6xl font-extrabold text-[#112D6E]">403</p>
        <h1 class="mt-4 text-xl font-bold text-slate-900">{{ __('Access denied') }}</h1>
        <p class="mt-2 text-sm text-slate-500">
            {{ __('Your role does not have permission to open this page. If you believe this is a mistake, please contact your administrator.') }}
        </p>
        <a href="{{ $backUrl ?? route('dashboard.index') }}"
            class="mt-6 inline-block rounded bg-[#112D6E] px-6 py-2 text-sm font-medium text-white hover:bg-[#0D2355]">
            {{ __('Back to my workspace') }}
        </a>
    </div>
@endsection
