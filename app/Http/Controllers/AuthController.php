<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'login' => ['required','string'],
            'password' => ['required','string'],
        ]);

        $login = $data['login'];
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        if (Auth::attempt([$field => $login, 'password' => $data['password']], true)) {
            $request->session()->regenerate();
            $role = Auth::user()->role ?? 'developer';

            if ($role === 'admin') {
                return redirect()->intended('/admin');
            } elseif ($role === 'team_lead') {
                return redirect()->intended('/lead');
            }
            return redirect()->intended('/member');
        }

        return back()->withErrors(['login' => 'Kredensial tidak valid.'])->onlyInput('login');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }

    public function showRegisterForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'fullname' => ['required','string','max:100'],
            'username' => ['required','alpha_dash','min:3','max:30','unique:users,username'],
            'email' => ['required','email','max:190','unique:users,email'],
            'password' => ['required','string','min:6','confirmed'],
            'role' => ['required','in:designer,developer'],
        ]);

        $user = \App\Models\User::create([
            'fullname' => $data['fullname'],
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],     // batasi hanya designer/developer
            'status' => 'idle',
        ]);

        Auth::login($user);
        return redirect()->intended('/member');
    }
}
