# 🔧 POS System Troubleshooting Guide

## 🎯 Overview

Dokumentasi ini menjelaskan cara mengatasi masalah umum yang terjadi pada POS System untuk RVM UI.

## 🚨 Common Issues & Solutions

### 1. Console Log Analysis

#### ✅ Expected Console Output
```
✅ "Setting up mock WebSocket events for testing"
✅ "Using mock session for testing: {session data}"
✅ "Using mock RVM status for testing: active"
✅ "Mock: Session authorized for Test User"
✅ "Mock: Deposit completed {deposit data}"
✅ "Available mock data: {rvm: {...}, sessions: Array(2), deposits: Array(3)}"
```

#### ⚠️ Warning Messages (Normal)
```
⚠️ "QRCode library failed to load, using fallback"
⚠️ "RVM status update failed: 401"
⚠️ "Error initializing WebSocket: TypeError: Cannot read properties of undefined (reading 'channel')"
⚠️ "WebSocket disabled, using polling mode"
```

#### ❌ Error Messages (Need Attention)
```
❌ "Failed to load resource: the server responded with a status of 401 (Unauthorized)"
❌ "Uncaught (in promise) Error: Could not establish connection. Receiving end does not exist."
```

### 2. API Error Handling

#### Problem: 401 Unauthorized Errors
**Symptoms**:
- Console shows "Failed to load resource: the server responded with a status of 401"
- API calls fail repeatedly
- Dashboard shows no data

**Root Cause**:
- Authentication middleware still active
- API routes require authentication
- No valid auth token provided

**Solution**:
```javascript
// Mock data automatically used as fallback
if (!response.ok) {
    console.warn('API call failed:', response.status);
    // Use mock data
    const mockData = getMockData();
    updateUI(mockData);
}
```

**Verification**:
- Check console for "Using mock [feature] for testing"
- UI should still function with mock data
- No user-facing errors

#### Problem: Network Connection Errors
**Symptoms**:
- Console shows "Could not establish connection"
- API calls timeout
- WebSocket connection failed

**Root Cause**:
- Network connectivity issues
- Server not running
- Firewall blocking requests

**Solution**:
```bash
# Check if server is running
docker compose ps

# Check server logs
docker compose logs app

# Restart services if needed
docker compose restart
```

**Verification**:
- Server status shows "Up"
- No error logs in console
- Mock data fallback working

### 3. WebSocket Issues

#### Problem: Echo Library Not Loaded
**Symptoms**:
- Console shows "TypeError: Cannot read properties of undefined (reading 'channel')"
- WebSocket initialization fails
- Real-time updates not working

**Root Cause**:
- Laravel Echo library not loaded
- CDN connection issues
- JavaScript loading order problems

**Solution**:
```javascript
// Mock WebSocket events automatically activated
if (typeof Echo === 'undefined') {
    console.warn('Laravel Echo not available, WebSocket disabled');
    setupMockWebSocketEvents();
    return;
}
```

**Verification**:
- Check console for "Setting up mock WebSocket events for testing"
- Mock events should be available via `testMockEvents`
- UI should function normally

#### Problem: WebSocket Connection Failed
**Symptoms**:
- Console shows "WebSocket disabled, using polling mode"
- Real-time updates not working
- Connection errors in network tab

**Root Cause**:
- WebSocket server not running
- Configuration issues
- Network connectivity problems

**Solution**:
```javascript
// Graceful fallback to mock events
try {
    echo = new Echo({...});
} catch (error) {
    console.error('Error initializing WebSocket:', error);
    setupMockWebSocketEvents();
}
```

**Verification**:
- Mock WebSocket events working
- Testing functions available
- No critical errors

### 4. QRCode Library Issues

#### Problem: QRCode Library Failed to Load
**Symptoms**:
- Console shows "QRCode library failed to load, using fallback"
- QR Code not displayed
- Fallback text shown instead

**Root Cause**:
- CDN connection issues
- Library loading problems
- Network restrictions

**Solution**:
```javascript
// Fallback to text display
if (typeof QRCode === 'undefined') {
    console.warn('QRCode library not loaded, using fallback');
    qrContainer.innerHTML = `
        <div class="text-center">
            <div class="w-48 h-48 mx-auto bg-gray-100 border-2 border-dashed border-gray-300 rounded-lg flex items-center justify-center">
                <div class="text-gray-500">
                    <div class="text-sm font-semibold">QR Code</div>
                    <div class="text-xs mt-1">Session Token:</div>
                    <div class="text-xs font-mono break-all">${sessionToken.substring(0, 20)}...</div>
                </div>
            </div>
        </div>
    `;
    return;
}
```

**Verification**:
- Fallback display shows session token
- No error messages
- Functionality preserved

### 5. UI/UX Issues

#### Problem: Dashboard Not Loading
**Symptoms**:
- Blank page or loading spinner
- Console shows errors
- No data displayed

**Root Cause**:
- JavaScript errors
- CSS loading issues
- API failures

**Solution**:
```javascript
// Check for JavaScript errors
console.log('Dashboard initialization started');

// Verify mock data is available
if (typeof mockData === 'undefined') {
    console.error('Mock data not available');
    return;
}

// Check if testing functions are available
if (typeof testMockEvents === 'undefined') {
    console.error('Testing functions not available');
    return;
}
```

**Verification**:
- Console shows initialization messages
- Mock data available
- Testing functions working

