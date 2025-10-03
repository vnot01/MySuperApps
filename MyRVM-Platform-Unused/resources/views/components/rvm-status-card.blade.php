@php
use App\Helpers\RvmStatusHelper;

$statusConfig = RvmStatusHelper::getStatusConfig($status);
$connectionConfig = RvmStatusHelper::getConnectionStatusConfig($connectionStatus ?? 'unknown');

if (!$statusConfig) {
    $statusConfig = RvmStatusHelper::getStatusConfig('unknown');
}

$cardClass = $cardClass ?? 'card';
$headerClass = 'card-header bg-' . $statusConfig['class'];
$iconClass = 'fas fa-' . $statusConfig['icon'];
$showCapacity = $showCapacity ?? true;
$showConnection = $showConnection ?? true;
$showActions = $showActions ?? false;
@endphp

<div class="{{ $cardClass }}" data-rvm-id="{{ $rvmId ?? '' }}" data-status="{{ $status }}">
    <div class="{{ $headerClass }} text-white">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <i class="{{ $iconClass }}"></i>
                <strong>{{ $name ?? 'Unknown RVM' }}</strong>
            </div>
            <div>
                <x-rvm-status-badge :status="$status" template="badge" class="badge-light" />
            </div>
        </div>
    </div>
    
    <div class="card-body">
        <div class="row">
            <div class="col-md-8">
                <p class="mb-1">
                    <i class="fas fa-map-marker-alt text-muted"></i>
                    <strong>Location:</strong> {{ $location ?? 'Not Set' }}
                </p>
                
                @if($showCapacity && isset($capacity))
                    <p class="mb-1">
                        <i class="fas fa-battery-half text-muted"></i>
                        <strong>Capacity:</strong> {{ $capacity }}%
                        <div class="progress mt-1" style="height: 6px;">
                            <div class="progress-bar bg-{{ $statusConfig['class'] }}" 
                                 role="progressbar" 
                                 style="width: {{ min($capacity, 100) }}%"
                                 aria-valuenow="{{ $capacity }}" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                            </div>
                        </div>
                    </p>
                @endif
                
                @if(isset($lastSeen))
                    <p class="mb-1">
                        <i class="fas fa-clock text-muted"></i>
                        <strong>Last Seen:</strong> {{ $lastSeen }}
                    </p>
                @endif
                
                @if(isset($ipAddress))
                    <p class="mb-1">
                        <i class="fas fa-network-wired text-muted"></i>
                        <strong>IP Address:</strong> {{ $ipAddress }}
                    </p>
                @endif
            </div>
            
            <div class="col-md-4 text-end">
                @if($showConnection)
                    <x-rvm-connection-status 
                        :connection-status="$connectionStatus ?? 'unknown'" 
                        :show-pulse="true" 
                        class="mb-2" />
                @endif
                
                @if($showActions)
                    <div class="btn-group-vertical btn-group-sm">
                        <button class="btn btn-outline-primary btn-sm" 
                                onclick="viewRvmDetails({{ $rvmId ?? 0 }})">
                            <i class="fas fa-eye"></i> View
                        </button>
                        <button class="btn btn-outline-warning btn-sm" 
                                onclick="toggleMaintenance({{ $rvmId ?? 0 }})">
                            <i class="fas fa-wrench"></i> Maintenance
                        </button>
                    </div>
                @endif
            </div>
        </div>
        
        @if(isset($description))
            <div class="mt-2">
                <small class="text-muted">{{ $description }}</small>
            </div>
        @endif
    </div>
</div>