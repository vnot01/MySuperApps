<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Config;

class RvmStatusHelper
{
    /**
     * Calculate the status of an RVM based on capacity and special status
     *
     * @param float|null $capacity
     * @param string|null $specialStatus
     * @param string|null $baseStatus
     * @return string
     */
    public static function calculateStatus($capacity = null, $specialStatus = null, $baseStatus = null)
    {
        $config = Config::get('rvm_status');
        
        // Ensure config exists and has required structure
        if (!$config || !isset($config['calculation_rules'])) {
            return 'inactive'; // Default fallback status
        }
        
        // Check for special status override (highest priority)
        if (!empty($specialStatus) && self::isValidStatus($specialStatus)) {
            return $specialStatus;
        }
        
        // Check capacity-based rules
        if ($capacity !== null && isset($config['calculation_rules']['capacity_rules']['full_threshold'], $config['calculation_rules']['capacity_rules']['active_min'])) {
            $fullThreshold = $config['calculation_rules']['capacity_rules']['full_threshold'];
            $activeMin = $config['calculation_rules']['capacity_rules']['active_min'];
            
            if ($capacity >= $fullThreshold) {
                return 'full';
            } elseif ($capacity >= $activeMin) {
                return 'active';
            }
        }
        
        // Use base status if available and valid
        if (!empty($baseStatus) && self::isValidStatus($baseStatus)) {
            return $baseStatus;
        }
        
        // Return default status
        return $config['calculation_rules']['default_status'] ?? 'inactive';
    }

    /**
     * Get status configuration for a given status
     *
     * @param string $status
     * @return array|null
     */
    public static function getStatusConfig($status)
    {
        $statuses = Config::get('rvm_status.statuses');
        if (!$statuses || !is_array($statuses)) {
            return null;
        }
        return $statuses[$status] ?? null;
    }

    /**
     * Get all available statuses
     *
     * @return array
     */
    public static function getAllStatuses()
    {
        $statuses = Config::get('rvm_status.statuses');
        return $statuses ?? [];
    }

    /**
     * Check if a status is valid
     *
     * @param string $status
     * @return bool
     */
    public static function isValidStatus($status)
    {
        $statuses = Config::get('rvm_status.statuses');
        if (!$statuses || !is_array($statuses)) {
            return false;
        }
        return array_key_exists($status, $statuses);
    }

    /**
     * Get status badge HTML
     *
     * @param string $status
     * @param string $template
     * @return string
     */
    public static function getStatusBadge($status, $template = 'badge')
    {
        $statusConfig = self::getStatusConfig($status);
        if (!$statusConfig) {
            return '';
        }

        $templateConfig = Config::get("rvm_status.templates.{$template}");
        if (!$templateConfig) {
            $templateConfig = Config::get('rvm_status.templates.badge');
        }
        
        // Fallback if template config is still null
        if (!$templateConfig) {
            return '<span class="badge bg-secondary">' . ($statusConfig['label'] ?? $status) . '</span>';
        }

        $classPrefix = $templateConfig['class_prefix'] ?? '';
        $iconPrefix = $templateConfig['icon_prefix'] ?? '';
        $showIcon = $templateConfig['show_icon'] ?? false;
        $showLabel = $templateConfig['show_label'] ?? true;
        $showDescription = $templateConfig['show_description'] ?? false;
        
        $class = $classPrefix . ($statusConfig['class'] ?? '');
        $icon = $showIcon ? '<i class="' . $iconPrefix . ($statusConfig['icon'] ?? '') . '"></i> ' : '';
        $label = $showLabel ? ($statusConfig['label'] ?? $status) : '';
        $description = $showDescription ? ' - ' . ($statusConfig['description'] ?? '') : '';

        return '<span class="' . $class . '">' . $icon . $label . $description . '</span>';
    }

    /**
     * Get status for JavaScript (JSON format)
     *
     * @param string $status
     * @return array
     */
    public static function getStatusForJs($status)
    {
        $statusConfig = self::getStatusConfig($status);
        if (!$statusConfig) {
            return [];
        }

        return [
            'status' => $status,
            'label' => $statusConfig['label'],
            'class' => $statusConfig['class'],
            'icon' => $statusConfig['icon'],
            'color' => $statusConfig['color'],
            'description' => $statusConfig['description'],
            'priority' => $statusConfig['priority'],
        ];
    }

    /**
     * Get connection status configuration
     *
     * @param string $connectionStatus
     * @return array|null
     */
    public static function getConnectionStatusConfig($connectionStatus)
    {
        $statuses = Config::get('rvm_status.connection_statuses');
        return $statuses[$connectionStatus] ?? null;
    }

    /**
     * Get comprehensive status data for an RVM.
     *
     * @param \App\Models\ReverseVendingMachine $rvm The RVM model instance.
     * @return array An array containing all relevant status information.
     */
    public static function getStatusData($rvm)
    {
        // Calculate the definitive status based on various factors.
        $calculatedStatus = self::calculateStatus($rvm->capacity, $rvm->special_status, $rvm->status);

        // Retrieve the full configuration for the calculated status.
        $statusConfig = self::getStatusForJs($calculatedStatus);

        // Return a merged array with both the status string and its configuration.
        return array_merge($statusConfig, ['status' => $calculatedStatus]);
    }

