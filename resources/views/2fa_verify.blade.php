@extends('layouts.user_type.auth')

@section('content')
    <div class="container">
        <h2>Verify OTP</h2>
        <p>Please enter the OTP from your Google Authenticator app.</p>

        <form method="POST" action="{{ route('2fa.verify.post') }}">
            @csrf
            <div class="mb-3">
                <label for="otp" class="form-label">OTP</label>
                <input type="text" name="otp" class="form-control" required>
                @error('otp')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
            <button type="submit" class="btn btn-primary">Verify</button>
        </form>
    </div>
@endsection
