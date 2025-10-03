<?php

namespace App\Console\Commands;

use App\Jobs\CheckRvmPulse;
use App\Models\ReverseVendingMachine;
use Illuminate\Console\Command;

class ScheduleRvmPulseChecks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rvm:schedule-pulse-checks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatch jobs to check the pulse of all RVMs.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Scheduling RVM pulse checks...');

        $rvms = ReverseVendingMachine::all();

        foreach ($rvms as $rvm) {
            CheckRvmPulse::dispatch($rvm);
        }

        $this->info('Done scheduling RVM pulse checks.');
    }
}
