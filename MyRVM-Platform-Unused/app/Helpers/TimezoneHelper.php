<?php

namespace App\Helpers;

use Carbon\Carbon;

class TimezoneHelper
{
    /**
     * Get the configured timezone from environment
     */
    public static function getTimezone(): string
    {
        return env('APP_TIMEZONE', 'Asia/Jakarta');
    }

    /**
     * Get the display timezone from environment
     */
    public static function getDisplayTimezone(): string
    {
        return env('APP_DISPLAY_TIMEZONE', 'WIB');
    }

    /**
     * Format datetime with configured timezone
     */
    public static function formatDateTime($date = null, string $format = null): string
    {
        $date = $date ? Carbon::parse($date) : Carbon::now();
        $format = $format ?: env('APP_DATETIME_FORMAT', 'Y-m-d H:i:s');
        
        return $date->setTimezone(self::getTimezone())->format($format);
    }

    /**
     * Format time with configured timezone
     */
    public static function formatTime($date = null, string $format = null): string
    {
        $date = $date ? Carbon::parse($date) : Carbon::now();
        $format = $format ?: env('APP_TIME_FORMAT', 'H:i:s');
        
        return $date->setTimezone(self::getTimezone())->format($format);
    }

    /**
     * Format date with configured timezone
     */
    public static function formatDate($date = null, string $format = null): string
    {
        $date = $date ? Carbon::parse($date) : Carbon::now();
        $format = $format ?: env('APP_DATE_FORMAT', 'Y-m-d');
        
        return $date->setTimezone(self::getTimezone())->format($format);
    }

    /**
     * Get current datetime with timezone
     */
    public static function now(): Carbon
    {
        return Carbon::now(self::getTimezone());
    }

    /**
     * Convert datetime to configured timezone
     */
    public static function convertToTimezone($date, string $timezone = null): Carbon
    {
        $timezone = $timezone ?: self::getTimezone();
        return Carbon::parse($date)->setTimezone($timezone);
    }

    /**
     * Get timezone offset in hours
     */
    public static function getTimezoneOffset(): int
    {
        $timezone = self::getTimezone();
        $now = Carbon::now($timezone);
        return $now->offsetHours;
    }

    /**
     * Get timezone info for JavaScript
     */
    public static function getTimezoneInfo(): array
    {
        return [
            'timezone' => self::getTimezone(),
            'display_timezone' => self::getDisplayTimezone(),
            'offset_hours' => self::getTimezoneOffset(),
            'date_format' => env('APP_DATE_FORMAT', 'Y-m-d'),
            'time_format' => env('APP_TIME_FORMAT', 'H:i:s'),
            'datetime_format' => env('APP_DATETIME_FORMAT', 'Y-m-d H:i:s'),
        ];
    }
}
