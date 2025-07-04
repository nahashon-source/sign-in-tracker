<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Services\LoginFilterService;

class LoginTrackingController extends Controller
{
    protected $filterService;

    public function __construct(LoginFilterService $filterService)
    {
        $this->filterService = $filterService;
    }

    /**
     * Display users with login activity based on filters.
     */
    public function index(Request $request)
    {
        // ✅ Validate filter inputs
        $validated = $request->validate([
            'filter' => 'nullable|in:this_month,previous_month,last_3_months',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'system' => 'nullable|string',
        ]);

        // 🔄 Extract standardized filters from request
        extract($this->filterService->extract($request));

        // 🧠 Build cache key (include page for paginated data)
        $page = $request->input('page', 1);
        $cacheKey = "logins_{$system}_{$filter}_{$startDate}_{$endDate}_page_{$page}";

        // 🚀 Use cache to avoid duplicate queries
        $users = Cache::remember($cacheKey, now()->addMinutes(15), function () use ($startDate, $endDate, $system) {
            return User::with(['signIns' => function ($query) use ($startDate, $endDate, $system) {
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
        });

        // 📦 Return filtered result to view
        return view('login-tracking.index', compact('users', 'startDate', 'endDate', 'system', 'filter'));
    }

    /**
     * Display users with no login activity in given period/system.
     */
    public function nonLoggedInUsers(Request $request)
    {
        // ✅ Validate filter inputs
        $validated = $request->validate([
            'filter' => 'nullable|in:this_month,previous_month,last_3_months',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'system' => 'nullable|string',
        ]);

        // 🔄 Extract standardized filters
        extract($this->filterService->extract($request));

        // 🧠 Build cache key
        $page = $request->input('page', 1);
        $cacheKey = "non_logins_{$system}_{$filter}_{$startDate}_{$endDate}_page_{$page}";

        // 🚀 Cache result
        $nonLoggedInUsers = Cache::remember($cacheKey, now()->addMinutes(15), function () use ($startDate, $endDate, $system) {
            return User::leftJoin('interactive_sign_ins', function ($join) use ($startDate, $endDate, $system) {
                    $join->on('users.id', '=', 'interactive_sign_ins.user_id')
                         ->whereBetween('interactive_sign_ins.date_utc', [$startDate, $endDate])
                         ->where('interactive_sign_ins.system', $system);
                })
                ->whereNull('interactive_sign_ins.user_id')
                ->select('users.*')
                ->orderBy('displayName')
                ->paginate(20);
        });

        return view('login-tracking.non-logged-in', compact('nonLoggedInUsers', 'startDate', 'endDate', 'system', 'filter'));
    }

    /**
     * Add a new user manually.
     */
    public function storeUser(Request $request)
    {
        // ✅ Validate required fields
        $validated = $request->validate([
            'userPrincipalName' => 'required|unique:users',
            'displayName' => 'required',
            'surname' => 'required',
            'mail' => 'required|email|unique:users',
            'givenName' => 'required',
        ]);

        // 🧾 Create user with defaults
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
     * Remove a user (only if no login history exists).
     */
    public function destroyUser($id)
    {
        $user = User::findOrFail($id);

        // 🔐 Prevent deletion if login history exists
        if ($user->signIns()->exists()) {
            return redirect()->back()->with('error', 'Cannot delete user with login history.');
        }

        $user->delete();

        return redirect()->route('login-tracking.index')->with('success', 'User removed successfully.');
    }
}
