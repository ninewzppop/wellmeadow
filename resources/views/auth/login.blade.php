@extends('layouts.app')

@section('title', __('Login'))

@section('content')
    <div class="fixed inset-0 -z-10">
        <img src="{{ asset('images/background.jpg') }}" alt="" class="h-full w-full object-cover opacity-10">
    </div>

    <div class="flex min-h-[80vh] flex-col items-center justify-center">
        <div class="mb-6 flex flex-col items-center">
            <img src="{{ asset('images/logo.png') }}" alt="Logo" class="mb-3 h-16 w-16 rounded-2xl object-contain shadow-sm">
            <h2 class="text-xl font-bold tracking-wide text-[#112D6E]">WELLMEADOWS HOSPITAL</h2>
            <p class="mt-0.5 text-xs text-slate-400">Healthcare Management System</p>
        </div>

        <div class="w-full max-w-md">
            <form method="POST" action="{{ route('login.submit') }}" class="rounded-lg bg-white p-6 shadow">
                @csrf

                @error('g-recaptcha-response')
                    <div class="mb-4 rounded border border-[#9E2A2B]/30 bg-[#9E2A2B]/5 px-4 py-3 text-sm text-[#9E2A2B]">
                        {{ $message }}
                    </div>
                @enderror

                @error('email')
                    <div class="mb-4 rounded border border-[#9E2A2B]/30 bg-[#9E2A2B]/5 px-4 py-3 text-sm text-[#9E2A2B]">
                        {{ $message }}
                    </div>
                @enderror

                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-slate-900">{{ __('Sign in') }}</h1>
                    <p class="mt-1 text-sm text-slate-500">{{ __('Enter your account credentials to continue.') }}</p>
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700">{{ __('Email address') }} *</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                           class="mt-1 w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-[#112D6E] focus:outline-none">
                </div>

                <div class="mt-4">
                    <label for="password" class="block text-sm font-medium text-slate-700">{{ __('Password') }} *</label>
                    <input type="password" id="password" name="password" required
                           class="mt-1 w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-[#112D6E] focus:outline-none">
                </div>

                <div class="mt-4">
                    <label class="flex items-center gap-2 text-sm text-slate-600">
                        <input type="checkbox" name="remember" value="1"
                               class="rounded border-slate-300 text-[#112D6E] focus:ring-[#112D6E]">
                        {{ __('Remember me') }}
                    </label>
                </div>

                <div class="mt-6">
                    <div class="mb-4 flex justify-center">
                        <div class="g-recaptcha" data-sitekey="{{ config('recaptcha.site_key') }}" data-theme="light"></div>
                    </div>
                    <button type="submit"
                            class="w-full rounded bg-[#112D6E] px-6 py-2 text-sm font-medium text-white hover:bg-[#0D2355]">
                        {{ __('Sign in') }}
                    </button>
                </div>
            </form>

            <div class="mt-4 rounded-lg border border-dashed border-slate-300 bg-slate-50 p-4">
                <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">For test this website</p>
                <div class="space-y-1 text-sm text-slate-600">
                    <p><span class="font-medium text-slate-700">Email:</span> test@example.com</p>
                    <p><span class="font-medium text-slate-700">Password:</span> password</p>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
@endpush
