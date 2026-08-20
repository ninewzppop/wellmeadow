@extends('layouts.app')

@section('title', __('Login'))

@section('content')
    <div class="mx-auto max-w-md">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-slate-900">{{ __('Sign in') }}</h1>
            <p class="mt-1 text-sm text-slate-500">{{ __('Enter your account credentials to continue.') }}</p>
        </div>

        <form method="POST" action="{{ route('login.submit') }}" class="rounded-lg bg-white p-6 shadow">
            @csrf

            <div>
                <label for="email" class="block text-sm font-medium text-slate-700">{{ __('Email address') }} *</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                       class="mt-1 w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none">
            </div>

            <div class="mt-4">
                <label for="password" class="block text-sm font-medium text-slate-700">{{ __('Password') }} *</label>
                <input type="password" id="password" name="password" required
                       class="mt-1 w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none">
            </div>

            <div class="mt-4">
                <label class="flex items-center gap-2 text-sm text-slate-600">
                    <input type="checkbox" name="remember" value="1"
                           class="rounded border-slate-300 text-sky-600 focus:ring-sky-500">
                    {{ __('Remember me') }}
                </label>
            </div>

            <div class="mt-6">
                <button type="submit"
                        class="w-full rounded bg-sky-600 px-6 py-2 text-sm font-medium text-white hover:bg-sky-700">
                    {{ __('Sign in') }}
                </button>
            </div>
        </form>
    </div>
@endsection