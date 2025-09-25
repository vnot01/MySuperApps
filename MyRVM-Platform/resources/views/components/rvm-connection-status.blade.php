@php
use App\Helpers\RvmStatusHelper;

$connectionConfig = RvmStatusHelper::getConnectionStatusConfig($connectionStatus);

if (!$connectionConfig) {
    $connectionConfig = RvmStatusHelper::getConnectionStatusConfig('unknown');
}

$badgeClass = 'badge bg-' . $connectionConfig['class'];
$iconClass = 'fas fa-' . $connectionConfig['icon'];
$pulseClass = 'pulse-indicator ' . $connectionConfig['pulse_class'];

$showPulse = $showPulse ?? false;
$showBadge = $showBadge ?? true;
$additionalClasses = $class ?? '';
@endphp

<div class="connection-status-wrapper {{ $additionalClasses }}">
    @if($showPulse)
        <span class="{{ $pulseClass }}" 
              title="{{ $connectionConfig['label'] }} Connection"></span>
    @endif
    
    @if($showBadge)
        <span class="{{ $badgeClass }}" 
              title="{{ $connectionConfig['label'] }} Connection"
              data-connection-status="{{ $connectionStatus }}">
            <i class="{{ $iconClass }}"></i>
            {{ $connectionConfig['label'] }}
        </span>
    @endif
</div>