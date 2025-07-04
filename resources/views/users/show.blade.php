@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto mt-10 bg-white p-6 rounded shadow">
    <h2 class="text-2xl font-bold mb-4">Employee Details</h2>

    <div class="grid grid-cols-2 gap-4 mb-6">
        <div><strong>User Principal Name:</strong> {{ $user->userPrincipalName }}</div>
        <div><strong>Display Name:</strong> {{ $user->displayName }}</div>
        <div><strong>Email:</strong> {{ $user->mail ?? 'N/A' }}</div>
        <div><strong>Job Title:</strong> {{ $user->jobTitle ?? 'N/A' }}</div>
        <div><strong>Department:</strong> {{ $user->department ?? 'N/A' }}</div>
        <div><strong>Office:</strong> {{ $user->officeLocation ?? 'N/A' }}</div>
    </div>

    <h3 class="text-xl font-semibold mb-2">Sign In History</h3>
    <table class="min-w-full bg-gray-100 rounded">
        <thead>
            <tr>
                <th class="py-2 px-4">Date</th>
                <th class="py-2 px-4">IP Address</th>
                <th class="py-2 px-4">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($user->signIns as $signIn)
                <tr class="border-t">
                    <td class="py-2 px-4">{{ \Carbon\Carbon::parse($signIn['date_utc'])->format('D, M j, Y g:i A') }}</td>
                    <td class="py-2 px-4">{{ $signIn['ip_address'] }}</td>
                    <td class="py-2 px-4">{{ $signIn['status'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="text-center py-4 text-gray-500">No login history available.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <form method="POST" action="{{ route('users.destroy', $user->id) }}" class="mt-6"
          onsubmit="return confirm('Are you sure you want to remove this user?');">
        @csrf
        @method('DELETE')
        <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
            Remove User
        </button>
    </form>

    <div class="mt-4">
        <a href="{{ route('login-tracking.index') }}" class="text-blue-500 hover:underline">← Back to Dashboard</a>
    </div>
</div>
@endsection
