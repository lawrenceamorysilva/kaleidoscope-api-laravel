<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class NetoService
{
    protected string $url;
    protected string $apiKey;
    protected string $username;

    public function __construct()
    {
        $this->url = config('services.neto.url');
        $this->apiKey = config('services.neto.key');
        $this->username = config('services.neto.username');
    }

    public function getCustomerByEmail(string $email): ?array
    {
        $response = Http::withHeaders([
            'NETOAPI_ACTION'   => 'GetCustomer',
            'NETOAPI_KEY'      => config('services.neto.key'),
            'NETOAPI_USERNAME' => config('services.neto.username'),
            'Accept'           => 'application/json',
            'Content-Type'     => 'application/json',
        ])->post(config('services.neto.url'), [
            'Filter' => [
                'Email' => $email,
                'OutputSelector' => [
                    'ID',
                    'Username',
                    'OnCreditHold',
                    'DefaultInvoiceTerms',
                    'BillingAddress',
                ],
            ],
        ]);

        // Debug logging
        logger()->info('Neto Customer API response', [
            'request_email' => $email,
            'response_body' => $response->body(),
        ]);

        if ($response->successful()) {
            return $response->json()['Customer'][0] ?? null;
        }

        logger()->error('Failed to fetch customer from Neto', [
            'email' => $email,
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        return null;
    }



}
