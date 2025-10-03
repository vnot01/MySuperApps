<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Timezone
    |--------------------------------------------------------------------------
    |
    | This option controls the default timezone that will be used by the
    | application. You may set this to any of the timezones which will be
    | supported by your application.
    |
    */
    'default' => env('APP_TIMEZONE', 'Asia/Jakarta'),

    /*
    |--------------------------------------------------------------------------
    | Display Timezone
    |--------------------------------------------------------------------------
    |
    | This option controls the display timezone that will be shown to users.
    | This is typically the same as the default timezone but can be different
    | for display purposes.
    |
    */
    'display' => env('APP_DISPLAY_TIMEZONE', 'WIB'),

    /*
    |--------------------------------------------------------------------------
    | Date Format
    |--------------------------------------------------------------------------
    |
    | This option controls the default date format used throughout the
    | application.
    |
    */
    'date_format' => env('APP_DATE_FORMAT', 'Y-m-d'),

    /*
    |--------------------------------------------------------------------------
    | Time Format
    |--------------------------------------------------------------------------
    |
    | This option controls the default time format used throughout the
    | application.
    |
    */
    'time_format' => env('APP_TIME_FORMAT', 'H:i:s'),

    /*
    |--------------------------------------------------------------------------
    | DateTime Format
    |--------------------------------------------------------------------------
    |
    | This option controls the default datetime format used throughout the
    | application.
    |
    */
    'datetime_format' => env('APP_DATETIME_FORMAT', 'Y-m-d H:i:s'),

    /*
    |--------------------------------------------------------------------------
    | Supported Timezones
    |--------------------------------------------------------------------------
    |
    | This option contains a list of supported timezones for the application.
    | This can be used for validation or dropdown selections.
    |
    */
    'supported_timezones' => [
        'Asia/Jakarta' => 'Western Indonesian Time (WIB)',
        'Asia/Makassar' => 'Central Indonesian Time (WITA)',
        'Asia/Jayapura' => 'Eastern Indonesian Time (WIT)',
        'UTC' => 'Coordinated Universal Time (UTC)',
        'America/New_York' => 'Eastern Time (ET)',
        'Europe/London' => 'Greenwich Mean Time (GMT)',
        'Asia/Tokyo' => 'Japan Standard Time (JST)',
        'Asia/Shanghai' => 'China Standard Time (CST)',
    ],

    /*
    |--------------------------------------------------------------------------
    | Indonesian Timezones
    |--------------------------------------------------------------------------
    |
    | This option contains Indonesian timezones for easy reference.
    |
    */
    'indonesian_timezones' => [
        'Asia/Jakarta' => 'WIB (UTC+7)',
        'Asia/Makassar' => 'WITA (UTC+8)',
        'Asia/Jayapura' => 'WIT (UTC+9)',
    ],
];
