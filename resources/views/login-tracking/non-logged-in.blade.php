<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Non-Logged-In Users</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
<div class="container mx-auto p-4">

    <h1 class="text-2xl font-bold mb-4 text-gray-800">Users Who Have Not Logged In</h1>
    
    <a href="{{ route('login-tracking.index') }}" class="mb-4 inline-block text-blue-500 underline">← Back to Dashboard</a>

    <!-- Flash messages -->
    @if (session('success'))
        <div class="bg-green-200 text-green-800 p-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @elseif (session('error'))
        <div class="bg-red-200 text-red-800 p-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    <!-- Filters -->
    <form method="GET" class="flex flex-wrap items-end gap-4 mb-6 bg-white p-4 rounded shadow">

        <!-- Predefined Filter -->
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
            <input type="date" name="start_date" value="{{ request('start_date') }}" class="border p-2 rounded">
        </div>

        <!-- End Date -->
        <div>
            <label for="end_date" class="block font-medium">End</label>
            <input type="date" name="end_date" value="{{ request('end_date') }}" class="border p-2 rounded">
        </div>

        <!-- System -->
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

        <!-- Buttons -->
        <div class="flex items-center gap-2 mt-2 sm:mt-0">
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Filter</button>
            <a href="{{ route('login-tracking.non-logged-in') }}" class="text-blue-500 underline">Reset</a>
        </div>

    </form>

    <!-- Count Summary -->
    <p class="text-gray-600 mb-3">
        Showing {{ $nonLoggedInUsers->count() }} of {{ $nonLoggedInUsers->total() }} users
    </p>

    <!-- User Table -->
    <div class="overflow-x-auto">
        <table class="w-full bg-white shadow rounded">
            <thead>
                <tr class="bg-gray-200 text-left text-sm">
                    <th class="p-3">Display Name</th>
                    <th class="p-3">Email</th>
                    <th class="p-3">Department</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($nonLoggedInUsers as $user)
                    <tr class="hover:bg-gray-50 border-t text-sm">
                        <td class="p-3">{{ $user->displayName ?? $user->name ?? 'N/A' }}</td>
                        <td class="p-3">{{ $user->mail ?? 'N/A' }}</td>
                        <td class="p-3">{{ $user->department ?? 'N/A' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="p-4 text-center text-gray-500">
                            No users found for this period.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $nonLoggedInUsers->appends(request()->except('page'))->links() }}
    </div>

</div>
</body>
</html>
