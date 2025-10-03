# 🏪 MyRVM POS System - Quick Start Guide

## 🎯 Overview

POS System untuk RVM UI dengan fitur Remote Access Control, Security Authentication, RVM Status Monitoring, dan Remote Control. Sistem ini menggunakan mock data untuk testing dan memiliki fallback mechanisms yang robust.

## 🚀 Quick Access

### Admin Dashboard
```
URL: http://localhost:8000/admin/rvm-dashboard
```

### Kiosk Mode (Remote UI)
```
URL: http://localhost:8000/admin/rvm/2/remote/nQghw8zcyn1WVmGqOCiRbXhBBduQKJSN
```

## ✅ Current Status

### Working Features
- ✅ **Admin Dashboard**: RVM monitoring dengan charts dan statistics
- ✅ **Kiosk Mode**: Fullscreen interface dengan guest login
- ✅ **Mock Data System**: Comprehensive dummy data untuk testing
- ✅ **Error Handling**: Graceful fallbacks untuk semua API calls
- ✅ **WebSocket Mock**: Mock events untuk real-time testing
- ✅ **Security Features**: Admin PIN verification dan kiosk mode protection

### Console Log Status
```
✅ "Setting up mock WebSocket events for testing"
✅ "Using mock session for testing: {session data}"
✅ "Using mock RVM status for testing: active"
✅ "Mock: Session authorized for Test User"
✅ "Mock: Deposit completed {deposit data}"
✅ "Available mock data: {rvm: {...}, sessions: Array(2), deposits: Array(3)}"
```

## 🧪 Testing Functions

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

## 📱 User Flow Testing

### 1. Guest Login Flow
1. **Access Kiosk Mode** → Interface loads dengan QR Code
2. **Click "Lanjutkan sebagai Tamu"** → Transitions to authorized state
3. **Click "Mulai Proses Deposit"** → Shows processing state
4. **Wait 5 seconds** → Shows completion dengan mock data

### 2. Admin Features
1. **Press Ctrl+Alt+E** → Shows exit button
2. **Enter PIN** → Test dengan: `0000`, `1234`, `5678`, `9999`
3. **Exit Kiosk** → Closes window

### 3. Dashboard Monitoring
1. **Access Dashboard** → Shows RVM monitoring data
2. **View Charts** → Status distribution dan statistics
3. **Remote Control** → Test connection dan status updates

## 🔧 Mock Data Available

### RVM Data
- **ID**: 2, **Name**: "RVM-002"
- **Location**: "Food Court, Lantai 2"
- **Status**: "active"

### Session Data
- **Session 1**: "session-001", User: "John Doe"
- **Session 2**: "session-002", User: "Guest"

### Deposit Data
- **Plastic Bottles**: 0.5kg, 100 points
- **Aluminum Cans**: 0.3kg, 75 points
- **Glass Bottles**: 0.8kg, 150 points

## 🐛 Error Handling

### Automatic Fallbacks
- **401 Unauthorized** → Mock data used automatically
- **WebSocket Failed** → Mock events activated
- **QRCode Failed** → Text display fallback
- **Fullscreen Failed** → Window mode

### Console Messages
- **✅ Success**: Mock data working correctly
- **⚠️ Warning**: Expected fallback messages
- **❌ Error**: Issues that need attention

## 📚 Documentation

### Complete Guides
- **[Testing Guide](docs/pos-system-testing-guide.md)**: Comprehensive testing scenarios
- **[API Testing](docs/pos-system-api-testing.md)**: API endpoints dan mock data
- **[Troubleshooting](docs/pos-system-troubleshooting.md)**: Common issues dan solutions

### Quick References
- **Mock Data**: `testMockEvents.showMockData()`
- **Testing Functions**: Available via `testMockEvents.*`
- **Console Logs**: Check for success/warning messages

## 🚀 Next Steps

### Production Readiness
- [ ] Enable real API authentication
- [ ] Implement actual WebSocket connection
- [ ] Add proper error logging
- [ ] Performance optimization

### Hardware Integration
- [ ] Connect to real RVM hardware
- [ ] Implement actual deposit processing
- [ ] Add camera integration
- [ ] Sensor data integration

## 🔍 Troubleshooting

### Common Issues
1. **Dashboard not loading** → Check console for errors
2. **401 Unauthorized** → Mock data should work automatically
3. **WebSocket errors** → Mock events should activate
4. **QRCode not showing** → Fallback text should display

### Debug Commands
```javascript
// Check system status
console.log('Mock Data:', mockData);
console.log('Current Session:', currentSession);
console.log('Testing Functions:', testMockEvents);
```

## 📞 Support

Jika ada masalah:
1. **Check console logs** untuk error messages
2. **Verify mock data** dengan `testMockEvents.showMockData()`
3. **Test functions** dengan testing commands
4. **Check documentation** untuk detailed troubleshooting

---

**Status**: ✅ Testing Phase - All Features Working with Mock Data  
**Last Updated**: 2025-09-07  
**Version**: 1.0.0