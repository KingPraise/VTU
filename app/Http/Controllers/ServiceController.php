<?php
namespace App\Http\Controllers;

use App\Models\Transaction;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ServiceController extends Controller
{
    // Handle the transaction process for services like airtime and data
    public function processTransaction(Request $request)
    {
        $user = Auth::user(); // Get the authenticated user
        $amount = $request->input('amount'); // Get the amount for the transaction
        $serviceType = $request->input('service'); // Determine the type of service (e.g., 'airtime', 'data')
        $phoneNumber = $request->input('phone_number'); // Get the phone number for the transaction
        $paymentMethod = $request->input('payment_method'); // Get the selected payment method (wallet or paystack)
        $walletBalance = $user->wallet_balance; // Get the user's current wallet balance

        if ($paymentMethod === 'wallet') {
            // If the payment method is wallet and the user has enough balance
            if ($walletBalance >= $amount) {
                $this->deductFromWallet($user, $amount); // Deduct the amount from the user's wallet
                return $this->processService($user, $serviceType, $amount, $phoneNumber, $request); // Process the service
            }

            // If the wallet balance is insufficient, redirect back with an error message
            return redirect()->back()->withErrors(['error' => 'Insufficient wallet balance.']);
        } elseif ($paymentMethod === 'paystack') {
            // If the payment method is Paystack, initiate a Paystack payment
            return $this->paystackPayment($amount, $serviceType, $phoneNumber);
        }

        // If an invalid payment method is selected, redirect back with an error message
        return redirect()->back()->withErrors(['error' => 'Invalid payment method selected.']);
    }

    // Method to handle payments through Paystack
    private function paystackPayment($amount, $serviceType, $phoneNumber = null)
    {
        $paystackSecretKey = config('services.paystack.secret'); // Get Paystack secret key from config
        $client = new Client(); // Create a new Guzzle HTTP client

        try {
            // Initialize the Paystack payment
            $response = $client->post('https://api.paystack.co/transaction/initialize', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $paystackSecretKey, // Set the authorization header
                    'Content-Type' => 'application/json', // Set content type to JSON
                ],
                'json' => [
                    'email' => Auth::user()->email, // User's email address
                    'amount' => $amount * 100, // Paystack requires the amount in kobo
                    'callback_url' => route('paystack.callback'), // URL to redirect after payment
                    'metadata' => [
                        'service_type' => $serviceType, // Include the service type in metadata
                        'phone_number' => $phoneNumber, // Include the phone number in metadata
                    ],
                ],
            ]);

            // Decode the response body from JSON
            $responseBody = json_decode($response->getBody(), true);

            if ($responseBody['status']) {
                // If the initialization was successful, redirect to the payment page
                return redirect($responseBody['data']['authorization_url']);
            }

            // If the initialization failed, return back with an error
            return redirect()->back()->withErrors('Paystack payment initialization failed. Please try again.');
        } catch (\Exception $e) {
            // Handle any exceptions that occur during the API request
            return redirect()->back()->withErrors('Error: ' . $e->getMessage());
        }
    }

    // Method to handle the callback from Paystack after payment
    public function handlePaystackCallback(Request $request)
    {
        $paystackSecretKey = config('services.paystack.secret'); // Get Paystack secret key from config
        $reference = $request->query('reference'); // Get the payment reference from the callback

        try {
            $client = new Client(); // Create a new Guzzle HTTP client
            // Verify the transaction with Paystack using the reference
            $response = $client->get('https://api.paystack.co/transaction/verify/' . $reference, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $paystackSecretKey, // Set the authorization header
                ],
            ]);

            // Decode the response body from JSON
            $responseBody = json_decode($response->getBody(), true);

            if ($responseBody['status'] && $responseBody['data']['status'] == 'success') {
                // If the verification is successful, extract service details
                $serviceType = $responseBody['data']['metadata']['service_type'];
                $amount = $responseBody['data']['amount'] / 100; // Convert from kobo to naira
                $phoneNumber = $responseBody['data']['metadata']['phone_number'];

                // Process the service after successful payment
                return $this->processService(Auth::user(), $serviceType, $amount, $phoneNumber, $request);
            }

            // If verification failed, redirect back with an error
            return redirect()->route('dashboard')->withErrors('Payment verification failed. Please try again.');
        } catch (\Exception $e) {
            // Handle any exceptions that occur during verification
            return redirect()->route('dashboard')->withErrors('Error: ' . $e->getMessage());
        }
    }

    // Method to process the actual service (e.g., airtime, data purchase)
    private function processService($user, $serviceType, $amount, $phoneNumber = null, $request)
    {
        $paystackSecretKey = config('services.paystack.secret'); // Get Paystack secret key from config
        $client = new Client(); // Create a new Guzzle HTTP client

        $serviceApiEndpoint = ''; // Placeholder for the service endpoint URL
        $data = []; // Placeholder for the API request data

        // Determine the API endpoint and data based on the service type
        switch ($serviceType) {
            case 'airtime':
                $serviceApiEndpoint = 'https://api.paystack.co/bill/airtime'; // Airtime purchase endpoint
                $data = [
                    'email' => $user->email, // User's email
                    'amount' => $amount * 100, // Paystack uses kobo for amount
                    'phone' => $phoneNumber, // Phone number for the airtime
                    'network' => $this->getNetworkCode($request->input('network')), // Map network to code
                ];
                break;

            case 'data':
                $serviceApiEndpoint = 'https://api.paystack.co/bill/data'; // Data bundle purchase endpoint
                $data = [
                    'email' => $user->email, // User's email
                    'amount' => $amount * 100, // Paystack uses kobo for amount
                    'phone' => $phoneNumber, // Phone number for the data bundle
                    'network' => $this->getNetworkCode($request->input('network')), // Map network to code
                ];
                break;

            default:
                // Redirect back with an error if the service type is invalid
                return redirect()->back()->withErrors('Invalid service type selected.');
        }

        try {
            // Make the API request to the service endpoint
            $response = $client->post($serviceApiEndpoint, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $paystackSecretKey, // Set the authorization header
                    'Content-Type' => 'application/json', // Set content type to JSON
                ],
                'json' => $data, // Set the request data
            ]);

            // Decode the response body from JSON
            $responseBody = json_decode($response->getBody(), true);

            if ($responseBody['status']) {
                // If the API request is successful, log the transaction in the database
                Transaction::create([
                    'user_id' => $user->id,
                    'type' => $serviceType,
                    'amount' => $amount,
                    'status' => 'success',
                    'service' => ucfirst($serviceType),
                ]);

                // Redirect back to the dashboard with a success message
                return redirect()->route('dashboard')->with('success', ucfirst($serviceType) . ' transaction successful!');
            }

            // If the API request failed, redirect back with an error
            return redirect()->back()->withErrors('Service transaction failed. Please try again.');
        } catch (\Exception $e) {
            // Handle any exceptions that occur during the API request
            return redirect()->back()->withErrors('Service processing error: ' . $e->getMessage());
        }
    }

    // Helper method to map network names to network codes for API requests
    private function getNetworkCode($network)
    {
        $networks = [
            'MTN' => 'mtn',
            'Airtel' => 'airtel',
            'Glo' => 'glo',
            '9mobile' => 'etisalat',
        ];

        return $networks[$network] ?? null; // Return the corresponding network code or null if not found
    }

    // Deduct the specified amount from the user's wallet balance
    private function deductFromWallet($user, $amount)
    {
        $user->wallet_balance -= $amount; // Subtract the amount from the user's wallet balance
        $user->save(); // Save the updated wallet balance
    }
}