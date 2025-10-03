<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ReverseVendingMachine extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'location',
        'address',
        'latitude',
        'longitude',
        'status',
        'capacity',
        'current_load',
        'ip_address',
        'api_key',
        'api_key_expires_at',
        'last_api_access',
        'last_ping',
        'last_maintenance',
        'connection_status',
        'api_status',
        'last_connection_check',
        'last_api_check',
        'configuration',
        'metrics'
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'capacity' => 'integer',
        'current_load' => 'integer',
        'api_key_expires_at' => 'datetime',
        'last_api_access' => 'datetime',
        'last_ping' => 'datetime',
        'last_maintenance' => 'datetime',
        'last_connection_check' => 'datetime',
        'last_api_check' => 'datetime',
        'configuration' => 'array',
        'metrics' => 'array'
    ];

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeOnline($query)
    {
        return $query->where('last_ping', '>=', Carbon::now()->subMinutes(5));
    }

    public function scopeOffline($query)
    {
        return $query->where('last_ping', '<', Carbon::now()->subMinutes(5))
                    ->orWhereNull('last_ping');
    }

    // Accessors
    public function getIsOnlineAttribute()
    {
        if (!$this->last_ping) {
            return false;
        }
        
        return $this->last_ping->diffInMinutes(Carbon::now()) <= 5;
    }

    public function getCapacityPercentageAttribute()
    {
        if ($this->capacity <= 0) {
            return 0;
        }
        
        return round(($this->current_load / $this->capacity) * 100, 2);
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'active' => $this->is_online ? 'green' : 'yellow',
            'inactive' => 'gray',
            'maintenance' => 'orange',
            'error' => 'red',
            default => 'gray'
        };
    }

    // Methods
    public function updatePing()
    {
        $this->update(['last_ping' => Carbon::now()]);
    }

    public function updateMetrics(array $metrics)
    {
        $this->update([
            'metrics' => array_merge($this->metrics ?? [], $metrics),
            'last_ping' => Carbon::now()
        ]);
    }

    public function updateStatus(string $status)
    {
        $this->update([
            'status' => $status,
            'last_ping' => Carbon::now()
        ]);
    }

    public function generateApiKey()
    {
        $apiKey = bin2hex(random_bytes(32));
        $this->update([
            'api_key' => $apiKey,
            'api_key_expires_at' => now()->addMonth() // 1 bulan, bukan 1 tahun
        ]);
        return $apiKey;
    }

    public function detectionResults()
    {
        return $this->hasMany(DetectionResult::class);
    }

    // Status Management Methods
    
    /**
     * Update RVM status based on current_load
     */
    public function updateStatusBasedOnLoad()
    {
        // Don't update status for dummy RVMs (no IP address)
        if (!$this->ip_address) {
            return;
        }
        
        if ($this->current_load >= 0 && $this->current_load <= 100) {
            $this->update(['status' => 'active']);
        } elseif ($this->current_load > 100) {
            $this->update(['status' => 'inactive']);
            // TODO: Send notification for over-capacity
        }
    }

    /**
     * Check connection status by pinging RVM IP
     */
    public function checkConnectionStatus()
    {
        if (!$this->ip_address) {
            // Don't update dummy RVMs, just return false
            return false;
        }

        // Ping the RVM IP
        $pingResult = $this->pingHost($this->ip_address);
        
        $this->update([
            'connection_status' => $pingResult ? 'connected' : 'disconnected',
            'last_connection_check' => now()
        ]);

        return $pingResult;
    }

    /**
     * Check API status by calling health endpoint
     */
    public function checkApiStatus()
    {
        if (!$this->ip_address) {
            // Don't update dummy RVMs, just return false
            return false;
        }

        // Check API health endpoint
        $apiResult = $this->checkApiHealth();
        
        $this->update([
            'api_status' => $apiResult ? 'valid' : 'invalid',
            'last_api_check' => now()
        ]);

        return $apiResult;
    }

    /**
     * Ping a host to check connectivity
     */
    private function pingHost($host, $timeout = 3)
    {
        try {
            $output = [];
            $returnCode = 0;
            exec("ping -c 1 -W {$timeout} {$host}", $output, $returnCode);
            return $returnCode === 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Check API health endpoint
     */
    private function checkApiHealth()
    {
        try {
            $url = "http://{$this->ip_address}:5000/api/health";
            $context = stream_context_create([
                'http' => [
                    'timeout' => 5,
                    'method' => 'GET'
                ]
            ]);
            
            $response = @file_get_contents($url, false, $context);
            return $response !== false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get comprehensive status information
     */
    public function getComprehensiveStatus()
    {
        return [
            'status' => $this->status,
            'connection_status' => $this->connection_status,
            'api_status' => $this->api_status,
            'is_online' => $this->is_online,
            'is_api_valid' => $this->isApiKeyValid(),
            'last_connection_check' => $this->last_connection_check,
            'last_api_check' => $this->last_api_check,
            'capacity_percentage' => $this->capacity_percentage
        ];
    }

    public function recentDetections($limit = 10)
    {
        return $this->detectionResults()
            ->latest('detected_at')
            ->limit($limit);
    }

    public function isApiKeyValid()
    {
        return $this->api_key && 
               $this->api_key_expires_at && 
               $this->api_key_expires_at->isFuture();
    }
}