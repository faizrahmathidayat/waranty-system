<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class LoginController extends Controller
{
    public function index()
    {
        return view('login/index');
    }

    public function auth(Request $request)
    {
        $infologin = [
            'username' => $request->username,
            'password' => $request->password
        ];

        if (Auth::attempt($infologin)) {

            // Cek status user
            if (Auth::user()->status == 'disabled') {

                Auth::logout();

                return back()->with([
                    'warning' => 'Akun Anda tidak aktif. Silakan hubungi Administrator.',
                ])->withInput();
            }

            $request->session()->regenerate();

            return redirect()->intended('/');
        } else {

            return back()->with([
                'warning' => 'Wrong username or password',
            ])->withInput();
        }
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
