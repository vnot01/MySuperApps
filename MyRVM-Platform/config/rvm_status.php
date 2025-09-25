<?php

return [
    /*
    |--------------------------------------------------------------------------
    | RVM Status Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the centralized configuration for RVM status
    | definitions, including display properties, logic rules, and priorities.
    | This ensures consistency across Dashboard and RVM Management pages.
    |
    */

    'statuses' => [
        'active' => [
            'label' => 'Active',
            'class' => 'success',
            'icon' => 'check',
            'color' => '#28a745',
            'description' => 'RVM is operational and accepting deposits',
            'priority' => 1,
        ],
        'inactive' => [
            'label' => 'Inactive',
            'class' => 'secondary',
            'icon' => 'times',
            'color' => '#6c757d',
            'description' => 'RVM is offline or not operational',
            'priority' => 4,
        ],
        'maintenance' => [
            'label' => 'Maintenance',
            'class' => 'warning',
            'icon' => 'wrench',
            'color' => '#ffc107',
            'description' => 'RVM is under maintenance',
            'priority' => 3,
        ],
        'error' => [
            'label' => 'Error',
            'class' => 'danger',
            'icon' => 'exclamation-triangle',
            'color' => '#dc3545',
            'description' => 'RVM has encountered an error',
            'priority' => 5,
        ],
        'full' => [
            'label' => 'Full',
            'class' => 'danger',
            'icon' => 'exclamation-triangle',
            'color' => '#dc3545',
            'description' => 'RVM storage is full',
            'priority' => 2,
        ],
        'unknown' => [
            'label' => 'Unknown',
            'class' => 'primary',
            'icon' => 'question-circle',
            'color' => '#696cff',
            'description' => 'RVM status cannot be determined',
            'priority' => 6,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Status Calculation Rules
    |--------------------------------------------------------------------------
    |
    | These rules define how the calculated_status is determined based on
    | capacity and special_status values.
    |
    */

    'calculation_rules' => [
        // Priority order for special_status override
        'special_status_priority' => [
            'error',
            'maintenance', 
            'inactive',
            'unknown'
        ],
        
        // Capacity-based status rules
        'capacity_rules' => [
            'full_threshold' => 100,    // >= 100% = full
            'active_min' => 0,          // >= 0% and < 100% = active
        ],
        
        // Default status when no rules match
        'default_status' => 'inactive',
    ],

    /*
    |--------------------------------------------------------------------------
    | Display Templates
    |--------------------------------------------------------------------------
    |
    | Templates for different display contexts (compact vs detailed)
    |
    */

    'templates' => [
        'badge' => [
            'class_prefix' => 'badge bg-',
            'icon_prefix' => 'fas fa-',
            'show_icon' => true,
            'show_label' => true,
        ],
        'card' => [
            'class_prefix' => 'text-',
            'icon_prefix' => 'fas fa-',
            'show_icon' => true,
            'show_label' => true,
            'show_description' => false,
        ],
        'detailed' => [
            'class_prefix' => 'status-',
            'icon_prefix' => 'fas fa-',
            'show_icon' => true,
            'show_label' => true,
            'show_description' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Connection Status Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for RVM connection status indicators
    |
    */

    'connection_statuses' => [
        'connected' => [
            'label' => 'Connected',
            'class' => 'success',
            'icon' => 'wifi',
            'color' => '#28a745',
            'pulse_class' => 'connected',
        ],
        'disconnected' => [
            'label' => 'Disconnected',
            'class' => 'danger',
            'icon' => 'wifi-slash',
            'color' => '#dc3545',
            'pulse_class' => 'disconnected',
        ],
        'local' => [
            'label' => 'Local',
            'class' => 'info',
            'icon' => 'home',
            'color' => '#17a2b8',
            'pulse_class' => 'local',
        ],
        'unknown' => [
            'label' => 'Unknown',
            'class' => 'secondary',
            'icon' => 'question',
            'color' => '#6c757d',
            'pulse_class' => 'unknown',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Statistics Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for dashboard statistics and trends
    |
    */

    'statistics' => [
        'active_statuses' => ['active'],
        'attention_statuses' => ['error', 'maintenance', 'inactive', 'full'],
        'operational_statuses' => ['active', 'maintenance'],
        'critical_statuses' => ['error', 'full'],
    ],
];