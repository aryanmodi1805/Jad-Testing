<?php

namespace App\Services\Accounting;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use RuntimeException;

class WafeqService
{
    private string $apiKey;
    private string $baseUrl = 'https://api.wafeq.com/v1';

    // Saudi VAT = 15%
    private float $vatRate = 0.15;

    public function __construct()
    {
        $this->apiKey = config('services.wafeq.api_key');

        if (empty($this->apiKey)) {
            throw new RuntimeException('Wafeq API key is missing.');
        }
    }

    /**
     * Get the next sequential invoice number from Wafeq
     * Fetches invoices and finds the last INV-XXXX to increment
     */
    private function getNextInvoiceNumber(): string
    {
        // Use cache lock to prevent race conditions
        return Cache::lock('wafeq_invoice_number', 10)->block(5, function () {
            // Fetch recent invoices from Wafeq (sorted by newest first)
            // Fetch more to find the last INV-* format invoice
            $response = Http::withHeaders(['Authorization' => 'Api-Key ' . $this->apiKey])
                ->get($this->baseUrl . '/invoices/', [
                    'page_size' => 50, // Fetch more to find last INV-* invoice
                    'ordering' => '-created_at', // Most recent first
                ]);

            if (!$response->successful()) {
                // Fallback: generate based on timestamp if API fails
                Log::warning('WAFEQ: Could not fetch invoices, using timestamp fallback');
                return 'INV-' . time();
            }

            $invoices = $response->json()['results'] ?? [];
            
            if (empty($invoices)) {
                // No invoices yet, start with INV-0001
                return 'INV-0001';
            }

            // Find the highest INV-XXXX number (ignore WLT-*, SRV-*, etc.)
            $highestNumber = 0;
            foreach ($invoices as $invoice) {
                $invoiceNumber = $invoice['invoice_number'] ?? '';
                if (preg_match('/^INV-(\d+)$/', $invoiceNumber, $matches)) {
                    $number = (int)$matches[1];
                    if ($number > $highestNumber) {
                        $highestNumber = $number;
                    }
                }
            }
            
            if ($highestNumber > 0) {
                $nextNumber = $highestNumber + 1;
                return 'INV-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
            }
            
            // No INV-* invoices found, start with INV-0001
            return 'INV-0001';
        });
    }

    /**
     * Wallet recharge invoice (FINAL amount includes VAT)
     */
    public function createWalletRechargeInvoice($user, float $amount, string $transactionId, string $currency = 'SAR')
    {
        try {
            // Prevent duplicate invoices for same transaction
            $cacheKey = 'wafeq_invoice_' . $transactionId;
            if (Cache::has($cacheKey)) {
                Log::info("WAFEQ: Invoice already created for transaction: {$transactionId}");
                return ['duplicate' => true, 'transaction_id' => $transactionId];
            }
            
            $netAmount = $this->netAmountBeforeVat($amount);
            
            // 1. Get/Create Contact ID
            $contactId = $this->getOrCreateContact($user);

            // 2. Create Invoice with sequential invoice number
            $result = $this->createInvoice([
                'invoice_number'    => $this->getNextInvoiceNumber(),
                'invoice_date'      => now()->format('Y-m-d'),
                'invoice_due_date'  => now()->format('Y-m-d'),
                'currency'          => $currency,
                'language'          => 'ar',

                // Required so invoice shows in Wafeq UI
                'status'            => 'SENT',

                // Contact ID is required
                'contact'           => $contactId,

                // Line items (NET amount only)
                'line_items' => [
                    [
                        'description' => 'Wallet Recharge',
                        'quantity'    => 1,
                        'unit_amount' => $netAmount, // BEFORE VAT
                        'account'     => config('services.wafeq.revenue_account'),
                        'tax_rate'    => config('services.wafeq.vat_tax_rate'),
                    ],
                ],

                'notes' => "Wallet recharge | Ref: {$transactionId}",
            ]);
            
            // Mark as created to prevent duplicates (cache for 24 hours)
            if ($result && isset($result['id'])) {
                Cache::put($cacheKey, $result['id'], now()->addHours(24));
            } elseif ($result && isset($result['error']) && $result['error'] === true) {
                // Log failed invoice attempt with friendly message
                $this->logFailedInvoice(
                    user: $user,
                    invoiceType: 'wallet_recharge',
                    amount: $amount,
                    transactionId: $transactionId,
                    currency: $currency,
                    errorMessage: $result['friendly_message'],
                    errorDetails: $result['technical_details'],
                    requestPayload: ['amount' => $amount, 'currency' => $currency, 'contact_id' => $contactId ?? null]
                );
                return false;
            }
            
            return $result;
        } catch (\Exception $e) {
            Log::error("WAFEQ: Critical failure in createWalletRechargeInvoice: " . $e->getMessage());
            
            $this->logFailedInvoice(
                user: $user,
                invoiceType: 'wallet_recharge',
                amount: $amount,
                transactionId: $transactionId,
                currency: $currency,
                errorMessage: 'System Error: ' . $e->getMessage(),
                errorDetails: ['exception' => get_class($e), 'trace' => substr($e->getTraceAsString(), 0, 1000)],
                requestPayload: ['amount' => $amount, 'currency' => $currency]
            );
            
            return false;
        }
    }

