<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    public function airtimeTopUpForm()
    {
        $user = Auth::user();

        $balance = $user->wallet_balance;
        // Show the Airtime Top-Up form
        return view('transactions\airtime-topup', [
            'balance' => $balance,
        ]);
    }

    public function processAirtimeTopUp(Request $request)
    {
        // Validate the request
        $request->validate([
            'network' => 'required|string',
            'phone_number' => 'required|string|max:15',
            'amount' => 'required|numeric|min:1',
        ]);

        // Create a new transaction
        $transaction = Transaction::create([
            'user_id' => Auth::id(),
            'type' => 'airtime-topup',
            'amount' => $request->input('amount'),
            'status' => 'pending',
            'service' => 'Airtime Top-Up',
        ]);

        // Here you can integrate with an airtime API to process the transaction

        // Update transaction status based on the API response (e.g., 'success', 'failed')
        $transaction->update(['status' => 'success']); // Assuming the top-up was successful

        return redirect()->route('transaction.history')->with('success', 'Airtime Top-Up successful!');
    }

    public function dataBundleForm()
    {
        $user = Auth::user();

        $balance = $user->wallet_balance;
        // Show the Data Bundle Purchase form
        return view('transactions\data-bundle', [
            'balance' => $balance,
        ]);
    }

    public function processDataBundlePurchase(Request $request)
    {
        // Validate the request
        $request->validate([
            'network' => 'required|string',
            'phone_number' => 'required|string|max:15',
            'amount' => 'required|numeric|min:1',
            'data_bundle' => 'required|string',
        ]);

        $user = Auth::user();
        $amount = $request->input('amount');

        // Check if user has enough balance in the wallet
        if ($user->wallet_balance < $amount) {
            return redirect()->back()->withErrors(['error' => 'Insufficient balance. Please top up your wallet.']);
        }

        // Deduct the amount from user's wallet
        $user->wallet_balance -= $amount;
        $user->save();

        // Create a new transaction
        $transaction = Transaction::create([
            'user_id' => Auth::id(),
            'type' => 'data-bundle-purchase',
            'amount' => $amount,
            'status' => 'pending',
            'service' => 'Data Bundle',
        ]);

        // Here you can integrate with a data bundle API to process the transaction

        // Update transaction status based on the API response (e.g., 'success', 'failed')
        $transaction->update(['status' => 'success']); // Assuming the purchase was successful

        return redirect()->route('transaction.history')->with('success', 'Data Bundle Purchase successful!');
    }

    public function transactionHistory()
    {
        // Fetch user's transactions
        $transactions = Auth::user()->transactions()->latest()->get();

        return view('transactions\history', ['transactions' => $transactions]);
    }
}