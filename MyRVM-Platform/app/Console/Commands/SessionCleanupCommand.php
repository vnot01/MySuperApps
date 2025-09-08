<?php

namespace App\Console\Commands;

use App\Services\SessionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SessionCleanupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'session:cleanup 
                            {--force : Force cleanup without confirmation}
                            {--dry-run : Show what would be cleaned without actually cleaning}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up expired and invalid sessions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting session cleanup...');

        try {
            if ($this->option('dry-run')) {
                $this->performDryRun();
                return 0;
            }

            if (!$this->option('force')) {
                if (!$this->confirm('Are you sure you want to clean up expired sessions?')) {
                    $this->info('Session cleanup cancelled.');
                    return 0;
                }
            }

            $cleanedCount = SessionService::cleanupExpiredSessions();
            
            if ($cleanedCount > 0) {
                $this->info("Successfully cleaned up {$cleanedCount} expired sessions.");
                Log::info("Session cleanup completed", ['cleaned_sessions' => $cleanedCount]);
            } else {
                $this->info('No expired sessions found to clean up.');
            }

            // Show session statistics after cleanup
            $this->showSessionStats();

        } catch (\Exception $e) {
            $this->error("Session cleanup failed: {$e->getMessage()}");
            Log::error("Session cleanup failed", ['error' => $e->getMessage()]);
            return 1;
        }

        return 0;
    }

    /**
     * Perform dry run to show what would be cleaned
     */
    private function performDryRun(): void
    {
        $this->info('Performing dry run...');

        try {
            $stats = SessionService::getSessionStats();
            
            if (empty($stats)) {
                $this->warn('No session statistics available');
                return;
            }

            $this->info('Current session statistics:');
            $this->table(
                ['Metric', 'Value'],
                [
                    ['Total Sessions', $stats['total_sessions'] ?? 0],
                    ['Active Sessions', $stats['active_sessions'] ?? 0],
                    ['Expired Sessions', $stats['expired_sessions'] ?? 0],
                    ['Destroyed Sessions', $stats['destroyed_sessions'] ?? 0],
                ]
            );

            $expiredCount = $stats['expired_sessions'] ?? 0;
            $destroyedCount = $stats['destroyed_sessions'] ?? 0;
            $totalToClean = $expiredCount + $destroyedCount;

            if ($totalToClean > 0) {
                $this->info("Would clean up {$totalToClean} sessions:");
                $this->line("  - {$expiredCount} expired sessions");
                $this->line("  - {$destroyedCount} destroyed sessions");
            } else {
                $this->info('No sessions need to be cleaned up.');
            }

        } catch (\Exception $e) {
            $this->error("Dry run failed: {$e->getMessage()}");
        }
    }

    /**
     * Show session statistics
     */
    private function showSessionStats(): void
    {
        try {
            $stats = SessionService::getSessionStats();
            
            if (!empty($stats)) {
                $this->info("\nUpdated session statistics:");
                $this->table(
                    ['Metric', 'Value'],
                    [
                        ['Total Sessions', $stats['total_sessions'] ?? 0],
                        ['Active Sessions', $stats['active_sessions'] ?? 0],
                        ['Expired Sessions', $stats['expired_sessions'] ?? 0],
                        ['Destroyed Sessions', $stats['destroyed_sessions'] ?? 0],
                        ['User Sessions', $stats['user_sessions'] ?? 0],
                        ['Guest Sessions', $stats['guest_sessions'] ?? 0],
                    ]
                );
            }

        } catch (\Exception $e) {
            $this->warn("Could not retrieve session statistics: {$e->getMessage()}");
        }
    }
}
