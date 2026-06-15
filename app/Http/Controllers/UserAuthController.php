<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserAuthController extends Controller
{
    /**
     * Tampilkan form login
     */
    public function showLogin()
    {
        return view('auth.login');
    }

    /**
     * Proses login
     */
    public function login(Request $request)
{
    $credentials = $request->validate([
        'email'    => 'required|email',
        'password' => 'required'
    ]);

    if (Auth::attempt($credentials)) {
    $request->session()->regenerate();
    return redirect()->intended(route('home'))->with('success', 'Berhasil login!');
}

    return back()->with('error', 'Email atau password salah.');
}

    /**
     * Tampilkan form register
     */
    public function showRegister()
    {
        return view('auth.register');
    }

    /**
     * Proses register
     */
    public function register(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:6|confirmed',
    ]);

    // simpan user baru
    $user = \App\Models\User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => bcrypt($request->password),
    ]);

    // setelah berhasil register arahkan ke login
    return redirect()->route('login')->with('success', 'Registrasi berhasil! Silakan login.');
}


    /**
     * Dashboard
     */
    public function dashboard()
    {
        return view('dashboard');
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda sudah logout.');
    }

    public function showResetForm()
{
    return view('auth.reset-password');
}

public function resetPassword(Request $request)
{
    $request->validate([
        'email' => 'required|email|exists:users,email',
        'password' => 'required|string|min:6|confirmed',
    ]);

    $user = \App\Models\User::where('email', $request->email)->first();
    $user->password = bcrypt($request->password);
    $user->save();

    return redirect()->route('login')->with('success', 'Password berhasil direset. Silakan login dengan password baru.');
}

}
