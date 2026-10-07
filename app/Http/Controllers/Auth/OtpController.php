<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * Demo Verification Mode: OTP is generated locally (fixed demo code),
 * hashed at rest, expires, and can be resent after a cooldown.
 * No SMS/email provider is contacted.
 */
class OtpController extends Controller
{
    public function issue(User $user): void
    {
        $user->forceFill([
            'otp_code' => Hash::make(config('tumbuh.demo.otp')),
            'otp_expires_at' => now()->addMinutes(config('tumbuh.demo.otp_ttl_minutes')),
            'otp_sent_at' => now(),
        ])->save();
    }

    public function show(Request $request)
    {
        $user = $request->user();
        if ($user->isVerified()) {
            return redirect()->route('onboarding.show');
        }
        if (! $user->otp_code) {
            $this->issue($user);
        }

        return view('auth.otp', [
            'user' => $user,
            'expiresAt' => $user->otp_expires_at,
            'resendIn' => max(0, config('tumbuh.demo.otp_resend_seconds') - (int) $user->otp_sent_at?->diffInSeconds(now())),
        ]);
    }

    public function verify(Request $request)
    {
        $request->validate(['code' => ['required', 'digits:6']], ['code.digits' => 'Kode OTP terdiri dari 6 angka.']);
        $user = $request->user();

        if (! $user->otp_expires_at || $user->otp_expires_at->isPast()) {
            throw ValidationException::withMessages(['code' => 'Kode OTP kedaluwarsa. Kirim ulang kode.']);
        }
        if (! Hash::check($request->input('code'), (string) $user->otp_code)) {
            throw ValidationException::withMessages(['code' => 'Kode OTP salah. (Demo: gunakan '.config('tumbuh.demo.otp').')']);
        }

        $user->forceFill(['phone_verified_at' => now(), 'email_verified_at' => now(), 'otp_code' => null, 'otp_expires_at' => null])->save();

        return redirect()->route('onboarding.show')->with('success', 'Akun berhasil dibuat ✓');
    }

    public function resend(Request $request)
    {
        $user = $request->user();
        $wait = config('tumbuh.demo.otp_resend_seconds') - (int) $user->otp_sent_at?->diffInSeconds(now());
        if ($user->otp_sent_at && $wait > 0) {
            return back()->withErrors(['code' => "Tunggu {$wait} detik sebelum mengirim ulang."]);
        }
        $this->issue($user);

        return back()->with('success', 'Kode OTP baru telah dikirim (Demo Verification Mode).');
    }
}
