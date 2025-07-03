<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Tracking</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4">User Login Tracking</h1>

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
            <a href="{{ route('login-tracking.non-logged-in') }}" class="ml-4 text-blue-500 underline">View Non-Logged-In Users</a>
        </form>

        <!-- User Addition Form -->
        <form method="POST" action="{{ route('login-tracking.store') }}" class="mb-4 flex flex-wrap gap-2">
            @csrf
            <input type="text" name="userPrincipalName" placeholder="User Principal Name" class="border p-2 rounded" required>
            <input type="text" name="displayName" placeholder="Display Name" class="border p-2 rounded" required>
            <input type="text" name="surname" placeholder="Surname" class="border p-2 rounded" required>
            <input type="email" name="mail" placeholder="Email" class="border p-2 rounded" required>
            <input type="text" name="givenName" placeholder="Given Name" class="border p-2 rounded" required>
            <button type="submit" class="bg-blue-500 text-white p-2 rounded">Add User</button>
        </form>

        <!-- Login Data Table -->
        <table class="w-full bg-white shadow rounded">
            <thead>
                <tr class="bg-gray-200">
                    <th class="p-2 text-left">User</th>
                    <th class="p-2 text-left">Login Count</th>
                    <th class="p-2 text-left">Last Login</th>
                    <th class="p-2 text-left">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($loginData as $data)
                    <tr class="border-t">
                        <td class="p-2">{{ $data['user']->displayName ?? $data['user']->name ?? $data['user']->mail }}</td>
                        <td class="p-2">{{ $data['login_count'] }}</td>
                        <td class="p-2">
                            {{ optional($data['sign_ins']->first())->date_utc ? \Carbon\Carbon::parse($data['sign_ins']->first()->date_utc)->toDayDateTimeString() : 'No logins' }}
                        </td>
                        <td class="p-2">
                            <form method="POST" action="{{ route('login-tracking.destroy', $data['user']->id) }}" onsubmit="return confirm('Are you sure you want to delete this user?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="bg-red-500 text-white p-1 rounded hover:bg-red-600">Remove</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="p-4 text-center text-gray-500">No users found for this time range.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</body>
</html>
