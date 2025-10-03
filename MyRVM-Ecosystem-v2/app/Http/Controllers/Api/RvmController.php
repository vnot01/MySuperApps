<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ReverseVendingMachine;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class RvmController extends Controller
{
    /**
     * Display a listing of RVMs.
     */
    public function index(Request $request)
    {
        $query = ReverseVendingMachine::query();

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by online/offline
        if ($request->has('online')) {
            if ($request->boolean('online')) {
                $query->online();
            } else {
                $query->offline();
            }
        }

        // Search by name or location
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            });
        }

        // Pagination
        $perPage = $request->get('per_page', 15);
        $rvms = $query->orderBy('name')->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'RVMs retrieved successfully',
            'data' => $rvms
        ]);
    }

    /**
     * Store a newly created RVM.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:reverse_vending_machines',
            'location' => 'required|string|max:255',
            'address' => 'nullable|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'ip_address' => 'nullable|ip',
            'configuration' => 'nullable|array'
        ]);

        // Set default values
        $validated['status'] = 'inactive';
        $validated['capacity'] = 100;
        $validated['current_load'] = 0;

        $rvm = ReverseVendingMachine::create($validated);
        
        // Generate API key
        $apiKey = $rvm->generateApiKey();

        // Check if this is a web request (not API)
        if (!request()->is('api/*')) {
            // For web requests, return Inertia redirect with API key in session
            session()->flash('rvm_created', [
                'rvm' => $rvm->fresh(),
                'api_key' => $apiKey
            ]);
            return redirect()->back()->with('success', 'RVM created successfully');
        }
        
        // Return JSON for API requests only
        return response()->json([
            'success' => true,
            'message' => 'RVM created successfully',
            'data' => [
                'rvm' => $rvm->fresh(),
                'api_key' => $apiKey
            ]
        ], 201);
    }

    /**
     * Remove the specified RVM from storage.
     */
    public function destroy(ReverseVendingMachine $rvm)
    {
        try {
            // Delete the RVM
            $rvm->delete();
            
            // Check if this is a web request (not API)
            if (!request()->is('api/*')) {
                // For web requests, return Inertia redirect
                return redirect()->back()->with('success', 'RVM deleted successfully');
            }
            
            // Return JSON for API requests only
            return response()->json([
                'success' => true,
                'message' => 'RVM deleted successfully'
            ], 200);
            
        } catch (\Exception $e) {
            // Check if this is a web request (not API)
            if (!request()->is('api/*')) {
                // For web requests, return Inertia redirect with error
                return redirect()->back()->with('error', 'Failed to delete RVM: ' . $e->getMessage());
            }
            
            // Return JSON for API requests only
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete RVM: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified RVM.
     */
    public function show(ReverseVendingMachine $rvm)
    {
        return Inertia::render('Rvms/Show', [
            'rvm' => [
                'id' => $rvm->id,
                'name' => $rvm->name,
                'location' => $rvm->location,
                'address' => $rvm->address,
                'latitude' => $rvm->latitude,
                'longitude' => $rvm->longitude,
                'ip_address' => $rvm->ip_address,
                'status' => $rvm->status,
                'capacity' => $rvm->capacity,
                'current_load' => $rvm->current_load,
                'capacity_percentage' => $rvm->capacity_percentage,
                'connection_status' => $rvm->connection_status,
                'api_status' => $rvm->api_status,
                'last_ping' => $rvm->last_ping?->toISOString(),
                'last_connection_check' => $rvm->last_connection_check?->toISOString(),
                'last_api_check' => $rvm->last_api_check?->toISOString(),
                'api_key' => $rvm->api_key,
                'api_key_expires_at' => $rvm->api_key_expires_at?->toISOString(),
                'configuration' => $rvm->configuration,
                'metrics' => $rvm->metrics,
                'created_at' => $rvm->created_at?->toISOString(),
                'updated_at' => $rvm->updated_at?->toISOString(),
            ]
        ]);
    }

    public function edit(ReverseVendingMachine $rvm)
    {
        return Inertia::render('Rvms/Edit', [
            'rvm' => $rvm
        ]);
    }

    /**
     * Update the specified RVM in storage.
     */
    public function update(Request $request, ReverseVendingMachine $rvm)
    {
        // Check if this is a partial update from RVM Details page
        $isBasicInfoUpdate = $request->has('name') && $request->has('location') && $request->has('ip_address') && 
                            !$request->has('status') && !$request->has('capacity') && !$request->has('current_load');
        
        $isCapacityUpdate = $request->has('current_load') && !$request->has('name') && !$request->has('location') && 
                           !$request->has('ip_address') && !$request->has('status') && !$request->has('capacity');
        
        if ($isBasicInfoUpdate) {
            // Partial update for RVM Details page (only basic info)
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:reverse_vending_machines,name,' . $rvm->id,
                'location' => 'required|string|max:255',
                'ip_address' => 'nullable|ip'
            ]);
        } elseif ($isCapacityUpdate) {
            // Partial update for Capacity & Load (only current_load)
            $validated = $request->validate([
                'current_load' => 'required|integer|min:0|max:100'
            ]);
        } else {
            // Full update for RVM Edit page
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:reverse_vending_machines,name,' . $rvm->id,
                'location' => 'required|string|max:255',
                'address' => 'nullable|string',
                'latitude' => 'nullable|numeric|between:-90,90',
                'longitude' => 'nullable|numeric|between:-180,180',
                'ip_address' => 'nullable|ip',
                'status' => 'required|in:active,inactive,maintenance,error',
                'capacity' => 'required|integer|min:1|max:1000',
                'current_load' => 'required|integer|min:0',
                'configuration' => 'nullable|array'
            ]);
        }

        $rvm->update($validated);

        // Check if this is a web request (not API)
        if (!request()->is('api/*')) {
            // For Inertia requests, return Inertia response
            return Inertia::render('Rvms/Show', [
                'rvm' => [
                    'id' => $rvm->id,
                    'name' => $rvm->name,
                    'location' => $rvm->location,
                    'address' => $rvm->address,
                    'latitude' => $rvm->latitude,
                    'longitude' => $rvm->longitude,
                    'ip_address' => $rvm->ip_address,
                    'status' => $rvm->status,
                    'capacity' => $rvm->capacity,
                    'current_load' => $rvm->current_load,
                    'capacity_percentage' => $rvm->capacity_percentage,
                    'connection_status' => $rvm->connection_status,
                    'api_status' => $rvm->api_status,
                    'last_ping' => $rvm->last_ping?->toISOString(),
                    'last_connection_check' => $rvm->last_connection_check?->toISOString(),
                    'last_api_check' => $rvm->last_api_check?->toISOString(),
                    'api_key' => $rvm->api_key,
                    'api_key_expires_at' => $rvm->api_key_expires_at?->toISOString(),
                    'configuration' => $rvm->configuration,
                    'metrics' => $rvm->metrics,
                    'created_at' => $rvm->created_at?->toISOString(),
                    'updated_at' => $rvm->updated_at?->toISOString(),
                ]
            ])->with('success', 'RVM updated successfully');
        }

        // Return JSON for API requests only
        return response()->json([
            'success' => true,
            'message' => 'RVM updated successfully',
            'data' => $rvm->fresh()
        ]);
    }

    /**
     * Update API settings for the specified RVM.
     */
    public function updateApi(Request $request, ReverseVendingMachine $rvm)
    {
        $validated = $request->validate([
            'api_expiration_period' => 'required|integer|min:1|max:3650', // Max 10 years
            'regenerate_api_key' => 'boolean'
        ]);

        $expirationPeriod = $validated['api_expiration_period'];
        $shouldRegenerate = $validated['regenerate_api_key'] ?? false;

        if ($shouldRegenerate) {
            // Generate new API key
            $apiKey = $rvm->generateApiKey();
            $expirationDate = now()->addDays($expirationPeriod);
            
            $rvm->update([
                'api_key_expires_at' => $expirationDate
            ]);

            // Check if this is a web request (not API)
            if (!request()->is('api/*')) {
                // For Inertia requests, return Inertia response with new API key
                return Inertia::render('Rvms/Show', [
                    'rvm' => [
                        'id' => $rvm->id,
                        'name' => $rvm->name,
                        'location' => $rvm->location,
                        'address' => $rvm->address,
                        'latitude' => $rvm->latitude,
                        'longitude' => $rvm->longitude,
                        'ip_address' => $rvm->ip_address,
                        'status' => $rvm->status,
                        'capacity' => $rvm->capacity,
                        'current_load' => $rvm->current_load,
                        'capacity_percentage' => $rvm->capacity_percentage,
                        'connection_status' => $rvm->connection_status,
                        'api_status' => $rvm->api_status,
                        'last_ping' => $rvm->last_ping?->toISOString(),
                        'last_connection_check' => $rvm->last_connection_check?->toISOString(),
                        'last_api_check' => $rvm->last_api_check?->toISOString(),
                        'api_key' => $apiKey,
                        'api_key_expires_at' => $rvm->api_key_expires_at?->toISOString(),
                        'configuration' => $rvm->configuration,
                        'metrics' => $rvm->metrics,
                        'created_at' => $rvm->created_at?->toISOString(),
                        'updated_at' => $rvm->updated_at?->toISOString(),
                    ],
                    'new_api_key' => $apiKey
                ])->with('success', 'API key regenerated successfully');
            }

            // Return JSON for API requests
            return response()->json([
                'success' => true,
                'message' => 'API key regenerated successfully',
                'data' => [
                    'api_key' => $apiKey,
                    'api_key_expires_at' => $expirationDate->toISOString()
                ]
            ], 200);
        } else {
            // Just update expiration date
            $expirationDate = now()->addDays($expirationPeriod);
            $rvm->update([
                'api_key_expires_at' => $expirationDate
            ]);

            // Check if this is a web request (not API)
            if (!request()->is('api/*')) {
                // For Inertia requests, return Inertia response
                return Inertia::render('Rvms/Show', [
                    'rvm' => [
                        'id' => $rvm->id,
                        'name' => $rvm->name,
                        'location' => $rvm->location,
                        'address' => $rvm->address,
                        'latitude' => $rvm->latitude,
                        'longitude' => $rvm->longitude,
                        'ip_address' => $rvm->ip_address,
                        'status' => $rvm->status,
                        'capacity' => $rvm->capacity,
                        'current_load' => $rvm->current_load,
                        'capacity_percentage' => $rvm->capacity_percentage,
                        'connection_status' => $rvm->connection_status,
                        'api_status' => $rvm->api_status,
                        'last_ping' => $rvm->last_ping?->toISOString(),
                        'last_connection_check' => $rvm->last_connection_check?->toISOString(),
                        'last_api_check' => $rvm->last_api_check?->toISOString(),
                        'api_key' => $rvm->api_key,
                        'api_key_expires_at' => $rvm->api_key_expires_at?->toISOString(),
                        'configuration' => $rvm->configuration,
                        'metrics' => $rvm->metrics,
                        'created_at' => $rvm->created_at?->toISOString(),
                        'updated_at' => $rvm->updated_at?->toISOString(),
                    ]
                ])->with('success', 'API settings updated successfully');
            }

            // Return JSON for API requests
            return response()->json([
                'success' => true,
                'message' => 'API settings updated successfully',
                'data' => [
                    'api_key_expires_at' => $expirationDate->toISOString()
                ]
            ], 200);
        }
    }




    /**
     * Update RVM status
     */
    public function updateStatus(Request $request, ReverseVendingMachine $rvm)
    {
        $validated = $request->validate([
            'status' => 'required|in:active,inactive,maintenance,error'
        ]);

        $rvm->updateStatus($validated['status']);

        return response()->json([
            'success' => true,
            'message' => 'RVM status updated successfully',
            'data' => $rvm->fresh()
        ]);
    }

    /**
     * Update RVM metrics
     */
    public function updateMetrics(Request $request, ReverseVendingMachine $rvm)
    {
        $validated = $request->validate([
            'metrics' => 'required|array',
            'current_load' => 'nullable|integer|min:0'
        ]);

        $updateData = ['metrics' => $validated['metrics']];
        
        if (isset($validated['current_load'])) {
            $updateData['current_load'] = $validated['current_load'];
        }

        $rvm->updateMetrics($updateData);

        return response()->json([
            'success' => true,
            'message' => 'RVM metrics updated successfully',
            'data' => $rvm->fresh()
        ]);
    }

    /**
     * Ping RVM (heartbeat)
     */
    public function ping(ReverseVendingMachine $rvm)
    {
        $rvm->updatePing();

        return response()->json([
            'success' => true,
            'message' => 'RVM ping updated successfully',
            'data' => [
                'rvm_id' => $rvm->id,
                'name' => $rvm->name,
                'status' => $rvm->status,
                'is_online' => $rvm->is_online,
                'last_ping' => $rvm->last_ping
            ]
        ]);
    }

    /**
     * Get RVM statistics
     */
    public function statistics()
    {
        $stats = [
            'total' => ReverseVendingMachine::count(),
            'active' => ReverseVendingMachine::where('status', 'active')->count(),
            'inactive' => ReverseVendingMachine::where('status', 'inactive')->count(),
            'maintenance' => ReverseVendingMachine::where('status', 'maintenance')->count(),
            'error' => ReverseVendingMachine::where('status', 'error')->count(),
            'online' => ReverseVendingMachine::online()->count(),
            'offline' => ReverseVendingMachine::offline()->count(),
            'capacity_usage' => [
                'total_capacity' => ReverseVendingMachine::sum('capacity'),
                'total_load' => ReverseVendingMachine::sum('current_load'),
                'average_usage' => ReverseVendingMachine::avg('current_load')
            ]
        ];

        return response()->json([
            'success' => true,
            'message' => 'RVM statistics retrieved successfully',
            'data' => $stats
        ]);
    }
}