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

    <h1 class="text-2xl font-bold mb-4">Users Who Have Not Logged In</h1>
    
    <a href="{{ route('login-tracking.index') }}" class="mb-4 inline-block text-blue-500 underline">← Back to Dashboard</a>

    <!-- Flash messages -->
    @if (session('success'))
        <div class="bg-green-200 text-green-800 p-2 rounded mb-4">
            {{ session('success') }}
        </div>
    @elseif (session('error'))
        <div class="bg-red-200 text-red-800 p-2 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    <!-- Filters -->
    <form method="GET" class="flex flex-wrap items-center gap-2 mb-4">
        <label for="days" class="font-medium">Quick Range:</label>
        <select name="days" id="days" class="border p-2 rounded">
            <option value="6" {{ request('days') == 6 ? 'selected' : '' }}>Last 6 Days</option>
            <option value="12" {{ request('days') == 12 ? 'selected' : '' }}>Last 12 Days</option>
            <option value="30" {{ request('days') == 30 ? 'selected' : '' }}>Last 30 Days</option>
        </select>

        <label for="start_date" class="ml-2 font-medium">Start:</label>
        <input type="date" name="start_date" value="{{ request('start_date') }}" class="border p-2 rounded">

        <label for="end_date" class="ml-2 font-medium">End:</label>
        <input type="date" name="end_date" value="{{ request('end_date') }}" class="border p-2 rounded">

        <button type="submit" class="bg-blue-500 text-white px-3 py-2 rounded">Filter</button>
        <a href="{{ route('login-tracking.non-logged-in') }}" class="text-blue-500 underline">Reset</a>
    </form>

    <!-- Count Summary -->
    <p class="text-gray-600 mb-3">
        Showing {{ $nonLoggedInUsers->count() }} of {{ $nonLoggedInUsers->total() }} users
    </p>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="w-full bg-white shadow rounded">
            <thead>
                <tr class="bg-gray-200 text-left">
                    <th class="p-2">Display Name</th>
                    <th class="p-2">Email</th>
                    <th class="p-2">Department</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($nonLoggedInUsers as $user)
                    <tr class="hover:bg-gray-50 border-t">
                        <td class="p-2">{{ $user->displayName ?? $user->name ?? 'N/A' }}</td>
                        <td class="p-2">{{ $user->mail ?? 'N/A' }}</td>
                        <td class="p-2">{{ $user->department ?? 'N/A' }}</td>
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

    <!-- Pagination Links -->
    <div class="mt-4">
        {{ $nonLoggedInUsers->appends(request()->except('page'))->links() }}
    </div>

</div>
</body>
</html>
