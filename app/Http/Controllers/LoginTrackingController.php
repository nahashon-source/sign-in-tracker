<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\InteractiveSignIn;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;

class LoginTrackingController extends Controller
{
    protected $validSystems = [
        'SCM', 'Odoo', 'D365 Live', 'Fit Express', 'FIT ERP',
        'Fit Express UAT', 'FITerp UAT', 'OPS', 'OPS UAT',
    ];

    public function index(Request $request)
    {
        // Handle system filtering
        $system = in_array($request->input('system'), $this->validSystems) 
                    ? $request->input('system') 
                    : 'SCM';

        // Handle date range filters
        $filter = $request->input('filter');
        switch ($filter) {
            case 'this_month':
                $startDate = Carbon::now()->startOfMonth();
                $endDate = Carbon::now()->endOfDay();
                break;
            case 'previous_month':
                $startDate = Carbon::now()->subMonth()->startOfMonth();
                $endDate = Carbon::now()->subMonth()->endOfMonth();
                break;
            case 'last_3_months':
                $startDate = Carbon::now()->subMonths(3)->startOfMonth();
                $endDate = Carbon::now()->endOfDay();
                break;
            default:
                $startDate = $request->input('start_date')
                    ? Carbon::parse($request->input('start_date'))->startOfDay()
                    : Carbon::now()->subDays(30)->startOfDay();

                $endDate = $request->input('end_date')
                    ? Carbon::parse($request->input('end_date'))->endOfDay()
                    : Carbon::now()->endOfDay();
        }

        $users = User::with(['signIns' => function ($query) use ($startDate, $endDate, $system) {
            $query->whereBetween('date_utc', [$startDate, $endDate])
                  ->where('system', $system)
                  ->orderByDesc('date_utc');
        }])
        ->withCount(['signIns as login_count' => function ($query) use ($startDate, $endDate, $system) {
            $query->whereBetween('date_utc', [$startDate, $endDate])
                  ->where('system', $system);
        }])
        ->orderBy('displayName')
        ->paginate(20);

        return view('login-tracking.index', compact('users', 'startDate', 'endDate', 'system', 'filter'));
    }

    public function nonLoggedInUsers(Request $request)
    {
        $filter = $request->input('filter');
        switch ($filter) {
            case 'this_month':
                $startDate = Carbon::now()->startOfMonth();
                $endDate = Carbon::now()->endOfDay();
                break;
            case 'previous_month':
                $startDate = Carbon::now()->subMonth()->startOfMonth();
                $endDate = Carbon::now()->subMonth()->endOfMonth();
                break;
            case 'last_3_months':
                $startDate = Carbon::now()->subMonths(3)->startOfMonth();
                $endDate = Carbon::now()->endOfDay();
                break;
            default:
                $startDate = $request->input('start_date')
                    ? Carbon::parse($request->input('start_date'))->startOfDay()
                    : Carbon::now()->subDays(30)->startOfDay();

                $endDate = $request->input('end_date')
                    ? Carbon::parse($request->input('end_date'))->endOfDay()
                    : Carbon::now()->endOfDay();
        }

        $system = in_array($request->input('system'), $this->validSystems)
                    ? $request->input('system')
                    : 'SCM';

        $nonLoggedInUsers = User::leftJoin('interactive_sign_ins', function ($join) use ($startDate, $endDate, $system) {
                $join->on('users.id', '=', 'interactive_sign_ins.user_id')
                     ->whereBetween('interactive_sign_ins.date_utc', [$startDate, $endDate])
                     ->where('interactive_sign_ins.system', $system);
            })
            ->whereNull('interactive_sign_ins.user_id')
            ->select('users.*')
            ->paginate(20);

        return view('login-tracking.non-logged-in', [
            'nonLoggedInUsers' => $nonLoggedInUsers,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'filter' => $filter,
            'system' => $system,
        ]);
    }

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

    public function destroyUser($id)
    {
        $user = User::findOrFail($id);

        if ($user->signIns()->exists()) {
            return redirect()->back()->with('error', 'Cannot delete user with login history.');
        }

        $user->delete();

        return redirect()->route('login-tracking.index')->with('success', 'User removed successfully.');
    }
}
