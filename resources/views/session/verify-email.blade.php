@extends('layouts.user_type.guest')

@section('content')
    <section class="min-vh-100 mb-8">
        <div class="page-header align-items-start min-vh-50 pt-5 pb-11 mx-3 border-radius-lg"
            style="background-image: url('../assets/img/curved-images/curved14.jpg');">
            <span class="mask bg-gradient-dark opacity-6"></span>
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-5 text-center mx-auto">
                        <h1 class="text-white mb-2 mt-5">Email Verification Required!</h1>
                        <p class="text-lead text-white">We have sent you an email with a verification link. Please check your
                            inbox and click the link to verify your email address. If you didn't receive the email, we will
                            gladly send you another.</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="container">
            <div class="row mt-lg-n10 mt-md-n11 mt-n10">
                <div class="col-xl-4 col-lg-5 col-md-7 mx-auto">
                    <div class="card z-index-0">
                        <div class="card-body text-center">
                            @if (session('success'))
                                <div class="alert alert-success" role="alert">
                                    {{ session('success') }}
                                </div>
                            @endif
                            <form method="POST" action="{{ route('verification.resend') }}">
                                @csrf
                                <button type="submit" class="btn bg-gradient-dark w-100 my-4 mb-2">Resend Verification
                                    Email</button>
                            </form>
                            <p class="text-sm mt-3 mb-0">Already verified your email? <a href="{{ route('login') }}"
                                    class="text-dark font-weight-bolder">Login here</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
