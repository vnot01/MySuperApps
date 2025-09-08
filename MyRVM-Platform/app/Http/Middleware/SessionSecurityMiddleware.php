<?php

namespace App\Http\Middleware;

use App\Services\SessionService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class SessionSecurityMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip session security for API routes without session
        if ($request->is('api/*') && !$request->hasSession()) {
            return $next($request);
        }

        $sessionId = $request->session()->getId();
        
        if ($sessionId) {
            // Validate session security
            $isValid = SessionService::validateSessionSecurity(
                $sessionId,
                $request->ip(),
                $request->userAgent()
            );

            if (!$isValid) {
                Log::warning("Session security validation failed", [
                    'session_id' => $sessionId,
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'url' => $request->fullUrl()
                ]);

                // Destroy the session and redirect to login
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Session security validation failed',
                        'error' => 'SESSION_SECURITY_FAILED'
                    ], 401);
                }

                return redirect()->route('admin.login')->with('error', 'Session security validation failed');
            }

            // Extend session if it's close to expiry
            $ttl = SessionService::getSessionTTL($sessionId);
            if ($ttl < 60) { // Less than 1 minute remaining
                SessionService::extendSession($sessionId, SessionService::TTL_DEFAULT);
            }
        }

        return $next($request);
    }
}
