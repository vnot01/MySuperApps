# 📋 POS System Testing Guide - MyRVM Platform

## 🎯 Overview

Dokumentasi ini menjelaskan cara testing POS System untuk RVM UI yang telah diimplementasi dengan fitur Remote Access Control, Security Authentication, RVM Status Monitoring, dan Remote Control.

## 🚀 Quick Start

### 1. Akses Dashboard Admin
```
URL: http://localhost:8000/admin/rvm-dashboard
```

### 2. Akses Kiosk Mode (Remote UI)
```
URL: http://localhost:8000/admin/rvm/2/remote/nQghw8zcyn1WVmGqOCiRbXhBBduQKJSN
```

## 🔧 Setup & Configuration

### Prerequisites
- Docker & Docker Compose running
- Laravel application accessible at `localhost:8000`
- Browser dengan Developer Tools (F12)

### Database Setup
```bash
# Run migrations
docker compose exec app php artisan migrate

# Seed test data
docker compose exec app php artisan db:seed --class=RvmPosSystemSeeder
```

## 📊 Testing Scenarios

### 1. Admin Dashboard Testing

#### 1.1 Dashboard Access
- **URL**: `http://localhost:8000/admin/rvm-dashboard`
- **Expected**: Dashboard loads dengan monitoring data
- **Features**:
  - RVM status monitoring
  - Statistics charts
  - Remote control buttons
  - Real-time updates

#### 1.2 RVM Monitoring
- **Status Counts**: Active, Maintenance, Error, Full
- **Session Statistics**: Active sessions, Total sessions today
- **Deposit Statistics**: Total deposits today
- **Chart Visualization**: Doughnut chart dengan status distribution

#### 1.3 Remote Control
- **Connect to RVM**: Test connection ke specific RVM
- **Update Status**: Change RVM status (Active, Maintenance, Error, Full)
- **Bulk Operations**: Update multiple RVMs sekaligus

### 2. Kiosk Mode Testing

#### 2.1 Basic Functionality
- **URL**: `http://localhost:8000/admin/rvm/2/remote/nQghw8zcyn1WVmGqOCiRbXhBBduQKJSN`
- **Expected**: Kiosk mode interface loads
- **Features**:
  - Fullscreen display
  - QR Code generation
  - Guest login option
  - Session management

#### 2.2 Guest Login Flow
1. **Waiting State**: QR Code displayed, Guest button available
2. **Click "Lanjutkan sebagai Tamu"**: Should transition to authorized state
3. **Authorized State**: Shows "Guest (Mock)" name
4. **Processing**: Click "Mulai Proses Deposit"
5. **Completed**: Shows deposit details after 5 seconds

#### 2.3 Admin Features
- **Exit Kiosk**: Press `Ctrl+Alt+E` to show exit button
- **PIN Verification**: Test dengan PIN: `0000`, `1234`, `5678`, `9999`
- **Fullscreen**: Automatic fullscreen on user interaction

## 🧪 Mock Data Testing

### Available Mock Data
```javascript
// Access via browser console
testMockEvents.showMockData()
```

#### RVM Data
```json
{
  "id": 2,
  "name": "RVM-002",
  "location": "Food Court, Lantai 2",
  "status": "active",
  "api_key": "E5gKWDmrYkp6or9dly6ty4ouuWPhZ1tl"
}
```

#### Session Data
```json
[
  {
    "id": "session-001",
    "token": "token-abc123",
    "user_name": "John Doe",
    "expires_at": "2025-09-07T16:15:35.687Z"
  },
  {
    "id": "session-002",
    "token": "token-def456",
    "user_name": "Guest",
    "expires_at": "2025-09-07T16:15:35.687Z"
  }
]
```

#### Deposit Data
```json
[
  {
    "id": 1,
    "waste_type": "Plastic Bottles",
    "weight": "0.5",
    "reward_amount": "100",
    "timestamp": "2025-09-07T15:45:35.687Z"
  },
  {
    "id": 2,
    "waste_type": "Aluminum Cans",
    "weight": "0.3",
    "reward_amount": "75",
    "timestamp": "2025-09-07T15:45:35.687Z"
  },
  {
    "id": 3,
    "waste_type": "Glass Bottles",
    "weight": "0.8",
    "reward_amount": "150",
    "timestamp": "2025-09-07T15:45:35.687Z"
  }
]
```

## 🔍 Testing Functions

### Console Testing Commands
Buka Developer Console (F12) dan jalankan:

```javascript
// Test session authorization
testMockEvents.testSessionAuth()

// Test guest activation
testMockEvents.testGuestActivation()

// Test deposit processing
testMockEvents.testDepositProcessing()

// Test deposit completion
testMockEvents.testDepositCompleted()

// Test deposit failure
testMockEvents.testDepositFailed()

// Show all mock data
testMockEvents.showMockData()
```

### Expected Console Output
```
✅ "Setting up mock WebSocket events for testing"
✅ "Using mock session for testing: {session data}"
✅ "Using mock RVM status for testing: active"
✅ "Mock: Session authorized for Test User"
✅ "Mock: Deposit processing started"
✅ "Mock: Deposit completed {deposit data}"
✅ "Available mock data: {rvm: {...}, sessions: Array(2), deposits: Array(3)}"
```

