<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Show detailed view of a single user and their login activity.
     */
    public function show(Request $request, $id)
    {
        $system = $request->input('system');

        $user = User::findOrFail($id);
        $signInsQuery = $user->signIns()->orderByDesc('date_utc');

        if ($system) {
            $signInsQuery->where('system', $system);
        }

        $signIns = $signInsQuery->get();

        return view('users.show', compact('user', 'signIns', 'system'));
    }

    /**
     * Delete a user only if they have no login history.
     */
    public function destroy($id)
    {
        $user = User::withCount('signIns')->findOrFail($id);

        if ($user->sign_ins_count > 0) {
            return redirect()->back()->with('error', 'Cannot delete user with login history.');
        }

        $user->delete();

        return redirect('/login-tracking')->with('success', 'User removed.');
    }
}
