<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\InteractiveSignIn;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;

class LoginTrackingController extends Controller
{
    /**
     * Show login activity of all users within the given timeframe.
     * Default: 30 days. Allows filtering by 6, 12, or 30 days.
     */
    public function index(Request $request)
    {
        $days = in_array($request->input('days'), [6, 12, 30]) ? (int) $request->input('days') : 30;

        $startDate = Carbon::now()->subDays($days)->startOfDay();
        $endDate = Carbon::now()->endOfDay();

        // Eager-load sign-in records only within the date range
        $users = User::with(['signIns' => function ($query) use ($startDate, $endDate) {
            $query->whereBetween('date_utc', [$startDate, $endDate])
                  ->orderByDesc('date_utc');
        }])->get();

        $loginData = $users->map(function ($user) {
            return [
                'user' => $user,
                'login_count' => $user->signIns->count(), // No extra query here
                'sign_ins' => $user->signIns,
            ];
        });

        return view('login-tracking.index', compact('loginData', 'days'));
    }

    /**
     * Show users who have NOT logged in at all during the specified period.
     */
    public function nonLoggedInUsers(Request $request)
    {
        $days = in_array($request->input('days'), [6, 12, 30]) ? (int) $request->input('days') : 30;

        $startDate = Carbon::now()->subDays($days)->startOfDay();
        $endDate = Carbon::now()->endOfDay();

        // Left join to find users with no activity in the range
        $nonLoggedInUsers = User::leftJoin('interactive_sign_ins', function ($join) use ($startDate, $endDate) {
                $join->on('users.id', '=', 'interactive_sign_ins.user_id')
                     ->whereBetween('interactive_sign_ins.date_utc', [$startDate, $endDate]);
            })
            ->whereNull('interactive_sign_ins.user_id')
            ->select('users.*')
            ->paginate(20);

        return view('login-tracking.non-logged-in', compact('nonLoggedInUsers', 'days'));
    }

    /**
     * Add a new user to the system. Used by admin.
     */
    public function storeUser(Request $request)
    {
        $validated = $request->validate([
            'userPrincipalName' => 'required|unique:users',
            'displayName' => 'required',
            'surname' => 'required',
            'mail' => 'required|email|unique:users',
            'givenName' => 'required',
        ]);

        User::create([
            'id' => Str::uuid()->toString(),
            'userPrincipalName' => $validated['userPrincipalName'],
            'displayName' => $validated['displayName'],
            'surname' => $validated['surname'],
            'mail' => $validated['mail'],
            'givenName' => $validated['givenName'],
            'userType' => 'Member',
            'jobTitle' => 'Unknown',
            'department' => 'Unknown',
            'accountEnabled' => true,
            'createdDateTime' => Carbon::now(),
        ]);

        return redirect()->route('login-tracking.index')->with('success', 'User added successfully.');
    }

    /**
     * Delete a user from the system (soft or hard depending on config).
     */
    public function destroyUser($id)
    {
        $user = User::findOrFail($id);

        // Optional: Check if user has any login records
        if ($user->signIns()->exists()) {
            return redirect()->back()->with('error', 'Cannot delete user with login history.');
        }

        $user->delete();

        return redirect()->route('login-tracking.index')->with('success', 'User removed successfully.');
    }
}
