<?php

namespace App\Jobs;

use App\Helpers\RvmStatusHelper;
use App\Models\ReverseVendingMachine;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CheckRvmPulse implements ShouldQueue
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
    public $backoff = 10;

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
            Log::warning("RVM Pulse Check: Skipping RVM ID {$this->rvm->id} due to missing IP address.");
            return;
        }

        // Construct the full URL for the pulse check
        $url = "http://{$this->rvm->ip_address}:5002/pulse";

        try {
            $response = Http::timeout(5)->get($url);

            if ($response->successful()) {
                $this->rvm->update([
                    'status' => 'connected',
                    'connection_status' => 'connected',
                    'last_pulse_at' => Carbon::now(),
                ]);
                Log::info("RVM Pulse Check: Successfully connected to RVM ID {$this->rvm->id} at {$url}");
            } else {
                $this->rvm->update([
                    'status' => 'disconnected',
                    'connection_status' => 'disconnected'
                ]);
                Log::warning("RVM Pulse Check: Failed to connect to RVM ID {$this->rvm->id} at {$url}. Status: {" . $response->status() . "}");
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            $this->rvm->update([
                'status' => RvmStatusHelper::calculateStatus($this->rvm),
                'connection_status' => 'disconnected'
            ]);
            Log::error("RVM Pulse Check: Connection exception for RVM ID {$this->rvm->id} at {$url}. Error: {" . $e->getMessage() . "}");
        } catch (\Exception $e) {
            $this->rvm->update([
                'status' => RvmStatusHelper::calculateStatus($this->rvm),
                'connection_status' => 'disconnected'
            ]);
            Log::error("RVM Pulse Check: An unexpected error occurred for RVM ID {$this->rvm->id} at {$url}. Error: {" . $e->getMessage() . "}");
        }
    }
}
