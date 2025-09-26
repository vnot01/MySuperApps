<?php

namespace App\Jobs;

use App\Models\ReverseVendingMachine;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CheckRvmHealth implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $backoff = 60; // Wait a minute before retrying

    /**
     * The RVM instance.
     *
     * @var \App\Models\ReverseVendingMachine
     */
    protected $rvm;

    /**
     * Create a new job instance.
     */
    public function __construct(ReverseVendingMachine $rvm)
    {
        $this->rvm = $rvm;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (empty($this->rvm->ip_address)) {
            Log::warning("RVM Health Check: Skipping RVM ID {$this->rvm->id} due to missing IP address.");
            return;
        }

        $url = "http://{$this->rvm->ip_address}:5002/rvm-health";

        try {
            $response = Http::timeout(30)->get($url); // Longer timeout for potentially larger health data

            if ($response->successful()) {
                $this->rvm->update(['health_data' => $response->json()]);
                Log::info("RVM Health Check: Successfully retrieved health data for RVM ID {$this->rvm->id} from {$url}");
            } else {
                Log::warning("RVM Health Check: Failed to retrieve health data for RVM ID {$this->rvm->id} from {$url}. Status: {" . $response->status() . "}");
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error("RVM Health Check: Connection exception for RVM ID {$this->rvm->id} at {$url}. Error: {" . $e->getMessage() . "}");
        } catch (\Exception $e) {
            Log::error("RVM Health Check: An unexpected error occurred for RVM ID {$this->rvm->id} at {$url}. Error: {" . $e->getMessage() . "}");
        }
    }
}
