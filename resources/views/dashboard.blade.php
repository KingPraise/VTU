@extends('layouts.user_type.auth')

@section('content')
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

    <!-- Available Services Section -->
    <div class="row mt-4">
        <div class="col-lg-6 mb-lg-0 mb-4">
            <div class="card">
                <div class="card-header pb-0">
                    <h6>Available Services</h6>
                </div>
                <div class="card-body p-3">
                    <ul>
                        <li><a href="{{ route('service.transaction', ['service' => 'airtime']) }}">Buy Airtime</a></li>
                        <li><a href="{{ route('service.transaction', ['service' => 'data']) }}">Buy Data</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Transactions Section -->
    <div class="row mt-4">

        <div class="col-lg-10 mb-lg-0 mb-4">
            <div class="card">
                <div class="card-header pb-0">
                    <h6>Recent Transactions</h6>
                </div>
                <div class="card-body p-3">
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Date
                                    </th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                        Type</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                        Amount</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                        Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($transactions as $transaction)
                                    <tr>
                                        <td>
                                            <p class="text-xs font-weight-bold mb-0">
                                                {{ $transaction->created_at->format('d/m/Y H:i') }}</p>
                                        </td>
                                        <td>
                                            <p class="text-xs font-weight-bold mb-0">
                                                {{ ucfirst($transaction->type) }}</p>
                                        </td>
                                        <td>
                                            <p class="text-xs font-weight-bold mb-0">
                                                #{{ number_format($transaction->amount, 2) }}</p>
                                        </td>
                                        <td>
                                            <span
                                                class="badge badge-sm bg-gradient-{{ $transaction->status === 'success' ? 'success' : ($transaction->status === 'pending' ? 'secondary' : 'danger') }}">
                                                {{ ucfirst($transaction->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
