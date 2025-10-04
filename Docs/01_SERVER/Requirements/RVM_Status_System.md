# RVM Status System Documentation

## Overview
Sistem status RVM yang komprehensif dengan 3 jenis status yang berbeda untuk monitoring dan manajemen RVM secara real-time.

## Status Types

### 1. Status Operasional (Operational Status)
**Lokasi**: `reverse_vending_machines.status`

#### Status Values:
- **`active`**: RVM beroperasi normal
- **`inactive`**: RVM tidak aktif atau over-capacity
- **`maintenance`**: RVM dalam mode maintenance
- **`error`**: RVM mengalami error

#### Logic:
```php
// Status Aktif
if (current_load >= 0 && current_load <= 100) {
    status = 'active'
}

// Status Non Aktif (Over Capacity)
if (current_load > 100) {
    status = 'inactive'
    // TODO: Send notification
}

// Status Maintenance
// Set by admin via action menu
```

### 2. Status Koneksi (Connection Status)
**Lokasi**: `reverse_vending_machines.connection_status`

#### Status Values:
- **`connected`**: RVM terhubung ke jaringan
- **`disconnected`**: RVM tidak terhubung

#### Logic:
```php
// Ping RVM IP address
$pingResult = pingHost($rvm->ip_address);
$connection_status = $pingResult ? 'connected' : 'disconnected';
```

#### Check Method:
- Ping ke IP RVM setiap beberapa detik
- Timeout: 3 detik
- Command: `ping -c 1 -W 3 {ip_address}`

### 3. Status API (API Status)
**Lokasi**: `reverse_vending_machines.api_status`

#### Status Values:
- **`valid`**: API endpoint RVM responsif
- **`invalid`**: API endpoint RVM tidak responsif

#### Logic:
```php
// Check API health endpoint
$url = "http://{$ip_address}:5000/api/health";
$response = file_get_contents($url);
$api_status = $response !== false ? 'valid' : 'invalid';
```

#### Check Method:
- HTTP GET ke `http://{ip}:5000/api/health`
- Timeout: 5 detik
- Expected response: JSON health check

## Database Schema

### New Columns Added:
```sql
-- Connection status
connection_status ENUM('connected', 'disconnected') DEFAULT 'disconnected'

-- API status  
api_status ENUM('valid', 'invalid') DEFAULT 'invalid'

-- Timestamps
last_connection_check TIMESTAMP NULL
last_api_check TIMESTAMP NULL
```

### Indexes:
```sql
CREATE INDEX idx_connection_status ON reverse_vending_machines(connection_status);
CREATE INDEX idx_api_status ON reverse_vending_machines(api_status);
```

## API Key Management

### Expiration:
- **Duration**: 1 bulan (bukan 1 tahun)
- **Auto-generated**: Ya, saat RVM dibuat
- **Column**: `api_key_expires_at`

```php
// Generate API key with 1 month expiration
$apiKey = bin2hex(random_bytes(32));
$api_key_expires_at = now()->addMonth();
```

## Model Methods

### ReverseVendingMachine Model:

#### Status Management:
```php
// Update status based on current_load
$rvm->updateStatusBasedOnLoad()

// Check connection status
$rvm->checkConnectionStatus()

// Check API status  
$rvm->checkApiStatus()

// Get comprehensive status
$rvm->getComprehensiveStatus()
```

#### Helper Methods:
```php
// Ping host
private function pingHost($host, $timeout = 3)

// Check API health
private function checkApiHealth()
```

## Console Commands

### Check RVM Status:
```bash
# Check all RVMs
php artisan rvm:check-status

# Check specific RVM
php artisan rvm:check-status --rvm-id=1
```

### Command Features:
- Update status based on load
- Check connection status (ping)
- Check API status (health endpoint)
- Display comprehensive status table
- Support single RVM or all RVMs

## Frontend Display

### Dashboard RVM Cards:
- **Status Badge**: Operational status (active/inactive/maintenance/error)
- **Connection Badge**: Connection status (Terhubung/Tidak Terhubung)
- **API Badge**: API status (API Valid/API Invalid)

### Color Coding:
- **Status**: Green (active), Gray (inactive), Yellow (maintenance), Red (error)
- **Connection**: Green (connected), Red (disconnected)
- **API**: Blue (valid), Orange (invalid)

## Monitoring & Notifications

### Current Implementation:
- ✅ Status checking methods
- ✅ Database schema
- ✅ Frontend display
- ✅ Console commands

### TODO (Future Implementation):
- ⏳ Notification system for over-capacity
- ⏳ Maintenance mode page
- ⏳ Automated status checking (cron job)
- ⏳ Real-time status updates (WebSocket)

## Integration Points

### Jetson Device:
- **Health Endpoint**: `http://{jetson_ip}:5000/api/health`
- **Upload Endpoint**: `http://{jetson_ip}:5000/api/upload`
- **API Key Header**: `X-RVM-API-Key`

### RVM Platform:
- **Status Updates**: Via API calls
- **Load Updates**: Via detection results
- **Ping Updates**: Via health checks

## Configuration

### Timeouts:
- **Ping Timeout**: 3 seconds
- **API Timeout**: 5 seconds
- **Connection Check**: Every 30 seconds (planned)
- **API Check**: Every 60 seconds (planned)

### Thresholds:
- **Over Capacity**: current_load > 100
- **Online Status**: last_ping < 5 minutes
- **API Key Expiry**: 1 month

## Error Handling

### Connection Failures:
- Graceful degradation
- Log connection attempts
- Update status to 'disconnected'

### API Failures:
- Timeout handling
- HTTP error handling
- Update status to 'invalid'

### Database Errors:
- Transaction rollback
- Error logging
- Status preservation
