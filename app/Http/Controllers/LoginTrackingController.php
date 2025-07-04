<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Services\LoginFilterService;
use Illuminate\Pagination\LengthAwarePaginator;

class LoginTrackingController extends Controller
{
    protected $filterService;
    protected $systems;

    public function __construct(LoginFilterService $filterService)
    {
        $this->filterService = $filterService;
        $this->systems = ['SCM', 'Odoo', 'D365 Live', 'Fit Express', 'FIT ERP', 'Fit Express UAT', 'FITerp UAT', 'OPS', 'OPS UAT'];
    }

    /**
     * Display users with login activity based on filters.
     */
    public function index(Request $request)
    {
        // ✅ Validate filters
        $validated = $request->validate([
            'filter' => 'nullable|in:this_month,previous_month,last_3_months',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'system' => 'nullable|string|in:' . implode(',', $this->systems),
            'only_logged_in' => 'nullable|boolean',
        ]);

        // 🔄 Extract standardized filters
        extract($this->filterService->extract($request));

        // 🧠 Cache Key with pagination
        $page = $request->input('page', 1);
        $cacheKey = "logins_{$system}_{$filter}_{$startDate}_{$endDate}_page_{$page}";

        // 🚀 Get paginated user login data
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

        // ✅ Filter out users with no logins if requested
        if ($request->boolean('only_logged_in')) {
            $filtered = $users->getCollection()->filter(fn($user) => $user->login_count > 0)->values();

            $users = new LengthAwarePaginator(
                $filtered->forPage($page, 20),
                $filtered->count(),
                20,
                $page,
                ['path' => $request->url(), 'query' => $request->query()]
            );
        }

        // 🧮 Count total logged-in users
        $loggedInCountKey = "logins_count_{$system}_{$filter}_{$startDate}_{$endDate}";
        $loggedInCount = Cache::remember($loggedInCountKey, now()->addMinutes(15), function () use ($startDate, $endDate, $system) {
            return User::withCount(['signIns as login_count' => function ($query) use ($startDate, $endDate, $system) {
                $query->whereBetween('date_utc', [$startDate, $endDate])
                      ->where('system', $system);
            }])->having('login_count', '>', 0)->count();
        });

        // 🧮 Total system users
        $totalUsersCount = Cache::remember('total_users_count', now()->addMinutes(60), fn () => User::count());
        $nonLoggedInCount = $totalUsersCount - $loggedInCount;

        // 📌 Filter label (for UI summary)
        $filterLabel = match($filter) {
            'this_month' => 'This Month',
            'previous_month' => 'Previous Month',
            'last_3_months' => 'Last 3 Months',
            default => 'Custom Range'
        };

        return view('login-tracking.index', [
            'users' => $users,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'system' => $system,
            'filter' => $filter,
            'filterLabel' => $filterLabel,
            'systems' => $this->systems,
            'summaryCounts' => [
                'loggedIn' => $loggedInCount,
                'nonLoggedIn' => $nonLoggedInCount,
                'total' => $totalUsersCount,
            ],
        ]);
    }

    /**
     * Display users with no login activity in given period/system.
     */
    public function nonLoggedInUsers(Request $request)
    {
        $validated = $request->validate([
            'filter' => 'nullable|in:this_month,previous_month,last_3_months',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'system' => 'nullable|string',
        ]);

        extract($this->filterService->extract($request));
        $page = $request->input('page', 1);
        $cacheKey = "non_logins_{$system}_{$filter}_{$startDate}_{$endDate}_page_{$page}";

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

        // 🧮 Total users
        $totalUsersCount = Cache::remember('total_users_count', now()->addMinutes(60), fn() => User::count());

        // 🧮 Logged-in users
        $loggedInCountKey = "logins_count_{$system}_{$filter}_{$startDate}_{$endDate}";
        $loggedInCount = Cache::remember($loggedInCountKey, now()->addMinutes(15), function () use ($startDate, $endDate, $system) {
            return User::withCount(['signIns as login_count' => function ($query) use ($startDate, $endDate, $system) {
                $query->whereBetween('date_utc', [$startDate, $endDate])
                      ->where('system', $system);
            }])->having('login_count', '>', 0)->count();
        });

        $nonLoggedInCount = $totalUsersCount - $loggedInCount;

        return view('login-tracking.non-logged-in', compact(
            'nonLoggedInUsers',
            'startDate',
            'endDate',
            'system',
            'filter',
            'nonLoggedInCount',
            'loggedInCount',
            'totalUsersCount'
        ));
    }

    /**
     * Add a user manually.
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
     * Remove user only if no login activity exists.
     */
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
