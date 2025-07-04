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
        $validated = $request->validate([
            'filter' => 'nullable|in:this_month,previous_month,last_3_months',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'system' => 'nullable|string',
        ]);

        $system = $this->resolveSystem($request->input('system'));
        [$startDate, $endDate] = $this->parseDateRange($request);

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

        return view('login-tracking.index', compact('users', 'startDate', 'endDate', 'system'));
    }

    public function nonLoggedInUsers(Request $request)
    {
        $validated = $request->validate([
            'filter' => 'nullable|in:this_month,previous_month,last_3_months',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'system' => 'nullable|string',
        ]);

        $system = $this->resolveSystem($request->input('system'));
        [$startDate, $endDate] = $this->parseDateRange($request);

        $nonLoggedInUsers = User::leftJoin('interactive_sign_ins', function ($join) use ($startDate, $endDate, $system) {
                $join->on('users.id', '=', 'interactive_sign_ins.user_id')
                     ->whereBetween('interactive_sign_ins.date_utc', [$startDate, $endDate])
                     ->where('interactive_sign_ins.system', $system);
            })
            ->whereNull('interactive_sign_ins.user_id')
            ->select('users.*')
            ->orderBy('displayName')
            ->paginate(20);

        return view('login-tracking.non-logged-in', [
            'nonLoggedInUsers' => $nonLoggedInUsers,
            'start_date' => $startDate,
            'end_date' => $endDate,
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

    /** 
     * Normalize and validate system input.
     */
    private function resolveSystem(?string $input): string
    {
        $normalized = trim($input ?? '');
        return in_array($normalized, $this->validSystems) ? $normalized : 'SCM';
    }

    /**
     * Determine date range from filter or start/end dates
     */
    private function parseDateRange(Request $request): array
    {
        $filter = $request->input('filter');

        switch ($filter) {
            case 'this_month':
                return [Carbon::now()->startOfMonth(), Carbon::now()->endOfDay()];
            case 'previous_month':
                return [
                    Carbon::now()->subMonth()->startOfMonth(),
                    Carbon::now()->subMonth()->endOfMonth()
                ];
            case 'last_3_months':
                return [Carbon::now()->subMonths(3)->startOfMonth(), Carbon::now()->endOfDay()];
            default:
                $start = $request->input('start_date')
                    ? Carbon::parse($request->input('start_date'))->startOfDay()
                    : Carbon::now()->subDays(30)->startOfDay();

                $end = $request->input('end_date')
                    ? Carbon::parse($request->input('end_date'))->endOfDay()
                    : Carbon::now()->endOfDay();

                return [$start, $end];
        }
    }
}
