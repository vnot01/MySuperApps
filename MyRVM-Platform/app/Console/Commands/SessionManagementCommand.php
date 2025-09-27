<?php

namespace App\Console\Commands;

use App\Services\SessionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class SessionManagementCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'session:manage 
                            {action : The action to perform (cleanup, stats, list, clear)}
                            {--user-id= : User ID to filter sessions}
                            {--session-id= : Specific session ID to manage}
                            {--force : Force action without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage application sessions (cleanup, stats, list, clear)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $action = $this->argument('action');

        switch ($action) {
            case 'cleanup':
                $this->cleanupSessions();
                break;
            case 'stats':
                $this->showSessionStats();
                break;
            case 'list':
                $this->listSessions();
                break;
            case 'clear':
                $this->clearSessions();
                break;
            default:
                $this->error("Unknown action: {$action}");
                $this->info('Available actions: cleanup, stats, list, clear');
                return 1;
        }

        return 0;
    }

    /**
     * Clean up expired sessions
     */
    private function cleanupSessions(): void
    {
        $this->info('Cleaning up expired sessions...');

        try {
            $cleanedCount = SessionService::cleanupExpiredSessions();
            
            if ($cleanedCount > 0) {
                $this->info("Cleaned up {$cleanedCount} expired sessions successfully!");
            } else {
                $this->info('No expired sessions found to clean up.');
            }

        } catch (\Exception $e) {
            $this->error("Session cleanup failed: {$e->getMessage()}");
        }
    }

    /**
     * Show session statistics
     */
    private function showSessionStats(): void
    {
        $this->info('Session Statistics:');

        try {
            $stats = SessionService::getSessionStats();
            
            if (empty($stats)) {
                $this->warn('No session statistics available');
                return;
            }

            $this->table(
                ['Metric', 'Value'],
                [
                    ['Total Sessions', $stats['total_sessions'] ?? 0],
                    ['Active Sessions', $stats['active_sessions'] ?? 0],
                    ['Expired Sessions', $stats['expired_sessions'] ?? 0],
                    ['Destroyed Sessions', $stats['destroyed_sessions'] ?? 0],
                    ['User Sessions', $stats['user_sessions'] ?? 0],
                    ['Guest Sessions', $stats['guest_sessions'] ?? 0],
                    ['Average Duration', $this->formatDuration($stats['average_session_duration'] ?? 0)],
                ]
            );

            // Show hourly statistics
            if (!empty($stats['sessions_by_hour'])) {
                $this->info("\nSessions by Hour:");
                $hourlyData = [];
                foreach ($stats['sessions_by_hour'] as $hour => $count) {
                    $hourlyData[] = ["{$hour}:00", $count];
                }
                $this->table(['Hour', 'Sessions'], $hourlyData);
            }

        } catch (\Exception $e) {
            $this->error("Failed to get session statistics: {$e->getMessage()}");
        }
    }

    /**
     * List sessions
     */
    private function listSessions(): void
    {
        $userId = $this->option('user-id');
        $sessionId = $this->option('session-id');

        if ($sessionId) {
            $this->showSessionDetails($sessionId);
            return;
        }

        if ($userId) {
            $this->showUserSessions($userId);
            return;
        }

        $this->showAllSessions();
    }

    /**
     * Show all sessions
     */
    private function showAllSessions(): void
    {
        $this->info('All Sessions:');

        try {
            $pattern = 'session:*';
            $keys = Cache::getRedis()->keys($pattern);
            
            if (empty($keys)) {
                $this->warn('No sessions found');
                return;
            }

            $sessions = [];
            foreach ($keys as $key) {
                $sessionData = Cache::get($key);
                if ($sessionData) {
                    $sessions[] = [
                        'ID' => substr($sessionData['id'], 0, 8) . '...',
                        'User ID' => $sessionData['data']['user_id'] ?? 'Guest',
                        'Status' => $sessionData['status'],
                        'Created' => \Carbon\Carbon::parse($sessionData['created_at'])->format('Y-m-d H:i:s'),
                        'Last Activity' => \Carbon\Carbon::parse($sessionData['last_activity'])->format('Y-m-d H:i:s'),
                        'TTL' => $sessionData['ttl'] . 's',
                    ];
                }
            }

            $this->table(
                ['ID', 'User ID', 'Status', 'Created', 'Last Activity', 'TTL'],
                $sessions
            );

        } catch (\Exception $e) {
            $this->error("Failed to list sessions: {$e->getMessage()}");
        }
    }

    /**
     * Show user sessions
     */
    private function showUserSessions(int $userId): void
    {
        $this->info("Sessions for User ID: {$userId}");

        try {
            $sessions = SessionService::getUserSessions($userId);
            
            if (empty($sessions)) {
                $this->warn('No sessions found for this user');
                return;
            }

            $sessionData = [];
            foreach ($sessions as $session) {
                $sessionData[] = [
                    'ID' => substr($session['id'], 0, 8) . '...',
                    'Status' => $session['status'],
                    'Created' => \Carbon\Carbon::parse($session['created_at'])->format('Y-m-d H:i:s'),
                    'Last Activity' => \Carbon\Carbon::parse($session['last_activity'])->format('Y-m-d H:i:s'),
                    'IP Address' => $session['ip_address'],
                    'TTL' => $session['ttl'] . 's',
                ];
            }

            $this->table(
                ['ID', 'Status', 'Created', 'Last Activity', 'IP Address', 'TTL'],
                $sessionData
            );

        } catch (\Exception $e) {
            $this->error("Failed to get user sessions: {$e->getMessage()}");
        }
    }

    /**
     * Show session details
     */
    private function showSessionDetails(string $sessionId): void
    {
        $this->info("Session Details for ID: {$sessionId}");

        try {
            $sessionData = SessionService::getSession($sessionId);
            
            if (!$sessionData) {
                $this->warn('Session not found');
                return;
            }

            $this->table(
                ['Property', 'Value'],
                [
                    ['ID', $sessionData['id']],
                    ['Status', $sessionData['status']],
                    ['Created', \Carbon\Carbon::parse($sessionData['created_at'])->format('Y-m-d H:i:s')],
                    ['Last Activity', \Carbon\Carbon::parse($sessionData['last_activity'])->format('Y-m-d H:i:s')],
                    ['TTL', $sessionData['ttl'] . ' seconds'],
                    ['IP Address', $sessionData['ip_address']],
                    ['User Agent', substr($sessionData['user_agent'], 0, 50) . '...'],
                    ['User ID', $sessionData['data']['user_id'] ?? 'Guest'],
                ]
            );

            // Show session data
            if (!empty($sessionData['data'])) {
                $this->info("\nSession Data:");
                $this->line(json_encode($sessionData['data'], JSON_PRETTY_PRINT));
            }

        } catch (\Exception $e) {
            $this->error("Failed to get session details: {$e->getMessage()}");
        }
    }

    /**
     * Clear sessions
     */
    private function clearSessions(): void
    {
        $userId = $this->option('user-id');
        $sessionId = $this->option('session-id');
        $force = $this->option('force');

        if ($sessionId) {
            $this->clearSpecificSession($sessionId, $force);
            return;
        }

        if ($userId) {
            $this->clearUserSessions($userId, $force);
            return;
        }

        $this->clearAllSessions($force);
    }

    /**
     * Clear specific session
     */
    private function clearSpecificSession(string $sessionId, bool $force): void
    {
        if (!$force) {
            if (!$this->confirm("Are you sure you want to clear session {$sessionId}?")) {
                $this->info('Operation cancelled.');
                return;
            }
        }

        try {
            $success = SessionService::deleteSession($sessionId);
            
            if ($success) {
                $this->info("Session {$sessionId} cleared successfully!");
            } else {
                $this->error("Failed to clear session {$sessionId}");
            }

        } catch (\Exception $e) {
            $this->error("Failed to clear session: {$e->getMessage()}");
        }
    }

    /**
     * Clear user sessions
     */
    private function clearUserSessions(int $userId, bool $force): void
    {
        if (!$force) {
            if (!$this->confirm("Are you sure you want to clear all sessions for user {$userId}?")) {
                $this->info('Operation cancelled.');
                return;
            }
        }

        try {
            $sessions = SessionService::getUserSessions($userId);
            $clearedCount = 0;

            foreach ($sessions as $session) {
                if (SessionService::deleteSession($session['id'])) {
                    $clearedCount++;
                }
            }

            $this->info("Cleared {$clearedCount} sessions for user {$userId}");

        } catch (\Exception $e) {
            $this->error("Failed to clear user sessions: {$e->getMessage()}");
        }
    }

    /**
     * Clear all sessions
     */
    private function clearAllSessions(bool $force): void
    {
        if (!$force) {
            if (!$this->confirm("Are you sure you want to clear ALL sessions? This will log out all users.")) {
                $this->info('Operation cancelled.');
                return;
            }
        }

        try {
            $pattern = 'session:*';
            $keys = Cache::getRedis()->keys($pattern);
            
            $clearedCount = 0;
            foreach ($keys as $key) {
                if (Cache::forget($key)) {
                    $clearedCount++;
                }
            }

            $this->info("Cleared {$clearedCount} sessions successfully!");

        } catch (\Exception $e) {
            $this->error("Failed to clear all sessions: {$e->getMessage()}");
        }
    }

    /**
     * Format duration in human readable format
     */
    private function formatDuration(int $seconds): string
    {
        if ($seconds < 60) {
            return "{$seconds}s";
        } elseif ($seconds < 3600) {
            return round($seconds / 60) . 'm';
        } else {
            return round($seconds / 3600) . 'h';
        }
    }
}
