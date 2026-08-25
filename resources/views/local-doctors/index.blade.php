@extends('layouts.app')

@section('title', __('Local Doctors'))

@section('content')
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-[#112D6E]">{{ __('Local Doctors') }}</h1>
        <a href="{{ route('local-doctors.create') }}"
           class="rounded bg-sky-600 px-4 py-2 text-sm font-medium text-white hover:bg-sky-700">
            {{ __('+ New doctor') }}
        </a>
    </div>

    <div class="overflow-hidden rounded-lg bg-white shadow">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50">
                <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <th class="px-4 py-3">{{ __('Clinic No') }}</th>
                    <th class="px-4 py-3">{{ __('Name') }}</th>
                    <th class="px-4 py-3">{{ __('Address') }}</th>
                    <th class="px-4 py-3">{{ __('Tel No') }}</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($doctors as $doctor)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 font-mono text-xs">{{ $doctor->Clinic_No }}</td>
                        <td class="px-4 py-3 font-medium text-slate-900">{{ $doctor->full_name }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $doctor->Address }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $doctor->TelNo }}</td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <a href="{{ route('local-doctors.show', $doctor) }}" class="text-sky-600 hover:text-sky-800">{{ __('View') }}</a>
                            <a href="{{ route('local-doctors.edit', $doctor) }}" class="ml-2 text-sky-600 hover:text-sky-800">{{ __('Edit') }}</a>
                            <form action="{{ route('local-doctors.destroy', $doctor) }}" method="POST"
                                  onsubmit="return confirm('{{ addslashes(__('Delete :name?', ['name' => $doctor->full_name])) }}');" class="inline ml-2">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800">{{ __('Delete') }}</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-10 text-center text-slate-400">
                            {{ __('No local doctor records yet.') }}
                            <a href="{{ route('local-doctors.create') }}" class="text-sky-600 hover:underline">{{ __('Add the first one') }}</a>.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $doctors->links() }}
    </div>
@endsection