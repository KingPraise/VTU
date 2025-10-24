@extends('layouts.user_type.auth')

@section('content')
    <div class="container">
        <h2>Buy Airtime</h2>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <!-- Account Balance Section -->
        <div class="row">
            <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                <div class="card">
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-8">
                                <div class="numbers">
                                    <p class="text-sm mb-0 text-capitalize font-weight-bold">Account Balance</p>
                                    <h5 class="font-weight-bolder mb-0">
                                        #{{ number_format($balance, 2) }}
                                    </h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <form action="{{ route('service.transaction') }}" method="POST">
            @csrf
            <input type="hidden" name="service" value="airtime">

            <div class="form-group">
                <label for="network">Select Network:</label>
                <select name="network" id="network" class="form-control">
                    <option value="MTN">MTN</option>
                    <option value="Airtel">Airtel</option>
                    <option value="Glo">Glo</option>
                    <option value="9mobile">9mobile</option>
                </select>
            </div>

            <div class="form-group">
                <label for="phone_number">Phone Number:</label>
                <input type="text" name="phone_number" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="amount">Amount:</label>
                <input type="number" name="amount" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="payment_method">Select Payment Method:</label><br>
                <label>
                    <input type="radio" name="payment_method" value="wallet" checked>
                    Wallet
                </label>
                <label>
                    <input type="radio" name="payment_method" value="paystack">
                    Paystack
                </label>
            </div>

            <button type="submit" class="btn btn-primary">Buy Airtime</button>
        </form>
    </div>
@endsection
