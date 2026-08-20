<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Hospital Staff') &middot; Hospital</title>
    @vite('resources/css/app.css')
</head>
<body class="h-full font-sans text-slate-800 antialiased">
    <nav class="bg-sky-800 text-white shadow">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-3">
            <a href="{{ route('staff.index') }}" class="text-lg font-bold">Hospital Staff System</a>
            <div class="flex gap-4 text-sm font-medium">
                <a href="{{ route('staff.index') }}" class="hover:text-sky-200">Staff</a>
                <a href="{{ route('staff.search') }}" class="hover:text-sky-200">Search</a>
                <a href="{{ route('allocations.index') }}" class="hover:text-sky-200">Allocations</a>
                <a href="{{ route('wards.report') }}" class="hover:text-sky-200">Ward Report</a>
                @auth
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="hover:text-sky-200">Logout</button>
                    </form>
                @endauth
            </div>
        </div>
    </nav>

    <main class="mx-auto max-w-6xl px-4 py-8">
        @if (session('status'))
            <div class="mb-4 rounded border border-green-300 bg-green-50 px-4 py-3 text-sm text-green-800">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 rounded border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-800">
                <strong>Please fix the following errors:</strong>
                <ul class="mt-1 list-inside list-disc">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>