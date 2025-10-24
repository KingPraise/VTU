@extends('layouts.user_type.auth')

@section('content')
    <div class="container">
        <h2>Wallet Top-Up</h2>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
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


        <form action="{{ route('wallet.processTopUp') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="amount">Amount:</label>
                <input type="number" name="amount" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary">Top Up Wallet</button>
        </form>
    </div>
@endsection