## 🐛 Error Handling & Fallbacks

### 1. API Errors (401 Unauthorized)
- **Cause**: Authentication middleware still active
- **Fallback**: Mock data digunakan otomatis
- **Console**: "Using mock [feature] for testing"

### 2. WebSocket Errors
- **Cause**: Echo library tidak ter-load
- **Fallback**: Mock WebSocket events
- **Console**: "WebSocket disabled, using polling mode"

### 3. QRCode Library Errors
- **Cause**: QRCode library gagal load
- **Fallback**: Text-based session token display
- **Console**: "QRCode library not loaded, using fallback"

### 4. Fullscreen API Errors
- **Cause**: User gesture required
- **Fallback**: Window mode
- **Console**: "Fullscreen request failed"

## 📱 User Interface Testing

### 1. Responsive Design
- **Desktop**: Full layout dengan sidebar
- **Tablet**: Responsive grid layout
- **Mobile**: Stacked layout

### 2. Kiosk Mode Features
- **Fullscreen**: Automatic fullscreen
- **No Scrollbars**: Hidden scrollbars
- **No Context Menu**: Disabled right-click
- **Keyboard Shortcuts**: Disabled browser shortcuts

### 3. Status Indicators
- **Active**: Green color (#10B981)
- **Maintenance**: Yellow color (#F59E0B)
- **Error**: Red color (#EF4444)
- **Full**: Red color (#EF4444)
- **Unknown**: Gray color (#6B7280)

## 🔐 Security Testing

### 1. Admin PIN Verification
- **Valid PINs**: `0000`, `1234`, `5678`, `9999`
- **Invalid PINs**: Any other combination
- **Test**: Press `Ctrl+Alt+E` → Enter PIN → Verify access

### 2. Kiosk Mode Security
- **Disabled Shortcuts**: F12, Ctrl+Shift+I, Ctrl+U, Ctrl+R, Ctrl+W
- **Exit Protection**: PIN required untuk exit
- **Fullscreen Lock**: Prevents easy access to browser

### 3. Session Management
- **Session Expiry**: 30 minutes default
- **Token Security**: Random session tokens
- **Guest Access**: Limited functionality

## 📊 Performance Testing

### 1. Chart Performance
- **Chart.js Optimization**: Disabled animations
- **Debouncing**: 200ms throttle untuk updates
- **Memory Management**: Proper cleanup on page unload

### 2. API Performance
- **Mock Data**: Instant response untuk testing
- **Error Handling**: Graceful fallbacks
- **Caching**: Session data cached locally

### 3. WebSocket Performance
- **Connection Management**: Auto-reconnect
- **Event Throttling**: Prevents spam
- **Fallback Mode**: Polling jika WebSocket gagal

## 🚨 Troubleshooting

### Common Issues

#### 1. Dashboard Tidak Load
```bash
# Check if server running
docker compose ps

# Check logs
docker compose logs app
```

#### 2. 401 Unauthorized Errors
- **Solution**: Routes sudah dibuat public untuk testing
- **Fallback**: Mock data otomatis digunakan

#### 3. WebSocket Connection Failed
- **Solution**: Mock WebSocket events aktif
- **Fallback**: Polling mode enabled

#### 4. QRCode Tidak Muncul
- **Solution**: Fallback text display aktif
- **Fallback**: Session token ditampilkan sebagai text

### Debug Commands
```javascript
// Check mock data
console.log(mockData)

// Check current session
console.log(currentSession)

// Check WebSocket status
console.log(echo)

// Check testing functions
console.log(testMockEvents)
```

## 📈 Success Criteria

### ✅ Dashboard Testing
- [ ] Dashboard loads tanpa error
- [ ] RVM monitoring data displayed
- [ ] Charts render correctly
- [ ] Remote control buttons functional
- [ ] Real-time updates working

### ✅ Kiosk Mode Testing
- [ ] Kiosk mode loads in fullscreen
- [ ] Guest login berfungsi
- [ ] Deposit processing simulation works
- [ ] Admin PIN verification works
- [ ] Exit kiosk mode functional

### ✅ Mock Data Testing
- [ ] All testing functions work
- [ ] Mock data displays correctly
- [ ] Fallbacks activate on errors
- [ ] Console logs informative

### ✅ Error Handling
- [ ] 401 errors handled gracefully
- [ ] WebSocket errors don't break app
- [ ] QRCode fallback works
- [ ] Fullscreen errors handled

## 🔄 Next Steps

### 1. Production Readiness
- [ ] Enable proper authentication
- [ ] Implement real WebSocket connection
- [ ] Add proper error logging
- [ ] Performance optimization

### 2. Hardware Integration
- [ ] Connect to real RVM hardware
- [ ] Implement actual deposit processing
- [ ] Add camera integration for QR scanning
- [ ] Sensor data integration

### 3. Advanced Features
- [ ] Multi-language support
- [ ] Advanced analytics
- [ ] Notification system
- [ ] Backup and recovery

## 📞 Support

Jika ada masalah atau pertanyaan:
1. Check console logs untuk error messages
2. Verify mock data dengan `testMockEvents.showMockData()`
3. Test individual functions dengan testing commands
4. Check network tab untuk API call status

---

**Last Updated**: 2025-09-07  
**Version**: 1.0.0  
**Status**: Testing Phase
