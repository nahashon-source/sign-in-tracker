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

    <!-- Time Range + Date Range Filter -->
    <form method="GET" class="mb-6 flex flex-wrap gap-3 items-end bg-white p-4 rounded shadow">
        <div>
            <label for="days" class="block font-medium">Quick Range</label>
            <select name="days" id="days" class="border p-2 rounded" onchange="this.form.submit()">
                <option value="6" {{ request('days') == 6 ? 'selected' : '' }}>Last 6 Days</option>
                <option value="12" {{ request('days') == 12 ? 'selected' : '' }}>Last 12 Days</option>
                <option value="30" {{ request('days') == 30 ? 'selected' : '' }}>Last 30 Days</option>
                <option value="" {{ !request('days') ? 'selected' : '' }}>Custom</option>
            </select>
        </div>

        <div>
            <label for="start_date" class="block font-medium">Start</label>
            <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}" class="border p-2 rounded">
        </div>

        <div>
            <label for="end_date" class="block font-medium">End</label>
            <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}" class="border p-2 rounded">
        </div>

        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Filter</button>

        <a href="{{ route('login-tracking.index') }}" class="text-blue-500 underline ml-2">Reset</a>
        <a href="{{ route('login-tracking.non-logged-in') }}" class="ml-auto text-blue-600 font-medium underline">View Non-Logged-In Users</a>
    </form>

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