    /**
     * Service payment invoice (FINAL amount includes VAT)
     */
    public function createServicePaymentInvoice(
        $customer,
        $seller,
        string $service,
        float $amount,
        string $transactionId,
        string $currency = 'SAR'
    ) {
        try {
            // Prevent duplicate invoices for same transaction
            $cacheKey = 'wafeq_invoice_' . $transactionId;
            if (Cache::has($cacheKey)) {
                Log::info("WAFEQ: Invoice already created for transaction: {$transactionId}");
                return ['duplicate' => true, 'transaction_id' => $transactionId];
            }
            
            $netAmount = $this->netAmountBeforeVat($amount);
            
            // 1. Get/Create Contact ID
            $contactId = $this->getOrCreateContact($customer);

            $result = $this->createInvoice([
                'invoice_number'    => $this->getNextInvoiceNumber(),
                'invoice_date'      => now()->format('Y-m-d'),
                'invoice_due_date'  => now()->format('Y-m-d'),
                'currency'          => $currency,
                'language'          => 'ar',

                // Required so invoice shows in Wafeq UI
                'status'            => 'SENT',

                // Contact ID is required
                'contact'           => $contactId,

                // Line items (NET amount only)
                'line_items' => [
                    [
                        'description' => "Service: {$service} | Seller: {$seller->name}",
                        'quantity'    => 1,
                        'unit_amount' => $netAmount, // BEFORE VAT
                        'account'     => config('services.wafeq.revenue_account'),
                        'tax_rate'    => config('services.wafeq.vat_tax_rate'),
                    ],
                ],

                'notes' => "Service payment | Ref: {$transactionId}",
            ]);
            
            // Mark as created to prevent duplicates (cache for 24 hours)
            if ($result && isset($result['id'])) {
                Cache::put($cacheKey, $result['id'], now()->addHours(24));
            } elseif ($result && isset($result['error']) && $result['error'] === true) {
                // Log failed invoice attempt with friendly message
                $this->logFailedInvoice(
                    user: $customer,
                    invoiceType: 'service_payment',
                    amount: $amount,
                    transactionId: $transactionId,
                    currency: $currency,
                    errorMessage: $result['friendly_message'],
                    errorDetails: array_merge(
                        $result['technical_details'] ?? [],
                        ['seller' => $seller->name ?? 'Unknown', 'service' => $service]
                    ),
                    requestPayload: ['amount' => $amount, 'currency' => $currency, 'contact_id' => $contactId ?? null]
                );
                return false;
            }
            
            return $result;
        } catch (\Exception $e) {
            Log::error("WAFEQ: Critical failure in createServicePaymentInvoice: " . $e->getMessage());
            
            $this->logFailedInvoice(
                user: $customer,
                invoiceType: 'service_payment',
                amount: $amount,
                transactionId: $transactionId,
                currency: $currency,
                errorMessage: 'System Error: ' . $e->getMessage(),
                errorDetails: ['exception' => get_class($e), 'trace' => substr($e->getTraceAsString(), 0, 1000)],
                requestPayload: ['amount' => $amount, 'currency' => $currency]
            );
            
            return false;
        }
    }

    /**
     * Core invoice creation
     */
    private function createInvoice(array $payload)
    {
        Log::info('WAFEQ CREATE REQUEST', $payload);

        $response = Http::timeout(15)
            ->retry(3, 1000)
            ->withHeaders([
                'Authorization' => 'Api-Key ' . $this->apiKey,
                'Accept'        => 'application/json',
                'Content-Type'  => 'application/json',
            ])
            ->post($this->baseUrl . '/invoices/', $payload);

        $data = $response->json();

        Log::info('WAFEQ RAW RESPONSE', [
            'status' => $response->status(),
            'body'   => $data,
        ]);

        // Success
        if ($response->status() === 201 && isset($data['id'])) {
            Log::info('WAFEQ INVOICE CREATED', [
                'invoice_id'     => $data['id'],
                'invoice_number' => $data['invoice_number'] ?? null,
            ]);

            return $data;
        }

        // Failure - return error details for logging
        $friendlyMessage = $this->getFriendlyErrorMessage($response->status(), $data);
        
        Log::error('WAFEQ INVOICE CREATION FAILED', [
            'status' => $response->status(),
            'body'   => $data,
        ]);

        return [
            'error' => true,
            'friendly_message' => $friendlyMessage,
            'technical_details' => $data,
            'status_code' => $response->status(),
        ];
    }

