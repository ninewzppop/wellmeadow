@extends('layouts.app')

@section('title', __('Change password'))

@section('content')
    <div class="mx-auto max-w-md">
        <div class="rounded-lg bg-white p-6 shadow">
            <h1 class="text-2xl font-bold text-slate-900">{{ __('Change password') }}</h1>
            <p class="mt-1 text-sm text-slate-500">{{ __('Signed in as :email.', ['email' => auth()->user()->email]) }}</p>

            <form method="POST" action="{{ route('password.update') }}" class="mt-6 space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label for="current_password" class="block text-sm font-medium text-slate-700">{{ __('Current password') }} *</label>
                    <input type="password" id="current_password" name="current_password" required autofocus
                        class="mt-1 w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-[#112D6E] focus:outline-none">
                    @error('current_password')
                        <p class="mt-1 text-xs text-[#9E2A2B]">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700">{{ __('New password') }} *</label>
                    <input type="password" id="password" name="password" required
                        class="mt-1 w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-[#112D6E] focus:outline-none">
                    @error('password')
                        <p class="mt-1 text-xs text-[#9E2A2B]">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-slate-700">{{ __('Confirm new password') }} *</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required
                        class="mt-1 w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-[#112D6E] focus:outline-none">
                </div>

                <button type="submit"
                    class="w-full rounded bg-[#112D6E] px-6 py-2 text-sm font-medium text-white hover:bg-[#0D2355]">
                    {{ __('Update password') }}
                </button>
            </form>
        </div>
    </div>
@endsection
