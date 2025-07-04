<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function show($id)
    {
        $user = User::with('signIns')->findOrFail($id);
        return view('users.show', compact('user'));
    }
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
    
        return redirect('/login-tracking')->with('success', 'User removed.');
    }
    
}
