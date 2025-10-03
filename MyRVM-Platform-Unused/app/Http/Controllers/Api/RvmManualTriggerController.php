<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\CheckRvmPulse;
use App\Jobs\CheckRvmHealth;
use App\Models\ReverseVendingMachine;
use Illuminate\Http\JsonResponse;

class RvmManualTriggerController extends Controller
{
    /**
     * Manually trigger a pulse check for all RVMs.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function triggerPulseCheck(): JsonResponse
    {
        $rvms = ReverseVendingMachine::all();

        foreach ($rvms as $rvm) {
            CheckRvmPulse::dispatch($rvm);
        }

        return response()->json(['message' => 'Pulse check for all RVMs has been dispatched.']);
    }

    /**
     * Manually trigger a health check for all RVMs.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function triggerHealthCheck(): JsonResponse
    {
        $rvms = ReverseVendingMachine::all();

        foreach ($rvms as $rvm) {
            CheckRvmHealth::dispatch($rvm);
        }

        return response()->json(['message' => 'Health check for all RVMs has been dispatched.']);
    }
}
