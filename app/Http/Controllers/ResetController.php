<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\View;

class ResetController extends Controller
{
    public function create()
    {
        return view('session/reset-password/sendEmail');
        
    }


    // Show the reset password form directly after email input
    public function showResetForm(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $email = $request->input('email');
        return view('session/reset-password/resetPassword', ['email' => $email]);
    }

    public function resetPass($token)
    {
        return view('session/reset-password/resetPassword', ['token' => $token]);
    }
}
