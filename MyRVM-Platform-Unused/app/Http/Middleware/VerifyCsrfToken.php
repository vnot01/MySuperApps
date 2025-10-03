<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        // RVM-Jetson API endpoints
        'api/health-check',
        'api/status',
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
