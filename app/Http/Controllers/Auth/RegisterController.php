<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    public function create()
    {
        return view('auth.register');
    }

    public function store(Request $request, OtpController $otp)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:160', 'unique:users,email'],
            'phone' => ['required', 'regex:/^(\+62|62|0)8[0-9]{7,12}$/'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
            'role' => ['required', Rule::in(array_keys(config('tumbuh.roles')))],
            'referral_code' => ['nullable', 'string', 'max:40'],
            'terms' => ['accepted'],
        ], [
            'phone.regex' => 'Gunakan format nomor Indonesia, mis. 0812xxxxxxx.',
            'terms.accepted' => 'Anda perlu menyetujui Syarat & Kebijakan Privasi.',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => strtolower($data['email']),
            'phone' => $data['phone'],
            'role' => $data['role'],
            'referral_code' => $data['referral_code'] ?? null,
            'password' => $data['password'],
            'terms_accepted_at' => now(),
        ]);

        foreach (['marketing' => false, 'analytics' => true, 'partner' => false] as $type => $granted) {
            $user->consents()->create(['consent_type' => $type, 'granted' => $granted, 'granted_at' => $granted ? now() : null]);
        }

        Auth::login($user);
        $request->session()->regenerate();
        $otp->issue($user);

        return redirect()->route('otp.show');
    }
}
