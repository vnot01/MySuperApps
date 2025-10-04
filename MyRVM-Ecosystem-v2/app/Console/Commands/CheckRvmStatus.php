<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ReverseVendingMachine;

class CheckRvmStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rvm:check-status {--rvm-id= : Check specific RVM ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check RVM connection and API status';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $rvmId = $this->option('rvm-id');
        
        if ($rvmId) {
            $rvm = ReverseVendingMachine::find($rvmId);
            if (!$rvm) {
                $this->error("RVM with ID {$rvmId} not found.");
                return 1;
            }
            $this->checkSingleRvm($rvm);
        } else {
            $this->checkAllRvms();
        }
        
        return 0;
    }

    private function checkAllRvms()
    {
        $rvms = ReverseVendingMachine::all();
        
        $this->info("Checking status for {$rvms->count()} RVMs...");
        
        $headers = ['ID', 'Name', 'IP Address', 'Status', 'Connection', 'API', 'Last Check'];
        $rows = [];
        
        foreach ($rvms as $rvm) {
            $this->info("Checking RVM: {$rvm->name} (ID: {$rvm->id})");
            
            // Update status based on load
            $rvm->updateStatusBasedOnLoad();
            
            // Check connection status
            $connectionResult = $rvm->checkConnectionStatus();
            
            // Check API status
            $apiResult = $rvm->checkApiStatus();
            
            $rows[] = [
                $rvm->id,
                $rvm->name,
                $rvm->ip_address ?? 'N/A',
                $rvm->status,
                $rvm->connection_status,
                $rvm->api_status,
                $rvm->last_connection_check?->format('Y-m-d H:i:s') ?? 'Never'
            ];
        }
        
        $this->table($headers, $rows);
        $this->info("Status check completed!");
    }

    private function checkSingleRvm(ReverseVendingMachine $rvm)
    {
        $this->info("Checking RVM: {$rvm->name} (ID: {$rvm->id})");
        
        // Update status based on load
        $this->info("Updating status based on load...");
        $rvm->updateStatusBasedOnLoad();
        $this->info("Status: {$rvm->status}");
        
        // Check connection status
        $this->info("Checking connection status...");
        $connectionResult = $rvm->checkConnectionStatus();
        $this->info("Connection: " . ($connectionResult ? 'Connected' : 'Disconnected'));
        
        // Check API status
        $this->info("Checking API status...");
        $apiResult = $rvm->checkApiStatus();
        $this->info("API: " . ($apiResult ? 'Valid' : 'Invalid'));
        
        // Show comprehensive status
        $status = $rvm->getComprehensiveStatus();
        $this->info("Comprehensive Status:");
        $this->table(['Property', 'Value'], [
            ['Status', $status['status']],
            ['Connection Status', $status['connection_status']],
            ['API Status', $status['api_status']],
            ['Is Online', $status['is_online'] ? 'Yes' : 'No'],
            ['API Valid', $status['is_api_valid'] ? 'Yes' : 'No'],
            ['Capacity %', $status['capacity_percentage'] . '%'],
            ['Last Connection Check', $status['last_connection_check']?->format('Y-m-d H:i:s') ?? 'Never'],
            ['Last API Check', $status['last_api_check']?->format('Y-m-d H:i:s') ?? 'Never']
        ]);
    }
}