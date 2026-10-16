<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

// Log in, register and log out.
class AuthController
{
    // GET /login – show the login form
    public function showLogin()
    {
        return view('login');
    }

    // POST /login – check the email and password
    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Auth::attempt finds the user with this email and checks the password against the hashed one
        if (Auth::attempt($data)) {
            $request->session()->regenerate();   // new session id after login (protects against session hijacking)

            return redirect()->route('dashboard');
        }

        // Wrong email or password → back to the form with an error message
        return back()->withErrors(['email' => 'Wrong email or password.'])->withInput();
    }

    // GET /register – show the register form
    public function showRegister()
    {
        return view('register');
    }

    // POST /register – create a new user and log them in
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|max:100',
            'email' => 'required|email|max:100|unique:users',   // unique:users = nobody has this email yet
            'password' => 'required|min:8|confirmed',            // confirmed = same as "password_confirmation"
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),   // save the password hashed, never as plain text
        ]);

        Auth::login($user);

        return redirect()->route('dashboard');
    }

    // POST /logout
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();       // forget the old session
        $request->session()->regenerateToken();  // new CSRF token

        return redirect()->route('login');
    }
}
