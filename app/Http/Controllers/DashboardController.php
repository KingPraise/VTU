<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {

        $user = Auth::user();

        // Fetch wallet balance
        $balance = $user->balance;

        $transactions = $user->transactions()->latest()->limit(5)->get(); // Fetch recent transactions
        $balance = $user->transactions()->where('status', 'success')->sum('amount'); // Calculate balance

        return view('dashboard', [
            'balance' => $balance,
            'transactions' => $transactions,
            'available_services' => ['Airtime Top-Up', 'Data Bundle Purchase'], // Define available services
        ]);
    }
}