<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;
use Symfony\Component\HttpFoundation\Response;

class RvmCsrfMiddleware extends Middleware
{
    /**
     * Handle an incoming request.
     */
    public function handle($request, Closure $next): Response
    {
        // Check if this is a request from RVM-Jetson
        if ($this->isRvmRequest($request)) {
            return $this->handleRvmRequest($request, $next);
        }

        // For non-RVM requests, use standard CSRF verification
        return parent::handle($request, $next);
    }

    /**
     * Check if request is from RVM-Jetson
     */
    private function isRvmRequest(Request $request): bool
    {
        // Check for RVM-specific headers
        $rvmId = $request->header('X-RVM-ID');
        $rvmUserAgent = $request->header('User-Agent');
        
        // Check for RVM IP addresses
        $clientIp = $request->ip();
        $rvmIps = ['172.28.233.83', '10.3.52.161', '127.0.0.1', 'localhost'];
        
        // Check if request has RVM identifier
        return $rvmId || 
               str_contains($rvmUserAgent ?? '', 'RVM') ||
               in_array($clientIp, $rvmIps) ||
               $request->hasHeader('X-RVM-ID');
    }

    /**
     * Handle RVM request with custom CSRF logic
     */
    private function handleRvmRequest(Request $request, Closure $next): Response
    {
        // For RVM requests, we'll use API token authentication instead of CSRF
        $apiKey = $request->header('Authorization');
        
        if ($apiKey && str_starts_with($apiKey, 'Bearer ')) {
            // RVM is using API key authentication, skip CSRF
            return $next($request);
        }

        // If no API key, try to validate CSRF token
        if ($this->validateRvmCsrfToken($request)) {
            return $next($request);
        }

        // If all else fails, return 419 error
        return response()->json([
            'success' => false,
            'error' => 'CSRF token mismatch for RVM request',
            'message' => 'Please ensure proper CSRF token or API key is provided'
        ], 419);
    }

    /**
     * Validate CSRF token from RVM
     */
    private function validateRvmCsrfToken(Request $request): bool
    {
        try {
            // Get CSRF token from header
            $token = $request->header('X-XSRF-TOKEN') ?? 
                    $request->header('X-CSRF-TOKEN') ?? 
                    $request->input('_token');

            if (!$token) {
                return false;
            }

            // Validate token
            return hash_equals(
                $request->session()->token(),
                $token
            );
        } catch (\Exception $e) {
            \Log::warning('RVM CSRF validation failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * The URIs that should be excluded from CSRF verification.
     */
    protected $except = [
        // RVM-Jetson API endpoints
        'api/health-check',
        'api/status',
        'api/rvm/*',
        'admin/rvm/*/metrics',
        'admin/rvm/*/execute-command',
        'admin/rvm/*/command/*/status',
        'admin/rvm/*/recent-commands',
        'admin/rvm/*/store-metrics',
        
        // Webhook endpoints
        'webhook/*',
        
        // API endpoints for external services
        'api/*',
    ];
}
