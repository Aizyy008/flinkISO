<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\FlinkUser;
use Illuminate\Http\Request;

/**
 * Session login for the QMS web UI, authenticated against the legacy FlinkISO users.
 */
class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = FlinkUser::where('username', $data['username'])->where('soft_delete', 0)->first();

        if (!$user || !$user->verifyPassword($data['password']) || (int) $user->status !== 1) {
            return back()->withErrors(['username' => 'Invalid credentials'])->withInput();
        }

        $request->session()->regenerate();
        $request->session()->put('flink_user', [
            'id' => $user->id,
            'username' => $user->username,
            'name' => $user->name,
        ]);

        return redirect('/documents');
    }

    public function logout(Request $request)
    {
        $request->session()->forget('flink_user');
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
