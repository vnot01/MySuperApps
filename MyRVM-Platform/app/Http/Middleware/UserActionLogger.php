<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Events\UserActionNotification;
use App\Models\Notification;
use Symfony\Component\HttpFoundation\Response;

class UserActionLogger
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        
        // Only log for authenticated users
        if (Auth::check()) {
            $this->logUserAction($request, $response);
        }
        
        return $response;
    }

    /**
     * Log user action based on request
     */
    private function logUserAction(Request $request, Response $response): void
    {
        try {
            $user = Auth::user();
            $method = $request->method();
            $path = $request->path();
            $statusCode = $response->getStatusCode();
            
            // Only log successful actions (2xx status codes)
            if ($statusCode < 200 || $statusCode >= 300) {
                return;
            }
            
            // Skip logging for certain paths
            if ($this->shouldSkipLogging($path)) {
                return;
            }
            
            $actionData = $this->parseAction($method, $path, $request);
            
            if ($actionData) {
                $details = [
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'path' => $path,
                    'method' => $method,
                    'status_code' => $statusCode,
                ];
                
                // Add specific details based on action
                if (isset($actionData['details'])) {
                    $details = array_merge($details, $actionData['details']);
                }
                
                // Broadcast the event
                broadcast(new UserActionNotification(
                    $user,
                    $actionData['action'],
                    $actionData['resource'] ?? null,
                    $details
                ));
                
                // Create notification record for admin
                $this->createNotificationRecord($user, $actionData, $details);
            }
            
        } catch (\Exception $e) {
            Log::error('Failed to log user action: ' . $e->getMessage());
        }
    }

    /**
     * Check if logging should be skipped for this path
     */
    private function shouldSkipLogging(string $path): bool
    {
        $skipPaths = [
            'api/notifications',
            'api/health',
            'api/ping',
            'livewire',
            '_debugbar',
            'telescope',
            'horizon',
            'css/',
            'js/',
            'images/',
            'fonts/',
            'favicon.ico',
        ];
        
        foreach ($skipPaths as $skipPath) {
            if (str_starts_with($path, $skipPath)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Parse action from request method and path
     */
    private function parseAction(string $method, string $path, Request $request): ?array
    {
        // Handle API routes
        if (str_starts_with($path, 'api/')) {
            return $this->parseApiAction($method, $path, $request);
        }
        
        // Handle web routes
        return $this->parseWebAction($method, $path, $request);
    }

    /**
     * Parse API action
     */
    private function parseApiAction(string $method, string $path, Request $request): ?array
    {
        $pathParts = explode('/', trim($path, '/'));
        
        if (count($pathParts) < 2) {
            return null;
        }
        
        $resource = $pathParts[1] ?? null; // api/rvms -> rvms
        
        switch ($method) {
            case 'GET':
                if (count($pathParts) > 2 && is_numeric($pathParts[2])) {
                    return [
                        'action' => 'view',
                        'resource' => $resource,
                        'details' => ['resource_id' => $pathParts[2]]
                    ];
                }
                return [
                    'action' => 'view',
                    'resource' => $resource,
                    'details' => ['action_type' => 'list']
                ];
                
            case 'POST':
                return [
                    'action' => 'create',
                    'resource' => $resource,
                    'details' => $this->getResourceDetails($request)
                ];
                
            case 'PUT':
            case 'PATCH':
                $resourceId = $pathParts[2] ?? null;
                return [
                    'action' => 'update',
                    'resource' => $resource,
                    'details' => array_merge(
                        ['resource_id' => $resourceId],
                        $this->getResourceDetails($request)
                    )
                ];
                
            case 'DELETE':
                $resourceId = $pathParts[2] ?? null;
                return [
                    'action' => 'delete',
                    'resource' => $resource,
                    'details' => ['resource_id' => $resourceId]
                ];
        }
        
        return null;
    }

    /**
     * Parse web action
     */
    private function parseWebAction(string $method, string $path, Request $request): ?array
    {
        $pathParts = explode('/', trim($path, '/'));
        
        // Handle admin routes
        if (isset($pathParts[0]) && $pathParts[0] === 'admin') {
            $resource = $pathParts[1] ?? null;
            
            switch ($method) {
                case 'GET':
                    if (count($pathParts) > 2) {
                        if ($pathParts[2] === 'create') {
                            return null; // Don't log form views
                        }
                        if (is_numeric($pathParts[2])) {
                            return [
                                'action' => 'view',
                                'resource' => $resource,
                                'details' => ['resource_id' => $pathParts[2]]
                            ];
                        }
                    }
                    return [
                        'action' => 'view',
                        'resource' => $resource,
                        'details' => ['action_type' => 'list']
                    ];
                    
                case 'POST':
                    return [
                        'action' => 'create',
                        'resource' => $resource,
                        'details' => $this->getResourceDetails($request)
                    ];
                    
                case 'PUT':
                case 'PATCH':
                    $resourceId = $pathParts[2] ?? null;
                    return [
                        'action' => 'update',
                        'resource' => $resource,
                        'details' => array_merge(
                            ['resource_id' => $resourceId],
                            $this->getResourceDetails($request)
                        )
                    ];
                    
                case 'DELETE':
                    $resourceId = $pathParts[2] ?? null;
                    return [
                        'action' => 'delete',
                        'resource' => $resource,
                        'details' => ['resource_id' => $resourceId]
                    ];
            }
        }
        
        return null;
    }

    /**
     * Get resource details from request
     */
    private function getResourceDetails(Request $request): array
    {
        $details = [];
        
        // Get resource name if available
        if ($request->has('name')) {
            $details['resource_name'] = $request->input('name');
        } elseif ($request->has('title')) {
            $details['resource_name'] = $request->input('title');
        } elseif ($request->has('rvm_name')) {
            $details['resource_name'] = $request->input('rvm_name');
        }
        
        // Add other relevant details
        if ($request->has('status')) {
            $details['status'] = $request->input('status');
        }
        
        return $details;
    }

    /**
     * Create notification record
     */
    private function createNotificationRecord($user, array $actionData, array $details): void
    {
        try {
            $title = $this->generateNotificationTitle($actionData, $user);
            $message = $this->generateNotificationMessage($actionData, $user, $details);
            
            Notification::createNotification([
                'notification_id' => 'user_action_' . $user->id . '_' . microtime(true),
                'user_id' => null, // Admin notification
                'title' => $title,
                'message' => $message,
                'type' => $this->getNotificationType($actionData['action']),
                'category' => 'user_action',
                'data' => [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'user_role' => $user->role ?? 'user',
                    'action' => $actionData['action'],
                    'resource' => $actionData['resource'] ?? '',
                    'details' => $details,
                ],
                'is_system_wide' => false
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create user action notification: ' . $e->getMessage());
        }
    }

    /**
     * Generate notification title
     */
    private function generateNotificationTitle(array $actionData, $user): string
    {
        $action = ucfirst($actionData['action']);
        $resource = $actionData['resource'] ?? 'item';
        
        return "User {$action} - {$resource}";
    }

    /**
     * Generate notification message
     */
    private function generateNotificationMessage(array $actionData, $user, array $details): string
    {
        $userName = $user->name;
        $action = $actionData['action'];
        $resource = $actionData['resource'] ?? 'item';
        
        $message = "{$userName} {$action}d {$resource}";
        
        if (isset($details['resource_id'])) {
            $message .= " (ID: {$details['resource_id']})";
        }
        
        return $message;
    }

    /**
     * Get notification type based on action
     */
    private function getNotificationType(string $action): string
    {
        switch ($action) {
            case 'create':
                return 'success';
            case 'update':
                return 'info';
            case 'delete':
                return 'warning';
            case 'view':
                return 'info';
            default:
                return 'info';
        }
    }
}