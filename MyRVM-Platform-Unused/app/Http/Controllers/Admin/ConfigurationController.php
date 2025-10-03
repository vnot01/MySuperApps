<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RvmConfiguration;
use App\Models\ReverseVendingMachine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ConfigurationController extends Controller
{
    public function index($id)
    {
        try {
            $rvm = ReverseVendingMachine::findOrFail($id);
            $configurations = $rvm->configurations()->active()->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'rvm_id' => $rvm->id,
                    'rvm_name' => $rvm->name,
                    'configurations' => $configurations->map(function ($config) {
                        return [
                            'id' => $config->id,
                            'config_key' => $config->config_key,
                            'config_value' => $config->typed_value,
                            'config_type' => $config->config_type,
                            'description' => $config->description,
                            'is_active' => $config->is_active,
                            'updated_at' => $config->updated_at
                        ];
                    })
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get configurations: ' . $e->getMessage()
            ], 500);
        }
    }

    public function get($id, $key)
    {
        try {
            $rvm = ReverseVendingMachine::findOrFail($id);
            $configuration = $rvm->configurations()->active()->byKey($key)->first();

            if (!$configuration) {
                return response()->json([
                    'success' => false,
                    'message' => 'Configuration not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'rvm_id' => $rvm->id,
                    'rvm_name' => $rvm->name,
                    'configuration' => [
                        'id' => $configuration->id,
                        'config_key' => $configuration->config_key,
                        'config_value' => $configuration->typed_value,
                        'config_type' => $configuration->config_type,
                        'description' => $configuration->description,
                        'is_active' => $configuration->is_active,
                        'updated_at' => $configuration->updated_at
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get configuration: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id, $key)
    {
        $validator = Validator::make($request->all(), [
            'config_value' => 'required',
            'config_type' => 'nullable|in:string,integer,boolean,json,float',
            'description' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $rvm = ReverseVendingMachine::findOrFail($id);
            $configuration = $rvm->configurations()->byKey($key)->first();

            if (!$configuration) {
                // Create new configuration
                $configuration = RvmConfiguration::create([
                    'rvm_id' => $id,
                    'config_key' => $key,
                    'config_value' => $request->config_value,
                    'config_type' => $request->config_type ?? 'string',
                    'description' => $request->description,
                    'is_active' => true
                ]);
            } else {
                // Update existing configuration
                $configuration->config_value = $request->config_value;
                if ($request->has('config_type')) {
                    $configuration->config_type = $request->config_type;
                }
                if ($request->has('description')) {
                    $configuration->description = $request->description;
                }
                $configuration->save();
            }

            return response()->json([
                'success' => true,
                'message' => 'Configuration updated successfully',
                'data' => [
                    'rvm_id' => $rvm->id,
                    'rvm_name' => $rvm->name,
                    'configuration' => [
                        'id' => $configuration->id,
                        'config_key' => $configuration->config_key,
                        'config_value' => $configuration->typed_value,
                        'config_type' => $configuration->config_type,
                        'description' => $configuration->description,
                        'is_active' => $configuration->is_active,
                        'updated_at' => $configuration->updated_at
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update configuration: ' . $e->getMessage()
            ], 500);
        }
    }

    public function delete($id, $key)
    {
        try {
            $rvm = ReverseVendingMachine::findOrFail($id);
            $configuration = $rvm->configurations()->byKey($key)->first();

            if (!$configuration) {
                return response()->json([
                    'success' => false,
                    'message' => 'Configuration not found'
                ], 404);
            }

            $configuration->delete();

            return response()->json([
                'success' => true,
                'message' => 'Configuration deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete configuration: ' . $e->getMessage()
            ], 500);
        }
    }

    public function bulkUpdate(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'configurations' => 'required|array',
            'configurations.*.config_key' => 'required|string',
            'configurations.*.config_value' => 'required',
            'configurations.*.config_type' => 'nullable|in:string,integer,boolean,json,float',
            'configurations.*.description' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $rvm = ReverseVendingMachine::findOrFail($id);
            $updatedConfigurations = [];

            foreach ($request->configurations as $configData) {
                $configuration = $rvm->configurations()->byKey($configData['config_key'])->first();

                if (!$configuration) {
                    $configuration = RvmConfiguration::create([
                        'rvm_id' => $id,
                        'config_key' => $configData['config_key'],
                        'config_value' => $configData['config_value'],
                        'config_type' => $configData['config_type'] ?? 'string',
                        'description' => $configData['description'] ?? null,
                        'is_active' => true
                    ]);
                } else {
                    $configuration->config_value = $configData['config_value'];
                    if (isset($configData['config_type'])) {
                        $configuration->config_type = $configData['config_type'];
                    }
                    if (isset($configData['description'])) {
                        $configuration->description = $configData['description'];
                    }
                    $configuration->save();
                }

                $updatedConfigurations[] = [
                    'id' => $configuration->id,
                    'config_key' => $configuration->config_key,
                    'config_value' => $configuration->typed_value,
                    'config_type' => $configuration->config_type,
                    'description' => $configuration->description,
                    'is_active' => $configuration->is_active,
                    'updated_at' => $configuration->updated_at
                ];
            }

            return response()->json([
                'success' => true,
                'message' => 'Configurations updated successfully',
                'data' => [
                    'rvm_id' => $rvm->id,
                    'rvm_name' => $rvm->name,
                    'configurations' => $updatedConfigurations
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update configurations: ' . $e->getMessage()
            ], 500);
        }
    }
}
