# 🔌 POS System API Testing Guide

## 🎯 Overview

Dokumentasi ini menjelaskan cara testing API endpoints untuk POS System dengan mock data dan fallback mechanisms.

## 🚀 API Endpoints

### 1. RVM Management APIs

#### 1.1 Get RVM Details
```http
GET /api/v2/rvms/{id}
```

**Testing URL**: `http://localhost:8000/api/v2/rvms/2`

**Expected Response** (Success):
```json
{
  "success": true,
  "data": {
    "id": 2,
    "name": "RVM-002",
    "location_description": "Food Court, Lantai 2",
    "status": "active",
    "api_key": "E5gKWDmrYkp6or9dly6ty4ouuWPhZ1tl",
    "admin_access_pin": null,
    "remote_access_enabled": true,
    "kiosk_mode_enabled": true,
    "pos_settings": null,
    "created_at": "2025-09-07T04:56:30.000000Z",
    "updated_at": "2025-09-07T04:56:30.000000Z"
  }
}
```

**Fallback Response** (401 Error):
```javascript
// Mock data used automatically
{
  "id": 2,
  "name": "RVM-002",
  "location": "Food Court, Lantai 2",
  "status": "active",
  "api_key": "E5gKWDmrYkp6or9dly6ty4ouuWPhZ1tl"
}
```

#### 1.2 Get All RVMs
```http
GET /api/v2/rvms
```

**Testing URL**: `http://localhost:8000/api/v2/rvms`

**Expected Response**:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "RVM-001",
      "location_description": "Lobby Gedung A, Lantai 1",
      "status": "maintenance"
    },
    {
      "id": 2,
      "name": "RVM-002", 
      "location_description": "Food Court, Lantai 2",
      "status": "active"
    }
  ],
  "meta": {
    "total": 8,
    "per_page": 15,
    "current_page": 1
  }
}
```

### 2. Session Management APIs

#### 2.1 Create Session
```http
POST /api/v2/rvm/session/create
```

**Request Body**:
```json
{
  "rvm_id": 2
}
```

**Expected Response** (Success):
```json
{
  "success": true,
  "data": {
    "session_id": "session-001",
    "session_token": "token-abc123",
    "rvm_id": 2,
    "expires_at": "2025-09-07T16:45:35.687Z",
    "created_at": "2025-09-07T16:15:35.687Z"
  }
}
```

**Fallback Response** (Error):
```javascript
// Mock session created automatically
{
  "session_id": "session-001",
  "session_token": "token-abc123",
  "expires_at": "2025-09-07T16:45:35.687Z"
}
```

#### 2.2 Activate Guest Session
```http
POST /api/v2/rvm/session/activate-guest
```

**Request Body**:
```json
{
  "session_token": "token-abc123"
}
```

**Expected Response**:
```json
{
  "success": true,
  "data": {
    "session_id": "session-001",
    "user_name": "Guest",
    "status": "active"
  }
}
```

**Fallback Response**:
```javascript
// Mock guest activation
showAuthorizedState('Guest (Mock)')
```

### 3. Admin Control APIs

#### 3.1 Remote Access
```http
POST /api/v2/admin/rvm/{rvmId}/remote-access
```

**Request Body**:
```json
{
  "access_pin": "1234"
}
```

**Expected Response**:
```json
{
  "success": true,
  "data": {
    "access_granted": true,
    "rvm_id": 2,
    "admin_name": "Admin"
  }
}
```

**Fallback Response**:
```javascript
// Mock PIN validation
const validPins = ['0000', '1234', '5678', '9999'];
return validPins.includes(pin);
```

#### 3.2 Update RVM Status
```http
POST /api/v2/admin/rvm/{rvmId}/status
```

**Request Body**:
```json
{
  "status": "maintenance",
  "reason": "Scheduled maintenance"
}
```

**Expected Response**:
```json
{
  "success": true,
  "data": {
    "rvm_id": 2,
    "old_status": "active",
    "new_status": "maintenance",
    "updated_at": "2025-09-07T16:15:35.687Z"
  }
}
```

#### 3.3 Get RVM Monitoring
```http
GET /api/v2/admin/rvm/monitoring
```

**Expected Response**:
```json
{
  "success": true,
  "data": {
    "total_rvms": 8,
    "status_counts": {
      "active": 5,
      "maintenance": 2,
      "error": 1,
      "full": 0
    },
    "active_sessions": 3,
    "total_sessions_today": 15,
    "total_deposits_today": 25,
    "rvms": [
      {
        "id": 2,
        "name": "RVM-002",
        "location": "Food Court, Lantai 2",
        "status": "active",
        "last_activity": "2025-09-07T16:10:35.687Z"
      }
    ]
  }
}
```

## 🧪 Testing Scenarios

### 1. API Error Handling

#### Scenario 1: 401 Unauthorized
```javascript
// Expected behavior
fetch('/api/v2/rvms/2')
  .then(response => {
    if (!response.ok) {
      console.warn('RVM status update failed:', response.status);
      // Use mock data
      const mockStatus = mockData.rvm.status;
      // Update UI with mock data
    }
  })
```

#### Scenario 2: Network Error
```javascript
// Expected behavior
fetch('/api/v2/rvms/2')
  .catch(error => {
    console.error('Error updating RVM status:', error);
    // Use mock data
    const mockStatus = mockData.rvm.status;
    // Update UI with mock data
  })
