<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Login Tracking</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 text-gray-800">
<div class="container mx-auto p-6">

    <h1 class="text-3xl font-bold mb-6">User Login Tracking</h1>

    <!-- ✅ Flash Messages -->
    @if (session('success'))
        <div class="bg-green-100 border border-green-300 text-green-800 p-4 rounded mb-4">
            {{ session('success') }}
        </div>
    @elseif (session('error'))
        <div class="bg-red-100 border border-red-300 text-red-800 p-4 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    <!-- ✅ Filters -->
    <form method="GET" class="bg-white p-4 rounded shadow mb-6 flex flex-wrap gap-4 items-end">
        @php
            $systems = ['SCM', 'Odoo', 'D365 Live', 'Fit Express', 'FIT ERP', 'Fit Express UAT', 'FITerp UAT', 'OPS', 'OPS UAT'];
        @endphp

        <!-- Date Filter -->
        <div>
            <label for="filter" class="block font-semibold mb-1">Date Filter</label>
            <select name="filter" id="filter" class="border p-2 rounded w-40" onchange="this.form.submit()">
                <option value="">Custom Range</option>
                <option value="this_month" {{ request('filter') == 'this_month' ? 'selected' : '' }}>This Month</option>
                <option value="previous_month" {{ request('filter') == 'previous_month' ? 'selected' : '' }}>Previous Month</option>
                <option value="last_3_months" {{ request('filter') == 'last_3_months' ? 'selected' : '' }}>Last 3 Months</option>
            </select>
        </div>

        <!-- Start Date -->
        <div>
            <label for="start_date" class="block font-semibold mb-1">Start</label>
            <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}" class="border p-2 rounded w-44">
        </div>

        <!-- End Date -->
        <div>
            <label for="end_date" class="block font-semibold mb-1">End</label>
            <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}" class="border p-2 rounded w-44">
        </div>

        <!-- System Filter -->
        <div>
            <label for="system" class="block font-semibold mb-1">System</label>
            <select name="system" id="system" class="border p-2 rounded w-48">
                @foreach ($systems as $sys)
                    <option value="{{ $sys }}" {{ request('system', 'SCM') === $sys ? 'selected' : '' }}>
                        {{ $sys }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Actions -->
        <div class="flex gap-2">
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                Filter
            </button>
            <a href="{{ route('login-tracking.index') }}" class="text-blue-600 underline pt-2">Reset</a>
        </div>

        <!-- Link to non-logged-in users -->
        <div class="ml-auto">
            <a href="{{ route('login-tracking.non-logged-in', request()->only('filter', 'start_date', 'end_date', 'system')) }}"
               class="text-indigo-600 underline font-medium">
                View Non-Logged-In Users →
            </a>
        </div>
    </form>

    <!-- 🔍 Filter Summary -->
    <div class="bg-blue-50 border border-blue-200 text-blue-800 rounded p-4 mb-6 text-sm">
        <strong>Filter Summary:</strong><br>
        @php
            $activeSystem = request('system', 'SCM');
            $filterLabel = match(request('filter')) {
                'this_month' => 'This Month',
                'previous_month' => 'Previous Month',
                'last_3_months' => 'Last 3 Months',
                default => 'Custom Range'
            };
            $start = request('start_date') ? \Carbon\Carbon::parse(request('start_date'))->format('M j, Y') : now()->subDays(30)->format('M j, Y');
            $end = request('end_date') ? \Carbon\Carbon::parse(request('end_date'))->format('M j, Y') : now()->format('M j, Y');
        @endphp
        <div><span class="font-semibold">System:</span> {{ $activeSystem }}</div>
        <div><span class="font-semibold">Date Range:</span> {{ $start }} → {{ $end }} <span class="italic text-gray-500">({{ $filterLabel }})</span></div>
    </div>

    <!-- Summary -->
    <p class="text-gray-600 mb-3">Showing {{ $users->count() }} of {{ $users->total() }} users</p>

    <!-- ✅ Users Table -->
    <div class="overflow-x-auto">
        <table class="w-full bg-white rounded shadow text-sm">
            <thead>
                <tr class="bg-gray-200 text-left">
                    <th class="p-3">User</th>
                    <th class="p-3">Login Count</th>
                    <th class="p-3">Last Login</th>
                    <th class="p-3">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    <tr class="border-t hover:bg-gray-50">
                        <td class="p-3">
                            <div class="font-medium">{{ $user->displayName }}</div>
                            @if($user->email)
                                <div class="text-xs text-gray-500">{{ $user->email }}</div>
                            @endif
                        </td>
                        <td class="p-3 font-semibold {{ $user->login_count == 0 ? 'text-red-500' : 'text-green-600' }}">
                            {{ $user->login_count }}
                        </td>
                        <td class="p-3">
                            {{ optional($user->signIns->first())->date_utc
                                ? \Carbon\Carbon::parse($user->signIns->first()->date_utc)->format('D, M j, Y g:i A')
                                : 'No logins' }}
                        </td>
                        <td class="p-3">
                            <a href="{{ route('users.show', $user->id) }}"
                               class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600">
                                View
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="p-4 text-center text-gray-500">No users found for this period.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- ✅ Pagination -->
    <div class="mt-6">
        {{ $users->withQueryString()->links() }}
    </div>

</div>
</body>
</html>
