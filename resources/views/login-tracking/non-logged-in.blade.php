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
        <h1 class="text-2xl font-bold mb-4">Users Who Have Not Logged In (Last {{ $days }} Days)</h1>
        
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

        <!-- Time Range Filter -->
        <form method="GET" class="mb-4">
            <label for="days" class="mr-2">Select Time Range:</label>
            <select name="days" id="days" class="border p-2 rounded" onchange="this.form.submit()">
                <option value="6" {{ $days == 6 ? 'selected' : '' }}>Last 6 Days</option>
                <option value="12" {{ $days == 12 ? 'selected' : '' }}>Last 12 Days</option>
                <option value="30" {{ $days == 30 ? 'selected' : '' }}>Last 30 Days</option>
            </select>
        </form>

        <!-- Non-Logged-In Users Table -->
        <table class="w-full bg-white shadow rounded">
            <thead>
                <tr class="bg-gray-200">
                    <th class="p-2 text-left">Display Name</th>
                    <th class="p-2 text-left">Email</th>
                    <th class="p-2 text-left">Department</th>
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
                            No users found who haven't logged in during this period.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</body>
</html>