```

### 2. Mock Data Testing

#### Test Mock Data Availability
```javascript
// In browser console
testMockEvents.showMockData()
```

**Expected Output**:
```javascript
{
  rvm: {
    id: 2,
    name: "RVM-002",
    location: "Food Court, Lantai 2",
    status: "active",
    api_key: "E5gKWDmrYkp6or9dly6ty4ouuWPhZ1tl"
  },
  sessions: [
    {
      id: "session-001",
      token: "token-abc123",
      user_name: "John Doe",
      expires_at: "2025-09-07T16:45:35.687Z"
    },
    {
      id: "session-002",
      token: "token-def456", 
      user_name: "Guest",
      expires_at: "2025-09-07T16:45:35.687Z"
    }
  ],
  deposits: [
    {
      id: 1,
      waste_type: "Plastic Bottles",
      weight: "0.5",
      reward_amount: "100",
      timestamp: "2025-09-07T15:45:35.687Z"
    },
    {
      id: 2,
      waste_type: "Aluminum Cans",
      weight: "0.3",
      reward_amount: "75",
      timestamp: "2025-09-07T15:45:35.687Z"
    },
    {
      id: 3,
      waste_type: "Glass Bottles",
      weight: "0.8",
      reward_amount: "150",
      timestamp: "2025-09-07T15:45:35.687Z"
    }
  ]
}
```

### 3. WebSocket Mock Events

#### Test WebSocket Events
```javascript
// Test session authorization
testMockEvents.testSessionAuth()
// Expected: "Mock: Session authorized for Test User"

// Test guest activation
testMockEvents.testGuestActivation()
// Expected: "Mock: Guest session activated"

// Test deposit processing
testMockEvents.testDepositProcessing()
// Expected: "Mock: Deposit processing started"

// Test deposit completion
testMockEvents.testDepositCompleted()
// Expected: "Mock: Deposit completed {deposit data}"

// Test deposit failure
testMockEvents.testDepositFailed()
// Expected: "Mock: Deposit failed Test error message"
```

## 🔍 Console Log Analysis

### Expected Console Output

#### Successful Initialization
```
✅ "Setting up mock WebSocket events for testing"
✅ "Using mock session for testing: {session data}"
✅ "Using mock RVM status for testing: active"
✅ "Testing functions available:"
✅ "- testMockEvents.testSessionAuth()"
✅ "- testMockEvents.testGuestActivation()"
✅ "- testMockEvents.testDepositProcessing()"
✅ "- testMockEvents.testDepositCompleted()"
✅ "- testMockEvents.testDepositFailed()"
✅ "- testMockEvents.showMockData()"
```

#### Error Handling
```
⚠️ "QRCode library failed to load, using fallback"
⚠️ "RVM status update failed: 401"
✅ "Using mock RVM status for testing: active"
⚠️ "Error initializing WebSocket: TypeError: Cannot read properties of undefined (reading 'channel')"
✅ "WebSocket disabled, using polling mode"
✅ "Setting up mock WebSocket events for testing"
```

#### Mock Event Testing
```
✅ "Mock: Session authorized for Test User"
✅ "Mock: Deposit processing started"
✅ "Mock: Deposit completed {id: 1, waste_type: 'Plastic Bottles', weight: '0.5', reward_amount: '100', timestamp: '2025-09-07T15:45:35.687Z'}"
✅ "Available mock data: {rvm: {...}, sessions: Array(2), deposits: Array(3)}"
```

## 🐛 Troubleshooting

### Common API Issues

#### 1. 401 Unauthorized Errors
**Problem**: API calls return 401 status
**Solution**: Mock data automatically used
**Verification**: Check console for "Using mock [feature] for testing"

#### 2. WebSocket Connection Failed
**Problem**: Echo library not loaded
**Solution**: Mock WebSocket events activated
**Verification**: Check console for "Setting up mock WebSocket events for testing"

#### 3. QRCode Library Failed
**Problem**: QRCode library not loaded
**Solution**: Fallback text display
**Verification**: Check console for "QRCode library not loaded, using fallback"

### Debug Commands

#### Check API Status
```javascript
// Check if API endpoints are accessible
fetch('/api/v2/rvms/2')
  .then(response => console.log('API Status:', response.status))
  .catch(error => console.log('API Error:', error))
```

#### Check Mock Data
```javascript
// Verify mock data is available
console.log('Mock Data:', mockData)
console.log('Current Session:', currentSession)
console.log('Testing Functions:', testMockEvents)
```

#### Check WebSocket Status
```javascript
// Check WebSocket connection
console.log('Echo Object:', typeof Echo)
console.log('Mock Events:', window.mockEvents)
```

## 📊 Performance Metrics

### Expected Performance

#### API Response Times
- **Real API**: 100-500ms
- **Mock Data**: <10ms (instant)

#### WebSocket Events
- **Real WebSocket**: 50-100ms
- **Mock Events**: <5ms (instant)

#### UI Updates
- **Chart Updates**: <100ms
- **Status Changes**: <50ms
- **Mock Data Display**: <10ms

### Memory Usage
- **Mock Data**: ~5KB
- **Testing Functions**: ~2KB
- **Fallback Mechanisms**: ~3KB

## 🚀 Production Readiness

### Current Status
- ✅ Mock data system working
- ✅ Error handling implemented
- ✅ Fallback mechanisms active
- ✅ Testing functions available

### Next Steps
- [ ] Enable real API authentication
- [ ] Implement actual WebSocket connection
- [ ] Add proper error logging
- [ ] Performance optimization
- [ ] Security hardening

---

**Last Updated**: 2025-09-07  
**Version**: 1.0.0  
**Status**: Testing Phase with Mock Data
