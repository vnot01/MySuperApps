# Timezone Configuration Guide

## Overview

This document explains how to configure and use timezone settings in the MyRVM Platform application. The system supports configurable timezones with default settings for Indonesia (WIB/UTC+7).

## Environment Configuration

### .env File Settings

Add the following configuration to your `.env` file:

```env
# Timezone and Locale Configuration
APP_TIMEZONE=Asia/Jakarta
APP_DATE_FORMAT=Y-m-d
APP_TIME_FORMAT=H:i:s
APP_DATETIME_FORMAT=Y-m-d H:i:s
APP_DISPLAY_TIMEZONE=WIB
```

### Configuration Options

| Variable | Default | Description |
|----------|---------|-------------|
| `APP_TIMEZONE` | `Asia/Jakarta` | PHP timezone identifier |
| `APP_DATE_FORMAT` | `Y-m-d` | Date format (PHP date format) |
| `APP_TIME_FORMAT` | `H:i:s` | Time format (PHP date format) |
| `APP_DATETIME_FORMAT` | `Y-m-d H:i:s` | DateTime format (PHP date format) |
| `APP_DISPLAY_TIMEZONE` | `WIB` | Display timezone abbreviation |

## Supported Timezones

### Indonesian Timezones
- `Asia/Jakarta` - WIB (UTC+7) - Western Indonesian Time
- `Asia/Makassar` - WITA (UTC+8) - Central Indonesian Time  
- `Asia/Jayapura` - WIT (UTC+9) - Eastern Indonesian Time

### Other Common Timezones
- `UTC` - Coordinated Universal Time
- `America/New_York` - Eastern Time (ET)
- `Europe/London` - Greenwich Mean Time (GMT)
- `Asia/Tokyo` - Japan Standard Time (JST)
- `Asia/Shanghai` - China Standard Time (CST)

## Usage Examples

### PHP (Laravel Backend)

```php
use App\Helpers\TimezoneHelper;

// Get current datetime with configured timezone
$now = TimezoneHelper::now();

// Format datetime
$formatted = TimezoneHelper::formatDateTime();
// Output: 2024-01-15 14:30:25 WIB

// Format time only
$time = TimezoneHelper::formatTime();
// Output: 14:30:25

// Format date only
$date = TimezoneHelper::formatDate();
// Output: 2024-01-15

// Convert existing datetime to configured timezone
$converted = TimezoneHelper::convertToTimezone('2024-01-15 10:00:00');
```

### JavaScript (Frontend)

```javascript
// Configuration is automatically loaded from Laravel
const config = {
    timezone: 'Asia/Jakarta',
    dateFormat: 'Y-m-d',
    timeFormat: 'H:i:s',
    datetimeFormat: 'Y-m-d H:i:s',
    displayTimezone: 'WIB'
};

// Format datetime with timezone
const formatted = formatDateTime(new Date());
// Output: 01/15/2024, 14:30:25 WIB

// Format time only
const time = formatTime(new Date());
// Output: 2:30:25 PM

// Format date only
const date = formatDate(new Date());
// Output: 01/15/2024

// Get current time
const currentTime = getCurrentTime();
// Output: 2:30:25 PM
```

## Implementation Details

### Backend (PHP/Laravel)

1. **TimezoneHelper Class**: Located at `app/Helpers/TimezoneHelper.php`
   - Provides static methods for timezone operations
   - Uses Carbon for date manipulation
   - Automatically applies configured timezone

2. **Configuration File**: Located at `config/timezone.php`
   - Contains timezone settings and supported timezones
   - Provides validation and dropdown options

### Frontend (JavaScript)

1. **Configuration**: Automatically loaded from Laravel environment
2. **Functions**: Available in dashboard JavaScript
   - `formatDateTime(date, format)`
   - `formatTime(date)`
   - `formatDate(date)`
   - `getCurrentTime()`
   - `getCurrentDate()`
   - `getCurrentDateTime()`

## Dashboard Integration

### Features Using Timezone

1. **Last Updated Timestamp**: Shows current time with timezone
2. **RVM Last Seen**: Displays last activity time with timezone
3. **Remote Control Interface**: Shows current time in remote control
4. **Status Updates**: Timestamps for status changes
5. **Mock Data**: Generated with timezone-aware timestamps

### Visual Indicators

- Timezone abbreviation shown in parentheses: `(WIB)`
- Consistent formatting across all time displays
- Real-time updates with correct timezone

## Best Practices

1. **Always use helper functions** instead of direct date formatting
2. **Test with different timezones** to ensure consistency
3. **Use environment variables** for timezone configuration
4. **Document timezone changes** in deployment notes
5. **Consider user location** when setting default timezone

## Troubleshooting

### Common Issues

1. **Wrong timezone displayed**: Check `APP_TIMEZONE` in `.env`
2. **Format not applied**: Verify `APP_*_FORMAT` variables
3. **JavaScript errors**: Ensure timezone functions are loaded
4. **Inconsistent times**: Clear browser cache and reload

### Debug Commands

```bash
# Check current timezone in Laravel
php artisan tinker
>>> TimezoneHelper::getTimezoneInfo()

# Check environment variables
grep TIMEZONE .env
```

## Migration Guide

### From Default to Custom Timezone

1. Update `.env` file with desired timezone
2. Clear application cache: `php artisan config:clear`
3. Restart web server
4. Test dashboard functionality
5. Verify all timestamps display correctly

### From UTC to Indonesia Timezone

1. Set `APP_TIMEZONE=Asia/Jakarta`
2. Set `APP_DISPLAY_TIMEZONE=WIB`
3. Update any hardcoded timezone references
4. Test with existing data
5. Update documentation

## Security Considerations

1. **Input validation**: Always validate timezone input
2. **SQL injection**: Use parameterized queries for date operations
3. **XSS prevention**: Escape timezone data in HTML output
4. **CSRF protection**: Include CSRF tokens in timezone-related forms

## Performance Notes

1. **Caching**: Timezone calculations are cached where possible
2. **Database**: Store dates in UTC, convert for display
3. **JavaScript**: Timezone functions are optimized for performance
4. **Memory**: Minimal memory overhead for timezone operations
