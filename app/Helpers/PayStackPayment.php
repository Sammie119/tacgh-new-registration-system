<?php

namespace App\Helpers;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\App;

class PayStackPayment
{
    protected $client;
    protected $secretKey;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => config('services.paystack.payment_url'),
            'headers' => [
                'Authorization' => 'Bearer '.config('services.paystack.secret_key'),
                'Content-Type' => 'application/json',
            ],
            'verify' => !App::isLocal(), // ← Add this line to disable SSL verification
        ]);
        $this->secretKey = config('services.paystack.secret_key');
    }

    public function initializeTransaction(array $data)
    {
        if($data['amount'] > 0){
            $response = $this->client->post('/transaction/initialize', [
                'json' => $data
            ]);

            return json_decode($response->getBody(), true);
        }

        return $data;
    }

    public function verifyTransaction($reference)
    {
        $response = $this->client->get("/transaction/verify/{$reference}");

        return json_decode($response->getBody(), true);
    }
}