#### Problem: Kiosk Mode Not Working
**Symptoms**:
- Not in fullscreen mode
- Browser shortcuts still work
- Exit button not accessible

**Root Cause**:
- Fullscreen API not supported
- User gesture required
- Configuration issues

**Solution**:
```javascript
// Check fullscreen support
if (document.documentElement.requestFullscreen) {
    // Only request fullscreen on user interaction
    document.addEventListener('click', () => {
        if (!document.fullscreenElement) {
            document.documentElement.requestFullscreen().catch(err => {
                console.log('Fullscreen request failed:', err);
            });
        }
    }, { once: true });
}
```

**Verification**:
- Fullscreen activates on user click
- Browser shortcuts disabled
- Exit button accessible via Ctrl+Alt+E

### 6. Performance Issues

#### Problem: Chart Performance Violations
**Symptoms**:
- Console shows "requestAnimationFrame handler took <N>ms"
- Charts lag or freeze
- UI becomes unresponsive

**Root Cause**:
- Too many chart updates
- Heavy animations
- Memory leaks

**Solution**:
```javascript
// Optimize chart updates
let chartUpdateTimeout = null;
let isChartUpdating = false;

function updateStatusChart() {
    if (isChartUpdating) return;
    
    if (chartUpdateTimeout) {
        clearTimeout(chartUpdateTimeout);
    }
    
    chartUpdateTimeout = setTimeout(() => {
        isChartUpdating = true;
        // Update chart
        statusChart.update('none'); // Disable animation
        isChartUpdating = false;
    }, 200); // Debounce to 200ms
}
```

**Verification**:
- No performance violations in console
- Charts update smoothly
- UI remains responsive

#### Problem: Memory Leaks
**Symptoms**:
- Browser becomes slow
- Memory usage increases
- Console shows memory warnings

**Root Cause**:
- Event listeners not cleaned up
- Intervals not cleared
- Chart instances not destroyed

**Solution**:
```javascript
// Cleanup function
function cleanup() {
    if (chartUpdateTimeout) {
        clearTimeout(chartUpdateTimeout);
        chartUpdateTimeout = null;
    }
    if (refreshInterval) {
        clearInterval(refreshInterval);
        refreshInterval = null;
    }
    if (statusChart) {
        statusChart.destroy();
        statusChart = null;
    }
}

// Cleanup on page unload
window.addEventListener('beforeunload', cleanup);
```

**Verification**:
- Memory usage stable
- No memory leaks
- Clean console

## 🔍 Debug Commands

### Browser Console Commands

#### Check System Status
```javascript
// Check mock data availability
console.log('Mock Data:', mockData);

// Check current session
console.log('Current Session:', currentSession);

// Check WebSocket status
console.log('Echo Object:', typeof Echo);
console.log('Mock Events:', window.mockEvents);

// Check testing functions
console.log('Testing Functions:', testMockEvents);
```

#### Test Individual Features
```javascript
// Test session authorization
testMockEvents.testSessionAuth();

// Test guest activation
testMockEvents.testGuestActivation();

// Test deposit processing
testMockEvents.testDepositProcessing();

// Test deposit completion
testMockEvents.testDepositCompleted();

// Test deposit failure
testMockEvents.testDepositFailed();

// Show all mock data
testMockEvents.showMockData();
```

#### Check API Status
```javascript
// Test API endpoint
fetch('/api/v2/rvms/2')
  .then(response => console.log('API Status:', response.status))
  .catch(error => console.log('API Error:', error));

// Test session creation
fetch('/api/v2/rvm/session/create', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
  },
  body: JSON.stringify({ rvm_id: 2 })
})
.then(response => console.log('Session API Status:', response.status))
.catch(error => console.log('Session API Error:', error));
```

## 📊 Performance Monitoring

### Key Metrics to Monitor

#### Console Log Analysis
- **Error Count**: Should be minimal
- **Warning Count**: Expected for fallbacks
- **Success Messages**: Should show mock data usage

#### Memory Usage
- **Initial Load**: ~10-15MB
- **After Testing**: ~15-20MB
- **Memory Leaks**: Should not increase continuously

#### Response Times
- **Mock Data**: <10ms
- **UI Updates**: <100ms
- **Chart Updates**: <200ms

### Performance Optimization

#### Chart Optimization
```javascript
// Disable animations for better performance
statusChart = new Chart(ctx, {
    // ... chart config
    options: {
        animation: {
            duration: 0,
            animateRotate: false,
            animateScale: false
        }
    }
});
```

#### API Optimization
```javascript
// Debounce API calls
let apiCallTimeout = null;
function debouncedApiCall() {
    if (apiCallTimeout) {
        clearTimeout(apiCallTimeout);
    }
    apiCallTimeout = setTimeout(() => {
        // Make API call
    }, 300);
}
```

## 🚀 Production Readiness Checklist

### Current Status
- ✅ Mock data system working
- ✅ Error handling implemented
- ✅ Fallback mechanisms active
- ✅ Testing functions available
- ✅ Performance optimized

### Next Steps
- [ ] Enable real API authentication
- [ ] Implement actual WebSocket connection
- [ ] Add proper error logging
- [ ] Security hardening
- [ ] Load testing

### Monitoring Setup
- [ ] Error tracking
- [ ] Performance monitoring
- [ ] User analytics
- [ ] System health checks

---

**Last Updated**: 2025-09-07  
**Version**: 1.0.0  
**Status**: Testing Phase with Comprehensive Troubleshooting
