<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class MPesaService
{
    protected $consumerKey;
    protected $consumerSecret;
    protected $passkey;
    protected $shortcode;
    protected $environment;
    protected $baseUrl;
    protected $callbackUrl;

    public function __construct()
    {
        $this->environment = config('services.mpesa.environment');
        $this->consumerKey = config('services.mpesa.consumer_key');
        $this->consumerSecret = config('services.mpesa.consumer_secret');
        $this->passkey = config('services.mpesa.passkey');
        $this->shortcode = config('services.mpesa.shortcode');
        $this->baseUrl = $this->environment === 'production'
            ? 'https://api.safaricom.co.ke'
            : 'https://sandbox.safaricom.co.ke';
        $this->callbackUrl = config('services.mpesa.callback_url');
    }

    public function generateAccessToken()
    {
        $credentials = base64_encode($this->consumerKey . ':' . $this->consumerSecret);

        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . $credentials
        ])->get($this->baseUrl . '/oauth/v1/generate?grant_type=client_credentials');

        $result = $response->json();

        if (isset($result['access_token'])) {
            return $result['access_token'];
        }

        Log::error('M-Pesa access token generation failed', $result);
        throw new \Exception('Failed to generate M-Pesa access token');
    }

    public function initiateSTKPush($phoneNumber, $amount, $reference, $description)
    {
        $accessToken = $this->generateAccessToken();
        $timestamp = Carbon::now()->format('YmdHis');
        $password = base64_encode($this->shortcode . $this->passkey . $timestamp);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
            'Content-Type' => 'application/json',
        ])->post($this->baseUrl . '/mpesa/stkpush/v1/processrequest', [
            'BusinessShortCode' => $this->shortcode,
            'Password' => $password,
            'Timestamp' => $timestamp,
            'TransactionType' => 'CustomerPayBillOnline',
            'Amount' => $amount,
            'PartyA' => $this->formatPhoneNumber($phoneNumber),
            'PartyB' => $this->shortcode,
            'PhoneNumber' => $this->formatPhoneNumber($phoneNumber),
            'CallBackURL' => $this->callbackUrl,
            'AccountReference' => $reference,
            'TransactionDesc' => $description
        ]);

        $result = $response->json();

        Log::info('M-Pesa STK push request', [
            'response' => $result,
            'phone' => $this->formatPhoneNumber($phoneNumber),
            'amount' => $amount,
            'reference' => $reference
        ]);

        return $result;
    }

    public function checkTransactionStatus($checkoutRequestId)
    {
        $accessToken = $this->generateAccessToken();
        $timestamp = Carbon::now()->format('YmdHis');
        $password = base64_encode($this->shortcode . $this->passkey . $timestamp);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
            'Content-Type' => 'application/json',
        ])->post($this->baseUrl . '/mpesa/stkpushquery/v1/query', [
            'BusinessShortCode' => $this->shortcode,
            'Password' => $password,
            'Timestamp' => $timestamp,
            'CheckoutRequestID' => $checkoutRequestId
        ]);

        return $response->json();
    }

    protected function formatPhoneNumber($phoneNumber)
    {
        // Remove any non-numeric characters
        $phoneNumber = preg_replace('/\D/', '', $phoneNumber);

        // Check if it starts with '0' and replace with '254'
        if (substr($phoneNumber, 0, 1) == '0') {
            $phoneNumber = '254' . substr($phoneNumber, 1);
        }

        // Check if it starts with '+' and replace with nothing
        if (substr($phoneNumber, 0, 1) == '+') {
            $phoneNumber = substr($phoneNumber, 1);
        }

        // Ensure it starts with '254'
        if (substr($phoneNumber, 0, 3) != '254') {
            $phoneNumber = '254' . $phoneNumber;
        }

        return $phoneNumber;
    }
}