    /**
     * Convert Wafeq API errors to user-friendly messages
     */
    private function getFriendlyErrorMessage(int $statusCode, ?array $data): string
    {
        // Check for specific field errors
        if (isset($data['detail'])) {
            $detail = $data['detail'];
            if (str_contains($detail, 'account')) {
                return 'Accounting configuration issue - revenue account not set up correctly';
            }
            if (str_contains($detail, 'tax_rate')) {
                return 'Tax configuration issue - VAT rate not set up correctly';
            }
            if (str_contains($detail, 'contact')) {
                return 'Customer contact could not be created in accounting system';
            }
        }

        // Status code based messages
        return match($statusCode) {
            400 => 'Invalid invoice data - please check configuration',
            401 => 'Accounting system authentication failed - check API key',
            403 => 'Permission denied by accounting system',
            404 => 'Accounting system endpoint not found',
            429 => 'Too many requests - accounting system rate limited',
            500, 502, 503 => 'Accounting system temporarily unavailable',
            default => 'Could not create invoice in accounting system',
        };
    }

    /**
     * Get or Create Contact in Wafeq
     * Matches by name + phone to ensure correct contact is used
     */
    private function getOrCreateContact($user)
    {
        $userName = $user->name ?? 'Unknown';
        $userPhone = $user->phone ?? '';
        $userEmail = $user->email ?? '';
        
        // 1. Search by phone (more unique identifier)
        if (!empty($userPhone)) {
            $searchResponse = Http::withHeaders(['Authorization' => 'Api-Key ' . $this->apiKey])
                ->get($this->baseUrl . '/contacts/', ['search' => $userPhone]);

            if ($searchResponse->successful()) {
                $results = $searchResponse->json()['results'] ?? [];
                // Find exact match by name and phone
                foreach ($results as $contact) {
                    if (($contact['name'] ?? '') === $userName) {
                        Log::info("WAFEQ: Found existing contact: {$userName}");
                        return $contact['id'];
                    }
                }
            }
        }

        // 2. Create new contact with user's actual name
        Log::info("WAFEQ: Creating new contact: {$userName}");
        $payload = [
            'name'  => $userName,
            'phone' => $userPhone,
        ];
        
        if (!empty($userEmail)) {
            $payload['email'] = $userEmail;
        }

        $createResponse = Http::withHeaders(['Authorization' => 'Api-Key ' . $this->apiKey])
            ->post($this->baseUrl . '/contacts/', $payload);

        if ($createResponse->successful()) {
            return $createResponse->json()['id'];
        }

        // Fallback or error handling
        throw new RuntimeException('Failed to create Wafeq contact: ' . $createResponse->body());
    }

    /**
     * Convert FINAL amount (VAT included) → NET amount (before VAT)
     */
    private function netAmountBeforeVat(float $finalAmount): float
    {
        return round($finalAmount / (1 + $this->vatRate), 2);
    }

    /**
     * Log a failed invoice attempt to the database
     */
    public function logFailedInvoice(
        $user,
        string $invoiceType,
        float $amount,
        string $transactionId,
        string $currency,
        string $errorMessage,
        ?array $errorDetails = null,
        ?array $requestPayload = null
    ): void {
        try {
            \App\Models\FailedInvoice::create([
                'invoiceable_id' => $user->id ?? 0,
                'invoiceable_type' => $user ? get_class($user) : \App\Models\Seller::class, // Fallback to Seller if unknown
                'transaction_id' => $transactionId,
                'invoice_type' => $invoiceType,
                'amount' => $amount,
                'currency' => $currency,
                'error_message' => $errorMessage,
                'error_details' => $errorDetails,
                'request_payload' => $requestPayload,
                'resolved' => false,
            ]);
            Log::info("WAFEQ: Logged failed invoice for transaction: {$transactionId}");
        } catch (\Exception $e) {
            Log::error("WAFEQ: Could not log failed invoice: " . $e->getMessage());
        }
    }
}
