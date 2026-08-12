<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class AmevoApiService
{
    private const MAX_RETRIES = 3;
    private const RETRY_DELAY_MS = 1000; // 1 second
    private const TIMEOUT = 30; // 30 seconds

    /**
     * Get receivables data from Amevo API with retry logic
     *
     * @param string $vendorId
     * @param array $params Additional query parameters
     * @return array
     * @throws Exception
     */
    public function getReceivables(string $vendorId, array $params = []): array
    {
        $this->validateVendorId($vendorId);

        $url = $this->endpoint('/api/receivables/' . rawurlencode($vendorId));

        return $this->makeRequestWithRetry('GET', $url, [
            'query' => $params,
            'headers' => $this->authHeaders(),
        ]);
    }

    /**
     * Get vendor data from Amevo API
     *
     * @param string $vendorId
     * @return array
     * @throws Exception
     */
    public function getVendor(string $vendorId): array
    {
        $this->validateVendorId($vendorId);

        $url = $this->endpoint('/api/vendors/' . rawurlencode($vendorId));

        return $this->makeRequestWithRetry('GET', $url, [
            'headers' => $this->authHeaders(),
        ]);
    }

    private function endpoint(string $path): string
    {
        $baseUrl = rtrim((string) config('services.amevo.base_url'), '/');

        if ($baseUrl === '') {
            throw new Exception('Amevo API base URL is not configured.');
        }

        return $baseUrl . '/' . ltrim($path, '/');
    }

    private function authHeaders(): array
    {
        $apiKey = (string) config('services.amevo.api_key');

        if ($apiKey === '') {
            throw new Exception('Amevo API key is not configured. Ask the Amevo account owner for the production key.');
        }

        return [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $apiKey,
        ];
    }

    /**
     * Make HTTP request with automatic retry logic
     *
     * @param string $method HTTP method (GET, POST, etc)
     * @param string $url Full URL
     * @param array $options HTTP options
     * @return array Parsed response
     * @throws Exception
     */
    private function makeRequestWithRetry(string $method, string $url, array $options = []): array
    {
        $attempt = 0;
        $lastException = null;

        while ($attempt < self::MAX_RETRIES) {
            try {
                $attempt++;

                Log::debug("Amevo API request attempt $attempt", [
                    'method' => $method,
                    'url' => $this->maskUrl($url),
                ]);

                $options['timeout'] = self::TIMEOUT;

                $response = Http::withHeaders($options['headers'] ?? [])
                    ->timeout(self::TIMEOUT)
                    ->{$method}($url, $options['query'] ?? []);

                if ($response->failed()) {
                    $statusCode = $response->status();

                    if ($statusCode >= 400 && $statusCode < 500) {
                        throw new Exception("Amevo API client error: {$statusCode}");
                    }

                    throw new Exception("Amevo API HTTP error: {$statusCode}");
                }

                $data = $response->json();

                if (!$data || !is_array($data)) {
                    throw new Exception("Invalid Amevo API response format");
                }

                if (isset($data['error'])) {
                    throw new Exception("Amevo API error: {$data['error']}");
                }

                Log::info("Amevo API request successful", [
                    'method' => $method,
                    'attempt' => $attempt,
                ]);

                return $data;

            } catch (Exception $e) {
                $lastException = $e;

                Log::warning("Amevo API request failed", [
                    'attempt' => $attempt,
                    'max_retries' => self::MAX_RETRIES,
                    'error' => $e->getMessage(),
                    'exception' => get_class($e),
                ]);

                if ($attempt < self::MAX_RETRIES) {
                    $delayMs = self::RETRY_DELAY_MS * (2 ** ($attempt - 1));
                    usleep($delayMs * 1000);
                } else {
                    break;
                }
            }
        }

        Log::error("Amevo API request failed after {$attempt} attempts", [
            'error' => $lastException?->getMessage(),
            'url' => $this->maskUrl($url),
        ]);

        throw new Exception(
            "Amevo API request failed after {$attempt} attempts: " .
            ($lastException?->getMessage() ?? 'Unknown error')
        );
    }

    /**
     * Validate vendor ID format to prevent injection
     *
     * @param string $vendorId
     * @throws Exception
     */
    private function validateVendorId(string $vendorId): void
    {
        if (empty($vendorId)) {
            throw new Exception("Vendor ID cannot be empty");
        }

        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $vendorId)) {
            throw new Exception("Invalid vendor ID format");
        }

        if (strlen($vendorId) > 255) {
            throw new Exception("Vendor ID too long");
        }
    }

    /**
     * Mask sensitive data in logs (API key, tokens)
     *
     * @param string $url
     * @return string
     */
    private function maskUrl(string $url): string
    {
        return preg_replace('/api_key=[^&]+/', 'api_key=***', $url);
    }

    /**
     * Parse receivables data into standardized format
     *
     * @param array $receivables Raw data from Amevo API
     * @return array Formatted data
     */
    public function parseReceivablesData(array $receivables): array
    {
        return [
            'total_receivables' => $receivables['total'] ?? 0,
            'count' => $receivables['count'] ?? 0,
            'items' => $receivables['items'] ?? [],
            'last_updated' => now()->toDateTimeString(),
        ];
    }
}
