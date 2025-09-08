<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;

class SessionService
{
    /**
     * Session TTL constants
     */
    const TTL_DEFAULT = 120;        // 2 minutes (default Laravel session)
    const TTL_EXTENDED = 3600;      // 1 hour
    const TTL_LONG = 86400;         // 24 hours
    const TTL_REMEMBER = 2592000;   // 30 days (remember me)

    /**
     * Session key prefixes
     */
    const PREFIX_SESSION = 'session';
    const PREFIX_USER_SESSION = 'user_session';
    const PREFIX_GUEST_SESSION = 'guest_session';
    const PREFIX_REMEMBER_TOKEN = 'remember_token';

    /**
     * Session status constants
     */
    const STATUS_ACTIVE = 'active';
    const STATUS_EXPIRED = 'expired';
    const STATUS_DESTROYED = 'destroyed';

    /**
     * Store session data
     */
    public static function storeSession(string $sessionId, array $data, int $ttl = self::TTL_DEFAULT): bool
    {
        try {
            $sessionData = [
                'id' => $sessionId,
                'data' => $data,
                'created_at' => now()->toISOString(),
                'last_activity' => now()->toISOString(),
                'ttl' => $ttl,
                'status' => self::STATUS_ACTIVE,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ];

            $cacheKey = self::getSessionKey($sessionId);
            $success = Cache::put($cacheKey, $sessionData, $ttl);

            if ($success) {
                // Store session ID in user sessions index if user_id exists
                if (isset($data['user_id'])) {
                    self::addToUserSessions($data['user_id'], $sessionId, $ttl);
                }

                Log::info("Session stored", [
                    'session_id' => $sessionId,
                    'user_id' => $data['user_id'] ?? null,
                    'ttl' => $ttl
                ]);
            }

            return $success;
        } catch (\Exception $e) {
            Log::error("Session store error", [
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get session data
     */
    public static function getSession(string $sessionId): ?array
    {
        try {
            $cacheKey = self::getSessionKey($sessionId);
            $sessionData = Cache::get($cacheKey);

            if (!$sessionData) {
                return null;
            }

            // Check if session is expired
            if ($sessionData['status'] === self::STATUS_EXPIRED) {
                return null;
            }

            // Update last activity
            $sessionData['last_activity'] = now()->toISOString();
            Cache::put($cacheKey, $sessionData, $sessionData['ttl']);

            return $sessionData;
        } catch (\Exception $e) {
            Log::error("Session get error", [
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Update session data
     */
    public static function updateSession(string $sessionId, array $updates, int $ttl = null): bool
    {
        try {
            $sessionData = self::getSession($sessionId);
            if (!$sessionData) {
                return false;
            }

            // Merge updates with existing data
            $sessionData['data'] = array_merge($sessionData['data'], $updates);
            $sessionData['last_activity'] = now()->toISOString();

            if ($ttl) {
                $sessionData['ttl'] = $ttl;
            }

            $cacheKey = self::getSessionKey($sessionId);
            return Cache::put($cacheKey, $sessionData, $sessionData['ttl']);
        } catch (\Exception $e) {
            Log::error("Session update error", [
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Delete session
     */
    public static function deleteSession(string $sessionId): bool
    {
        try {
            $sessionData = self::getSession($sessionId);
            if ($sessionData && isset($sessionData['data']['user_id'])) {
                self::removeFromUserSessions($sessionData['data']['user_id'], $sessionId);
            }

            $cacheKey = self::getSessionKey($sessionId);
            return Cache::forget($cacheKey);
        } catch (\Exception $e) {
            Log::error("Session delete error", [
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Destroy session (mark as destroyed)
     */
    public static function destroySession(string $sessionId): bool
    {
        try {
            $sessionData = self::getSession($sessionId);
            if (!$sessionData) {
                return false;
            }

            $sessionData['status'] = self::STATUS_DESTROYED;
            $sessionData['destroyed_at'] = now()->toISOString();

            $cacheKey = self::getSessionKey($sessionId);
            return Cache::put($cacheKey, $sessionData, 60); // Keep for 1 minute for logging
        } catch (\Exception $e) {
            Log::error("Session destroy error", [
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get session TTL
     */
    public static function getSessionTTL(string $sessionId): int
    {
        try {
            $cacheKey = self::getSessionKey($sessionId);
            $sessionData = Cache::get($cacheKey);
            
            if (!$sessionData) {
                return 0;
            }

            return Cache::getRedis()->ttl($cacheKey);
        } catch (\Exception $e) {
            Log::error("Session TTL error", [
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    /**
     * Extend session TTL
     */
    public static function extendSession(string $sessionId, int $additionalTtl = self::TTL_DEFAULT): bool
    {
        try {
            $sessionData = self::getSession($sessionId);
            if (!$sessionData) {
                return false;
            }

            $newTtl = $sessionData['ttl'] + $additionalTtl;
            $sessionData['ttl'] = $newTtl;
            $sessionData['last_activity'] = now()->toISOString();

            $cacheKey = self::getSessionKey($sessionId);
            return Cache::put($cacheKey, $sessionData, $newTtl);
        } catch (\Exception $e) {
            Log::error("Session extend error", [
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get user sessions
     */
    public static function getUserSessions(int $userId): array
    {
        try {
            $userSessionsKey = self::getUserSessionsKey($userId);
            $sessionIds = Cache::get($userSessionsKey, []);

            $sessions = [];
            foreach ($sessionIds as $sessionId) {
                $sessionData = self::getSession($sessionId);
                if ($sessionData) {
                    $sessions[] = $sessionData;
                }
            }

            return $sessions;
        } catch (\Exception $e) {
            Log::error("Get user sessions error", [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Get active sessions count
     */
    public static function getActiveSessionsCount(): int
    {
        try {
            $pattern = self::getSessionKey('*');
            $keys = Cache::getRedis()->keys($pattern);
            
            $count = 0;
            foreach ($keys as $key) {
                $sessionData = Cache::get($key);
                if ($sessionData && $sessionData['status'] === self::STATUS_ACTIVE) {
                    $count++;
                }
            }

            return $count;
        } catch (\Exception $e) {
            Log::error("Get active sessions count error", [
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    /**
     * Get session statistics
     */
    public static function getSessionStats(): array
    {
        try {
            $pattern = self::getSessionKey('*');
            $keys = Cache::getRedis()->keys($pattern);
            
            $stats = [
                'total_sessions' => 0,
                'active_sessions' => 0,
                'expired_sessions' => 0,
                'destroyed_sessions' => 0,
                'user_sessions' => 0,
                'guest_sessions' => 0,
                'average_session_duration' => 0,
                'sessions_by_hour' => [],
            ];

            $totalDuration = 0;
            $sessionCount = 0;
            $hourlyStats = [];

            foreach ($keys as $key) {
                $sessionData = Cache::get($key);
                if (!$sessionData) continue;

                $stats['total_sessions']++;

                switch ($sessionData['status']) {
                    case self::STATUS_ACTIVE:
                        $stats['active_sessions']++;
                        break;
                    case self::STATUS_EXPIRED:
                        $stats['expired_sessions']++;
                        break;
                    case self::STATUS_DESTROYED:
                        $stats['destroyed_sessions']++;
                        break;
                }

                if (isset($sessionData['data']['user_id'])) {
                    $stats['user_sessions']++;
                } else {
                    $stats['guest_sessions']++;
                }

                // Calculate session duration
                if (isset($sessionData['created_at']) && isset($sessionData['last_activity'])) {
                    $created = \Carbon\Carbon::parse($sessionData['created_at']);
                    $lastActivity = \Carbon\Carbon::parse($sessionData['last_activity']);
                    $duration = $lastActivity->diffInSeconds($created);
                    $totalDuration += $duration;
                    $sessionCount++;
                }

                // Hourly statistics
                $hour = \Carbon\Carbon::parse($sessionData['created_at'])->format('H');
                $hourlyStats[$hour] = ($hourlyStats[$hour] ?? 0) + 1;
            }

            if ($sessionCount > 0) {
                $stats['average_session_duration'] = round($totalDuration / $sessionCount);
            }

            $stats['sessions_by_hour'] = $hourlyStats;

            return $stats;
        } catch (\Exception $e) {
            Log::error("Get session stats error", [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Clean up expired sessions
     */
    public static function cleanupExpiredSessions(): int
    {
        try {
            $pattern = self::getSessionKey('*');
            $keys = Cache::getRedis()->keys($pattern);
            
            $cleanedCount = 0;
            foreach ($keys as $key) {
                $sessionData = Cache::get($key);
                if (!$sessionData) {
                    Cache::forget($key);
                    $cleanedCount++;
                    continue;
                }

                // Check if session is expired
                $created = \Carbon\Carbon::parse($sessionData['created_at']);
                $ttl = $sessionData['ttl'] ?? self::TTL_DEFAULT;
                $expiresAt = $created->addSeconds($ttl);

                if (now()->gt($expiresAt)) {
                    $sessionData['status'] = self::STATUS_EXPIRED;
                    $sessionData['expired_at'] = now()->toISOString();
                    
                    // Remove from user sessions if applicable
                    if (isset($sessionData['data']['user_id'])) {
                        self::removeFromUserSessions($sessionData['data']['user_id'], $sessionData['id']);
                    }

                    Cache::forget($key);
                    $cleanedCount++;
                }
            }

            Log::info("Session cleanup completed", [
                'cleaned_sessions' => $cleanedCount
            ]);

            return $cleanedCount;
        } catch (\Exception $e) {
            Log::error("Session cleanup error", [
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    /**
     * Get session key
     */
    private static function getSessionKey(string $sessionId): string
    {
        return CacheService::getKey(self::PREFIX_SESSION, $sessionId);
    }

    /**
     * Get user sessions key
     */
    private static function getUserSessionsKey(int $userId): string
    {
        return CacheService::getKey(self::PREFIX_USER_SESSION, (string) $userId);
    }

    /**
     * Add session to user sessions index
     */
    private static function addToUserSessions(int $userId, string $sessionId, int $ttl): void
    {
        try {
            $userSessionsKey = self::getUserSessionsKey($userId);
            $sessionIds = Cache::get($userSessionsKey, []);
            
            if (!in_array($sessionId, $sessionIds)) {
                $sessionIds[] = $sessionId;
                Cache::put($userSessionsKey, $sessionIds, $ttl);
            }
        } catch (\Exception $e) {
            Log::error("Add to user sessions error", [
                'user_id' => $userId,
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Remove session from user sessions index
     */
    private static function removeFromUserSessions(int $userId, string $sessionId): void
    {
        try {
            $userSessionsKey = self::getUserSessionsKey($userId);
            $sessionIds = Cache::get($userSessionsKey, []);
            
            $sessionIds = array_filter($sessionIds, function($id) use ($sessionId) {
                return $id !== $sessionId;
            });
            
            if (empty($sessionIds)) {
                Cache::forget($userSessionsKey);
            } else {
                Cache::put($userSessionsKey, array_values($sessionIds), self::TTL_DEFAULT);
            }
        } catch (\Exception $e) {
            Log::error("Remove from user sessions error", [
                'user_id' => $userId,
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Generate secure session ID
     */
    public static function generateSessionId(): string
    {
        return Str::random(40);
    }

    /**
     * Validate session security
     */
    public static function validateSessionSecurity(string $sessionId, string $ipAddress, string $userAgent): bool
    {
        try {
            $sessionData = self::getSession($sessionId);
            if (!$sessionData) {
                return false;
            }

            // Check IP address
            if ($sessionData['ip_address'] !== $ipAddress) {
                Log::warning("Session IP mismatch", [
                    'session_id' => $sessionId,
                    'expected_ip' => $sessionData['ip_address'],
                    'actual_ip' => $ipAddress
                ]);
                return false;
            }

            // Check user agent
            if ($sessionData['user_agent'] !== $userAgent) {
                Log::warning("Session user agent mismatch", [
                    'session_id' => $sessionId,
                    'expected_ua' => $sessionData['user_agent'],
                    'actual_ua' => $userAgent
                ]);
                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error("Session security validation error", [
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}