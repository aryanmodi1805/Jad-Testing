<?php

namespace App\Services;

use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AppleInAppPurchaseService
{
    private const PRODUCTION_BASE_URL = 'https://api.storekit.itunes.apple.com';

    private const SANDBOX_BASE_URL = 'https://api.storekit-sandbox.itunes.apple.com';

    private ?string $cachedJwt = null;

    private int $jwtExpiresAt = 0;

    public function __construct(
        private ?string $issuerId = null,
        private ?string $keyId = null,
        private ?string $bundleId = null,
        private ?string $privateKey = null,
    ) {
        $this->issuerId ??= config('services.apple.issuer_id');
        $this->keyId ??= config('services.apple.key_id');
        $this->bundleId ??= config('services.apple.bundle_id');
        $this->privateKey ??= config('services.apple.private_key');

        if ($this->privateKey) {
            $this->privateKey = $this->normalizePrivateKey($this->privateKey);
        }
    }

    public function verifyTransaction(string $signedTransaction, ?string $fallbackTransactionId = null, ?string $expectedProductId = null, ?string $environmentHint = null): array
    {
        if (! $this->isConfigured()) {
            return [
                'valid' => false,
                'error' => 'app_store_not_configured',
            ];
        }

        $decoded = $this->decodeSignedPayload($signedTransaction);

        if ($decoded === null) {
            Log::channel('IOS')->warning('Unable to decode signed transaction payload', [
                'has_fallback_transaction' => $fallbackTransactionId !== null,
            ]);
        }

        if ($decoded === null && $fallbackTransactionId === null) {
            return [
                'valid' => false,
                'error' => 'invalid_transaction',
            ];
        }

        $payload = $decoded['payload'] ?? [];

        $transactionId = $payload['transactionId'] ?? $fallbackTransactionId;
        $productId = $payload['productId'] ?? $expectedProductId;
        $bundleId = $payload['bundleId'] ?? $payload['bundleIdentifier'] ?? null;
        $environment = $payload['environment'] ?? $environmentHint ?? 'Sandbox';

        if (! $transactionId || ! $productId) {
            Log::channel('IOS')->warning('Missing transaction fields after decode', [
                'has_signed_payload' => $decoded !== null,
                'fallback_transaction' => $fallbackTransactionId,
                'expected_product' => $expectedProductId,
            ]);

            return [
                'valid' => false,
                'error' => 'invalid_transaction',
            ];
        }

        if ($bundleId !== null && $bundleId !== $this->bundleId) {
            return [
                'valid' => false,
                'error' => 'bundle_id_mismatch',
            ];
        }

        $appStoreValidation = $this->validateTransactionWithAppStore($transactionId, $environment);

        if (! $appStoreValidation['valid']) {
            return [
                'valid' => false,
                'error' => $appStoreValidation['error'] ?? 'transaction_verification_failed',
            ];
        }

        $serverPayload = $appStoreValidation['transaction'] ?? [];

        if (! empty($serverPayload)) {
            $serverProductId = $serverPayload['productId'] ?? null;
            $serverBundleId = $serverPayload['bundleId'] ?? $serverPayload['bundleIdentifier'] ?? null;

            if ($serverProductId !== null && $productId !== $serverProductId) {
                return [
                    'valid' => false,
                    'error' => 'product_id_mismatch',
                ];
            }

            if ($serverBundleId !== null && $serverBundleId !== $this->bundleId) {
                return [
                    'valid' => false,
                    'error' => 'bundle_id_mismatch',
                ];
            }
        }

        $environmentResult = $appStoreValidation['environment'] ?? $environment;
        $originalTransactionId = $payload['originalTransactionId']
            ?? $serverPayload['originalTransactionId']
            ?? null;

        return [
            'valid' => true,
            'transaction_id' => $transactionId,
            'original_transaction_id' => $originalTransactionId,
            'product_id' => $serverPayload['productId'] ?? $productId,
            'bundle_id' => $bundleId
                ?? ($serverPayload['bundleId'] ?? $serverPayload['bundleIdentifier'] ?? null)
                ?? $this->bundleId,
            'environment' => $environmentResult,
            'purchase_date_ms' => $this->toInt($payload['purchaseDate'] ?? $serverPayload['purchaseDate'] ?? null),
            'expires_date_ms' => $this->resolveExpiry($payload, $serverPayload),
            'payload' => $payload,
            'server_payload' => $serverPayload,
            'renewal_payload' => $appStoreValidation['renewal'] ?? null,
            'signed_transaction' => $signedTransaction,
        ];
    }

    public function validateTransactionWithAppStore(string $transactionId, string $environment = 'Production'): array
    {
        if (! $this->isConfigured()) {
            return [
                'valid' => false,
                'error' => 'app_store_not_configured',
            ];
        }

        $baseUrl = $this->getBaseUrl($environment);

        try {
            $response = Http::timeout(10)
                ->retry(2, 250)
                ->withHeaders([
                    'Authorization' => 'Bearer '.$this->getOrGenerateToken(),
                    'Accept' => 'application/json',
                ])
                ->get("{$baseUrl}/inApps/v1/transactions/{$transactionId}");

            if ($response->status() === 404 && $environment === 'Production') {
                return $this->validateTransactionWithAppStore($transactionId, 'Sandbox');
            }

            if (! $response->successful()) {
                $responseBody = $response->json();

                Log::channel('IOS')->warning('App Store transaction validation failed', [
                    'transaction_id' => $transactionId,
                    'environment' => $environment,
                    'status' => $response->status(),
                    'body' => $responseBody,
                ]);

                $error = match ((int) ($responseBody['errorCode'] ?? 0)) {
                    4040001, 4040002 => 'transaction_not_found',
                    default => 'transaction_verification_failed',
                };

                return [
                    'valid' => false,
                    'error' => $error,
                    'status' => $response->status(),
                ];
            }

            $payload = $response->json();

            $transaction = isset($payload['signedTransactionInfo'])
                ? $this->decodeSignedPayload($payload['signedTransactionInfo'])
                : null;

            if (! $transaction) {
                return [
                    'valid' => false,
                    'error' => 'missing_transaction_info',
                ];
            }

            $renewal = isset($payload['signedRenewalInfo'])
                ? $this->decodeSignedPayload($payload['signedRenewalInfo'])
                : null;

            return [
                'valid' => true,
                'transaction' => $transaction['payload'] ?? null,
                'renewal' => $renewal['payload'] ?? null,
                'environment' => $payload['environment'] ?? $environment,
                'raw' => $payload,
            ];
        } catch (\Throwable $throwable) {
            Log::channel('IOS')->error('App Store Server API Error', [
                'transaction_id' => $transactionId,
                'environment' => $environment,
                'message' => $throwable->getMessage(),
            ]);

            return [
                'valid' => false,
                'error' => 'app_store_connection_failed',
            ];
        }
    }

    public function decodeSignedPayload(string $signedPayload): ?array
    {
        try {
            $parts = explode('.', $signedPayload);

            if (count($parts) !== 3) {
                return null;
            }

            [$headerB64, $payloadB64, $signatureB64] = $parts;

            $headerJson = $this->base64UrlDecode($headerB64);
            $payloadJson = $this->base64UrlDecode($payloadB64);
            $signature = $this->base64UrlDecode($signatureB64);

            if ($headerJson === false || $payloadJson === false || $signature === false) {
                return null;
            }

            $header = json_decode($headerJson, true);
            $payload = json_decode($payloadJson, true);

            if (! is_array($header) || ! is_array($payload)) {
                return null;
            }

            $certChain = $header['x5c'] ?? [];

            if (empty($certChain)) {
                return null;
            }

            $leafCert = $this->formatCertificate($certChain[0]);
            $publicKey = openssl_pkey_get_public($leafCert);

            if (! $publicKey) {
                return null;
            }

            $signingInput = "{$headerB64}.{$payloadB64}";
            $derSignature = $this->convertRawSignatureToDer($signature);

            if ($derSignature === null) {
                return null;
            }

            $isVerified = openssl_verify($signingInput, $derSignature, $publicKey, OPENSSL_ALGO_SHA256);

            if ($isVerified !== 1) {
                return null;
            }

            $certificate = openssl_x509_read($leafCert);

            if ($certificate) {
                $parsedCert = openssl_x509_parse($certificate);

                if (! empty($parsedCert['validTo_time_t']) && $parsedCert['validTo_time_t'] < time()) {
                    return null;
                }
            }

            return [
                'header' => $header,
                'payload' => $payload,
            ];
        } catch (\Throwable $throwable) {
            Log::channel('IOS')->error('Failed to decode signed payload', [
                'message' => $throwable->getMessage(),
            ]);

            return null;
        }
    }

    private function getOrGenerateToken(): string
    {
        if (! $this->isConfigured()) {
            throw new \RuntimeException('Apple App Store credentials not properly configured.');
        }

        if ($this->cachedJwt && $this->jwtExpiresAt > (time() + 60)) {
            return $this->cachedJwt;
        }

        $payload = [
            'iss' => $this->issuerId,
            'iat' => time(),
            'exp' => time() + 3000,
            'aud' => 'appstoreconnect-v1',
            'bid' => $this->bundleId,
        ];

        $jwt = JWT::encode($payload, $this->privateKey, 'ES256', $this->keyId);

        $this->cachedJwt = $jwt;
        $this->jwtExpiresAt = $payload['exp'];

        return $jwt;
    }

    private function getBaseUrl(string $environment): string
    {
        return $environment === 'Sandbox' ? self::SANDBOX_BASE_URL : self::PRODUCTION_BASE_URL;
    }

    public function getBundleIdentifier(): ?string
    {
        return $this->bundleId;
    }

    private function normalizePrivateKey(string $key): string
    {
        $key = str_replace('\\n', "\n", trim($key));

        if (! str_contains($key, 'BEGIN PRIVATE KEY')) {
            $key = "-----BEGIN PRIVATE KEY-----\n".chunk_split($key, 64, "\n").'-----END PRIVATE KEY-----';
        }

        return $key;
    }

    private function formatCertificate(string $certificate): string
    {
        $certificate = str_replace('\\n', "\n", trim($certificate));

        if (! str_contains($certificate, 'BEGIN CERTIFICATE')) {
            $certificate = "-----BEGIN CERTIFICATE-----\n".chunk_split($certificate, 64, "\n").'-----END CERTIFICATE-----';
        }

        return $certificate;
    }

    private function convertRawSignatureToDer(string $signature): ?string
    {
        $length = strlen($signature);

        if ($length === 0 || ($length % 2) !== 0) {
            return null;
        }

        $halfLength = (int) ($length / 2);
        $r = substr($signature, 0, $halfLength);
        $s = substr($signature, $halfLength);

        $r = $this->normalizeEcdsaComponent($r);
        $s = $this->normalizeEcdsaComponent($s);

        if ($r === null || $s === null) {
            return null;
        }

        $encodedR = "\x02".$this->encodeAsn1Length(strlen($r)).$r;
        $encodedS = "\x02".$this->encodeAsn1Length(strlen($s)).$s;

        $sequenceBody = $encodedR.$encodedS;

        return "\x30".$this->encodeAsn1Length(strlen($sequenceBody)).$sequenceBody;
    }

    private function normalizeEcdsaComponent(string $component): ?string
    {
        if ($component === '') {
            return "\x00";
        }

        $component = ltrim($component, "\x00");

        if ($component === '') {
            $component = "\x00";
        }

        if ((ord($component[0]) & 0x80) !== 0) {
            $component = "\x00".$component;
        }

        return $component;
    }

    private function encodeAsn1Length(int $length): string
    {
        if ($length < 128) {
            return chr($length);
        }

        $bytes = '';

        while ($length > 0) {
            $bytes = chr($length & 0xFF).$bytes;
            $length >>= 8;
        }

        return chr(0x80 | strlen($bytes)).$bytes;
    }

    private function base64UrlDecode(string $input): string|false
    {
        $remainder = strlen($input) % 4;
        if ($remainder) {
            $input .= str_repeat('=', 4 - $remainder);
        }

        return base64_decode(strtr($input, '-_', '+/'));
    }

    private function resolveExpiry(array $payload, array $serverPayload): ?int
    {
        if (isset($payload['expiresDateMs'])) {
            return $this->toInt($payload['expiresDateMs']);
        }

        if (isset($payload['expiresDate'])) {
            return $this->toInt($payload['expiresDate']);
        }

        if (isset($payload['expirationDate'])) {
            return $this->toInt($payload['expirationDate']);
        }

        if (isset($payload['expirationDateMs'])) {
            return $this->toInt($payload['expirationDateMs']);
        }

        if (isset($serverPayload['expiresDateMs'])) {
            return $this->toInt($serverPayload['expiresDateMs']);
        }

        if (isset($serverPayload['expiresDate'])) {
            return $this->toInt($serverPayload['expiresDate']);
        }

        if (isset($serverPayload['expirationDate'])) {
            return $this->toInt($serverPayload['expirationDate']);
        }

        if (isset($serverPayload['expirationDateMs'])) {
            return $this->toInt($serverPayload['expirationDateMs']);
        }

        return null;
    }

    private function toInt(mixed $value): ?int
    {
        if ($value === null) {
            return null;
        }

        return (int) $value;
    }

    private function isConfigured(): bool
    {
        return ! empty($this->issuerId)
            && ! empty($this->keyId)
            && ! empty($this->bundleId)
            && ! empty($this->privateKey);
    }
}
