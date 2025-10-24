@extends('layouts.user_type.auth')
@section('content')
    <div class="container">
        <h2>Two-Factor Authentication (2FA)</h2>
        <p>Scan the following QR code with your Google Authenticator app. After scanning, enter the OTP below to enable 2FA.
        </p>

        <div>
            {!! $qrCode !!}
        </div>

        <form method="POST" action="{{ route('2fa.enable') }}">
            @csrf
            <div class="mb-3">
                <label for="otp" class="form-label">OTP</label>
                <input type="text" name="otp" class="form-control" required>
                @error('otp')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
            <button type="submit" class="btn btn-primary">Enable 2FA</button>
        </form>
    </div>
@endsection
