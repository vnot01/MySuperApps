# RVM Status Logic Documentation

## Overview

This document explains the RVM (Reverse Vending Machine) status logic implemented in the MyRVM Platform. The status system is designed to be consistent between mock data and real database data.

## Status Logic

### Primary Status Determination

RVM status is determined by the following priority:

1. **Special Status** (if set): `maintenance`, `inactive`, `error`, `unknown`
2. **Capacity-based Status** (if no special status): Based on capacity percentage

### Status Types

#### Capacity-Based Status
- **Active** (0-99%): RVM is operational and ready for use
- **Full** (100%): RVM storage is completely full

#### Special Status
- **Maintenance**: RVM is under maintenance (overrides capacity)
- **Inactive**: RVM is offline or disabled (overrides capacity)
- **Error**: RVM has encountered an error (overrides capacity)
- **Unknown**: Status cannot be determined (fallback)

## Database Schema

### New Columns Added

```sql
-- Capacity percentage (0-100)
capacity INTEGER DEFAULT 0

-- Special status override
special_status VARCHAR NULL

-- Last capacity update timestamp
last_capacity_update TIMESTAMP NULL

-- Indexes for performance
INDEX (status, special_status)
INDEX (capacity)
```

### Database Structure

```sql
CREATE TABLE reverse_vending_machines (
    id BIGINT PRIMARY KEY,
    name VARCHAR NOT NULL,
    location_description TEXT,
    status VARCHAR NOT NULL,           -- Legacy status field
    capacity INTEGER DEFAULT 0,        -- NEW: 0-100 percentage
    special_status VARCHAR NULL,       -- NEW: maintenance, inactive, error, unknown
    last_capacity_update TIMESTAMP,    -- NEW: when capacity was last updated
    -- ... other existing columns
);
```

## Implementation

### Backend (PHP/Laravel)

#### Model: `ReverseVendingMachine`

```php
// Determine calculated status
public function getCalculatedStatusAttribute(): string
{
    // Priority 1: Special status
    if ($this->special_status && in_array($this->special_status, ['maintenance', 'inactive', 'error', 'unknown'])) {
        return $this->special_status;
    }
    
    // Priority 2: Capacity-based status
    if ($this->capacity >= 100) {
        return 'full';
    } elseif ($this->capacity >= 0) {
        return 'active';
    } else {
        return 'unknown';
    }
}

// Get status information
public function getStatusInfoAttribute(): array
{
    $status = $this->calculated_status;
    // Returns color, icon, label, description
}
```

#### Controller: `AdminRvmController`

```php
public function getRvmMonitoring()
{
    $rvms = ReverseVendingMachine::select([
        'id', 'name', 'location_description', 'status', 'capacity', 'special_status',
        'last_status_change', 'last_capacity_update', 'created_at', 'updated_at'
    ])->get();

    $processedRvms = $rvms->map(function($rvm) {
        return [
            'id' => $rvm->id,
            'name' => $rvm->name,
            'location' => $rvm->location_description,
            'capacity' => $rvm->capacity ?? 0,
            'status' => $rvm->calculated_status,  // Uses new logic
            'special_status' => $rvm->special_status,
            'status_info' => $rvm->status_info,
            'last_seen' => TimezoneHelper::formatTime($rvm->last_capacity_update)
        ];
    });
}
```

### Frontend (JavaScript)

#### Status Logic Functions

```javascript
function determineRvmStatus(capacity, specialStatus = null) {
    // Priority 1: Special status
    if (specialStatus && ['maintenance', 'inactive', 'error', 'unknown'].includes(specialStatus)) {
        return specialStatus;
    }
    
    // Priority 2: Capacity-based status
    if (capacity >= 100) {
        return 'full';
    } else if (capacity >= 0) {
        return 'active';
    } else {
        return 'unknown';
    }
}

function getStatusInfo(status) {
    const statusMap = {
        'active': { color: 'success', icon: 'fas fa-check-circle', label: 'Active' },
        'full': { color: 'danger', icon: 'fas fa-exclamation-triangle', label: 'Full' },
        'maintenance': { color: 'warning', icon: 'fas fa-tools', label: 'Maintenance' },
        'inactive': { color: 'secondary', icon: 'fas fa-pause-circle', label: 'Inactive' },
        'error': { color: 'danger', icon: 'fas fa-times-circle', label: 'Error' },
        'unknown': { color: 'info', icon: 'fas fa-question-circle', label: 'Unknown' }
    };
    return statusMap[status] || statusMap['unknown'];
}
```

