<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\ReverseVendingMachine;

class RvmSelfController extends Controller
{
    /**
     * RVM self-claim after pre-registration (auth by API key)
     */
    public function claim(Request $request): JsonResponse
    {
        $apiKey = $request->header('X-API-Key') ?: $request->input('api_key');
        if (!$apiKey) {
            return response()->json(['success' => false, 'message' => 'Missing API key'], 401);
        }

        $validated = $request->validate([
            'rvm_id' => 'required|integer|exists:reverse_vending_machines,id',
            'device_name' => 'nullable|string|max:255',
            'software_version' => 'nullable|string|max:255',
            'timezone' => 'nullable|string|max:100',
        ]);

        $rvm = ReverseVendingMachine::where('id', $validated['rvm_id'])
            ->where('api_key', $apiKey)
            ->first();

        if (!$rvm) {
            return response()->json(['success' => false, 'message' => 'Invalid API key or RVM not found'], 401);
        }

        // Update basic fields provided by device on claim
        $update = [];
        if (!empty($validated['device_name'])) {
            $update['name'] = $validated['device_name'];
        }
        if (!empty($validated['software_version'])) {
            $update['pos_settings'] = array_merge((array)($rvm->pos_settings ?? []), [
                'software_version' => $validated['software_version']
            ]);
        }
        if (!empty($validated['timezone'])) {
            $update['timezone'] = $validated['timezone'];
            $update['timezone_offset'] = $this->getTimezoneOffset($validated['timezone']);
        }

        if (!empty($update)) {
            $rvm->update($update);
        }

        return response()->json([
            'success' => true,
            'message' => 'RVM claimed successfully',
            'data' => [
                'rvm_id' => $rvm->id,
                'name' => $rvm->name,
                'timezone' => $rvm->timezone,
                'timezone_offset' => $rvm->timezone_offset,
            ]
        ]);
    }

    /**
     * RVM self-update details (IP/port/timezone) using API key
     */
    public function update(Request $request): JsonResponse
    {
        $apiKey = $request->header('X-API-Key') ?: $request->input('api_key');
        if (!$apiKey) {
            return response()->json(['success' => false, 'message' => 'Missing API key'], 401);
        }

        $validated = $request->validate([
            'ip_address' => 'nullable|ip',
            'port' => 'nullable|integer|min:1|max:65535',
            'timezone' => 'nullable|string|max:100',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        $rvm = ReverseVendingMachine::where('api_key', $apiKey)->first();
        if (!$rvm) {
            return response()->json(['success' => false, 'message' => 'Invalid API key'], 401);
        }

        $update = [];
        foreach (['ip_address','port','latitude','longitude'] as $f) {
            if (array_key_exists($f, $validated)) {
                $update[$f] = $validated[$f];
            }
        }
        if (!empty($validated['timezone'])) {
            $update['timezone'] = $validated['timezone'];
            $update['timezone_offset'] = $this->getTimezoneOffset($validated['timezone']);
        }

        if (!empty($update)) {
            $rvm->update($update);
        }

        return response()->json([
            'success' => true,
            'message' => 'RVM updated successfully',
            'data' => [
                'rvm_id' => $rvm->id,
                'ip_address' => $rvm->ip_address,
                'port' => $rvm->port,
                'timezone' => $rvm->timezone,
                'latitude' => $rvm->latitude,
                'longitude' => $rvm->longitude,
            ]
        ]);
    }

    private function getTimezoneOffset(string $timezone): string
    {
        try {
            $date = new \DateTime('now', new \DateTimeZone($timezone));
            return $date->format('P');
        } catch (\Exception $e) {
            return '+00:00';
        }
    }
}