    /**
     * Get connection status badge HTML
     *
     * @param string $connectionStatus
     * @return string
     */
    public static function getConnectionStatusBadge($connectionStatus)
    {
        $config = self::getConnectionStatusConfig($connectionStatus);
        if (!$config) {
            return '';
        }

        $class = 'badge bg-' . $config['class'];
        $icon = '<i class="fas fa-' . $config['icon'] . '"></i> ';
        
        return '<span class="' . $class . '">' . $icon . $config['label'] . '</span>';
    }

    /**
     * Get connection pulse indicator HTML
     *
     * @param string $connectionStatus
     * @return string
     */
    public static function getConnectionPulse($connectionStatus)
    {
        $config = self::getConnectionStatusConfig($connectionStatus);
        if (!$config) {
            return '';
        }

        return '<span class="pulse-indicator ' . $config['pulse_class'] . '"></span>';
    }

    /**
     * Get statistics configuration
     *
     * @param string $type
     * @return array
     */
    public static function getStatisticsConfig($type)
    {
        return Config::get("rvm_status.statistics.{$type}", []);
    }

    /**
     * Check if status requires attention
     *
     * @param string $status
     * @return bool
     */
    public static function requiresAttention($status)
    {
        $attentionStatuses = self::getStatisticsConfig('attention_statuses');
        return in_array($status, $attentionStatuses);
    }

    /**
     * Check if status is operational
     *
     * @param string $status
     * @return bool
     */
    public static function isOperational($status)
    {
        $operationalStatuses = self::getStatisticsConfig('operational_statuses');
        return in_array($status, $operationalStatuses);
    }

    /**
     * Check if status is critical
     *
     * @param string $status
     * @return bool
     */
    public static function isCritical($status)
    {
        $criticalStatuses = self::getStatisticsConfig('critical_statuses');
        return in_array($status, $criticalStatuses);
    }

    /**
     * Get status priority for sorting
     *
     * @param string $status
     * @return int
     */
    public static function getStatusPriority($status)
    {
        $statusConfig = self::getStatusConfig($status);
        return $statusConfig['priority'] ?? 999;
    }

    /**
     * Sort RVMs by status priority
     *
     * @param array $rvms
     * @param string $statusField
     * @return array
     */
    public static function sortByStatusPriority($rvms, $statusField = 'calculated_status')
    {
        usort($rvms, function ($a, $b) use ($statusField) {
            $priorityA = self::getStatusPriority($a[$statusField] ?? 'unknown');
            $priorityB = self::getStatusPriority($b[$statusField] ?? 'unknown');
            return $priorityA <=> $priorityB;
        });

        return $rvms;
    }

    /**
     * Get all status configurations for JavaScript
     *
     * @return array
     */
    public static function getAllStatusesForJs()
    {
        $statuses = self::getAllStatuses();
        $result = [];

        foreach ($statuses as $key => $config) {
            $result[$key] = [
                'label' => $config['label'],
                'class' => $config['class'],
                'icon' => $config['icon'],
                'color' => $config['color'],
                'description' => $config['description'],
                'priority' => $config['priority'],
            ];
        }

        return $result;
    }

    /**
     * Get all connection statuses for JavaScript
     *
     * @return array
     */
    public static function getAllConnectionStatusesForJs()
    {
        $statuses = Config::get('rvm_status.connection_statuses');
        $result = [];

        if (!is_array($statuses)) {
            return $result;
        }

        foreach ($statuses as $key => $config) {
            $result[$key] = [
                'label' => $config['label'],
                'class' => $config['class'],
                'icon' => $config['icon'],
                'color' => $config['color'],
                'pulse_class' => $config['pulse_class'],
            ];
        }

        return $result;
    }

    /**
     * Get all status configurations
     *
     * @return array
     */
    public static function getAllStatusConfigs()
    {
        return Config::get('rvm_status.statuses');
    }

    /**
     * Get all connection status configurations
     *
     * @return array
     */
    public static function getAllConnectionStatusConfigs()
    {
        return Config::get('rvm_status.connection_statuses');
    }

    /**
     * Calculate the number of items needing attention for a specific RVM.
     *
     * @param \App\Models\ReverseVendingMachine $rvm The RVM model instance.
     * @return int The number of items needing attention.
     */
    public static function getAttentionItemsCount($rvm)
    {
        $attentionCount = 0;
        $statusData = self::getStatusData($rvm);
        $attentionStatuses = self::getStatisticsConfig('attention_statuses');

        if (in_array($statusData['status'], $attentionStatuses)) {
            $attentionCount++;
        }
        if (empty($rvm->timezone)) {
            $attentionCount++;
        }
        if (empty($rvm->ip_address) || $rvm->ip_address === '0.0.0.0') {
            $attentionCount++;
        }
        if ($rvm->connection_status !== 'connected') {
            $attentionCount++;
        }

        return $attentionCount;
    }
}