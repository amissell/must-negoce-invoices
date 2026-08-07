<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ApiRateLimit
{
    /**
     * Rate limit configuration
     * Key: route pattern
     * Value: [requests_per_minute, burst_size]
     */
    protected array $limits = [
        'api/dashboard' => [60, 100],          // 60 requests per minute, burst to 100
        'api/invoices' => [100, 150],          // 100 requests per minute, burst to 150
        'api/clients' => [50, 75],             // 50 requests per minute, burst to 75
        'api/vendors' => [50, 75],             // 50 requests per minute, burst to 75
        'api/amevo' => [20, 30],               // 20 requests per minute, burst to 30 (external API)
    ];

    /**
     * Default rate limit for unlisted endpoints
     */
    protected array $defaultLimit = [100, 150];

    /**
     * Handle an incoming request
     */
    public function handle(Request $request, Closure $next): Response
    {
        $identifier = $this->getIdentifier($request);
        $limit = $this->getLimit($request);

        if (!$this->allowRequest($identifier, $limit)) {
            Log::warning('API rate limit exceeded', [
                'identifier' => $this->maskIdentifier($identifier),
                'route' => $request->path(),
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'error' => 'Rate limit exceeded. Please try again later.',
                'retry_after' => $this->getRetryAfter($identifier),
            ], Response::HTTP_TOO_MANY_REQUESTS)->header('Retry-After', $this->getRetryAfter($identifier));
        }

        return $next($request);
    }

    /**
     * Get a unique identifier for the request (IP + User ID or just IP)
     */
    protected function getIdentifier(Request $request): string
    {
        $userId = $request->user()?->id ?? 'anonymous';
        $ip = $request->ip() ?? 'unknown';

        return "rate_limit:{$ip}:{$userId}";
    }

    /**
     * Mask identifier in logs for privacy
     */
    protected function maskIdentifier(string $identifier): string
    {
        return preg_replace('/(\d+\.\d+)\.(\d+\.\d+)/', '$1.***', $identifier);
    }

    /**
     * Get rate limit for this request
     */
    protected function getLimit(Request $request): array
    {
        $path = $request->path();

        foreach ($this->limits as $pattern => $limit) {
            if (str_starts_with($path, $pattern)) {
                return $limit;
            }
        }

        return $this->defaultLimit;
    }

    /**
     * Check if request is allowed
     */
    protected function allowRequest(string $identifier, array $limit): bool
    {
        [$requestsPerMinute, $burstSize] = $limit;
        $ttl = 60; // 1 minute in seconds

        $cacheKey = $identifier;

        // Get current request count
        $count = Cache::get($cacheKey, 0);

        // Allow burst up to burst size
        if ($count < $burstSize) {
            // Increment counter
            Cache::put($cacheKey, $count + 1, $ttl);

            // Log approaching limit
            if ($count > ($requestsPerMinute * 0.8)) {
                Log::info('API approaching rate limit', [
                    'identifier' => $this->maskIdentifier($identifier),
                    'current_count' => $count,
                    'limit' => $requestsPerMinute,
                ]);
            }

            return true;
        }

        return false;
    }

    /**
     * Get retry-after value in seconds
     */
    protected function getRetryAfter(string $identifier): int
    {
        $ttl = Cache::getStore()->connection()->ttl($identifier);
        return max(1, $ttl); // At least 1 second
    }
}
