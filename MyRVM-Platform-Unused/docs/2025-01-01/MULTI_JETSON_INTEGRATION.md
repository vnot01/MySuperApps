# Multi-Jetson Orin Integration

## Overview
Integrasi status Jetson Orin ke dalam RVM Monitoring dashboard dengan dukungan multiple Jetson Orin devices.

## Perubahan yang Dilakukan

### 1. Hapus Status Cards Individual
- ❌ Dihapus: "Jetson Orin Status" card
- ❌ Dihapus: "CV Server Status" card  
- ❌ Dihapus: `jetson-integration.js` file

### 2. Integrasi ke RVM Cards
- ✅ Status Jetson ditampilkan di setiap RVM card
- ✅ Response time Jetson ditampilkan
- ✅ Icon dan warna berdasarkan status koneksi

### 3. Multi-Jetson Support
- ✅ Setiap RVM dapat memiliki Jetson Orin sendiri
- ✅ Status individual per RVM
- ✅ Handling RVM tanpa IP address

## Struktur Data

### RVM Card Data
```javascript
{
  id: 1,
  name: "RVM-001",
  location: "Lobby Building A",
  capacity: 75,
  status: "active",
  ip_address: "100.117.234.2",
  jetson_health: {
    success: true,
    connected: true,
    message: "Jetson Orin is healthy.",
    status: "healthy",
    response_time: 45
  },
  jetson_connected: true
}
```

### Statistics
```javascript
{
  total_rvm: 5,
  active_sessions: 2,
  deposits_today: 150,
  total_issues: 1,
  jetson_connected: 4  // Jumlah RVM dengan Jetson terhubung
}
```

## API Endpoints

### RVM Monitoring
- **GET** `/admin/rvm/monitoring`
- Returns: List of RVMs with Jetson status

### Individual RVM Jetson Status
- **GET** `/api/admin/jetson/{rvmId}/health`
- **GET** `/api/admin/jetson/{rvmId}/status`
- **GET** `/api/admin/jetson/{rvmId}/hardware`

## Frontend Integration

### RVM Card Display
```javascript
// Status indicator
<i class="fas fa-microchip"></i> Jetson Online
<i class="fas fa-tachometer-alt"></i> 45ms
<i class="fas fa-clock"></i> 2 minutes ago
```

### Color Coding
- 🟢 **Green**: Jetson Online
- 🔴 **Red**: Jetson Offline
- ⚪ **Gray**: No IP configured

## Configuration

### Jetson API Base URL
```php
// config/services.php
'jetson' => [
    'api_base_url' => 'http://100.117.234.2:5000',
    'timeout' => 5
]
```

### RVM IP Configuration
- Set `ip_address` field in `reverse_vending_machines` table
- Format: `100.117.234.2` (without port)
- Port 5000 digunakan untuk API Jetson

## Error Handling

### RVM tanpa IP Address
- Status: "No IP configured"
- Jetson health: `null`
- Jetson connected: `false`

### Jetson API Error
- Status: "Jetson Offline"
- Message: Error dari API
- Response time: `null`

## Testing

### Manual Test
1. Buka dashboard: `http://100.123.143.87:8001/admin/dashboard`
2. Login dengan kredensial admin
3. Lihat RVM cards dengan status Jetson
4. Test ping individual RVM

### API Test
```bash
# Test RVM monitoring (requires auth)
curl -X GET "http://100.123.143.87:8001/admin/rvm/monitoring" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <token>"
```

## Monitoring

### Dashboard Statistics
- Total RVM: Jumlah total RVM
- Jetson Connected: Jumlah RVM dengan Jetson online
- Active Sessions: Sesi aktif
- Issues: RVM dengan masalah

### Individual RVM Status
- Jetson Online/Offline
- Response time (ms)
- Last seen timestamp
- Capacity percentage

## Troubleshooting

### Jetson Tidak Terhubung
1. Cek IP address RVM
2. Cek koneksi network ke Jetson
3. Cek status API Jetson: `http://{ip}:5000/api/health`
4. Cek logs Laravel

### Performance Issues
1. Cek response time Jetson API
2. Cek timeout configuration
3. Cek concurrent requests

## Future Enhancements

### Planned Features
- [ ] Real-time status updates via WebSocket
- [ ] Jetson health history charts
- [ ] Bulk Jetson operations
- [ ] Jetson performance metrics
- [ ] Auto-discovery of Jetson devices

### Configuration Options
- [ ] Customizable timeout values
- [ ] Retry mechanisms
- [ ] Health check intervals
- [ ] Alert thresholds
