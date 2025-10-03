<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReverseVendingMachine;
use App\Models\SoftwareUpdate;
use App\Models\AiModel;
use App\Models\ConfigurationTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class OTAManagementController extends Controller
{
    public function index($id)
    {
        try {
            $rvm = ReverseVendingMachine::findOrFail($id);
            
            // Get current software version
            $currentSoftware = SoftwareUpdate::where('rvm_id', $id)
                ->where('update_type', 'software')
                ->where('status', 'completed')
                ->orderBy('completed_at', 'desc')
                ->first();
            
            // Get current AI model
            $currentModel = AiModel::where('rvm_id', $id)
                ->where('is_active', true)
                ->first();
            
            // Get recent updates
            $recentUpdates = SoftwareUpdate::where('rvm_id', $id)
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'rvm_id' => $rvm->id,
                    'rvm_name' => $rvm->name,
                    'current_software' => $currentSoftware,
                    'current_model' => $currentModel,
                    'recent_updates' => $recentUpdates
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get OTA information: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function checkForUpdates($id)
    {
        try {
            $rvm = ReverseVendingMachine::findOrFail($id);
            
            // Check GitHub for latest releases
            $githubReleases = $this->getGitHubReleases();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'rvm_id' => $rvm->id,
                    'software_updates' => $githubReleases,
                    'model_updates' => [],
                    'config_updates' => []
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to check for updates: ' . $e->getMessage()
            ], 500);
        }
    }
    
    private function getGitHubReleases()
    {
        try {
            $response = Http::get('https://api.github.com/repos/vnot01/MySuperApps/releases');
            
            if ($response->successful()) {
                return $response->json();
            }
            
            return [];
        } catch (\Exception $e) {
            Log::error('Failed to fetch GitHub releases', ['error' => $e->getMessage()]);
            return [];
        }
    }
}