#### Mock Data Structure

```javascript
const rawRvmData = [
    { id: 1, name: 'RVM-001', location: 'Mall Central', capacity: 85, specialStatus: null },
    { id: 2, name: 'RVM-002', location: 'Shopping Plaza', capacity: 60, specialStatus: 'maintenance' },
    { id: 3, name: 'RVM-003', location: 'City Center', capacity: 30, specialStatus: 'inactive' },
    { id: 4, name: 'RVM-004', location: 'Airport Terminal', capacity: 92, specialStatus: null },
    { id: 5, name: 'RVM-005', location: 'University Campus', capacity: 100, specialStatus: null },
    { id: 6, name: 'RVM-006', location: 'Hospital Lobby', capacity: 45, specialStatus: 'error' }
];

// Process with status logic
const rvms = rawRvmData.map(rvm => ({
    ...rvm,
    status: determineRvmStatus(rvm.capacity, rvm.specialStatus)
}));
```

## Status Examples

### Capacity-Based Examples

| Capacity | Status | Description |
|----------|--------|-------------|
| 0% | Active | Empty, ready for deposits |
| 25% | Active | Quarter full, operational |
| 50% | Active | Half full, operational |
| 75% | Active | Three-quarters full, operational |
| 99% | Active | Nearly full, still operational |
| 100% | Full | Completely full, needs emptying |

### Special Status Examples

| Special Status | Capacity | Final Status | Description |
|----------------|----------|--------------|-------------|
| maintenance | 85% | Maintenance | Under maintenance (ignores capacity) |
| inactive | 50% | Inactive | Offline/disabled (ignores capacity) |
| error | 30% | Error | Has error (ignores capacity) |
| unknown | 75% | Unknown | Status unclear (ignores capacity) |
| null | 100% | Full | Capacity-based: full |
| null | 85% | Active | Capacity-based: active |

## Migration from Mock to Real Data

### Consistency

The status logic is identical between mock data and real database data:

1. **Same Functions**: Both use `determineRvmStatus()` and `getStatusInfo()`
2. **Same Priority**: Special status always overrides capacity-based status
3. **Same Values**: All status values are consistent
4. **Same Display**: UI components use the same status information

### Data Structure Mapping

| Mock Data | Database | Description |
|-----------|----------|-------------|
| `capacity` | `capacity` | 0-100 percentage |
| `specialStatus` | `special_status` | maintenance, inactive, error, unknown |
| `status` | `calculated_status` | Final computed status |
| `last_seen` | `last_capacity_update` | Timestamp with timezone |

### API Response Format

Both mock and real data return the same format:

```json
{
  "success": true,
  "data": {
    "statistics": {
      "total_rvm": 12,
      "active_sessions": 8,
      "deposits_today": 756,
      "total_issues": 2
    },
    "rvms": [
      {
        "id": 1,
        "name": "RVM-001",
        "location": "Mall Central",
        "capacity": 85,
        "status": "active",
        "special_status": null,
        "status_info": {
          "color": "success",
          "icon": "fas fa-check-circle",
          "label": "Active",
          "description": "RVM is operational and ready"
        },
        "last_seen": "2:30:25 PM"
      }
    ]
  }
}
```

## Benefits

1. **Consistent Logic**: Same status determination everywhere
2. **Database Ready**: Schema supports real data seamlessly
3. **Flexible**: Special status can override capacity when needed
4. **Scalable**: Easy to add new status types
5. **Maintainable**: Single source of truth for status logic
6. **Timezone Aware**: All timestamps use configured timezone

## Future Enhancements

1. **Capacity Thresholds**: Configurable thresholds for different status levels
2. **Status History**: Track status changes over time
3. **Automated Status**: Auto-set special status based on conditions
4. **Status Notifications**: Alert when status changes
5. **Capacity Predictions**: Predict when RVM will be full
