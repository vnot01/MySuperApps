<?php

namespace App\Http\Controllers;

use App\Models\ReverseVendingMachine;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RvmUIController extends Controller
{
    /**
     * Display RVM UI interface
     *
     * @param Request $request
     * @param string $rvmId
     * @return View
     */
    public function show(Request $request, string $rvmId): View
    {
        // Find RVM by ID
        $rvm = ReverseVendingMachine::findOrFail($rvmId);
        
        // Check if RVM is active
        if ($rvm->status !== 'active') {
            abort(404, 'RVM tidak tersedia');
        }
        
        // Pass RVM data to view
        return view('rvm.ui', [
            'rvm' => $rvm,
            'rvmId' => $rvmId,
            'websocketUrl' => config('reverb.apps.apps.0.options.host') . ':' . config('reverb.apps.apps.0.options.port'),
            'websocketKey' => config('reverb.apps.apps.0.key'),
            'websocketSecret' => config('reverb.apps.apps.0.secret'),
        ]);
    }
}
