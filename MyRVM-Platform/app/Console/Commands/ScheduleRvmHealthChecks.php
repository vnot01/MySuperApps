<?php

namespace App\Console\Commands;

use App\Jobs\CheckRvmHealth;
use App\Models\ReverseVendingMachine;
use Illuminate\Console\Command;

class ScheduleRvmHealthChecks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rvm:schedule-health-checks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatch jobs to check the health of all RVMs.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Scheduling RVM health checks...');

        $rvms = ReverseVendingMachine::all();

        foreach ($rvms as $rvm) {
            CheckRvmHealth::dispatch($rvm);
        }

        $this->info('Done scheduling RVM health checks.');
    }
}
