@php
use App\Helpers\RvmStatusHelper;

$statusConfig = RvmStatusHelper::getStatusConfig($status);
$template = $template ?? 'badge';
$templateConfig = config("rvm_status.templates.{$template}");

if (!$statusConfig || !$templateConfig) {
    $statusConfig = RvmStatusHelper::getStatusConfig('unknown');
    $templateConfig = config('rvm_status.templates.badge');
}

$badgeClass = $templateConfig['class_prefix'] . $statusConfig['class'];
$iconClass = $templateConfig['icon_prefix'] . $statusConfig['icon'];
$showIcon = $templateConfig['show_icon'] ?? true;
$showLabel = $templateConfig['show_label'] ?? true;
$showDescription = $templateConfig['show_description'] ?? false;

$additionalClasses = $class ?? '';
@endphp

<span class="{{ $badgeClass }} {{ $additionalClasses }}" 
      title="{{ $statusConfig['description'] }}"
      data-status="{{ $status }}"
      data-priority="{{ $statusConfig['priority'] }}">
    @if($showIcon)
        <i class="{{ $iconClass }}"></i>
    @endif
    @if($showLabel)
        {{ $statusConfig['label'] }}
    @endif
    @if($showDescription)
        - {{ $statusConfig['description'] }}
    @endif
</span>