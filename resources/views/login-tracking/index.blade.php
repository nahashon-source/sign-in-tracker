<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Login Tracking</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
<div class="container mx-auto p-4">

    <h1 class="text-3xl font-bold mb-6 text-gray-800">User Login Tracking</h1>

    <!-- Flash Messages -->
    @if (session('success'))
        <div class="bg-green-200 text-green-800 p-3 rounded mb-4">{{ session('success') }}</div>
    @elseif (session('error'))
        <div class="bg-red-200 text-red-800 p-3 rounded mb-4">{{ session('error') }}</div>
    @endif

    <!-- System + Date Filter -->
    <form method="GET" class="mb-6 flex flex-wrap gap-3 items-end bg-white p-4 rounded shadow">

        <!-- Predefined Date Filters -->
        <div>
            <label for="filter" class="block font-medium">Date Filter</label>
            <select name="filter" id="filter" class="border p-2 rounded" onchange="this.form.submit()">
                <option value="">Custom Range</option>
                <option value="this_month" {{ request('filter') == 'this_month' ? 'selected' : '' }}>This Month</option>
                <option value="previous_month" {{ request('filter') == 'previous_month' ? 'selected' : '' }}>Previous Month</option>
                <option value="last_3_months" {{ request('filter') == 'last_3_months' ? 'selected' : '' }}>Last 3 Months</option>
            </select>
        </div>

        <!-- Start Date -->
        <div>
            <label for="start_date" class="block font-medium">Start</label>
            <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}" class="border p-2 rounded">
        </div>

        <!-- End Date -->
        <div>
            <label for="end_date" class="block font-medium">End</label>
            <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}" class="border p-2 rounded">
        </div>

        <!-- System Filter -->
        <div>
            <label for="system" class="block font-medium">System</label>
            <select name="system" id="system" class="border p-2 rounded">
                @php
                    $systems = ['SCM', 'Odoo', 'D365 Live', 'Fit Express', 'FIT ERP', 'Fit Express UAT', 'FITerp UAT', 'OPS', 'OPS UAT'];
                @endphp
                @foreach ($systems as $sys)
                    <option value="{{ $sys }}" {{ request('system', 'SCM') === $sys ? 'selected' : '' }}>
                        {{ $sys }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Filter and Reset -->
        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Filter</button>

        <a href="{{ route('login-tracking.index') }}" class="text-blue-500 underline ml-2">Reset</a>
        <a href="{{ route('login-tracking.non-logged-in', ['filter' => request('filter'), 'start_date' => request('start_date'), 'end_date' => request('end_date'), 'system' => request('system')]) }}"
           class="ml-auto text-blue-600 font-medium underline">
            View Non-Logged-In Users
        </a>
    </form>

    <!-- 🔍 Active Filter Summary -->
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
            $start = request('start_date') ? \Carbon\Carbon::parse(request('start_date'))->format('M j, Y') : '30 days ago';
            $end = request('end_date') ? \Carbon\Carbon::parse(request('end_date'))->format('M j, Y') : \Carbon\Carbon::now()->format('M j, Y');
        @endphp
        <div><span class="font-semibold">System:</span> {{ $activeSystem }}</div>
        <div><span class="font-semibold">Date Range:</span> {{ $start }} → {{ $end }} <span class="italic text-gray-500">({{ $filterLabel }})</span></div>
    </div>

    <p class="text-gray-600 mb-3">Showing {{ $users->count() }} of {{ $users->total() }} users</p>

    <!-- User Table -->
    <div class="overflow-x-auto">
        <table class="w-full bg-white shadow rounded">
            <thead>
                <tr class="bg-gray-200 text-left text-sm">
                    <th class="p-3">User</th>
                    <th class="p-3">Login Count</th>
                    <th class="p-3">Last Login</th>
                    <th class="p-3">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    <tr class="hover:bg-gray-50 border-t text-sm">
                        <td class="p-3 font-medium text-gray-800">
                            {{ $user->displayName }}
                            @if($user->email)
                                <div class="text-gray-500 text-xs">{{ $user->email }}</div>
                            @endif
                        </td>
                        <td class="p-3">
                            @if($user->login_count == 0)
                                <span class="text-red-500 font-semibold">{{ $user->login_count }}</span>
                            @else
                                <span class="text-green-600 font-semibold">{{ $user->login_count }}</span>
                            @endif
                        </td>
                        <td class="p-3">
                            {{ $user->signIns->first() ? \Carbon\Carbon::parse($user->signIns->first()->date_utc)->format('D, M j, Y g:i A') : 'No logins' }}
                        </td>
                        <td class="p-3">
                            <a href="{{ route('users.show', $user->id) }}" class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600">
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

    <!-- Pagination -->
    <div class="mt-6">
        {{ $users->withQueryString()->links() }}
    </div>

</div>
</body>
</html>
