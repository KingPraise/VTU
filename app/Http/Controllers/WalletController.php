<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Log;

class WalletController extends Controller
{
    public function showTopUpForm()
    {
        $user = Auth::user();

        $balance = $user->wallet_balance;

        return view('wallet.wallet-topup', [
            'balance' => $balance,
        ]);

    }

    public function processTopUp(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
        ]);

        $user = Auth::user();
        $amount = $request->input('amount') * 100; // Convert amount to kobo for Paystack

        // Paystack payment data
        $data = [
            'email' => $user->email,
            'amount' => $amount,
            'callback_url' => route('wallet.callback'),
        ];

        try {
            // Initiate the payment with Paystack
            $paymentResponse = Http::withToken(env('PAYSTACK_SECRET_KEY'))
                ->timeout(60) // Increase the timeout to 60 seconds
                ->post('https://api.paystack.co/transaction/initialize', $data);

            $paymentData = $paymentResponse->json();

            if ($paymentData['status']) {
                return redirect($paymentData['data']['authorization_url']);
            } else {
                Log::error('Paystack Initialization Error', $paymentData);
                return back()->withErrors(['error' => 'Unable to initiate payment. Please try again.']);
            }
        } catch (\Exception $e) {
            Log::error('Paystack cURL Error: ' . $e->getMessage());
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function handleCallback(Request $request)
    {
        $reference = $request->query('reference');

        try {
            // Verify the Paystack transaction
            $verificationResponse = Http::withToken(env('PAYSTACK_SECRET_KEY'))
                ->get("https://api.paystack.co/transaction/verify/{$reference}");

            $verificationData = $verificationResponse->json();

            if ($verificationData['status'] && $verificationData['data']['status'] === 'success') {
                // Update the user's wallet and save the transaction
                $user = Auth::user(); // Ensure this fetches the right user
                $amount = $verificationData['data']['amount'] / 100; // Convert from kobo to Naira

                $user->wallet_balance += $amount;
                $user->save();

                // Log the transaction
                Transaction::create([
                    'user_id' => $user->id,
                    'type' => 'wallet-topup',
                    'amount' => $amount,
                    'status' => 'success',
                    'service' => 'Wallet Top-Up',
                ]);

                return redirect()->route('dashboard')->with('success', 'Wallet top-up successful!');
            }

            return redirect()->route('dashboard')->with('error', 'Wallet top-up failed. Please try again.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

}