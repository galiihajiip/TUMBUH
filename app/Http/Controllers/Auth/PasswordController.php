<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules\Password as PasswordRule;

/**
 * Forgot password. In the demo environment no email is sent: the reset
 * link is shown on screen and labelled Demo Mode.
 */
class PasswordController extends Controller
{
    public function request()
    {
        return view('auth.forgot-password');
    }

    public function email(Request $request)
    {
        $request->validate(['email' => ['required', 'email']]);
        $user = User::where('email', strtolower($request->input('email')))->first();

        // Same response whether or not the account exists (no user enumeration).
        $link = $user ? route('password.reset', ['token' => Password::createToken($user), 'email' => $user->email]) : null;

        return back()->with('status', 'Jika email terdaftar, tautan reset telah dibuat.')->with('demo_link', $link);
    }

    public function reset(Request $request, string $token)
    {
        return view('auth.reset-password', ['token' => $token, 'email' => $request->query('email')]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)->letters()->numbers()],
        ]);

        $status = Password::reset($request->only('email', 'password', 'password_confirmation', 'token'), function (User $user, string $password) {
            $user->forceFill(['password' => $password])->save();
        });

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('success', 'Password berhasil diubah. Silakan masuk.')
            : back()->withErrors(['email' => __($status)]);
    }
}
