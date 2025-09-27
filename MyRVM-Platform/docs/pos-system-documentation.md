# POS System untuk RVM UI - Dokumentasi

## Overview
POS (Point of Sale) System untuk RVM UI adalah sistem yang memungkinkan admin, operator, dan teknisi untuk mengontrol dan memantau RVM secara remote dengan fitur keamanan yang ketat.

## Fitur Utama

### 1. Remote Access Control
- **Admin Access**: Super Admin, Admin, Operator, dan Teknisi dapat mengakses RVM UI secara remote
- **Role-based Access**: Setiap role memiliki level akses yang berbeda
- **Token-based Security**: Menggunakan access token yang aman dengan expiration time

### 2. Security Authentication
- **PIN Authentication**: Wajib memasukkan PIN untuk mengakses RVM UI
- **RVM-specific PIN**: Setiap RVM dapat memiliki PIN khusus
- **Role-based PIN**: Fallback ke PIN berdasarkan role user
- **Access Logging**: Semua percobaan akses dicatat untuk audit

### 3. RVM Status Monitoring
- **Real-time Status**: Monitoring status RVM secara real-time
- **Status Types**: Active, Inactive, Maintenance, Full, Error, Unknown
- **Dashboard Overview**: Tampilan dashboard dengan statistik lengkap
- **Auto-refresh**: Data diperbarui otomatis setiap 30 detik

### 4. Remote Control
- **Status Update**: Admin dapat mengubah status RVM dari dashboard
- **Bulk Operations**: Update status multiple RVM sekaligus
- **Settings Management**: Konfigurasi pengaturan RVM
- **Export Data**: Export data monitoring dalam format JSON

## Cara Penggunaan

### 1. Akses Admin Dashboard
```
URL: /admin/rvm-dashboard
Method: GET
Authentication: Required (Bearer Token)
```

### 2. Remote Access ke RVM
```javascript
// Step 1: Request remote access
POST /api/v2/admin/rvm/{rvmId}/remote-access
{
    "access_pin": "1234"
}

// Step 2: Buka URL yang dikembalikan
GET /admin/rvm/{rvmId}/remote/{token}
```

### 3. Update RVM Status
```javascript
POST /api/v2/admin/rvm/{rvmId}/status
{
    "status": "maintenance"
}
```

### 4. Update RVM Settings
```javascript
PUT /api/v2/admin/rvm/{rvmId}/settings
{
    "admin_access_pin": "5678",
    "remote_access_enabled": true,
    "kiosk_mode_enabled": true,
    "pos_settings": {
        "auto_fullscreen": true,
        "disable_shortcuts": true
    }
}
```

## PIN Configuration

### Default PIN berdasarkan Role:
- **Super Admin**: 0000
- **Admin**: 1234
- **Operator**: 5678
- **Technician**: 9999

### RVM-specific PIN:
Setiap RVM dapat memiliki PIN khusus yang dapat dikonfigurasi melalui admin dashboard.

## Kiosk Mode Features

### Security Features:
- **Fullscreen Mode**: Otomatis masuk fullscreen
- **Disabled Shortcuts**: Menonaktifkan shortcut browser (F12, Ctrl+Shift+I, dll)
- **Exit Protection**: Keluar dari kiosk mode memerlukan PIN admin
- **No Context Menu**: Menonaktifkan right-click menu

### Keyboard Shortcuts:
- **Ctrl+Alt+E**: Tampilkan tombol exit (admin only)
- **F11**: Toggle fullscreen
- **F12**: Disabled (kiosk mode)
- **Ctrl+Shift+I**: Disabled (kiosk mode)

## API Endpoints

### Admin RVM Control
```
GET    /api/v2/admin/rvm/list                    - List semua RVM
GET    /api/v2/admin/rvm/monitoring              - Data monitoring dashboard
GET    /api/v2/admin/rvm/{rvmId}/details         - Detail RVM
POST   /api/v2/admin/rvm/{rvmId}/remote-access   - Request remote access
POST   /api/v2/admin/rvm/{rvmId}/status          - Update status RVM
PUT    /api/v2/admin/rvm/{rvmId}/settings        - Update settings RVM
```

### Web Routes
```
GET    /admin/rvm-dashboard                      - Admin dashboard
GET    /admin/rvm/{rvm}/remote/{token}           - Remote RVM UI
```

## Database Schema

### New Fields di `reverse_vending_machines`:
```sql
admin_access_pin VARCHAR(8) NULL
remote_access_enabled BOOLEAN DEFAULT TRUE
kiosk_mode_enabled BOOLEAN DEFAULT TRUE
pos_settings JSON NULL
last_status_change TIMESTAMP NULL
```

## Security Considerations

### 1. Access Control
- Semua endpoint memerlukan authentication
- PIN verification untuk remote access
- Token expiration (2 jam)
- Access logging untuk audit

### 2. Kiosk Mode Security
- Disabled browser shortcuts
- Fullscreen enforcement
- Exit protection dengan PIN
- No context menu

### 3. Network Security
- HTTPS recommended untuk production
- Token-based authentication
- CORS configuration
- Rate limiting (recommended)

## Installation & Setup

### 1. Run Migration
```bash
docker compose exec app php artisan migrate
```

### 2. Configure RVM Settings
```php
// Set default settings untuk RVM
$rvm = ReverseVendingMachine::find(1);
$rvm->update([
    'admin_access_pin' => '1234',
    'remote_access_enabled' => true,
    'kiosk_mode_enabled' => true,
    'pos_settings' => [
        'auto_fullscreen' => true,
        'disable_shortcuts' => true,
        'session_timeout' => 3600
    ]
]);
```

### 3. Test Remote Access
```bash
# Test API endpoint
curl -X POST http://localhost/api/v2/admin/rvm/1/remote-access \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{"access_pin": "1234"}'
```

## Troubleshooting

### Common Issues:

1. **Remote Access Failed**
   - Check PIN configuration
   - Verify user role permissions
   - Check RVM remote_access_enabled setting

2. **Kiosk Mode Not Working**
   - Check kiosk_mode_enabled setting
   - Verify browser fullscreen permissions
   - Check JavaScript console for errors

3. **Status Not Updating**
   - Check WebSocket connection
   - Verify API endpoint accessibility
   - Check authentication token

### Debug Mode:
```javascript
// Enable debug logging
localStorage.setItem('debug', 'true');
```

## Best Practices

### 1. Security
- Gunakan PIN yang kuat (minimal 6 digit)
- Rotate PIN secara berkala
- Monitor access logs
- Gunakan HTTPS di production

### 2. Performance
- Set appropriate refresh intervals
- Monitor WebSocket connections
- Optimize dashboard queries
- Use caching untuk static data

### 3. User Experience
- Provide clear error messages
- Implement loading states
- Use consistent UI patterns
- Test pada berbagai browser

## Future Enhancements

### Planned Features:
1. **Multi-language Support**
2. **Advanced Analytics**
3. **Mobile App Integration**
4. **Push Notifications**
5. **Advanced Security (2FA)**
6. **Custom Themes**
7. **API Rate Limiting**
8. **Audit Trail Dashboard**

## Support

Untuk pertanyaan atau masalah teknis, silakan hubungi tim development atau buat issue di repository project.
