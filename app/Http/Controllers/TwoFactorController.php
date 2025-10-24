<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PragmaRX\Google2FAQRCode\Google2FA as Google2FAQRCode;

class TwoFactorController extends Controller
{
    public function show()
    {
        $user = Auth::user();

        if (!$user->google2fa_secret) {
            $google2fa = app('pragmarx.google2fa');
            $user->google2fa_secret = $google2fa->generateSecretKey();
            $user->save();
        }

        $google2fa = new Google2FAQRCode();

        $qrCodeUrl = $google2fa->getQRCodeInline(
            config('app.name'),
            $user->email,
            $user->google2fa_secret
        );

        return view('2fa', ['qrCode' => $qrCodeUrl]);
    }

    public function enable(Request $request)
    {
        $request->validate([
            'otp' => 'required|digits:6',
        ]);

        $google2fa = app('pragmarx.google2fa');

        $user = Auth::user();

        $isValid = $google2fa->verifyKey($user->google2fa_secret, $request->otp);

        if ($isValid) {
            $user->google2fa_enabled = true;
            $user->save();

            return redirect('/dashboard')->with('success', 'Two-Factor Authentication enabled successfully.');
        }

        return redirect()->back()->withErrors(['otp' => 'The OTP is invalid.']);
    }

    public function verifyIndex()
    {
        return view('2fa_verify');
    }

    public function verify(Request $request)
    {
        $request->validate([
            'otp' => 'required|digits:6',
        ]);

        $google2fa = app('pragmarx.google2fa');

        $user = Auth::user();

        $isValid = $google2fa->verifyKey($user->google2fa_secret, $request->otp);

        if ($isValid) {
            session(['2fa_verified' => true]);

            return redirect()->intended('/dashboard');
        }

        return redirect()->back()->withErrors(['otp' => 'The OTP is invalid.']);
    }

}