<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use App\Models\ReverseVendingMachine;

class SessionService
{
    /**
     * Create a new RVM session
     *
     * @param int $rvmId
     * @return array
     */
    public function createSession(int $rvmId): array
    {
        // Verify RVM exists
        $rvm = ReverseVendingMachine::find($rvmId);
        if (!$rvm) {
            throw new \Exception('RVM not found');
        }

        // Generate unique session token
        $sessionToken = Str::uuid()->toString();
        
        // Store session in cache with 10 minutes expiration
        $sessionData = [
            'token' => $sessionToken,
            'rvm_id' => $rvmId,
            'status' => 'menunggu_otorisasi',
            'user_id' => null,
            'created_at' => now(),
            'expires_at' => now()->addMinutes(10)
        ];
        
        Cache::put("rvm_session:{$sessionToken}", $sessionData, 600); // 10 minutes
        
        return $sessionData;
    }

    /**
     * Claim session by user
     *
     * @param string $sessionToken
     * @param int $userId
     * @return array
     */
    public function claimSession(string $sessionToken, int $userId): array
    {
        $sessionData = $this->getSession($sessionToken);
        
        if ($sessionData['status'] !== 'menunggu_otorisasi') {
            throw new \Exception('Session is no longer available for authorization');
        }

        // Update session with user info
        $sessionData['status'] = 'diotorisasi';
        $sessionData['user_id'] = $userId;
        $sessionData['authorized_at'] = now();
        
        Cache::put("rvm_session:{$sessionToken}", $sessionData, 600);
        
        return $sessionData;
    }

    /**
     * Activate session as guest
     *
     * @param string $sessionToken
     * @return array
     */
    public function activateGuestSession(string $sessionToken): array
    {
        $sessionData = $this->getSession($sessionToken);
        
        if ($sessionData['status'] !== 'menunggu_otorisasi') {
            throw new \Exception('Session is no longer available for activation');
        }

        // Update session for guest mode
        $sessionData['status'] = 'aktif_sebagai_tamu';
        $sessionData['activated_at'] = now();
        
        Cache::put("rvm_session:{$sessionToken}", $sessionData, 600);
        
        return $sessionData;
    }

    /**
     * Get session data
     *
     * @param string $sessionToken
     * @return array
     */
    public function getSession(string $sessionToken): array
    {
        $sessionData = Cache::get("rvm_session:{$sessionToken}");
        
        if (!$sessionData) {
            throw new \Exception('Session token not found or expired');
        }

        return $sessionData;
    }

    /**
     * Check if session is valid
     *
     * @param string $sessionToken
     * @return bool
     */
    public function isValidSession(string $sessionToken): bool
    {
        try {
            $this->getSession($sessionToken);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Clean up expired sessions
     *
     * @return int Number of sessions cleaned
     */
    public function cleanupExpiredSessions(): int
    {
        // This would typically be called by a scheduled task
        // For now, we rely on Cache TTL to handle expiration
        return 0;
    }
}
